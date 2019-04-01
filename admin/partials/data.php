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
 * @var $countOptin int
 * @var $countFinished int
 * @var $countUnfinished int
 * @var $countDeleted int
 */
?>
<div class="wrap demovox">
	<h2>Download CSV</h2>
	<p>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=optin', 'get_csv') ?>">
			<button>All opt-in (<?= $countOptin ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=finished', 'get_csv') ?>">
			<button>Form input finished (<?= $countFinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=unfinished', 'get_csv') ?>">
			<button>Unfinished (<?= $countUnfinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=deleted', 'get_csv') ?>">
			<button>Deleted (<?= $countDeleted ?>)</button>
		</a>
	</p>
</div>