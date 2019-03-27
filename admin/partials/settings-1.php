<?php

namespace Demovox;

/**
 * @var $this Admin
 * @var $page string
 * @var $languages array
 */
?>
<div class="wrap demovox">
	<h2>Settings: signature sheet</h2>
	<form method="post" action="options.php">
		<?php
		submit_button();
		settings_fields($page);
		$this->doSettingsSections($page);
		submit_button();
		?>
	</form>
</div>
<script>
	hideOnSet($('#demovox_field_qr_mode'), $('.showOnQr'), 'disabled');
	showOnChecked($('#demovox_allow_swiss_abroad'), $('.showOnSwissAbroadChecked'));
</script>