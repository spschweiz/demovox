<?php
namespace Demovox;
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/public/partials
 */
/**
 * @var $this SignSteps
 * @var $permalink string
 * @var $title string
 * @var $pdfUrl string
 * @var $fields string
 * @var $qrData string
 */

?>
<div class="demovox" id="demovox-pdf">
	<div id="demovox-buttons" class="form-row">
        <button class="btn btn-success col-md-12 pdf-download"><?= __('Download signature sheet', 'demovox') ?></button>
	</div>
	<?php if (Config::getValue('show_pdf')) { ?>
		<iframe src="about:blank" class="pdf-iframe" type="application/pdf">PDF not yet ready</iframe>
	<?php } ?>
	<span id="demovox-permalink" style="display: none;"><?= $permalink; ?></span>
	<script>
		jQuery(function () {
			window.createPdf(jQuery('#demovox-pdf'), '<?= $title ?>', '<?= $pdfUrl ?>', <?= $fields ?>, <?= $qrData ?>);
		});
	</script>
</div>