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
 * @var  AdminCollection $this
 * @var  string          $csvFormat
 * @var  string          $delimiter
 * @var  string          $page
 * @var  string          $statusMsg
 * @var  int             $collectionId
 */

?>
<div class="wrap demovox">
	<h2><?= __('Import received signature sheets', 'demovox.admin') ?></h2>
	<p>
		<?= __(
			'To send reminder mails and create statistics, you need to record the received signatures here. '
			. 'You need to scan the QR code from the sheets, we recommend to use a smartphone app '
			. 'which can export the scanned serials as CSV.',
			'demovox.admin'
		) ?>
	</p>
	<?= $statusMsg ?>
	<form method="post" action="<?= Infos::getRequestUri() ?>">
		<?php wp_nonce_field($page); ?>
		<input type="hidden" name="action" value="<?= $page ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="deliveryDate"><?= __('Delivery date', 'demovox.admin') ?></label></th>
				<td><input name="deliveryDate" id="deliveryDate" size="40" value="<?= date('d.m.Y') ?>" required="">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="csvFormat"><?= __('CSV format', 'demovox.admin') ?></label></th>
				<td>
					<?php
					$options = ['' => 'Please select', 1 => 'serial', 2 => 'serial' . $delimiter . 'count',];
					Strings::createSelect($options, $csvFormat, 'csvFormat', 'csvFormat')
					?>
				</td>
			</tr>
			<tr class="showOnFormat1">
				<th scope="row"><label for="signCount"><?= __('Number of signatures', 'demovox.admin') ?></label></th>
				<td><input name="signCount" id="signCount" type="number" value="" size="40">
					<p class="description"><?= __('Number of signatures on each of the currently importing sheets', 'demovox.admin') ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="delimiter"><?= __('Delimiter', 'demovox.admin') ?></label></th>
				<td><input name="delimiter" id="delimiter" type="text" value="<?= $delimiter ?>" size="3"></td>
			</tr>
			<tr>
				<th scope="row"><label for="csv"><?= __('CSV', 'demovox.admin') ?></label></th>
				<td><textarea name="csv" id="csv" cols="50" rows="10" required=""></textarea></td>
			</tr>
			</tbody>
		</table>
		<?php
		submit_button(__('Import', 'demovox.admin'));
		?>
	</form>
</div>
<?php
$this->loadDatepicker();
?>
<script>
	jQuery(document).ready(function () {
		jQuery('#deliveryDate').datepicker({dateFormat: 'dd.mm.yy'});
	});
	(function (jQuery) {
		window.$ = jQuery.noConflict();
		demovoxAdminClass.showOnVal($('#csvFormat'), $('.showOnFormat1'), '1');
	})(jQuery);
</script>