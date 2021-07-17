<?php

namespace PHPSTORM_META
{

	registerArgumentsSet('bitrix_main_serviceLocator_codes',
		'exceptionHandler',
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_main_serviceLocator_codes'));

	override(\Bitrix\Main\DI\ServiceLocator::get(0), map([
		'exceptionHandler' => \Bitrix\Main\Diag\ExceptionHandler::class,
	]));

	exitPoint(\Bitrix\Main\Application::end());
	exitPoint(\Bitrix\Main\Application::terminate());
	exitPoint(\CAllMain::FinalActions());

	registerArgumentsSet(
		'bitrix_main_modules_list',
		'bitrix24',
		'bizcard',
		'bizproc',
		'bizprocdesigner',
		'blog',
		'bxtest',
		'calendar',
		'catalog',
		'clouds',
		'crm',
		'currency',
		'dav',
		'disk',
		'documentgenerator',
		'extranet',
		'faceid',
		'fileman',
		'forum',
		'highloadblock',
		'iblock',
		'im',
		'imbot',
		'imconnector',
		'imconnectorserver',
		'imopenlines',
		'intranet',
		'landing',
		'lists',
		'mail',
		'main',
		'meeting',
		'messageservice',
		'mobile',
		'mobileapp',
		'oauth',
		'perfmon',
		'photogallery',
		'pull',
		'recyclebin',
		'replica',
		'report',
		'rest',
		'sale',
		'salescenter',
		'search',
		'security',
		'sender',
		'seo',
		'seoproxy',
		'socialnetwork',
		'socialservices',
		'subscribe',
		'tasks',
		'timeman',
		'transformer',
		'translate',
		'ui',
		'vote',
		'voximplant',
		'voximplantadmin',
		'webdav',
		'webservice',
		'wiki',
	);
	expectedArguments(\CModule::IncludeModule(), 0, argumentsSet('bitrix_main_modules_list'));
	expectedArguments(\Bitrix\Main\Loader::includeModule(), 0, argumentsSet('bitrix_main_modules_list'));
	expectedArguments(\Bitrix\Main\ModuleManager::isModuleInstalled(), 0, argumentsSet('bitrix_main_modules_list'));
	expectedArguments(\Bitrix\Main\Config\Option::get(), 0, argumentsSet('bitrix_main_modules_list'));
	expectedArguments(\Bitrix\Main\Config\Option::set(), 0, argumentsSet('bitrix_main_modules_list'));
}