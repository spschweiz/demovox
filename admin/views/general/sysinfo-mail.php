<?php

namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var bool         $isSent
 * @var string       $mailTo
 * @var              $connectionLog
 */
?>
<?php if ($isSent): ?>
	<h3><?= strtr(__('Mail sent to {mail}', 'demovox.admin'), ['{mail}' => $mailTo]) ?></h3>
<?php else: ?>
	<h3><?= __('Sending failed', 'demovox.admin') ?></h3>
<?php endif; ?>
<h4><?= __('Logs', 'demovox.admin') ?></h4>
<pre><?= Strings::nl2br($connectionLog) ?></pre>