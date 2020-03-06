<?php

namespace Demovox;

class ConfigVars
{
	static private $fieldsCache = null;
	static public $sections = [
		'base'                 => [
			'title' => 'Signature counter',
			'page'  => 'demovoxFields0',
		],
		'enabledLanguages'     => [
			'title' => 'demovox option languages',
			'page'  => 'demovoxFields0',
			'sub'   => 'Enable languages for the demovox option translations like signature sheets, mails and opt-in text.<br/>'
					   . ' The frontend language, like the translation of the form input titles, is affected by the WordPress option'
					   . ' <b>Site Language</b> under <b>General Settings</b>.'
					   . ' Another way is to set the language by an internationalisation plugin to allow multiple languages for the client.'
					   . ' This is currently tested with <a href="https://wpml.org/" target="_blank">WPML</a>, with the WPML option'
					   . ' <a href="https://wpml.org/documentation/getting-started-guide/language-setup/enabling-language-cookie-to-support-ajax-filtering/" target="_blank">'
					   . 'Language filtering for AJAX operations</a> enabled.',
		],
		'optIn'                => [
			'title' => 'Opt-in',
			'page'  => 'demovoxFields1',
		],
		'optInText'            => [
			'title' => 'Checkbox label',
			'page'  => 'demovoxFields1',
			'sub'   => 'Text beside the checkbox, specify as exactly as possible how the data will be used. <code>&lt;a&gt;</code> tags can'
					   . ' be used.<br/> If you use the opt-out mode, invert the description logic accordingly. '
					   . '<br/><br/>Example: <code>Mit dem Unterzeichnen akzeptiere ich die &lt;a href="https://beispiel.ch/datenschutz-bestimmungen" target="_blank"&gt;Datenschutzbestimmungen&lt;/a&gt;.</code>',
			'class'   => 'hideOnOptinDisabled',
			'addPre'  => '<div class="hideOnOptinDisabled">',
			'addPost' => '</div>',
		],
		'signatureSheet'       => [
			'title' => 'Signature sheet',
			'page'  => 'demovoxFields2',
			'sub'   => 'This page usually shows the link for the PDF download. When you change a page, already signed up users will still use the old previously configured.',
		],
		'swiss_abroad'         => [
			'title' => 'Swiss Abroad',
			'page'  => 'demovoxFields2',
			'sub'   => 'Allow swiss abroad people to sign.',
		],
		'local_initiative'     => [
			'title' => 'Local initiative',
			'page'  => 'demovoxFields2',
			'sub'   => 'Restrict initiative to a local area by redirecting other visitors to another success page.'
					   . ' Disables reminder mails and ignores signature in the signature counter.'
					   . ' Requires "Success page redirect" to be enabled.',
		],
		'signatureSheetPdf'    => [
			'title' => 'Signature sheet PDF',
			'page'  => 'demovoxFields3',
			'sub'   => 'Upload and select the signature sheet. If you use language specific domains on your page, adapt the paths accordingly.',
		],
		'signatureSheetFields' => [
			'title' => 'Signature sheet fields',
			'page'  => 'demovoxFields3',
			'sub'   => 'Fields on the signature sheet',
		],
		'mailBase'             => [
			'title' => 'Email settings',
			'page'  => 'demovoxFields4',
			'sub'   => 'You must also set the mail server settings in the advanced settings. To send test mails or to make sure the mail'
					   . ' crons are executed, take a look at the <b>System info</b> page.',
		],
		'mailSender'           => [
			'title' => 'Email sender',
			'page'  => 'demovoxFields4',
		],
		'mailTasks'             => [
			'title' => 'Email tasks',
			'page'  => 'demovoxFields4',
			'sub'   => 'Remember the <code>{&hellip;_link}</code> placeholders only contain the URL.'
					   .' Therefore you might want to use <code>&lt;a&gt;</code>-tags to create a link.',
		],
		'api_address'          => [
			'title' => 'Address lookup API',
			'page'  => 'demovoxFields5',
			'sub'   => 'Lookup API for the address information, used in the address form for autocompletion and commune identification. '
					   . 'Check <a href="https://demovox.ch/" target="_blank">documentation on demovox.ch</a> if you want to use our service.',
		],
		'api_export'           => [
			'title' => 'Export API',
			'page'  => 'demovoxFields5',
			'sub'   => 'Used to export sign-up data to a REST API of a CRM (server-side based submission, HTTPS required!).',
		],
		'security'             => [
			'title' => 'Security',
			'page'  => 'demovoxFields6',
		],
		'mailConfig'           => [
			'title' => 'Email engine / server',
			'page'  => 'demovoxFields6',
			'sub'   => 'To send test mails, take a look at the System info page.',
		],
		'cron'                 => [
			'title' => 'Cron',
			'page'  => 'demovoxFields6',
			'sub'   => 'To make sure the mail crons are executed, take a look at the System info page.',
		],
		'analytics'            => [
			'title' => 'Analytics',
			'page'  => 'demovoxFields6',
		],
		'danger'               => [
			'title' => 'Danger area',
			'page'  => 'demovoxFields6',
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
			'uid'     => 'count_thousands_sep',
			'label'   => 'Thousands separator on signature count',
			'section' => 'base',
			'type'    => 'text',
			'default' => "'",
		],
		[
			'uid'          => 'use_page_as_success',
			'label'        => 'Success page redirect',
			'supplemental' => 'Replace the user form by ajax with the signature sheet or redirect user to this page after successfully filling out the form.'
							  . ' You might want to use the same page as set for "Link this page in mails" and you should include <code>[demovox_form]</code> on that page to show the signature sheet. ',
			'section'      => 'signatureSheet',
			'type'         => 'wpPage',
			'optionNone'   => '[No, show on current page]',
		],
		[
			'uid'          => 'use_page_as_mail_link',
			'label'        => 'Link this page in mails',
			'section'      => 'signatureSheet',
			'type'         => 'wpPage',
			'supplemental' => 'You should include <code>[demovox_form]</code> on that page to show the signature sheet.'
							  . ' This setting is used for the link in mails as the placeholder <code>{link_pdf}</code>.',
		],
		[
			'uid'          => 'download_pdf',
			'label'        => 'Download button',
			'section'      => 'signatureSheet',
			'type'         => 'checkbox',
			'supplemental' => 'Show button to download signature sheet on the on the success page',
			'default'      => 1,
		],
		[
			'uid'          => 'print_pdf',
			'label'        => 'Print button',
			'section'      => 'signatureSheet',
			'type'         => 'checkbox',
			'supplemental' => 'Show print button  on the success page. Not supported by Firefox, sheet will be opened in a new window instead.',
		],
		[
			'uid'          => 'show_pdf',
			'label'        => 'Show signature sheet',
			'section'      => 'signatureSheet',
			'type'         => 'checkbox',
			'supplemental' => 'Show signature sheet PDF on the success page in an iFrame',
		],
		[
			'uid'          => 'swiss_abroad_allow',
			'label'        => 'Swiss abroad',
			'section'      => 'swiss_abroad',
			'type'         => 'checkbox',
			'supplemental' => 'Show a country selection for swiss abroad to sign the initiative',
			'default'      => 1,
		],
		[
			'uid'          => 'swiss_abroad_redirect',
			'label'        => 'Success page for swiss abroad',
			'section'      => 'swiss_abroad',
			'type'         => 'wpPage',
			'optionNone'   => '[Disabled]',
			'supplemental' => 'Redirect user to a different page if he has a swiss abroad address as you might want to add special'
							  . ' instructions. You should include <code>[demovox_form]</code> on that page to show the signature sheet.'
							  . ' This setting is also used for the link in mails as the placeholder </code>{link_pdf}</code>. '
							  . ' Requires both "Success page redirect" and "Swiss abroad" to be enabled.',
			'class'        => 'showOnRedirect',
		],
		[
			'uid'          => 'local_initiative_mode',
			'label'        => 'Restriction mode',
			'section'      => 'local_initiative',
			'default'      => 'disabled',
			'type'         => 'select',
			'options'      => [
				'disabled' => 'Disabled',
				'canton'   => 'Canton',
				'commune'  => 'Commune',
			],
			'supplemental' => 'Commune requires Address lookup API for the address information to be set up first (see "advanced" tab).',
			'class'        => 'showOnRedirect',
		],
		[
			'uid'          => 'swiss_abroad_allow',
			'label'        => 'Swiss abroad',
			'section'      => 'signatureSheetPdf',
			'type'         => 'checkbox',
			'supplemental' => 'Needed to show/hide "Swiss abroad font size"',
			'default'      => 1,
			'class'        => 'hidden',
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
				'hashids'        => 'Hashids (5 chars alphanumeric. PHP 7.1.3 and module GMP or BC Math required)',
				'BaseIntEncoder' => 'BaseIntEncoder (1-4 chars alphanumeric, no obfuscation, BC Math required)',
				'PseudoCrypt'    => 'PseudoCrypt (1-5 chars alphanumeric, confusable letters incl, BC Math required)',
				'id'             => 'ID (no obfuscation)',
			],
			'supplemental' => 'Don\'t change algorithm on a productive system! The mode <b>Hashids</b> is recommended, obfuscation helps'
							  . ' not to confuse numbers when entering them manually.'
							  . '<br/>Information about required PHP modules:'
							  . ' <a href="https://secure.php.net/manual/en/book.gmp.php" target="_blank">GMP</a> and'
							  . ' <a href="https://secure.php.net/manual/en/book.bc.php" target="_blank">BC Math</a>.',
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
			'supplemental' => 'Recommended! Encrypt personal details, only affects new entries. <code>DEMOVOX_ENC_KEY</code> and <code>DEMOVOX_HASH_KEY</code> have to be set in wp-config.php (see <b>System info</b>). '
							  . 'Protects against DB data theft like SQL injections or direct database access by a intruder, but not on file system access. ',
		],
		[
			'uid'     => 'save_ip',
			'label'   => 'Save client IP address',
			'section' => 'security',
			'type'    => 'checkbox',
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
				'optOutChk' => 'Opt-out',
				'optInChk'  => 'Opt-in, enabled by default (not recommended)',
				'optOut'    => 'Opt-out, disabled by default (not recommended)',
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
			'class'   => 'hideOnOptinDisabled',
		],
		[
			'uid'          => 'use_page_as_optin_link',
			'label'        => 'Link this page as opt-in page',
			'section'      => 'optIn',
			'type'         => 'wpPage',
			'supplemental' => 'This page can be linked in mails as opt-in edit page with theplaceholder <code>{link_optin}</code>. '
							  . 'On this page, you should include the shortcode <code>[demovox_optin]</code> to show the opt-in edit form. '
							  . 'When you change this setting, already signed up users will still use the old page.',
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
			'uid'     => 'mail_max_per_execution',
			'label'   => 'Send up to x emails per cron execution',
			'section' => 'mailConfig',
			'type'    => 'number',
			'default' => 300,
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
			'uid'     => 'api_address_url',
			'label'   => 'URL addressinformation',
			'section' => 'api_address',
			'type'    => 'text',
		],
		[
			'uid'     => 'api_address_key',
			'label'   => 'Key',
			'section' => 'api_address',
			'type'    => 'text',
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_city_input',
			'label'   => 'Allow custom city name',
			'section' => 'api_address',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_gde_input',
			'label'   => 'Allow custom commune name',
			'section' => 'api_address',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'     => 'api_address_gde_select',
			'label'   => 'Allow custom commune selection',
			'section' => 'api_address',
			'type'    => 'checkbox',
			'default' => 1,
			'class'   => 'showOnApiAddress',
		],
		[
			'uid'          => 'api_export_url',
			'label'        => 'API URL',
			'section'      => 'api_export',
			'type'         => 'text',
			'default'      => 'https://',
			'supplemental' => 'URL of a HTTPS REST API to send the signatures to. Ex: "https://server.ch/api/rest/"',
		],
		[
			'uid'          => 'api_export_data',
			'label'        => 'Export Data (JSON payload)',
			'section'      => 'api_export',
			'type'         => 'textarea',
			'class'        => 'showOnApiExport',
			'default'      => '{"firstname": "{first_name}", "api_key": "X8ZoPz3G2UxApfYpAfjE"}',
			'supplemental' => 'JSON which will be used to generate the POST data payload for to the REST API.'
							  . '<br/>Avaiblable placeholders: <code>{language}</code>, <code>{ip_address}</code>,'
							  . ' <code>{first_name}</code>, <code>{last_name}</code> <code>{birth_date}</code>, <code>{mail}</code>,'
							  . ' <code>{phone}</code>, <code>{country}</code>, <code>{street}</code>, <code>{street_no}</code>,'
							  . ' <code>{zip}</code>, <code>{city}</code>, <code>{gde_no}</code> <code>{gde_zip}</code>,'
							  . ' <code>{gde_name}</code>, <code>{gde_canton}</code>, <code>{is_optin}</code>,'
							  . ' <code>{creation_date}</code>, <code>{source}</code>',
		],
		[
			'uid'     => 'api_export_max_per_execution',
			'label'   => 'Send upto x rows per cron execution',
			'section' => 'api_export',
			'type'    => 'number',
			'class'   => 'showOnApiExport',
			'default' => 300,
		],
		[
			'uid'          => 'api_export_no_optin',
			'label'        => 'Optin not required',
			'section'      => 'api_export',
			'type'         => 'checkbox',
			'class'        => 'showOnApiExport',
			'default'      => 0,
			'supplemental' => 'Also export signatures without optin',
		],
		[
			'uid'          => 'analytics_matomo',
			'label'        => 'Matomo',
			'section'      => 'analytics',
			'type'         => 'checkbox',
			'default'      => 0,
			'supplemental' => 'Send tracking events to a Matomo script, which has to be embedded on the website',
		],
		[
			'uid'          => 'form_ajax_submit',
			'label'        => 'AJAX form submission',
			'section'      => 'danger',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'Use AJAX for form submission by default (recommended)',
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
			'uid'          => 'drop_tables_on_uninstall',
			'label'        => 'Drop signatures on uninstall',
			'section'      => 'danger',
			'type'         => 'checkbox',
			'supplemental' => 'Drops all signature information when this plugin is uninstalled!',
		],
	];

	/**
	 * Try to avoid this due to poor performance
	 *
	 * @param $id
	 *
	 * @return mixed|null
	 */
	public static function getField($id)
	{
		$fields = ConfigVars::getFields();
		$key    = array_search($id, array_column($fields, 'uid'));
		if ($key === false) {
			Core::logMessage('Option field "' . $id . '" does not exist.');
			return null;
		}
		$field = $fields[$key];
		return $field;
	}

	/**
	 * @return array|null
	 */
	public static function getFields()
	{
		if (self::$fieldsCache !== null) {
			return self::$fieldsCache;
		}
		$fields        = self::$fields;
		$fields[]      = [
			'uid'          => 'mail_confirmation_enabled',
			'label'        => 'Confirmation mail enabled',
			'section'      => 'mailTasks',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'This mail is sent to the signee, just after signing up. If this option is enabled after people have already signed up, confirmations will also be sent for those who did not receive any mail yet.',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_sheet_enabled',
			'label'        => 'Sheet reminder mail enabled',
			'section'      => 'mailTasks',
			'type'         => 'checkbox',
			'default'      => 0,
			'supplemental' => 'Send a reminder to signees which didn\'t send their signature sheets. To use this function, you must regularly import the received signature sheets.',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_sheet_min_age',
			'label'        => 'Sheet reminder - minimum signature age',
			'section'      => 'mailTasks',
			'type'         => 'number',
			'default'      => 30,
			'supplemental' => 'Minimum age of a signature before a sheet reminder is sent.',
			'class'        => 'showOnMailRemindSheetEnabled',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_signup_enabled',
			'label'        => 'Sign-up reminder mail enabled',
			'section'      => 'mailTasks',
			'type'         => 'checkbox',
			'default'      => 0,
			'supplemental' => 'Send a reminder to signees which didn\'t finish filling the sign-up form.',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_signup_min_age',
			'label'        => 'Sign-up reminder - Minimum signature age',
			'section'      => 'mailTasks',
			'type'         => 'number',
			'default'      => 5,
			'supplemental' => 'Minimum age of a signature before a form reminder is sent.',
			'class'        => 'showOnMailRemindSignupEnabled',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_max_date',
			'label'        => 'Last reminder date',
			'section'      => 'mailBase',
			'type'         => 'date',
			'default'      => date('d.m.Y'),
			'supplemental' => 'Stop sending reminders after this date (format: DD.MM.YYYY). Applies to both <b>sheet reminder</b> and <b>sign-up reminder</b>.',
		];
		$fields[]      = [
			'uid'          => 'mail_remind_dedup',
			'label'        => 'Only send one reminder per mail adress',
			'section'      => 'mailBase',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'Might weaken email address encryption security. Applies to both <b>sheet reminder</b> and <b>sign-up reminder</b>.',
		];
		$fields[]      = [
			'uid'          => 'mail_nl2br',
			'label'        => 'Newline to BR',
			'section'      => 'mailBase',
			'type'         => 'checkbox',
			'default'      => 1,
			'supplemental' => 'Inserts HTML line breaks before all newlines in mail body. Don\'t activate this if you insert the mail body in HTML anyway.',
		];
		$glueLang      = Config::GLUE_LANG;
		$wpMailAddress = get_bloginfo('admin_email');
		$wpMailName    = get_bloginfo('name');

		foreach (i18n::getLangs() as $langId => $language) {
			$langEnabled = !!self::getConfigValue('is_language_enabled' . $glueLang . $langId, null, true);
			$class       = $langEnabled ? '' : ' hidden';
			$glueLangId  = $glueLang . $langId;

			// language
			$fields[] = [
				'uid'     => 'is_language_enabled' . $glueLangId,
				'label'   => $language,
				'section' => 'enabledLanguages',
				'type'    => 'checkbox',
				'default' => 1,
			];

			// signatureSheet_LANG
			$fields[] = [
				'uid'     => 'signature_sheet' . $glueLangId,
				'label'   => $language,
				'section' => 'signatureSheetPdf',
				'type'    => 'wpMedia',
				'options' => 0,
				'class'   => $class,
			];

			// optIn
			$fields[] = [
				'uid'     => 'text_optin' . $glueLangId,
				'label'   => $language,
				'section' => 'optInText',
				'type'    => 'text',
				'class'   => $class,
			];

			// signatureSheetFields_LANG
			$fields[] = [
				'uid'          => 'field_canton' . $glueLangId,
				'label'        => 'Canton',
				'section'      => 'signatureSheetFields_' . $langId,
				'type'         => 'pos_rot',
				'supplemental' => 'Position on the sign sheet "x-y" while y is measured from bottom to top',
				'defaultX'     => 100,
				'defaultY'     => 655,
			];
			$fields[] = [
				'uid'      => 'field_zip' . $glueLangId,
				'label'    => 'ZIP',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 210,
				'defaultY' => 655,
			];
			$fields[] = [
				'uid'      => 'field_commune' . $glueLangId,
				'label'    => 'Commune',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 260,
				'defaultY' => 655,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_day' . $glueLangId,
				'label'    => 'Birth date day',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 200,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_month' . $glueLangId,
				'label'    => 'Birth date month',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 218,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_birthdate_year' . $glueLangId,
				'label'    => 'Birth date year',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 236,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'      => 'field_street' . $glueLangId,
				'label'    => 'Street',
				'section'  => 'signatureSheetFields_' . $langId,
				'type'     => 'pos_rot',
				'defaultX' => 260,
				'defaultY' => 617,
			];
			$fields[] = [
				'uid'        => 'field_qr_img' . $glueLangId,
				'label'      => 'QR code image',
				'section'    => 'signatureSheetFields_' . $langId,
				'type'       => 'pos_rot',
				'defaultX'   => 579,
				'defaultY'   => 370,
				'defaultRot' => 180,
				'class'      => 'showOnQr',
			];
			$fields[] = [
				'uid'          => 'field_qr_img_size' . $glueLangId,
				'label'        => 'QR code image size',
				'section'      => 'signatureSheetFields_' . $langId,
				'type'         => 'number',
				'supplemental' => 'Size of one module in pixels',
				'default'      => 3,
				'class'        => 'showOnQr',
			];
			$fields[] = [
				'uid'        => 'field_qr_text' . $glueLangId,
				'label'      => 'QR code text',
				'section'    => 'signatureSheetFields_' . $langId,
				'type'       => 'pos_rot',
				'defaultX'   => 558,
				'defaultY'   => 373,
				'defaultRot' => 180,
				'class'      => 'showOnQr',
			];

			// Mail sender
			$fields[] = [
				'uid'     => 'mail_from_name' . $glueLangId,
				'label'   => $language . '<br/>From name',
				'section' => 'mailSender',
				'type'    => 'text',
				'default' => $wpMailName,
				'class'   => $class,
			];
			$fields[] = [
				'uid'     => 'mail_from_address' . $glueLangId,
				'label'   => 'From address',
				'section' => 'mailSender',
				'type'    => 'text',
				'default' => $wpMailAddress,
				'class'   => $class,
			];

			// Confirmation mail
			$fields[] = [
				'uid'          => 'mail_confirm_subj' . $glueLangId,
				'label'        => 'Subject',
				'section'      => 'mailConfirm_' . $langId,
				'type'         => 'text',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>.',
			];
			$fields[] = [
				'uid'          => 'mail_confirm_body' . $glueLangId,
				'label'        => 'Body',
				'section'      => 'mailConfirm_' . $langId,
				'type'         => 'textarea',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>, <code>{mail}</code>,'
								  . ' <code>{link_pdf}</code>, <code>{link_optin}</code>, <code>{subject}</code>.',
			];

			$fields[] = [
				'uid'          => 'mail_remind_sheet_subj' . $glueLangId,
				'label'        => 'Subject',
				'section'      => 'mailRemindSheet_' . $langId,
				'type'         => 'text',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>. This mail is sent to the signee after signing up.',
			];
			$fields[] = [
				'uid'          => 'mail_remind_sheet_body' . $glueLangId,
				'label'        => 'Body',
				'section'      => 'mailRemindSheet_' . $langId,
				'type'         => 'textarea',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>, <code>{mail}</code>, <code>{link_pdf}</code>, <code>{link_optin}</code>, <code>{subject}</code>.',
			];

			$fields[] = [
				'uid'          => 'mail_remind_signup_subj' . $glueLangId,
				'label'        => 'Subject',
				'section'      => 'mailRemindSignup_' . $langId,
				'type'         => 'text',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>. This mail is sent to the signee after signing up.',
			];
			$fields[] = [
				'uid'          => 'mail_remind_signup_body' . $glueLangId,
				'label'        => 'Body',
				'section'      => 'mailRemindSignup_' . $langId,
				'type'         => 'textarea',
				'supplemental' => 'Available placeholders: <code>{first_name}</code>, <code>{last_name}</code>, <code>{mail}</code>, <code>{link_pdf}</code>, <code>{link_optin}</code>, <code>{subject}</code>. ',
			];
		}
		$cantons     = i18n::$cantons;
		$cantons[''] = '[Please select]';
		$fields[]    = [
			'uid'     => 'local_initiative_canton',
			'label'   => 'Restrict on canton',
			'section' => 'local_initiative',
			'default' => 'disabled',
			'type'    => 'select',
			'options' => $cantons,
			'class'   => 'showOnLocalInitiativeCanton',
		];
		$fields[]    = [
			'uid'          => 'local_initiative_commune',
			'label'        => 'Restrict on commune',
			'section'      => 'local_initiative',
			'default'      => 'disabled',
			'type'         => 'number',
			'supplemental' => 'Commune ID from API',
			'class'        => 'showOnLocalInitiativeCommune',
		];
		$fields[]    = [
			'uid'          => 'local_initiative_error_redirect',
			'label'        => 'Success page for disallowed visitors',
			'section'      => 'local_initiative',
			'type'         => 'wpPage',
			'optionNone'   => '[Please select]',
			'supplemental' => 'Redirect user to this page if he has an address outside the allowed area.',
			'class'        => 'showOnLocalInitiative',
		];
		$fields[]    = [
			'uid'          => 'default_language',
			'label'        => 'Default language',
			'section'      => 'enabledLanguages',
			'type'         => 'select',
			'options'      => i18n::getLangs(),
			'default'      => 'de',
			'supplemental' => 'Fallback language if the WordPress frontend is not set to any of the enabled demovox languages',
		];
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

	/**
	 * @return array
	 */
	public static function getSections()
	{
		$sections = self::$sections;
		$glueLang = Config::GLUE_LANG;
		foreach (i18n::getLangs() as $langId => $language) {
			$langEnabled = !!self::getConfigValue('is_language_enabled' . $glueLang . $langId, null, true);

			$sections['signatureSheetFields_' . $langId] = [
				'title'   => 'Signature sheet field positions ' . $language,
				'page'    => 'demovoxFields3',
				'addPre'  => $langEnabled ? '' : '<div class="hidden">',
				'addPost' => '<br/><div id="preview-' . $langId . '">'
							 . '<input type="button" class="showPdf" data-lang="' . $langId . '" value="Show preview"/>'
							 . '<div class="demovox-pdf-error hidden alert alert-danger"></div>'
							 . '<div class="demovox-pdf-loading hidden">'
							 . __('Preparing your signature sheet, please wait...', 'demovox') . '</div>'
							 . '<div class="demovox-pdf-ok hidden"><iframe src="about:blank" class="pdf-iframe"></iframe></div>'
							 . '</div>'
							 . ($langEnabled ? '' : '</div>'),
			];
			$sections['mailConfirm_' . $langId]          = [
				'page'    => 'demovoxFields4',
				'title'   => $language . '<br/>Confirmation mail',
				'addPre'  => '<div class="showOnMailConfirmEnabled' . ($langEnabled ? '' : ' hidden') . '">',
				'addPost' => '</div>',
			];
			$sections['mailRemindSheet_' . $langId]      = [
				'title'   => $language . '<br/>Sheet reminder mail',
				'page'    => 'demovoxFields4',
				'addPre'  => '<div class="showOnMailRemindSheetEnabled' . ($langEnabled ? '' : ' hidden') . '">',
				'addPost' => '</div>',
			];
			$sections['mailRemindSignup_' . $langId]     = [
				'title'   => $language . '<br/>Sign-up reminder mail',
				'page'    => 'demovoxFields4',
				'addPre'  => '<div class="showOnMailRemindSignupEnabled' . ($langEnabled ? '' : ' hidden') . '">',
				'addPost' => '</div>',
			];
		}

		return $sections;
	}

	/**
	 * Access config values from this class without creating loops.
	 * Use Config::getValue() from other locations!
	 *
	 * @param $id      string
	 * @param $valPart null|string
	 * @param $default null|mixed Default value (ignore value in ConfigVars, for example to avoid function nesting)
	 *
	 * @return mixed Value set for the config.
	 */
	protected static function getConfigValue($id, $valPart = null, $default = null)
	{
		$fullId = $id . ($valPart ? config::GLUE_PART . $valPart : '');
		$value  = Core::getOption($fullId);
		if ($value !== false) {
			return $value;
		}
		return $default;
	}
}