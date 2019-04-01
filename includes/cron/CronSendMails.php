<?php

namespace Demovox;

class CronSendMails extends CronBase
{

	public function __construct()
	{
		parent::__construct('sendMails');
	}

	public function run()
	{
		if (!Config::getValue('mail_confirmation_enabled')) {
			$this->setSkipped('Reminder mails are disabled in config');
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
			' is_mail_sent  <= 0 AND is_mail_sent > -3 AND is_step2_done = 1 AND is_deleted = 0',
			' LIMIT ' . $maxMails
		);

		$mail = new Mail; // TODO test
		$this->log('Found ' . count($rows) . ' signatures to send mails (max ' . $maxMails . ')', 'notice');
		add_action('phpmailer_init', [$mail, 'config'], 10, 1);
		foreach ($rows as $row) {
			$clientLang = $row->language;
			$fromAddress = Config::getValueByLang('mail_from_address', $clientLang);
			$fromName = Config::getValueByLang('mail_from_name', $clientLang);

			$mailSubject = Mail::getMailSubject($row);
			$mailText = Mail::getMailText($row, $mailSubject);

			$isSent = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName) ? 1 : ($row->is_mail_sent - 1);

			DB::updateStatus(['is_mail_sent' => $isSent], ['ID' => $row->ID]);
			$this->log(
				'Mail ' . ($isSent ? '' : 'NOT ') . 'sent for signature ID "' . $row->ID
				. '" with language "' . $row->language . '" with sender ' . $fromName . ' (' . $fromAddress . ')',
				'notice'
			);
		}
	}
}