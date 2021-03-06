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
 * @var $this SignSteps
 * @var $guid string
 * @var $textOptin string
 * @var $isOptIn int
 */
?>
<div class="wrap">
	<form method="post" id="demovox_form_opt-in" class="demovox" action="<?= Infos::getRequestUri() ?>">
		<input name="action" type="hidden" value="demovox_optin">
		<input name="nonce" type="hidden" value="<?= Core::createNonce($this->nonceId) ?>">
		<input name="sign" type="hidden" value="<?= $guid ?>">
		<div id="demovox-grp-is_optin" class="form-group">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="1" id="demovox-is_optin" name="is_optin" <?= $isOptIn ? 'checked="checked"' : '' ?>>
				<label class="form-check-label" for="demovox-is_optin"><?= $textOptin ?></label>
			</div>
		</div>
		<div id="demovox-grp-submit" class="form-group">
			<input type="submit" id="demovox-ajax-button" class="form-submit btn btn-primary" value="<?= __('Continue', 'demovox') ?>" />
		</div>
	</form>
</div>