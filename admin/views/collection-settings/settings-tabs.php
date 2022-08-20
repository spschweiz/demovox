<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var array                   $tabs
 * @var string                  $currentTab
 * @var int                     $collectionId
 * @var string                  $collectionName
 */
if (!defined('ABSPATH')) {
	exit;
}

$tabExists = isset($tabs[$currentTab]);
$currentTabLabel = $tabs[$currentTab] ?? '';

$enabledLanguages = i18n::getLangsEnabled();
if (!$tabExists) {
	wp_safe_redirect(admin_url('admin.php?page=wc-settings'));
	exit;
}
?>
<input type="hidden" id="cln" name="cln" value="<?= $collectionId ?>">
<div class="wrap demovox">
	<h1><?= $collectionName ?></h1>
	<?php
	if (!$enabledLanguages) {
		?>
		<span class="error">No languages enabled!</span><br/>
		It's essential to enable at least one language for your collection.
		<?php
	}
	?>
	<form method="post" id="mainform" action="options.php" enctype="multipart/form-data">
		<?php wp_nonce_field($page); ?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			foreach ($tabs as $tabId => $label) {
				$url = Strings::getAdminUrl('admin.php?page=demovoxSettings&tab=' . $tabId . '&cln=' . $collectionId);
				$class = 'nav-tab ' . ($currentTab == $tabId ? 'nav-tab-active' : '');
				echo '<a href="' . $url . '" class="' . $class . '">' . esc_html($label) . '</a>';
			}
			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html($currentTabLabel); ?></h1>
		<?php
		$this->{'pageSettings' . $currentTab}();
		?>
	</form>
</div>