<?php

namespace Demovox;

/**
 * @var $this AdminSettings
 * @var $page string
 * @var $languages array
 */
?>
<?php
submit_button();
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>
<script>
	demovoxAdminClass.hideOnVal($('#demovox_use_page_as_success'), $('.showOnRedirect'), '');
	demovoxAdminClass.setOnVal($('#demovox_use_page_as_success'), $('#demovox_local_initiative_mode'), '', 'disabled');
	demovoxAdminClass.showOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCanton'), 'canton');
	demovoxAdminClass.showOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCommune'), 'commune');
	demovoxAdminClass.hideOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiative'), 'disabled');
	demovoxAdminClass.showOnChecked($('#demovox_swiss_abroad_allow'), $('.showOnSwissAbroadChecked'));
</script>