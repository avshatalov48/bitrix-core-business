<?
require($_SERVER["DOCUMENT_ROOT"]."#SITE_DIR#eshop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetPageProperty("BodyClass", "detail");
$arParams = array(
	"ORDER_DETAIL_PATH" => SITE_DIR.'eshop_app/personal/order/order_detail.php'
	);

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm("Для просмотра списка заказов, необходимо авторизоваться");
}
if ($USER->IsAuthorized())
	$arParams["FILTER"] = array("USER_ID" => $USER->GetID());
	
$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.orders.list',
	'.default',
	$arParams,
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>