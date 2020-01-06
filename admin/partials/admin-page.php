<?php
namespace Demovox;
/**
 * @var AdminPages $this
 * @var int        $count
 * @var int        $addCount
 * @var string     $userLang
 * @var int        $countOptin
 * @var int        $countOptout
 * @var int        $countUnfinished
 */
?>
<div class="wrap demovox">
	<h2>demovox - Overview</h2>
	<p>
		<?php if ($userLang == 'fr') { ?>
			<a href="https://www.sp-ps.ch/fr" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo_fr.png"/>
			</a>
		<?php } elseif ($userLang == 'it') { ?>
			<a href="http://www.ps-ticino.ch/" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo_fr.png"/>
			</a>
		<?php } else { ?>
			<a href="http://www.sp-ps.ch/" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png"/>
			</a>
		<?php } ?>
	</p>
	<p>
		<b><?= $count ?></b> visitors have signed up
		<?php if ($addCount) { ?>
			(and additional <?= $addCount ?> signatures
			<a href="<?= admin_url('/admin.php?page=demovoxFields') ?>">in the settings</a>)
		<?php } ?>
	</p>
	<?php if (!$count) { ?>
		<h3>
			Don't forget to check the <a href="<?= admin_url('/admin.php?page=demovoxSysinfo') ?>">System info page</a> before publishing
			the plugin
		</h3>
	<?php } ?>
	<h3>Shortcodes</h3>
	<p>Global shortcodes: <code>[demovox_form]</code> <code>[demovox_count]</code></p>
	<p>Opt-in page and success pages shortcodes: <code>[demovox_form]</code> <code>[demovox_optin]</code> <code>[demovox_firstname]</code>
		<code>[demovox_lastname]</code></p>
	<?php if (current_user_can('demovox_stats') && $count) { ?>
		<h3>Sign-up stats</h3>
		<div class="row">
			<div class="col-md-5">
				<canvas id="pieChart"></canvas>
			</div>
		</div>
		<script>
			var dataPie = {
					labels: [
						'Opt-in',
						'Opt-out',
						'Unfinished'
					],
					datasets: [{
						data: [<?= $countOptin ?>,<?= $countOptout ?>,<?= $countUnfinished ?>],
						backgroundColor: [
							'rgba(0, 255, 99, 0.2)',
							'rgba(255, 206, 86, 0.2)',
							'rgba(255, 99, 132, 0.2)'
						],
						borderColor: [
							'rgba(0, 255, 99, 1)',
							'rgba(255, 206, 86, 1)',
							'rgba(255,99,132,1)'
						],
					}],
				},
				options = {},
				ctxPie = document.getElementById("pieChart");
			var pieChart = new demovoxChart(ctxPie, {
				type: 'pie',
				data: dataPie,
				options: options
			});
		</script>
		<p>
			<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php', 'demovox_source_stats') ?>">
				Source stats
			</button>
			<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php', 'demovox_charts_stats') ?>">
				Time based chart
			</button>
			<br/>
			<span class="ajaxContainer"></span>
		</p>
		<div class="row">
			<div class="col-md-10">
				<canvas id="dateChart"></canvas>
			</div>
		</div>
	<?php } ?>
</div>