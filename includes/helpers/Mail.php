<?php

namespace Demovox;

/**
 * The mail plugin class.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class Mail
{

	static function send($to, $subject, $body, $fromAddress = '', $fromName = '')
	{
		$headers = '';
		if ($fromAddress) {
			$headers = [$fromName ? 'from: ' . $fromName . ' <' . $fromAddress . '>' : 'from: ' . $fromAddress];
		}
		if ('wp_mail' == !Config::getValue('mail_method')) {
			return wp_mail($to, $subject, $body, $headers);
		}
		$mail = new Mail();
		add_filter('wp_mail_content_type', [$mail, 'setContentType']);
		$success = wp_mail($to, $subject, $body, $headers);
		// Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
		remove_filter('wp_mail_content_type', [$mail, 'setMailContentType']);

		return $success;
	}

	static function setContentType()
	{
		return 'text/html';
	}

	static function config(\PHPMailer $mailer)
	{
		$method = Config::getValue('mail_method');
		if ($method == 'wp_mail') {
			return;
		}
		add_action('wp_mail_failed', [new Mail(), 'logMailerErrors'], 10, 1);

		$mailer->isHTML();
		$mailer->SMTPDebug = defined('WP_SMTPDEBUG') && WP_SMTPDEBUG ? 2 : 0; // write 0 if you don't want to see client/server communication in page
		$mailer->CharSet = "utf-8";
		/*
		if ($fromAddress = Options::getValue('mail_reminder_from_address')) {
			try {
				$mailer->setFrom($fromAddress, Options::getValue('mail_confirm_from_name'));
			} catch (\phpmailerException $e) {
				Core::logMessage($e->getMessage(), 'mail');
			}
		}
		*/

		Core::logMessage('Mailer is configured to use method "' . $method . '"', 'notice', 'mail');
		switch ($method) {
			case 'mail':
				$mailer->IsMail();
				break;
			case 'sendmail':
				$mailer->isSendmail();
				break;
			case 'smtp':
				$mailer->IsSMTP();
				$mailer->Host = Config::getValue('mail_smtp_host');
				$mailer->Port = Config::getValue('mail_smtp_port');
				if ($smtpAuthType = Config::getValue('mail_smtp_authtype')) {
					$mailer->SMTPAuth = true;
					$mailer->Username = Config::getValue('mail_smtp_user');
					$mailer->Password = Config::getValue('mail_smtp_password');
					$mailer->AuthType = $smtpAuthType;
				}
				$mailer->SMTPSecure = Config::getValue('mail_smtp_security');
				break;
		}
	}

	static function validateAddress($address)
	{
		return \PHPMailer::validateAddress($address);
	}

	static function logMailerErrors(\WP_Error $wp_error)
	{
		$string = "Mailer: " . $wp_error->get_error_message() . "\n";
		Core::logMessage($string, 'error', 'mail');
	}

	/**
	 * @param signObject $sign
	 * @return string
	 */
	static function getMailSubject($sign)
	{
		$subject = Config::getValueByLang('mail_confirm_subj', $sign->language);
		$subject = str_replace('{first_name}', $sign->first_name, $subject);
		$subject = str_replace('{last_name}', $sign->last_name, $subject);
		return $subject;
	}

	/**
	 * @param signObject $sign
	 * @param string $mailSubject
	 * @return string
	 */
	static function getMailText($sign, $mailSubject)
	{
		$clientLang = $sign->language;
		$text = Config::getValueByLang('mail_confirm_body', $clientLang);
		if (Config::getValue('mail_nl2br')) {
			$text = Strings::nl2br($text);
		}
		$text = str_replace('{first_name}', $sign->first_name, $text);
		$text = str_replace('{last_name}', $sign->last_name, $text);
		$text = str_replace('{mail}', $sign->mail, $text);
		$text = str_replace('{link_pdf}', $sign->link_pdf, $text);
		$text = str_replace('{link_optin}', $sign->link_optin, $text);
		$text = str_replace('{subject}', $mailSubject, $text);
		return $text;
	}
}

class signObject
{
	public function __construct($language, $first_name, $last_name, $mail)
	{
		$this->language = $language;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->mail = $mail;
		$this->link_pdf = 'https://example.com/link_to_pdf';
		$this->link_optin = 'https://example.com/link_to_optin';
	}

	var $language,
		$first_name,
		$last_name,
		$mail,
		$link_pdf,
		$link_optin;
}