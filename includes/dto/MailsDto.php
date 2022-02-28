<?php

namespace Demovox;

/**
 * Dto for @DbMails
 */
class MailsDto extends Dto
{
	/** @var int */
	public int $ID, $sign_ID, $collection_ID;
	/** @var string */
	public string $mail_md5;
	/** @var int */
	public int $is_step2_done, $is_sheet_received, $state_remind_sheet_sent, $state_remind_signup_sent;
	/** @var string */
	public string $creation_date;

	/**
	 * Init new entry values before insert
	 * @return bool
	 */
	public function prepareInsert(): bool
	{
		if (!parent::prepareInsert()) {
			return false;
		}
		if (isset($this->mail)) {
			$hashedMail = Strings::hashMail($this->mail);
			$this->mail_md5 = $hashedMail;
		}
	}
}