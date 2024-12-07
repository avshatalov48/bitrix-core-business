<?php

use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Loader;
use Bitrix\MobileApp\Janative\Entity\Extension;
use Bitrix\MobileApp\Janative\Manager;

define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

/** @var CMain $APPLICATION */

$types = ["component", "extension"];
$componentName = $_GET['componentName'] ?? null;
$namespace = $_GET['namespace'] ?? null;
$version = $_REQUEST['version'] ?? null;
$type = $_REQUEST['type'] ?? null;
$onlyTextOfExt = (bool)($_REQUEST['onlyTextOfExt'] ?? false);
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
		$content = "";
		$langExpression = "";
		$extension = Extension::getInstance($componentName);
		header('BX-Extension: true');

		if ($onlyTextOfExt)
		{
			$content = $extension->getContent();
			$langExpression = $extension->getLangDefinitionExpression();

			echo "$langExpression\n$content";

			return;
		}

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
		foreach ($deps as $name)
		{
			$item = Extension::getInstance($name);
			$langExpression .= $item->getLangDefinitionExpression();
			$content .= $item->getContent();
		}

		$result = "$langExpression\n$content";
		$componentDeps = $extension->getComponentDependencies();
		if ($componentDeps !== null)
		{
			$allComponents = Manager::getAvailableComponents();
			$data = array_map(function ($component) {
				return $component->getInfo();
			}, array_intersect_key($allComponents, array_flip($componentDeps)));
			$jsonData = json_encode($data);
			$updateComponentsExpression = "\nthis.availableComponents = { ... this.availableComponents, ... $jsonData };\n";
			$result = "$updateComponentsExpression\n$result";
		}

		echo $result;
	}
	catch (Exception $e)
	{
		$error = escapePHPString($e->getMessage(), "'");
		header('BX-Extension:false');
		echo "console.error('$error')";
	}
}

\CMain::FinalActions();

