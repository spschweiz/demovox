<?php

$fields = [
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
		'label'   => 'Store client IP address',
		'section' => 'security',
		'type'    => 'checkbox',
		'class'   => 'showOnEncrypt',
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
		'uid'     => 'mail_max_per_execution',
		'label'   => 'Send up to x emails per cron execution',
		'section' => 'cron',
		'type'    => 'number',
		'default' => 300,
	],
	[
		'uid'          => 'mail_remind_dedup',
		'label'        => 'Mail deduplication',
		'section'      => 'cron',
		'type'         => 'checkbox',
		'default'      => 1,
		'supplemental' => 'Only send one reminder per mail address. Might weaken email address encryption security. Applies to both <b>sheet reminder</b> and <b>sign-up reminder</b>.',
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
		'uid'          => 'form_honeypot',
		'label'        => 'Spambot honeypot',
		'section'      => 'forms',
		'type'         => 'checkbox',
		'default'      => 1,
		'supplemental' => 'Add an invisible form field to block some spam bots (recommended)',
	],
	[
		'uid'          => 'form_ajax_submit',
		'label'        => 'AJAX form submission',
		'section'      => 'forms',
		'type'         => 'checkbox',
		'default'      => 1,
		'supplemental' => 'Use AJAX for form submission (recommended)',
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
	[
		'uid'          => 'settings_no_register',
		'label'        => 'Register settings only when needed',
		'section'      => 'danger',
		'type'         => 'checkbox',
		'supplemental' => 'Improve Wordpress performance by registering setting variables only when they are required for saving the values. This will provoke side effects when other plugins try to identify demovox settings like cleaners of unregistered settings.',
	],
];
if (WP_DEBUG) {
	$fields[] = [
		'uid'          => 'redirect_http_to_https',
		'label'        => 'Require clients to access via HTTPS',
		'section'      => 'danger',
		'default'      => 1,
		'type'         => 'select',
		'options'      => [
			'1'  => 'Enabled (default)',
			'0'  => 'Disabled - ONLY for tests on non-productive systems!',
		],
		'supplemental' => 'DO NOT DISABLE this option on a productive system. Disables personal data submission without an encrypted connection.',
	];
}

return $fields;