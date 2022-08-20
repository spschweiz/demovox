<?php

namespace Demovox;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/admin
 * @author     SP Schweiz
 */
class AdminCollection extends BaseController
{
	use CollectionTrait, AdminScriptsTrait;

	public function __construct(string $pluginName, string $version)
	{
		parent::__construct($pluginName, $version);
		$this->setCollectionIdByReq();
	}

	protected function getCurrentPage()
	{
		return sanitize_text_field($_REQUEST['page']);
	}

	public function pageOverview()
	{
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
			$this->saveOverview();
		}

		$collectionId = $this->getCollectionId();
		$collectionName = $this->getCollectionName();

		$collections = new DbCollections();
		$collection = $collections->getRow(['name', 'end_date', 'end_message'], 'ID = ' . $collectionId);
		if(!$collection) {
			Core::errorDie('Requested collection not found where ID = ' . $collectionId, 404);
		}

		$dbSign = new DbSignatures();
		$count = $dbSign->countSignatures($collectionId, false);
		$addCount = Settings::getCValue('add_count', $collectionId);
		$userLang = Infos::getUserLanguage();

		$page = $this->getCurrentPage();

		$stats = new CollectionStatsDto();
		if ($count) {
			$stats->countOptin = $dbSign->count(DbSignatures::WHERE_OPTIN, $collectionId);
			$stats->countOptout = $dbSign->count(DbSignatures::WHERE_OPTOUT, $collectionId);
			$stats->countOptNULL = $dbSign->count(DbSignatures::WHERE_OPTNULL, $collectionId);
			$stats->countUnfinished = $dbSign->count(DbSignatures::WHERE_UNFINISHED, $collectionId);
		}

		$mailRecipient = $this->getWpMailAddress();
		$languages = i18n::getLangsEnabled();

		include Infos::getPluginDir() . 'admin/views/collection/overview.php';
	}

	protected function getWpMailAddress()
	{
		return get_bloginfo('admin_email');
	}

	protected function getWpMailName()
	{
		return get_bloginfo('name');
	}

	/**
	 * ajax action "collection_create"
	 * @return void
	 */
	public function createNew()
	{
		Core::requireAccess('demovox_edit_collection');

		$collections = new DbCollections();
		$latestRecord = $collections->getRow(['ID'], '', 'ORDER BY ID DESC LIMIT 1');
		$collectionId = ($latestRecord->ID + 1);
		$this->setCollectionID($collectionId);

		$newRecord = new CollectionsDto();
		$newRecord->ID = $collectionId;
		$newRecord->name = 'Collection ' . $collectionId;

		$success = $collections->insert($newRecord);

		Settings::initDefaults($collectionId);
		echo $success ? 'ok<script>window.location.reload();</script>' : 'failed';
	}

	/**
	 * ajax action "charts_stats"
	 * @return void
	 */
	public function statsCharts()
	{
		Core::requireAccess('demovox_stats');
		$collectionId = $this->getCollectionId();

		$dbSign = new DbSignatures();
		$whereAppend = ' AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)';
		$whereAppend .= ' AND collection_ID = ' . $collectionId;
		$source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : null;
		if ($source !== null) {
			$whereAppend .= ' AND source=\'' . $source . '\'';
		}
		$datasets = [];
		// sheet_received_date
		$datasets[] = [
			'label'           => 'Received signatures',
			'borderColor'     => 'rgba(50, 100, 150, 1)',
			'backgroundColor' => 'rgba(50, 100, 150, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'SUM(is_sheet_received) AS count'],
				'is_sheet_received<>0' . $whereAppend
			),
		];
		$datasets[] = [
			'label'           => 'Received sheets',
			'borderColor'     => 'rgba(0, 50, 0, 1)',
			'backgroundColor' => 'rgba(0, 50, 0, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) AS count'],
				'is_sheet_received<>0' . $whereAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-in',
			'borderColor'     => 'rgba(0, 255, 99, 1)',
			'backgroundColor' => 'rgba(0, 255, 99, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 1 AND is_step2_done = 1' . $whereAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-out',
			'borderColor'     => 'rgba(255, 206, 86, 1)',
			'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 0 AND is_step2_done = 1' . $whereAppend
			),
		];
		$datasets[] = [
			'label'           => 'No opt-in info',
			'borderColor'     => 'rgb(68,78,255, 1)',
			'backgroundColor' => 'rgba(68,78,255, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin IS NULL AND is_step2_done = 1' . $whereAppend
			),
		];
		$datasets[] = [
			'label'           => 'Unfinished',
			'borderColor'     => 'rgba(255,99,132,1 )',
			'backgroundColor' => 'rgba(255,99,132, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_step2_done = 0' . $whereAppend
			),
		];
		include Infos::getPluginDir() . 'admin/views/collection/statsCharts.php';
	}

	/**
	 * ajax action "source_stats"
	 * @return void
	 */
	public function statsSource()
	{
		Core::requireAccess('demovox_stats');
		$collectionId = $this->getCollectionId();

		$dbSign = new DbSignatures();
		$sourceList = $dbSign->getResultsRaw(
			[
				'source',
				'SUM(' . $dbSign->getWhere(DbSignatures::WHERE_SHEETS_SIGNS_RECEIVED) . ') AS signatures',
				'SUM(' . $dbSign->getWhere(DbSignatures::WHERE_SHEETS_RECEIVED) . ') AS sheetsRec',
				'SUM((' . $dbSign->getWhere(DbSignatures::WHERE_OPTIN) . ')) AS optin',
				'SUM((' . $dbSign->getWhere(DbSignatures::WHERE_OPTOUT) . ')) AS optout',
				'SUM((' . $dbSign->getWhere(DbSignatures::WHERE_UNFINISHED) . ')) AS unfinished',
			],
			'collection_ID = ' . $collectionId,
			'GROUP BY source ORDER BY source'
		);
		include Infos::getPluginDir() . 'admin/views/collection/statsSource.php';
	}

	public function pageData()
	{
		$collectionId = $this->getCollectionId();
		$collectionName = $this->getCollectionName();
		$page = $this->getCurrentPage();
		$dbSign = new DbSignatures();
		$count = [
			DbSignatures::WHERE_OPTIN              => $dbSign->count(DbSignatures::WHERE_OPTIN, $collectionId),
			DbSignatures::WHERE_FINISHED_IN_SCOPE  => $dbSign->count(DbSignatures::WHERE_FINISHED_IN_SCOPE, $collectionId),
			DbSignatures::WHERE_FINISHED_OUT_SCOPE => $dbSign->count(DbSignatures::WHERE_FINISHED_OUT_SCOPE, $collectionId),
			DbSignatures::WHERE_UNFINISHED         => $dbSign->count(DbSignatures::WHERE_UNFINISHED, $collectionId),
			DbSignatures::WHERE_DELETED            => $dbSign->count(DbSignatures::WHERE_DELETED, $collectionId)
		];

		$option = 'per_page';
		$args = [
			'label'   => 'Signatures',
			'default' => 5,
			'option'  => 'signatures_per_page',
		];
		add_screen_option($option, $args);

		require_once Infos::getPluginDir() . 'admin/helpers/SignatureList.php';
		$signatureList = new SignatureList($collectionId);

		include Infos::getPluginDir() . 'admin/views/collection/data.php';
	}

	public function pageCron()
	{
		$collectionId   = $this->getCollectionId();
		$collectionName = $this->getCollectionName();
		$allCrons       = ManageCron::getCrons($collectionId);
		include Infos::getPluginDir() . 'admin/views/collection/cron.php';
	}

	/**
	 * ajax action "run_cron"
	 * @return void
	 */
	public function runCron()
	{
		Core::requireAccess('demovox_sysinfo');

		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::triggerCron($hook, $this->getCollectionId());
		echo 'Event triggered at ' . date('d.m.Y G:i:s');
	}

	/**
	 * ajax action "cancel_cron"
	 * @return void
	 */
	public function cancelCron()
	{
		Core::requireAccess('demovox_sysinfo');

		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::cancel($hook, $this->getCollectionId());
		echo 'Cron cancelled at ' . date('d.m.Y G:i:s');
	}

	protected function saveOverview()
	{
		Core::requireAccess('demovox_edit_collection');
		$collectionId = $this->getCollectionId();

		$collection = new DbCollections;

		$data           = new CollectionsDto();
		$data->name     = sanitize_text_field($_REQUEST['name']);
		$data->end_date = '';
		if (!empty($_REQUEST['end_date'])) {
			$endDate        = strtotime(sanitize_text_field($_REQUEST['end_date']));
			$data->end_date = $endDate ? date('Y-m-d', $endDate) : '';
		}
		$data->end_message = sanitize_text_field($_REQUEST['end_message']);
		$collection->update($data, ['ID' => $collectionId]);
	}

	/**
	 * @param $mail
	 * @return bool success
	 */
	protected function dedupSetSheetReceived($mail)
	{
		$hashedMail = Strings::hashMail($mail);
		$dbMailDd = new DbMails();
		$update = $dbMailDd->updateStatus(['is_sheet_received' => 1], ['mail_md5' => $hashedMail]);
		return $update !== false;
	}

	/**
	 * ajax action "get_csv"
	 * @return void
	 */
	public function getCsv()
	{
		Core::requireAccess('demovox_export');
		$collectionId = $this->getCollectionId();

		$dtoSign = new SignaturesDto();
		$dbSign = new DbSignatures();

		$csvMapper = $dtoSign->getAvailableFields();
		$csv = implode(',', $csvMapper) . "\n";

		$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : null;
		$where = $dbSign->getWhere($type) . ' AND collection_ID = ' . $collectionId;
		$allSignatures = $dbSign->getResultsRaw(array_keys($csvMapper), $where);

		foreach ($allSignatures as $signature) {
			$csvSignature = [];

			foreach ($csvMapper as $key => $value) {
				$valueEscaped = str_replace('"', '""', $signature->$key);
				$csvSignature[] = '"' . $valueEscaped . '"';
			}

			$csv .= implode(',', $csvSignature) . "\n";
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header('Content-Disposition: attachment; filename="demovox-' . $type . '.csv";');
		header("Content-Transfer-Encoding: binary");

		echo $csv;
	}

	/**
	 * ajax action "mail_test"
	 * @return void
	 */
	public function testMail()
	{
		Core::requireAccess('demovox_sysinfo');

		$mailTo = $this->getWpMailAddress();
		$langId = (isset($_REQUEST['lang']) && $_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : i18n::$languageDefault;
		$mailType = isset($_REQUEST['mailType']) ? intval($_REQUEST['mailType']) : Mail::TYPE_CONFIRM;
		$mailFrom = Settings::getCValueByLang('mail_from_address', $langId);
		$nameFrom = Settings::getCValueByLang('mail_from_name', $langId);

		$sign = new SignaturesDto();
		$sign->language = $langId;
		$sign->first_name = $nameFrom;
		$sign->last_name = 'last name';
		$sign->title = 'Miss';
		$sign->mail = $mailFrom;
		$sign->guid = 'secretguid';
		$sign->link_pdf = Strings::getPageUrl('SIGNEE_PERSONAL_CODE');
		$sign->link_optin = Strings::getPageUrl('SIGNEE_PERSONAL_CODE', Settings::getCValue('use_page_as_optin_link'));

		define('WP_SMTPDEBUG', true);
		Loader::addAction('phpmailer_init', new Mail(), 'config', 10, 1);

		$mailSubject = Mail::getMailSubject($sign, $mailType);
		$mailText = Mail::getMailText($sign, $mailSubject, $mailType);

		Loader::addAction('wp_mail_failed', new Mail(), 'echoMailerErrors', 20, 1);

		ob_start();
		$isSent = Mail::send($mailTo, $mailSubject, $mailText, $mailFrom, $nameFrom);
		$connectionLog = ob_get_contents();
		ob_end_clean();

		include Infos::getPluginDir() . 'admin/views/general/sysinfo-mail.php';
	}
}