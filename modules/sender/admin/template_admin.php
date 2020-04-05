<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Sender\MailingChainTable;
use \Bitrix\Sender\PostingTable;
use \Bitrix\Sender\PostingRecipientTable;

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if($request->get('action') == 'get_template')
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$templateType = $request->get('template_type');
	$templateId = $request->get('template_id');
	$templateCharset = $request->get('template_charset');

	\Bitrix\Main\Loader::includeModule('fileman');
	if ($templateType == 'USER' && !$templateId && isset($_SESSION['bx_sender_template_tmp']))
	{
		echo base64_decode($_SESSION['bx_sender_template_tmp']);
	}
	else
	{
		$template = \Bitrix\Sender\Preset\Template::getById($templateType, $templateId);
		if ($template)
		{
			echo \Bitrix\Fileman\Block\Editor::getHtmlForEditor($template['HTML'], $templateCharset);
		}
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

$sTableID = "tbl_sender_template";
$ID = intval($_REQUEST["ID"]);

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
	"find_name",
	"find_active",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array(
		"ID" => ($find!="" && $find_type == "id"? $find:$find_id),
		"%NAME" => ($find!="" && $find_type == "name"? $find:$find_name),
		"=ACTIVE" => $find_active,
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
		$arAllowedFieldsForUpdate = array('NAME', 'ACTIVE');
		$dataPrimary = array('ID' => $ID);
		$arData = \Bitrix\Sender\TemplateTable::getRowById($dataPrimary);
		if($arData)
		{
			foreach($arFields as $key=>$value)
				if(in_array($key, $arAllowedFieldsForUpdate)) $arData[$key]=$value;

			unset($arData['ID']);
			$dataUpdateDb = \Bitrix\Sender\TemplateTable::update($dataPrimary, $arData);
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
			$dataDeleteDb = \Bitrix\Sender\TemplateTable::delete($dataPrimary);
			if(!$dataDeleteDb->isSuccess())
			{
				$DB->Rollback();
				$LAST_ERROR = $dataDeleteDb->getErrorMessages();
				$lAdmin->AddGroupError(GetMessage("rub_del_err") . '<br>' . $LAST_ERROR[0], $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$arFields = array(
				"ACTIVE" => ($_REQUEST['action'] == "activate" ? "Y" : "N")
			);
			$dataUpdateDb = \Bitrix\Sender\TemplateTable::update($dataPrimary, $arFields);
			if(!$dataUpdateDb->isSuccess())
			{
				$LAST_ERROR = $dataUpdateDb->getErrorMessages();
				$LAST_ERROR = $LAST_ERROR[0];
				$lAdmin->AddGroupError(GetMessage("rub_save_error") . '<br>' . $LAST_ERROR, $ID);
			}
			break;
		}

	}
}

$templateListDb = \Bitrix\Sender\TemplateTable::getList(array(
	'select' => array('*'),
	'filter' => $arFilter,
	'order' => array($by=>$order)
));

$rsData = new CAdminResult($templateListDb, $sTableID);
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
	array(	"id"		=>"ACTIVE",
		"content"	=>GetMessage("rub_act"),
		"sort"		=>"ACTIVE",
		"default"	=>true,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="sender_template_edit.php?ID='.$f_ID.'&amp;lang='.LANG.'">'.$f_NAME.'</a>');
	$row->AddCheckField("ACTIVE");

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("rub_edit"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_template_edit.php?ID=".$f_ID)
	);
	if ($POST_RIGHT>="W")
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("rub_del"),
			"ACTION"=>"if(confirm('".GetMessage('rub_del_conf')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);

	$arActions[] = array("SEPARATOR"=>true);


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
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"sender_template_edit.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("POST_ADD_TITLE"),
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
		"ID",
		GetMessage("rub_f_name"),
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
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_name")?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
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