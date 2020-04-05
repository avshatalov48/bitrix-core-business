<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Dummy component for compatibility with the old OAuth scheme.
 *
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 */


use Bitrix\Main\Loader;

if(!Loader::includeModule('rest'))
{
	return;
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$httpClient = new \Bitrix\Main\Web\HttpClient(array(
	'socketTimeout' => 10,
	'streamTimeout' => 10,
	'redirect' => false,
));

$requestArray = $request->toArray();
$requestedScope = '';
if(isset($requestArray['scope']))
{
	$requestedScope = $requestArray['scope'];
	unset($requestArray['scope']);
}

$authResult = $httpClient->get(\Bitrix\Rest\OAuthService::SERVICE_URL.'/oauth/token/'
	.'?bx_proxy_from='.urlencode($request->getHttpHost())
	.'&'.http_build_query($requestArray));

try
{
	$auth = \Bitrix\Main\Web\Json::decode($authResult);


	if(is_array($auth))
	{
		$auth['domain'] = $request->getHttpHost();

		if($requestedScope != '')
		{
			$auth['scope'] = $requestedScope;
		}

		$authResult = \Bitrix\Main\Web\Json::encode($auth);
	}
}
catch (\Bitrix\Main\ArgumentException $e)
{
}

$responseHeaders = $httpClient->getHeaders();

\CHTTP::SetStatus($httpClient->getStatus());

Header('Content-Type: '.$responseHeaders->get('Content-Type'));

echo $authResult;

\CMain::FinalActions();
die();

