<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $USER;

if (!$USER->CanDoOperation('catalog_discount'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
IncludeModuleLangFile(__FILE__);

if ('POST' == $_SERVER['REQUEST_METHOD'] && (isset($_REQUEST["Convert"]) && 'Y' == $_REQUEST["Convert"]) && check_bitrix_sessid())
{
	CUtil::JSPostUnescape();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$max_execution_time = 10;
	if (isset($_REQUEST['max_execution_time']) && 0 < intval($_REQUEST['max_execution_time']))
	{
		$max_execution_time = intval($_REQUEST['max_execution_time']);
	}
	COption::SetOptionString("catalog", "max_execution_time", $max_execution_time);

	$converted = isset($_REQUEST['converted'])? intval($_REQUEST['converted']): 0;
	$maxMessage = isset($_REQUEST['maxMessage'])? intval($_REQUEST['maxMessage']): 0;
	$maxMessagePerStep = isset($_REQUEST['maxMessagePerStep'])? intval($_REQUEST['maxMessagePerStep']): 100;
	if ($converted == 0 && $maxMessage == 0)
		$maxMessage = CCatalogDiscountConvert::GetCountOld();
	$strSessID = isset($_REQUEST['DC']) ? $_REQUEST['DC'] : '';

	CCatalogDiscountConvert::$intConvertPerStep = 0;
	CCatalogDiscountConvert::$intConverted = $converted;
	CCatalogDiscountConvert::$strSessID = $strSessID;

	CCatalogDiscountConvert::ConvertDiscount($maxMessagePerStep, $max_execution_time);

	if (CCatalogDiscountConvert::$intConvertPerStep > 0)
	{
		$aboutMinute = ($maxMessage-CCatalogDiscountConvert::$intConverted)/CCatalogDiscountConvert::$intConvertPerStep*$max_execution_time/60;
		$strAbout = ($aboutMinute >= 1 ? str_replace('#MIN#', ceil($aboutMinute), GetMessage('CAT_DISC_CONVERT_TOTAL_MIN')) : str_replace('#SEC#', ceil($aboutMinute*60), GetMessage('CAT_DISC_CONVERT_TOTAL_SEC')));

		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("CAT_DISC_CONVERT_IN_PROGRESS"),
			"DETAILS" => str_replace(array('#COUNT#', '#PERCENT#', '#TIME#'), array($converted, ceil(CCatalogDiscountConvert::$intConverted/$maxMessage*100), $strAbout), GetMessage('CAT_DISC_CONVERT_TOTAL')),
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		?><script type="text/javascript">
			BX.closeWait();
			DoNext(<?=CCatalogDiscountConvert::$intConverted; ?>, <?=$maxMessage?>, <?=CCatalogDiscountConvert::$intNextConvertPerStep; ?>, '<?=CUtil::JSEscape(CCatalogDiscountConvert::$strSessID); ?>');
		</script><?
	}
	else
	{
		$strDetail = '';
		$arErrors = CCatalogDiscountConvert::GetErrors();
		if (!empty($arErrors) && is_array($arErrors))
		{
			$strDetail = str_replace('#COUNT#', $converted, GetMessage("CAT_DISC_CONVERT_RESULT")).'<br><br>';
			$strDetail .= GetMessage('CAT_DISC_CONVERT_ERR').'<br>';
			$strDetail .= '<ul>';
			foreach ($arErrors as &$arOneError)
			{
				$strDetail .= '<li>'.str_replace(
					array('#LINK#', '#NAME#', '#MESS#'),
					array(
						'/bitrix/admin/cat_discount_edit.php?lang='.LANGUAGE_ID.'&ID='.$arOneError['ID'],
						htmlspecialcharsbx($arOneError['NAME']),
						htmlspecialcharsbx($arOneError['ERROR']),
					),
					GetMessage('CAT_DISC_CONVERT_ONE_ERROR')
				).'</li>';
			}
			if (isset($arOneError))
				unset($arOneError);
			$strDetail .= '</ul>';
			$strDetail .= GetMessage('CAT_DISC_CONVERT_ERROR_RESUME');
			$strDetail .= '<div id="cat_disc_convert_finish"></div>';
		}
		else
		{
			$strDetail = str_replace('#COUNT#', $converted, GetMessage("CAT_DISC_CONVERT_RESULT")).'<div id="cat_disc_convert_finish"></div>';
		}

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("CAT_DISC_CONVERT_COMPLETE"),
			"DETAILS" => $strDetail,
			"HTML" => true,
			"TYPE" => "OK",
		));
		CAdminNotify::DeleteByTag("CATALOG_DISC_CONVERT");
		?><script type="text/javascript">
			BX.closeWait();
			EndConvert();
		</script><?
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{
	$APPLICATION->SetTitle(GetMessage("CAT_DISC_CONVERT_TITLE"));

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CAT_DISC_CONVERT_TAB"), "ICON"=>"catalog", "TITLE"=>GetMessage("CAT_DISC_CONVERT_TAB_TITLE")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><script type="text/javascript">
	var stop;
	var interval = 0;
	function StartConvert(maxMessage)
	{
		stop=false;
		BX('convert_result_div').innerHTML='';
		BX('stop_button').disabled=false;
		BX('start_button').disabled=true;
		DoNext(0, maxMessage, 100, '');
	}
	function StopConvert()
	{
		stop=true;
		BX('stop_button').disabled=true;
		BX('start_button').disabled=false;
	}
	function EndConvert()
	{
		stop=true;
		BX('stop_button').disabled=true;
		BX('start_button').disabled=false;
	}
	function DoNext(converted, maxMessage, maxMessagePerStep, sess)
	{
		var arParams = {
			'Convert': 'Y',
			'lang': '<? echo CUtil::JSEscape(LANGUAGE_ID); ?>',
			'converted': parseInt(converted),
			'maxMessage': parseInt(maxMessage),
			'maxMessagePerStep': parseInt(maxMessagePerStep),
			'max_execution_time': BX('max_execution_time').value,
			'DC': sess,
			'sessid': BX.bitrix_sessid()
		};

		if(!stop)
		{
			BX.showWait();
			BX.ajax.post(
				'cat_discount_convert.php',
				arParams,
				function(result){
					BX('convert_result_div').innerHTML = result;
					if(BX('cat_disc_convert_finish') != null)
					{
						BX.closeWait();
						StopConvert();
					}
				}
			);
		}

		return false;
	}
	</script><?
	$intCountOld = CCatalogDiscountConvert::GetCountOld();
	if ($intCountOld <= 0)
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("CAT_DISC_CONVERT_COMPLETE"),
			"DETAILS"=>GetMessage("ICAT_DISC_CONVERT_COMPLETE_ALL_OK"),
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		CAdminNotify::DeleteByTag("CATALOG_DISC_CONVERT");
	}
	?><div id="convert_result_div" style="margin:0;"></div>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage(); ?>" name="fs1"><?
	$tabControl->Begin();
	$tabControl->BeginNextTab();

	$max_execution_time = intval(COption::GetOptionString("catalog", "max_execution_time", 10));
	if($max_execution_time <= 0)
		$max_execution_time = '';
	?>
		<tr>
			<td width="40%"><?echo GetMessage("CAT_DISC_CONVERT_STEP")?></td>
			<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo htmlspecialcharsbx($max_execution_time);?>"> <?echo GetMessage("CAT_DISC_CONVERT_STEP_SEC")?></td>
		</tr>
	<?
	$tabControl->Buttons();
	?>
		<input type="button" id="start_button" value="<?echo GetMessage("CAT_DISC_CONVERT_BUTTON")?>" <? (0 < $intCountOld ? "" : "disabled"); ?>>
		<input type="button" id="stop_button" value="<?=GetMessage("CAT_DISC_CONVERT_STOP")?>" disabled>
	<?
	$tabControl->End();
	?></form>
	<script type="text/javascript">
	BX.ready(function(){
		var obStartButton = BX('start_button');
		if (!!obStartButton)
		{
			BX.bind(obStartButton, 'click', function(){
				StartConvert(<? echo $intCountOld; ?>);
			});
		}
		var obStopButton = BX('stop_button');
		if (!!obStopButton)
		{
			BX.bind(obStopButton, 'click', StopConvert);
		}
	});
	</script>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>