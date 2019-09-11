<?php
namespace Demovox;
/**
 * @var $this Admin
 * @var $encKey true|string
 * @var $hashKey true|string
 * @var $saltsFailed bool
 * @var $phpShowErrors bool
 * @var $languages array
 * @var $mailRecipient string
 */

?>
<div class="wrap demovox">
	<h2>System info</h2>
	<h3>Wordpress</h3>
	<?php
	$wpErr = false;
	if ($encKey !== true || $hashKey !== true) {
		$wpErr = true;
		?>
		<h4 class="error">Keys need to be set in config to enable encryption or reminder mail</h4>
		<p>
			You need to put the following line at the end of wp-config.php and backup them in a secure location:
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
			If you have restored this wordpress with the demovox database, you need to set the original encryption key.
		</p>
		<?php
	}
	if (WP_DEBUG) {
		$wpErr = true;
		?>
		<h4 class="error">WP_DEBUG is enabled</h4>
		<?php
	}
	if ($saltsFailed) {
		$wpErr = true;
		?>
		<h4 class="error">Configured salts are insecure</h4>
		<p>
			Please <a href="https://api.wordpress.org/secret-key/1.1/salt/">generate missing salts</a> and save them in
			wp-config.php<br/>
			You might have to reset your Wordpress password and sign in again afterwards.
		</p>
		<?php
	}
	if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
		$wpErr = true;
		?>
		<h4 class="error">DISABLE_WP_CRON is enabled, cronjobs like sending mails will not be executed</h4>
		<?php
	}
	if (!$wpErr) {
		?>
		<p>
			Success: WP config looks fine
		</p>
	<?php } else { ?>
		<h4>Config</h4>
		<p>You can find the Wordpress config here: <?= dirname(ABSPATH) ?>/wp-config.php</p>
	<?php } ?>
	<h3>PHP</h3>
	<?php

	if (extension_loaded('gmp')) {
		echo "<p>GMP is installed</p>";
	} else {
		echo "<p>GMP is not available</p>";
	}

	if (extension_loaded('bcmath')) {
		echo "<p>BC Math is installed</p>";
	} else {
		echo "<p>BC Math is not available</p>";
	}
	$phpErr = false;
	if ($phpShowErrors) {
		$phpErr = true;
		?>
		<h4 class="error">show errors is enabled</h4>
		<p>
			PHP stack traces can display the arguments passed to methods on the call stack.<br/>
			The value of encryption passwords and other personally sensitive data may be leaked out to an attacker.
		</p>
		<?php
	}
	if (!$phpErr) {
		?>
		<p>
			Success: PHP config looks fine
		</p>
	<?php } ?>
	<h3>SSL</h3>
	<p>
		Notice: The plugin form is not available through unencrypted HTTP, please make sure the clients are forwarded to HTTPS.<br/>
		You should also check the encryption quality, for example on
		<a href="https://www.ssllabs.com/ssltest/analyze.html?d=<?= $_SERVER['HTTP_HOST'] ?>&hideResults=on"
		   target="_blank">ssllabs.com</a>.
	</p>
	<h3>Cron</h3>
	<p>cron manager plugin is recommended for detailed configuration</p>
	<?php
	$cronNames = ManageCron::getAllCrons();
	foreach ($cronNames as $cronName) {
		$cron = $cronName;
		$dateStart = $cron->getStausDateStart();
		$dateStop = $cron->getStatusDateStop();
		$lastSkipped = $cron->getStatusSkipped();
		$lastMessage = $cron->getStatusMessage();
		$lastSuccess = $cron->getStatusSuccess();
		?>
		<h4><?= $cron->getName() ?></h4>
		<p>
			<button class="ajaxButton"
					data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?cron=' . $cron->getHookName(), 'demovox_run_cron') ?>">
				Run Now
			</button>
			<span class="ajaxContainer"></span>
			<br/>
			Status: <?php if ($cron->isRunning()) { ?>currently running
				<button class="ajaxButton"
						data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?cron=' . $cron->getClassName(), 'demovox_cancel_cron') ?>"
						data-confirm="Force cancel?" data-container=".ajaxCancelContainer">
					cancel execution
				</button><span class="ajaxCancelContainer"></span>
			<?php } else { ?>
				finished
			<?php } ?><br/>
			Last started: <?= $dateStart ? date('d.m.Y G:i:s', $dateStart) : '-' ?><br/>
			Last ended: <?= $dateStop ? date('d.m.Y G:i:s', $dateStop) : '-' ?><br/>
			<?php if ($lastSkipped) { ?>
				Last skipped execution: <?= date('d.m.Y G:i:s', $lastSkipped) ?> (Reason: <?= $lastMessage ?>)
				<br/>
			<?php } elseif ($lastMessage) {
				echo 'Last status: '
					. ($lastSuccess ? '<span class="success">success' : '<span class="error">error') . '</span>: '
					. $lastMessage;
			} ?>
		</p>
		<?php

	}
	?>
	<h4>CPU Load infos (not supported by Windows)</h4>
	Current load: <?= Infos::getLoad() ?>% / Absolute load: <?= Infos::getLoad(false) ?>%<br/>
	Is high load (&gt; <?= intval(Config::getValue('cron_max_load')) ?>%): <?=
	Infos::isHighLoad()
		? '<span class="error">Yes</span> (would NOT execute CRON)'
		: '<span class="success">No</span> (would execute CRON)' ?><br/>
	Recognized Cores: <?= Infos::countCores() ?> / Configured cores: <?= intval(Config::getValue('cron_cores')) ?>
	(this value is used to
	calculate current load from absolute load)
	<h2>Encryption performance</h2>
	<h4>php-encryption</h4>
	<p>
		Realistic length fields:
		<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=10', 'demovox_encrypt_test') ?>">
			Test 10 iterations
		</button>
		<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=50', 'demovox_encrypt_test') ?>">
			Test 50 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100', 'demovox_encrypt_test') ?>">
			Test 100 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=1000', 'demovox_encrypt_test') ?>">
			Test 1'000 iterations
		</button>
		<br/>
		Maxlength fields:
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=10&fullLen=1', 'demovox_encrypt_test') ?>">
			Test 10 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=50&fullLen=1', 'demovox_encrypt_test') ?>">
			Test 50 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100&fullLen=1', 'demovox_encrypt_test') ?>">
			Test 100 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=1000&fullLen=1', 'demovox_encrypt_test') ?>">
			Test 1'000 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100&fullLen=1&showStrLen=1', 'demovox_encrypt_test') ?>">
			Test 100 iterations and show string lengths
		</button>
		<br/>
		The test is executed single-threaded.
		<br/>
		<span class="ajaxContainer"></span>
	</p>
	<h2>Email config test</h2>
	<p>

		<?php
		function createMailButton($langId, $language, $mailType = Mail::TYPE_CONFIRM){
		?>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?lang=' . $langId . '&mailType=' . $mailType, 'demovox_mail_test') ?>">
			<?= $language ?>
			(<?= Config::getValueByLang('mail_from_address', $langId) ?: 'mail from address not set' ?>)
		</button>
		<?php
		}
		?>
		Send test mails to <?= $mailRecipient ?>.<br/>
		Confirmation mail:
		<?php foreach ($languages as $langId => $language) {
			createMailButton($langId, $language, $mailType = Mail::TYPE_CONFIRM);
		} ?><br/>
		Sheet reminder mail:
		<?php foreach ($languages as $langId => $language) {
			createMailButton($langId, $language, $mailType = Mail::TYPE_REMIND_SHEET);
		} ?>
		<br/>
		Sign reminder mail:
		<?php foreach ($languages as $langId => $language) {
			createMailButton($langId, $language, $mailType = Mail::TYPE_REMIND_SIGNUP);
		} ?>
		<br/>
		<span class="ajaxContainer"></span>
	</p>
</div>