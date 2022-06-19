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
 * @var $this      SignSteps
 * @var $permalink string
 * @var $title     string
 * @var $pdfUrl    string
 * @var $fields    string
 * @var $qrData    string
 */
?>
<div class="demovox" id="demovox-pdf">
	<div class="demovox-pdf-error hidden alert alert-danger"></div>
	<div class="demovox-pdf-loading">
		<div class="spinner-border spinner-border-sm" role="status">
			<span class="sr-only">Loading...</span>
		</div>
		<?= __('Preparing your signature sheet, please wait...', 'demovox') ?>
	</div>
	<div class="demovox-pdf-ok hidden">
		<div id="demovox-buttons">
			<?php if (Settings::getCValue('download_pdf')): ?>
				<button class="btn btn-success pdf-download"><?= __('Download signature sheet', 'demovox') ?></button>
			<?php endif;
			if (Settings::getCValue('print_pdf')) : ?>
				<button class="btn btn-success pdf-print"><?= __('Print signature sheet', 'demovox') ?></button>
			<?php endif; ?>
		</div>
		<?php if (Settings::getCValue('show_pdf')): ?>
			<iframe src="about:blank" class="pdf-iframe" type="application/pdf">PDF not yet ready</iframe>
		<?php endif; ?>
		<span id="demovox-permalink" style="display: none;"><?= $permalink; ?></span>
	</div>
	<div id="createPdf"
		 data-params="{title: '<?= $title ?>', pdfUrl: '<?= $pdfUrl ?>', fields: '<?= $fields ?>', qrData: '<?= $qrData ?>'}"
		 data-pdf-js-url="<?= plugin_dir_url(__FILE__) . 'js/demovox-public-pdf.min.js' ?>"></div>
	<noscript>
		JavaScript is disabled in your browser. Personalized signature sheet unavailable, you can use this one instead:<br/>
		<a href="<?= $pdfUrl ?>" class="btn btn-success pdf-download"><?= __('Download signature sheet', 'demovox') ?></a>
	</noscript>
</div>