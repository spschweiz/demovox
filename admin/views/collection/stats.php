<?php
namespace Demovox;
/** @var CollectionStatsDto $stats */
?>
<?php if (current_user_can('demovox_stats') && $stats) { ?>
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
					'Opt-in unknown',
					'Unfinished'
				],
				datasets: [{
					data: [<?= $stats->countOptin ?>, <?= $stats->countOptout ?>, <?= $stats->countOptNULL ?>, <?= $stats->countUnfinished ?>],
					backgroundColor: [
						'rgba(0, 255, 99, 0.2)',
						'rgba(255, 206, 86, 0.2)',
						'rgba(68,78,255, 0.2)',
						'rgba(255, 99, 132, 0.2)'
					],
					borderColor: [
						'rgba(0, 255, 99, 1)',
						'rgba(255, 206, 86, 1)',
						'rgba(68,78,255, 1)',
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
			Historical chart
		</button>
		<br/>
		<span class="ajaxContainer"></span>
		<span class="ajaxContainerChart"></span>
	</p>
<?php } ?>