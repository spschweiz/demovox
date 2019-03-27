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
?>
<div class="wrap demovox">
	<p>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php', 'get_csv') ?>">
			<button>Download CSV</button>
		</a>
	</p>
</div>