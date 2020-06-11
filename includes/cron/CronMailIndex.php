<?php

namespace Demovox;

class CronMailIndex extends CronBase
{
	const STATUS_INIT     = 0;
	const STATUS_RUNNING  = 1;
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

		list($countTotal, $rows) = $this->getPending();

		$countProceeded = 0;
		$countInserted  = 0;
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
			$this->setStateMessage(
				'Proceeded ' . $countProceeded . ' new mail addresses, thereof ' . $countInserted
				. ' unique adresses imported. ' . $countTotal - $countProceeded . ' more to go'
			);
		} else {
			$statusEnd = self::STATUS_FINISHED;
			$this->setStateMessage(
				'Proceeded ' . $countProceeded . ' new mail addresses, thereof ' . $countInserted
				. ' unique adresses imported. '
			);
		}
		Core::setOption('cron_index_mail_status', $statusEnd);
	}

	/**
	 * @param DbSignatures $row signature row
	 *
	 * @return bool inserted
	 */
	protected function importRow($row): bool
	{
		$dbMailDd = new DbMailDedup();
		$save     = $dbMailDd->importRow($row);

		if ($save === false) {
			$msg = 'Exception on save mail status with sign_ID=' . $row->ID
				   . '. Last error: "' . Db::getLastError() . '". Last query:' . Db::getLastQuery();
			Core::errorDie($msg, 500);
			$this->setStateMessage('Could not save mail from signature ID ' . $row->ID, false);
		}

		return $save === 'insert';
	}

	/**
	 * @return array|null
	 */
	protected function getPending()
	{
		$dbMailDd = new DbMailDedup();
		$dbSign   = new DbSignatures();
		// To ensure we get the correct is_step2_done for signatures a client is still working on, wait for all php sessions to die
		$maxDate = date("Y-m-d G:i:s", strtotime('12 hour ago'));
		$where   = "is_deleted = 0 AND creation_date < '{$maxDate}' AND  is_outside_scope = 0";

		$lastImport = $dbMailDd->getRow(['sign_ID'], null, 'ORDER BY sign_ID DESC');
		if ($lastImport) {
			$where .= ' AND ID > ' . $lastImport->sign_ID;
		}
		$countTotal = $dbSign->count($where);
		if (!$countTotal) {
			return null;
		}

		$sqlAppend = ($countTotal > $this->maxSignsPerCall ? ' ORDER BY ID ASC LIMIT ' . $this->maxSignsPerCall : '');
		$rows      = $dbSign->getResults(
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
			$sqlAppend
		);

		$this->log(
			'Loaded ' . count($rows) . ' signatures to index their mail (there is a total of ' . $countTotal
			. ' to import, max ' . $this->maxSignsPerCall . ' per execution)',
			'notice'
		);

		return [$countTotal, $rows];
	}
}
