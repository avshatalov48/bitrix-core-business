<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$editMode = \Bitrix\Landing\Landing::getEditMode();

if ($editMode)
{
	return;
}
else
{
	\Bitrix\Landing\Manager::getApplication()->restartBuffer();
}
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.payment",
	"",
	Array(
	)
);?>

<?
if (!$editMode)
{
	\CMain::finalActions();
	die();
}
?>