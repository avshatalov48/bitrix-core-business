<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

if(!$USER->CanDoOperation('edit_php'))
	die(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

CUtil::JSPostUnescape();

$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => GetMessage("comp_prop_title"),
		'ARGS' => 'path='.urlencode(CUtil::addslashes($_GET["path"])).
				'&amp;template_id='.urlencode(CUtil::addslashes($_GET["template_id"])).
				'&amp;lang='.LANGUAGE_ID.
				'&amp;src_path='.urlencode(CUtil::addslashes($_GET["src_path"])).
				'&amp;src_line='.intval($_GET["src_line"]).
				'&amp;action=save'
	)
);

$obJSPopup->ShowTitlebar();

$strWarning = "";
$arValues = array();
$arTemplate = false;
$aComponent = false;

$io = CBXVirtualIo::GetInstance();

if($_SERVER["REQUEST_METHOD"] == "POST" && $_GET["action"] == "refresh")
{
	// parameters were changed by "ok" button
	// we need to refresh the component description with new values
	$arValues = $_POST;
}
else
{
	// try to read parameters from script file

	/* Try to open script containing the component call */
	$src_path = $_GET["src_path"];
	$src_line = intval($_GET["src_line"]);

	if(!$src_path || $src_line <= 0)
	{
		$strWarning .= GetMessage("comp_prop_err_param")."<br>";
	}
	else
	{
		$abs_path = $src_path;
		$f = $io->GetFile($abs_path);
		$filesrc = $f->GetContents();

		if(!$filesrc || $filesrc == "")
			$strWarning .= GetMessage("comp_prop_err_open")."<br>";
	}

	if($strWarning == "")
	{
		/* parse source file for PHP code */
		$arScripts = PHPParser::ParseFile($filesrc);

		/* identify the component by line number */
		$aComponents = array();
		foreach($arScripts as $script)
		{
			$nLineFrom = substr_count(substr($filesrc, 0, $script[0]), "\n")+1;
			$nLineTo = substr_count(substr($filesrc, 0, $script[1]), "\n")+1;
			if($nLineFrom <= $src_line && $nLineTo >= $src_line)
				$aComponents[] = $script;
			if($nLineTo > $src_line)
				break;
		}

		foreach($aComponents as $component)
		{
			$arRes = PHPParser::CheckForComponent($component[2]);

			if($arRes && $arRes["SCRIPT_NAME"] == $_GET["path"])
			{
				$arValues = $arRes["PARAMS"];
				$aComponent = $component;
				break;
			}
		}
	}
	if($aComponent === false)
		$strWarning .= GetMessage("comp_prop_err_comp")."<br>";
} //$_SERVER["REQUEST_METHOD"] == "POST" && $_GET["action"] == "refresh"


if($strWarning == "")
{
	$arTemplate = CTemplates::GetByID($_GET["path"], $arValues, $_GET["template_id"]);

	/* save parameters to file */
	if($_SERVER["REQUEST_METHOD"] == "POST" && $_GET["action"] == "save" && $aComponent !== false && $arTemplate !== false)
	{
		if (!check_bitrix_sessid())
		{
			$strWarning .= GetMessage("comp_prop_err_save")."<br>";
		}
		else
		{
			$params = PHPParser::ReturnPHPStr($_POST, $arTemplate["PARAMS"]);

			if($params <> "")
				$code =  "<"."?".($arRes["VARIABLE"]?$arRes["VARIABLE"]."=":"")."\$APPLICATION->IncludeFile(\"".$_GET["path"]."\", Array(\r\n\t".$params."\r\n\t)\r\n);?".">";
			else
				$code = "<"."?".($arRes["VARIABLE"]?$arRes["VARIABLE"]."=":"")."\$APPLICATION->IncludeFile(\"".$_GET["path"]."\");?".">";

			$filesrc_for_save = substr($filesrc, 0, $aComponent[0]).$code.substr($filesrc, $aComponent[1]);

			if($APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				$obJSPopup->Close();
			}
			else
				$strWarning .= GetMessage("comp_prop_err_save")."<br />";
		}
	}
}

if($arTemplate["ICON"] == "" || !is_file($_SERVER["DOCUMENT_ROOT"].$arTemplate["ICON"]))
	$arTemplate["ICON"] = "/bitrix/images/fileman/htmledit2/component.gif";

$obJSPopup->StartDescription($arTemplate['ICON']);
?>
<?if($arTemplate["NAME"] <> ""):?>
<p title="<?echo GetMessage("comp_prop_name")?>"><b><?echo htmlspecialcharsbx($arTemplate["NAME"])?></b></p>
<?endif;?>
<?if($arTemplate["DESCRIPTION"] <> ""):?>
<p title="<?echo GetMessage("comp_prop_desc")?>"><?echo htmlspecialcharsbx($arTemplate["DESCRIPTION"])?></p>
<?endif;?>
<p class="note" title="<?echo GetMessage("comp_prop_path")?>"><?echo htmlspecialcharsbx($arTemplate["REAL_PATH"]<>""? $arTemplate["REAL_PATH"]:$_GET["path"])?></p>
<?
if($strWarning <> "")
	//ShowError($strWarning);
	$obJSPopup->ShowValidationError($strWarning);
?>
<?
$obJSPopup->StartContent();
if(!empty($arTemplate["PARAMS"])):
?>
<table cellspacing="0" class="bx-width100">
<?
foreach($arTemplate["PARAMS"] as $ID=>$prop):
?>
	<tr>
		<td><?echo htmlspecialcharsbx($prop["NAME"]).":"?></td>
		<td>
<?
if(!array_key_exists($ID, $arValues) && isset($prop["DEFAULT"]))
	$arValues[$ID] = $prop["DEFAULT"];

if($prop["MULTIPLE"]=='Y' && !is_array($arValues[$ID]))
{
	if(isset($arValues[$ID]))
		$val = Array($arValues[$ID]);
	else
		$val = Array();
}
elseif($prop["TYPE"]=="LIST" && !is_array($arValues[$ID]))
	$val = Array($arValues[$ID]);
else
	$val = $arValues[$ID];

$res = "";
if($prop["COLS"]<1)
	$prop["COLS"] = '30';

if($prop["MULTIPLE"]=='Y')
{
	$prop["CNT"] = IntVal($prop["CNT"]);
	if($prop["CNT"]<1)
		$prop["CNT"] = 1;
}

switch(strtoupper($prop["TYPE"]))
{
	case "LIST":
		$prop["SIZE"] = ($prop["MULTIPLE"]=='Y' && IntVal($prop["SIZE"])<=1 ? '3' : $prop["SIZE"]);
		if(intval($prop["SIZE"])<=0)
			$prop["SIZE"] = 1;

		$res .= '<select name="'.$ID.($prop["MULTIPLE"]=="Y"?'[]':'').'"'.
			($prop["MULTIPLE"]=="Y"?
				' multiple ':
				($prop['ADDITIONAL_VALUES']!=='N'?
				' onChange="this.form.elements[\''.$ID.'_alt\'].disabled = (this.selectedIndex!=0);" '
				:'')
			).
			' size="'.$prop["SIZE"].'">';

		if(!is_array($prop["VALUES"]))
			$prop["VALUES"] = Array();

		$tmp = ''; $bFound = false;
		foreach($prop["VALUES"] as $v_id=>$v_name)
		{
			$key = array_search($v_id, $val);
			if($key===FALSE || $key===NULL)
				$tmp .= '<option value="'.htmlspecialcharsbx($v_id).'">'.htmlspecialcharsbx($v_name).'</option>';
			else
			{
				unset($val[$key]);
				$bFound = true;
				$tmp .= '<option value="'.htmlspecialcharsbx($v_id).'" selected>'.htmlspecialcharsbx($v_name).'</option>';
			}
		}
		if($prop['ADDITIONAL_VALUES']!=='N')
			$res .= '<option value=""'.(!$bFound?' selected':'').'>'.($prop["MULTIPLE"]=="Y"?GetMessage("comp_prop_not_sel"):GetMessage("comp_prop_other").' -&gt;').'</option>';
		$res .= $tmp;
		$res .= '</select>';
		if($prop['ADDITIONAL_VALUES']!=='N')
		{
			if($prop["MULTIPLE"]=='Y')
			{
				reset($val);
				foreach($val as $v)
				{
					$res .= '<br>';
					if($prop['ROWS']>1)
						$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialcharsbx($v).'</textarea>';
					else
						$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialcharsbx($v).'">';
				}

				for($i=0; $i<$prop["CNT"]; $i++)
				{
					$res .= '<br>';
					if($prop['ROWS']>1)
						$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'></textarea>';
					else
						$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="">';
				}
				$res .= '<input type="button" value="+" onClick="var span = document.createElement(\'SPAN\'); this.parentNode.insertBefore(span, this); span.innerHTML=\''.
						'<br>';
				if($prop['ROWS']>1)
					$res .= '<textarea name=\\\''.$ID.'[]\\\' cols=\\\''.$prop["COLS"].'\\\'></textarea>';
				else
					$res .= '<input type=\\\'text\\\' name=\\\''.$ID.'[]\\\' size=\\\''.$prop["COLS"].'\\\'>';

				$res .= '\'">';
			}
			else
			{
				$res .= '<br>';
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'_alt" '.($bFound?' disabled ':'').' cols='.$prop["COLS"].'>'.htmlspecialcharsbx(count($val)>0?$val[0]:'').'</textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'_alt" '.($bFound?' disabled ':'').'size='.$prop["COLS"].' value="'.htmlspecialcharsbx(count($val)>0?$val[0]:'').'">';
			}
		}
		break;
	default:
		if($prop["MULTIPLE"]=='Y')
		{
			$bBr = false;
			foreach($val as $v)
			{
				if($bBr)
					$res .= '<br>';
				else
					$bBr = true;
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialcharsbx($v).'</textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialcharsbx($v).'">';
			}

			for($i=0; $i<$prop["CNT"]; $i++)
			{
				if($bBr)
					$res .= '<br>';
				else
					$bBr = true;
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'></textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="">';
			}

			$res .= '<input type="button" value="+" onClick="var span = document.createElement(\'SPAN\'); this.parentNode.insertBefore(span, this); span.innerHTML=\''.
					'<br>';
			if($prop['ROWS']>1)
				$res .= '<textarea name=\\\''.$ID.'[]\\\' cols=\\\''.$prop["COLS"].'\\\'></textarea>';
			else
				$res .= '<input type=\\\'text\\\' name=\\\''.$ID.'[]\\\' size=\\\''.$prop["COLS"].'\\\'>';

			$res .= '\'">';
		}
		else
		{
			if($prop['ROWS']>1)
				$res .= '<textarea name="'.$ID.'" cols='.$prop["COLS"].'>'.htmlspecialcharsbx($val).'</textarea>';
			else
				$res .= '<input name="'.$ID.'" size='.$prop["COLS"].' value="'.htmlspecialcharsbx($val).'" type="text">';
		}
		break;
}
if($prop["REFRESH"]=="Y")
	$res .= '<input type="button" value="OK" onclick="'.$obJSPopup->jsPopup.'.PostParameters(\''.
		'path='.urlencode(CUtil::addslashes($_GET["path"])).
		'&amp;template_id='.urlencode(CUtil::addslashes($_GET["template_id"])).
		'&amp;lang='.LANGUAGE_ID.
		'&amp;src_path='.urlencode(CUtil::addslashes($_GET["src_path"])).
		'&amp;src_line='.intval($_GET["src_line"]).
		'&amp;action=refresh\');">';

echo $res;
?>
		</td>
	</tr>
<?endforeach?>
</table>
<?
	$obJSPopup->StartButtons();
	echo '<input id="btn_popup_save" name="btn_popup_save" type="button" value="'.GetMessage("JSPOPUP_SAVE_CAPTION").'" onclick="'.$obJSPopup->jsPopup.'.PostParameters(\'action=save\');" title="'.GetMessage("JSPOPUP_SAVE_CAPTION").'" />'."\r\n";
	$obJSPopup->ShowStandardButtons(array('close'));
else: //!empty($arTemplate["PARAMS"])
	$obJSPopup->ShowStandardButtons(array('close'));
endif; //!empty($arTemplate["PARAMS"])

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>