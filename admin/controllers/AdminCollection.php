<?php

namespace Demovox;
require_once Infos::getPluginDir() . 'admin/controllers/AdminBaseController.php';

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
class AdminCollection extends AdminBaseController
{
	public function getCollectionId()
	{
		if (!isset($_REQUEST['collection']) || !is_int($_REQUEST['collection'])) {
			return $this->getDefaultCollection();
		}
		return $_REQUEST['collection'];
	}

	public function pageOverview()
	{
		$dbSign = new DbSignatures();
		$count = $dbSign->countSignatures(false);
		$addCount = Config::getValue('add_count');
		$userLang = Infos::getUserLanguage();
		$collectionId = $this->getCollectionId();

		$page = 'demovoxCollectionOverview';
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
			$this->saveOverview();
		}

		$collections = new DbCollections;
		$collection = $collections->getRow(['name', 'end_date', 'end_message'], 'ID = ' . $collectionId);

		if ($count) {
			$add = ' AND collection_ID = ' . $collectionId;
			$stats = new CollectionStatsDto();
			$stats->countOptin = $dbSign->count('is_optin = 1 AND is_step2_done = 1 AND is_deleted = 0' . $add);
			$stats->countOptout = $dbSign->count('is_optin = 0 AND is_step2_done = 1 AND is_deleted = 0' . $add);
			$stats->countOptNULL = $dbSign->count('is_optin IS NULL AND is_step2_done = 1 AND is_deleted = 0' . $add);
			$stats->countUnfinished = $dbSign->count('is_step2_done = 0 AND is_deleted = 0' . $add);
		}

		include Infos::getPluginDir() . 'admin/views/collection/overview.php';
	}

	public function pageImport()
	{
		$statusMsg = '';
		$page = 'demovoxImport';
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
			$statusMsg = $this->doCsvImport();
		}
		$csvFormat = Core::getOption('importCsvFormat');
		$delimiter = Core::getOption('importCsvDelimiter') ?: ';';
		include Infos::getPluginDir() . 'admin/views/collection/import.php';
	}

	public function statsCharts()
	{
		Core::checkAccess('demovox_stats');

		$dbSign = new DbSignatures();
		$sqlAppend = ' AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)';
		$sqlAppend .= ' AND collection_ID = ' . $this->getCollectionId();
		$source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : null;
		if ($source !== null) {
			$sqlAppend .= ' AND source=\'' . $source . '\'';
		}
		$datasets = [];
		// sheet_received_date
		$datasets[] = [
			'label'           => 'Received signatures',
			'borderColor'     => 'rgba(50, 100, 150, 1)',
			'backgroundColor' => 'rgba(50, 100, 150, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'SUM(is_sheet_received) AS count'],
				'is_sheet_received<>0' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Received sheets',
			'borderColor'     => 'rgba(0, 50, 0, 1)',
			'backgroundColor' => 'rgba(0, 50, 0, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) AS count'],
				'is_sheet_received<>0' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-in',
			'borderColor'     => 'rgba(0, 255, 99, 1)',
			'backgroundColor' => 'rgba(0, 255, 99, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 1 AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-out',
			'borderColor'     => 'rgba(255, 206, 86, 1)',
			'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 0 AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'No opt-in info',
			'borderColor'     => 'rgb(68,78,255, 1)',
			'backgroundColor' => 'rgba(68,78,255, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin IS NULL AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Unfinished',
			'borderColor'     => 'rgba(255,99,132,1 )',
			'backgroundColor' => 'rgba(255,99,132, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_step2_done = 0' . $sqlAppend
			),
		];
		include Infos::getPluginDir() . 'admin/views/collection/statsCharts.php';
	}

	public function statsSource()
	{
		Core::checkAccess('demovox_stats');

		$dbSign = new DbSignatures();
		$sourceList = $dbSign->getResultsRaw(
			[
				'source',
				'SUM(is_sheet_received) AS signatures',
				'SUM(is_sheet_received<>0) AS sheetsRec',
				'SUM((is_optin<>0 AND is_step2_done<>0 AND is_deleted = 0)) AS optin',
				'SUM((is_optin=0 AND is_step2_done<>0 AND is_deleted = 0)) AS optout',
				'SUM((is_step2_done=0 AND is_deleted = 0)) AS unfinished',
			],
			'is_deleted = 0',
			'GROUP BY source ORDER BY source'
		);
		include Infos::getPluginDir() . 'admin/views/collection/statsSource.php';
	}

	public function pageData()
	{
		$page = 'demovoxData';
		$dbSign = new DbSignatures();
		$countOptin = $dbSign->count(DbSignatures::WHERE_OPTIN);
		$countFinished = $dbSign->count(DbSignatures::WHERE_FINISHED_IN_SCOPE);
		$countOutsideScope = $dbSign->count(DbSignatures::WHERE_FINISHED_OUT_SCOPE);
		$countUnfinished = $dbSign->count(DbSignatures::WHERE_UNFINISHED);
		$countDeleted = $dbSign->count(DbSignatures::WHERE_DELETED);

		$option = 'per_page';
		$args = [
			'label'   => 'Signatures',
			'default' => 5,
			'option'  => 'signatures_per_page',
		];
		add_screen_option($option, $args);

		require_once Infos::getPluginDir() . 'admin/helpers/SignatureList.php';
		$signatureList = new SignatureList();

		include Infos::getPluginDir() . 'admin/views/collection/data.php';
	}

	protected function saveOverview()
	{
		$collection = new DbCollections;
		$collectionId = intval($_REQUEST['collection_ID']);

		$data = new CollectionsDto();
		$data->name = sanitize_text_field($_REQUEST['name']);
		$data->end_date = empty($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : null;
		$data->end_message = sanitize_text_field($_REQUEST['end_message']);
		$collection->update($data, ['ID' => $collectionId]);
	}

	protected function doCsvImport()
	{
		// Validate request & prepare data
		$deliveryDate = sanitize_text_field($_REQUEST['deliveryDate']);
		$format = intval($_REQUEST['csvFormat']);
		$delimiter = sanitize_text_field($_REQUEST['delimiter']);
		$signCount = intval($_REQUEST['signCount']);
		$csvArr = Strings::parseCsv($_REQUEST['csv'], $delimiter);
		$deliveryDateMysql = date('Y-m-d', strtotime($deliveryDate));
		Core::setOption('importCsvFormat', $format);
		Core::setOption('importCsvDelimiter', $delimiter);
		if (!$deliveryDate) {
			return Strings::wpMessage('Error: Delivery date not defined or invalid', 'error');
		}
		$dbSign = new DbSignatures();

		$dbMailDd = new DbMails();
		$hasDedup = !!$dbMailDd->count();

		// Handle csv
		$ok = $okSign = 0;
		$receivedAgain = $fail = [];
		$return = '';
		foreach ($csvArr as $line) {
			$serial = sanitize_text_field($line[0]);
			if (!$serial) {
				continue;
			}
			switch ($format) {
				case 1:
					if (!$signCount) {
						return Strings::wpMessage('Error: Signature count is required', 'error');
					}
					break;
				case 2:
					$signCount = isset($line[1]) ? intval($line[1]) : null;
					break;
				case 3:
					$signCount = isset($line[2]) ? intval($line[2]) : null;
					break;
				default:
					return Strings::wpMessage('Error: Format is required', 'error');
			}
			if (!$signCount) {
				Core::logMessage('doCsvImport: missing signature count for serial = "' . $serial);
				$fail[] = $serial;
				continue;
			}

			$row = $dbSign->getRow(['ID', 'is_sheet_received', 'mail'], "serial = '" . $serial . "'");
			if (!$row) {
				$fail[] = $serial;
				Core::logMessage('doCsvImport: Could not find serial = "' . $serial . '"');
				continue;
			}

			$save = $dbSign->updateStatus(
				[
					'is_sheet_received'   => $signCount,
					'sheet_received_date' => $deliveryDateMysql,
				],
				['ID' => $row->ID]
			);
			if ($save === false) {
				$fail[] = $serial;
				Core::logMessage(
					'doCsvImport: Could not update signature ID = "' . $row->ID . '" (serial = "' . $serial . '): '
					. Db::getLastError()
				);
				continue;
			}

			if ($hasDedup) {
				$saveDedup = $this->dedupSetSheetReceived($row->mail);
				if (!$saveDedup) {
					$fail[] = $serial;
					Core::logMessage(
						'doCsvImport: Could not update mail dedup ID = "' . $row->ID . '" mail = "' . $row->mail
						. '" (serial = "' . $serial . '): ' . Db::getLastError()
					);
					continue;
				}
			}

			if ($row->is_sheet_received && $row->is_sheet_received != $signCount) {
				$receivedAgain[] = $serial . ' (' . $row->is_sheet_received . ')';
			}
			$ok++;
			$okSign += $signCount;
		}

		// Return
		if ($ok) {
			$return .= Strings::wpMessage('Imported ' . $ok . ' sheets (total of ' . $okSign . ' signatures)', 'success');
		}
		if ($count = count($receivedAgain)) {
			$return .= Strings::wpMessage(
				$count . ' sheets were already marked as received before with another number of signatures.'
				. ' Affected serials with their old number of signatures:<br/>'
				. implode(', ', $receivedAgain),
				'warning '
			);
		}
		if ($count = count($fail)) {
			$return .= Strings::wpMessage($count . ' failed sheet(s):<br/>' . implode(', ', $fail), 'error');
		}
		return $return;
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

	public function getCsv()
	{
		Core::checkAccess('export');

		$dtoSign = new SignaturesDto();
		$dbSign = new DbSignatures();
		$csvMapper = $dtoSign->getAvailableFields();
		$csv = implode(',', $csvMapper) . "\n";
		$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : null;
		$allSignatures = $dbSign->getResultsRaw(array_keys($csvMapper), $dbSign->getWhere($type));

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
}