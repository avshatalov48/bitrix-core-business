<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Rest\Internal\Service\MemoryLimitChecker;

if (!CModule::IncludeModule('rest'))
{
	return;
}

MemoryLimitChecker::createByDefault()->executeOnMemoryExcess(function ($memoryUsageMB, $maxAllowedSizeMB) {
	if (IsModuleInstalled('bitrix24'))
	{
		AddMessage2Log(
			"Rest payload Too Large. Estimated memory usage: {$memoryUsageMB} MB, max allowed: {$maxAllowedSizeMB} MB",
			'rest',
		);
	}
});

$query = \CRestUtil::getRequestData();

$arDefaultUrlTemplates404 = [
	'method' => '#method#',
	'method1' => '#method#/',
	'webhook' => '#aplogin#/#ap#/#method#',
	'webhook1' => '#aplogin#/#ap#/#method#/',
	'apiWebhook' => 'api/#aplogin#/#ap#/#method#',
	'apiWebhook1' => 'api/#aplogin#/#ap#/#method#/',
	'apiMethod' => 'api/#method#',
	'apiMethod1' => 'api/#method#/',
];

$arDefaultVariableAliases404 = [];
$arDefaultVariableAliases = [];

$arComponentVariables = [
	'method', 'aplogin', 'ap'
];

$arVariables = [];

$serverClass = CRestServer::class;

if ($arParams['SEF_MODE'] == 'Y')
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams['SEF_URL_TEMPLATES'] ?? []);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams['VARIABLE_ALIASES'] ?? []);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams['SEF_FOLDER'],
		$arUrlTemplates,
		$arVariables,
	);

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$query = array_merge($query, $arVariables);
	unset($query['method']);
}
else
{
	ShowError('Non-SEF mode is not supported by bitrix:rest.server component');
}

$transport = CRestServer::TRANSPORT_JSON;

if (!in_array($componentPage, ['apiWebhook', 'apiWebhook1', 'apiMethod', 'apiMethod1'], true))
{
	$methods = [mb_strtolower($arVariables['method']), $arVariables['method']];

	// try lowercase first, then original
	foreach ($methods as $method)
	{
		$point = mb_strrpos($method, '.');

		if ($point > 0)
		{
			$check = mb_substr($method, $point + 1);
			if (CRestServer::transportSupported($check))
			{
				$transport = $check;
				$method = mb_substr($method, 0, $point);
			}
		}

		$server = new CRestServer([
			'CLASS' => $arParams['CLASS'] ?? null,
			'METHOD' => $method,
			'TRANSPORT' => $transport,
			'QUERY' => $query,
		], false);

		$result = $server->process();

		// try original controller name if lower is not found
		if (is_array($result) && !empty($result['error']) && $result['error'] === 'ERROR_METHOD_NOT_FOUND')
		{
			continue;
		}

		// output result
		break;
	}
}
else
{
	$schemaManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get(\Bitrix\Rest\V3\Schema\SchemaManager::class);
	$routes = $schemaManager->getRouteAliases();
	$serverRequest = new \Bitrix\Rest\V3\Interaction\Request\ServerRequest(($routes[$arVariables['method']] ?? $arVariables['method']), $query, \Bitrix\Main\Context::getCurrent()->getRequest());

	$server = new CRestApiServer([
		'LOCAL_ERROR_LANGUAGE' => \Bitrix\Main\Context::getCurrent()->getRequest()->getHeader('X-Local-Error-Language'),
	]);

	$result = $server->processServerRequest($serverRequest);
}

$APPLICATION->RestartBuffer();

$output = $server->output($result);
if ($output instanceof \Bitrix\Main\HttpResponse)
{
	$server->sendHeadersAdditional();
	$output->send();
}
else
{
	$server->sendHeaders();
	echo $output;
}

CMain::FinalActions();
die;
