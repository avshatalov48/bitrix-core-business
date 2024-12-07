<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/prolog.php");
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
$searchDB = CDatabase::GetModuleConnection('search');

$POST_RIGHT = $APPLICATION->GetGroupRight("search");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if($get_select=="Y"):
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
if(intval($FIELD_ID)>0)
	$FIELD_NAME = "FIELDS[".intval($FIELD_ID)."][PARAM1]";
else
	$FIELD_NAME = htmlspecialcharsbx($FIELD_ID);
$arOptions = array();
$strAttributes="";
if($PARAM=="1" && $MODULE_ID=="iblock" && CModule::IncludeModule("iblock"))
{
	$rs = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while($ar=$rs->Fetch())
		if($arIBType=CIBlockType::GetByIDLang($ar["ID"], $lang))
			$arOptions[$ar["ID"]]="[".$ar["ID"]."] ".$arIBType["~NAME"];
	$strControl = "select";
	if(intval($ID)>0)
		$strAttributes = " OnChange=\"BoxUpdateNew('param1', ".intval($ID).")\" ";
	else
		$strAttributes = " OnChange=\"BoxUpdateNew('param1')\" ";
}
elseif($PARAM=="1" && $MODULE_ID=="forum" && CModule::IncludeModule("forum"))
{
	$rs = CForumNew::GetList(array("sort"=>"asc"), array("LID"=>$SITE_ID));
	while($ar=$rs->Fetch())
		$arOptions[$ar["ID"]]="[".$ar["ID"]."] ".$ar["NAME"];
	$strControl = "select";
}
elseif($PARAM=="2" && $PARAM1!="" && $MODULE_ID=="iblock" && CModule::IncludeModule("iblock"))
{
	$rs = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>$PARAM1,"LID"=>$SITE_ID));
	while($ar=$rs->Fetch())
		$arOptions[$ar["ID"]]="[".$ar["ID"]."] ".$ar["NAME"];
	$strControl = "select";
}
elseif($PARAM=="2" && $MODULE_ID=="forum" && CModule::IncludeModule("forum"))
{
	$strControl = "input";
}
else
{
	$strControl = "hidden";
}

switch($strControl)
{
	case "select":
		?>
		<select name="<?=$FIELD_NAME?>" id="<?=$FIELD_NAME?>" <?=$strAttributes?>>
		<option value=""><?=GetMessage("customrank_edit_no")?></option>
		<?foreach($arOptions as $key=>$value):?>
			<option value="<?=htmlspecialcharsbx($key)?>"<?=$key==$PARAM1?" selected":""?>><?=htmlspecialcharsbx($value)?></option>
		<?endforeach;?>
		</select>
		<?
		break;
	case "input":
		?><input type="text" size="15" name="<?=$FIELD_NAME?>" id="<?=$FIELD_NAME?>" value="<?=htmlspecialcharsbx($PARAM2)?>"><?
		break;
	case "hidden":
		?>&nbsp;<input type="hidden" name="<?=$FIELD_NAME?>" id="<?=$FIELD_NAME?>" value=""><?
		break;
}
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
die();
endif;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("customrank_edit_rule"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("customrank_edit_rule_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$ID = intval($ID);		// Id of the edited record
switch($MODULE_ID)
{
	case "iblock":
		$PARAM1 = $PARAM1;
		$PARAM2 = $PARAM2;
		$ITEM_ID = $ITEM_ID["KEY"];
		break;
	case "forum":
		$PARAM1 = $PARAM1;
		$PARAM2 = $PARAM2;
		$ITEM_ID = $ITEM_ID["FORUM"];
		break;
	case "main":
		$PARAM1 = "";
		$PARAM2 = "";
		$ITEM_ID = $ITEM_ID["MAIN"];
		break;
	default:
		$PARAM1 = "";
		$PARAM2 = "";
		$ITEM_ID = $ITEM_ID["MAIN"];
		break;
}
$strError = "";
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$cCustomRank = new CSearchCustomRank;
	$arFields = Array(
		"SITE_ID"	=> $SITE_ID,
		"MODULE_ID"	=> $MODULE_ID,
		"PARAM1"	=> $PARAM1,
		"PARAM2"	=> $PARAM2,
		"ITEM_ID"	=> ($MODULE_ID=="main" && $ITEM_ID!=""?$SITE_ID."|".$ITEM_ID:$ITEM_ID),
		"RANK"		=> $RANK
	);

	if($ID>0)
	{
		$res = $cCustomRank->Update($ID, $arFields);
	}
	else
	{
		$ID = $cCustomRank->Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("search_customrank_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("search_customrank_admin.php?lang=".LANG."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$strError = $cCustomRank->LAST_ERROR;
		$bVarsFromForm = true;
	}
}

//Edit/Add part
ClearVars();
$str_MODULE_ID = "main";
$str_RANK = 0;

if($ID>0)
{
	$customrank = CSearchCustomRank::GetByID($ID);
	if(!$customrank->ExtractFields("str_"))
		$ID=0;
	elseif($str_MODULE_ID=="main")
	{
		list($site, $url) = explode("|", $str_ITEM_ID, 2);
		$str_ITEM_ID = $url;
	}
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_search_custom_rank", "", "str_");

$APPLICATION->SetTitle(($ID>0?GetMessage("customrank_edit_edit_rule").$ID :GetMessage("customrank_edit_add_rule")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(is_object($message))
	echo $message->Show();

$aMenu = array(
	array(
		"TEXT"=>GetMessage("customrank_edit_list_rule"),
		"TITLE"=>GetMessage("customrank_edit_list_rule_title"),
		"LINK"=>"search_customrank_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("customrank_edit_add_new"),
		"TITLE"=>GetMessage("customrank_edit_add_new_title"),
		"LINK"=>"search_customrank_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("customrank_edit_delete"),
		"TITLE"=>GetMessage("customrank_edit_delete_title"),
		"LINK"=>"javascript:if(confirm('".AddSlashes(GetMessage("customrank_edit_delete_conf"))."'))window.location='search_customrank_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>

<script>
function ParamUpdate()
{
	var module_id = document.getElementById('MODULE_ID').value;
	document.getElementById('main_item_id').style.display="none";
	document.getElementById('iblock_item_id').style.display="none";
	document.getElementById('forum_item_id').style.display="none";
	switch(module_id)
	{
		case 'main':
			document.getElementById('param1_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param1"))?>';
			document.getElementById('param2_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param2"))?>';
			document.getElementById('item_id_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param3_main"))?>';
			document.getElementById('main_item_id').style.display="block";
			break;
		case 'iblock':
			document.getElementById('param1_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param1_iblock"))?>';
			document.getElementById('param2_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param2_iblock"))?>';
			document.getElementById('item_id_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param3_iblock"))?>';
			document.getElementById('iblock_item_id').style.display="block";
			break;
		case 'forum':
			document.getElementById('param1_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param1_forum"))?>';
			document.getElementById('param2_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param2_forum"))?>';
			document.getElementById('item_id_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param3_forum"))?>';
			document.getElementById('forum_item_id').style.display="block";
			break;
		default:
			document.getElementById('param1_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param1"))?>';
			document.getElementById('param2_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param2"))?>';
			document.getElementById('item_id_label').innerHTML='<?=AddSlashes(GetMessage("customrank_edit_param3"))?>';
			document.getElementById('main_item_id').style.display="block";
			break;
	}
}
var processing = false;
function GetAdminDiv(id, url, is_first, is_last)
{
	processing = true;
	CHttpRequest.Action = function(result)
	{
		if(is_last)
			CloseWaitWindow();
		document.getElementById(id).innerHTML = result;
		processing = false;
	}
	if(is_first)
		ShowWaitWindow();
	CHttpRequest.Send(url);
}
function BoxUpdateParam1()
{
	GetAdminDiv('param1_result_div'
		,'search_customrank_edit.php?get_select=Y'
		+'&'+'PARAM=1'
		+'&'+'FIELD_ID=PARAM1'
		+'&'+'SITE_ID='+encodeURIComponent(document.getElementById('SITE_ID').value)
		+'&'+'MODULE_ID='+encodeURIComponent(document.getElementById('MODULE_ID').value)
		+'&'+'PARAM1='+encodeURIComponent(document.getElementById('PARAM1').value)
	,true, false);
	ParamUpdate();
}
function BoxUpdateParam2()
{
	GetAdminDiv('param2_result_div'
		,'search_customrank_edit.php?get_select=Y'
		+'&'+'PARAM=2'
		+'&'+'FIELD_ID=PARAM2'
		+'&'+'SITE_ID='+encodeURIComponent(document.getElementById('SITE_ID').value)
		+'&'+'MODULE_ID='+encodeURIComponent(document.getElementById('MODULE_ID').value)
		+'&'+'PARAM1='+encodeURIComponent(document.getElementById('PARAM1').value)
		+'&'+'PARAM2='+encodeURIComponent(document.getElementById('PARAM2').value)
	,false, true);
	ParamUpdate();
}
function BoxUpdateNew(step)
{
	if(step=='param1')
	{
		BoxUpdateParam1();
		setTimeout("BoxUpdateNew('param2')", 500);
	}
	if(step=='param2')
	{
		if(processing)
			setTimeout("BoxUpdateNew('param2')", 500);
		else
			BoxUpdateParam2();
	}
}
</script>

<IFRAME name=hiddenframeX1 src="" width=0 height=0 style="width:0px; height:0px; border:0px;"></IFRAME>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form" id="tbl_search">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?=GetMessage("customrank_edit_site")?></td>
		<td width="60%"><?echo CLang::SelectBox("SITE_ID", $str_SITE_ID, "", "BoxUpdateNew('param1')", " id=\"SITE_ID\"");?></td>
	</tr>
	<tr>
		<td><?=GetMessage("customrank_edit_module")?></td>
		<td>
		<select name="MODULE_ID" id="MODULE_ID" OnChange="BoxUpdateNew('param1')">
		<option value="main"<?=$str_MODULE_ID=="main"?" selected":""?>><?=GetMessage("customrank_edit_files")?></option>
		<?foreach(CSearchParameters::GetModulesList() as $module_id => $module_name):?>
			<option value="<?echo $module_id?>"<?=$str_MODULE_ID==$module_id?" selected":""?>><?echo htmlspecialcharsbx($module_name)?></option>
		<?endforeach;?>
		</select>
		</td>
	</tr>
	<tr>
		<td><span id="param1_label"><?=GetMessage("customrank_edit_param1")?></span></td>
		<td>
		<div id="param1_result_div">
		<?if($str_MODULE_ID=="iblock" && CModule::IncludeModule("iblock")):?>
			<select name="PARAM1" id="PARAM1" OnChange="BoxUpdateNew('param1')">
			<option value=""><?=GetMessage("customrank_edit_no")?></option>
			<?
			$rsType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
			while($arr=$rsType->GetNext())
			{
				if($ar=CIBlockType::GetByIDLang($arr["ID"], $lang)):?>
				<option value="<?echo $arr["ID"]?>"<?=$arr["ID"]==$str_PARAM1?" selected":""?>><?echo "[".$arr["ID"]."] ".$ar["NAME"]?></option>
				<?endif;
			}
		?>
			</select>
		<?elseif($str_MODULE_ID=="forum" && CModule::IncludeModule("forum")):?>
			<select name="PARAM1" id="PARAM1" OnChange="BoxUpdateNew('param1')">
			<option value=""><?=GetMessage("customrank_edit_no")?></option>
		<?
			$rsForum = CForumNew::GetList(array("sort"=>"asc"), array("LID"=>$str_SITE_ID));
			while($arForum=$rsForum->Fetch())
			{
				$arForum["ID"]=intval($arForum["ID"]);
			?>
				<option value="<?=$arForum["ID"]?>"<?=$arForum["ID"]==$str_PARAM1?" selected":""?>><?="[".$arForum["ID"]."] ".htmlspecialcharsbx($arForum["NAME"])?></option>
			<?}
		?>
			</select>
		<?else:?>
			&nbsp;<input type="hidden" name="PARAM1" id="PARAM1" value="">
		<?endif;?>
		</div>
		</td>
	</tr>
	<tr>
		<td><span id="param2_label"><?=GetMessage("customrank_edit_param2")?></span></font></td>
		<td>
		<div id="param2_result_div">
		<?if($str_MODULE_ID=="iblock" && $str_PARAM1<>"" && CModule::IncludeModule("iblock")):?>
			<select name="PARAM2" id="PARAM2" OnChange="BoxUpdateNew('param1')">
			<option value=""><?=GetMessage("customrank_edit_no")?></option>
			<?
			$rsIBlock = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>$str_PARAM1,"LID"=>$str_SITE_ID));
			while($arIBlock=$rsIBlock->Fetch())
			{
				$arIBlock["ID"]=intval($arIBlock["ID"]);
			?>
				<option value="<?=$arIBlock["ID"]?>"<?=$arIBlock["ID"]==$str_PARAM2?" selected":""?>><?="[".$arIBlock["ID"]."] ".htmlspecialcharsbx($arIBlock["NAME"])?></option>
			<?}
			?>
			</select>
		<?elseif($str_MODULE_ID=="forum" && CModule::IncludeModule("forum")):?>
			<input name="PARAM2" id="PARAM2" value="<?=$str_PARAM2?>" size="15" type="text">
		<?else:?>
			&nbsp;<input type="hidden" name="PARAM2" id="PARAM2" value="">
		<?endif;?>
		</select>
		</div>
		</td>
	</tr>
	<tr>
		<td><span id="item_id_label"><?=GetMessage("customrank_edit_param3")?></span></td>
		<td>
		<div id="iblock_item_id" style="display:<?=$str_MODULE_ID=="iblock"?"block":"none"?>">
		<input name="ITEM_ID[KEY]" id="ITEM_ID[KEY]" value="<?=$str_ITEM_ID?>" size="10" type="text">
		<input type="button" value="..." id="ITEM_ID_CHOOSE" onClick="jsUtils.OpenWindow('iblock_element_search.php?lang=<?=LANG?>&amp;IBLOCK_ID='+document.getElementById('PARAM2').value+'&amp;n=ITEM_ID&amp;k=KEY', 600, 500);">
		&nbsp;<span id="sp_<?=md5("ITEM_ID")?>_<?="KEY"?>" class="tablebodytext">
		<?
		if($str_MODULE_ID=="iblock" && $str_PARAM1!="" && $str_PARAM2!="" && $str_ITEM_ID!="")
			echo CSearchCustomRank::__GetParam($lang, $str_SITE_ID, $str_MODULE_ID, $str_PARAM1, $str_PARAM2, $str_ITEM_ID);
		?>
		</span>
		</div>
		<div id="main_item_id" style="display:<?=$str_MODULE_ID=="main"?"block":"none"?>">
		<input name="ITEM_ID[MAIN]" id="ITEM_ID[MAIN]" value="<?=$str_ITEM_ID?>" size="40" type="text">
		</div>
		<div id="forum_item_id" style="display:<?=$str_MODULE_ID=="forum"?"block":"none"?>">
		<input name="ITEM_ID[FORUM]" id="ITEM_ID[FORUM]" value="<?=$str_ITEM_ID?>" size="15" type="text">
		</div>
		</td>

	</tr>
	<tr>
		<td><?=GetMessage("customrank_edit_sort")?></td>
		<td>
		<input name="RANK" id="RANK" value="<?=$str_RANK?>" size="10" type="text">
		</td>

	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"search_customrank_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
$tabControl->End();
?>
</form>

<script>
	ParamUpdate();
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>