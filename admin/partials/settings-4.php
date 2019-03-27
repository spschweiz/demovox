<div class="wrap demovox">
	<h2>Advanced settings</h2>
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
	hideOnSet($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	showOnSet($('#demovox_mail_method'), $('.showOnMethodSmtp'), 'smtp');
	hideOnSet($('#demovox_api_address_key'), $('.showOnApiAddress'), '');
	hideOnSet($('#demovox_api_export_key'), $('.showOnApiExport'), '');
</script>