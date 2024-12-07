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
	
$ID = intval($ID);

$message = false;

if (($save <> '' || $apply <> '') && $_SERVER['REQUEST_METHOD']=='POST' && $bAdmin && check_bitrix_sessid())
{
	$obSSC = new CSupportSuperCoupon();
	$bOK = false;
	$new = false;
	
	$arFields = array(
		"ACTIVE_FROM" 	 => $_POST['ACTIVE_FROM'],
		"ACTIVE_TO"		 => $_POST['ACTIVE_TO'],
		"ACTIVE"		 => $_POST['ACTIVE'],
		"COUNT_TICKETS"	 => intval($_POST['COUNT_TICKETS']),
		"SLA_ID"		 => intval($_POST['SLA_ID']),
	);
	
	if ($ID > 0)
	{
		$bOK = $obSSC->Update($ID, $arFields);
	}
	else 
	{
		if ($COUPON = $obSSC->Generate($arFields))
		{
			if ($COUPON !== false)
			{
				$_SESSION['BX_LAST_COUPON'] = $COUPON;
				
				$rsCoupons = $obSSC->GetList(false, array('COUPON' => $COUPON));
				$arCoupon = $rsCoupons->Fetch();
				$ID = intval($arCoupon['ID']);
				
				$bOK = true;
				$new = true;
			} 
			else 
			{
				$bOK = false;
			}
		}
	}
	if ($bOK)
	{
		if ($save <> '') LocalRedirect($LIST_URL . '?lang='.LANG . ($new?'&SHOW_COUPON=Y':''));
		elseif ($new) LocalRedirect($APPLICATION->GetCurPage() . '?ID='.$ID. '&lang='.LANG.'&tabControl_active_tab='.urlencode($tabControl_active_tab));
	}
	else 
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage('SUP_CE_ERROR'), $e);
	}	
}

$rsCoupons = CSupportSuperCoupon::GetList(false, array('ID' => $ID));
$arCoupon = $rsCoupons->GetNext();
if (!$arCoupon)
{
	$ID = 0;
	$arCoupon = array(
		'ACTIVE_FROM' => '',
		'ACTIVE_TO' => '',
		'ACTIVE' => 'Y',
		'COUNT_TICKETS' => 5,
		'SLA_ID' => COption::GetOptionString( "support", 'SUPERTICKET_DEFAULT_SLA' ),
	);
}

$str_ACTIVE_FROM = isset($_REQUEST["ACTIVE_FROM"]) ? htmlspecialcharsbx($_REQUEST["ACTIVE_FROM"]) : $arCoupon["ACTIVE_FROM"];
$str_ACTIVE_TO = isset($_REQUEST["ACTIVE_TO"]) ? htmlspecialcharsbx($_REQUEST["ACTIVE_TO"]) : $arCoupon["ACTIVE_TO"];
$str_COUNT_TICKETS = isset($_REQUEST["COUNT_TICKETS"]) ? intval($_REQUEST["COUNT_TICKETS"]) : $arCoupon["COUNT_TICKETS"];
$str_SLA_ID  = isset($_REQUEST["SLA_ID"]) ? intval($arCoupon["SLA_ID"]) : $arCoupon["SLA_ID"];
$str_ACTIVE  = isset($_REQUEST["ACTIVE"]) ? ($_REQUEST["ACTIVE"] == 'Y' ? 'Y' : 'N') : $arCoupon["ACTIVE"];


if ($ID > 0)
{
	$APPLICATION->SetTitle(GetMessage('SUP_CE_TITLE_EDIT', array('%COUPON%' => $arCoupon['~COUPON'])));
}
else 
{
	$APPLICATION->SetTitle(GetMessage('SUP_CE_TITLE_NEW'));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		'ICON'	=> 'btn_list',
		'TEXT'	=> GetMessage('SUP_CE_COUPONS_LIST'), 
		'LINK'	=> $LIST_URL . '?lang=' . LANG
	)
);


$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();

$aTabs = array();
$aTabs[] = array(
	'DIV' => 'edit1',
	'TAB' => GetMessage('SUP_CE_COUPON'),
	//'ICON'=>'ticket_dict_edit',
	'TITLE'=>GetMessage('SUP_CE_COUPON_TITLE')
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&ID=<?=$ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>
<?if ($ID > 0){?>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_COUPON')?></td>
	<td width="65%"><?=$arCoupon['COUPON']?></td>
</tr>
<?}?>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_ACTIVE_FROM')?></td>
	<td width="65%"><?echo CalendarDate("ACTIVE_FROM", $str_ACTIVE_FROM, "form1")?></td>
</tr>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_ACTIVE_TO')?></td>
	<td width="65%"><?echo CalendarDate("ACTIVE_TO", $str_ACTIVE_TO, "form1")?></td>
</tr>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_ACTIVE')?></td>
	<td width="65%"><input type="hidden" name="ACTIVE" value="N"><input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE == 'Y'){?> checked<?}?>></td>
</tr>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_COUNT')?></td>
	<td width="65%"><input type="text" name="COUNT_TICKETS" value="<?=$str_COUNT_TICKETS?>"></td>
</tr>
<?
$arr = Array("reference" => array(), "reference_id" => array());
$a = array('NAME' => 'ASC');
$rs = CTicketSLA::GetList($a, array(), $__is_f);
while ($arSla = $rs->GetNext())
{
	$arr['reference'][] = htmlspecialcharsback($arSla['NAME']) . ' ['.$arSla['ID'].']';
	$arr['reference_id'][] = $arSla['ID'];
}
?>
<tr> 
	<td align="right" width="35%"><?=GetMessage('SUP_CE_F_SLA')?></td>
	<td width="65%"><?=SelectBoxFromArray('SLA_ID', $arr, $str_SLA_ID , '')?></td>
</tr>
<?
$tabControl->Buttons(Array("disabled"=>!$bAdmin, 'back_url' => $LIST_URL . '?lang='.LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>