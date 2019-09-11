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
	hideOnVal($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	showOnVal($('#demovox_mail_method'), $('.showOnMethodSmtp'), 'smtp');
	hideOnVal($('#demovox_api_address_url'), $('.showOnApiAddress'), '');
	hideOnVal($('#demovox_api_export_url'), $('.showOnApiExport'), '');
</script>