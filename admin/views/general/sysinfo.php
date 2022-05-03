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
	<h2><?= Strings::__a('System info') ?></h2>
	<h3><?= Strings::__a('Wordpress') ?></h3>
	<?php
	$wpErr = false;
	if ($encKey !== true || $hashKey !== true) {
		$wpErr = true;
		?>
		<h4 class="error"><?= Strings::__a('Keys need to be set in config to enable encryption or reminder mail') ?></h4>
		<p>
			<?= Strings::__a('You need to put the following line at the end of wp-config.php and backup them in a secure location:') ?>
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
			<?= Strings::__a('If this is a restored this wordpress instance with a previous demovox database, you '
							 . 'need to set the original encryption keys instead.') ?>
		</p>
		<?php
	}
	if (WP_DEBUG_DISPLAY) {
		$wpErr = true;
		?>
		<h4 class="error">
			<?= Strings::__a(
				'<a href="https://codex.wordpress.org/WP_DEBUG" target="_blank">WP_DEBUG_DISPLAY</a> '
				. 'is enabled, this could lead to the disclosure of sensitive information about the website '
				. 'and server setup'
			) ?>
		</h4>
		<?php
	}
	if ($saltsFailed) {
		$wpErr = true;
		?>
		<h4 class="error">Configured salts are insecure</h4>
		<p>
			<?= Strings::__a(
				'Please <a href="https://api.wordpress.org/secret-key/1.1/salt/">generate missing salts</a> '
				. 'and save them in wp-config.php. '
				. 'You might have to reset your Wordpress password and sign in again afterwards.'
			) ?>
		</p>
		<?php
	}
	if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
		$wpErr = true;
		?>
		<h4 class="error">
			<?= Strings::__a('DISABLE_WP_CRON is enabled, cronjobs like sending mails will <i>not</i> be executed') ?>
		</h4>
		<?php
	}
	if (!$wpErr) {
		?>
		<p><?= Strings::__a('Success: Wordpress config looks fine') ?></p>
	<?php } ?>
	<?php $path = dirname(ABSPATH) . '/wp-config.php' ?>
	<p><?= Strings::__a('You can find the Wordpress config here on your webserver: {path}', ['{path}' => $path]) ?></p>
	<h3>PHP</h3>
	<p>
		<?= Strings::__a('Optional feature "Hashids" requires a PHP module, either GMP or BC Math:') ?><br/>
		<?php
		if (extension_loaded('gmp')) {
			echo Strings::__a('GMP is installed and enabled');
		} else {
			echo Strings::__a('GMP is <i>not</i> available');
		}
		?>
		<br/>
		<?php
		if (extension_loaded('bcmath')) {
			echo Strings::__a('BC Math is installed and enabled');
		} else {
			echo Strings::__a('BC Math is <i>not</i> available');
		} ?>
	</p>
	<?php
	$phpErr = false;
	if ($phpDisplayErrors) {
		$phpErr = true;
		?>
		<h4 class="error">
			<?= Strings::__a(
				'PHP <a href="https://www.php.net/manual/en/errorfunc.configuration.php" target="_blank">'
				. 'display_errors</a> is enabled'
			) ?></h4>
		<p>
			<?= Strings::__a(
				'PHP display_errors stack traces can display the arguments passed to methods on the call stack.<br/> '
				. 'The value of encryption passwords and other personally sensitive data may be leaked out to an attacker.')
			?>
		</p>
		<?php
	}
	if (!$phpErr) {
		?>
		<p class="success"><?= Strings::__a('Success: PHP config looks fine.') ?></p>
	<?php } ?>
	<p><?= Strings::__a('You can find the PHP config here: {path}', ['{path}' => php_ini_loaded_file()]) ?></p>
	<h3><?= Strings::__a('SSL') ?></h3>
	<p>
		<?= Strings::__a(
			'Notice: The plugin form is not available when a client requests it over unencrypted HTTP, please '
			. 'make sure all requests are forwarded to HTTPS.<br/> You should also check the encryption quality,'
			. ' for example on <a href="https://www.ssllabs.com/ssltest/analyze.html?d={host}&hideResults=on" target="_blank">ssllabs.com</a>.',
			['{host}' => $_SERVER['HTTP_HOST']]
		) ?>
	</p>
	<h4><?= Strings::__a('CPU Load infos (not supported by Windows servers)') ?></h4>
	<?= Strings::__a(
		'Current load: {current}% / Absolute load: {absolute}%',
		['{current}' => Infos::getLoad(), '{absolute}' => Infos::getLoad(false)]
	) ?>
	<br/>
	<?= Strings::__a('Is high load (&gt; {max}%): ', ['{max}' => intval(Settings::getValue('cron_max_load'))]) ?>
	<?=
	Infos::isHighLoad()
		? Strings::__a('<span class="error">Yes</span> (would currently <i>NOT</i> execute CRON)')
		: Strings::__a('<span class="success">No</span> (would currently execute CRON)')
	?>
	<br/>
	<?= Strings::__a(
		'Cores setting: {cores} (Recommended cores setting: {recommended})',
		['{cores}' => intval(Settings::getValue('cron_cores')), '{recommended}' => Infos::countCores()]
	) ?>
	<br/>
	<?= Strings::__a('Current load is calculated by dividing absolute load by the amount of cores') ?>
	<h2><?= Strings::__a('Encryption performance') ?></h2>
	<h4><?= Strings::__a('php-encryption') ?></h4>
	<p>
		<?= Strings::__a('Realistic length fields:') ?>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=10', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '10']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=50', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '50']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '100']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=1000', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '1\'000']) ?>
		</button>
		<br/>
		Maxlength fields:
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=10&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '10']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=50&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '50']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '100']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=1000&fullLen=1', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations', ['{count}' => '1\'000']) ?>
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php?i=100&fullLen=1&showStrLen=1', 'demovox_encrypt_test') ?>">
			<?= Strings::__a('Test {count} iterations and show string lengths', ['{count}' => '100']) ?>
		</button>
		<br/>
		<?= Strings::__a('The test is executed single-threaded.') ?>
		<br/>
		<span class="ajaxContainer"></span>
	</p>
</div>