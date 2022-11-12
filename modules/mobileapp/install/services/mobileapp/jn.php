<?

use Bitrix\MobileApp\Janative\Entity\Extension;

define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

/** @var CAllMain $APPLICATION */

$types = ["component", "extension"];
$componentName = $_GET['componentName'];
$namespace = $_GET['namespace'];
$version = $_REQUEST['version'];
$type = ($_REQUEST['type'] && in_array($_REQUEST['type'], $types ) ? $_REQUEST['type'] : "component");
if (isset($_REQUEST["reload"])) {
	define("JN_DEV_RELOAD", true);
}
\Bitrix\Main\Loader::includeModule("mobileapp");
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

		$payload = new \Bitrix\Main\Engine\JsonPayload();
		if (!empty($payload->getRaw())) {
			$exclude = $payload->getData();
			if (!empty($exclude) && is_array($exclude)) {
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

