<?php
/**
 * @var $emailConfirmEnabled bool
 * @var $honeypot            bool
 * @var $honeypotCaptcha     string|null
 * @var $mailFieldName       string
 */
$label = str_replace(
	'{captcha}',
	$honeypotCaptcha ?: '',
	__('Please solve this task: <span class="task">{captcha}</span>', 'demovox')
);
?>
<div id="demovox-grp-<?= $mailFieldName ?>-cont" class="form-group">
	<div id="demovox-grp-<?= $mailFieldName ?>" class="form-group<?= $emailConfirmEnabled ? ' col-md-6' : '' ?>">
		<label for="demovox-<?= $mailFieldName ?>"><?= __('Email', 'demovox') ?></label>
		<input name="<?= $mailFieldName ?>" id="demovox-<?= $mailFieldName ?>" class="form-control" maxlength="128"
			   required="" <?= $honeypot ? '' : 'autocomplete="email"  type="email"' ?> pattern="^.+@.+\..+">
	</div>
	<?php if ($emailConfirmEnabled) { ?>
		<div id="demovox-grp-<?= $mailFieldName ?>-validate" class="form-group col-md-6">
			<label for="demovox-<?= $mailFieldName ?>-validate"><?= __('Confirm', 'demovox') ?></label>
			<input name="<?= $mailFieldName ?>-validate" id="demovox-<?= $mailFieldName ?>-validate"
				   class="form-control" maxlength="128" required=""
				   data-parsley-equalto="#demovox-<?= $mailFieldName ?>" <?= $honeypot ? '' : 'autocomplete="email"  type="email"' ?>>
		</div>
	<?php } ?>
</div>