<?php

namespace Demovox;

abstract class TestBase extends \WP_UnitTestCase
{
	/** @var DbSignatures */
	protected static DbSignatures $dbSign;
	/** @var DbMails */
	protected static DbMails $dbMails;

	protected static array $langs = ['de', 'fr', 'it', 'en'];
	protected static array $mailsDomain = ['hotmail.com', 'bluewin.ch', 'gmx.ch', 'web.de', 'romandie.com', 'yahoo.com', 'gmail.com'];
	protected static array $signaturesMeta = [
		// 1h
		[
			'first_name'              => 'Maria',
			'last_name'               => 'Müller',
			'creation_date_hours_ago' => 1,
		],
		[
			'first_name'              => 'Daniel',
			'last_name'               => 'Müller',
			'creation_date_hours_ago' => 1,
			'is_step2_done'           => 1,
		],

		// 24h
		[
			'first_name'              => 'Anna',
			'last_name'               => 'Schmid',
			'creation_date_hours_ago' => 24,
		],

		// 14d
		[
			'first_name'              => 'Peter',
			'last_name'               => 'Schmid',
			'creation_date_hours_ago' => 24,
			'is_step2_done'           => 1,
		],
		// RemindSignup remindSignupDedup
		[
			'first_name'              => 'Ursula',
			'last_name'               => 'Meier',
			'creation_date_hours_ago' => 14 * 24,
		],
		[
			'first_name'              => 'Thomas',
			'last_name'               => 'Meier',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
		],
		[
			'first_name'              => 'Sandra',
			'last_name'               => 'Keller',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
		],
		[
			'first_name'              => 'Hans',
			'last_name'               => 'Keller',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
		],
		[
			'first_name'              => 'Ruth',
			'last_name'               => 'Huber',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'is_outside_scope'        => 1,
		],
		[
			'first_name'              => 'Christian',
			'last_name'               => 'Huber',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'state_confirm_sent'      => 1,
		],
		[
			'first_name'              => 'Marie',
			'last_name'               => 'Rochat',
			'creation_date_hours_ago' => 14 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'state_remind_sheet_sent' => 1,
		],
		[
			'first_name'               => 'Jean',
			'last_name'                => 'Rochat',
			'creation_date_hours_ago'  => 14 * 24,
			'is_step2_done'            => 1,
			'is_sheet_received'        => 1,
			'state_remind_signup_sent' => 1,
		],

		// 32d
		// RemindSignup remindSignupDedup
		[
			'first_name'              => 'Francesca',
			'last_name'               => 'Bernasconi',
			'mail'                    => 'francesca.bernasconi@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
		],
		// RemindSignup (duplicate)
		[
			'first_name'              => 'Francesca',
			'last_name'               => 'Bernasconi',
			'mail'                    => 'francesca.bernasconi@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
		],
		// RemindSignup (duplicate)
		[
			'first_name'              => 'Francesca',
			'last_name'               => 'Bernasconi',
			'mail'                    => 'francesca.bernasconi@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
		],
		// RemindSignup remindSignupDedup
		[
			'first_name'              => 'Giuseppe',
			'last_name'               => 'Bernasconi',
			'creation_date_hours_ago' => 32 * 24,
			'state_remind_sheet_sent' => 1,
		],
		// remindSheet remindSheetDedup
		[
			'first_name'              => 'Maria',
			'last_name'               => 'Gerber',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'state_remind_sheet_sent' => -1,
		],
		[
			'first_name'              => 'Daniel',
			'last_name'               => 'Gerber',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'state_remind_sheet_sent' => 1,
		],
		// remindSheet remindSheetDedup
		[
			'first_name'              => 'Anna',
			'last_name'               => 'Odermatt',
			'mail'                    => 'anna.odermatt@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
		],
		// remindSheet (duplicate)
		[
			'first_name'              => 'Anna',
			'last_name'               => 'Odermatt',
			'mail'                    => 'anna.odermatt@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
		],
		// remindSheet (duplicate)
		[
			'first_name'              => 'Anna',
			'last_name'               => 'Odermatt',
			'mail'                    => 'anna.odermatt@bluewin.ch',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
		],
		[
			'first_name'              => 'Peter',
			'last_name'               => 'Odermatt',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
		],
		[
			'first_name'              => 'Ursula',
			'last_name'               => 'Burch',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'is_outside_scope'        => 1,
		],
		// remindSheetDedup
		[
			'first_name'              => 'Thomas',
			'last_name'               => 'Burch',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'is_outside_scope'        => 1,
		],
		// remindSignupDedup
		[
			'first_name'              => 'Sandra',
			'last_name'               => 'Arnold',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'state_confirm_sent'      => 1,
		],
		[
			'first_name'              => 'Hans',
			'last_name'               => 'Arnold',
			'creation_date_hours_ago' => 32 * 24,
			'is_step2_done'           => 1,
			'is_sheet_received'       => 1,
			'state_remind_sheet_sent' => 1,
		],
		[
			'first_name'               => 'Ruth',
			'last_name'                => 'Schmid',
			'creation_date_hours_ago'  => 32 * 24,
			'is_step2_done'            => 1,
			'is_sheet_received'        => 1,
			'state_remind_signup_sent' => 1,
		],
		[
			'first_name'              => 'Hans',
			'last_name'               => 'Schmid',
			'creation_date_hours_ago' => 32 * 24,
			'is_deleted'              => 1,
		],
	];

	public function __construct()
	{
		parent::__construct();

		self::$dbMails = new DbMails;
		self::$dbSign  = new DbSignatures;
	}

	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();
		self::truncateTables();
	}

	protected static function truncateTables(): void
	{
		self::$dbMails->truncate();
		self::$dbSign->truncate();
	}

	/**
	 * Create all mail deduplication entries, based on DbSignatures.
	 * This
	 */
	protected static function createDbMails(): void
	{
		Core::setOption('cron_index_mail_status', CronMailIndex::STATUS_RUNNING);
		$rows = self::$dbSign->getResults(
			[
				'ID',
				'collection',
				'mail',
				'creation_date',
				'is_step2_done',
				'is_sheet_received',
				'state_remind_sheet_sent',
				'state_remind_signup_sent',
			],
			'is_deleted = 0 AND is_outside_scope = 0'
		);
		foreach ($rows as $row) {
			$dbMailDd = new DbMails();
			$sign     = new SignaturesDto($row);
			$dbMailDd->importRow($sign);
		}
		Core::setOption('cron_index_mail_status', CronMailIndex::STATUS_FINISHED);
	}

	/**
	 * Generate signature data
	 */
	protected static function genSign($meta): array
	{
		$language = self::$langs[array_rand(self::$langs)];

		$meta['language'] = $language;
		if (!isset($meta['mail'])) {
			$mailsDomain  = self::$mailsDomain[array_rand(self::$mailsDomain)];
			$mail         = strtolower($meta['first_name'] . '.' . $meta['last_name']) . '@' . $mailsDomain;
			$meta['mail'] = $mail;
		}
		$meta['phone'] = '0' . rand(100000000, 999999999);
		return $meta;
	}

	protected static function createDbSign(): void
	{
		foreach (self::$signaturesMeta as $meta) {
			$signData = self::genSign($meta);
			$sign = new SignaturesDto($signData);
			$inserts = self::$dbSign->insert($sign);
			if (!$inserts) {
				error_log('No signatures were inserted');
			}
		}
	}
}