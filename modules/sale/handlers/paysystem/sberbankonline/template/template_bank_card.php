<?
use Bitrix\Main\Page\Asset,
	Bitrix\Main\Localization\Loc;

Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");

Loc::loadMessages(__FILE__);

$sum = roundEx($params['SUM'], 2);
?>
<div class="mb-4" >
	<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_DESCRIPTION')." ".SaleFormatCurrency($params['SUM'], $params['CURRENCY']);?></p>
	<form action="<?=$params['URL']?>" method="GET">
		<? 	if (isset($params['FORM_PARAMS']))
			{
				foreach ($params['FORM_PARAMS'] as $param => $value)
				{
					?><input type="hidden" name="<?=$param?>" value="<?=$value?>"><?
				}
			}
		?>
		<div class="d-flex align-items-center justify-content-start">
			<input class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" name="registerOrder" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BUTTON_PAID')?>" type="submit">
			<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_REDIRECT');?></p>
		</div>

	</form>
	<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_WARNING_RETURN');?></p>
</div>