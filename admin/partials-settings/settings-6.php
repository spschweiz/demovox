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
	demovoxAdminClass.hideOnVal($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	demovoxAdminClass.showOnVal($('#demovox_mail_method'), $('.showOnMethodSmtp'), 'smtp');
</script>