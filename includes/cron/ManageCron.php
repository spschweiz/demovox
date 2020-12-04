<?php

namespace Demovox;

class ManageCron
{
	static protected $allCrons;

	/**
	 * @return string[]
	 */
	public static function getCronNames()
	{
		return CronBase::getCronClassNames();
	}

	/**
	 * @return CronBase[]
	 */
	public static function getAllCrons()
	{
		if (self::$allCrons === null) {
			$crons = [];
			foreach (self::getCronNames() as $cron) {
				$className = '\\' . __NAMESPACE__ . '\\' . $cron;
				$crons[]   = new $className();
			}
			self::$allCrons = $crons;
		}
		return self::$allCrons;
	}

	public static function loadDependencies()
	{
		$pluginDir = Infos::getPluginDir();
		require_once $pluginDir . 'includes/cron/CronBase.php';
		require_once $pluginDir . 'includes/cron/CronMailBase.php';
		$crons = self::getCronNames();
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

	public static function triggerCron($id)
	{
		$hook = self::getClass($id);
		$name = $hook->getHookName();
		wp_schedule_single_event(time() - 1, $name);
		spawn_cron();
	}

	/**
	 * @param int $id
	 */
	public static function cancel($id)
	{
		$sendMails = self::getClass($id);
		$sendMails->cancelRunning();
	}

	/**
	 * @param int $id
	 *
	 * @return CronBase
	 */
	protected static function getClass($id)
	{
		$cronNames = self::getCronNames();
		if (!isset($cronNames[$id])) {
			Core::errorDie('Invalid cron ' . $id, 400);
		}
		$name = __NAMESPACE__ . '\\' . $cronNames[$id];
		return new $name();
	}

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
		$crons = self::getCronNames();
		foreach ($crons as $cron) {
			Core::delOption('cron_' . $cron . '_lock');
			Core::delOption('cron_' . $cron . '_start');
			Core::delOption('cron_' . $cron . '_stop');
		}
	}
}