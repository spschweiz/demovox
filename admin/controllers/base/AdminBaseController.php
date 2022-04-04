<?php

namespace Demovox;

/**
 * @package    Demovox
 * @subpackage Demovox/admin
 * @author     SP Schweiz
 */
abstract class AdminBaseController extends BaseController
{
	protected function loadDatepicker(): void
	{
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style($this->getPluginName(), plugin_dir_url(__FILE__) . '../../../public/css/demovox-public.min.css', [], $this->getVersion(), 'all');
	}

	protected function setCollectionIdByReq(): void
	{
		if (isset($_REQUEST['cln']) && is_numeric($_REQUEST['cln'])) {
			$collectionId = intval($_REQUEST['cln']);
		} else {
			$collectionId = $this->getDefaultCollection();
		}
		$this->setCollectionId($collectionId);
	}
}