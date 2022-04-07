<?php
/**
 * @var $honeypotCaptcha string|null
 */
$label = str_replace(
	'{captcha}',
	$honeypotCaptcha ?: '',
	__('Please solve this task: <span class="task">{captcha}</span>', 'demovox')
);
?>
<div id="demovox-grp-captcha" class="form-group<?= $honeypotCaptcha ? '' : ' hidden' ?>">
	<label for="demovox-phone"><?= $label ?></label>
	<input name="captcha" id="demovox-captcha" autocomplete="off" class="form-control" type="text" minlength="1"
		   maxlength="64" <?= $honeypotCaptcha ? 'required=""' : '' ?>/>
</div>