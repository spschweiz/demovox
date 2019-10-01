<?php

namespace Demovox;

class CronMailIndex extends CronBase
{
	const STATUS_INIT = 0;
	const STATUS_RUNNING = 1;
	const STATUS_FINISHED = 2;

	protected $maxSignsPerCall = 200;

	public function run()
	{
		if (!Config::getValue('mail_remind_sheet_enabled') && !Config::getValue('mail_remind_signup_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
			return;
		}
		if (!Config::getValue('mail_remind_dedup')) {
			$this->setSkipped('Reminder mail deduplication is disabled in config');
			return;
		}
		if (!$this->prepareRun()) {
			return;
		}
		$this->setRunningStart();
		$this->indexMails();
		$this->setRunningStop();
	}

	protected function indexMails()
	{
		// Set state
		$status = Core::getOption('cron_index_mail_status');
		if ($status === false || $status === self::STATUS_INIT) {
			$statusRun = self::STATUS_INIT;
		} else {
			$statusRun = self::STATUS_RUNNING;
		}
		Core::setOption('cron_index_mail_status', $statusRun);

		list($countTotal, $rows) = $this->getRowsToImport();

		$countProceeded = 0;
		$countInserted = 0;
		if ($countTotal) {
			$inserted = null;
			foreach ($rows as $row) {
				$inserted = $this->importRow($row);
				if ($inserted) {
					$countInserted++;
				}
			}

			$countProceeded = count($rows);
		}

		if ($statusRun === self::STATUS_INIT && $countTotal > $countProceeded) {
			$statusEnd = self::STATUS_INIT;
			$this->setStatusMessage('Proceeded ' . $countProceeded . ' new mail addresses, thereof ' . $countInserted
				. ' unique adresses imported. ' . $countTotal - $countProceeded . ' more to go');
		} else {
			$statusEnd = self::STATUS_FINISHED;
			$this->setStatusMessage('Proceeded ' . $countProceeded . ' new mail addresses, thereof ' . $countInserted
				. ' unique adresses imported. ');
		}
		Core::setOption('cron_index_mail_status', $statusEnd);
	}

	/**
	 * @param object $row signature row
	 * @return bool|null inserted | null for errors
	 */
	protected function importRow($row)
	{
		$inserted = null;
		$hashedMail = Strings::hashMail($row->mail);
		$mailRow = DB::getRow(
			[
				'ID',
				'creation_date',
				'is_step2_done',
				'is_sheet_received',
				'state_remind_sheet_sent',
				'state_remind_signup_sent',
			],
			"mail = '" . $hashedMail . "'",
			DB::TABLE_MAIL
		);

		if (!$mailRow) {
			$setMailData = [
				'sign_ID'                  => $row->ID,
				'mail'                     => $hashedMail,
				'creation_date'            => $row->creation_date,
				'is_step2_done'            => $row->is_step2_done ? 1 : 0,
				'is_sheet_received'        => $row->is_sheet_received ? 1 : 0,
				'state_remind_sheet_sent'  => $row->state_remind_sheet_sent,
				'state_remind_signup_sent' => $row->state_remind_signup_sent,
			];
			$save = DB::insert($setMailData, DB::TABLE_MAIL);
			$inserted = true;
		} else {
			$setMailData = [];
			$setMailData['sign_ID'] = $row->ID;
			$setMailData['creation_date'] = $row->creation_date;
			if (!$mailRow->is_step2_done && $row->is_step2_done) {
				$setMailData['is_step2_done'] = 1;
			}
			if (!$mailRow->is_sheet_received && $row->is_sheet_received) {
				$setMailData['is_sheet_received'] = 1;
			}
			if ($mailRow->state_remind_sheet_sent !== 1 && $row->state_remind_sheet_sent == 1) {
				$setMailData['state_remind_sheet_sent'] = 1;
			}
			if ($mailRow->state_remind_signup_sent !== 1 && $row->state_remind_signup_sent == 1) {
				$setMailData['state_remind_signup_sent'] = 1;
			}
			$save = DB::updateStatus($setMailData, ['ID' => $mailRow->ID], DB::TABLE_MAIL);
			$inserted = false;
		}

		if ($save === false) {
			$msg = 'Exception on save mail status with sign_ID=' . $row->ID . ' with error:' . DB::getError();
			Core::showError($msg, 500);
			$this->setStatusMessage('Could not save mail from signature ID ' . $row->ID, false);
			return null;
		}

		return $inserted;
	}

	/**
	 * @return array|null
	 */
	protected function getRowsToImport()
	{
		// To ensure we get the correct is_step2_done for signatures a client is still working on, wait for all php sessions to die
		$maxDate = date("Y-m-d G:i:s", strtotime('12 hour ago'));
		$where = "is_deleted = 0 AND creation_date < '{$maxDate}' AND  is_outside_scope = 0";

		$lastImport = DB::getRow(['sign_ID'], null, DB::TABLE_MAIL, 'ORDER BY sign_ID DESC');
		if ($lastImport) {
			$where .= ' AND ID > ' . $lastImport->sign_ID;
		}
		$countTotal = DB::count($where, DB::TABLE_SIGN);
		if (!$countTotal) {
			return null;
		}

		$sqlAppend = ($countTotal > $this->maxSignsPerCall ? ' ORDER BY ID ASC LIMIT ' . $this->maxSignsPerCall : '');
		$rows = DB::getResults(
			[
				'ID',
				'mail',
				'creation_date',
				'is_step2_done',
				'is_sheet_received',
				'state_remind_sheet_sent',
				'state_remind_signup_sent',
			],
			$where,
			DB::TABLE_SIGN,
			$sqlAppend
		);

		$this->log('Loaded ' . count($rows) . ' signatures to index their mail (there is a total of ' . $countTotal
			. ' to import, max ' . $this->maxSignsPerCall . ' per execution)', 'notice');

		return [$countTotal, $rows];
	}
}
