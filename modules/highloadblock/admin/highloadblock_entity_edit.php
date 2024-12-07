<?php

use Bitrix\Highloadblock as HL;

const ADMIN_MODULE_NAME = 'highloadblock';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

/** @global \CUser $USER */
/** @global \CMain $APPLICATION */

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile(__DIR__.'/highloadblock_rows_list.php');

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// form
$aTabs = array(
	array(
		'DIV' => 'edit1',
		'TAB' => GetMessage('HLBLOCK_ADMIN_ENTITY_TITLE'),
		'TITLE' => GetMessage('HLBLOCK_ADMIN_ENTITY_TITLE')
	),
	array(
		'DIV' => 'edit2',
		'TAB' => GetMessage('HLBLOCK_ADMIN_ENTITY_RIGHTS'),
		'TITLE' => GetMessage('HLBLOCK_ADMIN_ENTITY_RIGHTS')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

// init vars
$is_create_form = true;
$is_update_form = false;
$isEditMode = true;
$errors = array();
$localization = array();
$currentRights = array();
$currentRightsName = array();
$access = new \CAccess;
$ID = (int)$request->get('ID');
$save = trim((string)$request->get('save'));
$apply = trim((string)$request->get('apply'));
$action = trim((string)$request->get('action'));
$requestMethod = $request->getRequestMethod();

// get highloadblock data
if ($ID > 0)
{
	$filter = array(
		'select' => array('ID', 'NAME', 'TABLE_NAME', 'FIELDS_COUNT'),
		'filter' => array('=ID' => $ID)
	);
	$hlblock = HL\HighloadBlockTable::getList($filter)->fetch();

	if (!empty($hlblock))
	{
		$is_update_form = true;
		$is_create_form = false;
	}

	// localization
	$res = HL\HighloadBlockLangTable::getList(array(
		'filter' => array('ID' => $ID)
	));
	while ($row = $res->fetch())
	{
		$localization[$row['LID']] = array(
			'ID' => $row['ID'],
			'NAME' => $row['NAME']
		);
	}
}

// get langs
$langs = array();
$res = \CLanguage::GetList();
while($row = $res->getNext())
{
	$langs[] = $row;
}

// current access
$accessCodes = array();
if ($ID)
{
	$res = HL\HighloadBlockRightsTable::getList(array(
		'filter' => array(
			'HL_ID' => $ID
		)
	));
	while ($row = $res->fetch())
	{
		$currentRights[$row['ID']] = array(
			'ACCESS_CODE' => $row['ACCESS_CODE'],
			'TASK_ID' => $row['TASK_ID']
		);
		$accessCodes[] = $row['ACCESS_CODE'];
	}
	$currentRightsName = $access->GetNames($accessCodes);
}

// rights
$tasks = array();
$tasksStr = '<select name="RIGHTS[TASK_ID][]">';
$res = \CTask::GetList(array('LETTER' => 'ASC'), array('MODULE_ID' => ADMIN_MODULE_NAME));
while ($row = $res->getNext())
{
	$tasks[$row['ID']] = $row['TITLE'];
	$tasksStr .= '<option value="'.$row['ID'].'">'.$row['TITLE'].'</option>';
}
$tasksStr .= '</select>';

// default values for create form / page title
if ($is_create_form)
{
	$hlblock = array_fill_keys(array('ID', 'NAME', 'TABLE_NAME'), '');
	$APPLICATION->SetTitle(GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_PAGE_TITLE_NEW'));
}
else
{
	$APPLICATION->SetTitle(GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_PAGE_TITLE_EDIT', array('#NAME#' => $hlblock['NAME'])));

	$entity = HL\HighloadBlockTable::compileEntity($hlblock);

	$entity_data_class = $entity->getDataClass();
	$entity_table_name = $hlblock['TABLE_NAME'];

	$hlblock['ROWS_COUNT'] = $entity_data_class::getCount();
}

// delete action
if ($is_update_form && $action === 'delete' && check_bitrix_sessid())
{
	$result = HL\HighloadBlockTable::delete($hlblock['ID']);
	if ($result->isSuccess())
	{
		\LocalRedirect('highloadblock_index.php?lang='.LANGUAGE_ID);
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}

// save action
if (($save != '' || $apply != '') && $requestMethod == 'POST' && check_bitrix_sessid())
{
	$data = array(
		'NAME' => trim($request->get('NAME')),
		'TABLE_NAME' => trim($request->get('TABLE_NAME'))
	);

	if ($is_update_form)
	{
		$result = HL\HighloadBlockTable::update($ID, $data);
	}
	else
	{
		$result = HL\HighloadBlockTable::add($data);
		$ID = $result->getId();
	}

	if ($result->isSuccess())
	{
		// localization
		foreach ($localization as $lid => $loc)
		{
			HL\HighloadBlockLangTable::delete([
				'ID' => $loc['ID'],
				'LID' => $lid,
			]);
		}
		if (is_array($request->get('LANGS')))
		{
			foreach ($request->get('LANGS') as $lng => $val)
			{
				if (trim($val) != '')
				{
					HL\HighloadBlockLangTable::add(array(
						'ID' => $ID,
						'LID' => $lng,
						'NAME' => $val
					));
				}
			}
		}

		// rights
		$notUpdated = $currentRights;
		if (is_array($request->get('RIGHTS')))
		{
			$rights = $request->get('RIGHTS');
			if (
				isset($rights['RIGHT_ID']) && is_array($rights['RIGHT_ID']) &&
				isset($rights['ACCESS_CODE']) && is_array($rights['ACCESS_CODE']) &&
				isset($rights['TASK_ID']) && is_array($rights['TASK_ID'])
			)
			{
				foreach ($rights['RIGHT_ID'] as $k => $rid)
				{
					if
						(
							isset($rights['ACCESS_CODE'][$k]) &&
							isset($rights['TASK_ID'][$k])
						)
					{
						// update
						if ($rid > 0  && isset($currentRights[$rid]))
						{
							unset($notUpdated[$rid]);
							HL\HighloadBlockRightsTable::update($rid, array(
								'ACCESS_CODE' => $rights['ACCESS_CODE'][$k],
								'TASK_ID' => $rights['TASK_ID'][$k]
							));
						}
						// add
						else
						{
							HL\HighloadBlockRightsTable::add(array(
								'HL_ID' => $ID,
								'ACCESS_CODE' => $rights['ACCESS_CODE'][$k],
								'TASK_ID' => $rights['TASK_ID'][$k]
							));
						}
					}
				}
			}
		}
		// delete
		if (!empty($notUpdated))
		{
			foreach (array_keys($notUpdated) as $rid)
			{
				HL\HighloadBlockRightsTable::delete($rid);
			}
		}

		if ($save != '')
		{
			\LocalRedirect('highloadblock_index.php?lang='.LANGUAGE_ID);
		}
		else
		{
			\LocalRedirect('highloadblock_entity_edit.php?ID='.$ID.'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam());
		}
	}
	else
	{
		$errors = $result->getErrorMessages();
	}

	// rewrite original value by form value to restore form
	foreach ($data as $k => $v)
	{
		$hlblock[$k] = $v;
	}
	if (is_array($request->get('LANGS')))
	{
		foreach ($request->get('LANGS') as $lng => $val)
		{
			$localization[$lng] = array(
				'NAME' => $val
			);
		}
	}
	if (is_array($request->get('RIGHTS')))
	{
		$rights = $request->get('RIGHTS');
		if (
			isset($rights['RIGHT_ID']) && is_array($rights['RIGHT_ID']) &&
			isset($rights['ACCESS_CODE']) && is_array($rights['ACCESS_CODE']) &&
			isset($rights['TASK_ID']) && is_array($rights['TASK_ID'])
		)
		{
			foreach ($rights['RIGHT_ID'] as $k => $rid)
			{
				$currentRights[$rid > 0 ? $rid : 'n'.$k] = array(
					'ACCESS_CODE' => $rights['ACCESS_CODE'][$k],
					'TASK_ID' => $rights['TASK_ID'][$k]
				);
				$accessCodes[] = $rights['ACCESS_CODE'][$k];
			}
			$currentRightsName += $access->GetNames($accessCodes);
		}
	}
}

// view
if ($request->get('mode') == 'list')
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');
}
else
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
}
// menu
$aMenu = array(
	array(
		'TEXT'	=> GetMessage('HLBLOCK_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'TITLE'	=> GetMessage('HLBLOCK_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'LINK'	=> 'highloadblock_index.php?lang='.LANGUAGE_ID,
		'ICON'	=> 'btn_list',
	)
);
$adminContextMenu = new CAdminContextMenu($aMenu);
$adminContextMenu->Show();


if (!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="ID" value="<?= htmlspecialcharsbx($hlblock['ID'])?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><strong><?= GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_NAME_FIELD')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($hlblock['NAME'])?><?
			else:
				?><input type="text" name="NAME" size="30" value="<?= htmlspecialcharsbx($hlblock['NAME'])?>"><?
			endif;
		?></td>
	</tr>
	<tr>
		<td><strong><?=GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_TABLE_NAME')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($hlblock['TABLE_NAME'])?><?
			else:
				?><input type="text" name="TABLE_NAME" size="30" value="<?=htmlspecialcharsbx($hlblock['TABLE_NAME'])?>"><?
			endif;
			?></td>
	</tr>
	<?if ($is_update_form):?>
		<tr>
			<td><?=GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_FIELDS_COUNT')?></td>
			<td><a href="userfield_admin.php?lang=<?=LANGUAGE_ID?>&amp;set_filter=Y&amp;find=HLBLOCK_<?=intval($hlblock['ID'])?>&amp;find_type=ENTITY_ID&amp;back_url=<?=urlencode($APPLICATION->GetCurPageParam())?>">[<?= intval($hlblock['FIELDS_COUNT'])?>]</a></td>
		</tr>
		<tr>
			<td><?=GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_ROWS_COUNT')?></td>
			<td><a href="highloadblock_rows_list.php?lang=<?=LANGUAGE_ID?>&amp;ENTITY_ID=<?=intval($hlblock['ID'])?>">[<?=intval($hlblock['ROWS_COUNT'])?>]</a></td>
		</tr>
	<?endif;?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage('HLBLOCK_ADMIN_ENTITY_EDIT_LANGS')?></td>
	</tr>
	<?foreach ($langs as $lng):?>
	<tr>
		<td><?= $lng['NAME']?></td>
		<td><input type="text" name="LANGS[<?= $lng['LID']?>]" size="30" maxlength="100" value="<?= isset($localization[$lng['LID']]) ? htmlspecialcharsbx($localization[$lng['LID']]['NAME']) : ''?>" /></td>
	</tr>
	<?endforeach;?>
	<?$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="2" align="center">
			<table width="100%" class="internal" id="RIGHTS_table" align="center">
				<tbody>
					<tr class="heading">
						<td colspan="2"></td>
					</tr>
					<?if (!empty($currentRights)):?>
						<?foreach ($currentRights as $i => $rght):
							$code = $rght['ACCESS_CODE'];
							$task = $rght['TASK_ID'];
							?>
						<tr>
							<td align="right">
								<?
								$title = isset($currentRightsName[$code]['provider']) && $currentRightsName[$code]['provider']
										? $currentRightsName[$code]['provider'].': '
										: '';
								echo htmlspecialcharsbx(
									isset($currentRightsName[$code]) && isset($currentRightsName[$code]['name'])
									? $title . $currentRightsName[$code]['name']
									: $code
								);
								?>:
							</td>
							<td>
								<select name="RIGHTS[TASK_ID][]">
									<?foreach ($tasks as $tid => $tname):?>
									<option value="<?= $tid?>"<?= ($task == $tid ? ' selected="selected"' : '') ?>><?= $tname?></option>
									<?endforeach;?>
								</select>
								<input type="hidden" name="RIGHTS[RIGHT_ID][]" value="<?= $i?>">
								<input type="hidden" name="RIGHTS[ACCESS_CODE][]" value="<?= htmlspecialcharsbx($code)?>">
								<a href="javascript:void(0);" onclick="deleteRow(this);" data-id="<?= htmlspecialcharsbx($code)?>" class="access-delete"></a>
							</td>
						</tr>
						<?endforeach;?>
					<?endif;?>
					<tr>
						<td width="40%" align="right">&nbsp;</td>
						<td width="60%" align="left">
							<a href="javascript:void(0)" onclick="showForm();" class="bx-action-href"><?= GetMessage('HLBLOCK_ADMIN_ENTITY_RIGHTS_ADD')?></a>
						</td>
					</tr>
				</tbody>
			</table>
			<?\CUtil::InitJSCore(array('access'))?>
			<script>

				var selected = <?= json_encode(array_fill_keys($accessCodes, true))?>;
				var name = 'RIGHTS';
				var tbl = BX(name + '_table');
				var select = '<?= CUtil::JSEscape($tasksStr)?>';

				BX.Access.Init({
					other: {
						disabled_cr: true
					}
				});

				BX.Access.SetSelected(selected, name);

				function deleteRow(link)
				{
					selected[BX.data(BX(link), 'id')] = false;
					BX.remove(BX.findParent(BX(link), {tag: 'tr'}, true));
				}

				function showForm()
				{
					BX.Access.ShowForm({callback: function(obSelected)
					{
						for (var provider in obSelected)
						{
							if (obSelected.hasOwnProperty(provider))
							{
								for (var id in obSelected[provider])
								{
									if (obSelected[provider].hasOwnProperty(id))
									{
										var cnt = tbl.rows.length;
										var row = tbl.insertRow(cnt-1);

										selected[id] = true;
										row.vAlign = 'top';
										row.insertCell(-1);
										row.insertCell(-1);
										row.cells[0].align = 'right';
										row.cells[0].style.textAlign = 'right';
										row.cells[0].style.verticalAlign = 'middle';
										row.cells[0].innerHTML = BX.Access.GetProviderName(provider) + ' ' +
																	BX.util.htmlspecialchars(obSelected[provider][id].name) + ':' +
																	'<input type="hidden" name="' + name + '[RIGHT_ID][]" value="">'+
																	'<input type="hidden" name="' + name + '[ACCESS_CODE][]" value="' + id + '">';
										row.cells[1].align = 'left';
										row.cells[1].innerHTML = select + ' ' + '<a href="javascript:void(0);" onclick="deleteRow(this);" data-id="' + id + '" class="access-delete"></a>';
									}
								}
							}
						}
					}, bind: name})
				}
			</script>
		</td>
	</tr>
	<?
	$tabControl->Buttons(array('disabled' => !$isEditMode, 'back_url' => 'highloadblock_index.php?lang='.LANGUAGE_ID));
	$tabControl->End();
	?>
</form>
<?php
if ($request->get('mode') == 'list')
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_js.php');
}
else
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}