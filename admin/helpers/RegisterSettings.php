<?php

namespace Demovox;

class RegisterSettings
{
	protected $settingsController;

	public function __construct(AdminSettings $settingsController)
	{
		$this->settingsController = $settingsController;
	}

	public function register()
	{
		require_once Core::getPluginDir() . 'includes/helpers/SettingsVars.php';
		$this->registerFields();
		$this->registerSections();
	}

	protected function getSettingsSections(): array
	{
		return SettingsVars::getSections();
	}

	protected function getSettingsFields(): array
	{
		return SettingsVars::getFields();
	}

	protected function getSettingsSection(string $sectionId): array
	{
		$sections = $this->getSettingsSections();
		if (!isset($sections[$sectionId])) {
			Core::logMessage('Config definition error, section not found: ' . $sectionId);
			return [];
		}
		return $sections[$sectionId];
	}

	protected function registerSections()
	{
		$areas = $this->getSettingsSections();
		foreach ($areas as $name => $section) {
			add_settings_section($name, $section['title'], null, $section['page']);
		}
	}

	protected function registerFields()
	{
		$fields = $this->getSettingsFields();
		foreach ($fields as $field) {
			$id = Core::getWpId($field['uid']);
			$this->registerField($field, $id);
		}
	}

	/**
	 * @param array  $field
	 * @param string $id
	 * @return mixed
	 */
	protected function registerField(array $field, string $id)
	{
		$callback = [$this->settingsController, 'fieldCallback',];

		$label = $field['label'];
		$sectionId = $field['section'];
		$section = $this->getSettingsSection($sectionId);
		$page = $section['page'];
		$fieldType = isset($field['type']) ? $field['type'] : null;
		$args = isset($field['default']) ? ['default' => $field['default']] : [];

		switch ($fieldType) {
			default:
				add_settings_field($id, $label, $callback, $page, $sectionId, $field);
				register_setting($page, $id, $args);
				break;
			case'pos_rot':
				$id = $id . Settings::GLUE_PART . Settings::PART_POS_X;
				$argX = isset($field['defaultX']) ? ['default' => $field['defaultX']] : [];
				$argY = isset($field['defaultY']) ? ['default' => $field['defaultY']] : [];
				$argRot = isset($field['defaultRot']) ? ['default' => $field['defaultRot']] : [];

				add_settings_field($id, $label, $callback, $page, $sectionId, $field);
				register_setting($page, $id . Settings::GLUE_PART . Settings::PART_POS_X, $argX);
				register_setting($page, $id . Settings::GLUE_PART . Settings::PART_POS_Y, $argY);
				register_setting($page, $id . Settings::GLUE_PART . Settings::PART_ROTATION, $argRot);
				break;
			case'wpPage':
				add_settings_field($id, $label, $callback, $page, $sectionId, $field);
				register_setting($page, $id, $args);
				register_setting($page, $id . Settings::GLUE_PART . Settings::PART_PREVIOUS_LANG);
				break;
		}
		return $field;
	}
}