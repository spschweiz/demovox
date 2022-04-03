<?php

namespace Demovox;

class CronExportToApi extends CronBase
{
	static protected array $fields = [
		'language', 'ip_address', 'title', 'first_name', 'last_name', 'birth_date', 'mail', 'phone', 'country', 'street', 'street_no',
		'zip', 'city', 'gde_no', 'gde_zip', 'gde_name', 'gde_canton', 'is_optin', 'creation_date', 'source',
	];

	public function run()
	{
		if (!$url = Settings::getCValue('api_export_url')) {
			$this->setSkipped('"Export URL disabled in config');
			return;
		}
		if (!$this->prepareRun()) {
			return;
		}
		$this->setRunningStart();
		$this->exportPendingRows($url);
		$this->setRunningStop();
	}

	/**
	 * @param string $url
	 */
	protected function exportPendingRows(string $url)
	{
		if (substr($url, 0, 8) !== 'https://') {
			$this->setStateMessage(
				'Configuration value "Export URL" should start with "https://" but the current value "' . $url . '" doesn\'t',
				false
			);
			return;
		}

		$dataJson = Settings::getCValue('api_export_data');
		$data     = json_decode($dataJson);
		if (!is_object($data)) {
			$this->setStateMessage('Configuration value "Export Data" is not valid JSON', false);
			return;
		}

		$rows = $this->getRows();

		$count = 0;
		foreach ($rows as $row) {
			if ($this->exportRow($row, $url, $data) === 1) {
				$count++;
			}
		}

		$countFailed = count($rows) - $count;
		$msg         = 'Exported ' . $count . ' signatures'
					   . ($countFailed ? ' and ' . $countFailed . ' failed! Please check log for details.' : '');
		$this->setStateMessage($msg);
	}

	/**
	 * @param SignaturesDto $row
	 * @param string        $url
	 * @param string[]      $data
	 *
	 * @return int
	 */
	protected function exportRow(SignaturesDto $row, string $url, array $data): int
	{
		foreach ($data as &$dataVal) {
			foreach (self::$fields as $fieldName) {
				$dataVal = str_replace('{' . $fieldName . '}', $row->{$fieldName}, $dataVal);
			}
		}

		$args     = [
			'method'      => 'POST',
			'timeout'     => 15,
			'redirection' => 0,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => [],
			'body'        => $data,
			'cookies'     => [],
		];
		$response = wp_safe_remote_post($url, $args);

		if ($error = is_wp_error($response)) {
			$error_message = $response->get_error_message();
			echo "API request failed: $error_message";
			$this->log('API request failed: ' . $error_message, 'error');
		} else {
			$this->log('Response: ' . print_r($response, 1), 'notice');
		}
		$stateExported = $error ? ($row->is_exported - 1) : 1;

		$dbSign = new DbSignatures();
		$dbSign->updateStatus(['is_exported' => $stateExported], ['ID' => $row->ID]);
		return $stateExported;
	}

	/**
	 * @return SignaturesDto[]
	 */
	protected function getRows(): array
	{
		$fields = array_merge(['ID', 'is_exported'], self::$fields);
		$where  = 'is_exported <= 0 AND is_exported > -3 AND is_step2_done = 1 AND is_deleted = 0';
		if (!Settings::getCValue('api_export_no_optin')) {
			$where .= ' AND is_optin = 1';
		}
		$maxMails = intval(Settings::getCValue('api_export_max_per_execution')) ?: 25;
		$dbSign   = new DbSignatures();
		$rows     = $dbSign->getResults($fields, $where, ' LIMIT ' . $maxMails);
		$this->log(
			'Prepared ' . count($rows) . ' signatures to send mails (select is limited to ' . $maxMails
			. ' per cron execution)',
			'notice'
		);
		return $rows;
	}
}