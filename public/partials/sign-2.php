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
 * @var $textOptin         string
 * @var $titleEnabled      bool
 * @var $countries         array
 * @var $allowSwissAbroad  bool
 * @var $apiAddressEnabled bool
 * @var $cantons           array
 * @var $optinMode         string|null
 */
?>
<form method="post" id="demovox_form_2" class="demovox" action="<?= Infos::getRequestUri() ?>">
	<input name="action" type="hidden" value="demovox_step3">
	<input name="nonce" type="hidden" value="<?= Core::createNonce($this->nonceId) ?>">
	<?php if ($titleEnabled) { ?>
		<div id="demovox-grp-title" class="form-group">
			<label for="demovox-title"><?= __('Title', 'demovox') ?></label>
			<select name="title" id="demovox-title" class="form-control" required="" data-parsley-errors-container="#title-errors">
				<option value="Mister"><?= __('Mister', 'demovox') ?></option>
				<option value="Miss"><?= __('Miss', 'demovox') ?></option>
				<option value="-">- <?= __('No title', 'demovox') ?> -</option>
			</select>
			<div id="demovox-title-errors"></div>
		</div>
	<?php } ?>
	<div id="demovox-grp-birth_date" class="form-group">
		<label for="demovox-birth_date"><?= __('Birth date', 'demovox') ?></label>
		<input name="birth_date" id="demovox-birth_date" autocomplete="bday" class="form-control" type="text"
		       pattern="([0-2]?[0-9]|3[0-2])\.(0?[1-9]|1[0-2])\.\d{2,4}$" required="">
	</div>
	<?php if ($allowSwissAbroad) { ?>
		<div id="demovox-grp-swiss_abroad" class="form-group">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="1" id="demovox-swiss_abroad" name="swiss_abroad">
				<label class="form-check-label" for="demovox-swiss_abroad"><?= __('Swiss abroad', 'demovox') ?></label>
			</div>
		</div>
	<?php } ?>
	<div id="demovox-grp-street-street_no" class="form-row">
		<div id="demovox-grp-street" class="form-group col-md-8">
			<label for="demovox-street"><?= __('Street', 'demovox') ?></label>
			<input name="street" id="demovox-street" autocomplete="off" class="form-control" type="text" minlength="4" maxlength="127" required=""/>
		</div>
		<div id="demovox-grp-street_no" class="form-group col-md-4">
			<label for="demovox-street_no"><?= __('Street number', 'demovox') ?></label>
			<input name="street_no" id="demovox-street_no" autocomplete="off" class="form-control" type="text" maxlength="5" required=""/>
		</div>
	</div>
	<div id="demovox-grp-zip-city" class="form-row">
		<div id="demovox-grp-zip" class="form-group col-md-4">
			<label for="demovox-zip"><?= __('ZIP', 'demovox') ?></label>
			<input name="zip" id="demovox-zip" autocomplete="postal-code" class="form-control hideOnAbroad required" type="text" size="6"
			       minlength="4" maxlength="4" required="" data-parsley-type="integer"/>
			<?php if ($allowSwissAbroad) { ?>
				<input name="zip_abroad" id="demovox-zip" autocomplete="postal-code" class="form-control showOnAbroad required hidden" type="text"
				       size="6" minlength="4" maxlength="16"/>
			<?php } ?>
		</div>
		<div id="demovox-grp-city" class="form-group col-md-8">
			<label for="demovox-city"><?= __('City', 'demovox') ?></label>
			<?php if ($apiAddressEnabled) { ?>
				<select name="city" id="demovox-city" autocomplete="address-level2" class="form-control hideOnAbroad required" required=""
				        data-parsley-errors-container="#city-errors">
					<option></option>
				</select>
				<div id="demovox-city-errors"></div>
			<?php } else { ?>
				<input name="city" id="demovox-city" autocomplete="address-level2" class="form-control hideOnAbroad required" type="text"
				       minlength="2" maxlength="64" required=""/>
			<?php } ?>
			<?php if ($allowSwissAbroad) { ?>
				<input name="city_abroad" id="demovox-city" autocomplete="address-level2" class="form-control showOnAbroad required hidden"
				       type="text" minlength="2" maxlength="64"/>
			<?php } ?>
		</div>
	</div>
	<?php if ($allowSwissAbroad) { ?>
		<div id="demovox-grp-country" class="form-group showOnAbroad hidden">
			<label for="demovox-country"><?= __('Country', 'demovox') ?></label>
			<select name="country" id="demovox-country" class="form-control" autocomplete="country">
				<option></option>
			</select>
		</div>
	<?php } ?>
	<div id="demovox-grp-gde" class="form-row">
		<div id="demovox-grp-gde_name" class="form-group col-md-8">
			<label for="demovox-gde_name"><?= __('Political commune', 'demovox') ?></label>
			<input name="gde_id" id="demovox-gde_id" type="hidden" maxlength="5">
			<input name="gde_zip" id="demovox-gde_zip" type="hidden" maxlength="4">
			<?php if ($apiAddressEnabled) { ?>
				<select name="gde_name" id="demovox-gde_name" class="form-control" required="" data-parsley-errors-container="#gde_name-errors">
					<option></option>
				</select>
				<div id="demovox-gde_name-errors"></div>
			<?php } else { ?>
				<input name="gde_name" id="demovox-gde_name" class="form-control" type="text" minlength="2" maxlength="45" required=""/>
			<?php } ?>
		</div>
		<div id="demovox-grp-gde_canton" class="form-group col-md-4">
			<label for="demovox-gde_canton"><?= __('Canton', 'demovox') ?></label>
			<select name="gde_canton" id="demovox-gde_canton" class="form-control" required="" data-parsley-errors-container="#gde_canton-errors">
				<?php
				foreach ($cantons as $short => $long) {
					echo '<option value="' . $short . '">' . $long . '</option>';
				}
				?>
			</select>
			<div id="demovox-gde_canton-errors"></div>
		</div>
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
		<input type="submit" id="demovox-grp-ajax-button" class="form-submit btn btn-primary" value="<?= __('Continue', 'demovox') ?>"/>
	</div>
</form>
