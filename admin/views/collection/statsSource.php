<?php
namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var array        $sourceList
 */
?>
<table class="table table-striped table-hover">
	<tr>
		<th><?= Strings::__a('Source') ?></th>
		<th><?= Strings::__a('Signatures received') ?></th>
		<th><?= Strings::__a('Signed sheets received') ?></th>
		<th><?= Strings::__a('= signatures per sheet') ?></th>
		<th><?= Strings::__a('Opt-in form') ?></th>
		<th><?= Strings::__a('Opt-out form') ?></th>
		<th><?= Strings::__a('Unfinished form') ?></th>
		<th><?= Strings::__a('Historical chart') ?></th>
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
					<?= Strings::__a('Show') ?>
				</button>
			</td>
		</tr>
	<?php } ?>
</table>