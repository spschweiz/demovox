<?php

namespace Demovox;

trait AdminScriptsTrait
{
	protected function loadDatepicker(): void
	{
		// load WP internal datepicker
		wp_enqueue_script('jquery-ui-datepicker');
		wp_localize_jquery_ui_datepicker();
		Core::addStyle($this->getPluginName(), 'public/css/demovox-public.min.css');
	}

	/**
	 * Init Tinymce WYSIWYG editor
	 * @return void
	 */
	public function loadTinymce(): void
	{
		// tinymce plugins for version 4.9.11
		Core::addScript('tinymce-plugin-code', 'admin/js/tinymce-4.9.11/code/plugin.js');
		Core::addScript('tinymce-plugin-preview', 'admin/js/tinymce-4.9.11/preview/plugin.js');
		Core::addScript('tinymce-plugin-table', 'admin/js/tinymce-4.9.11/table/plugin.js');

		// load WP internal tinymce
		$js_src  = includes_url('js/tinymce/') . 'tinymce.min.js';
		$css_src = includes_url('css/') . 'editor.css';
		echo '<script src="' . $js_src . '" type="text/javascript"></script>';
		wp_enqueue_style('tinymce_css', $css_src);

		echo "<script>
	function placeMce(selector) {
		if($(selector).length < 1) {
			console.log('Place MCE: form element not found', selector);
		}
		tinyMCE.init({
			selector: selector,
			menubar: 'edit view insert format table',
			plugins: 'link lists charmap hr fullscreen media directionality paste textcolor colorpicker image media code preview table',
			toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | charmap image link | fullscreen code preview table',
			image_advtab: true,
		});
	}
</script>";
	}
}