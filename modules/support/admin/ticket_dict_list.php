<? 
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
InitSorting();
$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
								Функции
****************************************************************************/

/***************************************************************************
							Обработка GET | POST
****************************************************************************/
if (
	!isset($find_type) ||
	mb_strlen($find_type) < 0 ||
	!in_array($find_type, Array("C","K","S","M","F","SR", "D"))
	)
	$find_type = "C";


$sTableID = "t_dict_list_".mb_strtolower($find_type);
$oSort = new CAdminSorting($sTableID, "s_c_sort", "asc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка


$oFilter = new CAdminFilter(
	$sTableID."_filter_id", 
	array(
		GetMessage("SUP_F_ID"),
		GetMessage("SUP_F_SITE"),
		//GetMessage("SUP_F_TYPE"),
		GetMessage("SUP_F_NAME"),
		GetMessage("SUP_F_DESCR"),
		GetMessage("SUP_F_SID"),
		GetMessage("SUP_F_RESPONSIBLE"),
		GetMessage("SUP_F_DEFAULT"),
	)
);


$arFilterFields = Array(
	"find",
	"find_type_ex",

	"find_id",
	"find_id_exact_match",
	"find_site", 
	//"find_type",
	"find_name",
	"find_name_exact_match",
	"find_descr",
	"find_descr_exact_match",
	"find_sid",
	"find_sid_exact_match",
	"find_responsible",
	"find_responsible_exact_match",
	"find_responsible_id",
	"find_default"
	);

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра


InitBVar($find_id_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_sid_exact_match);
InitBVar($find_responsible_exact_match);



$arFilter = Array(
	"ID"						=> $find_id,
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"SITE"						=> $find_site,	
	"TYPE"						=> $find_type,
	"NAME"						=> ($find!="" && $find_type_ex == "name"? $find: $find_name),
	"NAME_EXACT_MATCH"			=> $find_name_exact_match,
	"DESCR"						=> ($find!="" && $find_type_ex == "descr"? $find: $find_descr),
	"DESCR_EXACT_MATCH"			=> $find_descr_exact_match,
	"SID"						=> $find_sid,
	"SID_EXACT_MATCH"			=> $find_sid_exact_match,
	"RESPONSIBLE_ID"			=> $find_responsible_id,
	"RESPONSIBLE"				=> $find_responsible,
	"RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match,
	"DEFAULT"					=> $find_default
	);



if ($bAdmin=="Y" && $lAdmin->EditAction()) //если идет сохранение со списка
{

	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$arFields["C_SORT"] = intval($arFields["C_SORT"]);

		if (trim($arFields["NAME"]) <> '')
		{
			$arUpdate = array(
					'C_SORT' => $arFields['C_SORT'],
					'NAME' => $arFields['NAME']
					);

			if (!CTicketDictionary::Update($ID, $arUpdate))
			{
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SUP_ERROR_SAVE")), $ID);

			}
		}
		else
		{
			$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SUP_FORGOT_NAME")), $ID);
		}
	}
}


if($bAdmin=="Y" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$isFiltered = null;
		$rsData = CTicketDictionary::GetList('', '', $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				CTicketDictionary::Delete($ID);
			break;
		}
	}
}
// если была нажата кнопка "Сохранить изменения"

if ($find_type=="C" || 
	$find_type=="K" || 
	$find_type=="SR" || 
	$find_type=="NOT_REF" || 
	$find_type == '') $show_responsible_column = "Y";

global $by, $order;

$rsData = CTicketDictionary::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SUP_PAGES")));

//$tdic = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
$APPLICATION->SetTitle(GetMessage("SUP_TICKETS_DIC_TITLE"));



$arHeaders = Array();
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "s_id");
$arHeaders[] = Array("id"=>"C_SORT", "content"=>GetMessage("SUP_SORT"), "default"=>true, "sort" => "s_c_sort");
$arHeaders[] =  Array("id"=>"C_SITE", "content"=>GetMessage("SUP_SITE"), "default"=>true,);
$arHeaders[] =  Array("id"=>"NAME", "content"=>GetMessage("SUP_NAME"), "default"=>true,"sort" => "s_name");
if ($show_responsible_column=="Y")
	$arHeaders[] =  Array("id"=>"RESPONSIBLE_USER_ID", "content"=>GetMessage("SUP_RESPONSIBLE"), "default"=>true,"sort" => "s_responsible");

$lAdmin->AddHeaders($arHeaders);

// построение списка
$arRows = array();
$arSiteArrayForAllDict = CTicketDictionary::GetSiteArrayForAllDictionaries();
$arRespUserIDs = array();
while($arRes = $rsData->Fetch())
{

	if(intval($arRes["RESPONSIBLE_USER_ID"])>0)
	{
		$arRespUserIDs[] = intval($arRes["RESPONSIBLE_USER_ID"]);
	}
	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);
	$arRow = array("objRow" => $row, "arFields" => $arRes);
	$row->AddInputField("C_SORT", Array("size"=>"3"));

	$str_SITE = "";
	if(array_key_exists($arRes["ID"], $arSiteArrayForAllDict))
	{
		$arrSITE =  $arSiteArrayForAllDict[$arRes["ID"]];
		foreach($arrSITE as $sid)
		{
			$sidS = htmlspecialcharsbx($sid);
			$str_SITE .= ($str_SITE == "" ? "" : " / ") . '<a title="' . GetMessage("MAIN_ADMIN_MENU_EDIT") . '" href="/bitrix/admin/site_edit.php?LID=' . $sidS . '&lang=' . LANG . '">' . $sidS . '</a>';
		}
	}

	$row->AddViewField("C_SITE", $str_SITE);

	$row->AddInputField("NAME", Array("size"=>"35"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("SUP_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("ticket_dict_edit.php?find_type=" . $arRes["C_TYPE"] . "&ID=" . $arRes["ID"] . "&lang=" . LANG)
	);

	if ($bAdmin=="Y")
	{
		$arActions[] = array("SEPARATOR" => true);

		$arActions[] = array(
			"ICON" => "delete",
			"TEXT"	=> GetMessage("SUP_DELETE"),
			"ACTION"=>"if(confirm('" . GetMessage('SUP_DELETE_TDIC_CONF') . "')) " . $lAdmin->ActionDoGroup($arRes["ID"], "delete", "find_type=" . $arRes["C_TYPE"]),
		);
	}

	$row->AddActions($arActions);
	$arRows[] = $arRow;
}

$arRespUsersProp = array();
$arRespUserIDs = array_unique($arRespUserIDs);
$strUsers = implode("|", $arRespUserIDs);
$rs = CUser::GetList('id', 'asc', array( "ID" => $strUsers), array("FIELDS"=>array("NAME","LAST_NAME","LOGIN","ID")));
while($ar = $rs->Fetch())
{
	$arRespUsersProp[$ar["ID"]] = $ar;
}
// Еще один проход для ответственных пользователей
foreach($arRows as $k => $v)
{
	$str = "&nbsp;";
	$rUserID = intval($v["arFields"]["RESPONSIBLE_USER_ID"]);
	if ($rUserID > 0 && array_key_exists($rUserID, $arRespUsersProp)):
		$arUserPr = $arRespUsersProp[$rUserID];
		$str = '[<a title="' . GetMessage("SUP_USER_PROFILE") . '" href="/bitrix/admin/user_edit.php?lang=' . LANG . '&ID=' . $rUserID . '">' .
			$rUserID.'</a>] (' . htmlspecialcharsbx($arUserPr["LOGIN"]) . ') ' . htmlspecialcharsbx($arUserPr["NAME"]) . "  " . htmlspecialcharsbx($arUserPr["LAST_NAME"]);

	endif;
	$v["objRow"]->AddViewField("RESPONSIBLE_USER_ID", $str);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($bAdmin=="Y")
{
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

$aContext = array(
	array(
		"ICON"=> "btn_new",
		"TEXT"=> GetMessage("SUP_ADD"),
		//"LINK"=>"ticket_dict_edit.php?lang=".LANG."&find_type=".htmlspecialcharsbx($find_type),
		"TITLE"=>GetMessage("SUP_ADD"),
		"MENU" => Array(
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_CATEGORY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=C';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_CRITICALITY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=K';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_STATUS"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=S';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_MARK"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=M';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_FUA"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=F';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_SOURCE"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=SR';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_DIFFICULTY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=D';"
			),

		)
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" id="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$oFilter->Begin();?>

<tr>
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>">
		<select name="find_type_ex">
			<option value="name"<?if($find_type_ex=="name") echo " selected"?>><?=GetMessage("SUP_F_NAME")?></option>
			<option value="descr"<?if($find_type_ex=="descr") echo " selected"?>><?=GetMessage("SUP_F_DESCR")?></option>
		</select>
	</td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?=htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top"> 
	<td valign="top"><?=GetMessage("SUP_F_SITE")?>:<br><img src="/bitrix/images/support/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	$ref = array();
	$ref_id = array();
	$rs = CSite::GetList();
	while ($ar = $rs->Fetch()) 
	{
		$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
		$ref_id[] = $ar["ID"];
	}
	echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
	?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?=htmlspecialcharsbx($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_DESCR")?>:</td>
	<td><input type="text" name="find_descr" size="47" value="<?=htmlspecialcharsbx($find_descr)?>"><?=InputType("checkbox", "find_descr_exact_match", "Y", $find_descr_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_SID")?>:</td>
	<td><input type="text" name="find_sid" size="47" value="<?=htmlspecialcharsbx($find_sid)?>"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td nowrap valign="top"><?=GetMessage("SUP_F_RESPONSIBLE")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicket::GetSupportTeamList();
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_responsible_id", $arr, htmlspecialcharsbx($find_responsible_id), GetMessage("SUP_ALL"));
		?><br><input type="text" name="find_responsible" size="47" value="<?=htmlspecialcharsbx($find_responsible)?>"><?=InputType("checkbox", "find_responsible_exact_match", "Y", $find_responsible_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("SUP_F_DEFAULT")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("SUP_YES"), GetMessage("SUP_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_default", $arr, htmlspecialcharsbx($find_default), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?find_type=".$find_type, "form"=>"form1"));
$oFilter->End();
?>
</form>


<?$lAdmin->DisplayList();?>

<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>