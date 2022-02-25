<?php

namespace Demovox;

/**
 * @var AdminCollectionSettings $this
 * @var string                  $page
 * @var array                   $languages
 */
?>
<?php
settings_fields($page);
$this->doSettingsSections($page);
submit_button();
?>