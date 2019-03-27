<?php

namespace Demovox;

class ConfigVars
{
	static private $fieldsCache = null;
	static public $sections = [
		'base'                 => [
			'title' => 'Base settings',
			'page'  => 'demovoxFields1',
		],
		'signatureSheet'       => [
			'title' => 'Signature sheet',
			'page'  => 'demovoxFields1',
			'sub'   => '',
		],
		'signatureSheetPdf'    => [
			'title' => 'Signature sheet PDF',
			'page'  => 'demovoxFields1',
			'sub'   => 'Upload and select the signature sheet. If you use language specific domains on your page, adapt the paths accordingly.',
		],
		'signatureSheetFields' => [
			'title' => 'Signature sheet fields',
			'page'  => 'demovoxFields1',
			'sub'   => 'Fields on the signature sheet',
		],
		'mailText'             => [
			'title' => 'Email settings',
			'page'  => 'demovoxFields2',
		],
		'optIn'                => [
			'title' => 'Opt-in',
			'page'  => 'demovoxFields3',
		],
		'optInText'            => [
			'title' => 'Text beside the checkbox',
			'page'  => 'demovoxFields3',
		],
		'security'             => [
			'title' => 'Security',
			'page'  => 'demovoxFields4',
		],
		'mailConfig'           => [
			'title' => 'Email engine / server',
			'page'  => 'demovoxFields4',
		],
		'cron'                 => [
			'title' => 'Cron',
			'page'  => 'demovoxFields4',
		],
		'api'                  => [
			'title' => 'API settings',
			'page'  => 'demovoxFields4',
		],
		'danger'               => [
			'title' => 'Danger area',
			'page'  => 'demovoxFields4',
			'sub'   => 'This is where you can enable the dangerous stuff',
		],
	];
	static public $fields = [
		[
			'uid'          => 'add_count',
			'label'        => 'Add to signature count',
			'section'      => 'base',
			'type'         => 'number',
			'default'      => '0',
			'supplemental' => 'Add to public count to include manually collected signs',
		],
		[
			'uid'          => 'allow_swiss_abroad',
			'label'        => 'Swiss abroad',
			'section'      => 'base',
			'type'         => 'checkbox',
			'supplemental' => 'Show a country selection for swiss abroad to sign the initiative',
			'default'      => 1,
		],
		[
			'uid'          => 'use_page_as_success',
			'label'        => 'Redirect user after filling form',
			'section'      => 'signatureSheet',
			'type'         => 'wpPage',
			'optionNone'   => '[No, show on current page]',
			'supplemental' => 'Replace the user form by ajax with the signature sheet or redirect user to this page after successfully filling out the form. You should include [demovox_form] on that page to show the signature sheet.',
		],
		[
			'uid'          => 'use_page_as_mail_link',
			'label'        => 'Link this page in mail',
			'section'      => 'signatureSheet',
			'type'         => 'wpPage',
			'supplemental' => 'You should include [demovox_form] on that page to show the signature sheet. ',
		],
		[
			'uid'          => 'show_pdf',
			'label'        => 'Show signature sheet',
			'section'      => 'signatureSheet',
			'type'         => 'checkbox',
			'supplemental' => 'Show signature sheet PDF on the success page in an iFrame',
		],
		[
			'uid'          => 'fontsize',
			'label'        => 'Font size',
			'section'      => 'signatureSheetFields',
			'type'         => 'number',
			'placeholder'  => '',
			'helper'       => 'pt',
			'supplemental' => 'Font size on the signature sheet',
			'default'      => '12',
		],
		[
			'uid'          => 'swiss_abroad_fontsize',
			'label'        => 'Swiss abroad font size',
			'section'      => 'signatureSheetFields',
			'type'         => 'number',
			'placeholder'  => '',
			'helper'       => 'pt',
			'supplemental' => 'Font size for the address of swiss abroad',
			'class'        => 'showOnSwissAbroadChecked',
			'default'      => '11',
		],
		[
			'uid'          => 'field_qr_mode',
			'label'        => 'QR mode',
			'section'      => 'signatureSheetFields',
			'type'         => 'select',
			'options'      => [
				'disabled'       => 'Disabled',
				'hashids'        => 'Hashids (5 chars alphanumeric. GMP or BC Math required)',
				'BaseIntEncoder' => 'BaseIntEncoder (1-4 chars alphanumeric, no obfuscation, BC Math required)',
				'PseudoCrypt'    => 'PseudoCrypt (1-5 chars alphanumeric, confusable letters incl, BC Math required)',
				'id'             => 'ID (no obfuscation)',
			],
			'supplemental' => 'Don\'t change algorithm on a productive system! Obfuscation helps not to confuse numbers when entering them manually. PHP modules <a href="https://secure.php.net/manual/en/book.gmp.php" target="_blank">GMP</a> and <a href="https://secure.php.net/manual/en/book.bc.php" target="_blank">BC Math</a>',
			'default'      => 'disabled',
		],
		[
			'uid'          => 'encrypt_signees',
			'label'        => 'Encrypt signee details',
			'section'      => 'security',
			'type'         => 'select',
			'options'      => [
				'disabled' => 'Disabled',
				'1'        => 'Yes, php-encryption (requires at least PHP 5.6 and OpenSSL 1.0.1)',
			],
			'default'      => 'disabled',
			'supplemental' => 'Encrypt personal details, only affects new entries. DEMOVOX_ENC_KEY has to be set in wp-config.php',
		],
		[
			'uid'     => 'save_ip',
			'label'   => 'Save client IP address',
			'section' => 'security',
			'type'    => 'checkbox',
			'default' => 0,
			'class'   => 'showOnEncrypt',
		],
		[
			'uid'     => 'optin_mode',
			'label'   => 'Opt-in mode',
			'section' => 'optIn',
			'type'    => 'select',
			'options' => [
				'disabled'  => 'Disabled',
				'optIn'     => 'Opt-in',
				'optInChk'  => 'Opt-in, enabled by default (maybe illegal)',
				'optOut'    => 'Opt-out (maybe illegal)',
				'optOutChk' => 'Opt-out, enabled by default',
			],
			'default' => 'optIn',
		],
		[
			'uid'     => 'optin_position',
			'label'   => 'Show on form page number',
			'section' => 'optIn',
			'type'    => 'select',
			'options' => [
				'1' => '1',
				'2' => '2',
			],
			'default' => '2',
			'c'       => 'hideOnOptinDisabled',
		],
		[
			'uid'          => 'use_page_as_optin_link',
			'label'        => 'Link this page as opt-in page',
			'section'      => 'optIn',
			'type'         => 'wpPage',
			'supplemental' => 'You should include the text [demovox_optin] on selected page to show the signature sheet. '
				. 'Link is generated only one at on sign-up.',
			'class'        => 'hideOnOptinDisabled',
		],
		[
			'uid'     => 'mail_method',
			'label'   => 'Mail engine',
			'section' => 'mailConfig',
			'type'    => 'select',
			'options' => [
				'mail'     => 'PHP mail()',
				'wp_mail'  => 'Wordpress wp_mail',
				'smtp'     => 'SMTP',
				'sendmail' => 'sendmail',
			],
			'default' => 'mail',
		],
		[
			'uid'     => 'mail_smtp_host',
			'label'   => 'SMTP server address',
			'section' => 'mailConfig',
			'type'    => 'text',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'     => 'mail_smtp_port',
			'label'   => 'SMTP server port',
			'section' => 'mailConfig',
			'type'    => 'number',
			'default' => '465',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'     => 'mail_smtp_authtype',
			'label'   => 'SMTP auth type',
			'section' => 'mailConfig',
			'type'    => 'select',
			'options' => [
				'none'     => 'No auth required',
				'CRAM-MD5' => 'CRAM-MD5',
				'LOGIN'    => 'LOGIN',
				'PLAIN'    => 'PLAIN',
			],
			'default' => 'PLAIN',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'     => 'mail_smtp_user',
			'label'   => 'SMTP auth username',
			'section' => 'mailConfig',
			'type'    => 'text',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'     => 'mail_smtp_password',
			'label'   => 'SMTP auth password',
			'section' => 'mailConfig',
			'type'    => 'text',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'     => 'mail_smtp_security',
			'label'   => 'SMTP server security',
			'section' => 'mailConfig',
			'type'    => 'select',
			'options' => [
				'ssl' => 'SSL',
				'tls' => 'TLS',
				''    => 'None',
			],
			'default' => 'SSL',
			'class'   => 'showOnMethodSmtp',
		],
		[
			'uid'          => 'mail_max_per_execution',
			'label'        => 'Send up to x emails per cron execution',
			'section'      => 'mailConfig',
			'type'         => 'number',
			'default'      => 300,
			'supplemental' => 'Send up to x reminder emails per cron execution',
		],
		[
			'uid'          => 'cron_max_load',
			'label'        => 'Cron max server load %',
			'section'      => 'cron',
			'type'         => 'number',
			'default'      => 80,
			'supplemental' => 'When server load is higher than this value in percent, crons won\'t be started (Not supported by Windows)',
		],
		[
			'uid'          => 'cron_cores',
			'label'        => 'Server cores',
			'section'      => 'cron',
			'type'         => 'number',
			'default'      => 1,
			'supplemental' => 'Required to recognize correct load',
		],
		[
			'uid'     => 'api_address_key',
			'label'   => 'Key addressinformation',
			'section' => 'api',
			'type'    => 'text',
		],
		[
			'uid'     => 'api_address_url',
			'label'   => 'URL addressinformation',
			'section' => 'api',
			'type'    => 'text',
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_city_input',
			'label'   => 'Allow custom city name',
			'section' => 'api',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_gde_input',
			'label'   => 'Allow custom commune name',
			'section' => 'api',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_gde_select',
			'label'   => 'Allow custom commune selection',
			'section' => 'api',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_export_key',
			'label'   => 'Key data export',
			'section' => 'api',
			'type'    => 'text',
		],
		[
			'uid'     => 'api_export_url',
			'label'   => 'URL data export',
			'section' => 'api',
			'type'    => 'text',
			'class'   => 'showOnApiExport',
		],
		[
			'uid'          => 'drop_config_on_uninstall',
			'label'        => 'Drop Config on uninstall',
			'section'      => 'danger',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'Drops configuration when this plugin is uninstalled',
		],
		[
			'uid'          => 'drop_table_on_uninstall',
			'label'        => 'Drop signatures on uninstall',
			'section'      => 'danger',
			'type'         => 'checkbox',
			'default'      => 0,
			'supplemental' => 'Drops all signature information when this plugin is uninstalled!',
		],
	];

	public static function getFields()
	{
		if (self::$fieldsCache !== null) {
			return self::$fieldsCache;
		}
		$fields = self::$fields;
		$fields[] = [
			'uid'     => 'default_language',
			'label'   => 'Default language',
			'section' => 'base', // TODO: move to a better place
			'type'    => 'select',
			'options' => i18n::$languages,
			'default' => 'de',
		];
		$fields[] = [
			'uid'          => 'mail_reminder_enabled',
			'label'        => 'Mail reminder enabled',
			'section'      => 'mailText',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'If enabled later, reminders will also be sent for previous signees which did not receive the mail yet.<br/>You must also set the mailserver settings in the advanced settings.',
		];
		$fields[] = [
			'uid'          => 'mail_nl2br',
			'label'        => 'Newline to BR',
			'section'      => 'mailText',
			'type'         => 'checkbox',
			'default'      => 0,
			'supplemental' => 'Inserts HTML line breaks before all newlines in mail body',
		];
		foreach (i18n::$languages as $langId => $language) {
			// signatureSheet_LANG
			$fields[] = [
				'uid'     => 'signature_sheet_' . $langId,
				'label'   => $language,
				'section' => 'signatureSheetPdf',
				'type'    => 'wpMedia',
				'options' => 0,
			];

			// optIn
			$fields[] = [
				'uid'     => 'text_optin_' . $langId,
				'label'   => $language,
				'section' => 'optInText',
				'type'    => 'text',
				'class'   => 'hideOnOptinDisabled',
			];

			// signatureSheetFields_LANG
			$fields[] = [
				'uid'          => 'field_canton_' . $langId,
				'label'        => 'Canton',
				'section'      => 'signatureSheetFields_' . $langId,
				'type'         => 'pos_rot',
				'supplemental' => 'Position on the sign sheet "x-y" while y is measured from bottom to top',
				'defaultX'     => 100,
				'defaultY'     => 655,
			];
			$fields[] = [
				'uid'      => 'field_zip_' . $langId,
				'label'    => 'ZIP',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 210,
				'defaultY' => 655,
			];
			$fields[] = [
				'uid'      => 'field_commune_' . $langId,
				'label'    => 'Commune',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 260,
				'defaultY' => 655,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_day_' . $langId,
				'label'    => 'Birth date day',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 200,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_month_' . $langId,
				'label'    => 'Birth date month',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 218,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_year_' . $langId,
				'label'    => 'Birth date year',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 236,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_street_' . $langId,
				'label'    => 'Street',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 260,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'        => 'field_qr_img_' . $langId,
				'label'      => 'QR code image',
				'section'    => 'signatureSheetFields_' . $langId,
				'type'       => 'pos_rot',
				'class'      => 'showOnQr',
				'defaultX'   => 579,
				'defaultY'   => 370,
				'defaultRot' => 180,
			];
			$fields[] = [
				'uid'          => 'field_qr_img_size_' . $langId,
				'label'        => 'QR code image size',
				'section'      => 'signatureSheetFields_' . $langId,
				'type'         => 'number',
				'supplemental' => 'Size of one module in pixels',
				'default'      => 3,
				'class'        => 'showOnQr',
			];
			$fields[] = [
				'uid'        => 'field_qr_text_' . $langId,
				'label'      => 'QR code text',
				'section'    => 'signatureSheetFields_' . $langId,
				'type'       => 'pos_rot',
				'class'      => 'showOnQr',
				'defaultX'   => 558,
				'defaultY'   => 373,
				'defaultRot' => 180,
			];

			// mailText
			$fields[] = [
				'uid'     => 'mail_reminder_from_address_' . $langId,
				'label'   => 'Reminder from address',
				'section' => 'mailText_' . $langId,
				'type'    => 'text',
				'class'   => 'showOnMailReminderEnabled',
			];
			$fields[] = [
				'uid'     => 'mail_reminder_from_name_' . $langId,
				'label'   => 'Reminder from name',
				'section' => 'mailText_' . $langId,
				'type'    => 'text',
				'class'   => 'showOnMailReminderEnabled',
			];
			$fields[] = [
				'uid'          => 'text_mail_subj_' . $langId,
				'label'        => 'Reminder subject',
				'section'      => 'mailText_' . $langId,
				'type'         => 'text',
				'supplemental' => 'Available placeholders: {first_name}, {last_name}. This mail is sent to the signee after signing up.',
				'class'        => 'showOnMailReminderEnabled',
			];
			$fields[] = [
				'uid'          => 'text_mail_' . $langId,
				'label'        => 'Reminder content',
				'section'      => 'mailText_' . $langId,
				'type'         => 'textarea',
				'supplemental' => 'Available placeholders: {first_name}, {last_name}, {mail}, {link_pdf}, {link_optin}, {subject}. ',
				'class'        => 'showOnMailReminderEnabled',
			];
			/*
			$fields[] = [
				'uid'          => 'text_mail_reminder_subj_' . $langId,
				'label'        => 'Signature sheet mail',
				'section'      => 'mailText_' . $langId,
				'type'         => 'text',
				'supplemental' => 'Mail is sent to visitor if he did not send the signature sheet',
			];
			$fields[] = [
				'uid'     => 'text_mail_reminder_' . $langId,
				'label'   => 'Reminder mail',
				'section' => 'mailText_' . $langId,
				'type'    => 'wysiwyg',
			];
			*/
		}
		if (WP_DEBUG) {
			$fields[] = [
				'uid'          => 'redirect_http_to_https',
				'label'        => 'Redirect clients to secure HTTPS',
				'section'      => 'danger',
				'default'      => 1,
				'type'         => 'select',
				'options'      => [
					'1'  => 'Enabled',
					'2'  => 'Enabled',
					'3'  => 'Enabled',
					'4'  => 'Enabled',
					'5'  => 'Enabled',
					'6'  => 'Enabled',
					'7'  => 'Enabled',
					'8'  => 'Enabled',
					'9'  => 'Enabled',
					'10' => 'Enabled',
					'11' => 'Enabled',
					'12' => 'Enabled',
					'13' => 'Enabled',
					'14' => 'Enabled',
					'15' => 'Enabled',
					'16' => 'Enabled',
					'17' => 'Enabled',
					'18' => 'Enabled',
					'19' => 'Enabled',
					'20' => 'Enabled',
					'21' => 'Enabled',
					'22' => 'Enabled',
					'23' => 'Enabled',
					'24' => 'Enabled',
					'0'  => 'Disabled - ONLY for tests on non-productive systems!',
					'25' => 'Enabled',
					'26' => 'Enabled',
					'27' => 'Enabled',
					'28' => 'Enabled',
					'29' => 'Enabled',
					'30' => 'Enabled',
				],
				'supplemental' => 'DO NOT DISABLE this option on a productive system. Automatically redirect clients to encrypted connection.',
			];
		}
		self::$fieldsCache = $fields;
		return $fields;
	}
}