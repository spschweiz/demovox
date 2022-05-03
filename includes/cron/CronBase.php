<?php

namespace Demovox;

abstract class CronBase
{
	protected int $cronId;
	protected int $collectionId;
	protected string $namespace;
	protected string $cronName;
	protected string $className;
	protected string $scheduleRecurrence = 'hourly';

	public function __construct(int $collectionId)
	{
		$this->collectionId = $collectionId;
		Infos::setCollectionId($collectionId);
	}

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
		return ManageCron::getCronNames();
	}

	/**
	 * @return false|int
	 */
	public function getCronId()
	{
		if (!isset($this->cronId)) {
			$this->cronId = array_search($this->getClassName(), self::getCronClassNames());
		}
		return $this->cronId;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		if (!isset($this->cronName)) {
			$this->defineCronMeta();
		}
		return $this->cronName . '_' . $this->collectionId;
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
		$id                = $this->getCronId();
		$desc              = null;
		$mail_remind_dedup = $this->getStatusLabel(Settings::getValue('mail_remind_dedup'));
		switch ($id) {
			case 0: // CronMailConfirm
				$status = $this->getStatusLabel(Settings::getCValue('mail_confirmation_enabled'));
				$desc  = 'Send sign-up confirmation mails after a client has filled both forms. '
						  . 'Requires the setting <i>Confirmation mail ({status})</i> to be enabled.';
				$desc  = Strings::__a($desc, ['{status}' => $status]);
				break;
			case 1: // CronMailIndex
				$desc = 'Indexing cron to avoid duplicate reminders for the same mails address (table <i>wp_demovox_mails</i>). '
						 . 'If enabled, this indexer must be executed before reminder cron. '
						 . 'Requires the setting <i>Mail deduplication ({status_dedup})</i> to be enabled.';
				$desc = Strings::__a($desc, ['{status_dedup}' => $mail_remind_dedup]);
				break;
			case 2: // CronMailRemindSheet
				$mail_remind_sheet_min_age = intval(Settings::getCValue('mail_remind_sheet_min_age'));
				$status                    = $this->getStatusLabel(Settings::getCValue('mail_remind_sheet_enabled'));

				$desc = 'Send a reminder to signees which didn\'t send their signature sheets ' . 'after (<strong>{mail_remind_signup_min_age}</strong>) days. '
						 . '<i>Mail deduplication ({status_dedup})</i> can be applied. '
						 . 'Requires the setting <i>Sheet reminder mail ({status})</i> to be enabled.';
				$desc = Strings::__a(
					$desc,
					[
						'{mail_remind_signup_min_age}' => $mail_remind_sheet_min_age,
						'{status}' => $status,
						'{status_dedup}' => $mail_remind_dedup
					]
				);
				break;
			case 3: // CronMailRemindSignup
				$mail_remind_signup_min_age = intval(Settings::getCValue('mail_remind_signup_min_age'));
				$status                     = $this->getStatusLabel(Settings::getCValue('mail_remind_signup_enabled'));

				$desc = 'Send a reminder to signees which didn\'t finish filling the sign-up form '
						 . 'after (<strong>{mail_remind_signup_min_age}</strong>) days. '
						 . '<i>Mail deduplication ({status_dedup})</i> can be applied. '
						 . 'Requires the setting <i>Sheet reminder mail ({status})</i> to be enabled.';
				$desc = Strings::__a(
					$desc,
					[
						'{mail_remind_signup_min_age}' => $mail_remind_signup_min_age,
						'{status}' => $status,
						'{status_dedup}' => $mail_remind_dedup
					]
				);
				break;
			case 4: // CronExportToApi
				$api_export_url = Settings::getCValue('api_export_url');

				$status = $api_export_url ? 'enabled and set to "{url}"' : '<span class="error">disabled</span>';
				$status = Strings::__a($status, ['{url}' => $api_export_url]);
				$desc  = 'Export sign-up data to a REST API. '
						  . 'Requires the setting <i>API URL (' . $status . ')</i>.';
				break;
		}
		return $desc;
	}

	protected function getStatusLabel(bool $status): string
	{
		$label = Strings::__a($status ? 'enabled' : 'disabled');
		return '<span class="'.($status ? 'success' : 'error').'">' . $label . '</span>';
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
	 * @param string|null $msg
	 * @param bool        $success
	 */
	public function setStateMessage(?string $msg = null, bool $success = true): void
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
		Core::logMessage('Cron ' . $this->getClassName() . ' cancelled by user "' . Infos::getUserName()
						 . '"', 'notice');
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