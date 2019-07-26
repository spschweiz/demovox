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
	hideOnSet($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	showOnSet($('#demovox_mail_method'), $('.showOnMethodSmtp'), 'smtp');
	hideOnSet($('#demovox_api_address_url'), $('.showOnApiAddress'), '');
	hideOnSet($('#demovox_api_export_url'), $('.showOnApiExport'), '');
</script>