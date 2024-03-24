<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$ID = intval($request['ID']);

$APPLICATION->SetTitle(GetMessage('post_title'));

$sTableID = 'tbl_posting_bcc';
$oSort = new CAdminSorting($sTableID, 'EMAIL', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = [
	'find_status_id',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

if ($currentFilter['find_status_id'] !== 'N')
{
	$currentFilter['find_status_id'] = 'E';
}

$arEMAIL = $lAdmin->GroupAction();
if ($arEMAIL && $POST_RIGHT == 'W')
{
	$arSubscr = [];
	$rsData = CPosting::GetEmailsByStatus($ID, $currentFilter['find_status_id']);
	while ($arRes = $rsData->Fetch())
	{
		$arSubscr[$arRes['EMAIL']] = $arRes['SUBSCRIPTION_ID'];
	}

	if ($lAdmin->IsGroupActionToAll())
	{
		$arEMAIL = array_keys($arSubscr);
	}

	foreach ($arEMAIL as $EMAIL)
	{
		$SUBSCR_ID = intval($arSubscr[$EMAIL]);
		if ($SUBSCR_ID <= 0)
		{
			continue;
		}

		switch ($lAdmin->GetAction())
		{
		case 'sudelete':
			CSubscription::Delete($SUBSCR_ID);
			break;
		case 'inactive':
			$oSubscription = new CSubscription;
			$oSubscription->Update($SUBSCR_ID, ['ACTIVE' => 'N']);
			break;
		}
	}
}

$lAdmin->AddHeaders([
	[
		'id' => 'EMAIL',
		'content' => GetMessage('POST_EMAIL'),
		'default' => true,
	],
	[
		'id' => 'SUBSCRIPTION_ID',
		'content' => GetMessage('POST_SUBSCRIPTION_ID'),
		'default' => true,
		'align' => 'right',
	],
	[
		'id' => 'USER_ID',
		'content' => GetMessage('POST_USER_ID'),
		'default' => true,
		'align' => 'right',
	],
]);

$rsData = CPosting::GetEmailsByStatus($ID, $currentFilter['find_status_id']);
$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));
while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['EMAIL'], $arRes);
	if ($arRes['SUBSCRIPTION_ID'] > 0)
	{
		$rs = CSubscription::GetByID($arRes['SUBSCRIPTION_ID']);
		$ar = $rs->Fetch();
		if (!$ar)
		{
			$row->AddViewField('SUBSCRIPTION_ID', $arRes['SUBSCRIPTION_ID'] . ' (' . GetMessage('POST_SUBSCR_DELETED') . ')');
		}
		elseif ($ar['ACTIVE'] == 'N')
		{
			$row->AddViewField('SUBSCRIPTION_ID', '<a target="_blank" href="subscr_edit.php?lang=' . LANGUAGE_ID . '&amp;ID=' . $arRes['SUBSCRIPTION_ID'] . '">' . $arRes['SUBSCRIPTION_ID'] . '</a> (' . GetMessage('POST_SUBSCR_INACTIVE') . ')');
		}
		else
		{
			$row->AddViewField('SUBSCRIPTION_ID', '<a target="_blank" href="subscr_edit.php?lang=' . LANGUAGE_ID . '&amp;ID=' . $arRes['SUBSCRIPTION_ID'] . '">' . $arRes['SUBSCRIPTION_ID'] . '</a>');
		}
	}
	if ($arRes['USER_ID'] > 0)
	{
		$row->AddViewField('USER_ID', '<a target="_blank" href="user_edit.php?lang=' . LANGUAGE_ID . '&amp;ID=' . $arRes['USER_ID'] . '">' . $arRes['USER_ID'] . '</a>');
	}
}

$lAdmin->AddFooter(
	[
		['title' => GetMessage('post_total'), 'value' => $rsData->SelectedRowsCount()],
		['counter' => true, 'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'],
	]
);
$lAdmin->AddGroupActionTable([
	'inactive' => GetMessage('POST_GROUP_ACTION_INACTIVE'),
	'sudelete' => GetMessage('POST_GROUP_ACTION_DELETE'),
]);

$lAdmin->CheckListMode();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_popup_admin.php';

?>
<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage();?>">
<input type="hidden" name="ID" value="<?php echo $ID?>">
<?php
$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[]
);

$oFilter->Begin();
?>
<tr>
	<td><?=GetMessage('POST_STATUS_ID')?>:</td>
	<td>
		<?php
		$arr = [
			'reference' => [
				GetMessage('POST_STATUS_ID_ERROR'),
				GetMessage('POST_STATUS_ID_SUCCESS'),
			],
			'reference_id' => [
				'E',
				'N',
			]
		];
		echo SelectBoxFromArray('find_status_id', $arr, $currentFilter['find_status_id']);
		?>
	</td>
</tr>
<?php
$oFilter->Buttons(['table_id' => $sTableID, 'url' => $APPLICATION->GetCurPage(), 'form' => 'find_form']);
$oFilter->End();
?>
</form>
<?php
$lAdmin->DisplayList();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_popup_admin.php';
