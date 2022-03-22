<?php

namespace Demovox;

/**
 * The mail plugin class.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     SP Schweiz
 */
class Mail
{
	const TYPE_CONFIRM = 0;
	const TYPE_REMIND_SHEET = 1;
	const TYPE_REMIND_SIGNUP = 2;

	/**
	 * @param SignaturesDto $row
	 * @param int           $mailType Mail::TYPE_CONFIRM|Mail::TYPE_REMIND_SHEET|Mail::TYPE_REMIND_SIGNUP
	 * @return array
	 */
	static function sendBySign(SignaturesDto $row, int $mailType): array
	{

		$clientLang  = $row->language;
		$fromAddress = Settings::getCValueByLang('mail_from_address', $clientLang);
		$fromName    = Settings::getCValueByLang('mail_from_name', $clientLang);

		$mailSubject = Mail::getMailSubject($row, $mailType);
		$mailText    = Mail::getMailText($row, $mailSubject, $mailType);

		$isSent = Mail::send($row->mail, $mailSubject, $mailText, $fromAddress, $fromName);

		$logMsg = 'Mail ' . ($isSent ? '' : 'NOT ') . 'sent for signature ID "' . $row->ID
				   . '" with language "' . $clientLang . '" with sender ' . $fromName . ' (' . $fromAddress . ')';

		return [$isSent, $logMsg];
	}

	static function send($to, $subject, $body, $fromAddress = '', $fromName = '') : bool
	{
		$headers = '';
		if ($fromAddress) {
			$headers = [$fromName ? 'from: ' . $fromName . ' <' . $fromAddress . '>' : 'from: ' . $fromAddress];
		}
		if ('wp_mail' == !Settings::getCValue('mail_method')) {
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

	/**
	 * @param $mailer \PHPMailer|\PHPMailer\PHPMailer\PHPMailer
	 * @return void
	 */
	static function config($mailer): void
	{
		$method = Settings::getCValue('mail_method');
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
				$mailer->Host = Settings::getCValue('mail_smtp_host');
				$mailer->Port = Settings::getCValue('mail_smtp_port');
				if ($smtpAuthType = Settings::getCValue('mail_smtp_authtype')) {
					$mailer->SMTPAuth = true;
					$mailer->Username = Settings::getCValue('mail_smtp_user');
					$mailer->Password = Settings::getCValue('mail_smtp_password');
					$mailer->AuthType = $smtpAuthType;
				}
				$mailer->SMTPSecure = Settings::getCValue('mail_smtp_security');
				break;
		}
	}

	static function validateAddress(string $address)
	{
		$mailer = class_exists('PHPMailer\PHPMailer\PHPMailer') ? \PHPMailer : \PHPMailer\PHPMailer\PHPMailer;
		return $mailer::validateAddress($address);
	}

	static function logMailerErrors(\WP_Error $wp_error)
	{
		$string = "Mailer: " . $wp_error->get_error_message() . "\n";
		Core::logMessage($string, 'error', 'mail');
	}

	static function echoMailerErrors( \WP_Error $wp_error ){
		echo "Mailer Error: " . $wp_error->get_error_message();
		exit;
		self::logMailerErrors( $wp_error);
	}

	/**
	 * @param SignaturesDto $sign
	 * @param int           $mailType
	 * @return string
	 */
	static function getMailSubject(SignaturesDto $sign, int $mailType): string
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
		$subject = Settings::getCValueByLang($confName, $sign->language);
		$subject = str_replace('{title}', $sign->title ? __($sign->title, 'demovox') : '', $subject);
		$subject = str_replace('{first_name}', $sign->first_name, $subject);
		$subject = str_replace('{last_name}', $sign->last_name, $subject);
		return $subject;
	}

	/**
	 * @param SignaturesDto $sign
	 * @param string        $mailSubject
	 * @param int           $mailType
	 * @return string
	 */
	static function getMailText(SignaturesDto $sign, string $mailSubject, int $mailType): string
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
		$text = Settings::getCValueByLang($confName, $clientLang);
		if (Settings::getCValue('mail_nl2br')) {
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