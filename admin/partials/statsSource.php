<?php
namespace Demovox;
/**
 * @var AdminPages $this
 * @var array $sourceList
 */
?>
<table class="table table-striped table-hover">
	<tr>
		<th>Source</th>
		<th>Signatures received</th>
		<th>Signed sheets received</th>
		<th>= signatures per sheet</th>
		<th>Opt-in form</th>
		<th>Opt-out form</th>
		<th>Unfinished form</th>
		<th>Historical chart</th>
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
						data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?source=' . $source->source, 'demovox_charts_stats') ?>"
						data-container=".ajaxContainerChart">
					Show
				</button>
			</td>
		</tr>
	<?php } ?>
</table>