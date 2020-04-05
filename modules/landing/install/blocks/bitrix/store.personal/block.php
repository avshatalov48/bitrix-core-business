<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StorePersonalBlock $classBlock
 */
?>
<section class='landing-block g-pt-100 g-pb-100'>
	<div class='container g-font-size-13'>
		<?$APPLICATION->IncludeComponent(
			'bitrix:sale.personal.section',
			'bootstrap_v4',
			array(
				'ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS' => array(
				),
				'ACCOUNT_PAYMENT_PERSON_TYPE' => '',
				'ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES' => 'Y',
				'ACCOUNT_PAYMENT_SELL_TOTAL' => array(
					0 => '100',
					1 => '200',
					2 => '500',
					3 => '1000',
					4 => '5000',
				),
				'ACCOUNT_PAYMENT_SELL_USER_INPUT' => 'Y',
				'CACHE_GROUPS' => 'N',
				'CACHE_TIME' => '1296000',
				'CACHE_TYPE' => 'A',
				'CHECK_RIGHTS_PRIVATE' => 'N',
				'COMPATIBLE_LOCATION_MODE_PROFILE' => 'N',
				'CUSTOM_PAGES' => '',
				'CUSTOM_SELECT_PROPS' => array(
				),
				'NAV_TEMPLATE' => '',
				'ORDER_HISTORIC_STATUSES' => array(
					0 => 'F',
				),
				'PATH_TO_BASKET' => '#system_cart',
				'PATH_TO_CATALOG' => '#system_catalog',
				'PER_PAGE' => '20',
				'PROP_1' => array(
				),
				'PROP_2' => array(
				),
				'SAVE_IN_SESSION' => 'Y',
				'SEF_MODE' => 'N',
				'SEND_INFO_PRIVATE' => 'N',
				'SET_TITLE' => 'Y',
				'SHOW_ACCOUNT_COMPONENT' => 'Y',
				'SHOW_ACCOUNT_PAGE' => 'Y',
				'SHOW_ACCOUNT_PAY_COMPONENT' => 'Y',
				'SHOW_BASKET_PAGE' => 'Y',
				'SHOW_CONTACT_PAGE' => 'N',
				'SHOW_ORDER_PAGE' => 'Y',
				'SHOW_PRIVATE_PAGE' => 'Y',
				'SHOW_PROFILE_PAGE' => 'Y',
				'SHOW_SUBSCRIBE_PAGE' => 'Y',
				'USER_PROPERTY_PRIVATE' => '',
				'USE_AJAX_LOCATIONS_PROFILE' => 'N',
				'COMPONENT_TEMPLATE' => 'bootstrap_v4',
				'ORDER_HIDE_USER_INFO' => array(
				),
				'ORDER_RESTRICT_CHANGE_PAYSYSTEM' => array(
				),
				'ORDER_DEFAULT_SORT' => 'STATUS',
				'ORDER_REFRESH_PRICES' => 'N',
				'ALLOW_INNER' => 'N',
				'ONLY_INNER_FULL' => 'N',
				'ORDERS_PER_PAGE' => '20',
				'PROFILES_PER_PAGE' => '20',
				'PATH_TO_PAYMENT' => '#system_payment',
				'SUBSCRIBE_DETAIL_URL' => '#system_catalogitem/#ELEMENT_CODE#/',
				'EDITABLE_EXTERNAL_AUTH_ID' => ['shop'],
				'USE_PRIVATE_PAGE_TO_AUTH' => 'Y',
				'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID')
			),
			false
		);?>
	</div>
</section>
