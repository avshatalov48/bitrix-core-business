<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("sale");
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
</head>
<body>
<?
IncludeModuleLangFile(__FILE__);

$divInd = intval($divInd);

$file = str_replace("\\", "/", $file);
while (mb_strpos($file, "//") !== false)
	$file = str_replace("//", "/", $file);
while (mb_substr($file, mb_strlen($file) - 1, 1) == "/")
	$file = mb_substr($file, 0, mb_strlen($file) - 1);


function LocalGetPSActionParams($fileName)
{
	$arPSCorrespondence = array();

	if (file_exists($fileName) && is_file($fileName))
		include($fileName);

	return $arPSCorrespondence;
}


$res = "";
$fields = "";

$arPSCorrespondence = array();

$path2SystemPSFiles = "/bitrix/modules/sale/payment/";
$path2UserPSFiles = COption::GetOptionString("sale", "path2user_ps_files", BX_PERSONAL_ROOT."/php_interface/include/sale_payment/");
if (mb_substr($path2UserPSFiles, mb_strlen($path2UserPSFiles) - 1, 1) != "/")
	$path2UserPSFiles .= "/";

$bSystemPSFile = (mb_substr($file, 0, mb_strlen($path2SystemPSFiles)) == $path2SystemPSFiles);

if (!$bSystemPSFile)
{
	if (mb_substr($path2UserPSFiles, mb_strlen($path2UserPSFiles) - 1, 1) != "/")
		$path2UserPSFiles .= "/";
	$bUserPSFile = (mb_substr($file, 0, mb_strlen($path2UserPSFiles)) == $path2UserPSFiles);
}

if ($bUserPSFile || $bSystemPSFile)
{
	if ($bUserPSFile)
		$fileName = mb_substr($file, mb_strlen($path2UserPSFiles));
	else
		$fileName = mb_substr($file, mb_strlen($path2SystemPSFiles));

	$fileName = preg_replace("#[^A-Za-z0-9_.-]#i", "", $fileName);

	$arPSCorrespondence = LocalGetPSActionParams($_SERVER["DOCUMENT_ROOT"].(($bUserPSFile) ? $path2UserPSFiles : $path2SystemPSFiles).$fileName."/.description.php");

	if (is_array($arPSCorrespondence) && count($arPSCorrespondence) > 0)
	{
		$res  = '<table border="0" cellspacing="0" cellpadding="0" class="internal" width="100%">';
		$res .= '<tr class="heading"><td align="center" colspan="2">'.GetMessage("SPSG_ACT_PROP").'</td></tr>';

		foreach ($arPSCorrespondence as $key => $value)
		{
			if ($fields <> '')
				$fields .= ",";
			$fields .= $key;

			if ($value["TYPE"] == "SELECT" || $value["TYPE"] == "RADIO" || $value["TYPE"] == "FILE")
			{
				$res .= '<tr><td width="40%">\n';
				$res .= $value["NAME"];
				if ($value["DESCR"] <> '')
					$res .= "<br><small>".$value["DESCR"]."</small>";
				$res .= '</td>\n';
				$res .= '<td width="60%">';
				$res .= '<table border="0" cellspacing="2" cellpadding="0"><tr>\n';
				$res .= '<td>'.GetMessage("SPSG_VALUE").'</td>\n';
				$res .= '<td>';
				$res .= '<select name="TYPE_'.$key.'_'.$divInd.'" id="TYPE_'.$key.'_'.$divInd.'" style="display: none;"><option selected value="'.$value["TYPE"].'"></option></select>';

				if ($value["TYPE"] == "FILE")
				{
					$res .= '<input type="file" name="VALUE1_'.$key.'_'.$divInd.'" id="VALUE1_'.$key.'_'.$divInd.'" size="40"><br>\n';
					$res .= '<span id="'.$key.'_'.$divInd.'_preview"><br>';
					$res .= '<img id="'.$key.'_'.$divInd.'_preview_img" style="max-width: 150px; max-height: 150px; "><br><br>\n';
					$res .= '<input type="checkbox" name="'.$key.'_'.$divInd.'_del" value="Y" id="'.$key.'_'.$divInd.'_del" >\n';
					$res .= '<label for="'.$key.'_'.$divInd.'_del">' . GetMessage("SPSG_DEL") . '</label></span>\n';
				}
				elseif ($value["TYPE"] == "SELECT")
				{
					$res .= '<select name="VALUE1_'.$key.'_'.$divInd.'" id="VALUE1_'.$key.'_'.$divInd.'">';

					foreach ($value["VALUE"] as $k => $v)
						$res .= '<option value="'.$k.'">'.$v["NAME"].'</option>\n';

					$res .= '</select>\n';
				}
				elseif ($value["TYPE"] == "RADIO")
				{
					foreach ($value["VALUE"] as $k => $v)
					{
						$res .= '<input type="radio" name="VALUE1_'.$key.'_'.$divInd.'" id="VALUE1_'.$k.'_'.$divInd.'" value="'.$k.'">\n';
						$res .= '<label for="VALUE1_'.$k.'_'.$divInd.'">'.$v["NAME"].'</label><br>\n';
					}

				}
				$res .= '</td></tr></table></td></tr>\n';
			}
			else
			{
				$res .= '<tr><td width="40%">\n';
				$res .= $value["NAME"];
				if ($value["DESCR"] <> '')
					$res .= "<br><small>".$value["DESCR"]."</small>";
				$res .= '</td>\n';
				$res .= '<td width="60%">';

				$res .= '<table border="0" cellspacing="2" cellpadding="0"><tr>\n';
				$res .= '<td>'.GetMessage("SPSG_TYPE").'</td>\n';

				$res .= '<td><select name="TYPE_'.$key.'_'.$divInd.'" id="TYPE_'.$key.'_'.$divInd.'" OnChange="PropertyTypeChange(\''.$key.'\', '.$divInd.')">\n';
				$res .= '<option value="">'.GetMessage("SPSG_OTHER").'</option>\n';
				$res .= '<option value="USER">'.GetMessage("SPSG_FROM_USER").'</option>\n';
				$res .= '<option value="ORDER">'.GetMessage("SPSG_FROM_ORDER").'</option>\n';
				$res .= '<option value="PROPERTY">'.GetMessage("SPSG_FROM_PROPS").'</option>\n';
				$res .= '</select></td></tr>\n';

				$res .= '<tr><td>'.GetMessage("SPSG_VALUE").'</td>\n';
				$res .= '<td><select name="VALUE1_'.$key.'_'.$divInd.'" id="VALUE1_'.$key.'_'.$divInd.'" style="display: none;">\n';
				$res .= '</select>\n';

				$res .= '<input type="text" name="VALUE2_'.$key.'_'.$divInd.'" id="VALUE2_'.$key.'_'.$divInd.'" size="40">\n';
				$res .= '</td></tr></table>\n';
				$res .= '</td></tr>\n';
			}
		}


		$arTarif = CSalePaySystemsHelper::getPaySystemTarif((($bUserPSFile) ? $path2UserPSFiles : $path2SystemPSFiles).$fileName, $_REQUEST["psid"], $divInd);

		if(is_array($arTarif) && !empty($arTarif))
		{
			$res .= '<tr class="heading"><td align="center" colspan="2">'.GetMessage('SPSG_TARIFS').'</td></tr>';

			$arMultiControlQuery = array();
			foreach ($arTarif as $fieldId => $arField)
			{
				if(!empty($arMultiControlQuery)
					&& (
						!isset($arField['MCS_ID'])
						|| !array_key_exists($arField['MCS_ID'], $arMultiControlQuery)

				))
				{
					$res .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
					$arMultiControlQuery = array();
				}

				$controlHtml = CSaleHelper::getAdminHtml($fieldId, $arField, 'TARIF_'.$divInd, 'pay_sys_form');

				if($arField["TYPE"] == 'MULTI_CONTROL_STRING')
				{
					$arMultiControlQuery[$arField['MCS_ID']]['CONFIG'] = $arField;
					continue;
				}
				elseif(isset($arField['MCS_ID']))
				{
					$arMultiControlQuery[$arField['MCS_ID']]['ITEMS'][] = $controlHtml;
					continue;
				}

				$res .= CSaleHelper::wrapAdminHtml($controlHtml, $arField);
			}

			if(!empty($arMultiControlQuery))
				$res .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
		}

		$res .= '</table>\n';
		$res = str_replace("'", "\'", $res);
	}
}
?>
<script>
<!--
window.parent.document.forms["pay_sys_form"].elements["PS_ACTION_FIELDS_LIST_<?= $divInd ?>"].value = "<?= $fields ?>";
window.parent.document.getElementById("pay_sys_act_<?= $divInd ?>").style["backgroundColor"] = "#E4EDF3";
window.parent.document.getElementById("pay_sys_act_<?= $divInd ?>").innerHTML = '<?= $res; ?>';

<?
if (is_array($arPSCorrespondence) && count($arPSCorrespondence) > 0)
{
	foreach ($arPSCorrespondence as $key => $value)
	{
		?>
		window.parent.InitActionProps('<?= $key ?>', <?= $divInd ?>);
		<?
	}
}

if($exist == "Y")
{
	if (is_array($arPSCorrespondence) && count($arPSCorrespondence) > 0)
	{
		foreach ($arPSCorrespondence as $key => $value)
		{
			if($value["TYPE"] <> '')
			{
				?>
				window.parent.document.getElementById("TYPE_<?=$key?>_<?=$divInd?>").value = '<?=CUtil::JSEscape($value["TYPE"])?>';
				window.parent.PropertyTypeChange('<?=$key?>', '<?=$divInd?>', '<?=CUtil::JSEscape($value["VALUE"])?>');

				<?
			}
			elseif($value["VALUE"] <> '')
			{
				?>
				window.parent.document.getElementById("VALUE2_<?=$key?>_<?=$divInd?>").value = '<?=CUtil::JSEscape($value["VALUE"])?>';
				<?
			}
		}
	}
}

if ($res == '')
{
	?>
	window.parent.document.getElementById("pay_sys_switch_<?= $divInd ?>").innerHTML = "";
	window.parent.document.getElementById("pay_sys_act_<?= $divInd ?>").style["backgroundColor"] = "#F1F1F1";
	<?
}
else
{
	?>
	window.parent.SetActLinkText(<?= $divInd ?>, window.parent.paySysActVisible_<?= $divInd ?>);
	<?
}

?>

var tabControlLayout = window.parent.BX("tabControl_layout");
var rowsToHide = [];

if(tabControlLayout)
	rowsToHide = window.parent.BX.findChildren(tabControlLayout, {'tag':'tr', 'class':'ps-admin-hide'}, true);

for (var i in rowsToHide)
	window.parent.psToggleNextSiblings(rowsToHide[i], 4, true);

window.parent.setTarifValues("<?= $divInd ?>");
window.parent.BX.onCustomEvent('onAdminTabsChange');
//-->
</script>
</body>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>