<?php

namespace Demovox;

class CronBase
{
	protected $id;
	protected $namespace;
	protected $cronName;
	protected $className;
	protected $scheduleRecurrence = 'hourly';

	/**
	 * @var $crons array
	 */
	static protected $cronClassNames = [
		0 => 'CronMailConfirm', 1 => 'CronMailIndex', 2 => 'CronMailRemindSheet', 3 => 'CronMailRemindSignup', 4 => 'CronExportToApi',
	];

	public function __construct()
	{
		if (!Core::isPluginEnabled()) {
			// wp cannot disable crons without deleting them
			$this->setSkipped('Skip cron execution as demovox is disabled');
			return false;
		}
		[$namespace, $className] = explode('\\', get_class($this));
		$cronName          = strtolower(
			preg_replace(
				'/(?<=[a-z])([A-Z]+)/',
				'_$1',
				$className
			)
		);
		$this->namespace = $namespace;
		$this->cronName  = $cronName;
		$this->className = $className;
	}

	/**
	 * @return string[]
	 */
	public static function getCronClassNames(){
		return self::$cronClassNames;
	}

	/**
	 * @return false|int
	 */
	public function getId()
	{
		if ($this->id === null) {
			$this->id = array_search($this->getClassName(), self::$cronClassNames);
		}
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->cronName;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getHookName()
	{
		return strtolower($this->namespace) . '_' . $this->cronName;
	}

	/**
	 * @return string
	 */
	public function getRecurrence()
	{
		return $this->scheduleRecurrence;
	}

	/**
	 * @return bool
	 */
	protected function prepareRun()
	{
		if (Infos::isHighLoad()) {
			$this->setSkipped('server load: ' . Infos::getLoad() . '%');
			return false;
		}
		if ($this->isRunning()) {
			$this->setSkipped('running');
			return false;
		}
		return true;
	}

	public function run()
	{
		Core::errorDie('Not implemented', 500);
	}

	/**
	 * TODO: recognize old tasks as not running
	 *
	 * @return mixed
	 */
	public function isRunning()
	{
		return $this->getOption('lock');
	}

	public function getStausDateStart()
	{
		$time = $this->getOption('start');
		return $time;
	}

	public function getStatusSkipped()
	{
		return $this->getOption('lastSkipped');
	}

	/**
	 * @param string $msg
	 * @param bool   $success
	 */
	public function setStateMessage($msg, $success = true)
	{
		$this->setOption('statusMsg', $msg);
		$this->setOption('statusSuccess', $success);
		$this->log(($success ? 'Success: ' : 'Error: ') . $msg, 'notice');
	}

	public function getStatusMessage()
	{
		return $this->getOption('statusMsg');
	}

	public function getStatusSuccess()
	{
		return $this->getOption('statusSuccess');
	}

	public function getStatusDateStop()
	{
		$time = $this->getOption('stop');
		return $time;
	}

	protected function setRunningStart()
	{
		Core::logMessage('Cron ' . $this->className . ' started');
		$this->setOption('lock', true);
		$this->setOption('start', time());
		$this->setOption('lastSkipped', null);
		$this->setOption('lastFailed', null);
		$this->setStateMessage(null);
	}

	public function cancelRunning()
	{
		Core::logMessage('Cron ' . $this->className . ' cancelled by user "' . Infos::getUserName() . '"', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	protected function setSkipped($reason)
	{
		Core::logMessage('Cron ' . $this->className . ' execution skipped. Reason: ' . $reason, 'notice');
		$this->setOption('lastSkipped', time());
		$this->setStateMessage($reason);
	}

	protected function setRunningStop()
	{
		Core::logMessage('Cron ' . $this->className . ' ended', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	/**
	 * @param string $msg
	 * @param string $level
	 */
	protected function log($msg, $level = 'error')
	{
		Core::logMessage('Cron ' . $this->className . ' message: ' . $msg, $level);
	}

	/**
	 * @param string $id
	 *
	 * @return mixed Value set for the option.
	 */
	protected function getOption($id)
	{
		return Core::getOption($this->cronName . '_' . $id);
	}

	/**
	 * Update or set option
	 *
	 * @param string $id
	 * @param mixed  $value
	 *
	 * @return bool success
	 */
	protected function setOption($id, $value)
	{
		return Core::setOption($this->cronName . '_' . $id, $value);
	}
}