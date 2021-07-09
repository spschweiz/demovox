<?php
namespace Demovox;
/**
 * @var AdminPages $this
 * @var string|null $source
 * @var array $datasets
 */
?>

<div class="row">
	<div class="col-md-11">
		<h4>Historical chart<?= $source !== null ? ' of source "' . $source . '"' : '' ?></h4>
		<canvas id="dateChart"></canvas>
		Date always refers to original registration
	</div>
</div>
<script>
	var data = {
			datasets: [
				<?php
				foreach ($datasets as $dataset){
				?>
				{
					label: '<?= $dataset['label'] ?>',
					borderColor: ['<?= $dataset['borderColor'] ?>'],
					backgroundColor: ['<?= $dataset['backgroundColor'] ?>'],
					data: [
						<?php
						foreach ($dataset['data'] as $row) {
							echo '{t:demovoxAdminClass.nDate(' . $row->date . '),y:' . intval($row->count) . '},';
						}
						?>
					],
					spanGaps: false
				},
				<?php
				}
				?>
			]
		},
		options = {
			scales: {
				xAxes: [{
					type: 'time',
					time: {
						minUnit: 'day',
					},
					distribution: 'linear',
					bounds: 'data',
					ticks: {
						source: 'data'
					}
				}],
				yAxes: [{
					ticks: {
						minUnit: 1,
						min: 0
					},
					spanGaps: false
				}]
			}
		};

	demovoxAdminClass.hideOnVal(jQuery('#demovox_optin_mode'), jQuery('.hideOnOptinDisabled'), 'disabled');
	var ctxDate = document.getElementById("dateChart"),
		dateChart = new demovoxChart(ctxDate, {
			type: 'line',
			data: data,
			options: options
		});
</script>