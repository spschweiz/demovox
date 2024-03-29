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
 * @var int             $collectionId
 * @var string          $collectionName
 * @var int[]           $count
 * @var SignatureList   $signatureList
 */
$url = '/admin-post.php?cln=' . $collectionId . '&type=';
?>
<div class="wrap demovox">
	<h1><?= $collectionName ?></h1>
	<h2><?= __('Download CSV', 'demovox.admin') ?></h2>
	<p>
		<a href="<?= Strings::getAdminUrl($url . DbSignatures::WHERE_OPTIN, 'demovox_get_csv') ?>">
			<button><?= strtr(__('All opt-in ({count})', 'demovox.admin'), ['{count}' => $count[DbSignatures::WHERE_OPTIN]]) ?></button>
		</a>
		<a href="<?= Strings::getAdminUrl($url . DbSignatures::WHERE_FINISHED_IN_SCOPE, 'demovox_get_csv') ?>">
			<button><?= strtr(__('Form input finished ({count})', 'demovox.admin'), ['{count}' => $count[DbSignatures::WHERE_FINISHED_IN_SCOPE]]) ?></button>
		</a>
		<a href="<?= Strings::getAdminUrl($url . DbSignatures::WHERE_FINISHED_OUT_SCOPE, 'demovox_get_csv') ?>">
			<button><?= strtr(__('Finished - Outside limited area ({count})', 'demovox.admin'), ['{count}' => $count[DbSignatures::WHERE_FINISHED_OUT_SCOPE]]) ?></button>
		</a>
		<a href="<?= Strings::getAdminUrl($url . DbSignatures::WHERE_UNFINISHED, 'demovox_get_csv') ?>">
			<button><?= strtr(__('Unfinished ({count})', 'demovox.admin'), ['{count}' => $count[DbSignatures::WHERE_UNFINISHED]]) ?></button>
		</a>
		<a href="<?= Strings::getAdminUrl($url . DbSignatures::WHERE_DELETED, 'demovox_get_csv') ?>">
			<button><?= strtr(__('Deleted ({count})', 'demovox.admin'), ['{count}' => $count[DbSignatures::WHERE_DELETED]]) ?></button>
		</a>
	</p>
	<h2><?= __('Signatures', 'demovox.admin') ?></h2>
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
		<?= __('Please note that encrypted entries will only be searched for by their serial.', 'demovox.admin') ?>
		<br class="clear">
	</div>
</div>