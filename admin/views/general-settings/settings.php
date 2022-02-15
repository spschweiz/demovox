<?php

namespace Demovox;

/**
 * @var $this AdminInstanceSettings
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
		demovoxAdminClass.hideOnVal($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	})(jQuery);
</script>