<?
	use Bitrix\Main\Localization\Loc;
	\Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");
	Loc::loadMessages(__FILE__);

	$sum = roundEx($params['SUM'], 2);
?>

<div class="sale-paysystem-wrapper">
	<span class="tablebodytext">
		<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION')." ".SaleFormatCurrency($params['SUM'], $params['CURRENCY']);?>
	</span>
	<form action="" method="post"><br>
		<?foreach ($params['FIELDS'] as $field):?>
			<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?>: <input name="<?=$field;?>" value="" type="text">
		<?endforeach;?>

		<div class="sale-paysystem-yandex-button-container">
			<span class="sale-paysystem-yandex-button">
				<input class="sale-paysystem-yandex-button-item" name="BuyButton" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?>" type="submit">
			</span>
			<span class="sale-paysystem-yandex-button-descrition">
				<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_REDIRECT_MESS');?>
			</span>
		</div>
	</form>

	<p>
		<span class="tablebodytext sale-paysystem-description">
			<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?>
		</span>
	</p>
</div>