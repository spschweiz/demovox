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
	showOnSet($('#demovox_local_initiative_error_redirect'), $('.showOnLocalInitiativeNoredir'), '');
	showOnSet($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCanton'), 'canton');
	showOnSet($('#demovox_local_initiative_mode'), $('.showOnLocalInitiativeCommune'), 'commune');
	hideOnSet($('#demovox_local_initiative_mode'), $('.showOnLocalInitiative'), 'disabled');
	hideOnSet($('#demovox_api_export_url'), $('.showOnApiExport'), '');
</script>