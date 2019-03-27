<?php

namespace Demovox;
/**
 * @var Admin $this
 * @var int $iterations
 * @var int $timediffEncrypt
 * @var int $timediffDecrypt
 * @var bool $showStrLengths
 * @var array $lengths
 * @var array $enc the encrypted strings
 */
?>
<?= $iterations ?> iterations.<br/>
Time to encrypt: <?= $timediffEncrypt; ?>s<br/>
Time to decrypt: <?= $timediffDecrypt ?>s

<?php if ($showStrLengths) { ?>
	<h4>Field lengths</h4>
	<table class="table table-striped table-hover">
		<tr>
			<th>Original</th>
			<th>Encrypted</th>
		</tr>
		<?php foreach ($lengths as $length) { ?>
			<tr>
				<td><?= $length ?></td>
				<td><?= max(array_map('strlen', $enc[$length])) ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>