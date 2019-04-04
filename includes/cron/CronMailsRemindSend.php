<?php

namespace Demovox;

class CronMailsRemindSend extends CronBase
{
	protected $scheduleRecurrence = 'daily';
	/** @var bool */
	protected $isDedup = false;

	protected function sendPendingMails()
	{
		$colsSign = [
			'ID',
			'link_optin',
			'link_pdf',
			'guid',
			'first_name',
			'last_name',
			'mail',
			'language',
			'is_reminder_sent',
		];

		$minAge = intval(Config::getValue('mail_reminder_min_age'));
		$maxDate = date("Y-m-d", strtotime($minAge . ' day ago'));
		$where = "creation_date < '{$maxDate}' AND is_sheet_received = 0 "
			. 'AND is_reminder_sent <= 0 AND is_reminder_sent > -3 ';

		$maxMails = intval(Config::getValue('mail_max_per_execution'));
		$sqlAppend = ' LIMIT ' . $maxMails . ' ORDER BY ID ASC';

		if ($this->isDedup) {
			$rows = DB::getResults(
				['ID', 'sign_ID', 'is_reminder_sent'],
				$where,
				$sqlAppend,
				DB::TABLE_MAIL
			);
		} else {
			$where .= 'AND is_deleted = 0';
			$rows = DB::getResults($colsSign, $where . ' AND is_step2_done = 1', $sqlAppend);
		}
		$this->log('Loaded ' . count($rows) . ' signatures to send mails (select is limited to ' . $maxMails
			. ' per cron execution)', 'notice');

		$mail = new Mail;
		add_action('phpmailer_init', [$mail, 'config'], 10, 1);

		foreach ($rows as $row) {

			if ($this->isDedup) {
				$rowMail = $row;
				$row = DB::getRow($colsSign, 'ID = ' . $rowMail->sign_ID);
				$row->is_reminder_sent = $rowMail->is_reminder_sent;
				if ($row->is_deleted) {
					DB::delete(['ID' => $rowMail->ID], DB::TABLE_SIGN);
					continue;
				}

				$isSent = $this->sendMail($row);

				DB::updateStatus(['is_reminder_sent' => $isSent], ['ID = ' . $rowMail->ID]);
			} else {
				$this->sendMail($row);
			}
		}

		$this->setStatusMessage('Sent ' . count($rows) . ' mails');
	}

	public function run()
	{
		if (!Config::getValue('mail_reminder_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
			return;
		}
		if (!Config::getValue('mail_reminder_dedup')) {
			$importStatus = Core::getOption('cron_index_mail_status');
			if ($importStatus === false || $importStatus === CronMailsRemindIndex::STATUS_INIT) {
				$this->setSkipped('Reminder mail deduplication indexing (CronMailsRemindIndex) has not finished initial index yet');
				return;
			}
		}
		if (!$this->prepareRun()) {
			return;
		}
		$this->setRunningStart();
		$this->sendMails();
		$this->setRunningStop();
	}

	/**
	 * @param $row
	 * @return int
	 */
	protected function sendMail($row)
	{
		$clientLang = $row->language;
		$fromAddress = Config::getValueByLang('mail_from_address', $clientLang);
		$fromName = Config::getValueByLang('mail_from_name', $clientLang);

		$mailSubject = Mail::getMailSubject($row);
		$mailText = Mail::getMailText($row, $mailSubject);

		$isSent = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName);
		$isSentCount = $isSent ? 1 : ($row->is_mail_sent - 1);

		DB::updateStatus(['is_mail_sent' => $isSentCount], ['ID' => $row->ID]);
		$this->log(
			'Mail ' . ($isSent ? '' : 'NOT ') . 'sent for signature ID "' . $row->ID
			. '" with language "' . $row->language . '" with sender ' . $fromName . ' (' . $fromAddress . ')',
			$isSent ? 'notice' : 'error'
		);
		return $isSentCount;
	}
}