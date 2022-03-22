<?php
namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var CronBase[]   $allCrons
 */

?>
<h3>Cron</h3>
<p>A cron manager plugin is recommended for detailed wordpress cron configuration</p>
<?php
foreach ($allCrons as $cron) {
	$dateStart   = $cron->getStatusDateStart();
	$dateStop    = $cron->getStatusDateStop();
	$lastSkipped = $cron->getStatusSkipped();
	$lastMessage = $cron->getStatusMessage();
	$lastSuccess = $cron->getStatusSuccess();
	?>
	<h4><?= $cron->getName() ?></h4>
	<?php if ($description = $cron->getDescription()) { ?>
		<p><?= $description ?></p>
	<?php } ?>
	<p>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?cron=' . $cron->getId(), 'demovox_run_cron') ?>">
			Run now
		</button>
		<span class="ajaxContainer"></span>
		<br/>
		Status: <?php if ($cron->isRunning()) { ?>currently running
			<button class="ajaxButton"
					data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?cron=' . $cron->getId(), 'demovox_cancel_cron') ?>"
					data-confirm="Force cancel?" data-container=".ajaxCancelContainer">
				cancel execution
			</button><span class="ajaxCancelContainer"></span>
		<?php } else { ?>
			finished
		<?php } ?><br/>
		Last started: <?= $dateStart ? date('d.m.Y G:i:s', $dateStart) : '-' ?><br/>
		Last ended: <?= $dateStop ? date('d.m.Y G:i:s', $dateStop) : '-' ?><br/>
		<?php if ($lastSkipped) { ?>
			Last skipped execution: <?= date('d.m.Y G:i:s', $lastSkipped) ?> (Reason: <?= $lastMessage ?>)
			<br/>
		<?php } elseif ($lastMessage) {
			echo 'Last status: '
				 . ($lastSuccess ? '<span class="success">success' : '<span class="error">error') . '</span>: '
				 . $lastMessage;
		} ?>
	</p>
	<?php

}
?>