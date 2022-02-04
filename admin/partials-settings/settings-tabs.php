<?php

namespace Demovox;

/**
 * @var $this AdminSettings
 * @var $page string
 * @var $tabs array
 * @var $currentTab array
 */
if (!defined('ABSPATH')) {
	exit;
}

$tabExists = isset($tabs[$currentTab]);
$currentTabLabel = isset($tabs[$currentTab]) ? $tabs[$currentTab] : '';

if (!$tabExists) {
	wp_safe_redirect(admin_url('admin.php?page=wc-settings'));
	exit;
}
?>
<script>
    function placeMce(selector) {
        tinyMCE.init({
            selector: selector,
            menubar: 'edit view insert format table',
            plugins: 'link lists charmap hr fullscreen media directionality paste textcolor colorpicker image media code preview table',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | charmap image link | fullscreen code preview table',
            image_advtab: true,
        });
    }
</script>
<div class="wrap demovox">
	<form method="post" id="mainform" action="options.php" enctype="multipart/form-data">
		<?php wp_nonce_field($page); ?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			foreach ($tabs as $tabId => $label) {
				echo '<a href="' . esc_html(admin_url('admin.php?page=demovoxSettings&tab=' . esc_attr($tabId))) . '" class="nav-tab ' . ($currentTab == $tabId ? 'nav-tab-active' : '') . '">' . esc_html($label) . '</a>';
			}
			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html($currentTabLabel); ?></h1>
		<?php
		$this->{'pageSettings' . $currentTab}();
		?>
	</form>
</div>