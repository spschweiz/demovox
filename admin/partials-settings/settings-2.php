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
	hideOnVal($('#demovox_use_page_as_success'), $('.showOnRedirect'), '');
	setOnVal($('#demovox_use_page_as_success'), $('#demovox_local_initiative_mode'), '', 'disabled');
	showOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCanton'), 'canton');
	showOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCommune'), 'commune');
	hideOnVal($('#demovox_local_initiative_mode'), $('.showOnLocalInitiative'), 'disabled');
	showOnChecked($('#demovox_swiss_abroad_allow'), $('.showOnSwissAbroadChecked'));
</script>