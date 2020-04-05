<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2009 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param CDatabase $DB
 * @param integer $voteId
 * @param CAdminMainChain $adminChain
 */
global $APPLICATION, $DB, $adminChain, $CACHE_MANAGER;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$voteId = intval($request->getQuery("VOTE_ID"));
$sTableID = "tbl_vote_question".$voteId;
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

define("HELP_FILE","vote_list.php");

$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("vote");
IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE", array("#ID#"=> $voteId)));
try
{
	$vote = \Bitrix\Vote\Vote::loadFromId($voteId);
	if (!$vote->canEdit($USER->GetID()))
		throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
}
catch(Exception $e)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($e->getMessage());
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arFilterFields = Array(
	"find_id", 
	"find_id_exact_match",
	"find_active",
	"find_diagram",
	"find_required",
	"find_question",
	"find_question_exact_match");

$lAdmin->InitFilter($arFilterFields);
/********************************************************************
				Actions
********************************************************************/
InitBVar($find_id_exact_match);
InitBVar($find_question_exact_match);
$arFilter = array(
	"ID" => $find_id,
	"ID_EXACT_MATCH" => $find_id_exact_match,
	"ACTIVE" => $find_active,
	"DIAGRAM" => $find_diagram,
	"REQUIRED" => $find_required,
	"QUESTION" => $find_question,
	"QUESTION_EXACT_MATCH" => $find_question_exact_match);

if (!check_bitrix_sessid())
{
	//
}
else if ($lAdmin->EditAction())
{
	if (!function_exists("__makeFileArray"))
	{
		function __makeFileArray($data, $del = false)
		{
			global $APPLICATION;
			$emptyFile = array(
				"name" => null,
				"type" => null,
				"tmp_name" => null,
				"error" => 4,
				"size" => 0,
			);
			$result = false;
			if ($del)
			{
				$result = $emptyFile + array("del" => "Y");
			}
			elseif (is_null($data))
			{
				$result = $emptyFile;
			}
			elseif (is_string($data))
			{
				$io = CBXVirtualIo::GetInstance();
				$normPath = $io->CombinePath("/", $data);
				$absPath = $io->CombinePath($_SERVER["DOCUMENT_ROOT"], $normPath);
				if ($io->ValidatePathString($absPath) && $io->FileExists($absPath))
				{
					$perm = $APPLICATION->GetFileAccessPermission($normPath);
					if ($perm >= "W")
						$result = CFile::MakeFileArray($io->GetPhysicalName($absPath));
				}

				if ($result === false)
					$result = $emptyFile;
			}
			elseif (is_array($data))
			{
				if (is_uploaded_file($data["tmp_name"]))
				{
					$result = $data;
				}
				else
				{
					$emptyFile = array(
						"name" => null,
						"type" => null,
						"tmp_name" => null,
						"error" => 4,
						"size" => 0,
					);
					if ($data == $emptyFile)
						$result = $emptyFile;
				}
				if ($result === false)
					$result = $emptyFile;
			}
			else
			{
				$result = $emptyFile;
			}

			return $result;
		}
	}
	if(is_array($_FILES['FIELDS']))
		CAllFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

	foreach($_POST['FIELDS'] as $ID=>$arFields)
	{

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if(array_key_exists("IMAGE_ID", $arFields))
		{
			$arFields["IMAGE_ID"] = __makeFileArray(
				$arFields["IMAGE_ID"],
				$_REQUEST["FIELDS_del"][$ID]["IMAGE_ID"] === "Y"
			);
		}

		if (!CVoteQuestion::Update($ID, $arFields))
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".
				((($res = $APPLICATION->GetException()) && !!$res && ($text = $res->GetString()) && !!$text) ?: GetMessage("VOTE_SAVE_ERROR")), $ID);
		else if (defined("BX_COMP_MANAGED_CACHE"))
			$CACHE_MANAGER->ClearByTag("vote_form_question_".$ID);
	}
}
else if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsData = CVoteQuestion::GetList($voteId, $by, $order, $arFilter, $is_filtered);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	@set_time_limit(0);
	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if($ID > 0)
		{
			switch($_REQUEST['action'])
			{
				case "delete":
					if(!CVoteQuestion::Delete($ID))
						$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
					break;
				case "activate":
				case "deactivate":
					CVoteQuestion::setActive($ID, ($_REQUEST['action'] == "activate"));
					break;
			}
		}
	}
}

$rsData = CVoteQuestion::GetList($voteId, $by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));
$lAdmin->bMultipart = true;
$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
		array("id"=>"TIMESTAMP_X", "content"=>GetMessage("VOTE_TIMESTAMP_X"), "sort"=>"s_timestamp_x", "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage("VOTE_ACTIVE"), "sort"=>"s_active", "default"=>true),
		array("id"=>"DIAGRAM", "content"=>GetMessage("VOTE_DIAGRAM"), "sort"=>"s_diagram", "default"=>true),
		array("id"=>"REQUIRED", "content"=>GetMessage("VOTE_REQUIRED"), "sort"=>"s_required", "default"=>true),
		array("id"=>"C_SORT", "content"=>GetMessage("VOTE_C_SORT"), "sort"=>"s_c_sort", "default"=>true),
		array("id"=>"IMAGE_ID", "content"=>GetMessage("VOTE_IMAGE_ID"), "default"=>true),
		array("id"=>"QUESTION", "content"=>GetMessage("VOTE_QUESTION"), "sort"=>"s_question", "default"=>true),
	)
);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("QUESTION", ($arRes["QUESTION_TYPE"]=="text" ? htmlspecialcharsex($arRes["QUESTION"]) : HTMLToTxt($arRes["QUESTION"])));
	$row->AddViewFileField("IMAGE_ID", array(
		"IMAGE" => "Y",
		"PATH" => "Y",
		"FILE_SIZE" => "Y",
		"DIMENSIONS" => "Y",
		"IMAGE_POPUP" => "Y"
		)
	);
	$row->AddViewField("SITE",trim($str, " ,"));
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("DIAGRAM");
	$row->AddCheckField("REQUIRED");
	$row->AddInputField("C_SORT");
	$f_QUESTION_TEXT = ($arRes["QUESTION_TYPE"]=="text" ? "checked" : "");
	$f_QUESTION_HTML = ($arRes["QUESTION_TYPE"]=="text" ? "" : "checked");
	$sHTML = <<<HTML
		<input type="radio" name="FIELDS[{$f_ID}][QUESTION_TYPE]" value="text" id="{$f_ID}QUESTIONTEXT" {$f_QUESTION_TEXT} /><label for="{$f_ID}QUESTION">text</label>
		<input type="radio" name="FIELDS[{$f_ID}][QUESTION_TYPE]" value="html" id="{$f_ID}QUESTIONHTML" {$f_QUESTION_HTML} /><label for="{$f_ID}QUESTION">html</label><br>
		<textarea rows="10" cols="70" name="FIELDS[{$f_ID}][QUESTION]">{$f_QUESTION}</textarea>
HTML;
	$row->AddEditField("QUESTION", $sHTML);

	$row->AddFileField("IMAGE_ID", array(
		"IMAGE" => "Y",
		"PATH" => "Y",
		"FILE_SIZE" => "Y",
		"DIMENSIONS" => "Y",
		"IMAGE_POPUP" => "Y",
		), array(
			'upload' => true,
			'medialib' => false,
			'file_dialog' => false,
			'cloud' => false,
			'del' => true,
			'description' => false
		)
	);

	$row->AddActions(array(
		array("ICON"=>"edit", "DEFAULT" => true, "TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("vote_question_edit.php?ID=$f_ID&VOTE_ID=$voteId")),
		array("ICON"=>"delete", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".GetMessage("VOTE_CONFIRM_DEL_QUESTION")."')) window.location='vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=$voteId&action=delete&ID=$f_ID&".bitrix_sessid_get()."'")
	));
}

$lAdmin->AddFooter(
		array(
				array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
				array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("VOTE_DELETE"),
	"activate"=>GetMessage("VOTE_ACTIVATE"),
	"deactivate"=>GetMessage("VOTE_DEACTIVATE")
));
$lAdmin->AddAdminContextMenu(array(array(
	"TEXT"	=> GetMessage("VOTE_CREATE"),
	"TITLE"=>GetMessage("VOTE_ADD_QUESTION"),
	"LINK"=>"vote_question_edit.php?lang=".LANG."&VOTE_ID=$voteId",
	"ICON" => "btn_new"
)));
$lAdmin->CheckListMode();
/********************************************************************
				Form
********************************************************************/
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$context = new CAdminContextMenu(array(array(
	"TEXT"	=> GetMessage("VOTE_BACK_TO_VOTE"),
	"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteId,
	"ICON" => "btn_list")));
$context->Show();
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
				GetMessage("VOTE_FLT_ID"),
				GetMessage("VOTE_FLT_ACTIVE"),
				GetMessage("VOTE_FLT_DIAGRAM"),
				GetMessage("VOTE_FLT_REQUIRED")
		)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("VOTE_F_QUESTION")?></b></td>
	<td><input type="text" name="find_question" value="<?=htmlspecialcharsbx($find_question)?>" size="47"><?=InputType("checkbox", "find_question_exact_match", "Y", $find_question_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?=htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("VOTE_F_ACTIVE")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("VOTE_YES"), GetMessage("VOTE_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<tr valign="top">
	<td><?=GetMessage("VOTE_F_DIAGRAM")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("VOTE_YES"), GetMessage("VOTE_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_diagram", $arr, htmlspecialcharsbx($find_diagram), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<tr valign="top">
	<td><?=GetMessage("VOTE_F_REQUIRED")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("VOTE_YES"), GetMessage("VOTE_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_required", $arr, htmlspecialcharsbx($find_required), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>"/bitrix/admin/vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=$voteId", "form"=>"form1"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); 
?>
