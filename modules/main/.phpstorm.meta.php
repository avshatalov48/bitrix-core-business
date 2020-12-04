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
}