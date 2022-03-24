<?php
namespace Demovox;
/**
 * @var AdminCollection    $this
 * @var int                $collectionId
 * @var string             $mailRecipient
 * @var string[]           $languages
 */
?>
<h2>Email config test</h2>
<p>
	<input type="hidden" id="cln" name="cln" value="<?= $collectionId ?>">
	<?php
	function createMailButton($langId, $language, $mailType = Mail::TYPE_CONFIRM)
	{
		?>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl(
					'/admin-post.php?lang=' . $langId . '&mailType=' . $mailType,
					'demovox_mail_test'
				) ?>">
			<?= $language ?>
			(<?= Settings::getCValueByLang('mail_from_address', $langId) ?: 'mail from address not set' ?>)
		</button>
		<?php
	}
	?>
	Send test mails to <?= $mailRecipient ?>.<br/>
	Confirmation mail:
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_CONFIRM);
	} ?><br/>
	Sheet reminder mail:
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_REMIND_SHEET);
	} ?>
	<br/>
	Sign reminder mail:
	<?php foreach ($languages as $langId => $language) {
		createMailButton($langId, $language, Mail::TYPE_REMIND_SIGNUP);
	} ?>
	<br/>
	<span class="ajaxContainer"></span>
</p>