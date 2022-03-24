<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var int                     $collectionId
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
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_api_address_url'), $('.showOnApiAddress'), '');
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_api_export_url'), $('.showOnApiExport'), '');
	})(jQuery);
</script>