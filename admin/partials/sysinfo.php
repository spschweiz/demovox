<?php
namespace Demovox;
/**
 * @var $this Admin
 * @var $configPath null|string
 * @var $encKey true|string
 * @var $saltsFailed bool
 * @var $phpShowErrors bool
 * @var $httpsEnforced bool
 * @var $httpStatus string
 * @var $httpRedirect string
 * @var $languages array
 * @var $mailRecipient string
 */

?>
<div class="wrap demovox">
	<h2>System info</h2>
	<h3>Wordpress</h3>
	<?php
	$wpErr = false;
	if ($encKey !== true) {
		$wpErr = true;
		?>
		<h4 style="color:red">Encryption key needs to be set in config</h4>
		<p>
			You need to put the following line at the end of wp-config.php:
		</p>
		<pre>define('DEMOVOX_ENC_KEY', '<?= $encKey ?>');</pre>
		<p>
			If you have restored this wordpress with the demovox database, you need to set the original encryption key.
		</p>
		<?php
	}
	if (WP_DEBUG) {
		$wpErr = true;
		?>
		<h4 style="color:red">WP_DEBUG is enabled</h4>
		<?php
	}
	if ($saltsFailed) {
		$wpErr = true;
		?>
		<h4 style="color:red">Configured salts are insecure</h4>
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
		<h4 style="color:red">DISABLE_WP_CRON is enabled, cronjobs like sending mails will not be executed</h4>
		<?php
	}
	if (!$wpErr) {
		?>
		<p>
			Success: WP config looks fine
		</p>
	<?php } else { ?>
		<h4>Config</h4>
		<p>You can find the Wordpress config here: <?= $configPath ?></p>
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
		<h4 style="color:red">show errors is enabled</h4>
		<p>
			PHP stack traces can display the arguments passed to methods on the call stack.<br/>
			The value of encryption passwords and other personally sensitive data may be leaked out to an attacker.</br>

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
	<?php
	if (!$httpsEnforced) {
		?>
		<h4 style="color:red">Un-encrypted connections are allowed - DO NOT USE THIS SERVER IN PRODUCTION</h4>
		<p>
			Wordpress can be accessed without a secured HTTPS connection. Personal signature data can be stolen
			easily.<br/>
			Tell your hoster to enable HTTPS (SSL) and an automatic redirection on the whole site from HTTP to
			HTTPS.<br/>
			All user passwords should be changed as they are considered unsafe.<br/>
			Technical info: calling the site with HTTP returned status code "<span><?= $httpStatus ?></span>" (30x
			expected),
			redirect url is "<span><?= $httpRedirect ?></span>" ("<?= 'https://' . $_SERVER['HTTP_HOST'] ?>" expected)
		</p>
		<?php
	} else {
		?>
		<p>
			Success: Wordpress is not accessible through unencrypted HTTP.<br/>
			<?php if ($httpRedirect) { ?>
				Success: Client forwarding from HTTP to HTTPS was recognized.<br/>
			<?php } else { ?>
				Warning: Client forwarding from HTTP (port 80) to HTTPS was not recognized.<br/>
			<?php } ?>
			You should also check the encryption, for example on
			<a href="https://www.ssllabs.com/ssltest/analyze.html?d=<?= $_SERVER['HTTP_HOST'] ?>&hideResults=on"
			   target="_blank">ssllabs.com</a>
		</p>
	<?php } ?>
	<h3>Cron</h3>
	<?php
	$cronNames = ManageCron::getAllCrons();
	foreach ($cronNames as $cronName) {
		$cron = $cronName;
		$dateStart = $cron->getRunningStart();
		$dateStop = $cron->getRunningStop();
		$lastSkipped = $cron->getSkipped();
		?>
		<h4><?= $cron->getName() ?></h4>
		<p>
			<button class="ajaxButton"
					data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?cron=' . $cron->getHookName(), 'run_cron') ?>">
				Run Now
			</button>
			<span class="ajaxContainer"></span>
			<br/>
			Status: <?php if ($cron->isRunning()) { ?>currently running
				<button class="ajaxButton"
						data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php', 'cancel_cron') ?>"
						data-confirm="Force cancel?" data-container=".ajaxCancelContainer">
					cancel execution
				</button><span class="ajaxCancelContainer"></span>
			<?php } else { ?>
				finished
			<?php } ?><br/>
			Last started: <?= $dateStart ? date('d.m.Y G:i:s', $dateStart) : '-' ?><br/>
			Last ended: <?= $dateStop ? date('d.m.Y G:i:s', $dateStop) : '-' ?><br/>
			<?php if ($lastSkipped) { ?>
				Last skipped execution: <?= date('d.m.Y G:i:s', $lastSkipped[0]) ?> (Reason: <?= $lastSkipped[1] ?>)
				<br/>
			<?php } ?>
		</p>
		<?php

	}
	?>
	<h4>CPU Load infos (not supported by Windows)</h4>
	Current load: <?= Infos::getLoad() ?>% / Absolute load: <?= Infos::getLoad(false) ?>%<br/>
	Is high load (&gt; <?= intval(Config::getValue('cron_max_load')) ?>%): <?=
	Infos::isHighLoad()
		? '<span style="color:red;font-weight: bold;">Yes</span> (would NOT execute CRON)'
		: '<span style="color:green;font-weight: bold;">No</span> (would execute CRON)' ?><br/>
	Recognized Cores: <?= Infos::countCores() ?> / Configured cores: <?= intval(Config::getValue('cron_cores')) ?>
	(this value is used to
	calculate current load from absolute load)
	<h2>Encryption performance</h2>
	<h4>php-encryption</h4>
	<p>
		Realistic length fields:
		<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=10', 'encrypt_test') ?>">
			Test 10 iterations
		</button>
		<button class="ajaxButton" data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=50', 'encrypt_test') ?>">
			Test 50 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100', 'encrypt_test') ?>">
			Test 100 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=1000', 'encrypt_test') ?>">
			Test 1'000 iterations
		</button>
		<br/>
		Maxlength fields:
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=10&fullLen=1', 'encrypt_test') ?>">
			Test 10 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=50&fullLen=1', 'encrypt_test') ?>">
			Test 50 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100&fullLen=1', 'encrypt_test') ?>">
			Test 100 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=1000&fullLen=1', 'encrypt_test') ?>">
			Test 1'000 iterations
		</button>
		<button class="ajaxButton"
				data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?i=100&fullLen=1&showStrLen=1', 'encrypt_test') ?>">
			Test 100 iterations and show string lengths
		</button>
		<br/>
		The test is executed single-threaded.
		<br/>
		<span class="ajaxContainer"></span>
	</p>
	<h2>Email config test</h2>
	<p>
		Send test mail to <?= $mailRecipient ?>
		<?php foreach ($languages as $langId => $language) { ?>
			<button class="ajaxButton"
					data-ajax-url="<?= Strings::getLinkAdmin('/admin-post.php?lang='.$langId, 'mail_test') ?>">
				<?= $language ?>
				(<?= Config::getValue('mail_reminder_from_address_' . $langId) ?: 'mail address missing' ?>)
			</button>
		<?php } ?>
		<br/>
		<span class="ajaxContainer"></span>
	</p>
</div>