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
 * @var SignSteps $this
 * @var string $textOptin
 * @var array $countries
 * @var bool $apiAddressEnabled
 * @var array $cantons
 * @var bool $allowSwissAbroad
 */
?>
<form method="post" id="demovox_form_2" class="demovox" action="options.php">
	<input name="action" type="hidden" value="demovox_step3">
	<div class="form-group">
		<label for="birth_date"><?= __('Birth date', 'demovox') ?></label>
		<input name="birth_date" id="birth_date" autocomplete="bday" class="form-control" type="text"
			   pattern="([0-2]?[0-9]|3[0-2])\.(0?[1-9]|1[0-2])\.\d{2,4}$" required="">
	</div>
	<?php if ($allowSwissAbroad) { ?>
		<div class="form-group">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="true" id="swiss_abroad" name="swiss_abroad">
				<label class="form-check-label" for="swiss_abroad">
					<?= __('Swiss abroad', 'demovox') ?>
				</label>
			</div>
		</div>
	<?php } ?>
	<div class="form-row">
		<div class="form-group col-md-8">
			<label for="street"><?= __('Street', 'demovox') ?></label>
			<input name="street" id="street" autocomplete="street-address" class="form-control" type="text" minlength="4" maxlength="127"
				   required=""/>
		</div>
		<div class="form-group col-md-4">
			<label for="street_no"><?= __('Street number', 'demovox') ?></label>
			<input name="street_no" id="street_no" class="form-control" type="text" maxlength="5" required=""/>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label for="zip"><?= __('ZIP', 'demovox') ?></label>
			<input name="zip" id="zip" autocomplete="postal-code" class="form-control hideOnAbroad required" type="text" size="6"
				   minlength="4" maxlength="4" required="" data-parsley-type="integer"/>
			<?php if ($allowSwissAbroad) { ?>
				<input name="zip_abroad" id="zip" autocomplete="postal-code" class="form-control showOnAbroad required hidden" type="text"
					   size="6" minlength="4" maxlength="16"/>
			<?php } ?>
		</div>
		<div class="form-group col-md-8">
			<label for="city"><?= __('City', 'demovox') ?></label>
			<?php if ($apiAddressEnabled) { ?>
				<select name="city" id="city" autocomplete="address-level2" class="form-control hideOnAbroad required" required=""
						data-parsley-errors-container="#city-errors">
					<option></option>
				</select>
				<div id="city-errors"></div>
			<?php } else { ?>
				<input name="city" id="city" autocomplete="address-level2" class="form-control hideOnAbroad required" type="text"
					   minlength="2" maxlength="64" required=""/>
			<?php } ?>
			<?php if ($allowSwissAbroad) { ?>
				<input name="city_abroad" id="city" autocomplete="address-level2" class="form-control showOnAbroad required hidden"
					   type="text" minlength="2" maxlength="64"/>
			<?php } ?>
		</div>
	</div>
	<?php if ($allowSwissAbroad) { ?>
		<div class="form-group showOnAbroad hidden" id="country-group">
			<label for="country"><?= __('Country', 'demovox') ?></label>
			<select name="country" id="country" class="form-control" autocomplete="country">
				<option></option>
			</select>
		</div>
	<?php } ?>
	<div class="form-row">
		<div class="form-group col-md-8">
			<label for="gde_name"><?= __('Political commune', 'demovox') ?></label>
			<input name="gde_id" id="gde_id" type="hidden" maxlength="5">
			<input name="gde_zip" id="gde_zip" type="hidden" maxlength="4">
			<?php if ($apiAddressEnabled) { ?>
				<select name="gde_name" id="gde_name" class="form-control" required="" data-parsley-errors-container="#gde_name-errors">
					<option></option>
				</select>
				<div id="gde_name-errors"></div>
			<?php } else { ?>
				<input name="gde_name" id="gde_name" class="form-control" type="text" minlength="2" maxlength="45" required=""/>
			<?php } ?>
		</div>
		<div class="form-group col-md-4">
			<label for="gde_canton"><?= __('Canton', 'demovox') ?></label>
			<select name="gde_canton" id="gde_canton" class="form-control" required="" data-parsley-errors-container="#gde_canton-errors">
				<?php
				foreach ($cantons as $short => $long) {
					echo '<option value="' . $short . '">' . $long . '</option>';
				}
				?>
			</select>
			<div id="gde_canton-errors"></div>
		</div>
	</div>
	<?php if ($optinMode = $this->getOptinMode(2)) { ?>
		<div class="form-group">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="true" id="is_optin" name="is_optin" <?= ($optinMode === 'optInChk'
					|| $optinMode === 'optOutChk') ? 'checked="checked"' : '' ?>>
				<label class="form-check-label" for="is_optin">
					<?= $textOptin ?>
				</label>
			</div>
		</div>
	<?php } ?>
	<div class="form-group">
		<input type="submit" id="demovox-ajax-button" class="form-submit" value="<?= __('Continue', 'demovox') ?>">
	</div>
</form>