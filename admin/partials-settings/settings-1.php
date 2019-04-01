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
	hideOnSet($('#demovox_field_qr_mode'), $('.showOnQr'), 'disabled');
	showOnChecked($('#demovox_allow_swiss_abroad'), $('.showOnSwissAbroadChecked'));
</script>