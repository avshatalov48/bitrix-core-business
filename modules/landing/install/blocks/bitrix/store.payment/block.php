<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (\Bitrix\Landing\Landing::getEditMode())
{
	echo '<div class="g-min-height-200 g-flex-centered"></div>';
	return;
}
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.payment",
	"",
	Array(
	)
);?>

<?die();?>