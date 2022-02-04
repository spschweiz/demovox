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
class RemindSignupTest extends TestBase
{
	public static function setUpBeforeClass()
	{
		Config::setValue('mail_remind_signup_min_age', 5);
		parent::setUpBeforeClass();
		self::createDbSign();
		self::createDbMails();
	}

	function test_pending_dedup()
	{
		Config::setValue('mail_remind_dedup', 1);
		$this->assertEquals('1', Config::getValue('mail_remind_dedup'));
		$reminder = new CronMailRemindSignup();
		$pending  = $reminder->getPending();
		$this->assertCount(5, $pending);
	}

	function test_pending_nodedup()
	{
		Config::setValue('mail_remind_dedup', 0);
		$this->assertEquals('0', Config::getValue('mail_remind_dedup'));
		$reminder = new CronMailRemindSignup();
		$pending  = $reminder->getPending();
		$this->assertCount(7, $pending);
	}
}