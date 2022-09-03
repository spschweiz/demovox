<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var int                     $collectionId
 */
?>
<?php
submit_button();
settings_fields($page);
$this->doSettingsSections($page);
submit_button();

$this->loadDatepicker();
$this->loadTinymce();
?>
<script>
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.showOnChecked($('#demovox_<?= $collectionId ?>_mail_confirmation_enabled'), $('.showOnMailConfirmEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_<?= $collectionId ?>_mail_remind_sheet_enabled'), $('.showOnMailRemindSheetEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_<?= $collectionId ?>_mail_remind_signup_enabled'), $('.showOnMailRemindSignupEnabled'));
		demovoxAdminClass.showOnVal($('#demovox_<?= $collectionId ?>_mail_method'), $('.showOnMethodSmtp'), 'smtp');
	})(jQuery);
</script>
<script>
    jQuery(document).ready(function(){
        jQuery('#demovox_<?= $collectionId ?>_mail_remind_max_date').datepicker({dateFormat: 'dd.mm.yy'});
        <?php
        foreach (i18n::getLangsEnabled() as $langId => $lang):
        ?>
        placeMce('#demovox_<?= $collectionId ?>_mail_confirm_body_<?= $langId ?>');
        placeMce('#demovox_<?= $collectionId ?>_mail_remind_sheet_body_<?= $langId ?>');
        <?php
        endforeach;
        ?>
    });
</script>