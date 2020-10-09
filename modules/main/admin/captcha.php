<?
if(array_key_exists("Preview", $_REQUEST) && $_REQUEST["Preview"] <> '')
{
	define("NO_KEEP_STATISTIC", "Y");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$arSettings = array(
	"transparentTextPercent" => array(
		"int",
		5,
		10,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM1"),
	),
	"arBGColor_1" => array(
		"string",
		6,
		"FFFFFF",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM2"),
	),
	"arBGColor_2" => array(
		"string",
		6,
		"FFFFFF",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM3"),
	),
	"numEllipses" => array(
		"int",
		5,
		100,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM4"),
	),
	"arEllipseColor_1" => array(
		"string",
		6,
		"7F7F7F",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM5"),
	),
	"arEllipseColor_2" => array(
		"string",
		6,
		"FFFFFF",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM6"),
	),
	"bLinesOverText" => array(
		"checkbox",
		"Y",
		"N",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM7"),
	),
	"numLines" => array(
		"int",
		5,
		20,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM8"),
	),
	"arLineColor_1" => array(
		"string",
		6,
		"6E6E6E",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM9"),
	),
	"arLineColor_2" => array(
		"string",
		6,
		"FAFAFA",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM10"),
	),
	"textStartX" => array(
		"int",
		5,
		7,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM11"),
	),
	"textFontSize" => array(
		"int",
		5,
		20,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM12"),
	),
	"arTextColor_1" => array(
		"string",
		6,
		"000000",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM13"),
	),
	"arTextColor_2" => array(
		"string",
		6,
		"646464",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM14"),
	),
	"textAngel_1" => array(
		"int",
		5,
		-20,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM15"),
	),
	"textAngel_2" => array(
		"int",
		5,
		20,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM16"),
	),
	"textDistance_1" => array(
		"int",
		5,
		27,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM17"),
	),
	"textDistance_2" => array(
		"int",
		5,
		32,
		GetMessage("MAIN_ADM_CAPTCHA_PARAM18"),
	),
	"bWaveTransformation" => array(
		"checkbox",
		"Y",
		"N",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM19"),
	),
	"bEmptyText" => array(
		"checkbox",
		"Y",
		"N",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM23"),
	),
	"arBorderColor" => array(
		"string",
		6,
		"000000",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM20"),
	),
	"arTTFFiles" => array(
		"list",
		array(
//			"font.ttf" => "font.ttf",
//			"bitrix_captcha.ttf" => "bitrix_captcha.ttf",
		),
		array("font.ttf"),
		GetMessage("MAIN_ADM_CAPTCHA_PARAM21"),
	),
	"letters" => array(
		"string",
		35,
		"ABCDEFGHJKLMNPQRSTWXYZ23456789",
		GetMessage("MAIN_ADM_CAPTCHA_PARAM22"),
	),
);

$cpt = new CCaptcha;
$dh = opendir($_SERVER["DOCUMENT_ROOT"].$cpt->GetTTFFontsPath());
if($dh)
{
	while(($file = readdir($dh)) !== false)
	{
		if(mb_substr(mb_strtolower($file), -4) === ".ttf")
		{
			$arSettings["arTTFFiles"][1][$file] = $file;
		}
	}
	closedir($dh);
}

$aTabs = array(
	array("DIV" => "fedit1", "TAB" => GetMessage("MAIN_ADM_CAPTCHA_TAB"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_ADM_CAPTCHA_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && ($save <> '' || $apply <> '') && check_bitrix_sessid() && $isAdmin)
{
	foreach($arSettings as $key => $value)
	{
		if($key === "letters")
		{
			$strChars = mb_strtoupper($_POST[$key]);
			$arChars = array();
			for($i = 0, $c = mb_strlen($strChars);$i < $c;$i++)
			{
				$ch = mb_substr($strChars, $i, 1);
				$arChars[$ch] = $ch;
			}
			COption::SetOptionString("main", "CAPTCHA_".$key, implode("", $arChars));
		}
		elseif($value[0] === "int")
			COption::SetOptionInt("main", "CAPTCHA_".$key, intval($_POST[$key]));
		elseif($value[0] === "string")
			COption::SetOptionString("main", "CAPTCHA_".$key, $_POST[$key]);
		elseif($value[0] === "checkbox")
			COption::SetOptionString("main", "CAPTCHA_".$key, $_POST[$key]==="Y"? "Y": "N");
		elseif($value[0] === "list")
		{
			$ar = array();
			if(is_array($_POST[$key]))
			{
				foreach($_POST[$key] as $val)
					if(array_key_exists($val, $value[1]))
						$ar[] = $val;
			}
			COption::SetOptionString("main", "CAPTCHA_".$key, implode(",", $ar));
		}
	}
	COption::SetOptionInt("main", "CAPTCHA_presets", intval($_POST["presets"]));

	LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
}

if($Preview <> '')
{
	$cpt = new CCaptcha();

	$result = array();
	foreach($arSettings as $key => $value)
	{
		if($value[0] === "int")
		{
			if(array_key_exists($key, $_GET))
				$result[$key] = intval($_GET[$key]);
			else
				$result[$key] = COption::GetOptionInt("main", "CAPTCHA_".$key, $value[2]);
		}
		elseif($value[0] === "string")
		{
			if(array_key_exists($key, $_GET))
				$result[$key] = $_GET[$key];
			else
				$result[$key] = COption::GetOptionString("main", "CAPTCHA_".$key, $value[2]);
		}
		elseif($value[0] === "checkbox")
		{
			if(array_key_exists($key, $_GET))
				$result[$key] = $_GET[$key] === "Y"? "Y": "N";
			else
				$result[$key] = COption::GetOptionString("main", "CAPTCHA_".$key, $value[2]);
		}
		elseif($value[0] === "list")
		{
			$ar = array();
			if(array_key_exists($key, $_GET))
			{
				$_GET[$key] = explode(",", $_GET[$key]);
				foreach($_GET[$key] as $val)
					if(array_key_exists($val, $value[1]))
						$ar[] = $val;
			}
			else
			{
				$ar = explode(",", COption::GetOptionString("main", "CAPTCHA_".$key, implode(",", $value[2])));
			}
			$result[$key] = $ar;
		}
	}

	$cpt->SetTextTransparent(true, $result["transparentTextPercent"]);
	$cpt->SetBGColorRGB($result["arBGColor_1"], $result["arBGColor_2"]);
	$cpt->SetEllipsesNumber($result["numEllipses"]);
	$cpt->SetEllipseColorRGB($result["arEllipseColor_1"], $result["arEllipseColor_2"]);
	$cpt->SetLinesOverText($result["bLinesOverText"] === "Y");
	$cpt->SetLinesNumber($result["numLines"]);
	$cpt->SetLineColorRGB($result["arLineColor_1"], $result["arLineColor_2"]);
	$cpt->SetTextWriting($result["textAngel_1"], $result["textAngel_2"], $result["textStartX"], $result["textDistance_1"], $result["textDistance_2"], $result["textFontSize"]);
	$cpt->SetTextColorRGB($result["arTextColor_1"], $result["arTextColor_2"]);
	$cpt->SetWaveTransformation($result["bWaveTransformation"] === "Y");
	$cpt->SetEmptyText($result["bEmptyText"] === "Y");
	$cpt->SetBorderColorRGB($result["arBorderColor"]);
	$cpt->SetTTFFonts($result["arTTFFiles"]);

	$arChars = array();
	$l = mb_strlen($result["letters"]);
	for($i = 0; $i < $l; $i++)
		$arChars[] = mb_substr($result["letters"], $i, 1);
	$cpt->SetCodeChars($arChars);

	$cpt->SetCode();

	if ($cpt->InitCode($cpt->GetSID()))
		$cpt->Output();
	else
		$cpt->OutputError();

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
}

$APPLICATION->SetTitle(GetMessage("MAIN_ADM_CAPTCHA_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$CAPTCHA_CODE = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

$tabControl->Begin();
?>
<script>
<?foreach($arSettings as $key => $value):?>
	<?if($value[0] === "list"):?>
		var <?echo $key?> = '<?echo CUtil::JSEscape(COption::GetOptionString("main", "CAPTCHA_".$key, implode(",", $value[2])))?>';
	<?else:?>
		var <?echo $key?> = '<?echo CUtil::JSEscape(COption::GetOptionString("main", "CAPTCHA_".$key, $value[2]))?>';
	<?endif?>
<?endforeach?>
var j = 0;
var preset_selected = false;
function CheckForChanges()
{
	var changed = false;
	var url = '/bitrix/admin/captcha.php?Preview=Y&captcha_sid=<?echo $CAPTCHA_CODE?>';
	var ctl, b;

	<?foreach($arSettings as $key => $value):?>
		ctl = document.getElementById('<?echo $key?>');
		<?if($value[0] === "checkbox"):?>
			if(ctl.checked)
				b = 'Y';
			else
				b = 'N';
		<?elseif($value[0] == "list"):?>
			b = '';
			for(var i = 0; i < ctl.length; i++)
			{
				if(ctl[i].selected)
				{
					if(b.length)
						b += ',' + ctl[i].value;
					else
						b += ctl[i].value;
				}
			}
		<?else:?>
			b = ctl.value;
		<?endif?>
		if(b != <?echo $key?>)
			changed = true;
		<?echo $key?> = b;

		url += '&<?echo $key?>='+<?echo $key?>;
	<?endforeach?>

	if(changed)
	{
		j++;
		for(var i = 0;i < 10; i++)
		{
			var img = document.getElementById('CAPTCHA_' + i);
			img.src = url + '&i=' + i + '&j=' + j;
		}
		if(!preset_selected)
			document.getElementById('presets').value = '0';
		else
			preset_selected = false;
	}
	setTimeout('CheckForChanges()', 2000);
}
setTimeout('CheckForChanges()', 3000);
function set_presets()
{
	preset_selected = true;
	if(document.getElementById('presets').value == '1')
	{
		document.getElementById('transparentTextPercent').value = '10';
		document.getElementById('arBGColor_1').value = 'FFFFFF';
		document.getElementById('arBGColor_2').value = 'FFFFFF';
		document.getElementById('numEllipses').value = '100';
		document.getElementById('arEllipseColor_1').value = '7F7F7F';
		document.getElementById('arEllipseColor_2').value = 'FFFFFF';
		document.getElementById('bLinesOverText').checked = false;
		document.getElementById('numLines').value = '20';
		document.getElementById('arLineColor_1').value = '6E6E6E';
		document.getElementById('arLineColor_2').value = 'FAFAFA';
		document.getElementById('textStartX').value = '7';
		document.getElementById('textFontSize').value = '20';
		document.getElementById('arTextColor_1').value = '000000';
		document.getElementById('arTextColor_2').value = '646464';
		document.getElementById('textAngel_1').value = '-20';
		document.getElementById('textAngel_2').value = '20';
		document.getElementById('textDistance_1').value = '27';
		document.getElementById('textDistance_2').value = '32';
		document.getElementById('bWaveTransformation').checked = false;
		document.getElementById('bEmptyText').checked = false;
		document.getElementById('arBorderColor').value = '000000';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'font.ttf');
		}
	}
	if(document.getElementById('presets').value == '2')
	{
		document.getElementById('transparentTextPercent').value = '0';
		document.getElementById('arBGColor_1').value = 'FFFFFF';
		document.getElementById('arBGColor_2').value = 'FFFFFF';
		document.getElementById('numEllipses').value = '0';
		document.getElementById('numLines').value = '0';
		document.getElementById('textStartX').value = '40';
		document.getElementById('textFontSize').value = '26';
		document.getElementById('arTextColor_1').value = '000000';
		document.getElementById('arTextColor_2').value = '000000';
		document.getElementById('textAngel_1').value = '-15';
		document.getElementById('textAngel_2').value = '15';
		document.getElementById('textDistance_1').value = '-2';
		document.getElementById('textDistance_2').value = '-2';
		document.getElementById('bWaveTransformation').checked = true;
		document.getElementById('bEmptyText').checked = false;
		document.getElementById('arBorderColor').value = '000000';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'bitrix_captcha.ttf');
		}
	}
	if(document.getElementById('presets').value == '3')
	{
		document.getElementById('transparentTextPercent').value = '0';
		document.getElementById('arBGColor_1').value = 'FFFFFF';
		document.getElementById('arBGColor_2').value = 'FFFFFF';
		document.getElementById('numEllipses').value = '0';
		document.getElementById('bLinesOverText').checked = true;
		document.getElementById('numLines').value = '6';
		document.getElementById('arLineColor_1').value = 'FFFFFF';
		document.getElementById('arLineColor_2').value = 'FFFFFF';
		document.getElementById('textStartX').value = '40';
		document.getElementById('textFontSize').value = '26';
		document.getElementById('arTextColor_1').value = '000000';
		document.getElementById('arTextColor_2').value = '000000';
		document.getElementById('textAngel_1').value = '-15';
		document.getElementById('textAngel_2').value = '15';
		document.getElementById('textDistance_1').value = '-2';
		document.getElementById('textDistance_2').value = '-2';
		document.getElementById('bWaveTransformation').checked = false;
		document.getElementById('bEmptyText').checked = false;
		document.getElementById('arBorderColor').value = '000000';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'bitrix_captcha.ttf');
		}
	}
	if(document.getElementById('presets').value == '4')
	{
		document.getElementById('transparentTextPercent').value = '0';
		document.getElementById('arBGColor_1').value = '000000';
		document.getElementById('arBGColor_2').value = '000000';
		document.getElementById('numEllipses').value = '0';
		document.getElementById('bLinesOverText').checked = true;
		document.getElementById('numLines').value = '0';
		document.getElementById('arLineColor_1').value = 'FFFFFF';
		document.getElementById('arLineColor_2').value = 'FFFFFF';
		document.getElementById('textStartX').value = '40';
		document.getElementById('arTextColor_1').value = 'FFFFFF';
		document.getElementById('arTextColor_2').value = 'FFFFFF';
		document.getElementById('textAngel_1').value = '-15';
		document.getElementById('textAngel_2').value = '15';
		document.getElementById('textDistance_1').value = '-3';
		document.getElementById('textDistance_2').value = '-2';
		document.getElementById('bWaveTransformation').checked = false;
		document.getElementById('bEmptyText').checked = false;
		document.getElementById('arBorderColor').value = 'FFFFFF';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'font.ttf');
		}
	}
	if(document.getElementById('presets').value == '5')
	{
		document.getElementById('transparentTextPercent').value = '50';
		document.getElementById('arBGColor_1').value = '00C000';
		document.getElementById('arBGColor_2').value = '00C000';
		document.getElementById('numEllipses').value = '0';
		document.getElementById('numLines').value = '0';
		document.getElementById('textStartX').value = '7';
		document.getElementById('arTextColor_1').value = '001000';
		document.getElementById('arTextColor_2').value = '003000';
		document.getElementById('textDistance_1').value = '22';
		document.getElementById('textDistance_2').value = '24';
		document.getElementById('bWaveTransformation').checked = false;
		document.getElementById('bEmptyText').checked = false;
		document.getElementById('arBorderColor').value = 'FFFFFF';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'font.ttf');
		}
	}
	if(document.getElementById('presets').value == '6')
	{
		document.getElementById('transparentTextPercent').value = '0';
		document.getElementById('arBGColor_1').value = 'FFFFFF';
		document.getElementById('arBGColor_2').value = 'FFFFFF';
		document.getElementById('numEllipses').value = '0';
		document.getElementById('numLines').value = '0';
		document.getElementById('textStartX').value = '30';
		document.getElementById('textFontSize').value = '40';
		document.getElementById('arTextColor_1').value = '000000';
		document.getElementById('arTextColor_2').value = '000000';
		document.getElementById('textAngel_1').value = '0';
		document.getElementById('textAngel_2').value = '0';
		document.getElementById('textDistance_1').value = '-3';
		document.getElementById('textDistance_2').value = '-4';
		document.getElementById('bWaveTransformation').checked = false;
		document.getElementById('bEmptyText').checked = true;
		document.getElementById('arBorderColor').value = '000000';
		var ctl = document.getElementById('arTTFFiles');
		for(var i = 0; i < ctl.length; i++)
		{
			ctl[i].selected = (ctl[i].value == 'bitrix_captcha.ttf');
		}
	}
}
</script>
<form name="captcha_form" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>">
<?=bitrix_sessid_post()?>
<?$tabControl->BeginNextTab();?>
<tr>
	<td width="40%"><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESETS")?>:</td>
	<td width="300">
	<select id="presets" name="presets" onchange="set_presets()">
		<option value="0" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 0) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_0")?></option>
		<option value="1" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 1) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_1")?></option>
		<option value="2" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 2) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_2")?></option>
		<option value="3" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 3) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_3")?></option>
		<option value="4" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 4) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_4")?></option>
		<option value="5" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 5) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_5")?></option>
		<option value="6" <?if(COption::GetOptionInt("main", "CAPTCHA_presets") == 6) echo "selected"?>><?echo GetMessage("MAIN_ADM_CAPTCHA_PRESET_6")?></option>
	</select>
	</td>
	<td valign="top" rowspan="<?echo count($arSettings)+1?>">
		<?for($i=0;$i < 10; $i++):?>
			<img id="CAPTCHA_<?echo $i?>" src="/bitrix/admin/captcha.php?Preview=Y&amp;captcha_sid=<?echo $CAPTCHA_CODE?>&amp;i=<?echo $i?>&amp;j=0" width="180" height="40" alt="CAPTCHA" /><br><br><br>
		<?endfor?>
	</td>
</tr>
<?foreach($arSettings as $key => $value):?>
<tr>
	<td<?if($value[0] === "list" && count($value[1]) > 1):?> class="adm-detail-valign-top"<?endif?>>
		<?echo $value[3]?>:
	</td>
	<td>
		<?if($value[0] === "checkbox"):?>
			<input type="checkbox" id="<?echo $key?>" name="<?echo $key?>" value="<?echo htmlspecialcharsbx($value[1])?>" <?if(COption::GetOptionString("main", "CAPTCHA_".$key, $value[2]) === "Y") echo "checked"?>>
		<?elseif($value[0] === "list"):
			$vv = explode(",", COption::GetOptionString("main", "CAPTCHA_".$key, implode(",", $value[2])));
			?>
			<select multiple id="<?echo $key?>" name="<?echo $key?>[]" size="<?echo count($value[1])?>">
			<?foreach($value[1] as $k => $v):?>
				<option value="<?echo htmlspecialcharsbx($k)?>" <?if(in_array($k, $vv)) echo "selected"?>><?echo htmlspecialcharsbx($v)?></option>
			<?endforeach?>
			</select>
		<?else:?>
			<input type="text" size="<?echo $value[1]?>" id="<?echo $key?>" name="<?echo $key?>" value="<?echo htmlspecialcharsbx(COption::GetOptionString("main", "CAPTCHA_".$key, $value[2]))?>">
		<?endif?>
	</td>
</tr>
<?endforeach?>
<?
$tabControl->Buttons(array("disabled" => !$isAdmin));
$tabControl->End();
?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>