<?php

namespace Demovox;

trait AdminScriptsTrait
{
	protected function loadDatepicker(): void
	{
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style($this->getPluginName(), plugin_dir_url(__FILE__) . '../../../public/css/demovox-public.min.css', [], $this->getVersion(), 'all');
	}

	/**
	 * Init Tinymce WYSIWYG editor
	 * @return void
	 */
	public function loadTinymce(): void
	{
		// tinymce plugins for version 4.9.11
		wp_enqueue_script('tinymce-plugin-code', plugin_dir_url(__FILE__) . '../../js/tinymce-4.9.11/code/plugin.js');
		wp_enqueue_script('tinymce-plugin-preview', plugin_dir_url(__FILE__) . '../../js/tinymce-4.9.11/preview/plugin.js');
		wp_enqueue_script('tinymce-plugin-table', plugin_dir_url(__FILE__) . '../../js/tinymce-4.9.11/table/plugin.js');

		// load WP internal tinymce
		$js_src  = includes_url('js/tinymce/') . 'tinymce.min.js';
		$css_src = includes_url('css/') . 'editor.css';
		echo '<script src="' . $js_src . '" type="text/javascript"></script>';
		wp_register_style('tinymce_css', $css_src);
		wp_enqueue_style('tinymce_css');

		echo "<script>
    function placeMce(selector) {
		if($(selector).length < 1) {
			console.error('Place MCE: form element not found', selector);
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