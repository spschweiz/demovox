<?php

namespace Demovox;

/**
 * The mail plugin class.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     Fabian Horlacher / SP Schweiz
 */
class Mail
{
	const TYPE_CONFIRM = 0;
	const TYPE_REMIND_SHEET = 1;
	const TYPE_REMIND_SIGNUP = 2;

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

	static function config(\PHPMailer\PHPMailer\PHPMailer $mailer)
	{
		$method = Config::getValue('mail_method');
		if ($method == 'wp_mail') {
			return;
		}
		Loader::addAction('wp_mail_failed', new Mail(), 'logMailerErrors', 10, 1);

		$mailer->isHTML();
		$mailer->SMTPDebug = defined('WP_SMTPDEBUG') && WP_SMTPDEBUG ? 2 : 0; // write 0 if you don't want to see client/server communication in page
		$mailer->CharSet = "utf-8";

		Core::logMessage('Mailer is configured to use method "' . $method . '"', 'notice', 'mail');
		switch ($method) {
			case 'mail':
			default:
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
		return \PHPMailer\PHPMailer\PHPMailer::validateAddress($address);
	}

	static function logMailerErrors(\WP_Error $wp_error)
	{
		$string = "Mailer: " . $wp_error->get_error_message() . "\n";
		Core::logMessage($string, 'error', 'mail');
	}

	/**
	 * @param signObject $sign
	 * @param int $mailType
	 * @return string
	 */
	static function getMailSubject($sign, $mailType)
	{
		switch($mailType){
			case self::TYPE_REMIND_SHEET:
				$confName = 'mail_remind_sheet_subj';
				break;
			case self::TYPE_REMIND_SIGNUP:
				$confName = 'mail_remind_signup_subj';
				break;
			case self::TYPE_CONFIRM:
			default:
				$confName = 'mail_confirm_subj';
				break;
		}
		$subject = Config::getValueByLang($confName, $sign->language);
		$subject = str_replace('{title}', $sign->title ? __($sign->title, 'demovox') : '', $subject);
		$subject = str_replace('{first_name}', $sign->first_name, $subject);
		$subject = str_replace('{last_name}', $sign->last_name, $subject);
		return $subject;
	}

	/**
	 * @param signObject $sign
	 * @param string $mailSubject
	 * @param int $mailType
	 * @return string
	 */
	static function getMailText($sign, $mailSubject, $mailType)
	{
		$clientLang = $sign->language;
		$linkHome = get_home_url();
		switch($mailType){
			case self::TYPE_REMIND_SHEET:
				$confName = 'mail_remind_sheet_body';
				break;
			case self::TYPE_REMIND_SIGNUP:
				$confName = 'mail_remind_signup_body';
				break;
			case self::TYPE_CONFIRM:
			default:
				$confName = 'mail_confirm_body';
				break;
		}
		$text = Config::getValueByLang($confName, $clientLang);
		if (Config::getValue('mail_nl2br')) {
			$text = Strings::nl2br($text);
		}
		$text = str_replace('{title}', $sign->title ? __($sign->title, 'demovox') : '', $text);
		$text = str_replace('{first_name}', $sign->first_name, $text);
		$text = str_replace('{last_name}', $sign->last_name, $text);
		$text = str_replace('{mail}', $sign->mail, $text);
		$text = str_replace('{link_pdf}', $sign->link_pdf, $text);
		$text = str_replace('{link_optin}', $sign->link_optin, $text);
		$text = str_replace('{link_home}', $linkHome, $text);
		$text = str_replace('{subject}', $mailSubject, $text);
		$text = str_replace('{guid}', $sign->guid, $text);
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
		$this->link_pdf = Strings::getPageUrl('SIGNEE_PERSONAL_CODE');;
		$this->link_optin = Strings::getPageUrl('SIGNEE_PERSONAL_CODE', Config::getValue('use_page_as_optin_link'));
	}

	var $language,
		$title,
		$first_name,
		$last_name,
		$mail,
		$link_pdf,
		$link_optin;
}