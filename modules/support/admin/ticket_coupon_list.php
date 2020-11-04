<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');

IncludeModuleLangFile(__FILE__);

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$LIST_URL = $APPLICATION->GetCurPage();
$EDIT_URL = '/bitrix/admin/ticket_coupon_edit.php';

$message = false;

$sTableID = 't_coupon_list';
$oSort = new CAdminSorting($sTableID, 'SORT', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID.'_filter_id',
	array(
		'ID',
	)
);

$arFilterFields = Array(
	'FIND_COUPON',
	'FIND_COUPON_EXACT_MATCH',
	'FIND_ID',
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

if (array_key_exists('GENERATE', $_POST) && $_POST['GENERATE'] == 'Y' && $bAdmin && check_bitrix_sessid())
{
	$COUPON = CSupportSuperCoupon::Generate();
	if ($COUPON !== false)
	{
		$_SESSION['BX_LAST_COUPON'] = $COUPON;
		LocalRedirect($APPLICATION->GetCurPage() . '?SHOW_COUPON=Y&lang='.LANG);
	}
	else
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage('SUP_CL_GENERATE_ERROR'), $e);
	}
}

if (array_key_exists('SHOW_COUPON', $_GET) && $_GET['SHOW_COUPON'] == 'Y' && array_key_exists('BX_LAST_COUPON', $_SESSION))
{
	$message = new CAdminMessage( array('MESSAGE' => GetMessage('SUP_CL_GENERATE_MESS_OK', array('%COUPON%' => $_SESSION['BX_LAST_COUPON'])), 'TYPE' => 'OK') );
}

if ($bAdmin && $lAdmin->EditAction())
{
	$obSSC = new CSupportSuperCoupon();
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$arUpdate = array(
			'COUNT_TICKETS' => $arFields['COUNT_TICKETS'],
			'ACTIVE'  => $arFields['ACTIVE'],
		);
			
		if (!$obSSC->Update($ID, $arUpdate))
		{
			$ex = $APPLICATION->GetException();
			$lAdmin->AddUpdateError($ex->GetString(), $ID);
		}
	}
}


if($bAdmin && ($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CSupportSuperCoupon::GetList(array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case 'delete':
				@set_time_limit(0);
				CSupportSuperCoupon::Delete($ID);
			break;
		}
	}
}

$rsData = CSupportSuperCoupon::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);

$lAdmin->NavText($rsData->GetNavPrint(GetMessage('SUP_CL_PAGES')));

$arHeaders = Array();
$arHeaders[] = Array('id'=>'ID', 'content'=>'ID', 'default'=>true, 'sort' => 'ID');
$arHeaders[] = Array('id'=>'COUPON', 'content'=>GetMessage('SUP_CL_COUPON'), 'default'=>true, 'sort' => 'COUPON');
$arHeaders[] = Array('id'=>'COUNT_TICKETS', 'content'=>GetMessage('SUP_CL_COUNT_TICKETS'), 'default'=>true, 'sort' => 'COUNT_TICKETS');
$arHeaders[] = Array('id'=>'COUNT_USED', 'content'=>GetMessage('SUP_CL_COUNT_USED'), 'default'=>true, 'sort' => 'COUNT_USED');
$arHeaders[] = Array('id'=>'TIMESTAMP_X', 'content'=>GetMessage('SUP_CL_TIMESTAMP_X'), 'default'=>false, 'sort' => 'TIMESTAMP_X');
$arHeaders[] = Array('id'=>'DATE_CREATE', 'content'=>GetMessage('SUP_CL_DATE_CREATE'), 'default'=>false, 'sort' => 'DATE_CREATE');
$arHeaders[] = Array('id'=>'ACTIVE', 'content'=>GetMessage('SUP_CL_ACTIVE'), 'default'=>true, 'sort' => 'ACTIVE');
$arHeaders[] = Array('id'=>'ACTIVE_FROM', 'content'=>GetMessage('SUP_CL_ACTIVE_FROM'), 'default'=>true, 'sort' => 'ACTIVE_FROM');
$arHeaders[] = Array('id'=>'ACTIVE_TO', 'content'=>GetMessage('SUP_CL_ACTIVE_TO'), 'default'=>true, 'sort' => 'ACTIVE_TO');

$arHeaders[] = Array('id'=>'CREATED_USER_ID', 'content'=>GetMessage('SUP_CL_CREATED_USER_ID'), 'default'=>false, 'sort' => 'CREATED_USER_ID');
$arHeaders[] = Array('id'=>'CREATED_LOGIN', 'content'=>GetMessage('SUP_CL_CREATED_LOGIN'), 'default'=>false, 'sort' => 'CREATED_LOGIN');
$arHeaders[] = Array('id'=>'CREATED_FIRST_NAME', 'content'=>GetMessage('SUP_CL_CREATED_FIRST_NAME'), 'default'=>false, 'sort' => 'CREATED_FIRST_NAME');
$arHeaders[] = Array('id'=>'CREATED_LAST_NAME', 'content'=>GetMessage('SUP_CL_CREATED_LAST_NAME'), 'default'=>false, 'sort' => 'CREATED_LAST_NAME');

$arHeaders[] = Array('id'=>'UPDATED_USER_ID', 'content'=>GetMessage('SUP_CL_UPDATED_USER_ID'), 'default'=>false, 'sort' => 'UPDATED_USER_ID');
$arHeaders[] = Array('id'=>'UPDATED_LOGIN', 'content'=>GetMessage('SUP_CL_UPDATED_LOGIN'), 'default'=>false, 'sort' => 'UPDATED_LOGIN');
$arHeaders[] = Array('id'=>'UPDATED_FIRST_NAME', 'content'=>GetMessage('SUP_CL_UPDATED_FIRST_NAME'), 'default'=>false, 'sort' => 'UPDATED_FIRST_NAME');
$arHeaders[] = Array('id'=>'UPDATED_LAST_NAME', 'content'=>GetMessage('SUP_CL_UPDATED_LAST_NAME'), 'default'=>false, 'sort' => 'UPDATED_LAST_NAME');

$arHeaders[] = Array('id'=>'SLA_ID', 'content'=>GetMessage('SUP_CL_SLA_ID'), 'default'=>false, 'sort' => 'SLA_ID');
$arHeaders[] = Array('id'=>'SLA_NAME', 'content'=>GetMessage('SUP_CL_SLA_NAME'), 'default'=>true, 'sort' => 'SLA_NAME');


$lAdmin->AddHeaders($arHeaders);

while ($arCoupon = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arCoupon['ID'], $arCoupon);
	$row->AddInputField('COUNT_TICKETS', Array('size'=>'5'));
	$row->AddCheckField('ACTIVE');

	$arActions = Array();
	
	$arActions[] = array(
		'ICON'=>'edit',
		'DEFAULT' => 'Y',
		'TEXT'=>GetMessage('SUP_CL_EDIT'),
		'ACTION'=>$lAdmin->ActionRedirect($EDIT_URL.'?lang='.LANGUAGE_ID.'&ID='.$arCoupon['ID'])
	);
	$arActions[] = array(
		'ICON' => 'delete',
		'TEXT'	=> GetMessage('SUP_CL_DELETE'),
		'ACTION'=>'if(confirm(\''.GetMessage('SUP_CL_DELETE_CONFIRMATION').'\')) '.$lAdmin->ActionDoGroup($arCoupon['ID'], 'delete'),
	);
	$arActions[] = array('SEPARATOR' => true);
	$arActions[] = array(
		'TEXT'=>GetMessage('SUP_CL_LOG'),
		'ACTION'=>$lAdmin->ActionRedirect('ticket_coupon_log.php?lang='.LANGUAGE_ID.'&set_filter=Y&FIND_COUPON_ID='.$arCoupon['ID']),
	);

	$row->AddActions($arActions);
	
	$row->AddViewField("SLA_NAME", $arCoupon['SLA_NAME']);
}

$lAdmin->AddFooter(
	array(
		array('title'=>GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value'=>$rsData->SelectedRowsCount()),
		array('counter'=>true, 'title'=>GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value'=>'0'),
	)
);

$lAdmin->AddGroupActionTable(Array(
	'delete'=>GetMessage('MAIN_ADMIN_LIST_DELETE'),
	)
);


$aContext = array(
	array(
		'ICON'=> 'btn_new',
		'TEXT'=> GetMessage('SUP_CL_ADD'),
		'LINK'=>$EDIT_URL.'?lang='.LANG,
		'TITLE'=>GetMessage('SUP_CL_ADD_TITLE')
	),
);


$lAdmin->AddAdminContextMenu($aContext);

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
	<td><input type="text" name="FIND_COUPON" size="47" value="<?=htmlspecialcharsbx($FIND_COUPON)?>"><?=InputType("checkbox", "FIND_NAME_EXACT_MATCH", "Y", $FIND_NAME_EXACT_MATCH, false, "", "title='".GetMessage('SUP_CL_EXACT_MATCH')."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>ID:</td>
	<td><input type="text" name="FIND_ID" size="47" value="<?=htmlspecialcharsbx($FIND_ID)?>"></td>
</tr>

<?

$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?></form><?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>