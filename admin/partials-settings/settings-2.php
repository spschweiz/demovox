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
	showOnChecked($('#demovox_mail_confirmation_enabled'), $('.showOnMailConfirmEnabled'));
	showOnChecked($('#demovox_mail_reminder_sheet_enabled'), $('.showOnMailRemindSheetEnabled'));
	showOnChecked($('#demovox_mail_reminder_form_enabled'), $('.showOnMailRemindFormEnabled'));
</script>