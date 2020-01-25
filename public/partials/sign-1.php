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
 * @var $textOptin int
 */
?>
<div class="wrap">
	<form method="post" id="demovox_form_1" class="demovox" action="<?= Infos::getRequestUri() ?>">
		<input name="action" type="hidden" value="demovox_step2">
		<input name="nonce" type="hidden" value="<?= Core::createNonce($this->nonceId) ?>">
		<div class="form-group">
			<label for="name_first"><?= __('First Name', 'demovox') ?></label>
			<input name="name_first" id="name_first" autocomplete="given-name" class="form-control" type="text" minlength="3" maxlength="64" required="" pattern="[\w'\-,.][^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~<>;:[\]]{2,}">
		</div>
		<div class="form-group">
			<label for="name_last"><?= __('Last Name', 'demovox') ?></label>
			<input name="name_last" id="name_last" autocomplete="family-name" class="form-control" type="text" minlength="3" maxlength="64" required="" pattern="[\w'\-,.][^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~<>;:[\]]{2,}">
		</div>
		<div class="form-group">
			<label for="mail"><?= __('Email', 'demovox') ?></label>
			<input name="mail" id="mail" autocomplete="email" class="form-control" type="email" maxlength="128" required="">
		</div>
		<div class="form-group">
			<label for="phone"><?= __('Phone number', 'demovox') ?></label>
			<input name="phone" id="phone" autocomplete="tel" class="form-control" type="text" minlength="10" maxlength="64" pattern="((\+[1-9])|(0\d[1-9]))( |\d)+">
		</div>
		<?php
		if($optinMode = $this->getOptinMode(1)) {
			?>
			<div class="form-row">
				<div class="form-group col">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="1" id="is_optin" name="is_optin" <?= ($optinMode === 'optInChk' || $optinMode === 'optOutChk') ? 'checked="checked"' : '' ?>>
						<label class="form-check-label" for="is_optin">
							<?= $textOptin ?>
						</label>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="form-group">
			<input type="submit" id="demovox-ajax-button" class="form-submit btn btn-primary" value="<?= __('Continue', 'demovox') ?>" />
		</div>
	</form>
</div>
