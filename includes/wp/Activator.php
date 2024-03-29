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

		Settings::initDefaults();
	}

	/**
	 * @param $post int Post to check if visible
	 *
	 * @return bool
	 */
	protected static function isPostVisible(int $post): bool
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
		self::createMissingTables();
		self::upgradeTables();
	}

	protected static function createMissingTables(): void
	{
		$dbs = ModelInfo::getDbServices();
		foreach ($dbs as $db) {
			$sql = $db->getTableDefinition();
			$updates = $db->createMissingTables($sql);
		}
	}

	protected static function upgradeTables(): void
	{
		$dbSign     = new DbSignatures;
		$dbSignName = $dbSign->getWpTableName();
		if (!Db::query("SHOW TABLES LIKE '$dbSignName';")) {
			return;
		}
		$dbMail = new DbMails;
		$dbMailDdName = $dbMail->getWpTableName();
		$dbCollection = new DbCollections;
		$dbCollectionName = $dbCollection->getWpTableName();
		if (Db::query("SHOW COLUMNS FROM `$dbMailDdName` LIKE 'mail'")) {
			// previous version was < 1.3.3
			$update = "ALTER TABLE $dbMailDdName CHANGE COLUMN mail mail_md5 CHAR(32);";
			self::update($update);
		}
		if (Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'reminder_sent_date'")) {
			// previous version was < 2.1.3
			$update = "ALTER TABLE $dbSignName CHANGE COLUMN reminder_sent_date remind_sheet_sent_date datetime NULL, ";
			$update .= "ADD COLUMN remind_signup_sent_date datetime NULL AFTER sheet_received_date;";
			self::update($update);
		}
		if (!Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'title'")) {
			// previous version was < 2.3
			$update = "ALTER TABLE $dbSignName ADD COLUMN title VARCHAR(10) NULL BEFORE first_name;";
			self::update($update);
		}
		if (!Db::query("SHOW COLUMNS FROM `$dbSignName` LIKE 'collection_ID'")) {
			// previous version was < 3
			// add "collection_ID" columns and migrate existing entries with value '0'
			$update = "ALTER TABLE $dbSignName ADD COLUMN collection_ID int UNSIGNED NOT NULL DEFAULT '1' AFTER ID;";
			self::update($update);
			$update = "ALTER TABLE $dbSignName ALTER COLUMN collection_ID DROP DEFAULT;";
			self::update($update);
			$update = "ALTER TABLE $dbMailDdName ADD COLUMN collection_ID int UNSIGNED NOT NULL DEFAULT '1' AFTER sign_ID;";
			self::update($update);
			$update = "ALTER TABLE $dbMailDdName ALTER COLUMN collection_ID DROP DEFAULT;";
			self::update($update);

			// create collections table
			$sql = $dbCollection->getTableDefinition();
			$dbCollection->createMissingTables($sql);

			// foreign keys & index
			$update = "ALTER TABLE $dbSignName ADD FOREIGN KEY (collection_ID) REFERENCES $dbCollectionName (ID);";
			$update .= "ALTER TABLE $dbMailDdName ADD FOREIGN KEY (collection_ID) REFERENCES $dbCollectionName (ID);";
			$update .= "ALTER TABLE $dbMailDdName DROP INDEX mail_index, ADD UNIQUE KEY mail_index (collection_ID, mail_md5);";
			self::update($update);

			// migrate collection specific settings
			$pluginDir = Core::getPluginDir();
			$fieldsDef = include($pluginDir . 'includes/helpers/SettingsVarsCollection/ConfigFields.php');
			foreach ($fieldsDef as $field) {
				$oldName = $field['uid'];
				$newName = '1' . Settings::GLUE_PART . $field['uid'];
				self::renameSetting($oldName, $newName);

				$fieldType = $field['type'] ?? null;
				switch ($fieldType) {
					case 'pos':
						self::renameSetting($oldName, $newName . Settings::GLUE_PART . Settings::PART_POS_X);
						self::renameSetting($oldName, $newName . Settings::GLUE_PART . Settings::PART_POS_Y);
						break;
					case 'pos_rot':
						self::renameSetting($oldName, $newName . Settings::GLUE_PART . Settings::PART_POS_X);
						self::renameSetting($oldName, $newName . Settings::GLUE_PART . Settings::PART_POS_Y);
						self::renameSetting($oldName, $newName . Settings::GLUE_PART . Settings::PART_ROTATION);
						break;
				}
			}
		}
		if (!$dbCollection->count()) {
			// add a default collection
			$update = 'INSERT INTO `' . $dbCollectionName . '` (ID, name) values (1, \'Default collection\');';
			self::update($update);
		}
	}

	/**
	 * Run SQL query
	 *
	 * @param string $sql
	 * @return int|bool success | Number of rows affected
	 */
	protected static function update(string $sql)
	{
		$res = Db::query($sql);
		if(!$res) {
			$error = Db::getLastError();
			throw new \RuntimeException('Sql query failed:<br><code>' . $error . '</code><br>', 500);
		}
		return $res;
	}

	protected static function createPages(): void
	{
		$signatureSheetPageId = Settings::getCValue('signature_sheet_page_id');
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
			Settings::setCValue('signature_sheet_page_id', $signatureSheetPageId);
		}
		if (empty(Settings::getCValue('use_page_as_mail_link'))) {
			Settings::setCValue('use_page_as_mail_link', $signatureSheetPageId);
		}

		$optinPageId = Settings::getCValue('use_page_as_optin_link');

		if (!self::isPostVisible($optinPageId)) {
			$content     = 'Would you like to opt-in to or opt-out from our List?<br/>[demovox_optin]';
			$post_data   = [
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'ping_status'  => get_option('default_ping_status'),
				'post_content' => $content,
				'post_excerpt' => '',
				'post_title'   => 'Opt-in',
			];
			$optinPageId = wp_insert_post($post_data);
			Settings::setCValue('use_page_as_optin_link', $optinPageId);
		}
	}

	protected static function createCapabilities(): void
	{
		if (Settings::getValue('init_capabilities_version')) {
			return;
		}

		$role = get_role('super admin');
		if ($role) {
			$role->add_cap('demovox');
			$role->add_cap('demovox_stats');
			$role->add_cap('demovox_import');
			$role->add_cap('demovox_export');
			$role->add_cap('demovox_data');
			$role->add_cap('demovox_edit_collection');
			$role->add_cap('demovox_sysinfo');
		}

		$role = get_role('administrator');
		$role->add_cap('demovox');
		$role->add_cap('demovox_stats');
		$role->add_cap('demovox_import');
		$role->add_cap('demovox_export');
		$role->add_cap('demovox_data');
		$role->add_cap('demovox_edit_collection');
		$role->add_cap('demovox_sysinfo');

		$role = get_role('editor');
		$role->add_cap('demovox');
		$role->add_cap('demovox_stats');
		$role->add_cap('demovox_import');
		$role->add_cap('demovox_export');
		$role->add_cap('demovox_data');
		$role->add_cap('demovox_edit_collection');
		$role->add_cap('demovox_sysinfo');

		$role = get_role('author');
		$role->add_cap('demovox');
		$role->add_cap('demovox_stats');
		$role->add_cap('demovox_import');

		Settings::setValue('init_capabilities_version', 1);
	}

	/**
	 * Rename option from demovox settings
	 *
	 * @param string $oldName
	 * @param string $newName
	 * @return void
	 */
	protected static function renameSetting(string $oldName, string $newName): void
	{
		$oldId = Core::getWpId($oldName);
		$newId = Core::getWpId($newName);
		$update = "UPDATE wp_options SET option_name = '$newId' WHERE option_name = '$oldId';";
		Db::query($update);
	}
}