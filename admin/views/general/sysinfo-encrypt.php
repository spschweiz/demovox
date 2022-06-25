<?php

namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var int          $iterations
 * @var int          $timediffEncrypt
 * @var int          $timediffDecrypt
 * @var bool         $showStrLengths
 * @var array        $lengths
 * @var array        $enc the encrypted strings
 */
?>
<?= strtr(__('{count} iterations.', 'demovox.admin'), ['{count}' => $iterations]) ?><br/>
<?= strtr(__('Time to encrypt: {time}s', 'demovox.admin'), ['{time}' => $timediffEncrypt]) ?><br/>
<?= strtr(__('Time to decrypt: {time}s', 'demovox.admin'), ['{time}' => $timediffDecrypt]) ?>

<?php if ($showStrLengths): ?>
	<h4><?= __('Field lengths', 'demovox.admin') ?></h4>
	<table class="table table-striped table-hover">
		<tr>
			<th><?= __('Original', 'demovox.admin') ?></th>
			<th><?= __('Encrypted', 'demovox.admin') ?></th>
		</tr>
		<?php foreach ($lengths as $length) { ?>
			<tr>
				<td><?= $length ?></td>
				<td><?= max(array_map('strlen', $enc[$length])) ?></td>
			</tr>
		<?php } ?>
	</table>
<?php endif; ?>