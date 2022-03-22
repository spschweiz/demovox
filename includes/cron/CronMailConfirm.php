<?php

namespace Demovox;

class CronMailConfirm extends CronMailBase
{
	public function run()
	{
		if (!Settings::getCValue('mail_confirmation_enabled')) {
			$this->setSkipped('Confirmation mails are disabled in config');
			return;
		}
		if (!$this->prepareRun()) {
			return;
		}
		$this->setRunningStart();
		$this->sendPendingMails();
		$this->setRunningStop();
	}

	protected function sendPendingMails(): void
	{
		$maxMails = intval(Settings::getValue('mail_max_per_execution')) ?: 300;
		$dbSign   = new DbSignatures();
		$rows     = $dbSign->getResults(
			['ID', 'link_optin', 'link_pdf', 'guid', 'title', 'first_name', 'last_name', 'mail', 'language', 'state_confirm_sent'],
			'is_step2_done = 1 AND is_sheet_received = 0 AND state_confirm_sent <= 0 AND state_confirm_sent > -3 AND is_deleted = 0',
			' LIMIT ' . $maxMails
		);

		$this->log(
			'Loaded ' . count($rows) . ' signatures to send mails (select is limited to ' . $maxMails
			. ' per cron execution)',
			'notice'
		);

		Loader::addAction('phpmailer_init', new Mail(), 'config', 10, 1);
		foreach ($rows as $row) {
			$this->sendMail($row);
		}

		$this->setStateMessage('Sent ' . count($rows) . ' mails');
	}

	/**
	 * @param SignaturesDto $row
	 */
	protected function sendMail(SignaturesDto $row): void
	{
		[$isSent, $logMsg] = Mail::sendBySign($row, Mail::TYPE_CONFIRM);
		$stateSent = $isSent ? 1 : ($row->state_confirm_sent - 1);
		$dbSign    = new DbSignatures();
		$rows      = $dbSign->updateStatus(['state_confirm_sent' => $stateSent], ['ID' => $row->ID]);
		$this->log($logMsg, $isSent ? 'notice' : 'error');
	}

}