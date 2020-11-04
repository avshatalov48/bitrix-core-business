<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');

IncludeModuleLangFile(__FILE__);

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$LIST_URL = '/bitrix/admin/ticket_coupon_list.php';

$message = false;

$sTableID = 't_coupon_log';
$oSort = new CAdminSorting($sTableID, 'SORT', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID.'_filter_id',
	array(
		GetMessage('SUP_CL_FLT_COUPON_ID'),
	)
);

$arFilterFields = Array(
	'FIND_COUPON',
	'FIND_COUPON_EXACT_MATCH',
	'FIND_COUPON_ID',
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
foreach($arFilterFields as $key)
{
	if (mb_strpos($key, '_EXACT_MATCH') !== false) continue;

	if (array_key_exists($key . '_EXACT_MATCH', $_REQUEST) && $_REQUEST[$key . '_EXACT_MATCH'] == 'Y')
	{
		$op = '=';
	}
	else
	{
		$op = '%';
	}

	if (array_key_exists($key, $_REQUEST) && (string) $_REQUEST[$key] <> '')
	{
		if (in_array($key . '_EXACT_MATCH', $arFilterFields))
		{
			$arFilter[$op.mb_substr($key, 5)] = $_REQUEST[$key];
		}
		else
		{
			$arFilter[mb_substr($key, 5)] = $_REQUEST[$key];
		}
	}
}


$rsData = CSupportSuperCoupon::GetLogList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);

$lAdmin->NavText($rsData->GetNavPrint(GetMessage('SUP_CL_PAGES')));

$arHeaders = Array();
$arHeaders[] = Array('id'=>'COUPON_ID', 'content'=>GetMessage('SUP_CL_COUPON_ID'), 'default'=>false, 'sort' => 'COUPON_ID');
$arHeaders[] = Array('id'=>'COUPON', 'content'=>GetMessage('SUP_CL_COUPON'), 'default'=>true, 'sort' => 'COUPON');
$arHeaders[] = Array('id'=>'TIMESTAMP_X', 'content'=>GetMessage('SUP_CL_TIMESTAMP_X'), 'default'=>true, 'sort' => 'TIMESTAMP_X');
$arHeaders[] = Array('id'=>'USER_ID', 'content'=>GetMessage('SUP_CL_USER_ID'), 'default'=>true, 'sort' => 'USER_ID');
$arHeaders[] = Array('id'=>'LOGIN', 'content'=>GetMessage('SUP_CL_LOGIN'), 'default'=>true, 'sort' => 'LOGIN');
$arHeaders[] = Array('id'=>'FIRST_NAME', 'content'=>GetMessage('SUP_CL_FIRST_NAME'), 'default'=>false, 'sort' => 'FIRST_NAME');
$arHeaders[] = Array('id'=>'LAST_NAME', 'content'=>GetMessage('SUP_CL_LAST_NAME'), 'default'=>false, 'sort' => 'LAST_NAME');
$arHeaders[] = Array('id'=>'SESSION_ID', 'content'=>GetMessage('SUP_CL_SESSION_ID'), 'default'=>false, 'sort' => 'SESSION_ID');
$arHeaders[] = Array('id'=>'GUEST_ID', 'content'=>GetMessage('SUP_CL_GUEST_ID'), 'default'=>false, 'sort' => 'GUEST_ID');


$bStatIncluded = CModule::IncludeModule('statistic');

$lAdmin->AddHeaders($arHeaders);

while ($arCouponLog = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arCoupon['ID'], $arCouponLog);
	if ($bStatIncluded)
	{
		///bitrix/admin/guest_list.php?lang=ru&set_filter=Y&find_user_exact_match=N&find_id=33&find_id_exact_match=N&find_id_exact_match=Y&find_country_exact_match=N
		$row->AddViewField('SESSION_ID', '<a href="/bitrix/admin/session_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_id='.$arCouponLog['SESSION_ID'].'">'.$arCouponLog['SESSION_ID'].'</a>');
		$row->AddViewField('GUEST_ID', '<a href="/bitrix/admin/guest_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_id_exact_match=Y&amp;find_id='.$arCouponLog['GUEST_ID'].'">'.$arCouponLog['SESSION_ID'].'</a>');
	}
}


$lAdmin->AddAdminContextMenu();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('SUP_CL_TITLE'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if ($message)
	echo $message->Show();
?><form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?"><?
$filter->Begin();
?>
<tr>
	<td><?=GetMessage("SUP_CL_FLT_COUPON")?>:</td>
	<td><input type="text" name="FIND_COUPON" size="47" value="<?=htmlspecialcharsbx($FIND_COUPON)?>"><?=InputType("checkbox", "FIND_NAME_EXACT_MATCH", "Y", $FIND_NAME_EXACT_MATCH, false, "")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("SUP_CL_FLT_COUPON_ID")?>:</td>
	<td><input type="text" name="FIND_COUPON_ID" size="47" value="<?=htmlspecialcharsbx($FIND_COUPON_ID)?>"></td>
</tr>

<?

$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?></form><?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>