<?php

namespace Demovox;

class CronBase
{
	protected $cronId;
	protected $cronName;

	public function __construct($cronName)
	{
		$this->cronId = lcfirst($cronName);
		$this->cronName = ucfirst($cronName);
	}

	public function getName()
	{
		return $this->cronName;
	}

	public function getHookName()
	{
		return 'demovox_send_mails';
	}

	public function getId()
	{
		return $this->cronId;
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

	/**
	 * TODO: recognize old tasks as not running
	 * @return mixed
	 */
	public function isRunning()
	{
		return $this->getOption('lock');
	}

	public function getRunningStart()
	{
		$time = $this->getOption('start');
		return $time;
	}

	public function getSkipped()
	{
		$lastSkipped = $this->getOption('lastSkipped');
		return $lastSkipped
			? [$lastSkipped, $this->getOption('lastSkippedReason')]
			: null;
	}

	public function getRunningStop()
	{
		$time = $this->getOption('stop');
		return $time;
	}

	protected function setRunningStart()
	{
		Core::logMessage('Cron ' . $this->getName() . ' started');
		$this->setOption('lock', true);
		$this->setOption('start', time());
		$this->setOption('lastSkipped', null);
		$this->setOption('lastFailed', null);
	}

	public function cancelRunning()
	{
		Core::logMessage('Cron ' . $this->getName() . ' cancelled by user "' . Infos::getUserName() . '"', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	protected function setSkipped($reason)
	{
		Core::logMessage('Cron ' . $this->getName() . ' execution skipped. Reason: ' . $reason, 'notice');
		$this->setOption('lastSkipped', time());
		$this->setOption('lastSkippedReason', $reason);
	}

	protected function setRunningStop()
	{
		Core::logMessage('Cron ' . $this->getName() . ' ended', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	protected function log($msg, $level = 'error')
	{
		Core::logMessage('Cron ' . $this->getName() . ' message: ' . $msg, $level);
	}

	/**
	 * @param  string $id
	 * @return mixed Value set for the option.
	 */
	protected function getOption($id)
	{
		return Core::getOption('cron_' . $this->cronId . '_' . $id);
	}

	/**
	 * Update or set option
	 *
	 * @param string $id
	 * @param mixed $value
	 * @return bool success
	 */
	protected function setOption($id, $value)
	{
		return Core::setOption('cron_' . $this->cronId . '_' . $id, $value);
	}
}