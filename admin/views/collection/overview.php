<?php
namespace Demovox;
/**
 * @var AdminGeneral       $this
 * @var CollectionStatsDto $stats
 * @var CollectionsDto     $collection
 * @var int                $count
 * @var int                $addCount
 * @var int                $collectionId
 * @var string             $page
 */
?>
<div class="wrap demovox">
	<p>
		<b><?= $count ?></b> visitors have signed up to this collection
		<?php if ($addCount) { ?>
			(and additional <?= $addCount ?> signatures
			<a href="<?= admin_url('/admin.php?page=demovoxFields') ?>">in the settings</a>)
		<?php } ?>
	</p>
	<form method="post" action="<?= Infos::getRequestUri() ?>">
		<?php wp_nonce_field($page); ?>
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="collection_ID" value="<?= $collectionId ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="name"><?= $collection->getFieldName('name') ?></label></th>
				<td>
					<input name="name" id="name" size="40" value="<?= $collection->name ?>" required="">
					<p class="description">Internal collection name</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="end_date"><?= $collection->getFieldName('end_date') ?></label></th>
				<td><input name="end_date" id="end_date" value="<?= $collection->end_date?:'' ?>" size="40">
					<p class="description">Last day the collection is available, leave empty to keep it active</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="end_message"><?= $collection->getFieldName('end_message') ?></label></th>
				<td><textarea name="end_message" id="end_message" cols="40" rows="5" maxlength="255"><?= $collection->end_message ?></textarea>
					<p class="description">Message shown when collection has finished</p></td>
			</tr>
			</tbody>
		</table>
		<?php
		submit_button('Save');
		?>
	</form>
	<?php
	include Infos::getPluginDir() . 'admin/views/collection/stats.php';
	?>
</div>
<?php
$this->loadDatepicker();
?>
<script>
	jQuery(document).ready(function() {
		jQuery('#end_date').datepicker({dateFormat: 'dd.mm.yy'});
	});
</script>