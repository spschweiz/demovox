<?php

namespace Demovox;

class ManageCron
{
	/** @var array[] */
	static protected array $allCrons;
	/**
	 * @var $cronClassNames array
	 */
	static protected array $cronClassNames = [
		0 => 'CronMailConfirm',
		1 => 'CronMailIndex',
		2 => 'CronMailRemindSheet',
		3 => 'CronMailRemindSignup',
		4 => 'CronExportToApi',
	];

	/**
	 * @return string[]
	 */
	public static function getCronNames(): array
	{
		return self::$cronClassNames;
	}

	/**
	 * @return array[]
	 */
	public static function getAllCrons(): array
	{
		$allCrons = [];
		$collections = new DbCollections;
		foreach ($collections->getResults(['ID']) as $collection) {
			$allCrons[$collection->ID] = self::getCrons($collection->ID);
		}
		return $allCrons;
	}

	/**
	 * @param int $collectionId
	 * @return CronBase[]
	 */
	public static function getCrons($collectionId): array
	{
		if (!isset(self::$allCrons[$collectionId])) {
			$crons = [];
			foreach (self::getCronNames() as $id => $cron) {
				$className = '\\' . __NAMESPACE__ . '\\' . $cron;
				$crons[$id]   = new $className($collectionId);
			}
			self::$allCrons[$collectionId] = $crons;
		}
		return self::$allCrons[$collectionId];
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

	public static function run(int $id, ?int $collectionId = null): void
	{
		if(isset($collectionId)){
			$cron = self::getClass($id, $collectionId);
			$cron->run();
			return;
		}
		$collections = new DbCollections;
		foreach ($collections->getResults(['ID']) as $collection) {
			$cron = self::getClass($id, $collection->ID);
			$cron->run();
		}
	}

	/**
	 * Manually trigger a cron
	 * @param int $id
	 * @param int $collectionId
	 * @return void
	 */
	public static function triggerCron(int $id, int $collectionId)
	{
		$hook = self::getHookName();
		wp_schedule_single_event(time() - 1, $hook, [$id, $collectionId]);
		spawn_cron();
	}

	/**
	 * @param int $id
	 */
	public static function cancel(int $id, int $collectionId)
	{
		$cron = self::getClass($id, $collectionId);
		$cron->cancelRunning();
	}

	/**
	 * @param int $id
	 * @param int $collectionId
	 *
	 * @return CronBase
	 */
	protected static function getClass(int $id, int $collectionId)
	{
		$cronNames = self::getCronNames();
		if (!isset($cronNames[$id])) {
			Core::errorDie('Invalid cron ' . $id, 400);
		}
		$name = __NAMESPACE__ . '\\' . $cronNames[$id];
		return new $name($collectionId);
	}

	/**
	 * @return string
	 */
	public static function getHookName(): string
	{
		[$namespace, $className] = explode('\\', __CLASS__);
		return strtolower($namespace) . '_' . $className;
	}

	/**
	 * @return void
	 */
	public static function registerHooks()
	{
		Loader::addAction(self::getHookName(), new self(), 'run', 10, 2);

		// Include dependencies that aren't otherwise loaded if using WP_CLI
		$pluginDir = Infos::getPluginDir();
		require_once $pluginDir . 'includes/models/Db.php';
		require_once $pluginDir . 'includes/models/DbCollections.php';

		// Add the cron actions for the individual crons (per cron class and collection)
		$collectionCrons = self::getAllCrons();
		foreach($collectionCrons as $cronNames) {
			foreach ( $cronNames as $cron ) {
				$hook = $cron->getHookName();
				Loader::addAction($hook, new self(), 'run', 10, 2);
			}
		}
	}

	public static function activate()
	{
		$collectionCrons = ManageCron::getAllCrons();
		foreach($collectionCrons as $collectionId => $cronNames) {
			foreach ($cronNames as $cron_id => $cron) {
				$hook = $cron->getHookName();
				if (!wp_next_scheduled($hook)) {
					$args       = [$cron_id, $collectionId];
					$recurrence = $cron->getRecurrence();
					wp_schedule_event(time(), $recurrence, $hook, $args);
				}
			}
		}
	}

	public static function deactivate()
	{
		$collectionCrons = ManageCron::getAllCrons();
		foreach($collectionCrons as $cronNames) {
			foreach ($cronNames as $cron) {
				$hook = $cron->getHookName();
				wp_clear_scheduled_hook($hook);
			}
		}
	}

	public static function deleteOptions()
	{
		$collectionCrons = ManageCron::getAllCrons();
		foreach($collectionCrons as $cronNames) {
			foreach ($cronNames as $cron) {
				Core::delOption('cron_' . $cron . '_lock');
				Core::delOption('cron_' . $cron . '_start');
				Core::delOption('cron_' . $cron . '_stop');
				Core::delOption('cron_' . $cron . '_status');
			}
		}
		$crons = self::getCronNames();
		foreach ($crons as $cron) {
			Core::delOption('cron_' . $cron . '_lock');
			Core::delOption('cron_' . $cron . '_start');
			Core::delOption('cron_' . $cron . '_stop');
			Core::delOption('cron_' . $cron . '_status');
		}
	}
}
