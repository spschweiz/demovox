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
 * @var $pdfUrl string
 */
?>
<div class="demovox">
	<div id="demovox-buttons">
		<a href="<?= $pdfUrl ?>" class="button btn btn-primary" target="_blank"><?= __('Download signature sheet', 'demovox') ?></a>
	</div>
</div>