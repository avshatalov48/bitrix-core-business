<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
	isset($landing) ? $landing->getSiteId() : null
);
?>

<header class="landing-block g-bg-white g-pt-20 g-pb-20 g-brd-bottom g-brd-gray-light-v4 text-center text-lg-left">
	<div class="container">
		<div class="row flex-lg-row align-items-center justify-content-lg-start">
			<div class="col-12 col-sm-3 col-lg-2">
				<!-- Logo -->
				<a href="#system_mainpage"
				   class="landing-block-node-menu-logo-link navbar-brand g-mb-10 g-mb-0--sm g-mr-0">
					<img class="landing-block-node-logo img-fluid"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/real-estate-logo.png" alt="Logo">
				</a>
				<!-- End Logo -->
			</div>

			<div class="col-12 col-sm-9 col-lg-10">
				<div class="row g-ml-20--sm">
					<!--				contacts-->
					<div class="landing-block-card-menu-contact-container col-sm-8 col-md-9">
						<div class="landing-block-card-menu-contact-container-inner row">
							<div class="landing-block-node-card col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
								 data-card-preset="contact-link">

								<a href="tel:#crmPhone1"
								   class="landing-block-node-card-contactlink-link g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start g-text-decoration-none--hover">
							<span class="landing-block-node-card-icon-container text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
								<i class="landing-block-node-card-contactlink-icon icon icon-screen-smartphone"></i>
							</span>
									<span class="landing-block-node-card-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
								<span class="landing-block-node-menu-contactlink-title landing-block-node-card-title-style g-color-main d-block text-uppercase g-font-size-13">
									Call Us
								</span>
								<span class="landing-block-node-card-contactlink-text landing-block-node-card-text-style d-block g-color-gray-dark-v2 g-font-weight-700 g-text-decoration-none g-text-underline--hover">
									#crmPhoneTitle1
								</span>
							</span>
								</a>
							</div>

							<div class="landing-block-node-card col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
								 data-card-preset="contact-text">
								<div class="g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start">
									<div class="landing-block-node-card-icon-container text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
										<i class="landing-block-node-card-icon icon icon-clock"></i>
									</div>
									<div class="landing-block-node-card-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
										<div class="landing-block-node-card-title landing-block-node-card-title-style g-color-main text-uppercase g-font-size-13">
											Opening time
										</div>
										<div class="landing-block-node-card-text landing-block-node-card-text-style g-color-gray-dark-v2 g-font-weight-700">
											Mon-Sat: 08.00 -18.00
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!--				search-->
					<div class="landing-block-search-container col-sm-4 col-md-3 g-mb-10 g-mb-0--md">
						<? $APPLICATION->IncludeComponent("bitrix:search.title", "bootstrap_v4", array(
							"NUM_CATEGORIES" => "1",
							"TOP_COUNT" => "5",
							"CHECK_DATES" => "N",
							"SHOW_OTHERS" => "N",
							"PAGE" => "#system_catalog",
							"CATEGORY_0" => array(
								0 => "iblock_CRM_PRODUCT_CATALOG",
							),
							"CATEGORY_0_iblock_CRM_PRODUCT_CATALOG" => array(
								0 => $settings['IBLOCK_ID'],
							),
							"SHOW_INPUT" => "Y",
							"INPUT_ID" => "title-search-input",
							"CONTAINER_ID" => "search",
							"PRICE_CODE" => array(
								0 => "BASE",
							),
							"SHOW_PREVIEW" => "Y",
							"PREVIEW_WIDTH" => "75",
							"PREVIEW_HEIGHT" => "75",
							"CONVERT_CURRENCY" => "Y",
						),
							false
						); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>