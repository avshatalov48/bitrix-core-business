<?
IncludeModuleLangFile(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

$md = CModule::CreateModuleObject($module_id);

$arFilter = Array("ACTIVE"=>"Y");
if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
	$arFilter["ADMIN"] = "N";

$arGROUPS = array();
$z = CGroup::GetList("sort", "asc", $arFilter);
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;
}

if (!function_exists("__GroupRightsShowRow"))
{
	function __GroupRightsShowRowDefault($module_id, $ar, $arSites, $arRightsUseSites, $site_id_tmp)
	{
		$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", false, ($site_id_tmp <> '' ? $site_id_tmp : ""), ($site_id_tmp <> ''));
		if (!$GROUP_DEFAULT_RIGHT)
		{
			if ($site_id_tmp == '')
				$GROUP_DEFAULT_RIGHT = "D";
			else
				return;
		}

		$titleCol = bitrix_sessid_post()."<b>".GetMessage("MAIN_BY_DEFAULT")."</b>";

		__GroupRightsShowRow($titleCol, false, 0, $ar, $GROUP_DEFAULT_RIGHT, $site_id_tmp, $arRightsUseSites, $arSites, false);

	}	

	function __GetGroupRight($module_id, $groupID, $site_id_tmp, $arSites, $arGROUPS)
	{
		static $arRightsAll = array();
		static $bInit = false;

		if (!$bInit)
		{
			$arGroupId = array();
			foreach($arGROUPS as $valueTmp)
			{
				if (
					isset($valueTmp["ID"])
					&& !is_array($valueTmp["ID"])
					&& intval($valueTmp["ID"]) > 0
				)
				{
					$arGroupId[] = $valueTmp["ID"];
				}
			}

			if (!empty($arGroupId))
			{
				$arRightsAll = $GLOBALS["APPLICATION"]->GetUserRightArray($module_id, $arGroupId);
			}

			$bInit = true;
		}

		if (!$site_id_tmp)
		{
			$site_id_tmp = "common";
		}

		$res = '';
		if (
			isset($arRightsAll[$site_id_tmp])
			&& isset($arRightsAll[$site_id_tmp][$groupID])
		)
		{
			$res = $arRightsAll[$site_id_tmp][$groupID];
		}

		return $res;
	}

	function __GroupRightsShowRowGroup($module_id, $ar, $value, $arSites, $arRightsUseSites, $site_id_tmp, $arGROUPS)
	{
		$v = __GetGroupRight($module_id, $value["ID"], $site_id_tmp, $arSites, $arGROUPS);

		if($v == '')
		{
			return;
		}

		$titleCol = $value["NAME"]." [<a title=\"".GetMessage("MAIN_USER_GROUP_TITLE")."\" href=\"/bitrix/admin/group_edit.php?ID=".$value["ID"]."&amp;lang=".LANGUAGE_ID."\">".$value["ID"]."</a>]:".(($value["ID"]==1 && $md->SHOW_SUPER_ADMIN_GROUP_RIGHTS=="Y") ? "<br><small>".GetMessage("MAIN_SUPER_ADMIN_RIGHTS_COMMENT")."</small>" : "");

		__GroupRightsShowRow($titleCol, $value["ID"], $value["ID"], $ar, $v, $site_id_tmp, $arRightsUseSites, $arSites, true);
	}
	
	function __GroupRightsShowRow($titleCol, $groupID, $group_id, $ar, $v, $site_id_tmp, $arRightsUseSites, $arSites, $useDefault = true)
	{
		?><tr>
			<td width="40%"><?=$titleCol?></td>
			<td width="40%"><?
			echo '<input type="hidden" name="GROUPS[]" value="'.$group_id.'">';
			
			$strReturnBox = '<select class="typeselect" name="RIGHTS[]" onchange="__GroupRightsChangeSite(this)" >';

			$ref = $ar["reference"];
			$ref_id = $ar["reference_id"];
			if(!is_array($ref))
				$ref = $ar["REFERENCE"];
			if(!is_array($ref_id))
				$ref_id = $ar["REFERENCE_ID"];

			if ($useDefault)
				$strReturnBox .= '<option value="">'.GetMessage("MAIN_DEFAULT").'</option>';

			for($i=0,$n=count($ref); $i<$n; $i++)
			{
				$strReturnBox .= '<option';
				if(strcasecmp($ref_id[$i], htmlspecialcharsbx($v)) == 0)
					$strReturnBox .= ' selected';
				$strReturnBox .= ' value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($ref[$i]).'</option>';
			}

			echo $strReturnBox.'</select>';
			?></td><td width="20%"><span style="display: <?=(in_array($v, $arRightsUseSites) ? "inline-block" : "none")?>;"><?
				echo SelectBoxFromArray("SITES[]", $arSites, htmlspecialcharsbx($site_id_tmp), GetMessage("group_rights_sites_all"), "class='typeselect' style='width: 150px;'");
			?></span></td>
			<td width="0%"><a href="javascript:void(0)" onClick="__GroupRightsDeleteRow(this)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a></td>
		</tr><?
	}
	
}


if ($MODULE_RIGHT!="D") :

$arSites = array(
		"reference_id" => array(),
		"reference" => array()
	);

$rsSites = CSite::GetList("sort", "asc", Array("ACTIVE" => "Y"));
while ($arSite = $rsSites->GetNext())
{
	$arSites["reference_id"][] = $arSite["ID"];
	$arSites["reference"][] = "[".$arSite["ID"]."] ".$arSite["NAME"];
}

if (method_exists($md, "GetModuleRightList"))
	$ar = call_user_func(array($md, "GetModuleRightList"));
else
	$ar = $APPLICATION->GetDefaultRightList();


$arRightsUseSites = array();

echo "<script type=\"text/javascript\">\n".
	"var arRightsUseSites = new Array();\n";

if (array_key_exists("use_site", $ar))
{
	foreach ($ar["use_site"] as $reference_id)
	{
		$arRightsUseSites[] = $reference_id;
		echo "arRightsUseSites[arRightsUseSites.length] = '".$reference_id."';\n";
	}
}

echo "</script>\n";	

echo "<script type=\"text/javascript\">\n".
	"if ('__GroupRightsChangeSite' != typeof window.noFunc) { \n".
		"function __GroupRightsChangeSite(el)\n".
		"{\n".
			"var number = el.selectedIndex;\n".
			"if(BX.util.in_array(el.options[number].value, arRightsUseSites)) { \n".
				"BX(el).parentNode.nextSibling.firstChild.style.display = 'block';\n".
			"}\n".
			"else { \n".
				"BX(el).parentNode.nextSibling.firstChild.style.display = 'none';\n".
			"}\n".
		"}\n".
	"}\n".
	
	"if ('__GroupRightsDeleteRow' != typeof window.noFunc) { \n".
		"function __GroupRightsDeleteRow(el)\n".
		"{\n".
			"BX.remove(BX.findParent(el, {'tag': 'tr'}));\n".
			"return false;\n".
		"}\n".
	"}\n".
	"</script>\n";
				
if($REQUEST_METHOD=="POST" && $Update <> '' && $MODULE_RIGHT=="W" && check_bitrix_sessid())
{
	if (count($GROUPS)>0)
	{
// echo "Remove all options<br>";
		COption::RemoveOption($module_id, "GROUP_DEFAULT_RIGHT");	
// echo "Delete group rights for all sites<br>";
		$APPLICATION->DelGroupRight($module_id, array(), false);
		foreach($arSites["reference_id"] as $site_id_tmp)
		{
// echo "Delete group rights for site ".$site_id_tmp."<br>";		
			$APPLICATION->DelGroupRight($module_id, array(), $site_id_tmp);
		}

		foreach($GROUPS as $i => $group_id)
		{
			if ($group_id == '')
				continue;
				
			if (
				!array_key_exists($i, $RIGHTS)
				|| $RIGHTS[$i] == ''
			)
				continue;

			if (intval($group_id) == 0)
			{
				if (
					!in_array($RIGHTS[$i], $arRightsUseSites)
					|| $SITES[$i] == ''
				)
				{
// echo "Set Default for all sites: ". $RIGHTS[$i]."<br>";
					COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $RIGHTS[$i], "Right for groups by default", "");						
				}
				else
				{
// echo "Set Default for site ".$SITES[$i].": ".$RIGHTS[$i]."<br>";
					COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $RIGHTS[$i], "Right for groups by default for site ".$SITES[$i], $SITES[$i]);
				
				}
			}
			else
			{
				if (
					!in_array($RIGHTS[$i], $arRightsUseSites)
					|| $SITES[$i] == ''
				)
				{
// echo "Set Right for group ".$group_id." all sites: ".$RIGHTS[$i]."<br>";						
					$APPLICATION->SetGroupRight($module_id, $group_id, $RIGHTS[$i], false);
			
				}
				else
				{
// echo "Set Right for group ".$group_id." ".$SITES[$i].": ".$RIGHTS[$i]."<br>";
					$APPLICATION->SetGroupRight($module_id, $group_id, $RIGHTS[$i], $SITES[$i]);
				}
			}
		}
	}
}

__GroupRightsShowRowDefault($module_id, $ar, $arSites, $arRightsUseSites, "");
foreach ($arSites["reference_id"] as $site_id_tmp)
{
	__GroupRightsShowRowDefault($module_id, $ar, $arSites, $arRightsUseSites, $site_id_tmp);
}

foreach($arGROUPS as $value)
{
	__GroupRightsShowRowGroup($module_id, $ar, $value, $arSites, $arRightsUseSites, false, $arGROUPS);
	foreach ($arSites["reference_id"] as $site_id_tmp)
	{
		__GroupRightsShowRowGroup($module_id, $ar, $value, $arSites, $arRightsUseSites, $site_id_tmp, $arGROUPS);
	}
}
?><tr>
	<td><select style="width:300px" onchange="settingsSetGroupID(this)" name="GROUPS[]">
		<option value=""><?echo GetMessage("group_rights_select")?></option>
		<option value="0"><?echo GetMessage("group_rights_default")?></option>		
		<?
		foreach($arGROUPS as $group):
			?>
			<option value="<?=$group["ID"]?>"><?=$group["NAME"]." [".$group["ID"]."]"?></option>
			<?
		endforeach;
		?>
	</select></td>
	<td><?
		$strReturnBox = '<select class="typeselect" name="RIGHTS[]">';

		$ref = $ar["reference"];
		$ref_id = $ar["reference_id"];
		if(!is_array($ref))
			$ref = $ar["REFERENCE"];
		if(!is_array($ref_id))
			$ref_id = $ar["REFERENCE_ID"];

		if ($useDefault)
			$strReturnBox .= '<option value="">'.GetMessage("MAIN_DEFAULT").'</option>';

		for($i=0,$n=count($ref); $i<$n; $i++)
			$strReturnBox .= '<option value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($ref[$i]).'</option>';

		echo $strReturnBox.'</select>';
	?></td>
	<td width="20%"><span style="display: none;"><?
		echo SelectBoxFromArray("SITES[]", $arSites, "", GetMessage("group_rights_sites_all"), "class='typeselect' style='width: 150px;'");
	?></span></td>
	<td width="0%"></td>
</tr>
<tr>
	<td></td>
	<td style="padding-bottom:10px;">
<script type="text/javascript">

function settingsSetGroupID(el)
{
	var tableRow = BX.findParent(el, { 'tag': 'tr'});
	var sel = BX.findChild(tableRow.cells[1], "select", true);
	var selGroup = BX.findChild(tableRow.cells[0], {'tag': 'select'}, true);
	var selSite = BX.findChild(tableRow.cells[2], {'tag': 'select'}, true);
	var number = selGroup.selectedIndex;

	RightsRowNew = new BX.CRightsRowNew({'row': tableRow});
	BX.bind(sel, "change", BX.delegate(RightsRowNew.ChangeSite, RightsRowNew));
}

function settingsAddRights(a)
{
	var row = BX.findParent(a, { 'tag': 'tr'});
	var tbl = row.parentNode;

	var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
	tbl.insertBefore(tableRow, row);

	var selRights = BX.findChild(tableRow.cells[1], { 'tag': 'select'}, true);
	selRights.selectedIndex = 0;

	selGroups = BX.findChild(tableRow.cells[0], { 'tag': 'select'}, true);
	selGroups.selectedIndex = 0;

	selSites = BX.findChild(tableRow.cells[2], { 'tag': 'select'}, true);
	selSites.selectedIndex = 0;
	
	selSiteSpan = BX.findChild(tableRow.cells[2], { 'tag': 'span'}, true);
	selSiteSpan.style.display = "none";	
	
	RightsRowNew = new BX.CRightsRowNew({'row': tableRow});

	BX.bind(selRights, "change", BX.delegate(RightsRowNew.ChangeSite, RightsRowNew));
}

BX.CRightsRowNew = function(arParams) 
{
	this.row = arParams.row;
}

BX.CRightsRowNew.prototype.settingsSetGroupID = function()
{
	var tr = this.row;
	var selGroup = BX.findChild(tr.cells[0], { 'tag': 'select'}, true);
	var selSite = BX.findChild(tr.cells[2], { 'tag': 'select'}, true);	
	var selRights = BX.findChild(tr.cells[1], { 'tag': 'select'}, true);	

	BX.bind(selRights, "change", BX.delegate(this.ChangeSite, this));	
}

BX.CRightsRowNew.prototype.ChangeSite = function()
{
	var tr = this.row;
	var selSiteSpan = BX.findChild(tr.cells[2], { 'tag': 'span'}, true);
	var selRights = BX.findChild(tr.cells[1], { 'tag': 'select'}, true);
	var number = selRights.selectedIndex;

	if(BX.util.in_array(selRights.options[number].value, arRightsUseSites))
		selSiteSpan.style.display = 'block';
	else
		selSiteSpan.style.display = 'none';	
}

</script>
		<a href="javascript:void(0)" onclick="settingsAddRights(this)" hidefocus="true" class="adm-btn"><?echo GetMessage("group_rights_add")?></a>
	</td>
	<td></td>
	<td></td>
</tr>

<?endif;?>
