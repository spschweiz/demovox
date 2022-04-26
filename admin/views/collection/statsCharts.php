<?php
namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var string|null  $source
 * @var array        $datasets
 */
?>

<div class="row">
	<div class="col-md-11">
		<?php if ($source !== null): ?>
			<h4><?= Strings::__a('Historical chart of source "{source}"', ['{source}' => $source]) ?></h4>
		<?php else: ?>
			<h4><?= Strings::__a('Historical chart') ?></h4>
		<?php endif; ?>
		<canvas id="dateChart"></canvas>
		<?= Strings::__a('Date always refers to original registration') ?>
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