<?php

namespace Demovox;

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class Activator
{
	private static $tableDefinition = '
          ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          guid CHAR(36) NOT NULL,
          serial CHAR(6) NULL,
          language CHAR(2) NOT NULL,
          ip_address CHAR(232) NULL,
          first_name VARCHAR(678) NOT NULL,
          last_name VARCHAR(678) NOT NULL,
          birth_date VARCHAR(188) NULL,
          mail VARCHAR(424) NOT NULL,
          phone VARCHAR(296) NULL,
          country CHAR(2) NULL,
          street VARCHAR(422) NULL,
          street_no VARCHAR(188) NULL,
          zip VARCHAR(200) NULL,
          city VARCHAR(296) NULL,
          gde_no VARCHAR(178) NULL,
          gde_zip VARCHAR(176) NULL,
          gde_name VARCHAR(258) NULL,
          gde_canton VARCHAR(172) NULL,
          is_optin TINYINT NULL,
          is_step2_done TINYINT DEFAULT 0 NOT NULL,
          is_mail_sent TINYINT DEFAULT 0 NOT NULL,
          is_sheet_received TINYINT DEFAULT 0 NOT NULL,
          is_reminder_sent TINYINT DEFAULT 0 NOT NULL,
          is_exported TINYINT DEFAULT 0 NOT NULL,
          is_encrypted TINYINT DEFAULT 0 NOT NULL,
          is_deleted TINYINT DEFAULT 0 NOT NULL,
          link_pdf VARCHAR(255) NOT NULL,
          link_optin VARCHAR(255) NOT NULL,
          creation_date DATETIME NOT NULL DEFAULT NOW(),
          edit_date DATETIME NULL,
          sheet_received_date DATETIME NULL,
          reminder_sent_date DATETIME NULL,
          source VARCHAR(127) NULL,
          PRIMARY KEY (ID),
          UNIQUE KEY guid_index (guid),
          INDEX creation_date_index (creation_date)
    ';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$update = DB::createUpdateTable(self::$tableDefinition);

		if (!wp_next_scheduled('demovox_send_mails')) {
			wp_schedule_event(time(), 'hourly', 'demovox_send_mails');
		}

		// Create pages
		$signatureSheetPageId = Config::getValue('signature_sheet_page_id');
		if (empty($signatureSheetPageId)) {
			$content = '<p>' . __('Almost there', 'demovox') . '</p>';
			$content .= '<p>' .
				__('Print the following PDF, then fill it with your name and signature and send it to us:', 'demovox') .
				'</p>';
			$content .= '[demovox_form]';
			$postData = [
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ping_status'  => get_option('default_ping_status'),
				'post_content' => $content,
				'post_excerpt' => '',
				'post_title'   => 'Signature sheet',
			];
			$signatureSheetPageId = wp_insert_post($postData);
			Core::setOption('signature_sheet_page_id', $signatureSheetPageId);
		}
		if (empty(Config::getValue('use_page_as_mail_link'))) {
			Core::setOption('use_page_as_mail_link', $signatureSheetPageId);
		}

		$optinPageId = Config::getValue('use_page_as_optin_link');
		if (empty($optinPageId)) {
			$content .= '[demovox_optin]';
			$post_data = [
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ping_status'  => get_option('default_ping_status'),
				'post_content' => $content,
				'post_excerpt' => '',
				'post_title'   => 'Opt-in',
			];
			$optinPageId = wp_insert_post($post_data);
			Core::setOption('use_page_as_optin_link', $optinPageId);
		}

		// create capabilities
		$role = get_role('super admin');
		if ($role) {
			$role->add_cap('demovox_overview');
			$role->add_cap('demovox_stats');
			$role->add_cap('demovox_import');
		}

		$role = get_role('administrator');
		$role->add_cap('demovox_overview');
		$role->add_cap('demovox_stats');
		$role->add_cap('demovox_import');

		$role = get_role('editor');
		$role->add_cap('demovox_overview');
		$role->add_cap('demovox_stats');
		$role->add_cap('demovox_import');
	}
}