<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global \CMain $APPLICATION
 * @var StoreV3Menu1 $classBlock
 */
?>
<header class="landing-block u-header u-header--sticky u-header--relative g-z-index-9999 g-height-auto g-bg-white u-shadow-v41--header--lg">
	<div class="container u-header__section u-header__section--light g-transition-0_3 g-py-30--lg g-py-10">
		<nav
			class="navbar u-navbar-modal u-navbar-slider g-pa-0 u-navbar-color-gray-dark-v1 u-navbar-color-gray-dark-v1--hover flex-nowrap"
			data-modal-alert-classes="d-none d-lg-block order-2 w-100"
		>
			<div class="landing-block-node-title-container order-1 d-flex w-100 align-items-center g-overflow-hidden g-font-weight-700 g-letter-spacing-0_5 text-uppercase">
				<a
					class="landing-block-node-title g-font-size-20 g-font-size-25--lg g-text-decoration-none--hover g-nowrap g-text-overflow-ellipsis g-overflow-hidden"
					href="#system_mainpage"
				>
					#crmCompanyTitle
				</a>
			</div>

			<div class="landing-block-node-buttons-container order-3 d-flex align-items-center">
				<a
					class="landing-block-node-phone g-px-25 d-flex align-items-center"
					href="tel:#crmPhoneTitle1"
					data-page-url="#system_mainpage"
				>
					<span class="d-block">
						<svg width="26" height="32" viewBox="0 0 26 32" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M21.6546 22.8512L17.564 20.2495C16.9982 19.8924 16.2293 19.9816 15.7726 20.4676L13.7451 22.5569C12.098 21.7134 10.9534 20.9013 9.73771 19.4428C8.41705 17.879 7.88308 16.4815 7.41852 14.8316L9.77252 13.259C10.3417 12.9032 10.575 12.1586 10.3115 11.5392L8.49553 7.09295C8.16379 6.30641 7.23888 5.988 6.49621 6.3869L4.23132 7.84375C3.55705 8.19952 3.00675 9.13332 2.98463 9.87785C2.96867 10.6466 2.98533 11.6451 3.08073 12.3329C3.60741 15.4444 4.71319 18.685 7.14344 21.6023C9.57269 24.5198 12.397 26.2401 15.3654 27.3328C16.0364 27.5848 17.0044 27.7604 17.774 27.8812C18.5436 28.0021 19.4755 27.7475 19.9501 27.125L21.9717 24.9313C22.5404 24.2613 22.3761 23.3017 21.6546 22.8512Z"
								fill="#121212"
							/>
						</svg>
					</span>
					<span class="landing-block-node-text d-none d-md-block g-font-size-17 g-font-weight-500 g-nowrap g-pl-10">
						#crmPhoneTitle1
					</span>
				</a>

				<?php if ($classBlock->get('SHOW_CART')): ?>
					<?php
					$basketParams = [
						'PATH_TO_BASKET' => '#system_order',
						'PATH_TO_ORDER' => '#system_order',
						'PATH_TO_PERSONAL' => '#system_personal',
						'PATH_TO_REGISTER' => '',
						'PATH_TO_PROFILE' => '#system_personal',
						'PATH_TO_AUTHORIZE' => '#system_personal?SECTION=private',
						'SHOW_PERSONAL_LINK' => 'N',
						'SHOW_NUM_PRODUCTS' => 'Y',
						'SHOW_TOTAL_PRICE' => 'Y',
						'SHOW_PRODUCTS' => 'N',
						'POSITION_FIXED' => 'Y',
						'SHOW_AUTHOR' => $classBlock->get('SHOW_PERSONAL_LINK'),
						'SHOW_REGISTRATION' => 'N',
						'COMPONENT_TEMPLATE' => 'store_v3',
						'SHOW_EMPTY_VALUES' => 'N',
						'POSITION_HORIZONTAL' => $classBlock->get('CART_POSITION_HORIZONTAL'),
						'POSITION_VERTICAL' => $classBlock->get('CART_POSITION_VERTICAL'),
						'HIDE_ON_BASKET_PAGES' => 'N',
						'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
					];
					?>
					<div class="landing-block-node-basket-container d-none d-lg-block g-brd-left g-brd-1 g-brd-black-opacity-0_1 g-pl-25">
						<?php $APPLICATION->IncludeComponent(
							'bitrix:sale.basket.basket.line',
							'store_v3_inline',
							$basketParams,
							false
						); ?>
					</div>
					<?php if(!$classBlock->get('IS_ORDER_PAGE')): ?>
						<div class="landing-block-node-basket-float-container d-block d-lg-none">
							<?php $APPLICATION->IncludeComponent(
								'bitrix:sale.basket.basket.line',
								'store_v3',
								$basketParams,
								false
							); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<button
					class="navbar-toggler btn collapsed g-line-height-1 g-brd-none g-pa-0 d-block d-lg-none"
					type="button"
					aria-label="Toggle navigation"
					aria-expanded="false"
					aria-controls="navBar"
					data-toggle="collapse"
					data-target="#navBar"
				>
					<div class="ml-auto">
						<span class="hamburger hamburger--rounded">
							<span class="hamburger-box">
								<span class="hamburger-inner"></span>
							</span>
						</span>
					</div>
				</button>
			</div>

			<div class="collapse navbar-collapse d-lg-none g-bg-gray-light-v3 g-z-index-1 g-max-width-100x" id="navBar">
				<ul class="landing-block-node-menu-top catalog-sections-list-menu-items">
					<li class="landing-block-node-menu-top-item catalog-sections-list-menu-item nav-item">
						<a
							class="landing-block-node-menu-top-link catalog-sections-list-menu-item-link catalog-sections-list-menu-item-text"
							href="#system_mainpage"
						>
							Main page
						</a>
					</li>
				</ul>

				<?php $APPLICATION->IncludeComponent(
					"bitrix:catalog.section.list",
					"store_v3_menu",
					[
						'IBLOCK_TYPE' => '',
						'IBLOCK_ID' => $classBlock->get('IBLOCK_ID'),
						'SECTION_ID' => $classBlock->get('SECTION_ID'),
						'SECTION_URL' => '#system_catalog#SECTION_CODE_PATH#/',
						'COUNT_ELEMENTS' => 'Y',
						'ADDITIONAL_COUNT_ELEMENTS_FILTER' => $classBlock->get('ADDITIONAL_COUNT_ELEMENTS_FILTER'),
						'HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS' => $classBlock->get('HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'),
						'TOP_DEPTH' => '1',
						'CACHE_GROUPS' => 'Y',
						'CACHE_TIME' => '36000000',
						'CACHE_TYPE' => 'A',
						'ADD_SECTIONS_CHAIN' => 'N',
					]
				); ?>
				<ul class="landing-block-node-menu-bottom navbar-nav g-py-10">
					<li class="landing-block-node-menu-bottom-item nav-item g-pl-17 g-pr-17 g-py-9">
						<a
							class="landing-block-node-menu-bottom-link d-flex justify-content-between align-items-center g-text-decoration-none--hover g-font-size-16 g-opacity-0_6"
							href="#"
						>
							<span class="landing-block-node-menu-bottom-text">Business Card</span>
							<span class="catalog-sections-list-menu-item-angle"></span>
						</a>
					</li>
					<li class="landing-block-node-menu-bottom-item nav-item g-pl-17 g-pr-17 g-py-9">
						<a
							class="landing-block-node-menu-bottom-link d-flex justify-content-between align-items-center g-text-decoration-none--hover g-font-size-16 g-opacity-0_6"
							href="#"
						>
							<span class="landing-block-node-menu-bottom-text">Payment Options</span>
							<span class="landing-block-node-menu-icon catalog-sections-list-menu-item-angle"></span>
						</a>
					</li>
				</ul>
				<div class="u-navbar-slider--close">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							fill-rule="evenodd"
							clip-rule="evenodd"
							d="M2.32279 0.990234L0.719971 2.59305L5.57554 7.44862L0.719992 12.3042L2.32281 13.907L7.17836 9.05144L12.0337 13.9068L13.6365 12.3039L8.78117 7.44862L13.6365 2.59328L12.0337 0.990465L7.17836 5.84581L2.32279 0.990234Z"
							fill="white"
						/>
					</svg>
				</div>
			</div>
		</nav>
	</div>
</header>