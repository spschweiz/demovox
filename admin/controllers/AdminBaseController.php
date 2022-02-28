<?php

namespace Demovox;

/**
 * @package    Demovox
 * @subpackage Demovox/admin
 * @author     SP Schweiz
 */
class AdminBaseController extends BaseController
{
	public function loadDatepicker() {
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . '../css/jquery-ui.min.css', [], $this->getVersion());
	}
}