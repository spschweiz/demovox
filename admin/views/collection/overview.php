<?php
namespace Demovox;
/**
 * @var AdminCollection    $this
 * @var CollectionStatsDto $stats
 * @var CollectionsDto     $collection
 * @var int                $count
 * @var int                $addCount
 * @var int                $collectionId
 * @var string             $collectionName
 * @var string             $mailRecipient
 * @var string[]           $languages
 * @var string             $page
 */
$allowEdit = Core::hasAccess('demovox_edit_collection');
$this->loadTinymce();
?>
<div class="wrap demovox">
	<h1><?= $collectionName ?></h1>
	<p>
		<?= Strings::__a('<b>{count}</b> visitors have signed up to this collection', ['{count}' => $count]) ?>
		<?php if ($addCount) {
			$settingsLink = Strings::getAdminLink('/admin.php?page=demovoxSettings', 'settings');
			?>
			<?= Strings::__a(
				'(and additional {count}</b> signatures in the {settings})',
				['{count}' => $count, '{settings}' => $settingsLink]
			) ?>
		<?php } ?>
	</p>
	<?php
	if (Core::hasAccess('demovox_edit_collection')):
	?>
	<form method="post" action="<?= Infos::getRequestUri() ?>">
		<?php wp_nonce_field($page); ?>
		<input type="hidden" name="action" value="<?= $page ?>">
		<input type="hidden" name="cln" value="<?= $collectionId ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="demovox_name"><?= $collection->getFieldName('name') ?></label></th>
				<td>
					<input name="name" id="demovox_name" size="40" value="<?= $collection->name ?>"
						   required="" <?= $allowEdit ? '' : 'readonly="readonly"' ?>>
					<p class="description"><?= Strings::__a('Internal collection name') ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="demovox_end_date"><?= $collection->getFieldName('end_date') ?></label></th>
				<td>
					<input name="end_date" id="demovox_end_date" value="<?= $collection->end_date ?: '' ?>"
						   size="40" <?= $allowEdit ? '' : 'readonly="readonly"' ?>>
					<p class="description"><?= Strings::__a('Last day the collection is available, leave empty to keep it active') ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="demovox_end_message"><?= $collection->getFieldName('end_message') ?></label>
				</th>
				<td>
					<textarea name="end_message" id="demovox_end_message" cols="40"
							  rows="5" <?= $allowEdit ? '' : 'readonly="readonly"' ?>
							  maxlength="255"><?= $collection->end_message ?></textarea>
					<p class="description"><?= Strings::__a('Message shown when collection has finished') ?></p></td>
			</tr>
			</tbody>
		</table>
		<?php
		if ($allowEdit) {
			submit_button(Strings::__a('Save'));
		}
		?>
	</form>
		<script>
			(function (jQuery) {
				window.$ = jQuery.noConflict();
				placeMce('#demovox_end_message');
			})(jQuery);
		</script>
	<?php
	endif;
	include Infos::getPluginDir() . 'admin/views/collection/stats.php';
	include Infos::getPluginDir() . 'admin/views/collection/mailTest.php';
	?>
</div>
<?php if ($allowEdit): ?>
	<?php $this->loadDatepicker(); ?>
	<script>
		jQuery(document).ready(function () {
			jQuery('#demovox_end_date').datepicker({dateFormat: 'dd.mm.yy'});
		});
	</script>
<?php endif; ?>