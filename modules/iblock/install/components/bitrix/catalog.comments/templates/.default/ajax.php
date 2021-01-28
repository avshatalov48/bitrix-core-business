<?
/** @global CMain $APPLICATION */
define("NO_KEEP_STATISTIC", true);
define('PUBLIC_AJAX_MODE', true);
define("NOT_CHECK_PERMISSIONS", true);

if (isset($_REQUEST['SITE_ID']) && !empty($_REQUEST['SITE_ID']))
{
	if (!is_string($_REQUEST['SITE_ID']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['SITE_ID']) === 1)
		define('SITE_ID', $_REQUEST['SITE_ID']);
}
else
{
	die();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (check_bitrix_sessid())
{
	$commParams = array();
	if(isset($_REQUEST['IBLOCK_ID']) && isset($_REQUEST['ELEMENT_ID']))
	{
		$iblockID = (int)$_REQUEST['IBLOCK_ID'];
		$elementID = (int)$_REQUEST['ELEMENT_ID'];
		if ($iblockID > 0 && $elementID > 0)
		{
			$paramsId = 'IBLOCK_CATALOG_COMMENTS_PARAMS_'.$iblockID.'_'.$elementID;
			if (!empty($_SESSION[$paramsId]) && is_array($_SESSION[$paramsId]))
			{
				$commParams = $_SESSION[$paramsId];
			}
			unset($paramsId);
		}
		unset($elementID, $iblockID);
	}
	if (!empty($commParams))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.comments',
			'.default',
			$commParams,
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
}
CMain::FinalActions();