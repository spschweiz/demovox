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
 * @var $this SignSteps
 * @var $page string
 * @var $statusMsg string
 */
?>
<div class="wrap demovox">
	<h2>Send reminder</h2>
	<?= $statusMsg ?>
	<form method="post" action="<?= Infos::getRequestUri() ?>">
		<?php wp_nonce_field($page); ?>
		<input type="hidden" name="action" value="import">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="signMinAge">Required sign-up age</label></th>
				<td><input name="signMinAge" id="signMinAge" type="number" value="" size="40"></td>
			</tr>
			<tr>
				<th scope="row"><label for="signMinAge">Required sign-up age</label></th>
				<td><input name="signMinAge" id="signMinAge" type="number" value="" size="40"></td>
			</tr>
			<tr>
				<th scope="row"><label for="message">mail message</label></th>
				<td><textarea name="message" id="message" cols="50" rows="10" required=""></textarea></td>
			</tr>
			</tbody>
		</table>
		<?php
		submit_button('Import');
		?>
	</form>
</div>