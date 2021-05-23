<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$orderTemplate = '
	<a href="##ORDER_DETAIL_LINK##">
		<div class="order_itemlist_item_title">
			<div class="order_itemlist_item_title_container">
				<div class="order_itemlist_item_title_number">'.GetMessage('SMOL_N').'##ACCOUNT_NUMBER## </div>
				<div class="order_itemlist_item_title_date">'.GetMessage('SMOL_FROM').' ##DATE_INSERT_SHORT##</div>
			</div>
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
			<div class="order_itemlist_item_title_container">
				<div class="order_itemlist_item_title_number">'.GetMessage('SMOL_N').'##ACCOUNT_NUMBER## </div>
				<div class="order_itemlist_item_title_date">'.GetMessage('SMOL_FROM').' ##DATE_INSERT_SHORT##</div>
			</div>
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