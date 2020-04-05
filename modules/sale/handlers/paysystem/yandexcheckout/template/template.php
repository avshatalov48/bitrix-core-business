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
	<div class="sale-paysystem-yandex-button-container">
		<span class="sale-paysystem-yandex-button">
			<a class="sale-paysystem-yandex-button-item sale-paysystem-yandex-checkout-button-item" href="<?=$params['URL'];?>">
				<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?>
			</a>
		</span>
		<span class="sale-paysystem-yandex-button-descrition"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_REDIRECT_MESS');?></span>
	</div>

	<p>
		<span class="tablebodytext sale-paysystem-description">
			<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?>
		</span>
	</p>
</div>