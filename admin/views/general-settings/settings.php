<?php

namespace Demovox;

/**
 * @var $this AdminInstanceSettings
 * @var $page string
 */
?>
<div class="wrap demovox">
	<form method="post" id="mainform" action="options.php" enctype="multipart/form-data">
		<?php
		submit_button();
		settings_fields($page);
		$this->doSettingsSections($page);
		submit_button();
		?>
	</form>
</div>
<script>
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.hideOnVal($('#demovox_encrypt_signees'), $('.showOnEncrypt'), 'disabled');
	})(jQuery);
</script>