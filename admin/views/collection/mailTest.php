<?php
namespace Demovox;
/**
 * @var AdminCollection    $this
 * @var int                $collectionId
 * @var string             $mailRecipient
 * @var string[]           $languages
 */
?>
<h2><?= __('Email config test', 'demovox.admin') ?></h2>
<p>
	<input type="hidden" id="cln" name="cln" value="<?= $collectionId ?>">
	<?php
	function createMailButton($langId, $language, $mailType = Mail::TYPE_CONFIRM)
	{
		$url = Strings::getAdminUrl(
			'/admin-post.php?lang=' . $langId . '&mailType=' . $mailType,
			'demovox_mail_test'
		);
		?>
		<button class="ajaxButton" data-ajax-url="<?= $url ?>">
			<?= $language ?>
			(<?= Settings::getCValueByLang('mail_from_address', $langId) ?: 'mail from address not set' ?>)
		</button>
		<?php
	}
	?>
	<?= strtr(__('Send test mails to {mail}.', 'demovox.admin'), ['{mail}' => $mailRecipient]) ?><br/>
	<?= __('Confirmation mail:', 'demovox.admin') ?>
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_CONFIRM);
	} ?><br/>
	<?= __('Sheet reminder mail:', 'demovox.admin') ?>
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_REMIND_SHEET);
	} ?>
	<br/>
	<?= __('Sign reminder mail:', 'demovox.admin') ?>
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_REMIND_SIGNUP);
	} ?>
	<br/>
	<span class="ajaxContainer"></span>
</p>