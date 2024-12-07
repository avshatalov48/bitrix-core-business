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

$res = false;
$bFull = !isset($_REQUEST["Full"]) || $_REQUEST["Full"] != "N";

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Reindex"]=="Y")
{
	@set_time_limit(0);

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if(array_key_exists("NS", $_POST) && is_array($_POST["NS"]))
	{
		$NS = $_POST["NS"];
	}
	else
	{
		$NS = array();
		$max_execution_time = intval($max_execution_time);
		if($max_execution_time <= 0)
			$max_execution_time = '';
		COption::SetOptionString("search", "max_execution_time", $max_execution_time);
		if(!$bFull)
		{
			if(isset($_REQUEST["site_id"]) && $_REQUEST["site_id"] != "")
				$NS["SITE_ID"] = $_REQUEST["site_id"];
			if(isset($_REQUEST["module_id"]) && $_REQUEST["module_id"] != "")
				$NS["MODULE_ID"] = $_REQUEST["module_id"];
		}
	}

	//Check for expired session and set clear flag
	//in order to not accidetialy clear search index
	if(
		$bFull
		&& $NS["CLEAR"] != "Y"
		&& !check_bitrix_sessid()
	)
	{
		$NS["CLEAR"] = "Y";
	}

	$res = CSearch::ReIndexAll($bFull, COption::GetOptionInt("search", "max_execution_time"), $NS, $_REQUEST["clear_suggest"]==="Y");
	if(is_array($res)):
		$jsNS = CUtil::PhpToJSObject(array("NS"=>$res));
		$urlNS = "";
		foreach($res as $key => $value)
			$urlNS .= "&".urlencode("NS[".$key."]")."=".urlencode($value);
		if($bFull)
			$urlNS .= "&Full=Y";

		$path = "";
		if($res["MODULE"] === "main")
		{
			list($site, $path) = explode("|", $res["ID"], 2);
			if($path)
				$path .= "<br>";
		}

		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("SEARCH_REINDEX_IN_PROGRESS"),
			"DETAILS"=>GetMessage("SEARCH_REINDEX_TOTAL")." <b>".$res["CNT"]."</b><br>
				".$path."
				<a id=\"continue_href\" onclick=\"savedNS=".$jsNS."; ContinueReindex(); return false;\" href=\"".htmlspecialcharsbx("search_reindex.php?Continue=Y&lang=".urlencode(LANGUAGE_ID).$urlNS)."\">".GetMessage("SEARCH_REINDEX_NEXT_STEP")."</a>",
			"HTML"=>true,
			"TYPE"=>"PROGRESS",
		));
	?>
		<script>
			CloseWaitWindow();
			DoNext(<?echo $jsNS?>);
		</script>
	<?else:
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("SEARCH_REINDEX_COMPLETE"),
			"DETAILS"=>GetMessage("SEARCH_REINDEX_TOTAL")." <b>".$res."</b>",
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		if(IsModuleInstalled("socialnetwork"))
		{
			CAdminMessage::ShowMessage(array(
				"MESSAGE"=>GetMessage("SEARCH_REINDEX_SOCNET_WARNING"),
				"DETAILS"=>GetMessage("SEARCH_REINDEX_SOCNET_WARN_DETAILS"),
				"HTML"=>true,
				"TYPE"=>"ERROR",
			));
		}
	?>
		<script>
			CloseWaitWindow();
			EndReindex();
			var search_message = BX('search_message');
			if (search_message)
				search_message.style.display = 'none';
		</script>
	<?endif;
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{

$APPLICATION->SetTitle(GetMessage("SEARCH_REINDEX_TITLE"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SEARCH_REINDEX_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("SEARCH_REINDEX_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(is_object($message))
	echo '<div id="search_message">',$message->Show(),'</div>';
?>
<script>
var savedNS;
var stop;
var interval = 0;
function StartReindex()
{
	stop=false;
	document.getElementById('reindex_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	DoNext();
}
function DoNext(NS)
{
	var queryString = 'Reindex=Y'
		+ '&lang=<?echo htmlspecialcharsbx(LANG)?>';

	if(!NS)
	{
		interval = document.getElementById('max_execution_time').value;
		queryString += '&<?echo bitrix_sessid_get()?>'
		queryString += '&max_execution_time='+interval;
	}

	if(document.getElementById('Full').checked)
	{
		queryString += '&Full=N';

		if(!NS)
		{
			site_id = document.getElementById('LID').value;
			if(site_id != 'NOT_REF')
				queryString += '&site_id=' + site_id;

			module_id = document.getElementById('MODULE_ID').value;
			if(module_id != 'NOT_REF')
				queryString += '&module_id='+module_id;

			if(document.getElementById('clear_suggest').checked)
				queryString += '&clear_suggest=Y';
		}
	}
	else
	{
		queryString+='&Full=Y';
	}

	savedNS = NS;

	if(!stop)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'search_reindex.php?'+queryString,
			NS,
			function(result){
				document.getElementById('reindex_result_div').innerHTML = result;
				var href = document.getElementById('continue_href');
				if(!href)
				{
					CloseWaitWindow();
					StopReindex();
				}
			}
		);
	}

	return false;
}
function StopReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=false;
}
function ContinueReindex()
{
	stop=false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	DoNext(savedNS);
}
function EndReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=true;
}
</script>

<div id="reindex_result_div" style="margin:0px">
</div>

<script>
function Full_OnClick(full_checked)
{
	document.getElementById('MODULE_ID').disabled = !full_checked;
	document.getElementById('LID').disabled = !full_checked;
	document.getElementById('clear_suggest').disabled = !full_checked;
}

</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="fs1">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("SEARCH_REINDEX_REINDEX_CHANGED")?></td>
		<td width="60%"><input type="checkbox" name="Full" id="Full" value="N" checked OnClick="Full_OnClick(this.checked)"></td>
	</tr>
<?
$max_execution_time = intval(COption::GetOptionString("search", "max_execution_time"));
if($max_execution_time <= 0)
	$max_execution_time = '';
?>
	<tr>
		<td><?echo GetMessage("SEARCH_REINDEX_STEP")?></td>
		<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $max_execution_time;?>"> <?echo GetMessage("SEARCH_REINDEX_STEP_sec")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("SEARCH_REINDEX_SITE")?></td>
		<td><?echo CLang::SelectBox("LID", $str_LID, GetMessage("SEARCH_REINDEX_ALL"), "", "id=\"LID\"");?></td>
	</tr>
	<tr>
		<td><?=GetMessage("SEARCH_REINDEX_MODULE")?></td>
		<td>
		<select name="MODULE_ID" id="MODULE_ID">
		<option value="NOT_REF"><?=GetMessage("SEARCH_REINDEX_ALL")?></option>
		<option value="main"><?=GetMessage("SEARCH_REINDEX_MAIN")?></option>
		<?foreach(CSearchParameters::GetModulesList() as $module_id => $module_name):?>
			<option value="<?echo $module_id?>"><?echo htmlspecialcharsbx($module_name)?></option>
		<?endforeach;?>
		</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SEARCH_REINDEX_CLEAR_SUGGEST")?></td>
		<td width="60%"><input type="checkbox" name="clear_suggest" id="clear_suggest" value="Y"></td>
	</tr>

<?
$tabControl->Buttons();
?>
	<input type="button" id="start_button" value="<?echo GetMessage("SEARCH_REINDEX_REINDEX_BUTTON")?>" OnClick="StartReindex();" class="adm-btn-save">
	<input type="button" id="stop_button" value="<?=GetMessage("SEARCH_REINDEX_STOP")?>" OnClick="StopReindex();" disabled>
	<input type="button" id="continue_button" value="<?=GetMessage("SEARCH_REINDEX_CONTINUE")?>" OnClick="ContinueReindex();" disabled>
<?
$tabControl->End();
?>
</form>
<?if($Continue=="Y"):?>
<script>
	savedNS = <?echo CUtil::PhpToJSObject(array("NS"=>$_GET["NS"]));?>;
	<?if($_GET["Full"]=="Y"):?>
		document.getElementById('Full').checked = false;
		Full_OnClick(false);
	<?endif;?>
	ContinueReindex();
</script>
<?endif?>

<?
	if(IsModuleInstalled("socialnetwork"))
		echo BeginNote(),GetMessage("SEARCH_REINDEX_SOCNET_MESSAGE"),EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
