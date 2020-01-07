<?php
namespace Demovox;
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/public/partials
 */
/**
 * @var $this      SignSteps
 * @var $csvFormat string
 * @var $delimiter string
 * @var $page      string
 * @var $statusMsg string
 */

?>
<div class="wrap demovox">
	<h2>Import received signature sheets</h2>
	<p>
		To send reminder mails and create statistics, you need to record the received signatures here.
		You need to scan the QR code from the sheets, we recommend to use a smartphone app which can export the scanned serials as CSV.
	</p>
	<?= $statusMsg ?>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
		<?php wp_nonce_field($page); ?>
		<input type="hidden" name="action" value="import">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="deliveryDate">Delivery date</label></th>
				<td><input name="deliveryDate" id="deliveryDate" size="40" value="<?= date('d.m.Y') ?>" required="">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="csvFormat">CSV format</label></th>
				<td>
					<?php
					$options = ['' => 'Please select', 1 => 'serial', 2 => 'serial' . $delimiter . 'count',];
					Strings::createSelect($options, $csvFormat, 'csvFormat', 'csvFormat')
					?>
				</td>
			</tr>
			<tr class="showOnFormat1">
				<th scope="row"><label for="signCount">Number of signatures</label></th>
				<td><input name="signCount" id="signCount" type="number" value="" size="40">
					<p class="description">Number of signatures on each of the currently importing sheets</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="delimiter">Delimiter</label></th>
				<td><input name="delimiter" id="delimiter" type="input" value="<?= $delimiter ?>" size="3"></td>
			</tr>
			<tr>
				<th scope="row"><label for="csv">CSV</label></th>
				<td><textarea name="csv" id="csv" cols="50" rows="10" required=""></textarea></td>
			</tr>
			</tbody>
		</table>
		<?php
		submit_button('Import');
		?>
	</form>
</div>
<script>
	(function ($) {
		demovoxAdminClass.showOnVal($('#csvFormat'), $('.showOnFormat1'), '1');
	})(jQuery);
</script>