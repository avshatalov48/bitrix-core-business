<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main\Loader;

Loader::includeModule('socialnetwork');

$sonetModulePermissions = $APPLICATION->GetGroupRight("socialnetwork");
if ($sonetModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/prolog.php");

// идентификатор таблицы
$sTableID = "tbl_socnet_smile";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "ID", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// инициализация параметров списка - фильтры
$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

// обработка действий групповых и одиночных
if (($arID = $lAdmin->GroupAction()) && $sonetModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CSocNetSmile::GetList(
			array($by => $order),
			$arFilter
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":

				@set_time_limit(0);

				$DB->StartTransaction();

				$arOldSmile = CSocNetSmile::GetByID($ID);

				if (!CSocNetSmile::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DEL_SMILE"), $ID);
				}
				else
				{
					$DB->Commit();

					if ($arOldSmile)
					{
						$strDirNameOld = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork/";
						if ($arOldSmile["SMILE_TYPE"]=="I")
							$strDirNameOld .= "icon";
						else
							$strDirNameOld .= "smile";
						$strDirNameOld .= "/".$arOldSmile["IMAGE"];
						@unlink($strDirNameOld);
					}
				}

				break;
		}
	}
}

$dbResultList = CSocNetSmile::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

// установке параметров списка
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("PAGES")));

// заголовок списка
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("SMILE_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"SORT","content"=>GetMessage("SMILE_SORT"), "sort"=>"SORT", "default"=>true),
	array("id"=>"SMILE_TYPE", "content"=>GetMessage('SMILE_TYPE'),	"sort"=>"SMILE_TYPE", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SONET_NAME"),  "sort"=>"", "default"=>true),
	array("id"=>"TYPING", "content"=>GetMessage("SONET_TYPING"), "sort"=>"", "default"=>true),
	array("id"=>"ICON", "content"=>GetMessage("SONET_SMILE_ICON"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

// построение списка
while ($arSocNet = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arSocNet);

	$row->AddField("ID", '<a href="/bitrix/admin/socnet_smile_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("SONET_EDIT_DESCR").'">'.$f_ID.'</a>');
	$row->AddField("SORT", $f_SORT);
	$row->AddField("SMILE_TYPE", (($f_SMILE_TYPE=="I") ? GetMessage("SMILE_TYPE_ICON") : GetMessage("SMILE_TYPE_SMILE")));

	$fieldShow = "";
	if (in_array("NAME", $arVisibleColumns))
	{
		$arSmileLang = CSocNetSmile::GetLangByID($f_ID, LANG);
		$fieldShow .= htmlspecialcharsbx($arSmileLang["NAME"]);
	}
	$row->AddField("NAME", $fieldShow);

	$row->AddField("TYPING", $f_TYPING);
	$row->AddField("ICON", "<img src=\"/bitrix/images/socialnetwork/".(($f_SMILE_TYPE=="I")?"icon":"smile")."/".$f_IMAGE."\" border=\"0\" ".((intval($f_IMAGE_WIDTH) > 0) ? "width=\"".$f_IMAGE_WIDTH."\"" : "")." ".((intval($f_IMAGE_WIDTH) > 0) ? "height=\"".$f_IMAGE_HEIGHT."\"" : "" ).">");

	$arActions = Array();
	if ($sonetModulePermissions >= "R")
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SONET_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("socnet_smile_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	}
	if ($sonetModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SONET_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SMILE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

// показ формы с кнопками добавления, ...
$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

if ($sonetModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FSAN_ADD_NEW"),
			"LINK" => "socnet_smile_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FSAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SMILE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>