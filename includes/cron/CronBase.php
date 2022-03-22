<?php

namespace Demovox;

abstract class CronBase
{
	protected $id;
	protected string $namespace;
	protected string $cronName;
	protected string $className;
	protected string $scheduleRecurrence = 'hourly';

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

	protected function defineCronMeta(): void
	{
		[$namespace, $className] = explode('\\', get_class($this));
		$cronName = strtolower(
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
	public static function getCronClassNames(): array
	{
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
	public function getName(): string
	{
		if (!isset($this->cronName)) {
			$this->defineCronMeta();
		}
		return $this->cronName;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		if (!isset($this->namespace)) {
			$this->defineCronMeta();
		}
		return $this->namespace;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		$id                = $this->getId();
		$mail_remind_dedup = Settings::getValue('mail_remind_dedup') ? 'enabled' : 'disabled';
		switch ($id) {
			case 0: // CronMailConfirm
				$mail_confirmation_enabled = Settings::getCValue('mail_confirmation_enabled') ? 'enabled' : 'disabled';
				return 'Send sign-up confirmation mails after a client has filled both forms. '
					   . 'Requires the setting <i>Confirmation mail</i> (' . $mail_confirmation_enabled
					   . ') to be enabled.';
				break;
			case 1: // CronMailIndex
				return 'Indexing cron to avoid duplicate reminders for the same mails address (table <i>wp_demovox_mails</i>). '
					   . 'If enabled, this indexer must be executed before reminder cron. '
					   . 'Requires the setting <i>Mail deduplication</i> (' . $mail_remind_dedup . ') to be enabled.';
				break;
			case 2: // CronMailRemindSheet
				$mail_remind_sheet_min_age = intval(Settings::getCValue('mail_remind_sheet_min_age'));
				$mail_remind_sheet_enabled = Settings::getCValue('mail_remind_sheet_enabled') ? 'enabled' : 'disabled';
				return 'Send a reminder to signees which didn\'t send their signature sheets ' . 'after (<strong>'
					   . $mail_remind_sheet_min_age . '</strong>) days. '
					   . '<i>Mail deduplication</i> ('
					   . $mail_remind_dedup . ') can be applied. ' . 'Requires the setting <i>Sheet reminder mail</i> ('
					   . $mail_remind_sheet_enabled . ') to be enabled.';
				break;
			case 3: // CronMailRemindSignup
				$mail_remind_signup_min_age = intval(Settings::getCValue('mail_remind_signup_min_age'));
				$mail_remind_signup_enabled = Settings::getCValue('mail_remind_signup_enabled') ? 'enabled' : 'disabled';
				return 'Send a reminder to signees which didn\'t finish filling the sign-up form ' . 'after (<strong>'
					   . $mail_remind_signup_min_age . '</strong>) days. '
					   . '<i>Mail deduplication</i> ('
					   . $mail_remind_dedup . ') can be applied. ' . 'Requires the setting <i>Sheet reminder mail</i> ('
					   . $mail_remind_signup_enabled . ') to be enabled.';
				break;
			case 4: // CronExportToApi
				$api_export_url = Settings::getCValue('api_export_url');
				$api_export_url = $api_export_url ? 'enabled and set to "' . $api_export_url . '"' : 'disabled';
				return 'Export sign-up data to a REST API. '
					   . 'Requires the setting <i>API URL</i> (' . $api_export_url . ').';
				break;
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		if (!isset($this->className)) {
			$this->defineCronMeta();
		}
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getHookName(): string
	{
		return strtolower($this->getNamespace()) . '_' . $this->getName();
	}

	/**
	 * @return string
	 */
	public function getRecurrence(): string
	{
		return $this->scheduleRecurrence;
	}

	/**
	 * @return bool
	 */
	protected function prepareRun(): bool
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

	public function getStatusDateStart()
	{
		return $this->getOption('start');
	}

	public function getStatusSkipped()
	{
		return $this->getOption('lastSkipped');
	}

	/**
	 * @param string $msg
	 * @param bool   $success
	 */
	public function setStateMessage(string $msg, bool $success = true): void
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
		return $this->getOption('stop');
	}

	protected function setRunningStart(): void
	{
		Core::logMessage('Cron ' . $this->getClassName() . ' started', 'notice');
		$this->setOption('lock', true);
		$this->setOption('start', time());
		$this->setOption('lastSkipped', null);
		$this->setOption('lastFailed', null);
		$this->setStateMessage(null);
	}

	public function cancelRunning(): void
	{
		Core::logMessage('Cron ' . $this->getClassName() . ' cancelled by user "' . Infos::getUserName() . '"', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	protected function setSkipped(string $reason): void
	{
		Core::logMessage('Cron ' . $this->getClassName() . ' execution skipped. Reason: ' . $reason, 'notice');
		$this->setOption('lastSkipped', time());
		$this->setStateMessage($reason);
	}

	protected function setRunningStop(): void
	{
		Core::logMessage('Cron ' . $this->getClassName() . ' ended', 'notice');
		$this->setOption('lock', false);
		$this->setOption('stop', time());
	}

	/**
	 * @param string $msg
	 * @param string $level
	 */
	protected function log(string $msg, string $level = 'error'): void
	{
		Core::logMessage('Cron ' . $this->getClassName() . ' message: ' . $msg, $level);
	}

	/**
	 * @param string $id
	 *
	 * @return mixed Value set for the option.
	 */
	protected function getOption($id)
	{
		return Core::getOption($this->getName() . '_' . $id);
	}

	/**
	 * Update or set option
	 *
	 * @param string $id
	 * @param mixed  $value
	 *
	 * @return bool success
	 */
	protected function setOption(string $id, $value): bool
	{
		return Core::setOption($this->getName() . '_' . $id, $value);
	}
}