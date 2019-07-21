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
 * @var $signatureList SignatureList
 */
?>
<div class="wrap demovox">
	<h2>Download CSV</h2>
	<p>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=optin', 'demovox_get_csv') ?>">
			<button>All opt-in (<?= $countOptin ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=finished', 'demovox_get_csv') ?>">
			<button>Form input finished (<?= $countFinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=unfinished', 'demovox_get_csv') ?>">
			<button>Unfinished (<?= $countUnfinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=deleted', 'demovox_get_csv') ?>">
			<button>Deleted (<?= $countDeleted ?>)</button>
		</a>
	</p>
</div>
<div class="wrap">
	<h2>Signatures</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">
						<?php $signatureList->prepare_items(); ?>
						<input type="hidden" name="page" value="my_list_test" />
						<?php
						$signatureList->search_box('search', 'search_id');
						$signatureList->display();
						?>
					</form>
					<form method="post">
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>