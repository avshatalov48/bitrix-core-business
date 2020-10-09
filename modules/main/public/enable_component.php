<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

IncludeModuleLangFile(__FILE__);

$strWarning = "";
$arComponent = false;

$io = CBXVirtualIo::GetInstance();

$src_path = $io->CombinePath("/", $_GET["src_path"]);
$src_line = intval($_GET["src_line"]);

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoFileOperation('fm_lpa', array($_GET["src_site"], $src_path)))
	die(GetMessage("ACCESS_DENIED"));

// try to read parameters from script file
/* Try to open script containing the component call */
if(!$src_path || $src_line <= 0)
	$strWarning = GetMessage("comp_prop_err_param");
else
{
	$abs_path = $io->RelativeToAbsolutePath($src_path);
	$f = $io->GetFile($abs_path);
	$filesrc = $f->GetContents();
	if(!$filesrc || $filesrc == "")
		$strWarning = GetMessage("comp_prop_err_open");
}

if($strWarning == "")
{
	$arComponent = PHPParser::FindComponent($_GET["component_name"], $filesrc, $src_line);

	if($arComponent === false)
		$strWarning = GetMessage("comp_prop_err_comp");
}

if($strWarning == "")
{
	if(!check_bitrix_sessid())
	{
		$strWarning = GetMessage("comp_prop_err_save");
	}
	else
	{
		if(!is_array($arComponent["DATA"]["FUNCTION_PARAMS"]))
			$arComponent["DATA"]["FUNCTION_PARAMS"] = array();
		
		$arComponent["DATA"]["FUNCTION_PARAMS"]["ACTIVE_COMPONENT"] = ($_GET['active'] == 'N'? 'N':'Y');

		$code =  ($arComponent["DATA"]["VARIABLE"]? $arComponent["DATA"]["VARIABLE"]."=":"").
			"\$APPLICATION->IncludeComponent(\"".$arComponent["DATA"]["COMPONENT_NAME"]."\", ".
			"\"".$arComponent["DATA"]["TEMPLATE_NAME"]."\", ".
			"array(\r\n\t".PHPParser::ReturnPHPStr2($arComponent["DATA"]["PARAMS"])."\r\n\t)".
			",\r\n\t".($arComponent["DATA"]["PARENT_COMP"] <> ''? $arComponent["DATA"]["PARENT_COMP"] : "false").
			",\r\n\t"."array(\r\n\t".PHPParser::ReturnPHPStr2($arComponent["DATA"]["FUNCTION_PARAMS"])."\r\n\t)".
			"\r\n);";

		$filesrc_for_save = mb_substr($filesrc, 0, $arComponent["START"]).$code.mb_substr($filesrc, $arComponent["END"]);

		$f = $io->GetFile($abs_path);
		$arUndoParams = array(
			'module' => 'fileman',
			'undoType' => $_GET['active'] == 'N'? 'disable_component' : 'enable_component' ,
			'undoHandler' => 'CFileman::UndoEditFile',
			'arContent' => array(
				'absPath' => $abs_path,
				'content' => $f->GetContents()
			)
		);
		
		if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			$strWarning = GetMessage("comp_prop_err_save");
		else
			CUndo::ShowUndoMessage(CUndo::Add($arUndoParams));
	}
}

if($strWarning <> "")
	echo "<script>alert('".CUtil::JSEscape($strWarning)."')</script>";

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>