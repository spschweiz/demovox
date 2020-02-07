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
 * @author     Fabian Horlacher / SP Schweiz
 */
class Activator
{
	private static $tableDefinitions = [
		Db::TABLE_SIGN  => '
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          guid char(36) NOT NULL,
          serial char(6) NULL,
          language char(2) NOT NULL,
          ip_address char(232) NULL,
          first_name varchar(678) NOT NULL,
          last_name varchar(678) NOT NULL,
          birth_date varchar(188) NULL,
          mail varchar(424) NOT NULL,
          phone varchar(296) NULL,
          country char(2) NULL,
          street varchar(422) NULL,
          street_no varchar(188) NULL,
          zip varchar(200) NULL,
          city varchar(296) NULL,
          gde_no varchar(178) NULL,
          gde_zip varchar(176) NULL,
          gde_name varchar(258) NULL,
          gde_canton varchar(172) NULL,
          is_optin tinyint(4) NULL,
          is_step2_done tinyint(4) DEFAULT 0 NOT NULL,
          is_outside_scope tinyint(4) DEFAULT 0 NOT NULL,
          is_sheet_received tinyint(4) DEFAULT 0 NOT NULL,
          is_exported tinyint(4) DEFAULT 0 NOT NULL,
          is_encrypted tinyint(4) DEFAULT 0 NOT NULL,
          is_deleted tinyint(4) DEFAULT 0 NOT NULL,
          state_confirm_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_sheet_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_signup_sent tinyint(4) DEFAULT 0 NOT NULL,
          link_success varchar(255) NULL,
          link_pdf varchar(255) NULL,
          link_optin varchar(255) NULL,
          creation_date datetime NOT NULL DEFAULT NOW(),
          edit_date datetime NULL,
          sheet_received_date datetime NULL,
          reminder_sent_date datetime NULL,
          source varchar(127) NULL,
          PRIMARY KEY (ID),
          UNIQUE KEY guid_index (guid),
          INDEX creation_date_index (creation_date)',
		Db::TABLE_MAILS => '
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          sign_ID bigint(20) UNSIGNED NOT NULL,
          mail_md5 char(32) NOT NULL,
          creation_date datetime NOT NULL,
          is_step2_done tinyint(4) DEFAULT 0 NOT NULL,
          is_sheet_received tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_sheet_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_signup_sent tinyint(4) DEFAULT 0 NOT NULL,
          PRIMARY KEY (ID),
          UNIQUE KEY sign_ID_index (sign_ID),
          UNIQUE KEY mail_index (mail_md5),
          INDEX creation_date_index (creation_date)',
	];

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		self::activateDb();

		ManageCron::activate();

		self::createPages();

		self::createCapabilities();

		Config::initDefaults();
	}

	/**
	 * @param $post Post to check if visible
	 *
	 * @return bool
	 */
	protected static function isPostVisible($post)
	{
		if (!empty($post)) {
			$post_info = get_post($post);
			if (!$post_info) {
				return false;
			}
			$status = $post_info->post_status;

			return $status !== 'trash';
		}
		return false;
	}

	public static function activateDb(): void
	{
		self::upgradeTables();
		self::createTables();
	}

	protected static function createTables(): void
	{
		foreach (self::$tableDefinitions as $tableName => $sql) {
			$updates = Db::createUpdateTable($sql, $tableName);
		}
	}

	protected static function upgradeTables(): void
	{
		$dbSign     = new DbSignatures;
		$dbSignName = $dbSign->getTableName();
		if (!Db::query("SHOW TABLES LIKE '$dbSignName';")) {
			return;
		}
		$dbMailDd = new DbMailDedup;
		$dbMailDdName = $dbMailDd->getTableName();
		if (Db::query("SHOW COLUMNS FROM `$dbMailDdName` LIKE 'mail'")) {
			// previous version was < 1.3.3
			$update = "ALTER TABLE $dbMailDdName CHANGE COLUMN mail mail_md5 CHAR(32);";
			Db::query($update);
		}
	}

	protected static function createPages(): void
	{
		$signatureSheetPageId = Core::getOption('signature_sheet_page_id');
		if (!self::isPostVisible($signatureSheetPageId)) {
			$content              = '<p>' . __('Almost there', 'demovox') . '</p>';
			$content              .= '<p>' .
									 __('Print the following PDF, then fill it with your name and signature and send it to us:', 'demovox')
									 .
									 '</p>';
			$content              .= '[demovox_form]';
			$postData             = [
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

		if (!self::isPostVisible($optinPageId)) {
			$content     .= 'Would you like to opt-in to or opt-out from our List?<br/>[demovox_optin]';
			$post_data   = [
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ping_status'  => get_option('default_ping_status'),
				'post_content' => $content,
				'post_excerpt' => '',
				'post_title'   => 'Opt-in',
			];
			$optinPageId = wp_insert_post($post_data);
			Config::setValue('use_page_as_optin_link', $optinPageId);
		}
	}

	protected static function createCapabilities(): void
	{
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