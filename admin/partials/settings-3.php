<div class="wrap demovox">
	<h2>Settings: opt-in</h2>
	<form method="post" action="options.php">
		<?php
		settings_fields($page);
		$this->doSettingsSections($page);
		submit_button();
		?>
	</form>
</div>
<script>
	hideOnSet($('#demovox_optin_mode'), $('.hideOnOptinDisabled'), 'disabled');
</script>