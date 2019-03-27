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
	static private $crons = ['SendMails',];

	public function __construct($pluginName, $version)
	{
		$this->pluginName = $pluginName;
		$this->version = $version;
	}

	/**
	 * @return CronBase[]
	 */
	public static function getAllCrons()
	{
		$crons = [];
		foreach (self::$crons as $cron) {
			$className = '\Demovox\Cron' . ucfirst($cron);
			$crons[] = new $className;
		}
		return $crons;
	}

	public static function getRequired()
	{
		$pluginDir = Infos::getPluginDir();
		require_once $pluginDir . 'includes/cron/CronBase.php';
		$crons = self::$crons;
		foreach ($crons as $cron) {
			require_once $pluginDir . 'includes/cron/Cron' . $cron . '.php';
		}
	}

	public function sendMails()
	{
		$sendMails = new CronSendMails();
		$sendMails->run();
		return false;
	}

	public function cancelMail()
	{
		$sendMails = new CronSendMails();
		$sendMails->cancelRunning();
	}

	public static function triggerCron($hook)
	{
		wp_schedule_single_event(time() - 1, $hook);
		spawn_cron();
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