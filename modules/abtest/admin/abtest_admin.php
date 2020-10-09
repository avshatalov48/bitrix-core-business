<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('abtest');

$arLang = $APPLICATION->getLang();

$MOD_RIGHT = $APPLICATION->getGroupRight('abtest');
if ($MOD_RIGHT < 'R')
	$APPLICATION->authForm(getMessage('ACCESS_DENIED'));

$sTableID = "t_abtest_admin";
$oSort = new CAdminSorting($sTableID, 'id', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$aContext = array();

if ($MOD_RIGHT >= 'W')
{
	$aContext[] = array(
		"ICON" => "btn_new",
		"TEXT" => getMessage('ABTEST_BTN_NEW'),
		"LINK" => "abtest_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => getMessage('ABTEST_BTN_NEW'),
	);
}

$lAdmin->addAdminContextMenu($aContext);

if ($MOD_RIGHT >= 'W' && $arID = $lAdmin->groupAction())
{
	if ($_REQUEST['action'] == 'start')
	{
		$arActiveTests = array();
		$result = Bitrix\ABTest\ABTestTable::getList(array(
			'filter' => array('ACTIVE' => 'Y'),
			'select' => array('ID', 'SITE_ID')
		));
		while ($abtest = $result->fetch())
		{
			if (!isset($arActiveTests[$abtest['SITE_ID']]))
				$arActiveTests[$abtest['SITE_ID']] = array();

			$arActiveTests[$abtest['SITE_ID']][] = $abtest['ID'];
		}
	}

	foreach ($arID as $ID)
	{
		$ID = intval($ID);

		if ($ID <= 0)
			continue;

		$abtest = Bitrix\ABTest\ABTestTable::getById($ID)->fetch();
		if (empty($abtest))
			continue;

		switch ($_REQUEST['action'])
		{
			case 'delete':
				if (!Bitrix\ABTest\Helper::deleteTest($ID))
					$lAdmin->addGroupError(getMessage('ABTEST_DELETE_ERROR'));
				break;
			case 'start':
				if (!empty($arActiveTests[$abtest['SITE_ID']]))
				{
					if (in_array($ID, $arActiveTests[$abtest['SITE_ID']]))
						$lAdmin->addGroupError(getMessage('ABTEST_START_ERROR'));
					else
						$lAdmin->addGroupError(getMessage('ABTEST_ONLYONE_WARNING'));
				}
				else if ($abtest['ENABLED'] != 'Y')
				{
					$lAdmin->addGroupError(getMessage('ABTEST_START_ERROR'));
				}
				else if (!Bitrix\ABTest\Helper::startTest($ID))
				{
					$lAdmin->addGroupError(getMessage('ABTEST_START_ERROR'));
				}
				else
				{
					$arActiveTests[$abtest['SITE_ID']] = $abtest;
				}
				break;
			case 'stop':
				if (!Bitrix\ABTest\Helper::stopTest($ID))
					$lAdmin->addGroupError(getMessage('ABTEST_STOP_ERROR'));
				break;
		}
	}
}

$arHeaders = array(
	array('id' => 'TITLE', 'content' => getMessage('ABTEST_TBL_HEADER_TITLE'), 'default' => true, 'sort' => 'name'),
	array('id' => 'ACTIVE', 'content' => getMessage('ABTEST_TBL_HEADER_STATE'), 'default' => true, 'sort' => 'active')
);

$lAdmin->addHeaders($arHeaders);


$result = Bitrix\ABTest\ABTestTable::getList(array(
	'order'  => array(mb_strtoupper($by) => $order),
	'select' => array('*', 'USER_NAME' => 'USER.NAME', 'USER_LAST_NAME' => 'USER.LAST_NAME', 'USER_SECOND_NAME' => 'USER.SECOND_NAME', 'USER_TITLE' => 'USER.TITLE', 'USER_LOGIN' => 'USER.LOGIN')
));
$result = new CAdminResult($result, $sTableID);

$arRows = array();
$arActiveTests = array();
while ($abtest = $result->fetch())
{
	$arRows[] = $abtest;

	if ($abtest['ACTIVE'] == 'Y')
	{
		if (!isset($arActiveTests[$abtest['SITE_ID']]))
			$arActiveTests[$abtest['SITE_ID']] = array();

		$arActiveTests[$abtest['SITE_ID']][] = $abtest['ID'];
	}
}

foreach ($arRows as &$abtest)
{
	$row =& $lAdmin->addRow($abtest['ID'], $abtest);
	$row->addViewField('TITLE', sprintf(
		'<div%s><b>%s</b><br>%s</div>',
		in_array($abtest['ENABLED'], array('T', 'Y')) ? '' : ' style="color: #808080; "',
		htmlspecialcharsbx($abtest['NAME']) ?: str_replace('#ID#', $abtest['ID'], getMessage('ABTEST_TEST_TITLE')),
		htmlspecialcharsbx($abtest['DESCR'])
	));

	if (in_array($abtest['ENABLED'], array('T', 'Y')))
	{
		if ($abtest['ACTIVE'] == 'Y')
		{
			$start_date = $abtest['START_DATE']->format(Bitrix\Main\Type\Date::convertFormatToPhp($arLang['FORMAT_DATE']));
			$end_date   = null;

			if ($abtest['DURATION'] != 0)
			{
				if ($abtest['DURATION'] > 0)
				{
					$end = clone $abtest['START_DATE'];
					$end->add(intval($abtest['DURATION']).' days');

					$end_date = $end->format(Bitrix\Main\Type\Date::convertFormatToPhp($arLang['FORMAT_DATE']));
				}
				else
				{
					$siteCapacity = Bitrix\ABTest\AdminHelper::getSiteCapacity($abtest['SITE_ID']);
					$testCapacity = Bitrix\ABTest\AdminHelper::getTestCapacity($abtest['ID']);

					if ($abtest['MIN_AMOUNT'] > 0 && $abtest['PORTION'] > 0 && $siteCapacity['daily'] > 0)
					{
						$rem = $abtest['MIN_AMOUNT'] - min($testCapacity);
						$est = $rem > 0 ? $rem / ($siteCapacity['daily'] / 2) : 0;

						$end = new Bitrix\Main\Type\DateTime();
						$end->add(ceil(100 * $est / $abtest['PORTION']).' days');

						$end_date = $end->format(Bitrix\Main\Type\Date::convertFormatToPhp($arLang['FORMAT_DATE']));
					}
					else
					{
						$end_date = getMessage('ABTEST_DURATION_NA');
					}
				}
			}

			$user_name = CUser::formatName(
				CSite::getNameFormat(),
				array(
					'TITLE'       => $abtest['USER_TITLE'],
					'NAME'        => $abtest['USER_NAME'],
					'SECOND_NAME' => $abtest['USER_SECOND_NAME'],
					'LAST_NAME'   => $abtest['USER_LAST_NAME'],
					'LOGIN'       => $abtest['USER_LOGIN'],
				),
				true, true
			);

			$status  = '<table style="width: 100%; border-spacing: 0px; "><tr>';

			$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><img src="/bitrix/images/abtest/ab-test-on.gif"></td>';

			$status .= '<td style="padding: 0px 10px; vertical-align: top; ">';
			$status .= '<span style="white-space: nowrap; color: #729e00; font-weight: bold; ">'.getMessage('ABTEST_STATE_STARTED').'</span><br>';
			$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_START_DATE').': '.$start_date.'</span><br>';
			if ($end_date)
				$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_STOP_DATE2').': '.$end_date.'</span><br>';
			$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_STARTED_BY').': <a href="/bitrix/admin/user_edit.php?ID='.$abtest['USER_ID'].'&lang='.LANG.'">'.$user_name.'</a></span>';
			$status .= '</td>';

			if ($MOD_RIGHT >= 'W')
				$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><span class="adm-btn" onclick="if (confirm(\''.CUtil::JSEscape(getMessage('ABTEST_STOP_CONFIRM')).'\')) '.$lAdmin->actionDoGroup($abtest['ID'], 'stop').'">'.getMessage('ABTEST_BTN_STOP').'</span></td>';

			$status .= '</tr></table>';
		}
		else
		{
			$stop_date = $abtest['STOP_DATE'] ? $abtest['STOP_DATE']->format(Bitrix\Main\Type\Date::convertFormatToPhp($arLang['FORMAT_DATE'])) : false;

			$user_name = $abtest['USER_ID'] ? CUser::formatName(
				CSite::getNameFormat(),
				array(
					'TITLE'       => $abtest['USER_TITLE'],
					'NAME'        => $abtest['USER_NAME'],
					'SECOND_NAME' => $abtest['USER_SECOND_NAME'],
					'LAST_NAME'   => $abtest['USER_LAST_NAME'],
					'LOGIN'       => $abtest['USER_LOGIN'],
				),
				true, true
			) : false;

			$status  = '<table style="width: 100%; border-spacing: 0px; "><tr>';

			$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><img src="/bitrix/images/abtest/ab-test-off.gif"></td>';

			$status .= '<td style="padding: 0px 10px; vertical-align: top; ">';
			$status .= '<span style="white-space: nowrap; font-weight: bold; ">'.getMessage('ABTEST_STATE_STOPPED').'</span><br>';
			if ($stop_date)
				$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_STOP_DATE').': '.$stop_date.'</span><br>';
			if ($user_name)
				$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_STOPPED_BY').': <a href="/bitrix/admin/user_edit.php?ID='.$abtest['USER_ID'].'&lang='.LANG.'">'.$user_name.'</a></span>';
			$status .= '</td>';

			if ($MOD_RIGHT >= 'W')
			{
				if ($abtest['ENABLED'] == 'T')
					$action = $lAdmin->actionRedirect('abtest_edit.php?ID='.$abtest['ID'].'&lang='.LANG);
				else if (empty($arActiveTests[$abtest['SITE_ID']]))
					$action = 'if (confirm(\''.CUtil::JSEscape(getMessage('ABTEST_START_CONFIRM')).'\')) '.$lAdmin->actionDoGroup($abtest['ID'], 'start');
				else
					$action = 'alert(\''.CUtil::JSEscape(getMessage('ABTEST_ONLYONE_WARNING')).'\')';

				if (empty($arActiveTests[$abtest['SITE_ID']]))
					$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><span class="adm-btn adm-btn-green" onclick="'.$action.'">'.getMessage('ABTEST_BTN_START').'</span></td>';
				else
					$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><span class="adm-btn adm-btn-disabled" style="margin-right: 0px; " onclick="'.$action.'">'.getMessage('ABTEST_BTN_START').'</span></td>';
			}

			$status .= '</tr></table>';
		}
	}
	else
	{
		$status  = '<table style="width: 100%; border-spacing: 0px; color: #808080; "><tr>';

		$status .= '<td style="width: 1px; padding: 0px; vertical-align: top; "><img src="/bitrix/images/abtest/ab-test-off.gif"></td>';

		$status .= '<td style="padding: 0px 10px; vertical-align: top; ">';
		$status .= '<span style="white-space: nowrap; ">'.getMessage('ABTEST_NOT_READY').'</span>';
		$status .= '</td>';

		$status .= '</tr></table>';
	}

	$row->addViewField('ACTIVE', $status);

	$arActions = array();

	if (in_array($abtest['ENABLED'], array('T', 'Y')))
	{
		if (empty($arActiveTests[$abtest['SITE_ID']]) || in_array($abtest['ID'], $arActiveTests[$abtest['SITE_ID']]))
		{
			$arActions[] = array(
				'ICON'   => '',
				'TEXT'   => getMessage($abtest['ACTIVE'] == 'Y' ? 'ABTEST_BTN_STOP' : 'ABTEST_BTN_START'),
				'ACTION' => $abtest['ENABLED'] == 'T'
					? $lAdmin->actionRedirect('abtest_edit.php?ID='.$abtest['ID'].'&lang='.LANG)
					: "if (confirm('".CUtil::JSEscape(getMessage($abtest['ACTIVE'] == 'Y' ? 'ABTEST_STOP_CONFIRM' : 'ABTEST_START_CONFIRM'))."')) ".$lAdmin->actionDoGroup($abtest['ID'], $abtest['ACTIVE'] == 'Y' ? 'stop' : 'start')
			);
		}

		if ($abtest['USER_ID'])
		{
			$arActions[] = array(
				'ICON'    => '',
				'DEFAULT' => 'Y',
				'TEXT'    => getMessage('ABTEST_BTN_REPORT'),
				'ACTION'  => $lAdmin->actionRedirect('abtest_report.php?ID='.$abtest['ID'].'&lang='.LANG)
			);
		}

		$arActions[] = array('SEPARATOR' => 'Y');
		$arActions[] = array(
			'ICON'    => 'edit',
			'DEFAULT' => $abtest['USER_ID'] ? 'N' : 'Y',
			'TEXT'    => getMessage('ABTEST_BTN_EDIT'),
			'ACTION'  => $lAdmin->actionRedirect('abtest_edit.php?ID='.$abtest['ID'].'&lang='.LANG)
		);
	}

	$arActions[] = array(
		'ICON'   => 'delete',
		'TEXT'   => getMessage('ABTEST_BTN_DELETE'),
		'ACTION' => "if (confirm('".CUtil::JSEscape(getMessage('ABTEST_DELETE_CONFIRM'))."')) ".$lAdmin->actionDoGroup($abtest['ID'], 'delete'),
	);

	if ($MOD_RIGHT >= 'W')
		$row->addActions($arActions);
}

$lAdmin->checkListMode();

$APPLICATION->setTitle(getMessage('ABTEST_LIST_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<div style="background-color: #fff; border: 1px solid #ced7d8; padding: 20px; ">
	<table style="border-spacing: 0px; "><tr>
		<td style="border: none; padding: 15px; "><img src="/bitrix/images/abtest/ab-icon-big.png"></td>
		<td style="border: none; padding: 15px; max-width: 800px; "><?=getMessage('ABTEST_LIST_DESCR'); ?></td>
	</tr></table>
</div><br>

<?

$lAdmin->displayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
