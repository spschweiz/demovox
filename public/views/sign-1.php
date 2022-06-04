<?php
namespace Demovox;
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/public/partials
 */
/**
 * @var $this                SignSteps
 * @var $collectionId        int
 * @var $textOptin           int
 * @var $emailConfirmEnabled bool
 * @var $optinMode           string|null
 * @var $honeypotEnabled     bool
 * @var $honeypotPos         int
 * @var $honeypotCaptcha     string|null
 * @var $mailFieldName       string
 */
?>
<div class="wrap">
	<form method="post" id="demovox_form_1" class="demovox" action="<?= Infos::getRequestUri() ?>">
		<input name="action" type="hidden" value="demovox_step2">
		<input name="collection" type="hidden" value="<?= $collectionId ?>"">
		<input name="nonce" type="hidden" value="<?= Core::createNonce($this->nonceId) ?>">
		<div id="demovox-grp-name_first" class="form-group">
			<label for="demovox-name_first"><?= __('First Name', 'demovox') ?></label>
			<input name="name_first" id="demovox-name_first" autocomplete="given-name" class="form-control" type="text" minlength="1" maxlength="64"
			       required="" pattern="[^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~&lt;&gt;[\]]{1,}">
		</div>
		<div id="demovox-grp-name_last" class="form-group">
			<label for="demovox-name_last"><?= __('Last Name', 'demovox') ?></label>
			<input name="name_last" id="demovox-name_last" autocomplete="family-name" class="form-control" type="text" minlength="1" maxlength="64"
			       required="" pattern="[^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~&lt;&gt;[\]]{1,}">
		</div>
		<?php
		if($honeypotEnabled && $honeypotPos === 1) {
			include Infos::getPluginDir() . 'public/views/sign-1-honeypot.php';
		}
		include Infos::getPluginDir() . 'public/views/sign-1-mail.php';
		if ($honeypotEnabled && $honeypotPos === 2) {
			include Infos::getPluginDir() . 'public/views/sign-1-honeypot.php';
		}
		?>
		<div id="demovox-grp-phone" class="form-group">
			<label for="demovox-phone"><?= __('Phone number', 'demovox') ?></label>
			<input name="phone" id="demovox-phone" autocomplete="tel" class="form-control" type="text" minlength="10" maxlength="64"
				   pattern="((\+[1-9])|(0\d[1-9]))( |\d)+">
		</div>
		<?php
		if ($honeypotEnabled) {
			include Infos::getPluginDir() . 'public/views/sign-1-captcha.php';
		}
		?>
		<?php if ($optinMode) { ?>
			<div id="demovox-grp-is_optin" class="form-group">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" value="1" id="demovox-is_optin" name="is_optin"
						<?= ($optinMode === 'optInChk' || $optinMode === 'optOutChk') ? 'checked="checked"' : '' ?>>
					<label class="form-check-label" for="demovox-is_optin"><?= $textOptin ?></label>
				</div>
			</div>
		<?php } ?>
		<div id="demovox-grp-submit" class="form-group">
			<input type="submit" id="demovox-ajax-button" class="form-submit btn btn-primary" value="<?= __('Continue', 'demovox') ?>"/>
		</div>
	</form>
</div>
