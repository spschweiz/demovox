<?php

namespace Demovox;

/**
 * Mail deduplication table service
 */
class DbMails extends Db
{
	protected string $tableName = 'demovox_mails';
	protected string $tableDefinition = '
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          sign_ID bigint(20) UNSIGNED NOT NULL,
          instance int UNSIGNED NOT NULL,
          mail_md5 char(32) NOT NULL,
          creation_date datetime NOT NULL,
          is_step2_done tinyint(4) DEFAULT 0 NOT NULL,
          is_sheet_received tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_sheet_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_signup_sent tinyint(4) DEFAULT 0 NOT NULL,
          PRIMARY KEY (ID),
          UNIQUE KEY sign_ID_index (sign_ID),
          UNIQUE KEY mail_index (instance, mail_md5),
          INDEX creation_date_index (creation_date)';
	/**
	 * @param DtoSignatures $sign signature row
	 *
	 * @return string|false 'insert' | 'update' | 'skip' | false for db errors
	 */
	public function importRow($sign)
	{
		$updated    = false;
		$hashedMail = Strings::hashMail($sign->mail);
		$mailRow    = $this->getRow(
			[
				'ID',
				'creation_date',
				'is_step2_done',
				'is_sheet_received',
				'state_remind_sheet_sent',
				'state_remind_signup_sent',
			],
			"mail_md5 = '" . $hashedMail . "' AND instance = '" . $sign->instance . "'"
		);

		if (!$mailRow) {
			$mail = new DtoMails;
			$mail->instance = $sign->instance;
			$mail->sign_ID = $sign->ID;
			$mail->mail_md5 = $hashedMail;
			$mail->creation_date = $sign->creation_date;
			$mail->is_step2_done = $sign->is_step2_done ? 1 : 0;
			$mail->is_sheet_received = $sign->is_sheet_received ? 1 : 0;
			$mail->state_remind_sheet_sent = $sign->state_remind_sheet_sent;
			$mail->state_remind_signup_sent = $sign->state_remind_signup_sent;

			$save = $this->insert($mail);
		} else {
			if (!$mailRow->is_step2_done) {
				return 'skip';
			}
			$mail = new DtoMails;
			$mail->sign_ID = $sign->ID;
			$mail->creation_date = $sign->creation_date;
			if ($sign->is_step2_done) {
				$mail->is_step2_done = 1;
			}
			if (!$mailRow->is_sheet_received && $sign->is_sheet_received) {
				$mail->is_sheet_received = 1;
			}
			if ($mailRow->state_remind_sheet_sent !== 1 && $sign->state_remind_sheet_sent == 1) {
				$mail->state_remind_sheet_sent = 1;
			}
			if ($mailRow->state_remind_signup_sent !== 1 && $sign->state_remind_signup_sent == 1) {
				$mail->state_remind_signup_sent = 1;
			}
			$save    = $this->updateStatus($mail, ['ID' => $mailRow->ID]);
			$updated = true;
		}

		return $save ? ($updated ? 'update' : 'insert') : false;
	}
}