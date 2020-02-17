<?php

namespace Demovox;

class CronMailRemindSheet extends CronMailBase
{
	protected $scheduleRecurrence = 'daily';
	/** @var array */
	protected $colsSign = [
		'ID',
		'link_optin',
		'link_pdf',
		'guid',
		'first_name',
		'last_name',
		'mail',
		'language',
		'state_remind_sheet_sent',
	];

	public function run()
	{
		if (!Config::getValue('mail_remind_sheet_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
			return;
		}
		if (!$this->prepareRunMailReminder()) {
			return;
		}
		$this->setRunningStart();
		$this->sendPendingMails();
		$this->setRunningStop();
	}

	protected function sendPendingMails()
	{
		$dbSign   = new DbSignatures();
		$dbMailDd = new DbMailDedup();
		$rows     = $this->getPending();
		$maxMails = $this->limitPerExecution;
		$this->log(
			'Loaded ' . count($rows) . ' signatures to send mails (select is limited to ' . $maxMails . ' per cron execution)',
			'notice'
		);

		Loader::addAction('phpmailer_init', new Mail(), 'config', 10, 1);

		foreach ($rows as $row) {

			if ($this->isDedup) {
				$rowMail                      = $row;
				$row                          = $dbSign->getRow($this->colsSign, 'ID = ' . $rowMail->sign_ID);
				$row->state_remind_sheet_sent = $rowMail->state_remind_sheet_sent;
				if ($row->is_deleted) {
					$dbSign->delete(['ID' => $rowMail->ID]);
					continue;
				}

				$isSent = $this->sendMail($row);

				$dbMailDd->updateStatus(['state_remind_sheet_sent' => $isSent], ['ID' => $rowMail->ID]);
			} else {
				$this->sendMail($row);
			}
		}

		$this->setStateMessage('Sent ' . count($rows) . ' mails');
	}

	/**
	 * @param DbSignatures $row
	 *
	 * @return int
	 */
	protected function sendMail($row)
	{
		$clientLang  = $row->language;
		$fromAddress = Config::getValueByLang('mail_from_address', $clientLang);
		$fromName    = Config::getValueByLang('mail_from_name', $clientLang);

		$mailSubject = Mail::getMailSubject($row, Mail::TYPE_REMIND_SHEET);
		$mailText    = Mail::getMailText($row, $mailSubject, Mail::TYPE_REMIND_SHEET);

		$isSent    = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName);
		$stateSent = $isSent ? 1 : ($row->state_remind_sheet_sent - 1);

		$dbSign = new DbSignatures();
		if ($isSent) {
			$dbSign->updateStatus(
				['state_remind_sheet_sent' => $stateSent, 'remind_sheet_sent_date' => current_time('mysql')],
				['ID' => $row->ID]
			);
		} else {
			$dbSign->updateStatus(
				['state_remind_sheet_sent' => $stateSent],
				['ID' => $row->ID]
			);
		}
		$this->log(
			'Mail ' . ($isSent ? '' : 'NOT ') . 'sent for signature ID "' . $row->ID
			. '" with language "' . $row->language . '" with sender ' . $fromName . ' (' . $fromAddress . ')',
			$isSent ? 'notice' : 'error'
		);
		return $stateSent;
	}

	/**
	 * @return array
	 */
	public function getPending(): array
	{
		$minAge  = intval(Config::getValue('mail_remind_sheet_min_age'));
		$maxDate = date("Y-m-d", strtotime($minAge . ' day ago'));
		$where   = "creation_date < '{$maxDate}' AND is_step2_done = 1 AND is_sheet_received = 0 "
				   . 'AND state_remind_sheet_sent <= 0 AND state_remind_sheet_sent > -3';

		$sqlAppend = 'ORDER BY ID ASC LIMIT ' . $this->limitPerExecution;

		if ($this->isDedup) {
			$dbMailDd = new DbMailDedup();
			$rows     = $dbMailDd->getResults(
				['ID', 'sign_ID', 'state_remind_sheet_sent'],
				$where,
				$sqlAppend
			);
		} else {
			$dbSign = new DbSignatures();
			$where  .= ' AND is_deleted = 0 AND is_outside_scope = 0';
			$rows   = $dbSign->getResults($this->colsSign, $where, $sqlAppend);
		}
		return $rows;
	}
}