<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var array                   $languages
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
		demovoxAdminClass.showOnChecked($('demovox_<?= $collectionId ?>_swiss_abroad_allow'), $('.showOnSwissAbroadChecked'));
		demovoxAdminClass.showOnChecked($('demovox_<?= $collectionId ?>_print_names_on_pdf'), $('.showOnPrintNamesChecked'));
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_field_qr_mode'), $('.showOnQr'), 'disabled');
	})(jQuery);
</script>