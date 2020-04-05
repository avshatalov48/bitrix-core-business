<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/include.php");

IncludeModuleLangFile(__FILE__);
if (!CModule::IncludeModule("im"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$POST_RIGHT = $APPLICATION->GetGroupRight("im");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$res=false;
if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Convert"]=="Y")
{
	CUtil::JSPostUnescape();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$max_execution_time = intval($max_execution_time);
	if($max_execution_time <= 0)
		$max_execution_time = 10;
	COption::SetOptionString("im", "max_execution_time", $max_execution_time);

	$converted = isset($_REQUEST['converted'])? intval($_REQUEST['converted']): 0;
	$maxMessage = isset($_REQUEST['maxMessage'])? intval($_REQUEST['maxMessage']): 0;
	$maxMessagePerStep = isset($_REQUEST['maxMessagePerStep'])? intval($_REQUEST['maxMessagePerStep']): 100;
	if ($converted == 0 && $maxMessage == 0)
		$maxMessage = CIMConvert::ConvertCount();

	CIMConvert::$convertPerStep = 0;
	CIMConvert::$converted = $converted;

	CIMConvert::DeliveredMessage($maxMessagePerStep, $max_execution_time);

	if(CIMConvert::$convertPerStep > 0):
		$aboutMinute = ($maxMessage-CIMConvert::$converted)/CIMConvert::$convertPerStep*$max_execution_time/60;
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"HTML" => true,
			"MESSAGE" => GetMessage("IM_CONVERT_IN_PROGRESS"),
			"DETAILS" => "#PROGRESS_BAR# ".GetMessage("IM_CONVERT_TOTAL")." <b>".$converted."</b> (".ceil(CIMConvert::$converted/$maxMessage*100)."%, ".GetMessage("IM_CONVERT_ABOUT_TIME")." ".($aboutMinute >= 1? ceil($aboutMinute).' '.GetMessage("IM_CONVERT_ABOUT_TIME_MINUTE") : ceil($aboutMinute*60).' '.GetMessage("IM_CONVERT_ABOUT_TIME_SEC"))." )",
			"PROGRESS_TOTAL" => $maxMessage,
			"PROGRESS_VALUE" => CIMConvert::$converted,
		));
	?>
		<script>
			CloseWaitWindow();
			DoNext(<?=CIMConvert::$converted?>, <?=$maxMessage?>, <?=CIMConvert::$nextConvertPerStep?>);
		</script>

	<?else:
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("IM_CONVERT_COMPLETE"),
			"DETAILS"=>GetMessage("IM_CONVERT_TOTAL")." <b>".$converted."</b><div id='im_convert_finish'></div>",
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		CAdminNotify::DeleteByTag("IM_CONVERT");
	?>
		<script>
			CloseWaitWindow();
			EndConvert();
		</script>
	<?endif;
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{

	$APPLICATION->SetTitle(GetMessage("IM_CONVERT_TITLE"));

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("IM_CONVERT_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("IM_CONVERT_TAB_TITLE")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>
	<script language="JavaScript">
	var savedNS;
	var stop;
	var interval = 0;
	function StartConvert(maxMessage)
	{
		stop=false;
		document.getElementById('convert_result_div').innerHTML='';
		document.getElementById('stop_button').disabled=false;
		document.getElementById('start_button').disabled=true;
		DoNext(0, 0, 100);
	}
	function StopConvert()
	{
		stop=true;
		document.getElementById('stop_button').disabled=true;
		document.getElementById('start_button').disabled=false;
	}
	function EndConvert()
	{
		stop=true;
		document.getElementById('stop_button').disabled=true;
		document.getElementById('start_button').disabled=false;
	}
	function DoNext(converted, maxMessage, maxMessagePerStep)
	{
		var queryString = 'Convert=Y&lang=<?echo htmlspecialcharsbx(LANG)?>';

		interval = document.getElementById('max_execution_time').value;
		queryString += '&<?echo bitrix_sessid_get()?>';
		queryString += '&converted='+parseInt(converted);
		queryString += '&maxMessage='+parseInt(maxMessage);
		queryString += '&maxMessagePerStep='+parseInt(maxMessagePerStep);
		queryString += '&max_execution_time='+interval;

		if(!stop)
		{
			ShowWaitWindow();
			BX.ajax.post(
				'im_convert.php?'+queryString,
				{},
				function(result){
					document.getElementById('convert_result_div').innerHTML = result;
					if(BX('im_convert_finish') != null)
					{
						CloseWaitWindow();
						StopConvert();
					}
				}
			);
		}

		return false;
	}
	</script>

	<?
	$max_messages = CIMConvert::ConvertCount();
	if ($max_messages <= 0)
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("IM_CONVERT_COMPLETE"),
			"DETAILS"=>GetMessage("IM_CONVERT_COMPLETE_ALL_OK"),
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		CAdminNotify::DeleteByTag("IM_CONVERT");
	}
	?>
	<div id="convert_result_div" style="margin:0px">
	</div>


	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="fs1">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();


	$max_execution_time = intval(COption::GetOptionString("im", "max_execution_time", 10));
	if($max_execution_time <= 0)
		$max_execution_time = '';
	?>
		<tr>
			<td width="40%"><?echo GetMessage("IM_CONVERT_STEP")?></td>
			<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $max_execution_time;?>"> <?echo GetMessage("IM_CONVERT_STEP_sec")?></td>
		</tr>
	<?
	$tabControl->Buttons();
	?>
		<input type="button" id="start_button" value="<?echo GetMessage("IM_CONVERT_BUTTON")?>" OnClick="StartConvert(<?=$max_messages?>);" <?=($max_messages>0?"":"disabled")?>>
		<input type="button" id="stop_button" value="<?=GetMessage("IM_CONVERT_STOP")?>" OnClick="StopConvert();" disabled>
	<?
	$tabControl->End();
	?>
	</form>

	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
