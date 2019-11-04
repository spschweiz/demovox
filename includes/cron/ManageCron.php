<?php

namespace Demovox;

class ManageCron
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $pluginName The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var $crons array
	 */
	static private $crons = ['CronMailConfirm', 'CronMailIndex', 'CronMailRemindSheet', 'CronMailRemindSignup', 'CronExportToApi',];

	/**
	 * @return CronBase[]
	 */
	public static function getAllCrons()
	{
		$crons = [];
		foreach (self::$crons as $cron) {
			$className = '\\' . __NAMESPACE__ . '\\' . ucfirst($cron);
			$crons[] = new $className;
		}
		return $crons;
	}

	public static function loadDependencies()
	{
		$pluginDir = Infos::getPluginDir();
		require_once $pluginDir . 'includes/cron/CronBase.php';
		$crons = self::$crons;
		foreach ($crons as $cron) {
			require_once $pluginDir . 'includes/cron/' . $cron . '.php';
		}
	}

	public static function run($name)
	{
		$sendMails = self::getClass($name);
		$sendMails->run();
		return false;
	}

	public static function triggerCron($hook)
	{
		wp_schedule_single_event(time() - 1, $hook);
		spawn_cron();
	}

	public static function cancel($name)
	{
		$sendMails = self::getClass($name);
		$sendMails->cancelRunning();
	}

	/**
	 * @param $name
	 * @return CronBase
	 */
	protected static function getClass($name)
	{
		if (!in_array($name, self::$crons)) {
			Core::showError('Invalid cron ' . $name, 400);
		}
		$name = __NAMESPACE__ . '\\' . $name;
		return new $name();
	}

	/**
	 * @param Loader $loader
	 */
	public static function registerHooks()
	{
		$cronNames = ManageCron::getAllCrons();
		foreach ($cronNames as $cron) {
			$hook = $cron->getHookName();
			Loader::addAction($hook, $cron, 'run');
		}
	}

	public static function activate()
	{
		$cronNames = ManageCron::getAllCrons();
		foreach ($cronNames as $cron) {
			$hook = $cron->getHookName();
			if (!wp_next_scheduled($hook)) {
				$recurrence = $cron->getRecurrence();
				wp_schedule_event(time(), $recurrence, $hook);
			}
		}
	}

	public static function deactivate()
	{
		$cronNames = ManageCron::getAllCrons();
		foreach ($cronNames as $cron) {
			$hook = $cron->getHookName();
			wp_clear_scheduled_hook($hook);
		}
	}

	public static function deleteOptions()
	{
		$crons = self::$crons;
		foreach ($crons as $cron) {
			Core::delOption('cron_' . $cron . '_lock');
			Core::delOption('cron_' . $cron . '_start');
			Core::delOption('cron_' . $cron . '_stop');
		}
	}
}