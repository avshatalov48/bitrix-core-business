<?
	use Bitrix\Main\Localization\Loc;
\Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");
	Loc::loadMessages(__FILE__);

	$sum = roundEx($params['PAYMENT_SHOULD_PAY'], 2);
?>

<div class="sale-paysystem-wrapper">
	<span class="tablebodytext">
		<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_DESCRIPTION');?> <?=SaleFormatCurrency($params['PAYMENT_SHOULD_PAY'], $payment->getField('CURRENCY'));?>
	</span>
	<form name="ShopForm" action="<?=$params['URL'];?>" method="post">

		<input name="ShopID" value="<?=$params['YANDEX_SHOP_ID'];?>" type="hidden">
		<input name="scid" value="<?=$params['YANDEX_SCID'];?>" type="hidden">
		<input name="customerNumber" value="<?=$params['PAYMENT_BUYER_ID'];?>" type="hidden">
		<input name="orderNumber" value="<?=$params['PAYMENT_ID'];?>" type="hidden">
		<input name="Sum" value="<?=number_format($sum, 2, '.', '')?>" type="hidden">
		<input name="paymentType" value="<?=$params['PS_MODE']?>" type="hidden">
		<input name="cms_name" value="1C-Bitrix" type="hidden">
		<input name="BX_HANDLER" value="YANDEX_REFERRER" type="hidden">
		<input name="BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE']?>" type="hidden">

		<div class="sale-paysystem-yandex-button-container">
			<span class="sale-paysystem-yandex-button">
				<input class="sale-paysystem-yandex-button-item" name="BuyButton" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_BUTTON_PAID')?>" type="submit">
			</span><!--sale-paysystem-yandex-button-->
			<span class="sale-paysystem-yandex-button-descrition"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_REDIRECT_MESS');?></span><!--sale-paysystem-yandex-button-descrition-->
		</div><!--sale-paysystem-yandex-button-container-->

		<p>
			<span class="tablebodytext sale-paysystem-description"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_WARNING_RETURN');?></span>
		</p>
	</form>
</div><!--sale-paysystem-wrapper-->