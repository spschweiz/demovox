<?php

use Demovox\i18n;

$sections = [
	'base'                 => [
		'title' => 'Base settings',
		'page'  => 'demovoxFields0',
	],
	'signature_count'                 => [
		'title' => 'Signature counter',
		'page'  => 'demovoxFields0',
	],
	'enabledLanguages'     => [
		'title' => 'demovox option languages',
		'page'  => 'demovoxFields0',
		'sub'   => 'Enable languages for the demovox option translations like signature sheets, mails and opt-in text.<br/>'
			. ' The frontend language, like the translation of the form input titles, is affected by the WordPress option'
			. ' <b>Site Language</b> under <b>General Settings</b>.<br/>'
			. ' Another way is to set the language by an internationalisation plugin to allow multiple languages for the client.'
			. ' This is currently tested with <a href="https://wpml.org/" target="_blank">WPML</a>, with the WPML option'
			. ' <a href="https://wpml.org/documentation/getting-started-guide/language-setup/enabling-language-cookie-to-support-ajax-filtering/" target="_blank">'
			. 'Language filtering for AJAX operations</a> enabled. <a href="https://polylang.pro/">Polylang</a> doesn\'t'
			.' translate the generated URLs and therefore you must define them manually in the reminder templates.',
	],
	'form'                => [
		'title' => 'Form options',
		'page'  => 'demovoxFields1',
	],
	'optIn'                => [
		'title' => 'Opt-in',
		'page'  => 'demovoxFields1',
	],
	'optInText'            => [
		'title' => 'Opt-in checkbox label',
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
		'sub'   => 'Remember the <code>{link_&hellip;}</code> placeholders only contain the URL. '
			. 'Therefore you might want to use <code>&lt;a&gt;</code>-tags to create a link. <br/>'
			. 'Some translation plugins like Polylang do not translate those URLs correctly. '
			. 'As a orkaround, you can define them manually and use the <code>{guid}</code> placeholder to create the <code>sign</code>-parameter.',
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
];

$allLanguages = i18n::getLangs();
$enabledLanguages = i18n::getLangsEnabled();

foreach (i18n::getLangs() as $langId => $language) {
	$langEnabled = isset($enabledLanguages[$langId]);

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
$sections['mailServer'] = [
	'title' => 'Email engine / server',
	'page'  => 'demovoxFields4',
	'sub'   => 'To send test mails, take a look at the System info page.',
];

return $sections;