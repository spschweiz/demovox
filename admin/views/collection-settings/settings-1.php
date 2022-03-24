<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var int                     $collectionId
 */
?>
<?php
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>
<script>
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.hideOnVal($('demovox_<?= $collectionId ?>_optin_mode'), $('.hideOnOptinDisabled'), 'disabled');
	})(jQuery);
</script>