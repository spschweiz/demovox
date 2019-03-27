<?php
namespace Demovox;
/**
 * @var Admin $this
 * @var string $datesetsOi
 * @var string $datesetsOo
 * @var string $datesetsC
 */
?>
<script>
	var
		data = {
			datasets: [
				{
					backgroundColor: [
						'rgba(0, 255, 99, 0.2)'
					],
					borderColor: [
						'rgba(0, 255, 99, 1)'
					],
					label: 'Opt-in',
					data: [<?= $datesetsOi ?>],
					spanGaps: false
				},
				{
					backgroundColor: [
						'rgba(255, 206, 86, 0.2)'
					],
					borderColor: [
						'rgba(255, 206, 86, 1)'
					],
					label: 'Opt-out',
					data: [<?= $datesetsOo ?>],
					spanGaps: false
				},
				{
					backgroundColor: [
						'rgba(255, 99, 132, 0.2)'
					],
					borderColor: [
						'rgba(255,99,132,1)'
					],
					label: 'Unfinished',
					data: [<?= $datesetsC ?>],
					spanGaps: false
				}
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

	$(function () {
		var  ctxDate = document.getElementById("dateChart"),
			dateChart = new Chart(ctxDate, {
				type: 'line',
				data: data,
				options: options
			});
		ctxDate.canvas.parentNode.style.height = '128px';
	});
</script>