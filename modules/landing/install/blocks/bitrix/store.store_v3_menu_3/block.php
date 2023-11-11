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
	<div class="container u-header__section u-header__section--light g-transition-0_3 g-py-30--lg g-py-20">
		<nav class="navbar g-pa-0 u-navbar-color-gray-dark-v1 u-navbar-color-gray-dark-v1--hover flex-nowrap">
			<div class="landing-block-node-title-container order-1 d-flex w-100 align-items-center g-overflow-hidden g-font-weight-700 g-letter-spacing-0_5 text-uppercase">
				<a
					class="landing-block-node-title g-font-size-20 g-font-size-25--lg g-line-height-1 g-text-decoration-none--hover g-nowrap g-text-overflow-ellipsis g-overflow-hidden"
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
						<svg width="23" height="26" viewBox="0 0 23 26" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21.8788 19.7736L17.179 16.8329C16.5289 16.4293 15.6456 16.53 15.1209 17.0794L12.7914 19.4409C10.8991 18.4875 9.58404 17.5695 8.18728 15.9211C6.66995 14.1535 6.05647 12.574 5.52274 10.7091L8.22728 8.93162C8.88117 8.5294 9.14929 7.68779 8.84652 6.98769L6.76012 1.96217C6.37898 1.07315 5.31634 0.713259 4.46307 1.16413L1.86092 2.81079C1.08624 3.21292 0.453981 4.26838 0.428576 5.10991C0.410237 5.97878 0.429376 7.10737 0.538986 7.88486C1.14409 11.4017 2.41454 15.0646 5.20669 18.3619C7.99769 21.6595 11.2426 23.604 14.653 24.839C15.4239 25.1239 16.536 25.3223 17.4203 25.4589C18.3045 25.5955 19.3751 25.3077 19.9204 24.6042L22.2431 22.1247C22.8965 21.3673 22.7077 20.2827 21.8788 19.7736Z" fill="#121212"/>
						</svg>
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
						// 'SHOW_AUTHOR' => 'Y',
						'SHOW_REGISTRATION' => 'N',
						'COMPONENT_TEMPLATE' => 'store_v3',
						'SHOW_EMPTY_VALUES' => 'N',
						'POSITION_HORIZONTAL' => $classBlock->get('CART_POSITION_HORIZONTAL'),
						'POSITION_VERTICAL' => $classBlock->get('CART_POSITION_VERTICAL'),
						'HIDE_ON_BASKET_PAGES' => 'N',
						'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
					];
					?>
					<div class="landing-block-node-basket-container d-none d-md-block g-brd-left g-brd-1 g-brd-black-opacity-0_1 g-pl-25">
						<?php $APPLICATION->IncludeComponent(
							'bitrix:sale.basket.basket.line',
							'store_v3_inline',
							$basketParams,
							false
						); ?>
					</div>
					<?php if(!$classBlock->get('IS_ORDER_PAGE')): ?>
						<div class="landing-block-node-basket-float-container d-block d-md-none">
							<?php $APPLICATION->IncludeComponent(
								'bitrix:sale.basket.basket.line',
								'store_v3',
								$basketParams,
								false
							); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</nav>
	</div>
</header>