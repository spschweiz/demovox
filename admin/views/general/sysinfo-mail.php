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
	<h3><?= Strings::__a('Mail sent to {mail}', ['{mail}' => $mailTo]) ?></h3>
<?php else: ?>
	<h3><?= Strings::__a('Sending failed') ?></h3>
<?php endif; ?>
<h4><?= Strings::__a('Logs') ?></h4>
<pre><?= Strings::nl2br($connectionLog) ?></pre>