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
 * @author     SP Schweiz
 */
class Activator
{
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
		$dbs = ModelInfo::getDbServices();
		foreach ($dbs as $db) {
			$sql = $db->getTableDefinition();
			$updates = $db->createUpdateTable($sql);
		}
	}

	protected static function upgradeTables(): void
	{
		$dbSign     = new DbSignatures;
		$dbSignName = $dbSign->getTableName();
		if (!Db::query("SHOW TABLES LIKE '$dbSignName';")) {
			return;
		}
		$dbMail = new DbMails;
		$dbMailDdName = $dbMail->getTableName();
		if (Db::query("SHOW COLUMNS FROM `$dbMailDdName` LIKE 'mail'")) {
			// previous version was < 1.3.3
			$update = "ALTER TABLE $dbMailDdName CHANGE COLUMN mail mail_md5 CHAR(32);";
			Db::query($update);
		}
		if (Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'reminder_sent_date'")) {
			// previous version was < 2.1.3
			$update = "ALTER TABLE $dbSignName CHANGE COLUMN reminder_sent_date remind_sheet_sent_date datetime NULL, ";
			$update .= "ADD COLUMN remind_signup_sent_date datetime NULL AFTER sheet_received_date;";
			Db::query($update);
		}
		if (!Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'title'")) {
			// previous version was < 2.3
			$update = "ALTER TABLE $dbSignName ADD COLUMN title VARCHAR(10) NULL BEFORE first_name;";
			Db::query($update);
		}
		if (!Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'instance'")) {
			// previous version was < 3
			$update = "ALTER TABLE $dbSignName ADD COLUMN instance int UNSIGNED NOT NULL DEFAULT '0' AFTER ID;";
			$update .= "ALTER TABLE $dbSignName ALTER COLUMN instance DROP DEFAULT;";
			Db::query($update);
			$update = "ALTER TABLE $dbMailDdName ADD COLUMN instance int UNSIGNED NOT NULL DEFAULT '0' AFTER sign_ID;";
			$update .= "ALTER TABLE $dbMailDdName ALTER COLUMN instance DROP DEFAULT;";
			Db::query($update);
			$update = "ALTER TABLE $dbMailDdName DROP INDEX mail_index, ADD UNIQUE KEY mail_index (instance, mail_md5);";
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