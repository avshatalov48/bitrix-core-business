<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:sources@bitrixsoft.com              #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('mail');

$err_mess = "File: ".__FILE__."<br>Line: ";

$sTableID = "t_filter_admin";
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка


$filter = new CAdminFilter(
	$sTableID."_filter_id", 
	array(
		"ID",
		GetMessage("MAIL_FILT_ADM_FILT_MBOX"),
		GetMessage("MAIL_FILT_ADM_FILT_ACT")
	)
);


$arFilterFields = Array(
	"find_name",
	"find_id",
	"find_mailbox_id",
	"find_active",
);

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра

$arFilter = Array(
	"ID"=>$find_id,
	"NAME"=>$find_name,
	"MAILBOX_ID"=>$find_mailbox_id,
	"ACTIVE"=>$find_active
	);



if ($MOD_RIGHT=="W" && $lAdmin->EditAction()) //если идет сохранение со списка
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if(!CMailFilter::Update($ID, $arFields))
		{
			$e = $APPLICATION->GetException();
			$lAdmin->AddUpdateError(GetMessage("MAIL_SAVE_ERROR")." #".$ID.": ".$e->GetString(), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}


// обработка действий групповых и одиночных
if($MOD_RIGHT=="W" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CMailFilter::GetList(Array($by=>$order), $arFilter);
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
				$DB->StartTransaction();
				if(!CMailFilter::Delete($ID))
				{
					$DB->Rollback();
					$e = $APPLICATION->GetException();
					$lAdmin->AddGroupError($e->GetString(), $ID);
				}
				$DB->Commit();
			break;

			case "activate":
			case "deactivate":

			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!CMailFilter::Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("MAIL_SAVE_ERROR")." #".$ID.": ".$e->GetString(), $ID);
			break;
		}
	}
}

$rsData = CMailFilter::GetList(Array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("MAIL_FILT_ADM_NAVIGATION")));


$arHeaders = Array();
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "id");
$arHeaders[] = Array("id"=>"TIMESTAMP_X", "content"=>GetMessage("MAIL_FILT_ADM_DATECH"), "default"=>true, "sort" => "timestamp_x");
$arHeaders[] = Array("id"=>"NAME", "content"=>GetMessage("MAIL_FILT_ADM_NAME"), "default"=>true, "sort" => "name");
$arHeaders[] = Array("id"=>"ACTIVE", "content"=>GetMessage("MAIL_FILT_ADM_ACT"), "default"=>true, "sort" => "active");
$arHeaders[] = Array("id"=>"SORT", "content"=>GetMessage("MAIL_FILT_ADM_SORT"), "default"=>true, "sort" => "sort");
$arHeaders[] = Array("id"=>"MAILBOX_NAME", "content"=>GetMessage("MAIL_FILT_ADM_MBOX"), "default"=>true, "sort" => "mailbox_name");


$lAdmin->AddHeaders($arHeaders);

// построение списка
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($row->bEditMode)
	{
		$row->AddInputField("NAME",Array("size"=>"35"));
	}
	else
	{
		$strType = "";
		if($arRes["ACTION_TYPE"] <> '')
		{
			$res = CMailFilter::GetFilterList($arRes["ACTION_TYPE"]);
			if ($arModFilter = $res->Fetch()):
				$strType = htmlspecialcharsbx($arModFilter["NAME"])."<br>";
			endif;
		}
		else
		{
			$strType = GetMessage("MAIL_FILT_ADM_MANUAL_TYPE")."<br>";
		}

		$strWhen = "";
		if ($arRes["WHEN_MAIL_RECEIVED"]=="Y")
		{
			$strWhen .= GetMessage("MAIL_FILT_ADM_WHEN_RECIEVE");
		}

		if ($arRes["WHEN_MANUALLY_RUN"]=="Y")
		{
			$strWhen .= ($strWhen!=""?GetMessage("MAIL_FILT_ADM_WHEN_OR"):"").GetMessage("MAIL_FILT_ADM_WHEN_MANUAL");
		}

		$strCond = "";
		$res = CMailFilterCondition::GetList(Array("id"=>"asc"), Array("FILTER_ID"=>$f_ID));
		while($ar = $res->Fetch())
		{
			$strCond .= '';
			if ($strCond!="") $strCond .= GetMessage("MAIL_FILT_ADM_WHEN_AND");
			switch($ar["TYPE"])
			{
				case "SENDER":
					$strCond .= GetMessage("MAIL_FILT_ADM_SENDER");
					break;
				case "RECIPIENT":
					$strCond .= GetMessage("MAIL_FILT_ADM_RECIPIENT");
					break;
				case "SUBJECT":
					$strCond .= GetMessage("MAIL_FILT_ADM_SUBJECT");
					break;
				case "BODY":
					$strCond .= GetMessage("MAIL_FILT_ADM_BODY");
					break;
				case "HEADER":
					$strCond .= GetMessage("MAIL_FILT_ADM_HEADER");
					break;
				case "ALL":
					$strCond .= GetMessage("MAIL_FILT_ADM_ALL");
					break;
				case "ATTACHMENT":
					$strCond .= GetMessage("MAIL_FILT_ADM_ATTACH");
				break;
			}

			$strCond .= " ";
			switch($ar["COMPARE_TYPE"])
			{
				case "CONTAIN":
					$strCond .= GetMessage("MAIL_FILT_ADM_CONTAIN");
					$strNameTmp1 = GetMessage("MAIL_FILT_ADM_STRING");
					$strNameTmp2 = GetMessage("MAIL_FILT_ADM_ONE_STRING");
				break;
				case "NOT_CONTAIN":
					$strCond .= GetMessage("MAIL_FILT_ADM_NOTCONTAIN");
					$strNameTmp1 = GetMessage("MAIL_FILT_ADM_STRING");
					$strNameTmp2 = GetMessage("MAIL_FILT_ADM_ALL_STRING");
				break;
				case "EQUAL":
					$strCond .= GetMessage("MAIL_FILT_ADM_EQUAL");
					$strNameTmp1 = GetMessage("MAIL_FILT_ADM_EQUAL_STRING");
					$strNameTmp2 = GetMessage("MAIL_FILT_ADM_EQUAL_ONESTRING");
				break;
				case "NOT_EQUAL":
					$strCond .= GetMessage("MAIL_FILT_ADM_NOTEQUAL");
					$strNameTmp1 = GetMessage("MAIL_FILT_ADM_EQUAL_STRING");
					$strNameTmp2 = GetMessage("MAIL_FILT_ADM_EQUAL_ALLSTRING");
				break;
				case "REGEXP":
					$strCond .= GetMessage("MAIL_FILT_ADM_REGEXP");
					$strNameTmp1 = GetMessage("MAIL_FILT_ADM_REGEXP_STRING");
					$strNameTmp2 = GetMessage("MAIL_FILT_ADM_REGEXP_ONESTRING");
				break;
			}

			$ar["STRINGS"] = trim($ar["STRINGS"]);
			if (mb_strpos($ar["STRINGS"], "\n") > 0)
			{
				$ar["STRINGS"] = str_replace("\r", '', $ar["STRINGS"]);
				$ar["STRINGS"] = '"'.str_replace("\n", '","', $ar["STRINGS"]).'"';
				$strCond .= " ".$strNameTmp2." {".htmlspecialcharsbx(mb_substr($ar["STRINGS"], 0, 30)).(mb_strlen(trim($ar["STRINGS"])) > 30?"...":"")."}";
			}
			else
				$strCond .= " ".$strNameTmp1." &quot;".htmlspecialcharsbx(mb_substr($ar["STRINGS"], 0, 30)).(mb_strlen($ar["STRINGS"]) > 30?"...":"")."&quot;";
		}

		$strAction = "";
		if($arRes["ACTION_READ"]=="Y")
		{
			$strAction .= ($strAction!=""?", ":"").GetMessage("MAIL_FILT_ADM_MARK_READ");
		}

		if($arRes["ACTION_DELETE_MESSAGE"]=="Y")
		{
			$strAction .= ($strAction!=""?", ":"").GetMessage("MAIL_FILT_ADM_DELETE");
		}

		if($arRes["ACTION_PHP"] <> '')
		{
			$strAction .= ($strAction!=""?", ":"").GetMessage("MAIL_FILT_ADM_PHP_ACTION");
		}

		if ($arRes["ACTION_STOP_EXEC"]=="Y")
		{
			$strAction .= ($strAction!=""?GetMessage("MAIL_FILT_ADM_WHEN_AND"):"").GetMessage("MAIL_FILT_ADM_CANCEL_RULES");
		}

		$strDesc = $strType.
				($strCond!=""? 
				GetMessage("MAIL_FILT_ADM_WHEN").", ".($strWhen!=''?$strWhen.', ':'').$strCond:
				$strWhen
				).($strAction!="" && $strCond!=""?", ".GetMessage("then")." ":" ").$strAction;

		$row->AddViewField("NAME", $f_NAME."<br>".$strDesc);
	}

	$str = $f_MAILBOX_NAME.'&nbsp;[<a title="'.GetMessage("MAIL_FILT_ADM_CHANGE_MBOX").'" href="mail_mailbox_edit.php?ID='.$f_MAILBOX_ID.'&lang='.LANG.'">'.$f_MAILBOX_ID.'</a>]';
	$row->AddViewField("MAILBOX_NAME", $str);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("SORT", Array("size"=>"3"));

	$arActions = Array();


	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIL_FILT_ADM_CHANGE"),
		"ACTION"=>$lAdmin->ActionRedirect("mail_filter_edit.php?ID=".$f_ID."&lang=".LANG)
	);

	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("MAIL_FILT_ADM_LOG"),
		"ACTION"=>$lAdmin->ActionRedirect("mail_log.php?find_filter_id=".$f_ID."&set_filter=Y&lang=".LANG)
	);


	if ($MOD_RIGHT=="W")
	{
		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIL_FILT_ADM_DEL"),
			"ACTION"=>"if(confirm('".GetMessage('MAIL_FILT_ADM_DEL_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}

	$row->AddActions($arActions);
}


// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($MOD_RIGHT=="W")
{
	// показ добавление формы с кнопками
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}




$arSubMenu = Array();

$arSubMenu[] = array(
	"TEXT"	=> GetMessage("MAIL_FILT_ADM_MANUAL_TYPE"),
	"ACTION"	=> "window.location='/bitrix/admin/mail_filter_edit.php?filter_type=&lang=".LANG."';"
);

ClearVars("a_");
$res = CMailFilter::GetFilterList();
while($ar = $res->ExtractFields("a_"))
{
	$arSubMenu[] = array(
	"TEXT"	=> $a_NAME,
	"ACTION"	=> "window.location='/bitrix/admin/mail_filter_edit.php?filter_type=".$a_ID."&lang=".LANG."';"
	);
}

$aContext = array(
	array(
			"ICON" => "btn_new",
			"TEXT" => GetMessage("MAIL_ADD_FILTER"), 
			"TITLE" => GetMessage("MAIL_FILT_ADM_NEW_TYPE"),
			"MENU" => $arSubMenu,
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIL_FILT_ADM_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>




<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">

<?$filter->Begin();?>
<tr>
	<td nowrap><?echo GetMessage("MAIL_FILT_ADM_FILT_NAME")?>:</td>
	<td nowrap><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td nowrap>ID:</td>
	<td nowrap><input type="text" name="find_id" value="<?echo htmlspecialcharsbx($find_id)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIL_FILT_ADM_FILT_MBOX")?>:</td>
	<td nowrap>
		<select name="find_mailbox_id">
			<option value=""><?echo GetMessage("MAIL_FILT_ADM_FILT_ANY")?></option>
			<?
			ClearVars("mb_");
			$l = CMailbox::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array('VISIBLE' => 'Y', 'USER_ID' => 0));
			while($l->ExtractFields("mb_")):
				?><option value="<?echo $mb_ID?>"<?if($find_mailbox_id==$mb_ID)echo " selected"?>><?echo $mb_NAME?></option><?
			endwhile;
			?>
		</select>
		</td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIL_FILT_ADM_FILT_ACT")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));
		?></td>
</tr>

<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>"mail_filter_admin.php", "form"=>"form1"));
$filter->End();
?>
</form>






<?$lAdmin->DisplayList();?>





<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
