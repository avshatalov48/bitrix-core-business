<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CT_BMLI_MODULE_NOT_INSTALLED"));
	die();
}

$iblock_id = intval($_REQUEST["IBLOCK_ID"]);

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$_REQUEST["IBLOCK_TYPE_ID"],
	$iblock_id,
	$_REQUEST["SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CT_BMLI_WRONG_IBLOCK_TYPE"));
		die();
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CT_BMLI_WRONG_IBLOCK"));
		die();
	default:
		ShowError(GetMessage("CT_BMLI_UNKNOWN_ERROR"));
		die();
	}
}
elseif(
	$lists_perm < CListPermissions::CAN_READ
	&& !CIBlockRights::UserHasRightTo($iblock_id, $iblock_id, "element_read")
)
{
	ShowError(GetMessage("CT_BMLI_ACCESS_DENIED"));
	die();
}

$arIBlock = CIBlock::GetArrayByID($iblock_id);

if($_REQUEST['MODE'] == 'SEARCH')
{
	$APPLICATION->RestartBuffer();

	$arResult = array();
	$search = $_REQUEST['search'];

	$matches = array();
	if(preg_match('/^(.*?)\[([\d]+?)\]/i', $search, $matches))
	{
		$matches[2] = intval($matches[2]);
		if($matches[2] > 0)
		{
			$dbRes = CIBlockElement::GetList(
				array(),
				array("IBLOCK_ID" => $arIBlock["ID"], "=ID" => $matches[2]),
				false,
				false,
				array("ID", "NAME")
			);
			if($arRes = $dbRes->Fetch())
			{
				$arResult[] = array(
					'ID' => $arRes['ID'],
					'NAME' => $arRes['NAME'],
					'READY' => 'Y',
				);

				Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
				echo CUtil::PhpToJsObject($arResult);
				die();
			}
		}
		elseif($matches[1] <> '')
		{
			$search = $matches[1];
		}
	}

	$dbRes = CIBlockElement::GetList(
		array(),
		array("IBLOCK_ID" => $arIBlock["ID"], "%NAME" => $search),
		false,
		array("nTopCount" => 20),
		array("ID", "NAME")
	);

	while($arRes = $dbRes->Fetch())
	{
		$arResult[] = array(
			'ID' => $arRes['ID'],
			'NAME' => $arRes['NAME'],
		);
	}

	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($arResult);
	die();
}
?>