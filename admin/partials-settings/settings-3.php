<?php

namespace Demovox;

/**
 * @var $this      AdminSettings
 * @var $page      string
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
	(function ($) {
		demovoxAdminClass.showOnChecked($('#demovox_swiss_abroad_allow'), $('.showOnSwissAbroadChecked'));
		demovoxAdminClass.hideOnVal($('#demovox_field_qr_mode'), $('.showOnQr'), 'disabled');
	})(jQuery);
</script>