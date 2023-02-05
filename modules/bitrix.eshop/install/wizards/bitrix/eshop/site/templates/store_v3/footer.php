<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
</div>
</div>
</div>
	</section>
</div>
<footer>
	<section class="footer-container">
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu",
			"store_v3_bottom",
			Array(
				"ROOT_MENU_TYPE" => "top",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"CACHE_SELECTED_ITEMS" => "N",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "3",
				"CHILD_MENU_TYPE" => "top",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
				"COMPONENT_TEMPLATE" => "bootstrap_v4"
			),
			false
		);?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:eshop.socnet.links",
			"store_v3",
			array(
				"FACEBOOK" => "https://www.facebook.com/1CBitrix",
				"VKONTAKTE" => "https://vk.com/bitrix_1c",
				"TWITTER" => "https://twitter.com/1c_bitrix",
				"INSTAGRAM" => "https://instagram.com/1CBitrix/"
			)
		);?>
		<div class="text-center text-muted pb-3">Call to us</div>
		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.basket.basket.line",
			"store_v3",
			array(
				"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
				"PATH_TO_PERSONAL" => SITE_DIR."personal/",
				"SHOW_PERSONAL_LINK" => "N",
				"SHOW_NUM_PRODUCTS" => "Y",
				"SHOW_TOTAL_PRICE" => "N",
				"SHOW_PRODUCTS" => "N",
				"POSITION_FIXED" =>"Y",
				"POSITION_HORIZONTAL" => "left",
				"POSITION_VERTICAL" => "bottom",
				"SHOW_AUTHOR" => "N",
				"PATH_TO_REGISTER" => SITE_DIR."login/",
				"PATH_TO_PROFILE" => SITE_DIR."personal/"
			),
			false
		);?>

	</section>
</footer>
</body>
</html>
