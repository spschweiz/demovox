<?php

namespace Demovox;

/**
 * @var $this AdminInstanceSettings
 * @var $page string
 */
?>
<?php
submit_button();
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . '../css/jquery-ui.min.css', [], $this->getVersion());

$this->loadTinymce();
?>
<script>
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.showOnChecked($('#demovox_mail_confirmation_enabled'), $('.showOnMailConfirmEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_sheet_enabled'), $('.showOnMailRemindSheetEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_signup_enabled'), $('.showOnMailRemindSignupEnabled'));
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