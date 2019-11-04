<?php
namespace Demovox;
/**
 * @var AdminPages $this
 * @var array      $sourceList
 */
?>
<table class="table table-striped table-hover">
	<tr>
		<th>Source</th>
		<th>Signatures</th>
		<th>Signed sheets received</th>
		<th>Opt-in</th>
		<th>Opt-out</th>
		<th>Unfinished</th>
	</tr>
	<?php foreach ($sourceList as $source) { ?>
		<tr>
			<td><b><?= $source->source ?></b></td>
			<td><?= $source->signatures ?></td>
			<td><?= $source->sheetsRec ?></td>
			<td><?= $source->optin ?></td>
			<td><?= $source->optout ?></td>
			<td><?= $source->unfinished ?></td>
		</tr>
	<?php } ?>
</table>