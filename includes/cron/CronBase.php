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
	 * @var $cronClassNames array
	 */
	static protected $cronClassNames = [
		0 => 'CronMailConfirm', 1 => 'CronMailIndex', 2 => 'CronMailRemindSheet', 3 => 'CronMailRemindSignup', 4 => 'CronExportToApi',
	];

	public function __construct()
	{
		list($namespace, $className) = explode('\\', get_class($this));
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
	public function getDescription()
	{
		$id = $this->getId();
		$mail_remind_dedup = Config::getValue('mail_remind_dedup') ? 'enabled' : 'disabled';
		switch ($id) {
			case 0: // CronMailConfirm
				$mail_confirmation_enabled = Config::getValue('mail_confirmation_enabled') ? 'enabled' : 'disabled';
				return 'Send sign-up confirmation mails after a client has filled both forms. ' .
					'Requires the setting <i>Confirmation mail</i> (' . $mail_confirmation_enabled . ') to be enabled.';
				break;
			case 1: // CronMailIndex
				return 'Indexing cron to avoid duplicate reminders for the same mails address (table <i>wp_demovox_mails</i>). ' .
					'If enabled, this indexer must be executed before reminder cron. ' .
					'Requires the setting <i>Mail deduplication</i> (' . $mail_remind_dedup . ') to be enabled.';
				break;
			case 2: // CronMailRemindSheet
				$mail_remind_sheet_min_age = intval(Config::getValue('mail_remind_sheet_min_age'));
				$mail_remind_sheet_enabled = Config::getValue('mail_remind_sheet_enabled') ? 'enabled' : 'disabled';
				return 'Send a reminder to signees which didn\'t send their signature sheets ' .
					'after (<strong>' . $mail_remind_sheet_min_age . '</strong>) days. ' .
					'<i>Mail deduplication</i> (' . $mail_remind_dedup . ') can be applied. ' .
					'Requires the setting <i>Sheet reminder mail</i> (' . $mail_remind_sheet_enabled . ') to be enabled.';
				break;
			case 3: // CronMailRemindSignup
				$mail_remind_signup_min_age = intval(Config::getValue('mail_remind_signup_min_age'));
				$mail_remind_signup_enabled = Config::getValue('mail_remind_signup_enabled') ? 'enabled' : 'disabled';
				return 'Send a reminder to signees which didn\'t finish filling the sign-up form ' .
					'after (<strong>' . $mail_remind_signup_min_age . '</strong>) days. ' .
					'<i>Mail deduplication</i> (' . $mail_remind_dedup . ') can be applied. ' .
					'Requires the setting <i>Sheet reminder mail</i> (' . $mail_remind_signup_enabled . ') to be enabled.';
				break;
			case 4: // CronExportToApi
				$api_export_url = Config::getValue('api_export_url');
				$api_export_url = $api_export_url ? 'enabled and set to "' . $api_export_url . '"' : 'disabled';
				return 'Export sign-up data to a REST API. ' .
					'Requires the setting <i>API URL</i> (' . $api_export_url . ').';
				break;
		}
		return null;
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
		if (!Core::isPluginEnabled()) {
			// wp cannot disable crons without deleting them
			$this->setSkipped('Skip cron execution as demovox is disabled');
			return false;
		}
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
		Core::logMessage('Cron ' . $this->className . ' started', 'notice');
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