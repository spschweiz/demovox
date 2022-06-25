<?php
namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var array        $sourceList
 */
?>
<table class="table table-striped table-hover">
	<tr>
		<th><?= __('Source', 'demovox.admin') ?></th>
		<th><?= __('Signatures received', 'demovox.admin') ?></th>
		<th><?= __('Signed sheets received', 'demovox.admin') ?></th>
		<th><?= __('= signatures per sheet', 'demovox.admin') ?></th>
		<th><?= __('Opt-in form', 'demovox.admin') ?></th>
		<th><?= __('Opt-out form', 'demovox.admin') ?></th>
		<th><?= __('Unfinished form', 'demovox.admin') ?></th>
		<th><?= __('Historical chart', 'demovox.admin') ?></th>
	</tr>
	<?php foreach ($sourceList as $source) { ?>
		<tr>
			<td><b><?= $source->source ?></b></td>
			<td><?= $source->signatures ?></td>
			<td><?= $source->sheetsRec ?></td>
			<td><?= $source->sheetsRec ? number_format($source->signatures / $source->sheetsRec, 2) : 0 ?></td>
			<td><?= $source->optin ?></td>
			<td><?= $source->optout ?></td>
			<td><?= $source->unfinished ?></td>
			<td>
				<button class="ajaxButton"
						data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?source=' . $source->source, 'demovox_charts_stats') ?>"
						data-container=".ajaxContainerChart">
					<?= __('Show', 'demovox.admin') ?>
				</button>
			</td>
		</tr>
	<?php } ?>
</table>