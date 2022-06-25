<?php

namespace Demovox;

class Honeypot
{
	protected bool $enabled;

	public function isEnabled(): bool
	{
		if (!isset($this->enabled)) {
			$this->enabled = boolval(Settings::getValue('form_honeypot'));
		}
		return $this->enabled;
	}

	public function validateForm(): bool
	{
		if (!$this->isEnabled()) {
			return true;
		}

		if ($this->checkHoneypot()) {
			if ($this->checkChallenge()) {
				$this->deactivate();
			} else {
				$this->activate();
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a captcha if honeypot is active
	 *
	 * @return string|null required captcha
	 */
	public function getChallenge(): ?string
	{
		if (!$this->isEnabled()) {
			return null;
		}

		return Core::getSessionVar('formHoneypotCaptcha');
	}

	/**
	 * @return string field name
	 */
	public function initMailFieldName(): string
	{
		if (!$this->isEnabled()) {
			return $this->getMailFieldNameStatic();
		}

		$mailFieldName = uniqid();
		Core::setSessionVar('mailFieldName', $mailFieldName);
		return $mailFieldName;
	}

	/**
	 * @return string|null field name if defined
	 */
	public function getMailFieldName(): ?string
	{
		if (!$this->isEnabled()) {
			return $this->getMailFieldNameStatic();
		}

		return Core::getSessionVar('mailFieldName');
	}

	public function getMailFieldNameStatic(): string
	{
		return 'email';
	}

	/**
	 * Check if honeypot has been triggered by user input now or before
	 *
	 * @return bool
	 */
	protected function checkHoneypot(): bool
	{
		$honeypotActive = !!Core::getSessionVar('formHoneypotCaptcha');
		if ($honeypotActive) {
			return true;
		}

		if (isset($_REQUEST['mail']) && $_REQUEST['mail'] !== '') {
			return true;
		}

		return false;
	}

	protected function checkChallenge(): bool
	{
		if (isset($_REQUEST['captcha']) && $_REQUEST['captcha'] !== '') {
			$honeypotCaptchaSolution = Core::getSessionVar('formHoneypotCaptchaSolution');
			if (intval($_REQUEST['captcha']) == $honeypotCaptchaSolution) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Activate honeypot by setting a (new) captcha challenge and answer
	 */
	protected function activate(): void
	{
		$first  = rand(1, 15);
		$op     = rand(0, 1);
		$opText = $op ? __('captcha_operator_plus') : __('captcha_operator_minus');
		$second = rand(1, $first - 1);

		$honeypotCaptcha         = $first . ' ' . $opText . ' ' . $second;
		$honeypotCaptchaSolution = $op ? ($first + $second) : ($first - $second);
		Core::setSessionVar('formHoneypotCaptcha', $honeypotCaptcha);
		Core::setSessionVar('formHoneypotCaptchaSolution', $honeypotCaptchaSolution);

		Core::logMessage('Client fell into honeypot trap', 'error');
	}

	protected function deactivate(): void
	{
		Core::setSessionVar('formHoneypotCaptcha', null);
		Core::setSessionVar('formHoneypotCaptchaSolution', null);
	}
}