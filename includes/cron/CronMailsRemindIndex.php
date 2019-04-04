<?php

namespace Demovox;

class CronMailsRemindIndex extends CronBase
{
	const STATUS_INIT = 0;
	const STATUS_RUNNING = 1;
	const STATUS_FINISHED = 2;

	protected $maxSignsPerCall = 200;

	public function run()
	{
		if (!Config::getValue('mail_reminder_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
			return;
		}
		if (!Config::getValue('mail_reminder_dedup')) {
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
		$lastImportId = Core::getOption('cron_index_mail_last_import_id');
		if ($status === false || $status === self::STATUS_INIT) {
			$statusRun = self::STATUS_INIT;
		} else {
			$statusRun = self::STATUS_RUNNING;
		}
		Core::setOption('cron_index_mail_status', $statusRun);

		list($count, $rows) = $this->getRowsToImport($lastImportId);

		foreach ($rows as $row) {
			$lastImportId = $this->importRow($row);
		}

		Core::setOption('cron_index_mail_last_import_id', $lastImportId);
		if ($statusRun === self::STATUS_INIT && $count > count($rows)) {
			$statusEnd = self::STATUS_INIT;
			$this->setStatusMessage('Imported ' . count($rows) . ' mail addresses, ' . $count - count($rows) . ' more to go');
		} else {
			$statusEnd = self::STATUS_FINISHED;
			$this->setStatusMessage('Imported ' . count($rows) . ' mail addresses');
		}
		Core::setOption('cron_index_mail_status', $statusEnd);
	}

	/**
	 * @param $row
	 * @return mixed
	 */
	protected function importRow($row)
	{
		$hashedMail = Strings::hashMail($row->mail);
		$mailRow = DB::getRow(
			['ID', 'creation_date', 'is_sheet_received', 'is_reminder_sent'],
			"mail = '" . $hashedMail . "''",
			DB::TABLE_MAIL
		);

		if (!$mailRow) {
			$save = DB::insert(
				[
					'sign_ID'           => $row->ID,
					'mail'              => $hashedMail,
					'creation_date'     => $row->creation_date,
					'is_sheet_received' => $row->is_sheet_received ? 1 : 0,
					'is_reminder_sent'  => $row->is_reminder_sent ? 1 : 0,
				],
				DB::TABLE_MAIL
			);
		} else {
			$setMailData = [];
			$setMailData['sign_ID'] = $row->ID;
			$setMailData['creation_date'] = $row->creation_date;
			if (!$mailRow->is_sheet_received && $row->is_sheet_received) {
				$setMailData['is_sheet_received'] = 1;
			}
			if (!$mailRow->is_reminder_sent && $row->is_reminder_sent) {
				$setMailData['is_reminder_sent'] = 1;
			}
			$save = DB::updateStatus($setMailData, ['ID' => $mailRow->ID], DB::TABLE_MAIL);
		}

		if (!$save) {
			$this->setStatusMessage('Could not save mail from signature ID ' . $row->ID, false);
			$msg = 'Exception on save mail status with sign_ID=' . $row->ID . ' with error:' . DB::getError();
			Core::showError($msg, 500);
		}

		return $row->ID;
	}

	/**
	 * @param $lastImportId
	 * @return array
	 */
	protected function getRowsToImport($lastImportId)
	{
		// To ensure we dont skip any signature the client is still working on, wait for all php sessions to die
		$maxDate = date("Y-m-d", strtotime('12 hour ago'));
		$where = "is_deleted = 0 AND is_step2_done = 1 AND creation_date < '{$maxDate}'";
		if ($lastImportId) {
			$where .= ' AND ID > ' . $lastImportId;
		}
		$count = DB::count($where, DB::TABLE_SIGN);

		$sqlAppend = ($count > $this->maxSignsPerCall ? ' LIMIT ' . $this->maxSignsPerCall : '')
			. ' ORDER BY ID ASC';
		$rows = DB::getResults(
			['ID', 'mail', 'creation_date', 'is_sheet_received', 'is_reminder_sent',],
			$where,
			$sqlAppend,
			DB::TABLE_SIGN
		);

		$this->log('Loaded ' . count($rows) . ' signatures to index their mail (there is a total of ' . $count
			. ' to import, max ' . $this->maxSignsPerCall . ' per execution)', 'notice');

		return [$count, $rows];
	}
}