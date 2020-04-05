<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$ShopID = CSalePaySystemAction::GetParamValue("SHOP_ID");
$scid = CSalePaySystemAction::GetParamValue("SCID");
$customerNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$orderDate = CSalePaySystemAction::GetParamValue("ORDER_DATE");
$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$Sum = number_format($Sum, 2, ',', '');
?>
<font class="tablebodytext">
Вы хотите оплатить через систему <b>Яндекс.Деньги</b>.<br /><br />
Сумма к оплате по счету: <b><?=$Sum?> р.</b><br />
<br />
<?if(strlen(CSalePaySystemAction::GetParamValue("IS_TEST")) > 0):
	?>
	<form name="ShopForm" action="https://demomoney.yandex.ru/eshop.xml" method="post" target="_blank">
<?else:
	?>
	<form name="ShopForm" action="http://money.yandex.ru/eshop.xml" method="post">
<?endif;?>

<input name="ShopID" value="<?=$ShopID?>" type="hidden">
<input name="scid" value="<?=$scid?>" type="hidden">
<input name="customerNumber" value="<?=$customerNumber?>" type="hidden">
<input name="orderNumber" value="<?=$orderNumber?>" type="hidden">
<input name="Sum" value="<?=$Sum?>" type="hidden">
<input name="cms_name" value="1C-Bitrix" type="hidden">
<br />
Детали заказа:<br />
<input name="OrderDetails" value="заказ №<?=$orderNumber?> (<?=$orderDate?>)" type="hidden">
<br />
<input name="BuyButton" value="Оплатить" type="submit">

</font><p><font class="tablebodytext"><b>ВНИМАНИЕ!</b> Возврат средств по платежной системе Яндекс.Деньги - невозможен, пожалуйста, будьте внимательны при оплате заказа.</font></p>
</form>