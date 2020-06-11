<?php

namespace Demovox;

class CronMailBase extends CronBase
{
	/** @var bool */
	protected $isDedup = false;
	/** @var int */
	protected $limitPerExecution;

	public function __construct()
	{
		$this->limitPerExecution = intval(Config::getValue('mail_max_per_execution')) ?: 300;

		if (Config::getValue('mail_remind_dedup')) {
			$importStatus = Core::getOption('cron_index_mail_status');
			if ($importStatus === false || $importStatus === CronMailIndex::STATUS_INIT) {
				$this->setSkipped('Reminder mail deduplication indexing (CronMailsRemindIndex) has not finished initial index yet');
				return false;
			}
			$this->isDedup = true;
		}

		return parent::__construct();
	}

	protected function prepareRunMailReminder(): bool
	{
		if (!$this->prepareRun()) {
			return false;
		}
		if (!$this->isReminderActive()) {
			$this->setSkipped('Reminder expired: "Last reminder date" lies in the past');
		}
		return true;
	}

	protected function isReminderActive()
	{
		$maxDate = Config::getValue('mail_remind_max_date');
		if (!$maxDate || !strtotime($maxDate)) {
			return true;
		}
		return time() < (strtotime($maxDate) + 24 * 60 * 60);
	}
}