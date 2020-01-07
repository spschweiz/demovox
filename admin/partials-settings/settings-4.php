<?php

namespace Demovox;

/**
 * @var $this AdminSettings
 * @var $page string
 */
?>
<?php
submit_button();
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>
<script>
	(function ($) {
		demovoxAdminClass.showOnChecked($('#demovox_mail_confirmation_enabled'), $('.showOnMailConfirmEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_sheet_enabled'), $('.showOnMailRemindSheetEnabled'));
		demovoxAdminClass.showOnChecked($('#demovox_mail_remind_signup_enabled'), $('.showOnMailRemindSignupEnabled'));
	})(jQuery);
</script>