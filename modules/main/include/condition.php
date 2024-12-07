<?php

IncludeModuleLangFile(__FILE__);

// Compose string in compliance with selected type of condition
function ConditionCompose($arRequest, $i = 0)
{
	$type = $_REQUEST['selected_type'][$i];
	if ($type == 'folder' && $arRequest['CONDITION_folder'] <> '')
	{
		$cond = 'CSite::InDir(\'' . addslashes($arRequest['CONDITION_folder']) . '\')';
	}
	elseif ($type == 'ugroups' && is_array($arRequest['CONDITION_ugroups']))
	{
		for ($i = 0; $i < count($arRequest['CONDITION_ugroups']); $i++)
		{
			$arRequest['CONDITION_ugroups'][$i] = intval($arRequest['CONDITION_ugroups'][$i]);
		}
		$cond = 'CSite::InGroup(array(' . implode(",", $arRequest['CONDITION_ugroups']) . '))';
	}
	elseif ($type == 'period' && (MakeTimeStamp($arRequest['CONDITION_period_start']) || MakeTimeStamp($arRequest['CONDITION_period_end'])))
	{
		$cond = "CSite::InPeriod(" . intval(MakeTimeStamp($arRequest['CONDITION_period_start'])) . "," . intval(MakeTimeStamp($arRequest['CONDITION_period_end'])) . ")";
	}
	elseif ($type == 'url' && $arRequest['CONDITION_url_param'] <> '' && $arRequest['CONDITION_url_value'] <> '')
	{
		$cond = '($_GET[\'' . addslashes($arRequest['CONDITION_url_param']) . '\'] ?? "") == \'' . addslashes($arRequest['CONDITION_url_value']) . '\'';
	}
	elseif ($type === 'no_access')
	{
		$cond = GetNoAccessConditionString();
	}
	elseif ($type == 'false')
	{
		$cond = 'false';
	}
	elseif ($type == 'php')
	{
		$cond = $arRequest['CONDITION_php'];
	}
	return $cond ?? null;
}

// Parse string and init vars
function ConditionParse($c = '')
{
	global $strCondition, $arGroupsNames, $arDisplay, $arConditionTypes, $strFolder, $strUrl_param, $strUrl_value, $arSelGroups, $strPer_start, $strPer_end, $CurType;

	$strCondition = $c;
	$arDisplay = [
		"empty" => "none",
		"folder" => "none",
		"ugroups" => "none",
		"period" => "none",
		"url" => "none",
		"no_access" => "none",
		"php" => "none",
		"false" => "none",
	];

	$CurType = "";
	$strFolder = "";
	$strUrl_param = "";
	$strUrl_value = "";
	$arSelGroups = [];
	$strPer_start = "";
	$strPer_end = "";

	if (preg_match('/^CSite::InDir\(\'([^)]+)\'\)$/', $c, $r))
	{
		$CurType = 'folder';
		$strFolder = stripslashes($r[1]);
	}
	elseif (preg_match('/^CSite::InGroup\(array\(([0-9, ]+)\)\)$/', $c, $r))
	{
		$CurType = 'ugroups';
		$arSelGroups = explode(",", str_replace(" ", "", $r[1]));
	}
	elseif (preg_match('/^CSite::InPeriod\(([0-9]+) *, *([0-9]+)\)$/', $c, $r))
	{
		$strPer_start = $r[1] == 0 ? "" : ConvertTimeStamp($r[1]);
		$strPer_end = $r[2] == 0 ? "" : ConvertTimeStamp($r[2]);
		$CurType = 'period';
	}
	elseif (preg_match('/^\$_GET\[\'(.+)\'] *== *\'(.+)\'$/', $c, $r) || preg_match('/^\(\$_GET\[\'(.+)\'] *\?\? *""\) *== *\'(.+)\'$/', $c, $r))
	{
		$CurType = 'url';
		$strUrl_param = stripslashes($r[1]);
		$strUrl_value = stripslashes($r[2]);
	}
	elseif ($c === GetNoAccessConditionString())
	{
		$CurType = 'no_access';
	}
	elseif ($c == 'false')
	{
		$CurType = 'false';
	}
	elseif (empty($c))
	{
		$CurType = 'empty';
	}
	else
	{
		$CurType = 'php';
	}

	$arDisplay[$CurType] = 'block';
}

function GetNoAccessConditionString()
{
	return '$GLOBALS["USER"]->HasNoAccess() && $_SERVER["REMOTE_USER"]==""';
}

// Insert select-list with condition types
function ConditionSelect($i = '')
{
	global $CurType, $arConditionTypes;

	$types_options = '';
	foreach ($arConditionTypes as $key => $val)
	{
		$types_options .= "<option value=\"{$key}\"" . ($key == $CurType ? " selected" : "") . ">{$val}</option>";
	}

	echo "<select OnChange=\"ShowSelected('$i')\" id=\"selected_type$i\" name=\"selected_type[$i]\">$types_options</select>";
}

// Show condition types
function ConditionShow($arArgs = [])
{
	global $strCondition, $APPLICATION, $arGroupsNames, $arDisplay, $arConditionTypes, $strFolder, $strUrl_param, $strUrl_value, $arSelGroups, $strPer_start, $strPer_end, $CurType, $USER;

	$i = $arArgs['i'];
	$field_name = $arArgs['field_name'];
	$form = $arArgs['form'];
	?>
	<div style="display:<?= $arDisplay['empty'] ?>" id="type_empty<?= $i ?>"><?= GetMessage("TYPES_EMPTY_COND") ?></div>
	<? if (isset($arConditionTypes['false'])):?>
	<div style="display:<?= $arDisplay['false'] ?>" id="type_false<?= $i ?>"><?= GetMessage("TYPES_FALSE_COND") ?></div>
<?endif; ?>
	<div style="display:<?= $arDisplay['folder'] ?>" value="<?= htmlspecialcharsbx($strFolder) ?>"
		 id="type_folder<?= $i ?>">
		<?
		CAdminFileDialog::ShowScript([
				"event" => "BtnClick$i",
				"arResultDest" => ["ELEMENT_ID" => "fname$i"],
				"arPath" => ["PATH" => '/'],
				"select" => 'DF', // F - file only, D - folder only,
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"saveConfig" => true,
			]
		);
		?><input title="<?= GetMessage("MAIN_PATH") ?>" type="text" size="25" id="fname<?= $i ?>"
				 name="<?= $field_name ?>[CONDITION_folder]" value="<?= htmlspecialcharsbx($strFolder) ?>">&nbsp;<input
			type="button" name="browse" value="..." onClick="BtnClick<?= $i ?>()">
	</div>
	<div style="display:<?= $arDisplay['ugroups'] ?>" id="type_ugroups<?= $i ?>">
		<select title="<?= GetMessage("MAIN_USERGROUPS"); ?>" multiple size=5
				name="<?= $field_name ?>[CONDITION_ugroups][]"><?
			foreach ($arGroupsNames as $key => $val)
			{
				echo '<option value="' . $key . '"' . (in_array($key, $arSelGroups) ? " selected" : "") . '>' . htmlspecialcharsbx($val) . '</option>';
			}
			?></select>
	</div>
	<div style="display:<?= $arDisplay['period'] ?>" id="type_period<?= $i ?>">
		<input title="<?= GetMessage("MAIN_PERIOD_FROM") ?>" type="text" size="10"
			   value="<?= htmlspecialcharsbx($strPer_start) ?>" name="<?= $field_name ?>[CONDITION_period_start]"
			   id="<?= $field_name ?>[CONDITION_period_start]">
		<?= Calendar($field_name . "[CONDITION_period_start]", $form) ?>
		-
		<input title="<?= GetMessage("MAIN_PERIOD_TO") ?>" type="text" size="10"
			   value="<?= htmlspecialcharsbx($strPer_end) ?>" name="<?= $field_name ?>[CONDITION_period_end]">
		<?= Calendar($field_name . "[CONDITION_period_end]", $form) ?>
		<span class="required"></span>
	</div>
	<div style="display:<?= $arDisplay['url'] ?>" id="type_url<?= $i ?>">
		<input title="<?= GetMessage("MAIN_URL_FIELD") ?>" type="text" size="10"
			   name="<?= $field_name ?>[CONDITION_url_param]" value="<?= htmlspecialcharsbx($strUrl_param) ?>">
		=
		<input title="<?= GetMessage("MAIN_URL_VALUE") ?>" type="text" size="10"
			   name="<?= $field_name ?>[CONDITION_url_value]" value="<?= htmlspecialcharsbx($strUrl_value) ?>">
	</div>
	<div style="display:<?= $arDisplay['no_access'] ?>"
		 id="type_no_access<?= $i ?>"><?= GetMessage("TYPES_EMPTY_COND") ?></div>
	<div style="display:<?= $arDisplay['php'] ?>" id="type_php<?= $i ?>"><input type="text" size="30"
		name="<?= $field_name ?>[CONDITION_php]"
		value="<?= htmlspecialcharsex($strCondition) ?>" <? echo((!$USER->CanDoOperation('edit_php')) ? 'disabled' : ''); ?>>
	</div>
	<?
}

// JavaScript for displaying and hiding conditions of different types
function ConditionJS($arOpt = [])
{
	global $arConditionTypes, $arGroupsNames, $USER;

	$arGroupsNames = [];
	$dbGroups = CGroup::GetList("c_sort", "asc", ["ANONYMOUS" => "N"]);
	while ($arGroups = $dbGroups->Fetch())
	{
		$arGroupsNames[$arGroups["ID"]] = $arGroups["NAME"];
	}

	$arConditionTypes = [
		"empty" => GetMessage("TYPES_EMPTY"),
		"folder" => GetMessage("TYPES_FOLDER"),
		"ugroups" => GetMessage("TYPES_UGROUPS"),
		"period" => GetMessage("TYPES_PERIOD"),
		"url" => GetMessage("TYPES_URL"),
		"no_access" => GetMessage("TYPES_NO_ACCESS"),
		"php" => GetMessage("TYPES_PHP"),
	];

	if (isset($arOpt['enable_false']) && $arOpt['enable_false'])
	{
		$arConditionTypes["false"] = GetMessage("TYPES_FALSE");
	}
	?>
	<script>
		function ShowSelected(i)
		{
			a = document.getElementById("selected_type" + i).value;
			<?
			foreach ($arConditionTypes as $key => $dummy)
			{
				print "document.getElementById('type_{$key}'+i).style.display=\"none\"\n";
			}
			?>
			document.getElementById('type_' + a + i).style.display = "block";
		}
	</script>
	<?
}
