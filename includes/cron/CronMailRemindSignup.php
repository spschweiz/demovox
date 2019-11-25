<?php

namespace Demovox;

class CronMailRemindSignup extends CronMailBase
{
	protected $scheduleRecurrence = 'daily';
	/** @var array */
	protected $colsSign = [
		'ID',
		'link_optin',
		'guid',
		'first_name',
		'last_name',
		'mail',
		'language',
		'state_remind_signup_sent',
	];

	public function run()
	{
		if (!Config::getValue('mail_remind_signup_enabled')) {
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
		$rows = $this->getPending();

		$this->log(
			'Loaded ' . count($rows) . ' signatures to send mails (select is limited to ' . $this->limitPerExecution
			. ' per cron execution)',
			'notice'
		);

		Loader::addAction('phpmailer_init', new Mail(), 'config', 10, 1);

		$dbSign   = new DbSignatures();
		$dbMailDd = new DbMailDedup();
		foreach ($rows as $row) {

			if ($this->isDedup) {
				$rowMail                       = $row;
				$row                           = $dbSign->getRow($this->colsSign, 'ID = ' . $rowMail->sign_ID);
				$row->state_remind_signup_sent = $rowMail->state_remind_signup_sent;
				if ($row->is_deleted) {
					$dbSign->delete(['ID' => $rowMail->ID]);
					continue;
				}

				$isSent = $this->sendMail($row);

				$dbMailDd->updateStatus(['state_remind_signup_sent' => $isSent], ['ID' => $rowMail->ID]);
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

		$mailSubject = Mail::getMailSubject($row, Mail::TYPE_REMIND_SIGNUP);
		$mailText    = Mail::getMailText($row, $mailSubject, Mail::TYPE_REMIND_SIGNUP);

		$isSent    = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName);
		$stateSent = $isSent ? 1 : ($row->state_remind_signup_sent - 1);

		$dbSign = new DbSignatures();
		$dbSign->updateStatus(['state_remind_signup_sent' => $stateSent], ['ID' => $row->ID]);
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

		$minAge  = intval(Config::getValue('mail_remind_signup_min_age'));
		$maxDate = date("Y-m-d", strtotime($minAge . ' day ago'));
		$where   = "creation_date < '{$maxDate}' AND is_step2_done = 0 "
				   . 'AND state_remind_signup_sent <= 0 AND state_remind_signup_sent > -3';

		$sqlAppend = 'ORDER BY ID ASC LIMIT ' . $this->limitPerExecution;

		if ($this->isDedup) {
			$dbMailDd = new DbMailDedup();
			$rows     = $dbMailDd->getResults(
				['ID', 'sign_ID', 'state_remind_signup_sent'],
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