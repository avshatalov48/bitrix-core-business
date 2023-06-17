<?php

use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Loader;
use Bitrix\MobileApp\Janative\Entity\Extension;

define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

/** @var CAllMain $APPLICATION */

$types = ["component", "extension"];
$componentName = $_GET['componentName'] ?? null;
$namespace = $_GET['namespace'] ?? null;
$version = $_REQUEST['version'] ?? null;
$type = $_REQUEST['type'] ?? null;
$type = ($type && in_array($type, $types, true) ? $type : "component");
if (isset($_REQUEST["reload"]))
{
	define("JN_DEV_RELOAD", true);
}

Loader::includeModule("mobileapp");

if ($type == "component")
{
	$APPLICATION->IncludeComponent('bitrix:mobileapp.jnrouter', '', [
		'componentName' => $componentName,
		'namespace' => $namespace,
		'clientVersion' => $version,
		'checkVersion' => isset($_REQUEST['check']),
		'needAuth' => true,
	], null, ['HIDE_ICONS' => 'Y']);
}
else
{
	header('Content-Type: text/javascript;charset=UTF-8');
	try
	{
		$extension = new Extension($componentName);
		$deps = $extension->getDependencies();

		$payload = new JsonPayload();
		if (!empty($payload->getRaw()))
		{
			$exclude = $payload->getData();
			if (!empty($exclude) && is_array($exclude))
			{
				$deps = array_diff($deps, $exclude);
			}
		}

		$content = "";
		$langExpression = "";
		foreach ($deps as $name)
		{
			$item = new Extension($name);
			$langExpression .= $item->getLangDefinitionExpression();
			$content .= $item->getContent();
		}
		header('BX-Extension: true');
		echo "$langExpression\n$content";
	}
	catch (Exception $e)
	{
		$error = escapePHPString($e->getMessage(), "'");
		header('BX-Extension:false');
		echo "console.error('$error')";
	}
}

\CMain::FinalActions();

