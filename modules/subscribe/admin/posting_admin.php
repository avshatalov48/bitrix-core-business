<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$sTableID = 'tbl_posting';
$oSort = new CAdminSorting($sTableID, 'ID', 'desc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

if ($lAdmin->GetAction() === 'js_send' && check_bitrix_sessid())
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	$ID = intval($request['ID']);
	$cPosting = new CPosting;
	$rsPosting = CPosting::GetByID($ID);
	$arPosting = $rsPosting->Fetch();
	if ($arPosting)
	{
		if ($arPosting['STATUS'] == 'D' || $arPosting['STATUS'] == 'W')
		{
			if ($cPosting->ChangeStatus($ID, 'P'))
			{
				if ($arPosting['AUTO_SEND_TIME'] != '')
				{
					if (COption::GetOptionString('subscribe', 'subscribe_auto_method') !== 'cron')
					{
						$rsAgents = CAgent::GetList(['ID' => 'DESC'], [
							'MODULE_ID' => 'subscribe',
							'NAME' => 'CPosting::AutoSend(' . $ID . ',%',
						]);
						while ($arAgent = $rsAgents->Fetch())
						{
							CAgent::Delete($arAgent['ID']);
						}
						CAgent::AddAgent('CPosting::AutoSend(' . $ID . ',true);', 'subscribe', 'N', 0, $arPosting['AUTO_SEND_TIME'], 'Y', $arPosting['AUTO_SEND_TIME']);
						CAdminMessage::ShowMessage(['MESSAGE' => GetMessage('posting_agent_submitted'), 'TYPE' => 'OK']);
					}
					else
					{
						CAdminMessage::ShowMessage(['MESSAGE' => GetMessage('posting_cron_setup'), 'TYPE' => 'OK']);
					}
					?><script>
						<?=$sTableID?>.GetAdminList('<?php echo $APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID?>');
					</script><?php
				}
				else
				{
					$arEmailStatuses = CPosting::GetEmailStatuses($ID);
					$nEmailsSent = intval($arEmailStatuses['N']);
					$nEmailsError = intval($arEmailStatuses['E']);
					$nEmailsTotal = intval($arEmailStatuses['Y']) + $nEmailsSent + $nEmailsError;

					CAdminMessage::ShowMessage([
						'DETAILS' => '<p>' . GetMessage('POST_ADM_SENDING_NOTE_LINE1') . '<br>' . GetMessage('POST_ADM_SENDING_NOTE_LINE2') . '</p>'
							. '#PROGRESS_BAR#'
							. '<p>' . GetMessage('posting_addr_processed') . ' <b>' . ($nEmailsSent + $nEmailsError) . '</b> ' . GetMessage('posting_addr_of') . ' <b>' . $nEmailsTotal . '</b></p>'
							. '<p>' . GetMessage('POST_ADM_WITH_ERRORS') . ': <b>' . $nEmailsError . '</b>.</p>'
						,
						'HTML' => true,
						'TYPE' => 'PROGRESS',
						'PROGRESS_TOTAL' => $nEmailsTotal,
						'PROGRESS_VALUE' => $nEmailsSent + $nEmailsError,
						'BUTTONS' => [
							[
								'ID' => 'btn_stop',
								'VALUE' => GetMessage('POST_ADM_BTN_STOP'),
								'ONCLICK' => 'Stop()',
							],
							[
								'ID' => 'btn_cont',
								'VALUE' => GetMessage('posting_continue_button'),
								'ONCLICK' => 'Cont()',
							],
						],
					]);
					?><script>
						<?=$sTableID?>.GetAdminList('<?php echo $APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID?>', MoveProgress());
					</script><?php
				}
			}
			else
			{
				CAdminMessage::ShowMessage($cPosting->LAST_ERROR);
			}
		}
		elseif ($arPosting['STATUS'] == 'P')
		{
			if ($arPosting['AUTO_SEND_TIME'] != '')
			{
				//Wait for agent
			}
			else
			{
				$cPosting = new CPosting;
				if ($cPosting->SendMessage($ID, COption::GetOptionString('subscribe', 'posting_interval')) !== false)
				{
					$arEmailStatuses = CPosting::GetEmailStatuses($ID);
					$nEmailsSent = intval($arEmailStatuses['N']);
					$nEmailsError = intval($arEmailStatuses['E']);
					$nEmailsTotal = intval($arEmailStatuses['Y']) + $nEmailsSent + $nEmailsError;

					CAdminMessage::ShowMessage([
						'DETAILS' => '<p>' . GetMessage('POST_ADM_SENDING_NOTE_LINE1') . '<br>' . GetMessage('POST_ADM_SENDING_NOTE_LINE2') . '</p>'
							. '#PROGRESS_BAR#'
							. '<p>' . GetMessage('posting_addr_processed') . ' <b>' . ($nEmailsSent + $nEmailsError) . '</b> ' . GetMessage('posting_addr_of') . ' <b>' . $nEmailsTotal . '</b></p>'
							. '<p>' . GetMessage('POST_ADM_WITH_ERRORS') . ': <b>' . $nEmailsError . '</b>.</p>'
						,
						'HTML' => true,
						'TYPE' => 'PROGRESS',
						'PROGRESS_TOTAL' => $nEmailsTotal,
						'PROGRESS_VALUE' => $nEmailsSent + $nEmailsError,
						'BUTTONS' => [
							[
								'ID' => 'btn_stop',
								'VALUE' => GetMessage('POST_ADM_BTN_STOP'),
								'ONCLICK' => 'Stop()',
							],
							[
								'ID' => 'btn_cont',
								'VALUE' => GetMessage('posting_continue_button'),
								'ONCLICK' => 'Cont()',
							],
						],
					]);
					?><script>
						MoveProgress();
					</script><?php
				}
				else
				{
					CAdminMessage::ShowMessage($cPosting->LAST_ERROR);
				}
			}
		}
		elseif ($arPosting['STATUS'] == 'S' || $arPosting['STATUS'] == 'E')
		{
			$arEmailStatuses = CPosting::GetEmailStatuses($ID);
			$nEmailsSent = intval($arEmailStatuses['N']);
			$nEmailsError = intval($arEmailStatuses['E']);
			$nEmailsTotal = intval($arEmailStatuses['Y']) + $nEmailsSent + $nEmailsError;

			CAdminMessage::ShowMessage([
				'MESSAGE' => GetMessage('post_send_ok'),
				'DETAILS' => '#PROGRESS_BAR#'
					. '<p>' . GetMessage('posting_addr_processed') . ' <b>' . ($nEmailsSent + $nEmailsError) . '</b> ' . GetMessage('posting_addr_of') . ' <b>' . $nEmailsTotal . '</b></p>'
					. '<p>' . GetMessage('POST_ADM_WITH_ERRORS') . ': <b>' . $nEmailsError . '</b>.</p>'
				,
				'HTML' => true,
				'TYPE' => 'PROGRESS',
				'PROGRESS_TOTAL' => $nEmailsTotal,
				'PROGRESS_VALUE' => $nEmailsSent + $nEmailsError,
			]);
			?><script>
				<?=$sTableID?>.GetAdminList('<?php echo $APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID?>');
			</script><?php
		}
		else
		{
			CAdminMessage::ShowMessage(GetMessage('POST_ADM_POST_NOT_FOUND'));
		}
	}
	else
	{
		CAdminMessage::ShowMessage(GetMessage('POST_ADM_POST_NOT_FOUND'));
	}

	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php';
}

function PostingAdminCheckDateFilter(CAdminList $lAdmin, $date_from, $date_to)
{
	$date_from = trim($date_from);
	$date_to = trim($date_to);
	if ($date_from !== '' || $date_to !== '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($date_from,'D.M.Y'),'d.m.Y');
		$date2_stm = MkDateTime(FmtDate($date_to,'D.M.Y') . ' 23:59','d.m.Y H:i');
		if (!$date1_stm && $date_from !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_TIMESTAMP_FROM'));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && $date_to !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_TIMESTAMP_TILL'));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_FROM_TILL_TIMESTAMP'));
		}
	}

	return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = [
	'find',
	'find_type',
	'find_id',
	'find_timestamp_1',
	'find_timestamp_2',
	'find_date_sent_1',
	'find_date_sent_2',
	'find_auto_send_time_1',
	'find_auto_send_time_2',
	'find_status',
	'find_status_id',
	'find_subject',
	'find_from',
	'find_to',
	'find_body',
	'find_body_type',
	'find_rubric',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [];
if (
	PostingAdminCheckDateFilter($lAdmin, $currentFilter['find_timestamp_1'], $currentFilter['find_timestamp_2'])
	&& PostingAdminCheckDateFilter($lAdmin, $currentFilter['find_date_sent_1'], $currentFilter['find_date_sent_2'])
	&& PostingAdminCheckDateFilter($lAdmin, $currentFilter['find_auto_send_time_1'], $currentFilter['find_auto_send_time_2'])
)
{
	$arFilter = [
		'ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'id' ? $currentFilter['find'] : $currentFilter['find_id']),
		'TIMESTAMP_1' => $currentFilter['find_timestamp_1'],
		'TIMESTAMP_2' => $currentFilter['find_timestamp_2'],
		'DATE_SENT_1' => $currentFilter['find_date_sent_1'],
		'DATE_SENT_2' => $currentFilter['find_date_sent_2'],
		'AUTO_SEND_TIME_1' => $currentFilter['find_auto_send_time_1'],
		'AUTO_SEND_TIME_2' => $currentFilter['find_auto_send_time_2'],
		'STATUS' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'status' ? $currentFilter['find'] : $currentFilter['find_status']),
		'STATUS_ID' => $currentFilter['find_status_id'],
		'SUBJECT' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'subject' ? $currentFilter['find'] : $currentFilter['find_subject']),
		'FROM' => $currentFilter['find_from'],
		'TO' => $currentFilter['find_to'],
		'BODY' => $currentFilter['find_body'],
		'BODY_TYPE' => $currentFilter['find_body_type'],
		'RUB_ID' => $currentFilter['find_rubric'],
	];
}

if ($lAdmin->EditAction() && $POST_RIGHT == 'W')
{
	foreach ($request['FIELDS'] as $ID => $arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
		{
			continue;
		}
		$DB->StartTransaction();
		$ID = intval($ID);
		$ob = new CPosting;
		if (!$ob->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage('post_save_err') . $ID . ': ' . $ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

$arID = $lAdmin->GroupAction();
if ($arID && $POST_RIGHT == 'W')
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$cData = new CPosting;
		$rsData = $cData->GetList([$by => $order], $arFilter);
		while ($arRes = $rsData->Fetch())
		{
			$arID[] = $arRes['ID'];
		}
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}
		$ID = intval($ID);
		switch ($lAdmin->GetAction())
		{
		case 'delete':
			@set_time_limit(0);
			$DB->StartTransaction();
			if (!CPosting::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage('post_del_err'), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case 'stop':
			$cPosting = new CPosting;
			$cPosting->ChangeStatus($ID, 'W');
			$rsAgents = CAgent::GetList(['ID' => 'DESC'], [
				'MODULE_ID' => 'subscribe',
				'NAME' => 'CPosting::AutoSend(' . $ID . ',%',
			]);
			while ($arAgent = $rsAgents->Fetch())
			{
				CAgent::Delete($arAgent['ID']);
			}
			break;
		}
	}
}

$lAdmin->AddHeaders([
	[
		'id' => 'ID',
		'content' => 'ID',
		'sort' => 'id',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'TIMESTAMP_X',
		'content' => GetMessage('post_updated'),
		'sort' => 'timestamp',
		'default' => true,
	],
	[
		'id' => 'SUBJECT',
		'content' => GetMessage('post_subj'),
		'sort' => 'subject',
		'default' => true,
	],
	[
		'id' => 'BODY_TYPE',
		'content' => GetMessage('post_body_type'),
		'sort' => 'body_type',
		'default' => true,
	],
	[
		'id' => 'STATUS',
		'content' => GetMessage('post_stat'),
		'sort' => 'status',
		'default' => true,
	],
	[
		'id' => 'DATE_SENT',
		'content' => GetMessage('post_sent'),
		'sort' => 'date_sent',
		'default' => true,
	],
	[
		'id' => 'SENT_TO',
		'content' => GetMessage('post_report'),
		'sort' => false,
		'default' => false,
	],
	[
		'id' => 'FROM_FIELD',
		'content' => GetMessage('post_from'),
		'sort' => 'from_field',
		'default' => false,
	],
	[
		'id' => 'TO_FIELD',
		'content' => GetMessage('post_to'),
		'sort' => 'to_field',
		'default' => false,
	],
]);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$arVisibleColumns[] = 'ID';
if ($request['mode'] === 'excel')
{
	$arNavParams = false;
}
else
{
	$arNavParams = ['nPageSize' => CAdminResult::GetNavSize($sTableID)];
}

$cData = new CPosting;
$rsData = $cData->GetList([$by => $order], $arFilter, $arVisibleColumns, $arNavParams);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('post_nav')));

while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);
	$row->AddViewField('SUBJECT', '<a href="posting_edit.php?ID=' . $arRes['ID'] . '&amp;lang=' . LANGUAGE_ID . '" title="' . GetMessage('post_act_edit') . '">' . $arRes['SUBJECT'] . '</a>');
	$row->AddInputField('SUBJECT', ['size' => 20]);
	$row->AddSelectField('BODY_TYPE',['text' => GetMessage('POST_TEXT'),'html' => GetMessage('POST_HTML')]);
	$strStatus = '';
	switch ($arRes['STATUS']) :
		case 'S': $strStatus = '[S] ' . GetMessage('POST_STATUS_SENT'); break;
		case 'P': $strStatus = '[P] ' . GetMessage('POST_STATUS_PART'); break;
		case 'E': $strStatus = '[E] ' . GetMessage('POST_STATUS_ERROR'); break;
		case 'D': $strStatus = '[D] ' . GetMessage('POST_STATUS_DRAFT'); break;
		case 'W': $strStatus = '[W] ' . GetMessage('POST_STATUS_WAIT'); break;
	endswitch;
	if ($arRes['STATUS'] != 'D')
	{
		$arSTATUS = [$arRes['STATUS'] => $strStatus];
		if ($arRes['STATUS'] == 'P')
		{
			$arSTATUS['W'] = GetMessage('POST_STATUS_WAIT');
		}
		else
		{
			$arSTATUS['D'] = GetMessage('POST_STATUS_DRAFT');
		}
		$row->AddSelectField('STATUS', $arSTATUS);
	}

	$strStatus = '&nbsp;';
	switch ($arRes['STATUS']) :
		case 'S': $strStatus = '[<span style="color:green">S</span>]&nbsp;<span style="color:green">' . GetMessage('POST_STATUS_SENT') . '</span>'; break;
		case 'P': $strStatus = '[<span style="color:blue">P</span>]&nbsp;<span style="color:blue">' . GetMessage('POST_STATUS_PART') . '</span>'; break;
		case 'E': $strStatus = '[<span style="color:green">E</span>]&nbsp;<span style="color:green">' . GetMessage('POST_STATUS_ERROR') . '</span>'; break;
		case 'D': $strStatus = '[D]&nbsp;' . GetMessage('POST_STATUS_DRAFT'); break;
		case 'W': $strStatus = '[<span style="color:red">W</span>]&nbsp;<span style="color:red">' . GetMessage('POST_STATUS_WAIT') . '</span>'; break;
	endswitch;

	$row->AddViewField('STATUS', $strStatus);
	$row->AddViewField('SENT_TO', "[&nbsp;<a href=\"javascript:void(0)\" OnClick=\"jsUtils.OpenWindow('posting_bcc.php?ID=" . $arRes['ID'] . '&lang=' . LANGUAGE_ID . "', 600, 500);\">" . GetMessage('POST_SHOW_LIST') . '</a>&nbsp;]');
	$row->AddInputField('FROM_FIELD', ['size' => 20]);
	$row->AddInputField('TO_FIELD', ['size' => 20]);

	$arActions = [];

	if (($arRes['STATUS'] != 'P') && $POST_RIGHT == 'W')
	{
		$arActions[] = [
			'ICON' => 'edit',
			'DEFAULT' => true,
			'TEXT' => GetMessage('post_act_edit'),
			'ACTION' => $lAdmin->ActionRedirect('posting_edit.php?ID=' . $arRes['ID'])
		];
	}
	$arActions[] = [
		'ICON' => 'copy',
		'TEXT' => GetMessage('posting_copy_link'),
		'ACTION' => $lAdmin->ActionRedirect('posting_edit.php?ID=' . $arRes['ID'] . '&amp;action=copy')
	];
	if (($arRes['STATUS'] != 'P') && $POST_RIGHT == 'W')
	{
		$arActions[] = [
			'ICON' => 'delete',
			'TEXT' => GetMessage('post_act_del'),
			'ACTION' => "if(confirm('" . GetMessage('post_act_del_conf') . "')) " . $lAdmin->ActionDoGroup($arRes['ID'], 'delete')
		];
	}
	$arActions[] = [
		'ICON' => '',
		'TEXT' => GetMessage('post_report'),
		'ACTION' => "jsUtils.OpenWindow('posting_bcc.php?ID=" . $arRes['ID'] . '&lang=' . LANGUAGE_ID . "', 600, 500);"
	];

	$arActions[] = ['SEPARATOR' => true];

	if ($arRes['STATUS'] == 'D' && $POST_RIGHT == 'W')
	{
		$arActions[] = [
			'ICON' => '',
			'TEXT' => GetMessage('post_act_send'),
			'ACTION' => "if(confirm('" . GetMessage('post_conf') . "')) window.location='" . $APPLICATION->GetCurPage() . '?ID=' . $arRes['ID'] . '&action=send&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "'"
		];
	}
	if ($arRes['STATUS'] == 'W' && $POST_RIGHT == 'W')
	{
		$arActions[] = [
			'ICON' => '',
			'TEXT' => GetMessage('posting_continue_act'),
			'ACTION' => "if(confirm('" . GetMessage('posting_continue_conf') . "')) window.location='" . $APPLICATION->GetCurPage() . '?ID=' . $arRes['ID'] . '&action=send&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "'"
		];
	}
	if ($arRes['STATUS'] == 'P' && $POST_RIGHT == 'W')
	{
		$arActions[] = [
			'ICON' => '',
			'TEXT' => GetMessage('posting_stop_act'),
			'ACTION' => "if(confirm('" . GetMessage('posting_stop_conf') . "')) window.location='" . $APPLICATION->GetCurPage() . '?ID=' . $arRes['ID'] . '&action=stop&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "'"
		];
	}

	if (is_set($arActions[count($arActions) - 1], 'SEPARATOR'))
	{
		unset($arActions[count($arActions) - 1]);
	}
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	[
		['title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value' => $rsData->SelectedRowsCount()],
		['counter' => true, 'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'],
	]
);
$lAdmin->AddGroupActionTable([
	'delete' => GetMessage('MAIN_ADMIN_LIST_DELETE'),
]);

$aContext = [
	[
		'TEXT' => GetMessage('MAIN_ADD'),
		'LINK' => 'posting_edit.php?lang=' . LANGUAGE_ID,
		'TITLE' => GetMessage('POST_ADD_TITLE'),
		'ICON' => 'btn_new',
	],
];
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('post_title'));
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		'id' => GetMessage('POST_F_ID'),
		'timestamp' => GetMessage('POST_F_TIMESTAMP'),
		'date_sent' => GetMessage('POST_F_DATE_SENT'),
		'auto_send_time' => GetMessage('POST_F_AUTO_SEND_TIME'),
		'status' => GetMessage('POST_F_STATUS'),
		'from' => GetMessage('POST_F_FROM'),
		'to' => GetMessage('POST_F_TO'),
		'subject' => GetMessage('POST_F_SUBJECT'),
		'body_type' => GetMessage('POST_F_BODY_TYPE'),
		'body' => GetMessage('POST_F_BODY'),
		'rubric' => GetMessage('POST_F_RUBRIC'),
	]
);
?>
<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage();?>">
<?php
$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage('POST_FIND')?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find'])?>" title="<?=GetMessage('POST_FIND_TITLE')?>">
		<?php
		$arr = [
			'reference' => [
				GetMessage('POST_F_SUBJECT'),
				GetMessage('POST_F_ID'),
				GetMessage('POST_F_STATUS'),
			],
			'reference_id' => [
				'subject',
				'id',
				'status',
			]
		];
		echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_ID')?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_id'])?>">
		&nbsp;<?=ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_TIMESTAMP') . ' (' . FORMAT_DATE . '):'?></td>
	<td><?php echo CalendarPeriod('find_timestamp_1', $currentFilter['find_timestamp_1'], 'find_timestamp_2', $currentFilter['find_timestamp_2'], 'find_form','Y')?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_DATE_SENT') . ' (' . FORMAT_DATE . '):'?></td>
	<td><?php echo CalendarPeriod('find_date_sent_1', $currentFilter['find_date_sent_1'], 'find_date_sent_2', $currentFilter['find_date_sent_2'], 'find_form','Y')?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_AUTO_SEND_TIME') . ' (' . FORMAT_DATE . '):'?></td>
	<td><?php echo CalendarPeriod('find_auto_send_time_1', $currentFilter['find_auto_send_time_1'], 'find_auto_send_time_2', $currentFilter['find_auto_send_time_2'], 'find_form','Y')?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_STATUS')?>:</td>
	<td>
		<input type="text" name="find_status" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_status'])?>">&nbsp;<?=ShowFilterLogicHelp()?><br>
		<?php
		$arr = [
			'reference' => [
				'[S] ' . GetMessage('POST_STATUS_SENT'),
				'[P] ' . GetMessage('POST_STATUS_PART'),
				'[D] ' . GetMessage('POST_STATUS_DRAFT'),
				'[E] ' . GetMessage('POST_STATUS_ERROR'),
				'[W] ' . GetMessage('POST_STATUS_WAIT'),
			],
			'reference_id' => [
				'S',
				'P',
				'D',
				'E',
				'W',
			]
		];
		echo SelectBoxFromArray('find_status_id', $arr, $currentFilter['find_status_id'], GetMessage('MAIN_ALL'), '');
		?>
	</td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_FROM')?>:</td>
	<td><input type="text" name="find_from" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_from'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_TO')?>:</td>
	<td><input type="text" name="find_to" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_to'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_SUBJECT')?>:</td>
	<td><input type="text" name="find_subject" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_subject'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_BODY_TYPE')?>:</td>
	<td>
		<?php
		$arr = [
			'reference' => [
				GetMessage('POST_TEXT'),
				GetMessage('POST_HTML'),
			],
			'reference_id' => [
				'text',
				'html',
			]
		];
		echo SelectBoxFromArray('find_body_type', $arr, $currentFilter['find_body_type'], GetMessage('MAIN_ALL'), '');
		?>
	</td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_BODY')?>:</td>
	<td><input type="text" name="find_body" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_body'])?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_RUBRIC')?>:</td>
	<td>
		<?php
		$arr = [
			'reference' => [],
			'reference_id' => [],
		];
		$rsRubrics = CRubric::GetList();
		while ($arRubric = $rsRubrics->Fetch())
		{
			$arr['reference'][] = '[' . $arRubric['ID'] . '] ' . $arRubric['NAME'];
			$arr['reference_id'][] = $arRubric['ID'];
		}
		echo SelectBoxMFromArray('find_rubric[]', $arr, $currentFilter['find_rubric'], GetMessage('MAIN_ALL'), '');
		?>
	</td>
</tr>
<?php
$oFilter->Buttons(['table_id' => $sTableID,'url' => $APPLICATION->GetCurPage(), 'form' => 'find_form']);
$oFilter->End();
?>
</form>

<?php
//******************************
// Send message and show progress
//******************************
if ($lAdmin->GetAction() === 'send'):
	$ID = intval($request['ID']);
	?>
	<div id="progress_message">
	</div>
	<script>
		var stop = false;
		function Stop()
		{
			stop=true;
			document.getElementById('btn_stop').disabled = true;
			document.getElementById('btn_cont').disabled = false;
		}
		function Cont()
		{
			stop=false;
			document.getElementById('btn_stop').disabled = false;
			document.getElementById('btn_cont').disabled = true;
			MoveProgress();
		}
		function MoveProgress()
		{
			if(stop)
				return;

			var url = 'posting_admin.php?lang=<?php echo LANGUAGE_ID?>&ID=<?php echo $ID?>&<?php echo bitrix_sessid_get()?>&action=js_send';
			ShowWaitWindow();
			BX.ajax.post(
				url,
				null,
				function(result){
					CloseWaitWindow();
					document.getElementById('progress_message').innerHTML = result;
				}
			);
		}
		setTimeout('MoveProgress()', 100);
	</script>
<?php endif;?>

<?php $lAdmin->DisplayList();?>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
