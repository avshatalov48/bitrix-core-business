<?php
define('ADMIN_MODULE_NAME', 'clouds');

/*.require_module 'standard';.*/
/*.require_module 'hash';.*/
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;

if (!$USER->CanDoOperation('clouds_config'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/*.require_module 'bitrix_clouds_include';.*/
if (!CModule::IncludeModule('clouds'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

IncludeModuleLangFile(__FILE__);

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('CLO_STORAGE_EDIT_TAB'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('CLO_STORAGE_EDIT_TAB_TITLE'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('CLO_STORAGE_EDIT_TAB2'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('CLO_STORAGE_EDIT_TAB2_TITLE'),
	],
];
if (CCloudFailover::IsEnabled())
{
	$aTabs[] = [
		'DIV' => 'edit3',
		'TAB' => GetMessage('CLO_STORAGE_EDIT_TAB3'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('CLO_STORAGE_EDIT_TAB3_TITLE'),
	];
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

$ID = intval($_REQUEST['ID'] ?? 0); // Id of the edited record
$bVarsFromForm = false;
$message = /*.(CAdminMessage).*/null;

$FAILOVER_DELETE_DELAY = intval($_REQUEST['FAILOVER_DELETE_DELAY'] ?? 0);
if (isset($_POST['FAILOVER_DELETE_DELAY_TYPE']))
{
	if ($_POST['FAILOVER_DELETE_DELAY_TYPE'] == 'H')
	{
		$FAILOVER_DELETE_DELAY *= 60;
	}
	elseif ($_POST['FAILOVER_DELETE_DELAY_TYPE'] == 'D')
	{
		$FAILOVER_DELETE_DELAY *= 60 * 24;
	}
	elseif ($_POST['FAILOVER_DELETE_DELAY_TYPE'] == 'W')
	{
		$FAILOVER_DELETE_DELAY *= 60 * 24 * 7;
	}
	elseif ($_POST['FAILOVER_DELETE_DELAY_TYPE'] == 'N')
	{
		$FAILOVER_DELETE_DELAY *= 60 * 24 * 30;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid())
{
	if (isset($_REQUEST['save']) || isset($_REQUEST['apply']))
	{
		$ob = new CCloudStorageBucket($ID);
		$arFields = [
			'ACTIVE' => $_POST['ACTIVE'] === 'Y' ? 'Y' : 'N',
			'READ_ONLY' => $_POST['READ_ONLY'] === 'Y' ? 'Y' : 'N',
			'SORT' => $_POST['SORT'],
			'SERVICE_ID' => $_POST['SERVICE_ID'],
			'BUCKET' => $_POST['BUCKET'],
			'LOCATION' => $_POST['LOCATION'][$_POST['SERVICE_ID']],
			'SETTINGS' => $_POST['SETTINGS'][$_POST['SERVICE_ID']],
			'CNAME' => $_POST['CNAME'],
			'FILE_RULES' => CCloudStorageBucket::ConvertPOST($_POST),
		];
		if (CCloudFailover::IsEnabled())
		{
			$arFields['FAILOVER_ACTIVE'] = $_POST['FAILOVER_ACTIVE'] == 'Y' ? 'Y' : 'N';
			$arFields['FAILOVER_BUCKET_ID'] = (int)$_POST['FAILOVER_BUCKET_ID'];
			$arFields['FAILOVER_COPY'] = $_POST['FAILOVER_COPY'] == 'Y' ? 'Y' : 'N';
			$arFields['FAILOVER_DELETE'] = $_POST['FAILOVER_DELETE'] == 'Y' ? 'Y' : 'N';
			$arFields['FAILOVER_DELETE_DELAY'] = $FAILOVER_DELETE_DELAY;
		}

		if ($ID > 0)
		{
			$res = $ob->Update($arFields);
		}
		else
		{
			$res = $ob->Add($arFields);
		}

		if (
			$res > 0
			&& CCloudFailover::IsEnabled()
			&& $arFields['FAILOVER_BUCKET_ID'] > 0
			&& $_POST['FAILOVER_SYNC'] == 'Y'
		)
		{
			CAgent::AddAgent(
				'CCloudFailover::syncAgent(' . $res . ', ' . $arFields['FAILOVER_BUCKET_ID'] . ', 100);',
				'clouds', 'N', 1, '', 'Y', ''
			);
		}

		if ($res > 0)
		{
			if (isset($_REQUEST['apply']))
			{
				LocalRedirect('/bitrix/admin/clouds_storage_edit.php?ID=' . $res . '&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
			}
			else
			{
				LocalRedirect('/bitrix/admin/clouds_storage_list.php?lang=' . LANGUAGE_ID);
			}
		}
		else
		{
			$e = $APPLICATION->GetException();
			if (is_object($e))
			{
				$message = new CAdminMessage(GetMessage('CLO_STORAGE_EDIT_SAVE_ERROR'), $e);
			}
			$bVarsFromForm = true;
		}
	}
	elseif (isset($_REQUEST['delete']) && $ID > 1)
	{
		$ob = new CCloudStorageBucket($ID);
		if ($ob->Delete())
		{
			LocalRedirect('/bitrix/admin/clouds_storage_list.php?lang=' . LANGUAGE_ID);
		}
		else
		{
			$bVarsFromForm = true;
		}
	}
}

if ($bVarsFromForm)
{
	$arRes = [
		'ACTIVE' => (string)$_REQUEST['ACTIVE'],
		'SORT' => (int)$_POST['SORT'],
		'READ_ONLY' => (string)$_REQUEST['READ_ONLY'],
		'SERVICE_ID' => (string)$_REQUEST['SERVICE_ID'],
		'BUCKET' => (string)$_REQUEST['BUCKET'],
		'LOCATION' => (string)$_POST['LOCATION'][$_POST['SERVICE_ID']],
		'CNAME' => (string)$_REQUEST['CNAME'],
		'SETTINGS' => '',
		'FAILOVER_ACTIVE' => (string)$_REQUEST['FAILOVER_ACTIVE'],
		'FAILOVER_BUCKET_ID' => (int)$_REQUEST['FAILOVER_BUCKET_ID'],
		'FAILOVER_COPY' => (string)$_REQUEST['FAILOVER_COPY'],
		'FAILOVER_DELETE' => (string)$_REQUEST['FAILOVER_DELETE'],
		'FAILOVER_DELETE_DELAY' => (int)$FAILOVER_DELETE_DELAY,
	];

	if (isset($_REQUEST['SETTINGS']) && is_array($_REQUEST['SETTINGS']))
	{
		$arRes['SETTINGS'] = $_REQUEST['SETTINGS'];
	}
}
else
{
	$arRes = null;
	if ($ID > 0)
	{
		$rs = CCloudStorageBucket::GetList(['ID' => 'ASC'], ['=ID' => $ID]);
		$arRes = $rs->Fetch();
	}

	if (!is_array($arRes))
	{
		$ID = 0;
		$arRes = [
			'ACTIVE' => 'Y',
			'SORT' => '500',
			'READ_ONLY' => 'N',
			'SERVICE_ID' => '',
			'BUCKET' => 'upload-' . md5(uniqid('', true)),
			'LOCATION' => '',
			'CNAME' => '',
			'SETTINGS' => '',
			'FAILOVER_ACTIVE' => 'N',
			'FAILOVER_BUCKET_ID' => 0,
			'FAILOVER_COPY' => 'N',
			'FAILOVER_DELETE' => 'N',
			'FAILOVER_DELETE_DELAY' => 0,
		];
	}
}

$APPLICATION->SetTitle(($ID > 0 ? GetMessage('CLO_STORAGE_EDIT_EDIT_TITLE') : GetMessage('CLO_STORAGE_EDIT_ADD_TITLE')));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'TEXT' => GetMessage('CLO_STORAGE_EDIT_MENU_LIST'),
		'TITLE' => GetMessage('CLO_STORAGE_EDIT_MENU_LIST_TITLE'),
		'LINK' => 'clouds_storage_list.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_list',
	]
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

if (is_object($message))
{
	echo $message->Show();
}
?>
<script>
function ChangeLocation(select)
{
	var trs;
	trs = BX.findChildren(BX('editform'), {'tag':'tr','class':'location-tr'}, true);
	for(var i = 0;i < trs.length; i++)
		trs[i].style.display = 'none';

	trs = BX.findChildren(BX('editform'), {'tag':'tr','class':'settings-tr'}, true);
	for(var i = 0;i < trs.length; i++)
		trs[i].style.display = 'none';

	BX('LOCATION_' + select.value).style.display = '';

	var i = 0;
	while(true)
	{
		var tr = BX('SETTINGS_' + i + '_' + select.value);
		if(tr)
		{
			tr.style.display = '';
		}
		else
		{
			break;
		}
		i++;
	}
}

function editAddRule()
{
	var tbl = BX('tblRULES');
	var oRow = tbl.insertRow(-1);
	var oCell1 = oRow.insertCell(0); oCell1.innerHTML = '<input name="MODULE[]" type="text" style="width:100%">';
	var oCell2 = oRow.insertCell(1); oCell2.innerHTML = '<input name="EXTENSION[]" type="text" style="width:100%">';
	var oCell3 = oRow.insertCell(2); oCell3.innerHTML = '<input name="SIZE[]" type="text" style="width:100%">';
	var oCell4 = oRow.insertCell(3); oCell4.innerHTML = '<img src="/bitrix/themes/.default/images/actions/delete_button.gif" onclick="editDeleteRule(this) "/>';

	if (document.forms.editform.BXAUTOSAVE)
	{
		setTimeout(function() {
			var r = BX.findChildren(oRow, {tag: 'input'}, true);
			if (r && r.length > 0)
			{
				for (var i=0,l=r.length;i<l;i++)
				{
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				}
			}
		}, 10);
	}
}

function editDeleteRule(img)
{
	var oTR = BX.findParent(img, {tag: 'tr'});
	oTR.parentNode.removeChild(oTR);
}
</script>
<form method="POST" action="<?php echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="editform" id="editform">
<?php
$tabControl->Begin();
?>
<?php
$tabControl->BeginNextTab();
?>
	<?php if ($ID > 0)
	{
?>
		<tr>
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_ID')?>:</td>
			<td><?php echo $ID;?></td>
		</tr>
	<?php }?>
	<tr>
		<td width="40%"><?php echo GetMessage('CLO_STORAGE_EDIT_ACTIVE')?>:</td>
		<td width="60%"><input type="hidden" name="ACTIVE" value="N"><input type="checkbox" name="ACTIVE" value="Y"<?php echo $arRes['ACTIVE'] === 'Y' ? ' checked' : ''?>></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('CLO_STORAGE_EDIT_SORT')?>:</td>
		<td><input type="text" size="6" name="SORT" value="<?php echo intval($arRes['SORT'])?>"></td>
	</tr>
	<?php
	if ($ID > 0)
	{
	?>
		<tr>
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_SERVICE_ID')?>:</td>
			<td>
			<?php
			/* @var $obService CCloudStorageService */
			foreach (CCloudStorage::GetServiceList() as $SERVICE_ID => $obService)
			{
				if ($arRes['SERVICE_ID'] === $SERVICE_ID)
				{
					echo htmlspecialcharsEx($obService->GetName());
					break;
				}
			}
			?>
			<input type="hidden" name="SERVICE_ID" value="<?php echo htmlspecialcharsbx($arRes['SERVICE_ID']);?>">
			</td>
		</tr>
		<tr id="LOCATION_<?php echo htmlspecialcharsbx($arRes['SERVICE_ID'])?>" class="location-tr">
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_LOCATION')?>:</td>
			<td>
			<?php
			$locationList = CCloudStorage::GetServiceLocationList($arRes['SERVICE_ID']);
			if (is_array($locationList))
			{
				foreach ($locationList as $LOCATION_ID => $LOCATION_NAME)
				{
					if ($arRes['LOCATION'] == $LOCATION_ID)
					{
						echo htmlspecialcharsEx($LOCATION_NAME);
					}
				}
			}
			?>
			<input type="hidden" name="LOCATION[<?php echo htmlspecialcharsbx($arRes['SERVICE_ID']);?>]" value="<?php echo htmlspecialcharsbx($arRes['LOCATION']);?>">
			</td>
		</tr>
		<?php
		if (is_object($obService))
		{
			echo $obService->GetSettingsHTML($arRes, true, $arRes['SERVICE_ID'], $bVarsFromForm);
		}
	}
	else
	{
	?>
		<tr>
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_SERVICE_ID')?>:</td>
			<td>
			<select name="SERVICE_ID" onchange="ChangeLocation(this)">
			<?php
			$bServiceSet = false;
			/* @var $obService CCloudStorageService */
			foreach (CCloudStorage::GetServiceList() as $SERVICE_ID => $obService)
			{
				?><option value="<?php echo htmlspecialcharsbx($SERVICE_ID)?>"<?php echo $arRes['SERVICE_ID'] === $SERVICE_ID ? ' selected' : ''?>><?php echo htmlspecialcharsEx($obService->GetName())?></option><?php
				if ($arRes['SERVICE_ID'] === $SERVICE_ID)
				{
					$bServiceSet = true;
				}
			}
			?>
			</select>
			</td>
		</tr>
		<?php
		/* @var $obService CCloudStorageService */
		foreach (CCloudStorage::GetServiceList() as $SERVICE_ID => $obService)
		{
		?>
		<tr id="LOCATION_<?php echo htmlspecialcharsbx($SERVICE_ID)?>" style="display:<?php echo $arRes['SERVICE_ID'] === $SERVICE_ID || !$bServiceSet ? '' : 'none'?>" class="location-tr">
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_LOCATION')?>:</td>
			<td>
			<?php
			$locationList = CCloudStorage::GetServiceLocationList($SERVICE_ID);
			if (is_array($locationList))
			{
			?>
			<select name="LOCATION[<?php echo htmlspecialcharsbx($SERVICE_ID)?>]">
			<?php
			foreach (CCloudStorage::GetServiceLocationList($SERVICE_ID) as $LOCATION_ID => $LOCATION_NAME)
			{
				?><option value="<?php echo htmlspecialcharsbx($LOCATION_ID)?>"<?php echo $arRes['LOCATION'] === $LOCATION_ID ? ' selected' : ''?>><?php echo htmlspecialcharsEx($LOCATION_NAME)?></option><?php
			}
			?>
			</select>
			<?php
			}
			else
			{
			?>
			<input type="text" name="LOCATION[<?php echo htmlspecialcharsbx($SERVICE_ID)?>]" value="<?php echo htmlspecialcharsbx($arRes['LOCATION'])?>">
			<?php
			}
			?>
			</td>
		</tr>
		<?php
			echo $obService->GetSettingsHTML($arRes, $bServiceSet, $arRes['SERVICE_ID'], $bVarsFromForm);
			$bServiceSet = true;
		}
	}

	if ($ID > 0)
	{
	?>
		<tr class="adm-detail-required-field">
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_BUCKET')?>:</td>
			<td><input type="hidden" name="BUCKET" value="<?php echo htmlspecialcharsbx($arRes['BUCKET'])?>"><?php echo htmlspecialcharsEx($arRes['BUCKET'])?></td>
		</tr>
	<?php
	}
	else
	{
?>
		<tr class="adm-detail-required-field">
			<td><?php echo GetMessage('CLO_STORAGE_EDIT_BUCKET')?>:</td>
			<td><input type="text" size="55" name="BUCKET" value="<?php echo htmlspecialcharsbx($arRes['BUCKET'])?>"></td>
		</tr>
	<?php }?>
	<tr>
		<td><?php echo GetMessage('CLO_STORAGE_EDIT_READ_ONLY')?>:</td>
		<td><input type="hidden" name="READ_ONLY" value="N"><input type="checkbox" name="READ_ONLY" value="Y"<?php echo $arRes['READ_ONLY'] === 'Y' ? ' checked' : ''?>></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('CLO_STORAGE_EDIT_CNAME')?>:</td>
		<td><input type="text" size="55" name="CNAME" value="<?php echo htmlspecialcharsbx($arRes['CNAME'])?>"></td>
	</tr>
<?php
$tabControl->BeginNextTab();
?>
	<tr><td align="center">
<?php
if ($bVarsFromForm)
{
	$arRules = CCloudStorageBucket::ConvertPOST($_POST);
}
elseif (isset($arRes['FILE_RULES']))
{
	$arRules = unserialize($arRes['FILE_RULES'], ['allowed_classes' => false]);
}
else
{
	$arRules = [];
}

if (!is_array($arRules))
{
	$arRules = [];
}
?>
		<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center" id="tblRULES">
			<tr class="heading">
				<td><?php echo GetMessage('CLO_STORAGE_EDIT_MODULE')?><sup><span class="required">1</span></sup></td>
				<td><?php echo GetMessage('CLO_STORAGE_EDIT_EXTENSIONS')?><sup><span class="required">2</span></sup></td>
				<td><?php echo GetMessage('CLO_STORAGE_EDIT_SIZE')?><sup><span class="required">3</span></sup></td>
				<td>&nbsp;</td>
			</tr>
	<?php
	$ii = 0;
	foreach ($arRules as $rule)
	{
	?>
			<tr>
				<td><input name="MODULE[]" type="text" value="<?php echo htmlspecialcharsbx($rule['MODULE'])?>" style="width:100%"></td>
				<td><input name="EXTENSION[]" type="text" value="<?php echo htmlspecialcharsbx($rule['EXTENSION'] ?? '')?>" style="width:100%"></td>
				<td><input name="SIZE[]" type="text" value="<?php echo htmlspecialcharsbx($rule['SIZE'] ?? '')?>" style="width:100%"></td>
				<td><img src="/bitrix/themes/.default/images/actions/delete_button.gif" onclick="editDeleteRule(this)" /></td>
			</tr>
	<?php
		$ii++;
	}
	if ($ii == 0 && $ID == 0)
	{
		?>
			<tr>
				<td><input name="MODULE[]" type="text" style="width:100%"></td>
				<td><input name="EXTENSION[]" type="text" style="width:100%"></td>
				<td><input name="SIZE[]" type="text" style="width:100%"></td>
				<td><img src="/bitrix/themes/.default/images/actions/delete_button.gif" onclick="editDeleteRule(this)" /></td>
			</tr>
		<?php
		$ii = 1;
	}
?>
		</table>
<script>
BX.ready(function() {
	BX.addCustomEvent(document.forms.editform, 'onAutoSaveRestore', function(ob, data)
	{
		if (data['MODULE[]'] && BX.type.isArray(data['MODULE[]']) && data['MODULE[]'].length > <?=$ii?>)
		{
			for (var i=<?=$ii?>; i<data['MODULE[]'].length; i++)
				editAddRule();
		}
	});
});
</script><br>
		<a class="adm-btn" href="javascript:void(0)" onclick="editAddRule(this)" hidefocus="true" class="bx-action-href"><?php echo GetMessage('CLO_STORAGE_EDIT_ADD_FILE_RULE')?></a>
	</td></tr>
	<tr><td>
		<?php echo
			BeginNote(),
			'<p>',GetMessage('CLO_STORAGE_EDIT_RULES_NOTE'),'</p>',
			'<span class="required">1</span> - ',GetMessage('CLO_STORAGE_EDIT_RULES_NOTE1'),'<br />',
			'<span class="required">2</span> - ',GetMessage('CLO_STORAGE_EDIT_RULES_NOTE2'),'<br />',
			'<span class="required">3</span> - ',GetMessage('CLO_STORAGE_EDIT_RULES_NOTE3'),'<br />',
			EndNote();
		?>
	</td></tr>
<?php
if (CCloudFailover::IsEnabled())
{
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_ACTIVE')?>:</td>
		<td width="60%">
			<input type="hidden" name="FAILOVER_ACTIVE" value="N">
			<input type="checkbox" name="FAILOVER_ACTIVE" value="Y"<?php echo $arRes['FAILOVER_ACTIVE'] === 'Y' ? ' checked' : ''?>>
		</td>
	</tr>
	<tr valign="top">
		<td><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_BUCKET_ID')?>:</td>
		<td>
		<select name="FAILOVER_BUCKET_ID">
			<option value=""><?php echo GetMessage('MAIN_NO')?></option>
			<?php
			$rsBucketList = CCloudStorageBucket::GetList(['SORT' => 'DESC', 'ID' => 'ASC']);
			while ($arBucket = $rsBucketList->Fetch())
			{
				if ($ID == $arBucket['ID'])
				{
					continue;
				}
				?><option value="<?php echo htmlspecialcharsbx($arBucket['ID'])?>"<?php echo $arRes['FAILOVER_BUCKET_ID'] === $arBucket['ID'] ? ' selected' : ''?>><?php echo htmlspecialcharsEx($arBucket['BUCKET'])?></option><?php
			}
			?>
		</select>
		<?php echo
			BeginNote(),
			'<p>',GetMessage('CLO_STORAGE_EDIT_FAILOVER_NOTE'),'</p>',
			EndNote();
		?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_COPY')?>:</td>
		<td width="60%">
			<input type="hidden" name="FAILOVER_COPY" value="N">
			<input type="checkbox" name="FAILOVER_COPY" value="Y"<?php echo $arRes['FAILOVER_COPY'] === 'Y' ? ' checked' : ''?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE')?>:</td>
		<td width="60%">
			<input type="hidden" name="FAILOVER_DELETE" value="N">
			<input type="checkbox" name="FAILOVER_DELETE" value="Y"<?php echo $arRes['FAILOVER_DELETE'] === 'Y' ? ' checked' : ''?>>
		</td>
	</tr>
	<tr>
		<td><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY')?>:</td>
		<td>
		<?php
			$FAILOVER_DELETE_DELAY = intval($arRes['FAILOVER_DELETE_DELAY']);
			if ($FAILOVER_DELETE_DELAY % (60 * 24 * 30) == 0)
			{
				$FAILOVER_DELETE_DELAY_TYPE = 'N';
				$FAILOVER_DELETE_DELAY /= (60 * 24 * 30);
			}
			elseif ($FAILOVER_DELETE_DELAY % (60 * 24 * 7) == 0)
			{
				$FAILOVER_DELETE_DELAY_TYPE = 'W';
				$FAILOVER_DELETE_DELAY /= (60 * 24 * 7);
			}
			elseif ($FAILOVER_DELETE_DELAY % (60 * 24) == 0)
			{
				$FAILOVER_DELETE_DELAY_TYPE = 'D';
				$FAILOVER_DELETE_DELAY /= (60 * 24);
			}
			elseif ($FAILOVER_DELETE_DELAY % 60 == 0)
			{
				$FAILOVER_DELETE_DELAY_TYPE = 'H';
				$FAILOVER_DELETE_DELAY /= (60);
			}
			else
			{
				$FAILOVER_DELETE_DELAY_TYPE = 'M';
			}
			?>
			<input type="text" name="FAILOVER_DELETE_DELAY" id="FAILOVER_DELETE_DELAY" size="5" value="<?php echo $FAILOVER_DELETE_DELAY ?>">
			<select name="FAILOVER_DELETE_DELAY_TYPE" title="">
				<option value="M"<?php echo $FAILOVER_DELETE_DELAY_TYPE === 'M' ? ' selected' : ''?>><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY_MI') ?></option>
				<option value="H"<?php echo $FAILOVER_DELETE_DELAY_TYPE === 'H' ? ' selected' : ''?>><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY_HO') ?></option>
				<option value="D"<?php echo $FAILOVER_DELETE_DELAY_TYPE === 'D' ? ' selected' : ''?>><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY_DA') ?></option>
				<option value="W"<?php echo $FAILOVER_DELETE_DELAY_TYPE === 'W' ? ' selected' : ''?>><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY_WE') ?></option>
				<option value="N"<?php echo $FAILOVER_DELETE_DELAY_TYPE === 'N' ? ' selected' : ''?>><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_DELETE_DELAY_MO') ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?php echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_SYNC')?>:</td>
		<td width="60%">
			<?php
			$rsAgents = CAgent::GetList(['ID' => 'DESC'], [
				'MODULE_ID' => 'clouds',
				'NAME' => 'CCloudFailover::syncAgent(' . $ID . ', %',
			]);
			$arAgent = $rsAgents->Fetch();
			if (!$arAgent)
			{
				$task = \Bitrix\Clouds\CopyQueueTable::getList([
					'filter' => [
						'=STATUS' => 'Y',
						'=OP' => \Bitrix\Clouds\CopyQueueTable::OP_SYNC,
					],
					'limit' => 1,
					'order' => ['ID' => 'ASC']
				])->fetch();
			}
			else
			{
				$task = false;
			}

			if ($arAgent || $task)
			{
				echo GetMessage('CLO_STORAGE_EDIT_FAILOVER_SYNC_IN_PROGRESS');
			}
			else
			{
			?>
				<input type="hidden" name="FAILOVER_SYNC" value="N">
				<input type="checkbox" name="FAILOVER_SYNC" value="Y">
			<?php
			}
			?>
		</td>
	</tr>
	<?php
}

$tabControl->Buttons(
	[
		'back_url' => 'clouds_storage_list.php?lang=' . LANGUAGE_ID,
	]
);
?>
<?php echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
<input type="hidden" name="ID" value="<?php echo $ID?>">
<?php
$tabControl->End();
?>
</form>

<?php
$tabControl->ShowWarnings('editform', $message);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
