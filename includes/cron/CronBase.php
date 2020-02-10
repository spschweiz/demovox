<?php

namespace Demovox;

class CronBase
{
	protected $namespace;
	protected $cronId;
	protected $className;
	protected $scheduleRecurrence = 'hourly';

	public function __construct()
	{
		list($namespace, $className) = explode('\\', get_class($this));
		$cronId          = strtolower(
			preg_replace(
				'/(?<=[a-z])([A-Z]+)/',
				'_$1',
				$className
			)
		);
		$this->namespace = $namespace;
		$this->cronId    = $cronId;
		$this->className = $className;
		return;
	}

	public function getName()
	{
		return $this->cronId;
	}

	public function getClassName()
	{
		return $this->className;
	}

	public function getHookName()
	{
		return strtolower($this->namespace) . '_' . $this->cronId;
	}

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
		return Core::getOption($this->cronId . '_' . $id);
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
		return Core::setOption($this->cronId . '_' . $id, $value);
	}
}