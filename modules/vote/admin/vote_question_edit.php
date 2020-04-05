<?
/*
##############################################
# Bitrix: SiteManager						#
# Copyright (c) 2004 - 2009 Bitrix			#
# http://www.bitrix.ru						#
# mailto:admin@bitrix.ru					#
##############################################
*//**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param integer $ID
 */
use \Bitrix\Main\Localization\Loc;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
if($APPLICATION->GetGroupRight("vote") <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

ClearVars();
IncludeModuleLangFile(__DIR__."/vote_question_edit.php");
\Bitrix\Main\Loader::includeModule("vote");
$userOpt = \CUserOptions::getOption("admin_panel", "voting_view");
if ($userOpt["question_edit"] != "old")
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	/* @var $request \Bitrix\Main\HttpRequest */
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	?><?$APPLICATION->IncludeComponent("bitrix:voting.admin.question.edit", ".default",
	array(
		"VOTE_ID" => $request->getQuery("VOTE_ID"),
		"QUESTION_ID" => $request->getQuery("ID")
	));

	?><?=BeginNote();?><a href="javascript:void(0);" onclick="BX.userOptions.save('admin_panel', 'voting_view', 'question_edit', 'old');BX.reload();return false;"><?
		?><?=Loc::getMessage("VOTE_BACK_TO_OLD_PAGE_TITLE")?></a><?=EndNote();

	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE", "vote_list.php");
$old_module_version = CVote::IsOldVersion();

$aTabs = array(
	array("DIV" => "edit2", "TAB" => GetMessage("VOTE_QUESTION"), "ICON"=>"vote_question_edit", "TITLE"=>GetMessage("VOTE_QUESTION_TEXT")),
	array("DIV" => "edit3", "TAB" => GetMessage("VOTE_ANSWERS"), "ICON"=>"vote_question_edit", "TITLE"=>GetMessage("VOTE_ANSWER_LIST")),
);
/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;
$arSort = array(0);

$ID = intval($request->getQuery("ID"));
$voteId = intval($request->getQuery("VOTE_ID"));

$arQuestion = array();
$arAnswers = array();
$arAnswersFields = array();

if ($ID > 0 && ($db_res = CVoteQuestion::GetByID($ID)) && ($arQuestion = $db_res->fetch()))
{
	$ii = 1;
	$voteId = intval($arQuestion["VOTE_ID"]);
	$db_res = CVoteAnswer::GetList($ID);
	while ($db_res && ($res = $db_res->fetch()))
	{
		$arAnswers[$ii] = $res;
		$ii++;
	}
}
else
{
	$ID = 0;
}

if ($ID <= 0)
{
	$arQuestion = array(
		"ACTIVE"		=> "Y",
		"VOTE_ID"		=> $voteId,
		"C_SORT"		=> CVoteQuestion::GetNextSort($voteId),
		"QUESTION"		=> "",
		"QUESTION_TYPE"	=> "html",
		"IMAGE_ID"		=> "",
		"DIAGRAM"		=> "Y",
		"REQUIRED"		=> "N",
		"DIAGRAM_TYPE"	=> VOTE_DEFAULT_DIAGRAM_TYPE,
		"TEMPLATE"		=> "default.php",
		"TEMPLATE_NEW"	=> "default.php");
}

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

$sDocTitle = ($ID > 0 ? str_replace("#ID#", $ID, GetMessage("VOTE_EDIT_RECORD")) : GetMessage("VOTE_NEW_RECORD"));
$APPLICATION->SetTitle($sDocTitle);

/********************************************************************
ACTIONS
 ********************************************************************/


if (!($_SERVER["REQUEST_METHOD"] == "POST" && (strlen($save)>0 || strlen($apply)>0))) {}
elseif (!check_bitrix_sessid()) {}
else
{
	$bVarsFromForm = false;
	$_FILES["IMAGE_ID"] = (is_array($_FILES["IMAGE_ID"]) ? $_FILES["IMAGE_ID"] : array());
	$arFields = array(
		"ACTIVE"		=> (isset($_REQUEST["ACTIVE"])?$_REQUEST["ACTIVE"]:'N'),
		"VOTE_ID"		=> $voteId,
		"C_SORT"		=> $_REQUEST["C_SORT"],
		"QUESTION"		=> $_REQUEST["QUESTION"],
		"QUESTION_TYPE"	=> $_REQUEST["QUESTION_TYPE"],
		"IMAGE_ID"		=> ($_FILES["IMAGE_ID"] + ($_REQUEST["IMAGE_ID_del"] == "Y" ? array("del" => "Y") : array())),
		"DIAGRAM"		=> (isset($_REQUEST["DIAGRAM"])?$_REQUEST["DIAGRAM"]:'N'),
		"REQUIRED"		=> (isset($_REQUEST["REQUIRED"])?$_REQUEST["REQUIRED"]:'N'),
		"DIAGRAM_TYPE"	=> $_REQUEST["DIAGRAM_TYPE"],
		"FIELD_TYPE"	=> \Bitrix\Vote\QuestionTypes::COMPATIBILITY,
		"TEMPLATE"		=> $_REQUEST["TEMPLATE"],
		"TEMPLATE_NEW"	=> $_REQUEST["TEMPLATE_NEW"]);
	foreach ($_REQUEST["ANSWER"] as $pid)
	{
		$pid = intval($pid);
		if ($pid <= 0)
			continue;
		$arAnswer = array(
			"ID" => intval($_REQUEST["ANSWER_ID_".$pid]),
			"QUESTION_ID" => $ID,
			"ACTIVE" => ($_REQUEST["ACTIVE_".$pid] == 'Y' ? 'Y' : 'N'),
			"C_SORT" => $_REQUEST["C_SORT_".$pid],
			"MESSAGE" => ($_REQUEST["MESSAGE_".$pid] != ' ') ? trim($_REQUEST["MESSAGE_".$pid]):' ',
			"FIELD_TYPE" => $_REQUEST["FIELD_TYPE_".$pid],
			"FIELD_WIDTH" => intval($_REQUEST["FIELD_WIDTH_".$pid]),
			"FIELD_HEIGHT" => intval($_REQUEST["FIELD_HEIGHT_".$pid]),
			"FIELD_PARAM" => trim($_REQUEST["FIELD_PARAM_".$pid]),
			"COLOR" => trim($_REQUEST["COLOR_".$pid]));
		$arAnswersFields[$pid] = $arAnswer;
		if ($arAnswer["ID"] <= 0 && empty($arAnswer["MESSAGE"])):
			unset($arAnswersFields[$pid]);
		endif;
	}

	if ($ID > 0):
		$result = CVoteQuestion::Update($ID, $arFields);
	else:
		$result = $ID = CVoteQuestion::Add($arFields);
	endif;

	$aMsg = array();
	if (!$result)
		$bVarsFromForm = true;
	else
	{
		foreach ($arAnswersFields as $pid => $arAnswer)
		{
			$bResult = true;
			$APPLICATION->ResetException();
			if ($_REQUEST["del_".$pid] == "Y"):
				if ($arAnswer["ID"] > 0):
					CVoteAnswer::Delete($arAnswer["ID"]);
				endif;
				unset($arAnswersFields[$pid]);
			elseif ($arAnswer["ID"] > 0):
				$bResult = CVoteAnswer::Update($arAnswer["ID"], $arAnswer);
			else:
				$arAnswer["QUESTION_ID"] = $ID;
				$bResult = CVoteAnswer::Add($arAnswer);
				if ($bResult):
					$arAnswersFields[$pid]["ID"] = $bResult;
				endif;
			endif;
			if (!$bResult):
				$e = $APPLICATION->GetException();
				$aMsg[]	= array(
					"id" => "ANSWER_ID_".$pid,
					"text" => ($e ? $e->Getstring() : "Error"));
			endif;
			$bVarsFromForm = ($bVarsFromForm ? $bVarsFromForm : !$bResult);
		}
	}
	if (!$bVarsFromForm):
		if (strlen($save)>0):
			LocalRedirect("vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$voteId);
		endif;
		LocalRedirect("vote_question_edit.php?lang=".LANGUAGE_ID."&ID=$ID&VOTE_ID=".$voteId."&".$tabControl->ActiveTabParam());
	elseif (!empty($aMsg)):
		$e = new CAdminException($aMsg);
	else:
		$e = $APPLICATION->GetException();
	endif;
	$message = new CAdminMessage(GetMessage("VOTE_GOT_ERROR"), $e);
	$arFields["IMAGE_ID"] = (intval($arQuestion["IMAGE_ID"]) > 0 ? $arQuestion["IMAGE_ID"] : "");
	$arQuestion = $arFields;
	$arAnswers = $arAnswersFields;
}
/********************************************************************
/ACTIONS
 ********************************************************************/

/********************************************************************
Data
 ********************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($message):
	echo $message->Show();
endif;

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("VOTE_QUESTIONS"),
		"TITLE"	=> GetMessage("VOTE_QUESTIONS_LIST"),
		"LINK"	=> "/bitrix/admin/vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$voteId,
		"ICON" => "btn_list"));

if ($ID > 0)
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_CREATE"),
		"TITLE"	=> GetMessage("VOTE_CREATE_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/vote_question_edit.php?VOTE_ID=$voteId&lang=".LANGUAGE_ID,
		"ICON" => "btn_new");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_DELETE"),
		"TITLE"	=> GetMessage("VOTE_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_question_list.php?action=delete&ID=$ID&VOTE_ID=$voteId&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
		"ICON" => "btn_delete");
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

$z = CVoteAnswer::GetList($ID);
/************** Table of colors ************************************/
$t_COL = array("00", "33", "66", "99", "CC", "FF");
?>
<div id="ColorPick" style="visibility:hidden;position:absolute;top:0;left:0 ">
	<table cellspacing="0" cellpadding="1" border="0" bgcolor="#666666">
		<tr><td colspan=2>
				<table cellspacing="1" cellpadding="0" border="0" bgcolor="#FFFFFF">
					<?
					for ($i = 0; $i < 216; $i++)
					{
						$t_R = $i%6;
						$t_G = floor($i/36)%6;
						$t_B = floor($i/6)%6;
						$t_curCOL="#".$t_COL[$t_R].$t_COL[$t_G].$t_COL[$t_B];
						print ($i%18==0) ? "<tr>" : "";
						?>
						<td bgcolor='<?=$t_curCOL?>'><a href='javascript:void(0)' onmousedown='javascript:col_set("<?=$t_curCOL?>")' <?
							?>onmouseover='javascript:col_show("<?=$t_curCOL?>")'><img src=/bitrix/images/1.gif border="0" width="10" height="10"></a></td>
						<?
					}
					?>
				</table>
			</td>
		</tr>
		<tr>
			<td width=50% style="border:1px solid black" id="t_fillCOL"><img src=/bitrix/images/1.gif style="width:100%;height:5px"></td>
			<td width=50%><input id="t_COL" size=10 style="width:100%;border:1px solid black"></td>
		</tr>
	</table></div>
<SCRIPT LANGUAGE="JavaScript">
	<!--
	jsUtils.addEvent(document, "mousedown", function(e){hidePicker();});
	jsUtils.addEvent(document, "keypress", function(e){hidePicker();});

	top.elem_id = 0;

	function col_show(clr)
	{
		BX('t_COL').value = clr;
		BX('t_fillCOL').style.backgroundColor = clr;
	}

	function col_set(clr, node) {
		var node = node || top.node;
		node.value = clr;
		BX.adjust(node.parentNode, { "style" : { "backgroundColor" : clr } });
	}

	function hidePicker() {
		BX.adjust(BX("ColorPick"), { "style" : { "visibility" : "hidden" } });
	}

	function ColorPicker(node)
	{
		top.node = node;
		try {
			var
				res = BX.pos(node);
			BX.adjust(BX('ColorPick'), { "style" : {
					"visibility" : "visible",
					"top" : (res["top"] + 22) + 'px',
					"left" : (res["left"] - 204) + 'px'}});
			col_show(node.value);
		} catch(e){}
	}
	//-->
</SCRIPT>
<?
/************** Table of colors/************************************/
?>
<form name="form1" method="POST" action="" enctype="multipart/form-data">
	<script type="text/javascript">
		<!--
		function FIELD_TYPE_CHANGE(i)
		{
			v = document.getElementById("FIELD_TYPE_"+i)[document.getElementById("FIELD_TYPE_"+i).selectedIndex].value;
			document.getElementById("FIELD_WIDTH_"+i).disabled=false;
			document.getElementById("FIELD_HEIGHT_"+i).disabled=false;
			if (v!=4 && v!=5)
			{
				document.getElementById("FIELD_WIDTH_"+i).disabled=true;
			}
			if (v!=5 && v!=3)
			{
				document.getElementById("FIELD_HEIGHT_"+i).disabled=true;
			}
		}
		function OnDiagramFlagChange()
		{
			var diagramFlag = document.getElementById("DIAGRAM");
			document.getElementById("DIAGRAM_TYPE").disabled = !diagramFlag.checked;
		}

		//-->
	</SCRIPT>
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="ID" value="<?=$ID?>" />
	<input type="hidden" name="VOTE_ID" value="<?=$voteId?>" />
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">

	<?
	$tabControl->Begin();
	?>

	<?
	/************** General Tab ****************************************/
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td><?=GetMessage("VOTE_VOTE")?></td>
		<td>[<a href="vote_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$vote["ID"]?>" title="<?=GetMessage("VOTE_CONF")?>"><?=$vote["ID"]?></a>]&nbsp;
			<?=htmlspecialcharsbx($vote["TITLE"])?></td>
	</tr>
	<?if (strlen($arQuestion["TIMESTAMP_X"]) > 0):?>
		<tr><td><?=GetMessage("VOTE_TIMESTAMP")?></td>
			<td><?=$arQuestion["TIMESTAMP_X"]?></td>
		</tr>
		<tr><td><?=GetMessage("VOTE_COUNTER_QUESTION")?></td>
			<td><?=$arQuestion["COUNTER"]?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("VOTE_ACTIVE")?></td>
		<td width="60%"><?=InputType("checkbox", "ACTIVE", "Y", $arQuestion["ACTIVE"], false)?></td>
	</tr>
	<tr><td><?=GetMessage("VOTE_C_SORT")?></td>
		<td><input type="text" id="C_SORT" name="C_SORT" size="5" maxlength="18" value="<?=intval($arQuestion["C_SORT"])?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_DIAGRAM")?></td>
		<td><input type="checkbox" name="DIAGRAM" id="DIAGRAM" value="Y" onclick="OnDiagramFlagChange()" <?
			?> <?=($arQuestion["DIAGRAM"] == "Y" ? "checked='checked'" : "")?> /></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_REQUIRED")?></td>
		<td><input type="checkbox" name="REQUIRED" id="REQUIRED" value="Y" onclick="OnDiagramFlagChange()" <?
			?> <?=($arQuestion["REQUIRED"] == "Y" ? "checked='checked'" : "")?> /></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_DIAGRAM_TYPE")?>:</td>
		<td><?echo SelectBoxFromArray("DIAGRAM_TYPE", GetVoteDiagramList(), $arQuestion["DIAGRAM_TYPE"]);?>
			<script type="text/javascript">OnDiagramFlagChange();</script>
		</td>
	</tr>
	<?if (COption::GetOptionString("vote", "VOTE_COMPATIBLE_OLD_TEMPLATE", "N") == "Y"):?>
		<?if ($old_module_version=="Y"):?>
			<tr>
				<td><?=GetMessage("VOTE_TEMPLATE")?></td>
				<td><?echo SelectBoxFromArray("TEMPLATE", GetTemplateList("RQ"), $arQuestion["TEMPLATE"], " ");
					?></td>
			</tr>
		<?
		else:
			$arr = CMainAdmin::GetTemplateList(COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_QUESTION_NEW"));
			$arrTemplates = array("reference" => $arr, "reference_id" => $arr);
			?>
			<tr>
				<td><?=GetMessage("VOTE_TEMPLATE")?></td>
				<td><?echo SelectBoxFromArray("TEMPLATE_NEW", $arrTemplates, $arQuestion["TEMPLATE_NEW"], " ");
					?></td>
			</tr>
		<?endif;?>
	<?endif?>
	<tr>
		<td><?=GetMessage("VOTE_IMAGE")?></td>
		<td><?=CFile::InputFile("IMAGE_ID", 20, $arQuestion["IMAGE_ID"]);?><?
			if (!is_array($arQuestion["IMAGE_ID"]) && strlen($arQuestion["IMAGE_ID"]) > 0):
				?><br /><?=CFile::ShowImage($arQuestion["IMAGE_ID"], 200, 200, "border=0", "", true)?><?
			endif;?>
		</td>
	</tr>
	<tr class="heading" id="tr_QUESTION_LABEL">
		<td colspan="2"><?=GetMessage("VOTE_QUESTION_TEXT")?></td>
	</tr>
	<?
	if(COption::GetOptionString("vote", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):?>
		<tr>
			<td align="center" colspan="2"><?
				CFileMan::AddHTMLEditorFrame("QUESTION", htmlspecialcharsbx($arQuestion["QUESTION"]), "QUESTION_TYPE", $arQuestion["QUESTION_TYPE"], array('height' => '200', 'width' => '100%'));
				?></td>
		</tr>
	<?else:?>
		<tr>
			<td align="center" colspan="2"><?=InputType("radio","QUESTION_TYPE","text",$arQuestion["QUESTION_TYPE"],false)?>Text &nbsp;/&nbsp;<?=InputType("radio","QUESTION_TYPE","html",$arQuestion["QUESTION_TYPE"],false)?>HTML</td>
		</tr>
		<tr>
			<td align="center" colspan="2"><textarea name="QUESTION" style="width:100%" rows="23"><?=$arQuestion["QUESTION"]?></textarea></td>
		</tr>
	<?endif;?>



	<?
	/************** Answers Tab ****************************************/
	$tabControl->BeginNextTab();
	?>
	<tr class="adm-detail-required-field">
		<td colspan="2">
			<script type='text/javascript'>

				function addQuestionRow(tthis)
				{
					BX.unbindAll(tthis);
					var name = tthis.getAttribute('name');
					var num = parseInt(name.substr(name.indexOf('_')+1));
					var newnum = num+1;
					var node = tthis.parentNode.parentNode.cloneNode(true);
					node = tthis.parentNode.parentNode.parentNode.appendChild(node);
					BX.findChild(node, {property:{name:'ANSWER[]'}},true).setAttribute('value', newnum);
					BX.findChild(node, {property:{name:'ANSWER_ID_'+num}},true).setAttribute('name', 'ANSWER_ID_'+newnum);
					BX.findChild(node, {property:{name:'MESSAGE_'+num}},true).setAttribute('name', 'MESSAGE_'+newnum);
					BX.findChild(node, {property:{name:'MESSAGE_'+newnum}},true).value = '';
					BX.findChild(node, {property:{name:'FIELD_TYPE_'+num}},true).setAttribute('name', 'FIELD_TYPE_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_TYPE_'+newnum}},true).setAttribute('id', 'FIELD_TYPE_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_WIDTH_'+num}},true).setAttribute('name', 'FIELD_WIDTH_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_WIDTH_'+newnum}},true).setAttribute('id', 'FIELD_WIDTH_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_HEIGHT_'+num}},true).setAttribute('name', 'FIELD_HEIGHT_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_HEIGHT_'+newnum}},true).setAttribute('id', 'FIELD_HEIGHT_'+newnum);
					BX.findChild(node, {property:{name:'FIELD_PARAM_'+num}},true).setAttribute('name', 'FIELD_PARAM_'+newnum);
					BX.findChild(node, {property:{name:'C_SORT_'+num}},true).setAttribute('name', 'C_SORT_'+newnum);
					BX.findChild(node, {property:{name:'C_SORT_'+newnum}},true).setAttribute('value', 100*newnum);
					var node1 = BX.findChild(node, {property:{name:'COLOR_'+num}},true);
					BX.adjust(node1, {
						"attrs" : {"name" : "COLOR_" + newnum, "id" : "COLOR_" + newnum},
						"events" : {"click" : function(){ColorPicker(this);}, "change" : function() {col_set(this.value, this);}}});
					BX.findChild(node, {attr:{id:'COLB'+num}},true).setAttribute('id', 'COLB'+newnum);
					BX.adjust(BX.findChild(node, {property:{name:'ACTIVE_'+num}},true), {"attrs" : {"name" : 'ACTIVE_'+newnum, "id" : 'ACTIVE_'+newnum}});
					BX.bind(BX.findChild(node, {property:{name:'FIELD_TYPE_'+newnum}},true), 'change', function() {FIELD_TYPE_CHANGE(newnum);});
					BX.bind(BX.findChild(node, {property:{name:'MESSAGE_'+newnum}},true),'keyup', function() {
						addQuestionRow(this);
					});
					BX.bind(BX.findChild(node, {property:{name:'MESSAGE_'+newnum}},true),'change', function() {
						addQuestionRow(this);
					});

					setTimeout(function() {
						var r = BX.findChildren(node, {tag: /^(input|select|textarea)$/i}, true);
						if (r && r.length > 0)
						{
							for (var i=0,l=r.length;i<l;i++)
							{
								if (r[i].form && r[i].form.BXAUTOSAVE)
									r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
								else
									break;
							}
						}
					}, 10);
				}

				function lastInputChange()
				{
					var answerList = document.getElementById('answerlist');
					var answerRow = BX.findChild(answerList, {tag:'tr'}, true);
					var nextRow = answerRow;
					BX.style(answerRow, 'color','#00ff00');
					while (nextRow = BX.findNextSibling(nextRow, null))
					{
						answerRow = nextRow;
					}
					inputField = BX.findChild(answerRow, {tag:'input', property:{'type':'text'}}, true);
					BX.bind(inputField,'keyup', function() {
						addQuestionRow(this);
					});
					BX.bind(inputField,'change', function() {
						addQuestionRow(this);
					});
				}

				BX.ready(function() {
					lastInputChange();
				});

			</script>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal" id='answerlist'>
				<tr class="heading" >
					<td>ID</td>
					<td nowrap width="95%"><?=GetMessage("VOTE_MESSAGE")?><span class="required"><sup>1</sup></span></td>
					<td><?=GetMessage("VOTE_FIELD_TYPE")?></td>
					<td><?=GetMessage("VOTE_FIELD_WIDTH")?></td>
					<td><?=GetMessage("VOTE_FIELD_HEIGHT")?></td>
					<td><?=GetMessage("VOTE_FIELD_PARAM")?></td>
					<td><?=GetMessage("VOTE_SORT")?></td>
					<td><?=GetMessage("VOTE_COLOR")?></td>
					<td><?=GetMessage("VOTE_ACT")?></td>
					<td><?=GetMessage("VOTE_DEL")?></td>
				</tr>
				<?

				$arSort = array(0);
				foreach ($arAnswers as $i => $arAnswer)
				{
					$arSort[] = intval($arAnswer["C_SORT"]);
					?>
					<tr>
						<td>
							<input type="hidden" name="ANSWER[]" value="<?=$i?>" />
							<input type="hidden" name="ANSWER_ID_<?=$i?>" value="<?=intval($arAnswer["ID"])?>" />
							<?=(intval($arAnswer["ID"]) > 0 ? $arAnswer["ID"] : "")?></td>
						<td><input type="text" name="MESSAGE_<?=$i?>" value="<?=htmlspecialcharsbx($arAnswer["MESSAGE"])?>" style="width:100%;" /></td>
						<td><?=SelectBoxFromArray("FIELD_TYPE_".$i, GetAnswerTypeList(), $arAnswer["FIELD_TYPE"], "", "OnChange=\"FIELD_TYPE_CHANGE(".$i.")\" class='typeselect'")?></td>
						<td><input type="text" name="FIELD_WIDTH_<?=$i?>" id="FIELD_WIDTH_<?=$i?>" size="3" <?
							?>value="<?=(intval($arAnswer["FIELD_WIDTH"])>0 ? intval($arAnswer["FIELD_WIDTH"]) : "")?>" <?
							?><?=($arAnswer["FIELD_TYPE"]!=4 && $arAnswer["FIELD_TYPE"]!=5 ? "disabled='disabled'" : "")?> /></td>
						<td><input type="text" name="FIELD_HEIGHT_<?=$i?>" id="FIELD_HEIGHT_<?=$i?>" size="3" <?
							?>value="<?=(intval($arAnswer["FIELD_HEIGHT"])>0 ? intval($arAnswer["FIELD_HEIGHT"]) : "")?>" <?
							?><?=($arAnswer["FIELD_TYPE"]!=4 && $arAnswer["FIELD_TYPE"]!=5 ? "disabled='disabled'" : "")?> /></td>
						<td><input type="text" name="FIELD_PARAM_<?=$i?>" value="<?=htmlspecialcharsbx($arAnswer["FIELD_PARAM"])?>" size="10" /></td>
						<td><input type="text" name="C_SORT_<?=$i?>" value="<?=htmlspecialcharsbx($arAnswer["C_SORT"])?>" size="3" /></td>
						<td id="COLB<?=$i?>" style="background:<?=htmlspecialcharsbx($arAnswer["COLOR"])?>;">
							<input id="COLOR_<?=$i?>" name="COLOR_<?=$i?>" onchange="col_set(this.value, this)" onclick="ColorPicker(this);" <?
							?>type="text" value="<?=htmlspecialcharsbx($arAnswer["COLOR"])?>" size="7" />
						</td>
						<td><?=InputType("checkbox", "ACTIVE_".$i,"Y", $arAnswer["ACTIVE"], false);?></td>
						<td><input type="checkbox" name="del_<?=$i?>" value="Y" /></td>
					</tr>
					<?
				}
				$i = 0;
				if (!empty($arAnswers)):
					$i = max(array_keys($arAnswers));
				endif;
				$s = intval(max($arSort));
				for ($ii = 1; $ii <= 10; $ii++)
				{
					$i++;
					$s += 100;
					?>
					<tr>
						<td>
							<input type="hidden" name="ANSWER[]" value="<?=$i?>" />
							<input type="hidden" name="ANSWER_ID_<?=$i?>" value="0" />
						</td>
						<td><input type="text" name="MESSAGE_<?=$i?>" value="" style="width:100%;" /></td>
						<td><?=SelectBoxFromArray("FIELD_TYPE_".$i, GetAnswerTypeList(), "radio", "", "onchange=\"FIELD_TYPE_CHANGE(".$i.")\" class='typeselect'");
							?></td>
						<td><input type="text" id="FIELD_WIDTH_<?=$i?>" name="FIELD_WIDTH_<?=$i?>" value="" size="3" disabled="disabled" /></td>
						<td><input type="text" id="FIELD_HEIGHT_<?=$i?>" name="FIELD_HEIGHT_<?=$i?>" value="" size="3" disabled="disabled" /></td>
						<td><input type="text" name="FIELD_PARAM_<?=$i?>" value="" size="10" /></td>
						<td><input type="text" name="C_SORT_<?=$i?>" value="<?=$s?>" size="3" /></td>
						<td id="COLB<?=$i?>">
							<input id="COLOR_<?=$i?>" name="COLOR_<?=$i?>" onchange="col_set(this.value, this)" onclick="ColorPicker(this);" <?
							?>type="text" value="" size="7" />
						</td>
						<td><?=InputType("checkbox", "ACTIVE_".$i, "Y", "Y", false)?></td>
						<td>&nbsp;</td>
					</tr>
					<?
				}
				?>
			</table>
		</td>
	</tr>
	<?
	$tabControl->EndTab();
	$tabControl->Buttons(array("back_url"=>"vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$voteId));
	$tabControl->End();
	?>
</form>
<?$tabControl->ShowWarnings("form1", $message);?>
<style type="text/css">
	table #answerlist td { vertical-align: middle!important; }
</style>

<?=BeginNote();?>
<span class="required"><sup>1</sup></span> -  <?=GetMessage("VOTE_MESSAGE_SPACE")?>
<?=EndNote();?>
<?
?><?=BeginNote();?><a href="javascript:void(0);" onclick="BX.userOptions.save('admin_panel', 'voting_view', 'question_edit', 'new');BX.reload();return false;"><?= Loc::getMessage("VOTE_GO_TO_NEW_PAGE_TITLE") ?></a><?=EndNote();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
