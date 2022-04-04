<?php

namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var CronBase[]   $allCrons
 * @var int          $collectionId
 * @var string       $collectionName
 */

?>
<div class="wrap demovox">
	<h2><?= $collectionName ?></h2>
	<h3>Cron</h3>
	<p>A cron manager plugin is recommended for detailed wordpress cron configuration</p>
	<?php
foreach ($allCrons as $cron) {
	$dateStart   = $cron->getStatusDateStart();
	$dateStop    = $cron->getStatusDateStop();
	$lastSkipped = $cron->getStatusSkipped();
	$lastMessage = $cron->getStatusMessage();
	$lastSuccess = $cron->getStatusSuccess();
	$urlRun      = Strings::getAdminUrl('/admin-post.php?cron=' . $cron->getCronId() . '&cln='
										. $collectionId, 'demovox_run_cron');
	$urlCancel   = Strings::getAdminUrl('/admin-post.php?cron=' . $cron->getCronId() . '&cln='
										. $collectionId, 'demovox_cancel_cron');
	?>
	<h4><?= $cron->getName() ?></h4>
	<?php if ($description = $cron->getDescription()): ?>
		<p><?= $description ?></p>
	<?php endif; ?>
	<p>
	<table>
		<tr>
			<td>
				Status:
			</td>
			<td>
				<?php
				if ($cron->isRunning()) :
					?>currently running
					<button class="ajaxButton" data-ajax-url="<?= $urlCancel ?>" data-confirm="Did you wait until the job has stopped running?"
							data-container=".ajaxCancelContainer">
						Mark as stopped
					</button><span class="ajaxCancelContainer"></span>
				<?php else: ?>
					finished
				<?php
				endif;
				?>
			</td>
		</tr>
		<tr>
			<td>
				Last started:
			</td>
			<td>
				<?= $dateStart ? date('d.m.Y G:i:s', $dateStart) : '-' ?>
			</td>
		</tr>
		<tr>
			<td>
				Last ended:
			</td>
			<td>
				<?= $dateStop ? date('d.m.Y G:i:s', $dateStop) : '-' ?>
			</td>
		</tr>
		<?php if ($lastSkipped) : ?>
			<tr>
			<td>
				Last skipped execution:
			</td>
			<td>
				<?= date('d.m.Y G:i:s', $lastSkipped) ?> (Reason: <?= $lastMessage ?>)
			</td>
			<td>
		<?php elseif ($lastMessage): ?>
			<tr>
				<td>
					Last status:
				</td>
				<td>
					<?=
					($lastSuccess ? '<span class="success">success' : '<span class="error">error') . '</span>: '
					. $lastMessage;
					?>
				</td>
			</tr>
		<?php
		endif;
		?>
		<tr>
			<td>
				Start manually:
			</td>
			<td>
				<button class="ajaxButton" data-ajax-url="<?= $urlRun ?>">
					Run now
				</button>
				<span class="ajaxContainer"></span>
			</td>
		</tr>
	</table>
	<?php

}
?>
</div>
