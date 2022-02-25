<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
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
		demovoxAdminClass.hideOnVal($('#demovox_optin_mode'), $('.hideOnOptinDisabled'), 'disabled');
	})(jQuery);
</script>