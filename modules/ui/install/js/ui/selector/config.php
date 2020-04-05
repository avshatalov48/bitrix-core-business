<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

foreach (\Bitrix\Main\ModuleManager::getInstalledModules() as $module)
{
	$extensionsModuleSettings = \Bitrix\Main\Config\Configuration::getInstance($module['ID'])->get('ui.selector');

	if (!empty($extensionsModuleSettings))
	{
		if (is_array($extensionsModuleSettings))
		{
			foreach($extensionsModuleSettings as $extensionName)
			{
				if (!empty($extensionName))
				{
					\Bitrix\Main\UI\Extension::load($extensionName);
				}
			}
		}
		elseif (!empty($extensionsModuleSettings))
		{
			\Bitrix\Main\UI\Extension::load($extensionsModuleSettings);
		}
	}
}

return [
	"js" => array(
		"/bitrix/js/ui/selector/manager.js",
		"/bitrix/js/ui/selector/selector.js",
		"/bitrix/js/ui/selector/callback.js",
		"/bitrix/js/ui/selector/search.js",
		"/bitrix/js/ui/selector/navigation.js",
		"/bitrix/js/ui/selector/render.js"
	),
	"rel" => [
		'ajax',
		'finder'
	],

];