<?php

namespace Demovox;

/**
 * @var $this AdminSettings
 * @var $page string
 */
?>
<?php
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>
<script>
	hideOnVal($('#demovox_optin_mode'), $('.hideOnOptinDisabled'), 'disabled');
</script>