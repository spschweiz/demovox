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
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_use_page_as_success'), $('.showOnRedirect'), '');
		demovoxAdminClass.setOnVal($('demovox_<?= $collectionId ?>_use_page_as_success'), $('demovox_<?= $collectionId ?>_local_initiative_mode'), '', 'disabled');
		demovoxAdminClass.showOnVal($('demovox_<?= $collectionId ?>_local_initiative_mode'), $('.showOnLocalInitiativeCanton'), 'canton');
		demovoxAdminClass.showOnVal($('demovox_<?= $collectionId ?>_local_initiative_mode'), $('.showOnLocalInitiativeCommune'), 'commune');
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_local_initiative_mode'), $('.showOnLocalInitiative'), 'disabled');
		demovoxAdminClass.showOnChecked($('demovox_<?= $collectionId ?>_swiss_abroad_allow'), $('.showOnSwissAbroadChecked'));
	})(jQuery);
</script>