<?php

namespace Demovox;

/**
 * @var $this AdminCollectionSettings
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
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.hideOnVal($('#demovox_api_address_url'), $('.showOnApiAddress'), '');
		demovoxAdminClass.hideOnVal($('#demovox_api_export_url'), $('.showOnApiExport'), '');
	})(jQuery);
</script>