<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

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
$arAgent = Array(
		"FIZ" => Array(
			"SURNAME" => GetMessage("SOG_SURNAME"),
			"NAME" => GetMessage("SOG_NAME"),
			"SECOND_NAME" => GetMessage("SOG_SECOND_NAME"),
			"BIRTHDAY" => GetMessage("SOG_BIRTHDAY"),
			"MALE" => GetMessage("SOG_MALE"),
			"INN" => GetMessage("SOG_INN"),
			"KPP" => GetMessage("SOG_KPP"),
			"ADDRESS_FULL" => GetMessage("SOG_ADDRESS_FULL"),
			"INDEX" => GetMessage("SOG_INDEX"),
			"COUNTRY" => GetMessage("SOG_COUNTRY"),
			"REGION" => GetMessage("SOG_REGION"),
			"STATE" => GetMessage("SOG_STATE"),
			"TOWN" => GetMessage("SOG_TOWN"),
			"CITY" => GetMessage("SOG_CITY"),
			"STREET" => GetMessage("SOG_STREET"),
			"BUILDING" => GetMessage("SOG_BUILDING"),
			"HOUSE" => GetMessage("SOG_HOUSE"),
			"FLAT" => GetMessage("SOG_FLAT"),
		),
		"UR" => Array(
			"ADDRESS_FULL" => GetMessage("SOG_ADDRESS_FULL"),
			"INDEX" => GetMessage("SOG_INDEX"),
			"COUNTRY" => GetMessage("SOG_COUNTRY"),
			"REGION" => GetMessage("SOG_REGION"),
			"STATE" => GetMessage("SOG_STATE"),
			"TOWN" => GetMessage("SOG_TOWN"),
			"CITY" => GetMessage("SOG_CITY"),
			"STREET" => GetMessage("SOG_STREET"),
			"BUILDING" => GetMessage("SOG_BUILDING"),
			"HOUSE" => GetMessage("SOG_HOUSE"),
			"FLAT" => GetMessage("SOG_FLAT"),
			"INN" => GetMessage("SOG_INN"),
			"KPP" => GetMessage("SOG_KPP"),
			"EGRPO" => GetMessage("SOG_EGRPO"),
			"OKVED" => GetMessage("SOG_OKVED"),
			"OKDP" => GetMessage("SOG_OKDP"),
			"OKOPF" => GetMessage("SOG_OKOPF"),
			"OKFC" => GetMessage("SOG_OKFC"),
			"OKPO" => GetMessage("SOG_OKPO"),
			"ACCOUNT_NUMBER" => GetMessage("SOG_ACCOUNT_NUMBER"),
			"B_NAME" => GetMessage("SOG_B_NAME"),
			"B_BIK" => GetMessage("SOG_B_BIK"),
			"B_ADDRESS_FULL" => GetMessage("SOG_B_ADDRESS_FULL"),
			"B_INDEX" => GetMessage("SOG_B_INDEX"),
			"B_COUNTRY" => GetMessage("SOG_B_COUNTRY"),
			"B_REGION" => GetMessage("SOG_B_REGION"),
			"B_STATE" => GetMessage("SOG_B_STATE"),
			"B_TOWN" => GetMessage("SOG_B_TOWN"),
			"B_CITY" => GetMessage("SOG_B_CITY"),
			"B_STREET" => GetMessage("SOG_B_STREET"),
			"B_BUILDING" => GetMessage("SOG_B_BUILDING"),
			"B_HOUSE" => GetMessage("SOG_B_HOUSE"),
		),					
	);
	$arAgentInfo = Array(
			"AGENT_NAME" =>  GetMessage("SOG_AGENT_NAME"),
			"FULL_NAME" => GetMessage("SOG_FULL_NAME"),
		);
	
	if($type == '')
		$type = "FIZ";
		
	foreach($arAgent[$type] as $k => $v)
		$arAgentInfo[$k] = $v;

	$arAgentInfo["PHONE"] = GetMessage("SOG_PHONE");
	$arAgentInfo["EMAIL"] = GetMessage("SOG_EMAIL");
	$arAgentInfo["CONTACT_PERSON"] = GetMessage("SOG_CONTACT_PERSON");
	$arAgentInfo["F_ADDRESS_FULL"] = GetMessage("SOG_F_ADDRESS_FULL");
	$arAgentInfo["F_INDEX"] = GetMessage("SOG_F_INDEX");
	$arAgentInfo["F_COUNTRY"] = GetMessage("SOG_F_COUNTRY");
	$arAgentInfo["F_REGION"] = GetMessage("SOG_F_REGION");
	$arAgentInfo["F_STATE"] = GetMessage("SOG_F_STATE");
	$arAgentInfo["F_TOWN"] = GetMessage("SOG_F_TOWN");
	$arAgentInfo["F_CITY"] = GetMessage("SOG_F_CITY");
	$arAgentInfo["F_STREET"] = GetMessage("SOG_F_STREET");
	$arAgentInfo["F_BUILDING"] = GetMessage("SOG_F_BUILDING");
	$arAgentInfo["F_HOUSE"] = GetMessage("SOG_F_HOUSE");
	$arAgentInfo["F_FLAT"] = GetMessage("SOG_F_FLAT");
	

	$fields = "";
		
	if (is_array($arAgentInfo) && count($arAgentInfo) > 0)
	{
		$res  = '<table border="0" cellspacing="0" cellpadding="0" class="internal" width="0%">';
		$res .= '<tr class="heading"><td align="center">'.GetMessage("SOG_PARAM").'</td>';
		$res .= '<td align="center">'.GetMessage("SPSG_TYPE").'</td>';
		$res .= '<td align="center">'.GetMessage("SPSG_VALUE").'</td>';
		$res .= '</tr>';

		foreach ($arAgentInfo as $key => $value)
		{
			if ($fields <> '')
				$fields .= ",";
			$fields .= $key;

			$res .= '<tr><td>';
			$res .= $value;
			$res .= '</td>';
			$res .= '<td><select name="TYPE_'.$key.'_'.$divInd.'" id="TYPE_'.$key.'_'.$divInd.'" OnChange="PropertyTypeChange(\''.$key.'\', '.$divInd.')">';
			$res .= '<option value="">'.GetMessage("SPSG_OTHER").'</option>';
			$res .= '<option value="USER">'.GetMessage("SPSG_FROM_USER").'</option>';
			$res .= '<option value="ORDER">'.GetMessage("SPSG_FROM_ORDER").'</option>';
			$res .= '<option value="PROPERTY">'.GetMessage("SPSG_FROM_PROPS").'</option>';
			$res .= '</select></td>';
			$res .= '<td><select name="VALUE1_'.$key.'_'.$divInd.'" id="VALUE1_'.$key.'_'.$divInd.'" style="display: none;">';
			$res .= '</select>';
			$res .= '<input type="text" name="VALUE2_'.$key.'_'.$divInd.'" id="VALUE2_'.$key.'_'.$divInd.'" size="40">';
			$res .= '</td></tr>';
		}

		$res .= '</table>';
		$res .= '<br />';
		$res .= '<b>'.GetMessage("SO_ADIT_1C_PARAMS").'</b><br /><br />';
		$res .= '<table border="0" cellspacing="0" cellpadding="0" class="internal" width="0%" id="rekv-table-'.$divInd.'">';
		$res .= '<tr class="heading"><td align="center">'.GetMessage("SOG_PARAM").'</td>';
		$res .= '	<td align="center">'.GetMessage("SPSG_TYPE").'</td>';
		$res .= '	<td align="center">'.GetMessage("SPSG_VALUE").'</td>';
		$res .= '</tr>';
		
		$res .= '<tr>';
		$res .= '	<td><input type="text" name="REKV_'.$divInd.'_n0" id="REKV_'.$divInd.'_n0" size="40"></td>';
		$res .= '	<td><select name="TYPE_REKV_n0_'.$divInd.'" id="TYPE_REKV_n0_'.$divInd.'" OnChange="PropertyTypeChange(\'REKV_n0\', \''.$divInd.'\')">';
		$res .= '			<option value="">'.GetMessage("SPSG_OTHER").'</option>';
		$res .= '			<option value="USER">'.GetMessage("SPSG_FROM_USER").'</option>';
		$res .= '			<option value="ORDER">'.GetMessage("SPSG_FROM_ORDER").'</option>';
		$res .= '			<option value="PROPERTY">'.GetMessage("SPSG_FROM_PROPS").'</option>';
		$res .= '		</select></td>';
		$res .= '	<td><select name="VALUE1_REKV_n0_'.$divInd.'" id="VALUE1_REKV_n0_'.$divInd.'" style="display: none;"></select>';
		$res .= '	<input type="text" name="VALUE2_REKV_n0_'.$divInd.'" id="VALUE2_REKV_n0_'.$divInd.'" size="40">';
		$res .= '	</td>';
		$res .= '</tr>';

		$res .= '</table>';

		$res .= '<br /><input type="button" value="'.GetMessage('SPSG_ADD').'" onclick="AddRekvMore('.$divInd.')">';

		$res = CUtil::JSEscape($res);
	}
?>
<script language="JavaScript">
<!--
window.parent.document.getElementById("export_fields_<?= $divInd ?>").value = "<?= $fields ?>";
window.parent.document.getElementById("export_<?= $divInd ?>").innerHTML = '<?= $res ?>';
<?
if (is_array($arAgentInfo) && count($arAgentInfo) > 0)
{
	foreach ($arAgentInfo as $key => $value)
	{
		?>window.parent.InitActionProps('<?= $key ?>', <?= $divInd ?>);<?
	}
	?>window.parent.InitActionProps('REKV_n0', <?= $divInd ?>);<?
}
?>
window.parent.BX.onCustomEvent('onAdminTabsChange');
//-->
</script>
</body>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>