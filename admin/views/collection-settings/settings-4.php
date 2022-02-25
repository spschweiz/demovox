<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
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
		demovoxAdminClass.showOnChecked($('#demovox_mail_confirmation_enabled'), $('.showOnMailConfirmEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_sheet_enabled'), $('.showOnMailRemindSheetEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_signup_enabled'), $('.showOnMailRemindSignupEnabled'));
		demovoxAdminClass.showOnVal($('#demovox_mail_method'), $('.showOnMethodSmtp'), 'smtp');
	})(jQuery);
</script>
<script>
    jQuery(document).ready(function(){
        jQuery('#demovox_mail_remind_max_date').datepicker({dateFormat: 'dd.mm.yy'});
        <?php
        foreach (i18n::getLangsEnabled() as $langId => $lang):
        ?>
        placeMce('#demovox_mail_confirm_body_<?= $langId ?>');
        <?php
        endforeach;
        ?>
    });
</script>