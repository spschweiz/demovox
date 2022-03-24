<?php

namespace Demovox;

class RegisterSettingsCollection extends RegisterSettings
{
	public function register()
	{
		require_once Core::getPluginDir() . 'includes/helpers/SettingsVarsCollection.php';
		$this->registerFields();
		$this->registerSections();
	}

	protected function getSettingsSections(): array
	{
		$sections = SettingsVarsCollection::getSections();
		return $sections;
	}

	protected function getSettingsFields(): array
	{
		return SettingsVarsCollection::getFields();
	}

	protected function registerFields()
	{
		$fields = $this->getSettingsFields();
		foreach ($fields as $field) {
			$id = Core::getWpId($field['uid']);
			$this->registerField($field, $id);
		}
	}
}