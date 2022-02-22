<?php

namespace Demovox;

class DtoMails extends Dto
{
	/** @var int */
	public int $ID, $sign_ID, $instance;
	/** @var string */
	public string $mail_md5;
	/** @var int */
	public int $is_step2_done, $is_sheet_received, $state_remind_sheet_sent, $state_remind_signup_sent;
	/** @var string */
	public string $creation_date;

	public function __construct(array $parameters = [], $isNewRecord = true)
	{
		if (isset($parameters['mail'])) {
			$hashedMail = Strings::hashMail($parameters['mail']);
			$parameters['mail_md5'] = $hashedMail;
		}
		parent::__construct($parameters, $isNewRecord);
	}
}