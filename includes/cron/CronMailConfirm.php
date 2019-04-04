<?php

namespace Demovox;

class CronMailConfirm extends CronBase
{
	public function run()
	{
		if (!Config::getValue('mail_confirmation_enabled')) {
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

	protected function sendPendingMails()
	{
		$maxMails = intval(Config::getValue('mail_max_per_execution'));
		$rows = DB::getResults(
			['ID', 'link_optin', 'link_pdf', 'guid', 'first_name', 'last_name', 'mail', 'language', 'is_mail_sent'],
			' is_mail_sent <= 0 AND is_mail_sent > -3 AND is_step2_done = 1 AND is_deleted = 0',
			' LIMIT ' . $maxMails
		);

		$this->log('Loaded ' . count($rows) . ' signatures to send mails (select is limited to ' . $maxMails
			. ' per cron execution)', 'notice');

		$mail = new Mail;
		add_action('phpmailer_init', [$mail, 'config'], 10, 1);
		foreach ($rows as $row) {
			$this->sendMail($row);
		}

		$this->setStatusMessage('Sent ' . count($rows) . ' mails');
	}

	/**
	 * @param $row
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
	}
}