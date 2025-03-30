<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/prolog.php';
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
$searchDB = CDatabase::GetModuleConnection('search');

$SEARCH_RIGHT = $APPLICATION->GetGroupRight('search');
if ($SEARCH_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$sTableID = 'tbl_search_phrase_list';
$oSort = new CAdminSorting($sTableID, 'COUNT', 'DESC');
$lAdmin = new CAdminList($sTableID, $oSort);

$ref = $ref_id = [];
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = $ar['ID'];
	$ref_id[] = $ar['ID'];
}
$arSiteDropdown = ['reference' => $ref, 'reference_id' => $ref_id];

$arFilterFields = [
	'find_id',
	'find_date1',
	'find_date2',
	'find_site_id',
	'find_phrase',
	'find_stat_sess_id',
	'find_url_to',
	'find_url_to_404',
];

$lAdmin->InitFilter($arFilterFields);
if ($lAdmin->IsDefaultFilter())
{
	$sdate = time();
	$sdate = mktime(0, 0, 0, date('m', $sdate), date('d', $sdate) - 1, date('Y', $sdate));
	$find_date1 = ConvertTimeStamp($sdate);
}

$arFilter = [];

if ($_REQUEST['find_id_exact_match'] == 'Y')
{
	$arFilter['=ID'] = $find_id;
}
else
{
	$arFilter['ID'] = $find_id;
}

$arFilter['>=TIMESTAMP_X'] = $find_date1;
$arFilter['<=TIMESTAMP_X'] = $find_date2 && search_isShortDate($find_date2) ? ConvertTimeStamp(AddTime(MakeTimeStamp($find_date2), 1, 'D'), 'FULL') : $find_date2;
$arFilter['=SITE_ID'] = $find_site_id;

if ($_REQUEST['find_phrase_exact_match'] == 'Y')
{
	$arFilter['=PHRASE'] = $find_phrase;
}
else
{
	$arFilter['PHRASE'] = $find_phrase;
}

if ($_REQUEST['find_stat_sess_id_exact_match'] == 'Y')
{
	$arFilter['=STAT_SESS_ID'] = $find_stat_sess_id;
}
else
{
	$arFilter['STAT_SESS_ID'] = $find_stat_sess_id;
}

if ($_REQUEST['find_url_to_exact_match'] == 'Y')
{
	$arFilter['=URL_TO'] = $find_url_to;
}
else
{
	$arFilter['URL_TO'] = $find_url_to;
}

$arFilter['=URL_TO_404'] = $find_url_to_404;

foreach ($arFilter as $key => $value)
{
	if ($value == '')
	{
		unset($arFilter[$key]);
	}
}
$arFilter['!PHRASE'] = false;

$aContext = [];

$lAdmin->AddAdminContextMenu($aContext);
$arHeaders = [
	['id' => 'PHRASE', 'content' => GetMessage('SEARCH_PHS_PHRASE'), 'sort' => 'PHRASE', 'default' => true],
	['id' => 'COUNT', 'content' => GetMessage('SEARCH_PHS_COUNT'), 'sort' => 'COUNT', 'default' => true, 'align' => 'right'],
];

$lAdmin->AddHeaders($arHeaders);

$arFields = $lAdmin->GetVisibleHeaderColumns();
$arFields[] = 'COUNT';

$rsData = CSearchStatistic::GetList([$by => $order], $arFilter, $arFields, true);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('SEARCH_PHS_PHRASES')));

while ($arRes = $rsData->NavNext(true, 'f_'))
{
	$row = & $lAdmin->AddRow($f_ID, $arRes);
}

$lAdmin->AddFooter([
	[
		'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
		'value' => $rsData->SelectedRowsCount(),
	],
]);

$lAdmin->CheckListMode();
/***************************************************************************
			HTML form
****************************************************************************/
$APPLICATION->SetTitle(GetMessage('SEARCH_PHS_TITLE'));
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if (is_object($message))
{
	echo $message->Show();
}
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?php
$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		'find_id' => GetMessage('SEARCH_PHS_ID'),
		'find_dates' => GetMessage('SEARCH_PHS_DATE'),
		'find_site_id' => GetMessage('SEARCH_PHS_SITE_ID'),
		'find_url_to' => GetMessage('SEARCH_PHS_URL_TO'),
		'find_stat_sess_id' => GetMessage('SEARCH_PHS_STAT_SESS_ID'),
	]
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?php echo GetMessage('SEARCH_PHS_PHRASE')?>:</b></td>
	<td><input type="text" name="find_phrase" size="47" value="<?php echo htmlspecialcharsbx($find_phrase)?>"></td>
</tr>
<tr>
	<td><?php echo GetMessage('SEARCH_PHS_ID')?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?php echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td width="0%" nowrap><?php echo GetMessage('SEARCH_PHS_DATE')?>:</td>
	<td width="0%" nowrap><?php echo CalendarPeriod('find_date1', $find_date1, 'find_date2', $find_date2, 'form1','Y')?></td>
</tr>
<tr>
	<td><?php echo GetMessage('SEARCH_PHS_SITE_ID')?>:</td>
	<td><?php echo SelectBoxFromArray('find_site_id', $arSiteDropdown, $find_site_id, GetMessage('SEARCH_PHS_SITE'));?></td>
</tr>

<tr>
	<td nowrap><?php echo GetMessage('SEARCH_PHS_URL_TO')?></td>
	<td><?php
		echo SelectBoxFromArray('find_url_to_404', ['reference' => [GetMessage('MAIN_YES'), GetMessage('MAIN_NO')], 'reference_id' => ['Y','N']], htmlspecialcharsbx($find_url_to_404), GetMessage('SEARCH_PHS_404'));
	?>&nbsp;<input type="text" name="find_url_to" size="33" value="<?php echo htmlspecialcharsbx($find_url_to)?>"></td>
</tr>
<tr>
	<td nowrap><?php echo GetMessage('SEARCH_PHS_STAT_SESS_ID')?></td>
	<td><input type="text" name="find_stat_sess_id" size="47" value="<?php echo htmlspecialcharsbx($find_stat_sess_id)?>"></td>
</tr>

<?php
$oFilter->Buttons(['table_id' => $sTableID, 'url' => $APPLICATION->GetCurPage()]);
$oFilter->End();
?>
</form>

<?php
$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
