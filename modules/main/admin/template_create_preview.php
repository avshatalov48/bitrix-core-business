<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

ClearVars();

$edit_php = $USER->CanDoOperation('edit_php');
if(!$edit_php && !$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('lpa_template_edit'))
	die();

IncludeModuleLangFile(__FILE__);
$lpa = ($USER->CanDoOperation('lpa_template_edit') && !$edit_php); // Limit PHP access: for non admin users

$strWarning = "";
$ID = $_REQUEST["ID"];

if($_SERVER["REQUEST_METHOD"] == "POST" && ($edit_php || $lpa) && check_bitrix_sessid())
{
	CUtil::decodeURIComponent($_POST);
	$CONTENT = $_POST["CONTENT"];
	$STYLES = $_POST["STYLES"];
	$TEMPLATE_STYLES = $_POST["TEMPLATE_STYLES"];
	$strWarning = "";

	// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *   LPA  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
	if ($lpa)
	{
		// Find all php fragments in $CONTENT
		// 1. Kill all non-component 2.0 fragments
		// 2. Get and check params of components
		$content_ = $CONTENT;
		$arPHP = PHPParser::ParseFile($content_);
		$l = count($arPHP);
		if ($l > 0)
		{
			$new_content = '';
			$end = 0;
			$php_count = 0;
			for ($n = 0; $n<$l; $n++)
			{
				$start = $arPHP[$n][0];
				$new_content .= LPA::EncodePHPTags(substr($content_,$end,$start-$end));
				$end = $arPHP[$n][1];

				//Trim php tags
				$src = $arPHP[$n][2];
				if (substr($src, 0, 5) == "<?php")
					$src = '<?'.substr($src, 5);

				//If it's Component 2 - we handle it's params, non components2 will be erased
				$comp2_begin = '<?$APPLICATION->INCLUDECOMPONENT(';
				if (strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin)
				{
					$arRes = PHPParser::CheckForComponent2($src);
					if ($arRes)
					{
						$comp_name = _replacer($arRes['COMPONENT_NAME']);
						$template_name = _replacer($arRes['TEMPLATE_NAME']);
						$arParams = $arRes['PARAMS'];
						$arPHPparams = Array();
						//all php fragments wraped by ={}
						foreach ($arParams as $param_name => $paramval)
						{
							if (substr($paramval,0,2) == '={' && substr($paramval,-1) == '}')
								$arPHPparams[] = $param_name;
						}

						$len = count($arPHPparams);
						$br = "\r\n";

						$code =  '$APPLICATION->IncludeComponent('.$br.
								"\t".'"'.$comp_name.'",'.$br.
								"\t".'"'.$template_name.'",'.$br;
						// If exist at least one parameter with php code inside
						if (count($arParams) > 0)
						{
							// Get array with description of component params
							$arCompParams = CComponentUtil::GetComponentProps($comp_name);
							$arTemplParams = CComponentUtil::GetTemplateProps($comp_name, $template_name, $template);

							$arParameters = array();
							if (isset($arCompParams["PARAMETERS"]) && is_array($arCompParams["PARAMETERS"]))
								$arParameters = $arParameters + $arCompParams["PARAMETERS"];
							if (is_array($arTemplParams))
								$arParameters = $arParameters + $arTemplParams;

							// Replace values from 'DEFAULT'
							for ($e = 0; $e < $len; $e++)
							{
								$par_name = $arPHPparams[$e];
								$arParams[$par_name] = isset($arParameters[$par_name]['DEFAULT']) ? $arParameters[$par_name]['DEFAULT'] : '';
							}

							CComponentUtil::PrepareVariables($arParams);
							//ReturnPHPStr
							$params = PHPParser::ReturnPHPStr2($arParams, $arParameters);

							$code .= "\t".'Array('.$br.
								"\t".$params.$br.
								"\t".')'.$br.
								');';
						}
						else
							$code .= "\t".'Array()'.$br.');';

						$code = '<?'.$code.'?>';
						$new_content .= $code;

					}
				}
			}

			$new_content .= LPA::EncodePHPTags(substr($content_,$end));
			$CONTENT = $new_content;
		}
		else
			$CONTENT = LPA::EncodePHPTags($new_content);

		// Get array of PHP scripts from original template src
		if(strlen($ID) > 0)
		{
			$templ = CSiteTemplate::GetByID($ID);
			if(!$templ->ExtractFields("str_"))
				$strWarning = GetMessage('templ_create_err', array('#ID#'=>$ID));
		}
		else
		{
			$strWarning = GetMessage('templ_create_err1');
		}
		checkError($strWaring);

		$old_content = htmlspecialcharsback($str_CONTENT);
		$arPHP = PHPParser::ParseFile($old_content);
		$l = count($arPHP);
		$s1 = "";

		if ($l > 0)
		{
			$new_content = '';
			$end = 0;
			$php_count = 0;
			$wa = '#WORK_AREA#';

			for ($n = 0; $n<$l; $n++)
			{
				$start = $arPHP[$n][0];
				$s_cont = substr($old_content, $end, $start - $end);
				$end = $arPHP[$n][1];

				if ($n == 0)
					continue;

				$src = $arPHP[$n][2];
				if (strpos($s_cont, $wa) !== false)
				{
					$s2 = substr($s_cont, strpos($s_cont, $wa) + strlen($wa)).$src;
					continue;
				}
				//Trim php tags
				$src = SubStr($src, ((SubStr($src, 0, 5) == "<?"."php") ? 5 : 2));
				$src = SubStr($src, 0, -2);

				$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';

				$usrc = unifyPHPfragment($src);
				if ($usrc == unifyPHPfragment('$APPLICATION->ShowPanel()') ||
					strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin) // component 2.0 or another predefined fragment
					continue;
				$arPHPscripts[] = $src;
			}
			$s1 = $arPHP[0][2];
		}

		// Ok, so we already have array of php scripts lets check our new content
		// LPA-users CAN delete PHP fragments and swap them but CAN'T add new or modify existent:
		while (preg_match('/#PHP\d{4}#/i', $CONTENT, $res_php, PREG_OFFSET_CAPTURE))
		{
			$php_begin = $res_php[0][1];
			$php_fr_num = intval(substr($CONTENT, $php_begin + 4, 4)) - 1; // Number of PHP fragment from #PHPXXXX# conctruction
			if (isset($arPHPscripts[$php_fr_num]))
			{
				$codeFragment = '<?'.$arPHPscripts[$php_fr_num].'?>';
			}
			else
			{
				$codeFragment = '<??>';
			}
			$CONTENT = substr($CONTENT, 0, $php_begin).$codeFragment.substr($CONTENT, $php_begin + 9);
		}

		//Add ..->ShowPanel() & "Secutity Stubs"
		$sp = '<?$APPLICATION->ShowPanel();?>';
		$body = '<body>';
		$body_pos = strpos($CONTENT, $body);
		if ($body_pos > 0)
			$content_ = substr($CONTENT, 0, $body_pos + strlen($body)).$sp.substr($CONTENT, $body_pos + strlen($body));
		else
			$content_ = $CONTENT;

		$wa = '#WORK_AREA#';
		$wa_pos = strpos($content_, $wa, $body_pos);
		if ($wa_pos > 0)
			$content__ = $s1.substr($content_, 0, $wa_pos + strlen($wa)).$s2.substr($content_, $wa_pos + strlen($wa));

		$CONTENT = $content__;
	}
	// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *

	if ($USER->CanDoFileOperation('fm_view_file', array(SITE_ID, BX_PERSONAL_ROOT."/templates/".$ID)) && strlen($ID) > 0)
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID, $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview", false, true);

	if(strlen($CONTENT) > 0)
	{
		$p = strpos($CONTENT, "#WORK_AREA#");
		if ($p === false)
			$strWaring .= GetMessage('MAIN_TP_ERROR_WORK_AREA');

		checkError($strWaring);

		$header = substr($CONTENT, 0, $p);
		if (!$APPLICATION->SaveFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview/header.php", $header))
			$strWaring .= GetMessage('MAIN_TP_ERROR_SAVE_FILE', Array('#FILE#' => 'header.php'));

		$footer = substr($CONTENT, $p + strlen("#WORK_AREA#"));
		if (!$APPLICATION->SaveFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview/footer.php", $footer))
			$strWaring .= GetMessage('MAIN_TP_ERROR_SAVE_FILE', Array('#FILE#' => 'footer.php'));
	}

	if(strlen($STYLES) == 0)
		$STYLES = ' ';
	if (!$APPLICATION->SaveFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview/styles.css", $STYLES))
		$strWaring .= GetMessage('MAIN_TP_ERROR_SAVE_FILE', Array('#FILE#' => 'styles.css'));

	if(strlen($TEMPLATE_STYLES) == 0)
		$TEMPLATE_STYLES = ' ';
	if (!$APPLICATION->SaveFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview/template_styles.css", $TEMPLATE_STYLES))
		$strWaring .= GetMessage('MAIN_TP_ERROR_SAVE_FILE', Array('#FILE#' => 'template_styles.css'));

	checkError($strWaring);
	?>
<script type="text/javascript" bxrunfirst="true">
BX.adminPanel.closeWait();
__status = true;
</script>
	<?
}

function unifyPHPfragment($str)
{
	if (substr($str, -1) == ';')
		$str = substr($str, 0, -1);
	$str = strtolower($str);
	$str = preg_replace("/\\s/i", "", $str);
	return $str;
}

function _replacer($str)
{
	$str = preg_replace("/[^a-zA-Z0-9_:\\.]/i", "", $str);
	return $str;
}

function checkError($strWaring)
{
	if (strlen($strWaring) <= 0)
		return;
	echo 'ERROR';

	?>
<script type="text/javascript" bxrunfirst="true">
BX.adminPanel.closeWait();
__status = false;
strWarning = '<?=CUtil::JSEscape($strWaring)?>';
</script>
	<?
	die();
}
?>