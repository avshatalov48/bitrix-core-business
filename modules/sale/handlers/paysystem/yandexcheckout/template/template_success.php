<?
	use Bitrix\Main\Localization\Loc;
	\Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");
	Loc::loadMessages(__FILE__);
?>

<div class="sale-paysystem-wrapper">
	<span class="tablebodytext">
		<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_EXT_SUCCESS');?>
	</span>
</div>