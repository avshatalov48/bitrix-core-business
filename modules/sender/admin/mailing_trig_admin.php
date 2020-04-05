<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Sender\MailingChainTable;
use \Bitrix\Sender\PostingTable;
use \Bitrix\Sender\PostingRecipientTable;

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sender/admin/mailing_admin.php');

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_sender_mailing";
$ID = intval($_REQUEST["ID"]);
$CHAIN_ID = intval($_REQUEST["CHAIN_ID"]);
$sendToMeMailingId = intval($_REQUEST["send_to_me_mailing_id"]);

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_lid",
	"find_name",
	"find_active",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array(
		"ID" => ($find!="" && $find_type == "id"? $find:$find_id),
		"NAME" => ($find!="" && $find_type == "name"? $find:$find_name),
		"ACTIVE" => $find_active,
		"SITE_ID" => $find_lid,
	);

	foreach($arFilter as $k => $v) if(empty($v)) unset($arFilter[$k]);
}

if(isset($order)) $order = ($order=='asc'?'ASC': 'DESC');

if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = IntVal($ID);
		$arAllowedFieldsForUpdate = array('NAME', 'ACTIVE', 'SORT');
		$dataPrimary = array('ID' => $ID);
		$arData = \Bitrix\Sender\MailingTable::getRowById($dataPrimary);
		if($arData)
		{
			foreach($arFields as $key=>$value)
			{
				if(in_array($key, $arAllowedFieldsForUpdate))
				{
					$arData[$key]=$value;
				}
			}

			unset($arData['ID']);
			$dataUpdateDb = \Bitrix\Sender\MailingTable::update($dataPrimary, $arData);
			if(!$dataUpdateDb->isSuccess())
			{
				$LAST_ERROR = $dataUpdateDb->getErrorMessages();
				$LAST_ERROR = $LAST_ERROR[0];
				$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".$LAST_ERROR, $ID);
				$DB->Rollback();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$dataListDb = \Bitrix\Sender\MailingTable::getList(array(
			'select' => array('ID'),
			'filter' => $arFilter,
			'order' => array($by=>$order)
		));
		while($arRes = $dataListDb->fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);
		$dataPrimary = array('ID' => $ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$dataDeleteDb = \Bitrix\Sender\MailingTable::delete($dataPrimary);
			if(!$dataDeleteDb->isSuccess())
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
			$dataUpdateDb = \Bitrix\Sender\MailingTable::update($dataPrimary, $arFields);
			if(!$dataUpdateDb->isSuccess())
			{
				$LAST_ERROR = $dataUpdateDb->getErrorMessages();
				$LAST_ERROR = $LAST_ERROR[0];
				$lAdmin->AddGroupError(GetMessage("rub_save_error").$LAST_ERROR, $ID);
			}
			break;
		}

	}
}

// runtime: RECIPIENT_CNT = include_cnt - exclude_cnt = group_cnt * (2*exclude_int - 1)
$arFilter['IS_TRIGGER'] = 'Y';
$groupListDb = \Bitrix\Sender\MailingTable::getList(array(
	'select' => array('ID', 'NAME', 'SORT', 'DATE_INSERT', 'ACTIVE', 'SITE_ID'),
	'filter' => $arFilter,
	'order' => array($by=>$order)
));

$rsData = new CAdminResult($groupListDb, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("rub_nav")));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"ID",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("rub_name"),
		"sort"		=>"NAME",
		"default"	=>true,
	),
	array(	"id"		=>"SORT",
		"content"	=>GetMessage("rub_sort"),
		"sort"		=>"SORT",
		"default"	=>true,
	),
	array(	"id"		=>"ACTIVE",
		"content"	=>GetMessage("rub_act"),
		"sort"		=>"ACTIVE",
		"default"	=>true,
	),
	array(	"id"		=>"SITE_ID",
		"content"	=>GetMessage("rub_site"),
		"sort"		=>"SITE_ID",
		"default"	=>true,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="sender_mailing_wizard.php?step=trig_mailing&IS_TRIGGER=Y&MAILING_ID='.$f_ID.'&amp;lang='.LANGUAGE_ID.'">'.$f_NAME.'</a>');
	$row->AddInputField("SORT", array("size"=>6));
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("IS_PUBLIC");
	$row->AddViewField("RECIPIENT_CNT", '<a href="sender_mailing_recipient_admin.php?MAILING_ID='.$f_ID.'&amp;lang='.LANG.'">'.$f_RECIPIENT_CNT.'</a>');

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("rub_edit"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_wizard.php?step=trig_mailing&IS_TRIGGER=Y&MAILING_ID=".$f_ID."&lang=".LANGUAGE_ID)
	);
	if ($POST_RIGHT>="W")
	{
		if($f_ACTIVE <> 'Y')
		{
			$arActions[] = array(
				"ICON"=>"activate",
				"TEXT"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
				"ACTION"=>$lAdmin->ActionDoGroup($f_ID, "activate")
			);
		}
		else
		{
			$arActions[] = array(
				"ICON"=>"deactivate",
				"TEXT"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
				"ACTION"=>$lAdmin->ActionDoGroup($f_ID, "deactivate")
			);
		}

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("rub_del"),
			"ACTION"=>"if(confirm('".GetMessage('rub_del_conf')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	}

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"TEXT"=>GetMessage("sender_mailing_trig_adm_detbut_chain"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_trig_edit.php?ID=".$f_ID)
	);
	$arActions[] = array(
		"TEXT"=>GetMessage("sender_mailing_adm_detbut_stat"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_trig_statistics.php?MAILING_ID=".$f_ID)
	);
	$arActions[] = array(
		"TEXT"=>GetMessage("sender_mailing_adm_detbut_address"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_recipient_admin.php?MAILING_ID=".$f_ID)
	);

	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
	$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));

$aContext = array(
	/*
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"sender_mailing_edit.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("POST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
	*/
	array(
		"TEXT"=>GetMessage("sender_mailing_adm_wizard"),
		"LINK"=>"sender_mailing_wizard.php?step=trig_mailing&IS_TRIGGER=Y&lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("sender_mailing_adm_wizard_title"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("rub_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("rub_f_active"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("rub_f_find")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("rub_f_find_title")?>">
		<?
		$arr = array(
			"reference" => array(
				"ID",
				GetMessage("rub_f_name"),
			),
			"reference_id" => array(
				"id",
				"name",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("rub_f_active")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("MAIN_YES"),
				GetMessage("MAIN_NO"),
			),
			"reference_id" => array(
				"Y",
				"N",
			)
		);
		echo SelectBoxFromArray("find_active", $arr, $find_active, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>