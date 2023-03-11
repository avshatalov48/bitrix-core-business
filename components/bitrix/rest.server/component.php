<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
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

if(!CModule::IncludeModule('rest'))
{
	return;
}

$query = \CRestUtil::getRequestData();

$arDefaultUrlTemplates404 = array(
	"method" => "#method#",
	"method1" => "#method#/",
	"webhook" => "#aplogin#/#ap#/#method#",
	"webhook1" => "#aplogin#/#ap#/#method#/",
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"method", "aplogin", "ap"
);

$arVariables = array();

if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"] ?? []);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"] ?? []);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$query = array_merge($query, $arVariables);
	unset($query['method']);
}
else
{
	ShowError('Non-SEF mode is not supported by bitrix:rest.server component');
}

$transport = 'json';
$methods = [ToLower($arVariables['method']), $arVariables['method']];

// try lowercase first, then original
foreach ($methods as $method)
{
	$point = mb_strrpos($method, '.');

	if($point > 0)
	{
		$check = mb_substr($method, $point + 1);
		if(CRestServer::transportSupported($check))
		{
			$transport = $check;
			$method = mb_substr($method, 0, $point);
		}
	}

	$server = new CRestServer(array(
		"CLASS" => $arParams["CLASS"],
		"METHOD" => $method,
		"TRANSPORT" => $transport,
		"QUERY" => $query,
	), false);

	$result = $server->process();

	// try original controller name if lower is not found
	if (is_array($result) && !empty($result['error']) && $result['error'] === 'ERROR_METHOD_NOT_FOUND')
	{
		continue;
	}

	// output result
	break;
}

$APPLICATION->RestartBuffer();

$output = $server->output($result);
if (is_object($output) && $output instanceof \Bitrix\Main\HttpResponse)
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
die();
