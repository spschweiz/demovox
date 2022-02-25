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
 * @var AdminCollection $this
 * @var int             $countOptin
 * @var int             $countFinished
 * @var int             $countOutsideScope
 * @var int             $countUnfinished
 * @var int             $countDeleted
 * @var SignatureList   $signatureList
 */
?>
<div class="wrap demovox">
	<h2>Download CSV</h2>
	<p>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=' . DbSignatures::WHERE_OPTIN, 'demovox_get_csv') ?>">
			<button>All opt-in (<?= $countOptin ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=' . DbSignatures::WHERE_FINISHED, 'demovox_get_csv') ?>">
			<button>Form input finished (<?= $countFinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=' . DbSignatures::WHERE_FINISHED_OUT_SCOPE, 'demovox_get_csv') ?>">
			<button>Finished - Outside limited area (<?= $countOutsideScope ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=' . DbSignatures::WHERE_UNFINISHED, 'demovox_get_csv') ?>">
			<button>Unfinished (<?= $countUnfinished ?>)</button>
		</a>
		<a href="<?= Strings::getLinkAdmin('/admin-post.php?type=deleted' . DbSignatures::WHERE_DELETED, 'demovox_get_csv') ?>">
			<button>Deleted (<?= $countDeleted ?>)</button>
		</a>
	</p>
</div>
<div class="wrap">
	<h2>Signatures</h2>
	<div id="poststuff">
		<div id="post-body-content">
			<div class="meta-box-sortables ui-sortable">
				<form method="post">
					<?php $signatureList->prepare_items(); ?>
					<input type="hidden" name="page" value="my_list_test"/>
					<?php
					$signatureList->search_box('search', 'search_id');
					$signatureList->display();
					?>
				</form>
			</div>
		</div>
		Please note that encrypted entries will only be searched for by their serial.
		<br class="clear">
	</div>
</div>