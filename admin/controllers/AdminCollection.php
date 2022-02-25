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

		$dtoSign = new DtoSignatures();
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