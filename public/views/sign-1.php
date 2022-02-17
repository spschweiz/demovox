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
 * @var $this              SignSteps
 * @var $instance          int
 * @var $textOptin         int
 * @var $emailConfirmEnabled bool
 * @var $optinMode         string|null
 */
?>
<div class="wrap">
	<form method="post" id="demovox_form_1" class="demovox" action="<?= Infos::getRequestUri() ?>">
		<input name="action" type="hidden" value="demovox_step2">
		<input name="instance" type="hidden" value="<?= $instance ?>"">
		<input name="nonce" type="hidden" value="<?= Core::createNonce($this->nonceId) ?>">
		<div id="demovox-grp-name_first" class="form-group">
			<label for="demovox-name_first"><?= __('First Name', 'demovox') ?></label>
			<input name="name_first" id="demovox-name_first" autocomplete="given-name" class="form-control" type="text" minlength="1" maxlength="64"
			       required="" pattern="[\w'\-,.][^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~<>;:[\]]{1,}">
		</div>
		<div id="demovox-grp-name_last" class="form-group">
			<label for="demovox-name_last"><?= __('Last Name', 'demovox') ?></label>
			<input name="name_last" id="demovox-name_last" autocomplete="family-name" class="form-control" type="text" minlength="1" maxlength="64"
			       required="" pattern="[\w'\-,.][^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~<>;:[\]]{1,}">
		</div>
		<div id="demovox-grp-mail-cont" class="form-group">
			<div id="demovox-grp-mail" class="form-group<?= $emailConfirmEnabled ? ' col-md-6' : '' ?>">
				<label for="demovox-mail"><?= __('Email', 'demovox') ?></label>
				<input name="mail" id="demovox-mail" autocomplete="email" class="form-control" type="email" maxlength="128" required="">
			</div>
			<?php if($emailConfirmEnabled) { ?>
				<div id="demovox-grp-mail-validate" class="form-group col-md-6">
					<label for="demovox-mail-validate"><?= __('Confirm', 'demovox') ?></label>
					<input name="mail" id="demovox-mail-validate"  data-parsley-equalto="#demovox-mail" autocomplete="email" class="form-control" type="email" maxlength="128" required="">
				</div>
			<?php } ?>
		</div>
		<div id="demovox-grp-phone" class="form-group">
			<label for="demovox-phone"><?= __('Phone number', 'demovox') ?></label>
			<input name="phone" id="demovox-phone" autocomplete="tel" class="form-control" type="text" minlength="10" maxlength="64"
			       pattern="((\+[1-9])|(0\d[1-9]))( |\d)+">
		</div>
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
