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
	<h3><?= __('Cron', 'demovox.admin') ?></h3>
	<p><?= __('A cron manager plugin is recommended for detailed wordpress cron configuration', 'demovox.admin') ?></p>
	<?php
	foreach ($allCrons as $cron) {
		$dateStart   = $cron->getStatusDateStart();
		$dateStop    = $cron->getStatusDateStop();
		$lastSkipped = $cron->getStatusSkipped();
		$lastMessage = $cron->getStatusMessage();
		$lastSuccess = $cron->getStatusSuccess();
		$urlRun      = Strings::getAdminUrl(
			'/admin-post.php?cron=' . $cron->getCronId() . '&cln=' . $collectionId,
			'demovox_run_cron'
		);
		$urlCancel   = Strings::getAdminUrl(
			'/admin-post.php?cron=' . $cron->getCronId() . '&cln=' . $collectionId,
			'demovox_cancel_cron'
		);
		?>
		<h4><?= $cron->getName() ?></h4>
		<?php if ($description = $cron->getDescription()): ?>
			<p><?= $description ?></p>
		<?php endif; ?>
		<p>
		<table class="wp-list-table widefat striped table-view-list cron">
			<tr>
				<td style="width: 180px">
					<?= __('Status:', 'demovox.admin') ?>
				</td>
				<td>
					<?php
					if ($cron->isRunning()) :
						?>
						<?= __('currently running', 'demovox.admin') ?>
						<button class="ajaxButton" data-ajax-url="<?= $urlCancel ?>"
								data-confirm="<?= __('Did you wait until the job has stopped running?', 'demovox.admin') ?>"
								data-container=".ajaxCancelContainer">
							<?= __('Mark as stopped', 'demovox.admin') ?>
						</button><span class="ajaxCancelContainer"></span>
					<?php else: ?>
						<span class="success"><?= __('finished', 'demovox.admin') ?></span>
					<?php
					endif;
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?= __('Last started:', 'demovox.admin') ?>
				</td>
				<td>
					<?= $dateStart ? date('d.m.Y G:i:s', $dateStart) : '-' ?>
				</td>
			</tr>
			<tr>
				<td>
					<?= __('Last ended:', 'demovox.admin') ?>
				</td>
				<td>
					<?= $dateStop ? date('d.m.Y G:i:s', $dateStop) : '-' ?>
				</td>
			</tr>
			<?php if ($lastSkipped) : ?>
				<tr>
					<td>
						<?= __('Last skipped execution:', 'demovox.admin') ?>
					</td>
					<td>
						<?= date('d.m.Y G:i:s', $lastSkipped) ?> (Reason: <?= $lastMessage ?>)
					</td>
				</tr>
			<?php elseif ($lastMessage): ?>
				<tr>
					<td>
						<?= __('Last status:', 'demovox.admin') ?>
					</td>
					<td>
						<?=
						($lastSuccess ? '<span class="success">success' : '<span class="error">error')
						. '</span>: ' . $lastMessage;
						?>
					</td>
				</tr>
			<?php
			endif;
			?>
			<tr>
				<td>
					<?= __('Start manually:', 'demovox.admin') ?>
				</td>
				<td>
					<button class="ajaxButton" data-ajax-url="<?= $urlRun ?>">
						<?= __('Run now', 'demovox.admin') ?>
					</button>
					<span class="ajaxContainer"></span>
				</td>
			</tr>
		</table>
		<?php
	}
	?>
</div>
