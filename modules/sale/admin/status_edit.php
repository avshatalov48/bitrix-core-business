<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/include.php');

$readOnly = $APPLICATION->GetGroupRight('sale') < 'W';

if ($readOnly)
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

use	Bitrix\Sale\Internals\StatusTable,
	Bitrix\Sale\Internals\StatusLangTable,
	Bitrix\Sale\Internals\StatusGroupTaskTable,
	Bitrix\Main\Loader,
	Bitrix\Main\TaskTable,
	Bitrix\Main\GroupTable,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Localization\LanguageTable;

Loader::includeModule('sale');
Loc::loadMessages(__FILE__);
Loc::loadMessages(__DIR__.'/task_description.php');

// initialise variables
$statusId     = $_REQUEST['ID'] ? $DB->ForSql($_REQUEST['ID'], 2) : null;
$status       = array(); // TYPE, SORT, NOTIFY
$translations = array(); // LID => LID, NAME, DESCRIPTION
$groupTasks   = array(); // GROUP_ID => GROUP_ID, TASK_ID

$languages = array(); // LID => NAME
$groups    = array(); // ID => NAME
$errors    = array();

$tasks = array(); // ID => TASK
$result = TaskTable::getList(array(
	'select' => array('*'),
	'filter' => array('=MODULE_ID' => 'sale', '=BINDING' => 'status'),
));
while ($row = $result->fetch())
	$tasks[$row['ID']] = $row;
asort($tasks);

$statusFields = StatusTable::getEntity()->getFields();
$statusLangFields = StatusLangTable::getEntity()->getFields();

// get languages
$result = LanguageTable::getList(array(
	'select' => array('LID', 'NAME'),
	'filter' => array('=ACTIVE' => 'Y')
));
while ($row = $result->fetch())
	$languages[$row['LID']] = $row['NAME'];

// get groups
$saleGroupIds = array();
$result = $APPLICATION->GetGroupRightList(array('MODULE_ID' => 'sale'));
while ($row = $result->Fetch())
{
	if (in_array($row['G_ACCESS'], array('P', 'U')) && $row['GROUP_ID'] > 2)
	{
		$saleGroupIds[] = $row['GROUP_ID'];
	}
}

if ($saleGroupIds)
{
	$result = GroupTable::getList(array(
		'select' => array('ID', 'NAME'),
		'filter' => array('=ID' => $saleGroupIds),
		'order'  => array('C_SORT' => 'ASC', 'ID' => 'ASC'),
	));
	while ($row = $result->fetch())
		$groups[$row['ID']] = $row['NAME'];
}

// A D D / U P D A T E /////////////////////////////////////////////////////////////////////////////////////////////////
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$readOnly && check_bitrix_sessid() && ($_POST['save'] || $_POST['apply']))
{
	$adminSidePanelHelper->decodeUriComponent();

	$errors = array();
	$statusType = $_REQUEST['TYPE'] == \Bitrix\Sale\OrderStatus::TYPE ? \Bitrix\Sale\OrderStatus::TYPE : \Bitrix\Sale\DeliveryStatus::TYPE;
	$lockedStatusList = array(
		\Bitrix\Sale\OrderStatus::TYPE => array(
			\Bitrix\Sale\OrderStatus::getInitialStatus(),
			\Bitrix\Sale\OrderStatus::getFinalStatus(),
		),
		\Bitrix\Sale\DeliveryStatus::TYPE => array(
			\Bitrix\Sale\DeliveryStatus::getInitialStatus(),
			\Bitrix\Sale\DeliveryStatus::getFinalStatus(),
		),
	);

	if ($statusId)
	{
		foreach ($lockedStatusList as $lockStatusType => $lockStatusIdList)
		{
			foreach ($lockStatusIdList as $lockStatusId)
			{
				if ($lockStatusId == $statusId && $statusType != $lockStatusType)
				{
					$errors[] = Loc::getMessage('SALE_STATUS_WRONG_TYPE', array(
						'#STATUS_ID#' => htmlspecialcharsEx($statusId),
						'#STATUS_TYPE#' => Loc::getMessage('SSEN_TYPE_'.$statusType))
					);
					break;
				}
			}
		}
	}


	// prepare & check status
	$status = array(
		'TYPE'   => $statusType,
		'SORT'   => ($statusSort = intval($_POST['SORT'])) ? $statusSort : 100,
		'NOTIFY' => $_POST['NOTIFY'] ? 'Y' : 'N',
		'COLOR' => strlen($_POST['NEW_COLOR']) ? $_POST['NEW_COLOR'] : "",
		'XML_ID' => strlen($_POST['XML_ID']) ? $_POST['XML_ID'] : StatusTable::generateXmlId(),
	);

	$isNew = true;


	if ($statusId)
	{
		$isNew = false;
		if ($statusData = StatusTable::getList(array(
											'select' => array('ID', 'TYPE', 'COLOR'),
											'filter' => array('=ID' => $statusId),
											'limit'  => 1,
										))->fetch())
		{
			if ($statusData['TYPE'] != $statusType)
			{
				$checkFilter = array(
					'select' => array('ID'),
					'filter' => array('=STATUS_ID' => $statusId),
					'limit' => 1
				);

				if ($statusData['TYPE'] == \Bitrix\Sale\OrderStatus::TYPE)
				{
					$checkStatus = \Bitrix\Sale\Internals\OrderTable::getList($checkFilter)->fetch();
					$errorMessageCheck = Loc::getMessage('SALE_STATUS_TYPE_ORDER_EXISTS', array(
																			'#STATUS_ID#' => htmlspecialcharsEx($statusId),
																			'#STATUS_TYPE#' => Loc::getMessage('SSEN_TYPE_'.$statusType),
																			'#CURRENT_STATUS_ID#' => $statusId
																			));
				}
				else
				{
					$checkStatus = \Bitrix\Sale\Internals\ShipmentTable::getList($checkFilter)->fetch();
					$errorMessageCheck = Loc::getMessage('SALE_STATUS_TYPE_SHIPMENT_EXISTS', array(
																			'#STATUS_ID#' => htmlspecialcharsEx($statusId),
																			'#STATUS_TYPE#' => Loc::getMessage('SSEN_TYPE_'.$statusType),
																			'#CURRENT_STATUS_ID#' => $statusId,
																		   ));
				}

				if (!empty($checkStatus))
				{
					$errors[] = $errorMessageCheck;
				}
			}
		}
	}

	$result = new \Bitrix\Main\Entity\Result;
	if ($statusId)
	{
		$sid = $statusId;
		StatusTable::checkFields($result, $statusId, $status);
	}
	else
	{
		$sid = $status['ID'] = trim($_POST['NEW_ID']);
		StatusTable::checkFields($result, null, $status);
	}

	$errors = array_merge($errors, $result->getErrorMessages());

	// prepare & check translations
	foreach ($languages as $languageId => $languageName)
	{
		$translationName = trim($_REQUEST['NAME_'.$languageId]);
		$translations[$languageId] = array(
			'STATUS_ID'   => $sid,
			'LID'         => $languageId,
			'NAME'        => $translationName,
			'DESCRIPTION' => trim($_REQUEST['DESCRIPTION_'.$languageId]),
		);
		if (! $translationName)
			$errors[] = Loc::getMessage('ERROR_NO_NAME')." [$languageId] ".htmlspecialcharsbx($languageName);
	}

	// prepare & check group tasks
	foreach ($groups as $groupId => $groupName)
	{
		$taskId = $_REQUEST['TASK'.$groupId];
		$groupTasks[$groupId] = array(
			'STATUS_ID' => $sid,
			'GROUP_ID'  => $groupId,
			'TASK_ID'   => $taskId,
		);
		if (! $tasks[$taskId])
			$errors[] = Loc::getMessage('SSEN_INVALID_TASK_ID_FOR').' '.$groupName;
	}

	// add or update status
	if (! $errors)
	{
		// update status, delete translations and group tasks
		if (!$isNew)
		{
			$result = StatusTable::update($statusId, $status);
			if ($result->isSuccess())
			{
				StatusLangTable::deleteByStatus($statusId);
				StatusGroupTaskTable::deleteByStatus($statusId);
			}
			else
				$errors = $result->getErrorMessages();
		}
		// add new status, create mail template
		else
		{
			$result = StatusTable::add($status);
			if ($result->isSuccess())
			{
				$statusId = $status['ID'];
			}
			else
			{
				$errors = $result->getErrorMessages();
			}

		}
	}

	// add translations and group tasks, redirect
	if (! $errors)
	{
		foreach ($translations as $data)
		{
			StatusLangTable::add($data);
		}
		
		foreach ($groupTasks as $data)
		{
			StatusGroupTaskTable::add($data);
		}

		if ($result->isSuccess())
		{
			if ($isNew)
			{
				CSaleStatus::CreateMailTemplate($statusId);
			}
		}

		$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $statusId));

		if ($_POST['save'])
			LocalRedirect('sale_status.php?lang='.LANGUAGE_ID.GetFilterParams('filter_', false));
		else
			LocalRedirect("sale_status_edit.php?ID=".$statusId."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errors);
	}
}
// L O A D  O R  N E W /////////////////////////////////////////////////////////////////////////////////////////////////
else if ($statusId)
{
	if ($row = StatusTable::getList(array(
		'select' => array('*'),
		'filter' => array('=ID' => $statusId),
		'limit'  => 1,
	))->fetch())
	{
		$status = $row;

		$result = StatusLangTable::getList(array(
			'select' => array('*'),
			'filter' => array('=STATUS_ID' => $statusId),
		));
		while ($row = $result->fetch())
			$translations[$row['LID']] = $row;

		$result = StatusGroupTaskTable::getList(array(
			'select' => array('*'),
			'filter' => array('=STATUS_ID' => $statusId),
		));
		while ($row = $result->fetch())
			$groupTasks[$row['GROUP_ID']] = $row;
	}
	else
	{
		$status['ID'] = $statusId;
		$statusId = null;
	}
}

// V I E W /////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($statusId)
	$APPLICATION->SetTitle(Loc::getMessage('SALE_EDIT_RECORD', array('#ID#' => $statusId)));
else
	$APPLICATION->SetTitle(Loc::getMessage('SALE_NEW_RECORD'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SSEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/sale_status.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	)
);

if ($statusId && !$readOnly)
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SSEN_NEW_STATUS"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/sale_status_edit.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	);
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SSEN_DELETE_STATUS"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessageJS("SSEN_DELETE_STATUS_CONFIRM")."')) window.location='/bitrix/admin/sale_status.php?action=delete&ID[]=".urlencode($statusId)."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb';",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errors)
{
	$errorMessage = new CAdminMessage(
		array(
			"MESSAGE" => implode('<br>', $errors),
			"TYPE"=>"ERROR",
			"HTML" => true
		)
	);
	echo $errorMessage->Show();
}

?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?ID=<?=htmlspecialcharsbx($statusId)?>&lang=<?=LANGUAGE_ID?>" name="fform">
	<?=GetFilterHiddens('filter_')?>
	<input type="hidden" name="Update" value="Y">
	<?=bitrix_sessid_post()?>

	<?
	$tabControl = new CAdminTabControl("tabControl", array(
		array("DIV" => "edit1", "TAB" => Loc::getMessage("SSEN_TAB_STATUS"), "ICON" => "sale", "TITLE" => Loc::getMessage("SSEN_TAB_STATUS_DESCR")),
	));
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?=$statusFields['ID']->getTitle()?><?=$statusId ? '' : ' (1-2 '.Loc::getMessage('SALE_CODE_LEN').')'?>:</td>
		<td width="60%">
			<?if ($statusId):?>
				<b><?=$statusId?></b>
			<?else:?>
				<input type="text" name="NEW_ID" value="<?=htmlspecialcharsbx($status['ID'])?>" size="4" maxlength="2">
			<?endif?>
		</td>
	</tr>
	<tr>
		<td><?=$statusFields['TYPE']->getTitle()?>:</td>
		<td>
			<select name="TYPE">
				<option value="O"<?=$status['TYPE'] == 'O' ? 'selected' : ''?>><?=Loc::getMessage('SSEN_TYPE_O')?></option>
				<option value="D"<?=$status['TYPE'] == 'D' ? 'selected' : ''?>><?=Loc::getMessage('SSEN_TYPE_D')?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=$statusFields['SORT']->getTitle()?>:</td>
		<td><input type="text" name="SORT" value="<?=intval($status['SORT'])?>" size="10"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SSEN_NOTIFY_ASK')?>:</td>
		<td>
			<input type="checkbox" name="NOTIFY"<?if (! ($statusId && $status['NOTIFY'] == 'N')):?> checked="checked"<?endif?> onclick="(
				function (box)
				{
					var link = BX('status-template-link');
					if (box.checked)
						link.style.display = 'inline-block';
					else
						link.style.display = 'none';
				}
			)(this)">
			&nbsp;
			<a id="status-template-link"
				<?if ($statusId):?>
					href="/bitrix/admin/message_admin.php?lang=<?=LANGUAGE_ID?>&find_event_type=SALE_STATUS_CHANGED_<?=$statusId?>" target="_blank"
				<?else:?>
					href="#" onclick="(new BX.CDialog({
						title: '<?=Loc::getMessage('SSEN_NOTIFY_W_TITLE')?>',
						content: '<?=Loc::getMessage('SSEN_NOTIFY_W_CONTENT')?>',
						height: 100,
						width: 250,
						resizable: false,
						buttons: [BX.CDialog.prototype.btnClose]
					})).Show()"
				<?endif?>
				<?if ($statusId && $status['NOTIFY'] == 'N'):?>
					style="display:none"
				<?endif?>
			><?=Loc::getMessage('SSEN_NOTIFY_LINK')?></a>
		</td>
	</tr>
	<tr>
		<td><?=$statusFields['COLOR']->getTitle()?>:</td>
		<td>
			<style>
				#new_color_label{
					width: 23px;
					height: 23px;
					margin: 1px 0 0px 5px;
					padding: 0;
					position: relative;
					display: inline;
					float: left;
					border: 1px solid;
					border-color: #87919c #959ea9 #9ea7b1 #959ea9;
					border-radius: 4px;
					-webkit-box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);
					box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);
				}
			</style>
			<input type="text" name="NEW_COLOR" id="new_color" value="<?=htmlspecialcharsbx($status['COLOR'])?>" size="4" maxlength="7" style="float:left; margin-right: 5px">
			<script>
			function SetStatusColorInput(color)
			{
				if (!color)
					color = "";
				document.getElementById("new_color").value = color;
				document.getElementById("new_color_label").style.background = color;
			}
			</script>
			<?
			$APPLICATION->IncludeComponent(
				"bitrix:main.colorpicker",
				"",
				array(
					"SHOW_BUTTON" => "Y",
					"ID" => "123",
					"NAME" => Loc::getMessage('SSEN_COLOR'),
					"ONSELECT" => "SetStatusColorInput"
				),
				false
			);
			?>
			<div id="new_color_label" style="background: <?=htmlspecialcharsbx($status['COLOR'])?>"></div>
		</td>
	</tr>
	<tr>
		<td><?=$statusFields['XML_ID']->getTitle()?>:</td>
		<td><input type="text" name="XML_ID" value="<?=$status['XML_ID'] ?: StatusTable::generateXmlId();?>" size="30"></td>
	</tr>
	<?foreach ($languages as $languageId => $languageName):?>
		<tr class="heading">
			<td colspan="2">[<?=htmlspecialcharsex($languageId)?>] <?=htmlspecialcharsex($languageName)?></td>
		</tr>
		<tr class="adm-detail-required-field">
			<td><?=$statusLangFields['NAME']->getTitle()?>:</td>
			<td><input type="text" name="NAME_<?=htmlspecialcharsbx($languageId)?>" value="<?=htmlspecialcharsbx($translations[$languageId]['NAME'])?>" size="30"></td>
		</tr>
		<tr>
			<td valign="top"><?=$statusLangFields['DESCRIPTION']->getTitle()?>:</td>
			<td>
				<textarea name="DESCRIPTION_<?=htmlspecialcharsbx($languageId); ?>" cols="35" rows="3"><?=htmlspecialcharsbx($translations[$languageId]['DESCRIPTION'])?></textarea>
			</td>
		</tr>
	<?endforeach?>
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage('SSEN_ACCESS_PERMS')?></td>
	</tr>
	<?if ($groups):?>
		<?foreach ($groups as $groupId => $groupName): $groupTaskId = $groupTasks[$groupId]['TASK_ID']?>
			<tr>
				<td><?=htmlspecialcharsbx($groupName)?></td>
				<td>
					<select name="TASK<?=$groupId?>">
						<?foreach ($tasks as $taskId => $task):?>
							<option value="<?=$taskId?>" <?=$taskId == $groupTaskId ? 'selected': ''?>>
								<?=htmlspecialcharsbx(($name = Loc::getMessage('TASK_NAME_'.strtoupper($task['NAME']))) ? $name : $task['NAME'])?>
							</option>
						<?endforeach?>
					</select>
				</td>
			</tr>
		<?endforeach?>
	<?else:?>
		<tr>
			<td colspan="2" style="text-align: center;">
				<?=Loc::getMessage('SSEN_PERM_GROUPS_ABSENT')?>
			</td>
		</tr>
	<?endif?>
	<tr>
		<td>
			<a href="settings.php?lang=<?=LANGUAGE_ID?>&mid=sale&tabControl_active_tab=edit4" target="_blank">
				<?=Loc::getMessage('SSEN_GROUPS_LINK')?>
			</a>
		</td>
		<td>
			<a href="/bitrix/admin/task_admin.php?lang=<?=LANGUAGE_ID?>&set_filter=Y&find_module_id=sale&find_binding=status" target="_blank">
				<?=Loc::getMessage('SSEN_TASKS_LINK')?>
			</a>
		</td>
	</tr>

	<?
	$tabControl->EndTab();
	$tabControl->Buttons(array(
		"disabled" => $readOnly,
		"back_url" => "/bitrix/admin/sale_status.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	));
	$tabControl->End();
	?>

</form>

<?require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");