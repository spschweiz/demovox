<?php

namespace Demovox;

/**
 * @var $this AdminSettings
 * @var $page string
 * @var $languages array
 */
?>
<?php
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>