<?php

namespace Demovox;

/**
 * PHPUnit bootstrap file
 *
 * @package Demovox
 */
class TestsBootstrap
{
	/**
	 * Manually load the plugin being tested.
	 */
	function _manually_load_plugin()
	{
		require dirname(dirname(__FILE__)) . '/phpunit/demovox.php';
	}

	function getPluginDir()
	{
		return plugin_dir_path(dirname(__FILE__)) . '..' . DIRECTORY_SEPARATOR;
	}

	function testLoadDependencies()
	{
		$pluginDir = $this->getPluginDir();
		require_once $pluginDir . 'includes/Core.php';
		Core::loadDependencies();

		ManageCron::loadDependencies();

		require_once $pluginDir . 'admin/base/AdminSettings.php';
		require_once $pluginDir . 'admin/AdminGeneralSettings.php';

		require_once $pluginDir . 'includes/wp/Activator.php';
		Activator::activateDb();

		require_once $pluginDir . 'tests/phpunit/TestBase.php';
	}

	function testSetConfigValues()
	{
		$adminSettings = new AdminGeneralSettings('demovox', '1.0.0');
		$adminSettings->setupFields();

		$config = [
			'encrypt_signees' => 'disabled',
		];
		foreach ($config as $key => $value) {
			Config::setValue($key, $value);
		}

		define(
			'DEMOVOX_ENC_KEY',
			'def0000067ef16296c818a7c9834f99e6eebcb85bea1027b54a07b0fb065683dc6593162811bbc22ffbfb08e0d656b68c9a8131dee75154f416a6a1878ad0f0ce5c2e6cb'
		);
		define('DEMOVOX_HASH_KEY', 'df4ea9b7ebf036d1488833026d7fae07953ac996b1de1b84740162f68300');
	}

	function testsInit()
	{
		$_tests_dir = getenv('WP_TESTS_DIR');

		if (!$_tests_dir) {
			$_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
		}

		if (!file_exists($_tests_dir . '/includes/functions.php')) {
			echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // WPCS: XSS ok.
			exit(1);
		}

		// Give access to tests_add_filter() function.
		require_once $_tests_dir . '/includes/functions.php';

		tests_add_filter('muplugins_loaded', [$this, '_manually_load_plugin']);

		// Start up the WP testing environment.
		require $_tests_dir . '/includes/bootstrap.php';

		$this->testLoadDependencies();
		$this->testSetConfigValues();
	}
}

$testsBootstrap = new TestsBootstrap();
$testsBootstrap->testsInit();