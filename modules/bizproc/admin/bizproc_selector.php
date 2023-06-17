<?php
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

\Bitrix\Main\Loader::includeModule('bizproc');
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAuthorized())
	die('<script>alert("'.GetMessageJS("ACCESS_DENIED").'");</script>');

if (!defined('MODULE_ID') && !defined('ENTITY') && isset($_REQUEST['dts']))
{
	$dts = \CBPDocument::unSignDocumentType($_REQUEST['dts']);
	if ($dts)
	{
		define('MODULE_ID', $dts[0]);
		define('ENTITY', $dts[1]);
	}
}

CBPHelper::decodeTemplatePostData($_POST);

$documentType = array(MODULE_ID, ENTITY, $_POST['document_type']);
$documentId = !empty($_POST['document_id'])? array(MODULE_ID, ENTITY, $_POST['document_id']) : null;

try
{
	$canWrite = false;
	if ($documentId)
	{
		$canWrite = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::WriteDocument,
			$GLOBALS["USER"]->GetID(),
			$documentId
		);
	}

	if (!$canWrite)
	{
		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::WriteDocument,
			$GLOBALS["USER"]->GetID(),
			$documentType
		);
	}
}
catch (Exception $e)
{
	$canWrite = false;
}

if(!$canWrite)
{
	echo '<script>alert("'.GetMessageJS("ACCESS_DENIED").'");</script>';
	die();
}

$arWorkflowTemplate = isset($_POST['arWorkflowTemplate']) && is_array($_POST['arWorkflowTemplate'])? $_POST['arWorkflowTemplate']: array();
$arWorkflowParameters = isset($_POST['arWorkflowParameters']) && is_array($_POST['arWorkflowParameters'])? $_POST['arWorkflowParameters']: array();
$arWorkflowVariables = isset($_POST['arWorkflowVariables']) && is_array($_POST['arWorkflowVariables'])? $_POST['arWorkflowVariables']: array();
$arWorkflowConstants = isset($_POST['arWorkflowConstants']) && is_array($_POST['arWorkflowConstants'])? $_POST['arWorkflowConstants']: array();
$arGlobalConstants = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll($documentType);
$arGlobalVariables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($documentType);
$gVarVisibility =  \Bitrix\Bizproc\Workflow\Type\GlobalVar::getVisibilityFullNames($documentType);
$gConstVisibility =  \Bitrix\Bizproc\Workflow\Type\GlobalConst::getVisibilityFullNames($documentType);

$selectorMode = isset($_POST['selectorMode']) ? $_POST['selectorMode']: null;

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();

$documentService = $runtime->GetService("DocumentService");
$documentFields = $documentService->GetDocumentFields($documentType);
$documentFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

$arUsers = [];
$arAllowableUserGroups = $documentService->GetAllowableUserGroups($documentType, true);
foreach($arAllowableUserGroups as $gId=>$gName)
{
	$a = CBPHelper::extractUsersFromExtendedGroup($gId);
	if ($a === false)
	{
		$a = $documentService->GetUsersFromUserGroup($gId, $documentType);
	}
	$arUsers = array_merge($arUsers, $a);
}
$arUsers = array_values(array_unique($arUsers));

switch($_POST['fieldType'])
{
	case "int":
	case "double":
		$arFilter = ["int", "double", 'mixed', 'string'];
		break;

	case "date":
	case "datetime":
		$arFilter = ["datetime", "date", 'mixed', 'UF:date', 'string'];
		break;

	case "user":
		$arFilter = Array("user", 'mixed');
		break;

	default:
		$arFilter = false;
}
if (!empty($_REQUEST['load_access_lib']))
	CJSCore::init('access');
?>
<body class="dialogcontent">

<?
$popupWindow = new CJSPopup(GetMessage("BIZPROC_AS_TITLE"));

$popupWindow->ShowTitlebar(GetMessage("BIZPROC_AS_TITLE_TOOLBAR"));
?>
<style>
.dialogt {width:100% !important; }
.adm-workarea .dialogt option {padding: 0px;}
.bizproc-selector-optgroup {margin-left: 20px; position: relative}
/*.bizproc-selector-optgroup::before {content: '[+] '; position: absolute; top 0; left: -20px}*/
/*.bizproc-selector-optgroup.bizproc-selector-optgroup-expand::before {content: '[-] '; position: absolute; top 0; left: -20px}*/
.bizproc-selector-optgroup option {display: none}
.bizproc-selector-optgroup.bizproc-selector-optgroup-expand option {display: block}
</style>
<?
$popupWindow->StartDescription("");
?>
<?echo GetMessage("BIZPROC_SEL_TITLEBAR_DESC")?>
<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>
<script>
var BPSLastId = false;
var BPSLastResult = false;
function BPSHideShow(id)
{
	if(BPSLastId)
		document.getElementById(BPSLastId).style.display = 'none';

	if(BPSLastId==id)
		BPSLastId = false;
	else
	{
		BPSLastId = id;
		try{
			document.getElementById(BPSLastId).style.display = 'table-row';
		}catch(e){
			document.getElementById(BPSLastId).style.display = 'inline';
		}
	}
}
</script>

<table class="dialogt" cellpadding="0" cellspacing="0" border="0">
<?php if(!isset($_REQUEST['only_users']) || $_REQUEST['only_users'] !== 'Y'): ?>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId1')"><b><?echo GetMessage("BIZPROC_SEL_PARAMS_TAB")?></b></a>
		</td>
	</tr>
	<tr id="BPSId1" style="display:none">
		<td>
			<select id="BPSId1S" size="13" style="width:100%" ondblclick="BPSVInsert(this.value)">
				<?foreach($arWorkflowParameters as $fieldId => $documentField):?>
					<?if($arFilter===false || in_array($documentFieldTypes[$documentField["Type"]]["BaseType"], $arFilter)):
						if ($_POST['fieldType']=='text')
							$fieldId .= ' > printable';
						?>
						<option value="{=<?=htmlspecialcharsbx($arWorkflowTemplate[0]['Name'])?>:<?=htmlspecialcharsbx($fieldId)?>}<?if($_POST['fieldType']=='user')echo '; '?>"><?=htmlspecialcharsbx($documentField['Name'])?></option>
					<?endif?>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId3')"><b><?echo GetMessage("BP_SEL_VARS")?></b></a>
		</td>
	</tr>
	<tr id="BPSId3" style="display:none">
		<td>
			<select id="BPSId3S" size="13" style="width:100%" ondblclick="BPSVInsert(this.value)">
				<?foreach($arWorkflowVariables as $fieldId => $documentField):?>
					<?if($arFilter===false || in_array($documentFieldTypes[$documentField["Type"]]["BaseType"], $arFilter)):
						if ($_POST['fieldType']=='text')
							$fieldId .= ' > printable';
						?>
						<option value="{=Variable:<?=htmlspecialcharsbx($fieldId)?>}<?if($_POST['fieldType']=='user')echo '; '?>"><?=htmlspecialcharsbx($documentField['Name'])?></option>
					<?endif?>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId6')"><b><?echo GetMessage("BP_SEL_CONSTANTS")?></b></a>
		</td>
	</tr>
	<tr id="BPSId6" style="display:none">
		<td>
			<select id="BPSId6S" size="13" style="width:100%" ondblclick="BPSVInsert(this.value)">
				<?foreach($arWorkflowConstants as $fieldId => $documentField):?>
					<?if($arFilter===false || in_array($documentFieldTypes[$documentField["Type"]]["BaseType"], $arFilter)):
						if ($_POST['fieldType']=='text')
							$fieldId .= ' > printable';
						?>
						<option value="{=Constant:<?=htmlspecialcharsbx($fieldId)?>}<?if($_POST['fieldType']=='user')echo '; '?>"><?=htmlspecialcharsbx($documentField['Name'])?></option>
					<?endif?>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId61')"><b><?= GetMessage("BP_SEL_GCONST")?></b></a>
		</td>
	</tr>
	<tr id="BPSId61" style="display:none">
		<td>
			<select id="BPSId61S" size="13" style="width:100%" ondblclick="BPSVInsert(this.value)">
				<?php foreach($arGlobalConstants as $fieldId => $property): ?>
					<?php if($arFilter === false || in_array($documentFieldTypes[$property['Type']]['BaseType'], $arFilter)):
						$expr = sprintf('{{%s: %s}}', $gConstVisibility[$property['Visibility']], $property['Name']);
						if ($_POST['fieldType'] === 'text'):
							$fieldId .= ' > printable';
						endif ?>
						<option
							value="<?= htmlspecialcharsbx($expr) ?><?= ($_POST['fieldType'] === 'user') ? '; ' : ''?>"
						><?= htmlspecialcharsbx($property['Name']) ?></option>
					<?php endif ?>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId31')"><b><?= GetMessage('BP_SEL_GVAR') ?></b></a>
		</td>
	</tr>
	<tr id="BPSId31" style="display: none">
		<td>
			<select id="BPSId31S" size="13" style="width: 100%" ondblclick="BPSVInsert(this.value)">
				<?php foreach ($arGlobalVariables as $fieldId => $property):
					if (
						$arFilter === false
						|| (
							isset($documentFieldTypes[$property['Type']]['BaseType'])
							&& in_array($documentFieldTypes[$property['Type']]['BaseType'], $arFilter)
						)
					):
						$expr = sprintf('{{%s: %s}}', $gVarVisibility[$property['Visibility']], $property['Name']);
						if ($_POST['fieldType'] === 'text'):
							$fieldId .= ' > printable';
						endif ?>
						<option
							value="<?= htmlspecialcharsbx($expr) ?><?= ($_POST['fieldType'] === 'user') ? '; ' : ''?>"
						><?= htmlspecialcharsbx($property['Name']) ?></option>
					<?php endif?>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId2')"><b><?echo GetMessage("BIZPROC_SEL_FIELDS_TAB")?></b></a>
		</td>
	</tr>
	<tr id="BPSId2" style="display:none">
		<td>
			<select id="BPSId2S" size="13" style="width:100%" ondblclick="BPSVInsert(this.value)">
				<?foreach($documentFields as $fieldId => $documentField):?>
					<?if($arFilter===false || in_array($documentField["BaseType"], $arFilter)):
						$expr = sprintf('{{%s}}', $documentField['Name']);
					?>
						<option value="<?=htmlspecialcharsbx($expr)?>"><?=htmlspecialcharsbx($documentField['Name'])?></option>
					<?endif?>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSHideShow('BPSId4')"><b><?echo GetMessage("BP_SEL_ADDIT")?></b></a>
		</td>
	</tr>
	<tr id="BPSId4" style="display:none">
		<td>
<?
$runtime = CBPRuntime::GetRuntime();
$arAllActivities = $runtime->SearchActivitiesByType("activity", $documentType);

function _RecFindParams($act, $arFilter)
{
	global $arAllActivities;
	$result = [];
	foreach($act as $key => $value)
	{
		$value["Type"] = mb_strtolower($value["Type"]);
		if(
			isset($arAllActivities[$value["Type"]]['RETURN'])
			&& is_array($arAllActivities[$value["Type"]]['RETURN'])
			&& count($arAllActivities[$value["Type"]]['RETURN']) > 0
		)
		{
			$arResultTmp = [];
			foreach($arAllActivities[$value["Type"]]['RETURN'] as $return_name=>$return_props)
			{
				if($arFilter!==false && !in_array($return_props['TYPE'], $arFilter))
					continue;

				$arResultTmp[] = Array(
						'ID' => '{='.$value["Name"].':'.$return_name.'}',
						'NAME'	=>	'...'.$return_props['NAME'],
						'TYPE' => $return_props['TYPE'],
					);
			}

			if(count($arResultTmp)>0)
			{
				$result[] = Array(
					'ID' => $value["Name"],
					'NAME'=>$value['Properties']['Title'],
					'ITEMS' => $arResultTmp,
				);
			}
		}
		elseif(
			isset($arAllActivities[$value['Type']]['ADDITIONAL_RESULT'])
			&& is_array($arAllActivities[$value['Type']]['ADDITIONAL_RESULT'])
		)
		{
			$resultTmp = [];
			foreach($arAllActivities[$value['Type']]['ADDITIONAL_RESULT'] as $propertyKey)
			{
				if (!isset($value['Properties'][$propertyKey]) || !is_array($value['Properties'][$propertyKey]))
				{
					continue;
				}

				foreach($value['Properties'][$propertyKey] as $fieldId => $fieldData)
				{
					if($arFilter !== false && !in_array($fieldData['Type'], $arFilter))
						continue;

					$resultTmp[] = array(
						'ID' => '{='.$value['Name'].':'.$fieldId.'}',
						'NAME' => '...'.$fieldData['Name'],
						'TYPE' => $fieldData['Type'],
					);
				}
			}

			if(count($resultTmp) > 0)
			{
				$result[] = array(
					'ID' => $value['Name'],
					'NAME' => $value['Properties']['Title'],
					'ITEMS' => $resultTmp,
				);
			}
		}

		if (is_array($value["Children"]))
		{
			$result = array_merge($result, _RecFindParams($value["Children"], $arFilter));
		}
	}
	return $result;
}

$renderSelect = function($arReturns, $open = true, $title = null) use ($arFilter)
{
	static $id = 0;
	$selectId = 'BPSId4S-'.(++$id);

	if ($title):?>
		&nbsp;&nbsp;<a href="javascript:void(0)" onclick="BX.toggle(BX('<?=$selectId?>'))"><b><?=htmlspecialcharsbx($title)?></b></a></br>
	<?endif?>
			<select id="<?=$selectId?>" size="13" style="width:100%; <?=$open?'':'display:none'?>" ondblclick="BPSVInsert(this.value)">
				<?
				foreach($arReturns as $val):?>
					<optgroup label="&bull; <?=htmlspecialcharsbx($val['NAME'])?>" class="bizproc-selector-optgroup" onclick="this.classList.toggle('bizproc-selector-optgroup-expand')">
						<?foreach ($val['ITEMS'] as $item):
							if ($item['TYPE'] && $arFilter && !in_array($item['TYPE'], $arFilter))
							{
								continue;
							}
							?>
							<option value="<?=htmlspecialcharsbx($item['ID'])?>" onclick="event.stopPropagation(); BPSLastResult = this.value;">
								<?=htmlspecialcharsbx($item['NAME'])?>
							</option>
						<?endforeach;?>
					</optgroup>
				<?endforeach?>
			</select>
<?
};

	if ($arWorkflowTemplate[0]['Type'] === 'StateMachineWorkflowActivity')
	{
		foreach ($arWorkflowTemplate[0]['Children'] as $state)
		{
			if ($returns = _RecFindParams($state['Children'], $arFilter))
			{
				$renderSelect($returns, false, $state['Properties']['Title']);
			}
		}
	}
	else
	{
		$renderSelect(_RecFindParams($arWorkflowTemplate, $arFilter));
	}
?>
		</td>
	</tr>
<?endif?>
	<?if ($selectorMode != 'employee'):?>
	<tr>
		<td>
			<a href="javascript:void(0)" <?php if(!isset($_REQUEST['only_users']) || $_REQUEST['only_users'] !== "Y"):?> onclick="BPSHideShow('BPSId5')"<?endif?>><b><?echo GetMessage("BIZPROC_SEL_USERS_TAB")?></b></a>
		</td>
	</tr>
	<tr id="BPSId5" style="display:none">
		<td>
		<script>
		var prev = '';
		function BPSlookup(t)
		{
			t = t.toUpperCase();
			if(t == prev)
				return;

			prev = t;
			var ss = document.getElementById('BPSId5S');
			for(var i=0; i<ss.options.length; i++)
			{
				var o = ss.options[i];

				if(o.value)
				{
					if(o.value.toUpperCase().indexOf(t)>=0)
					{
						o.selected = true;
						break;
					}
				}
			}
		}

		function BPSKeyd(e)
		{
			var ss = document.getElementById('BPSId5S');
			if(e.keyCode == 40)
			{
				if(ss.options.selectedIndex < ss.options.length-1)
					ss.options.selectedIndex++;
				return false;
			}

			if(e.keyCode == 38)
			{
				if(ss.options.selectedIndex>0)
					ss.options.selectedIndex--;
				else
					ss.options.selectedIndex = 0;

				return false;
			}

			if(e.keyCode == 13)
			{
				if(ss.options.selectedIndex>0)
				{
					BPSVInsert(ss.options[ss.options.selectedIndex].value, true);
				}
				return false;
			}
		}
		</script>
			<input type="text" id="BPSId5I" style="width:100%" onkeyup="BPSlookup(this.value)" onkeydown="return BPSKeyd(event)">
			<select id="BPSId5S" size="<?= (isset($_REQUEST['only_users']) && $_REQUEST['only_users'] === 'Y' ? 14 : 11)?>" style="width:100%" ondblclick="BPSVInsert(this.value, true)">
				<option value="" style="background-color: #eeeeff" selected><?echo GetMessage("BIZPROC_SEL_USERS_TAB_GROUPS")?></option>
				<?foreach($arAllowableUserGroups as $groupId => $groupName):
					if ($groupName === "" || mb_strpos($groupId, 'group_u') === 0)
						continue;
					?>
					<option value="<?=htmlspecialcharsbx(str_replace(",","", $groupName))?>; "><?=htmlspecialcharsbx($groupName)?></option>
				<?endforeach?>
				<option value="" style="background-color: #eeeeff"><?echo GetMessage("BIZPROC_SEL_USERS_TAB_USERS")?></option>
				<?
				global $DB;
				$cnt = min(2000, count($arUsers));
				$mcnt = 500;
				$i = 0;
				$externalTypes = ['replica', 'email', 'imconnector', 'bot'];
				if (method_exists(\Bitrix\Main\UserTable::class, 'getExternalUserTypes'))
				{
					$externalTypes = \Bitrix\Main\UserTable::getExternalUserTypes();
				}

				while ($i < $cnt)
				{
					$str = "SELECT ID, LOGIN, NAME, LAST_NAME, SECOND_NAME, EMAIL FROM b_user WHERE ID IN (0";
					$cnt1 = min($cnt, $i + $mcnt);
					for ($j = $i; $j < $cnt1; $j++)
						$str .= ", ".intval($arUsers[$j]);
					$i += $mcnt;
					$str .= ") AND ACTIVE='Y' AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID NOT IN ('" . implode('\', \'', $externalTypes) . "')) ORDER BY LAST_NAME, EMAIL, ID";
					$dbuser = $DB->Query($str);
					while($user = $dbuser->fetch())
					{
						$n = CUser::FormatName(str_replace(",","", COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID)), $user, true, true);
						?>
						<option value="<?= $n ?> [<?=(int)$user['ID']?>]; "><?=$n?> &lt;<?=htmlspecialcharsbx($user['EMAIL'])?>&gt; [<?=(int)$user['ID']?>]</option>
						<?
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:void(0)" onclick="BPSShowUserGropupsDialog()"><b><?echo GetMessage("BIZPROC_SEL_GROUPS_TAB")?></b></a>
		</td>
	</tr>
	<?endif?>
</table>

<script>
function BPSVInsert(v, isUser)
{
	if(!v)
	{
		if(BPSLastId)
		{
			var s = document.getElementById(BPSLastId+'S');
			if(s)
			{
				v = s.value;
			}
			else if (BPSLastId === 'BPSId4')
			{
				v = BPSLastResult;
			}
		}
	}

	if(!v)
	{
		alert('<?=GetMessageJS("BIZPROC_SEL_ERR")?>');
		return;
	}
	else
	{
		var selectorMode = '<?= CUtil::JSEscape($selectorMode) ?>';

		if (selectorMode === 'employee')
		{
			v = BX.util.trim(v.replace(';', ''));
		}

		var tdocument = top.document;
		var toField = tdocument.getElementById('<?=AddSlashes(htmlspecialcharsbx($_POST["fieldName"]))?>');

		if (isUser)
		{
			var check = BX.util.trim(toField.value);
			if (check && check[check.length-1] !== ';' && check[check.length-1] !== ',')
			{
				toField.value += '; ';
			}
		}

		toField.focus();

		if (selectorMode === 'replace')
		{
			toField.value = v;
		}
		else if(tdocument.selection && tdocument.selection.createRange)
		{
			var range = tdocument.selection.createRange();
			if(range.text.length>0)
				range.text = v;
			else
				toField.value = toField.value + v;
		}
		else if(toField.selectionStart != 'undefined')
		{
			var value = toField.value;
			var pos = toField.selectionStart + v.length;
			toField.value = value.substring(0, toField.selectionStart) + v + value.substring(toField.selectionEnd);
			toField.selectionStart = pos;
			toField.selectionEnd = pos;
		}
		else
		{
			toField.value = toField.value + v;
		}
	}

	CloseDialog();
}

function CloseDialog()
{
	<?=$popupWindow->jsPopup?>.CloseDialog();
}

var BPSShowUserGropupsDialog = function()
{
	BX.Access.Init({other:{disabled:true}});
	BX.Access.ShowForm({
		bind: '<?=RandString(4);?>',
		callback: function (selected)
		{

			var prepareName = function(str)
			{
				str = str.replace(/&amp;/g, '&');
				str = str.replace(/&quot;/g, '"');
				str = str.replace(/&lt;/g, '<');
				str = str.replace(/&gt;/g, '>');
				str = str.replace(/,/g, '');
				str = str.replace(/;/g, '');

				return str;
			};

			var result = [];
			for (var provider in selected)
			{
				if (selected.hasOwnProperty(provider))
				{
					for (var varId in selected[provider])
					{
						if (selected[provider].hasOwnProperty(varId))
						{
							var id = varId;
							if (id.indexOf('U') === 0)
								id = id.substr(1);
							if (id.indexOf('IU') === 0)
								id = id.substr(2);
							result.push(prepareName(selected[provider][varId].name) + ' [' + id + ']');
						}
					}
				}
			}
			if (result)
			{
				BPSVInsert(result.join('; ')+'; ', true);
			}
		}
	});
};

<?if($_POST['fieldType']=='user' && $selectorMode != 'employee'):?>
BPSHideShow('BPSId5');
try{
document.getElementById('BPSId5I').focus();
}catch(e)
{}
<?else:?>
BPSHideShow('BPSId2');
try{
document.getElementById('BPSId2S').focus();
}catch(e)
{}
<?endif?>
</script>
<?
$popupWindow->EndContent();
$popupWindow->StartButtons();
?>
<input type="button" value="<?=GetMessage("BIZPROC_SEL_INSERT")?>" onclick="BPSVInsert();" />
<?
$popupWindow->ShowStandardButtons(array('cancel'));
?>
<?$popupWindow->EndButtons();?>
</body>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>
