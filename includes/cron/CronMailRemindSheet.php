<?php

namespace Demovox;

class CronMailRemindSheet extends CronBase
{
	protected $scheduleRecurrence = 'daily';
	/** @var bool */
	protected $isDedup = false;

	public function run()
	{
		if (!Config::getValue('mail_remind_sheet_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
			return;
		}
		if (!Config::getValue('mail_remind_dedup')) {
			$importStatus = Core::getOption('cron_index_mail_status');
			if ($importStatus === false || $importStatus === CronMailIndex::STATUS_INIT) {
				$this->setSkipped('Reminder mail deduplication indexing (CronMailsRemindIndex) has not finished initial index yet');
				return;
			}
		}
		if (!$this->prepareRun()) {
			return;
		}
		$this->setRunningStart();
		$this->sendPendingMails();
		$this->setRunningStop();
	}

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
			'is_remind_sheet_sent',
		];

		$minAge = intval(Config::getValue('mail_remind_sheet_min_age'));
		$maxDate = date("Y-m-d", strtotime($minAge . ' day ago'));
		$where = "creation_date < '{$maxDate}' AND is_sheet_received = 0 "
			. 'AND is_remind_sheet_sent <= 0 AND is_remind_sheet_sent > -3 ';

		$maxMails = intval(Config::getValue('mail_max_per_execution'));
		$sqlAppend = 'ORDER BY ID ASC LIMIT ' . $maxMails;

		if ($this->isDedup) {
			$rows = DB::getResults(
				['ID', 'sign_ID', 'is_remind_sheet_sent'],
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
				$row->is_remind_sheet_sent = $rowMail->is_remind_sheet_sent;
				if ($row->is_deleted) {
					DB::delete(['ID' => $rowMail->ID], DB::TABLE_SIGN);
					continue;
				}

				$isSent = $this->sendMail($row);

				DB::updateStatus(['is_remind_sheet_sent' => $isSent], ['ID = ' . $rowMail->ID], DB::TABLE_MAIL);
			} else {
				$this->sendMail($row);
			}
		}

		$this->setStatusMessage('Sent ' . count($rows) . ' mails');
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

		$mailSubject = Mail::getMailSubject($row, Mail::TYPE_REMIND_SHEET);
		$mailText = Mail::getMailText($row, $mailSubject, Mail::TYPE_REMIND_SHEET);

		$isSent = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName);
		$isSentCount = $isSent ? 1 : ($row->is_mail_sent - 1);

		DB::updateStatus(['is_remind_sheet_sent' => $isSentCount], ['ID' => $row->ID]);
		$this->log(
			'Mail ' . ($isSent ? '' : 'NOT ') . 'sent for signature ID "' . $row->ID
			. '" with language "' . $row->language . '" with sender ' . $fromName . ' (' . $fromAddress . ')',
			$isSent ? 'notice' : 'error'
		);
		return $isSentCount;
	}
}