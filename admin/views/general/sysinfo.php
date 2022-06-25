<?php
namespace Demovox;
/**
 * @var AdminGeneral $this
 * @var true|string  $encKey
 * @var true|string  $hashKey
 * @var bool         $saltsFailed
 * @var bool         $phpDisplayErrors
 */

?>
<div class="wrap demovox">
	<h2><?= __('System info', 'demovox.admin') ?></h2>
	<h3><?= __('Wordpress', 'demovox.admin') ?></h3>
	<?php
	$wpErr = false;
	if ($encKey !== true || $hashKey !== true) {
		$wpErr = true;
		?>
		<h4 class="error"><?= __('Keys need to be set in config to enable encryption or reminder mail', 'demovox.admin') ?></h4>
		<p>
			<?= __('You need to put the following line at the end of wp-config.php and backup them in a secure location:', 'demovox.admin') ?>
		</p>
		<pre><?php
			if ($encKey !== true) {
				echo "define('DEMOVOX_ENC_KEY', '{$encKey}');";
				if ($hashKey !== true) {
					echo "\n";
				}
			}
			if ($hashKey !== true) {
				echo "define('DEMOVOX_HASH_KEY', '{$hashKey}');";
			}
			?></pre>
		<p>
			<?= __(
				'If this is a restored this wordpress instance with a previous demovox database, you '
				. 'need to set the original encryption keys instead.',
				'demovox.admin'
			) ?>
		</p>
		<?php
	}
	if (WP_DEBUG_DISPLAY) {
		$wpErr = true;
		?>
		<h4 class="error">
			<?= __(
				'<a href="https://codex.wordpress.org/WP_DEBUG" target="_blank">WP_DEBUG_DISPLAY</a> '
				. 'is enabled, this could lead to the disclosure of sensitive information about the website '
				. 'and server setup',
				'demovox.admin'
			) ?>
		</h4>
		<?php
	}
	if ($saltsFailed) {
		$wpErr = true;
		?>
		<h4 class="error">Configured salts are insecure</h4>
		<p>
			<?= __(
				'Please <a href="https://api.wordpress.org/secret-key/1.1/salt/">generate missing salts</a> '
				. 'and save them in wp-config.php. '
				. 'You might have to reset your Wordpress password and sign in again afterwards.',
				'demovox.admin'
			) ?>
		</p>
		<?php
	}
	if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
		$wpErr = true;
		?>
		<h4 class="error">
			<?= __('DISABLE_WP_CRON is enabled, cronjobs like sending mails will <i>not</i> be executed', 'demovox.admin') ?>
		</h4>
		<?php
	}
	if (!$wpErr) {
		?>
		<p><?= __('Success: Wordpress config looks fine', 'demovox.admin') ?></p>
	<?php } ?>
	<?php $path = dirname(ABSPATH) . '/wp-config.php' ?>
	<p><?= strtr(__('You can find the Wordpress config here on your webserver: {path}', 'demovox.admin'), ['{path}' => $path]) ?></p>
	<h3>PHP</h3>
	<p>
		<?= __('Optional feature "Hashids" requires a PHP module, either GMP or BC Math:', 'demovox.admin') ?><br/>
		<?php
		if (extension_loaded('gmp')) {
			echo __('GMP is installed and enabled', 'demovox.admin');
		} else {
			echo __('GMP is <i>not</i> available', 'demovox.admin');
		}
		?>
		<br/>
		<?php
		if (extension_loaded('bcmath')) {
			echo __('BC Math is installed and enabled', 'demovox.admin');
		} else {
			echo __('BC Math is <i>not</i> available', 'demovox.admin');
		} ?>
	</p>
	<?php
	$phpErr = false;
	if ($phpDisplayErrors) {
		$phpErr = true;
		?>
		<h4 class="error">
			<?= __(
				'PHP <a href="https://www.php.net/manual/en/errorfunc.configuration.php" target="_blank">'
				. 'display_errors</a> is enabled',
				'demovox.admin'
			) ?></h4>
		<p>
			<?= __(
				'PHP display_errors stack traces can display the arguments passed to methods on the call stack.<br/> '
				. 'The value of encryption passwords and other personally sensitive data may be leaked out to an attacker.',
				'demovox.admin'
			)
			?>
		</p>
		<?php
	}
	if (!$phpErr) {
		?>
		<p class="success"><?= __('Success: PHP config looks fine.', 'demovox.admin') ?></p>
	<?php } ?>
	<p><?= strtr(__('You can find the PHP config here: {path}', 'demovox.admin'), ['{path}' => php_ini_loaded_file()]) ?></p>
	<h3><?= __('SSL', 'demovox.admin') ?></h3>
	<p>
		<?= strtr(
			__(
				'Notice: The plugin form is not available when a client requests it over unencrypted HTTP, please '
				. 'make sure all requests are forwarded to HTTPS.<br/> You should also check the encryption quality,'
				. ' for example on <a href="https://www.ssllabs.com/ssltest/analyze.html?d={host}&hideResults=on" target="_blank">ssllabs.com</a>.',
				'demovox.admin'
			),
			['{host}' => $_SERVER['HTTP_HOST']]
		) ?>
	</p>
	<h4><?= __('CPU Load infos (not supported by Windows servers)', 'demovox.admin') ?></h4>
	<?= strtr(__('Current load: {current}% / Absolute load: {absolute}%', 'demovox.admin'), [
		'{current}'  => Infos::getLoad(),
		'{absolute}' => Infos::getLoad(false),
	]) ?>
	<br/>
	<?= strtr(__('Is high load (&gt; {max}%): ', 'demovox.admin'), ['{max}' => intval(Settings::getValue('cron_max_load'))]) ?>
	<?=
	Infos::isHighLoad()
		? __('<span class="error">Yes</span> (would currently <i>NOT</i> execute CRON)', 'demovox.admin')
		: __('<span class="success">No</span> (would currently execute CRON)', 'demovox.admin')
	?>
	<br/>
	<?= strtr(__('Cores setting: {cores} (Recommended cores setting: {recommended})', 'demovox.admin'), [
		'{cores}'       => intval(Settings::getValue('cron_cores')),
		'{recommended}' => Infos::countCores(),
	]) ?>
	<br/>
	<?= __('Current load is calculated by dividing absolute load by the amount of cores', 'demovox.admin') ?>
	<h2><?= __('Encryption performance', 'demovox.admin') ?></h2>
	<h4><?= __('php-encryption', 'demovox.admin') ?></h4>
	<p>
		<?= __('Realistic length fields:', 'demovox.admin') ?>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=10', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '10']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=50', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '50']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '100']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=1000', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '1\'000']) ?>
		</button>
		<br/>
		Maxlength fields:
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=10&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '10']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=50&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '50']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '100']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=1000&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations', 'demovox.admin'), ['{count}' => '1\'000']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100&fullLen=1&showStrLen=1', 'demovox_encrypt_test') ?>">
			<?= strtr(__('Test {count} iterations and show string lengths', 'demovox.admin'), ['{count}' => '100']) ?>
		</button>
		<br/>
		<?= __('The test is executed single-threaded.', 'demovox.admin') ?>
		<br/>
		<span class="ajaxContainer"></span>
	</p>
</div>