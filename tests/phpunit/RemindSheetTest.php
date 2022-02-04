<?php

namespace Demovox;
/**
 * Class SampleTest
 *
 * @package Demovox
 */

/**
 * Sample test case.
 */
class RemindSheetTest extends TestBase
{
	public static function setUpBeforeClass()
	{
		Config::setValue('mail_remind_sheet_min_age', 30);
		parent::setUpBeforeClass();
		self::createDbSign();
		self::createDbMails();
	}

	function test_pending_dedup()
	{
		Config::setValue('mail_remind_dedup', 1);
		$reminder = new CronMailRemindSheet();
		$pending  = $reminder->getPending();
		$this->assertCount(5, $pending); // fails when dedup happened
	}

	function test_pending_nodedup()
	{
		Config::setValue('mail_remind_dedup', 0);
		$reminder = new CronMailRemindSheet();
		$pending  = $reminder->getPending();
		$this->assertCount(7, $pending);
	}
}