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
<?= Strings::__a('{count} iterations.', ['{count}' => $iterations]) ?><br/>
<?= Strings::__a('Time to encrypt: {time}s', ['{time}' => $timediffEncrypt]) ?><br/>
<?= Strings::__a('Time to decrypt: {time}s', ['{time}' => $timediffDecrypt]) ?>

<?php if ($showStrLengths): ?>
	<h4><?= Strings::__a('Field lengths') ?></h4>
	<table class="table table-striped table-hover">
		<tr>
			<th><?= Strings::__a('Original') ?></th>
			<th><?= Strings::__a('Encrypted') ?></th>
		</tr>
		<?php foreach ($lengths as $length) { ?>
			<tr>
				<td><?= $length ?></td>
				<td><?= max(array_map('strlen', $enc[$length])) ?></td>
			</tr>
		<?php } ?>
	</table>
<?php endif; ?>