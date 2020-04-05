<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');
define('BX_PUBLIC_MODE', 1);

if (!check_bitrix_sessid() || !\Bitrix\Main\Loader::includeModule('catalog'))
	return;

global $APPLICATION;

$success = false;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);
$ids = $request->get('ids');
$action = $request->get('action');

$jsLibrary = \CJSCore::Init(['core_condtree'], true);

if (!empty($ids) && is_array($ids))
{
	$condTree = new CCatalogCondTree();
	$success = $condTree->Init(
		BT_COND_MODE_DEFAULT,
		BT_COND_BUILD_CATALOG,
		array(
			'FORM_NAME' => $ids['form'],
			'CONT_ID' => $ids['container'],
			'JS_NAME' => $ids['treeObject']
		)
	);
}

if ($success)
{
	if ($action === 'init')
	{
		try
		{
			$condition = \Bitrix\Main\Web\Json::decode($request->get('condition'));
		}
		catch (Exception $e)
		{
			$condition = array();
		}

		echo $jsLibrary;
		$condTree->Show($condition);
	}
	elseif ($action === 'save')
	{
		$result = $condTree->Parse();

		$APPLICATION->RestartBuffer();
		echo \Bitrix\Main\Web\Json::encode($result);
	}
}

\CMain::FinalActions();
die();