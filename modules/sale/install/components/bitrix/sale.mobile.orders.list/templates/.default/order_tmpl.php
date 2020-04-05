<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$orderTemplate = '
	<a href="##ORDER_DETAIL_LINK##">
		<div class="order_itemlist_item_title">
				<span>'.GetMessage('SMOL_N').'##ACCOUNT_NUMBER## '.GetMessage('SMOL_FROM').' ##DATE_INSERT##</span>
		</div>
		<div class="order_itemlist_item_content">##CONTENT##';

if($arResult["SHOP_SITES_COUNT"]>1)
	$orderTemplate .= '
			<div class="order_itemlist_item_idshop">##LID##</div>';

$orderTemplate .= '
		</div>
		<div class="order_itemlist_item_total">
			<div class="order_itemlist_item_price">##ADD_PRICE##</div>
			<div class="order_itemlist_item_itemcount">'.GetMessage('SMOL_PRODUCTS_COUNT').': ##ADD_PRODUCT_COUNT## </div>
		</div>
	</a>';

$orderTemplateCompleted = '
	<a href="##ORDER_DETAIL_LINK##">
		<div class="order_itemlist_item_title">
				<span>'.GetMessage('SMOL_N').'##ACCOUNT_NUMBER## '.GetMessage('SMOL_FROM').' ##DATE_INSERT##</span>
		</div>
		<div class="order_itemlist_item_total">
			<div class="order_itemlist_item_total_completed">'.GetMessage('SMOL_SUMM').': <span>##ADD_PRICE##</span></div>';

if($arResult["SHOP_SITES_COUNT"]>1)
	$orderTemplateCompleted .= '
			<div class="order_itemlist_item_idshop">##LID##</div>';

$orderTemplateCompleted .= '
		</div>
	</a>';
?>