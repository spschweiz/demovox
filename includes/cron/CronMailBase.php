<?php

namespace Demovox;

abstract class CronMailBase extends CronBase
{
	/** @var bool */
	protected bool $isDedup = false;
	/** @var int */
	protected int $limitPerExecution;

	public function __construct(int $collectionId)
	{
		$this->limitPerExecution = intval(Settings::getValue('mail_max_per_execution')) ?: 300;

		if (Settings::getValue('mail_remind_dedup')) {
			$importStatus = Core::getOption('cron_index_mail_status');
			if ($importStatus === false || $importStatus === CronMailIndex::STATUS_INIT) {
				$this->setSkipped('Reminder mail deduplication indexing (CronMailsRemindIndex) has not finished initial index yet');
				return false;
			}
			$this->isDedup = true;
		}
		return parent::__construct($collectionId);
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

	protected function isReminderActive(): bool
	{
		$maxDate = Settings::getCValue('mail_remind_max_date');
		if (!$maxDate || !strtotime($maxDate)) {
			return true;
		}
		return time() < (strtotime($maxDate) + 24 * 60 * 60);
	}
}