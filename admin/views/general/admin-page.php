<?php
namespace Demovox;
/**
 * @var AdminGeneral       $this
 * @var int                $count
 * @var int                $addCount
 * @var string             $userLang
 * @var CollectionStatsDto $stats
 * @var CollectionList     $collectionList
 */
?>
<div class="wrap demovox">
	<h2><?= __('demovox - Overview', 'demovox.admin') ?></h2>
	<p>
		<?php if ($userLang == 'fr') { ?>
			<a href="https://www.sp-ps.ch/fr" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo_fr.png"/>
			</a>
		<?php } elseif ($userLang == 'it') { ?>
			<a href="http://www.ps-ticino.ch/" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo_fr.png"/>
			</a>
		<?php } else { ?>
			<a href="http://www.sp-ps.ch/" target="_blank">
				<img src="https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png"/>
			</a>
		<?php } ?>
	</p>
	<p>
		<?= strtr(__('Overall, <b>{count}</b> visitors have signed up', 'demovox.admin'), ['{count}' => $count]) ?>
	</p>
	<?php if (!$count) { ?>
		<h3>
			<?= strtr(__('Don\'t forget to check the <a href="{url}">System info page</a> before publishing the plugin', 'demovox.admin'), ['{url}' => admin_url('/admin.php?page=demovoxSysinfo')]) ?>
		</h3>
	<?php } ?>
	<h3><?= __('Privacy reminder', 'demovox.admin') ?></h3>
	<p>
		<?= __(
			'You are responsible for protecting the date stored by this plugin, as signee information consists of '
			. '<a href="https://www.admin.ch/opc/en/classified-compilation/19920153/index.html#a3" target="_blank">'
			. 'sensitive personal data</a>.',
			'demovox.admin'
		) ?>
	</p>
	<p>
		<?= strtr(
			__(
				'For example by blocking any FTP access (unencrypted protocol!), protecting WordPress accounts with '
				. 'good passwords and <a href="https://en.wikipedia.org/wiki/Multi-factor_authentication" target="_blank">MFA</a>, '
				. 'disabling external access to the database, only installing trustworthy plugins and taking care of '
				. 'security updates. demovox has a recommended option to encrypt signee data (see "Advanced" tab) which '
				. 'helps on database attacks, and you can find some help to avoid WordPress misconfiguration on the '
				. '<a href="{url_sysinfo}">System info page</a>.',
				'demovox.admin'
			),
			['{url_sysinfo' => admin_url('/admin.php?page=demovoxSysinfo')]
		) ?>
	</p>
	<h3><?= __('Public shortcodes', 'demovox.admin') ?></h3>
	<p>
		<?= __(
			'Public shortcodes can be placed anywhere on your website, but they depend on a collection. Please use '
			. 'the shortcode in the collection list below. The shortcode <i>demovox_form</i> will show the form and when'
			. ' filled out the signature sheet, <i>demovox_count</i> the current signee count (number).',
			'demovox.admin'
		) ?>
	</p>
	<h3><?= __('Opt-in page and success page shortcodes', 'demovox.admin') ?></h3>
	<p>
		<?= __(
			'Opt-in page and success page shortcodes are dependent on a signature the visitor has given. '
			. 'Those pages are linked in mails or are forwarded to after signing (links include the URL parameter "sign"):',
			'demovox.admin'
		) ?>
		<br/>
		<?= __('Signature sheet:', 'demovox.admin') ?> <code>[demovox_form]</code>
		<br/>
		<?= __('Optin form:', 'demovox.admin') ?> <code>[demovox_optin]</code>
		<br/>
		<?= __('User information (strings):', 'demovox.admin') ?>
		<code>[demovox_firstname]</code> <code>[demovox_lastname]</code> <code>[demovox_street]</code>
		<code>[demovox_street_no]</code> <code>[demovox_zip]</code> <code>[demovox_city]</code>
		<code>[demovox_mail]</code>
	</p>
</div>
<div class="wrap demovox">
	<h3><?= __('Collections', 'demovox.admin') ?></h3>
	<?php if (Core::hasAccess('demovox_edit_collection')): ?>
		<p>
			<button class="ajaxButton"
					data-ajax-url="<?= Strings::getAdminUrl('/admin-post.php', 'demovox_collection_create') ?>">
				<?= __('Add new collection', 'demovox.admin') ?>
			</button>
			<span class="ajaxContainer"></span>
		</p>
	<?php endif; ?>
	<div id="poststuff">
		<div id="post-body-content">
			<div class="meta-box-sortables ui-sortable">
				<form method="post">
					<?php $collectionList->prepare_items(); ?>
					<input type="hidden" name="page" value="demovox"/>
					<?php
					$collectionList->search_box('search', 'search_id');
					$collectionList->display();
					?>
				</form>
			</div>
		</div>
		<br class="clear"/>
	</div>
</div>
<div class="wrap demovox">
	<?php
	include Infos::getPluginDir() . 'admin/views/general/stats.php';
	?>
</div>