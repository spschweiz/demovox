<?php

namespace Demovox;
/**
 * @var AdminPages $this
 * @var bool       $isSent
 * @var string     $mailTo
 * @var            $connectionLog
 */
?>
<h3><?= $isSent ? 'Mail sent to ' . $mailTo : 'Sending failed' ?></h3>
<h4>Logs</h4>
<pre><?= Strings::nl2br($connectionLog) ?></pre>