<div class="wrap demovox">
	<h2>Settings: email messages</h2>
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
	showOnChecked($('#demovox_mail_reminder_enabled'), $('.showOnMailReminderEnabled'));
</script>