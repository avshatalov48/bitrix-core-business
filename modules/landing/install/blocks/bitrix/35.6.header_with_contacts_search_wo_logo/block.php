<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
	isset($landing) ? $landing->getSiteId() : null
);
?>

<header class="landing-block g-bg-white g-pt-20 g-pb-20 g-brd-bottom g-brd-gray-light-v4">
	<div class="container">
		<div class="row flex-lg-row align-items-center justify-content-between">
			<div class="col-sm-9 text-center text-lg-left">
				<div class="row align-items-center">

					<div class="landing-block-node-card col-sm g-brd-right--sm g-brd-gray-light-v4 g-mb-15 g-mb-0--sm" data-card-preset="link">
						<div class="g-pa-10--lg">
							<div class="landing-block-node-card-icon-container d-lg-inline-block g-valign-top g-color-primary g-mr-5 g-font-size-18 g-line-height-1">
								<i class="landing-block-node-card-icon icon icon-screen-smartphone"></i>
							</div>
							<div class="landing-block-node-card-text-container d-inline-block">
								<div class="landing-block-node-card-title text-uppercase g-font-size-13">
									Call Us
								</div>
								<a class="landing-block-node-card-link g-color-primary g-font-size-14 g-font-weight-700"
								   href="tel:#PHONE1#"
								   target="_blank">
									#PHONE1#
								</a>
							</div>
						</div>
					</div>

					<div class="landing-block-node-card col-sm g-brd-right--sm g-brd-gray-light-v4 g-mb-15 g-mb-0--sm" data-card-preset="text">
						<div class="g-pa-10--lg">
							<div class="landing-block-node-card-icon-container d-lg-inline-block g-valign-top g-color-primary g-mr-5 g-font-size-18 g-line-height-1">
								<i class="landing-block-node-card-icon icon icon-clock"></i>
							</div>
							<div class="landing-block-node-card-text-container d-inline-block">
								<div class="landing-block-node-card-title text-uppercase g-font-size-13">
									Opening time
								</div>
								<div class="landing-block-node-card-text g-font-size-14 g-font-weight-700">
									Mon-Sat: 08.00 -18.00
								</div>
							</div>
						</div>
					</div>

					<div class="landing-block-node-card col-sm g-brd-right--sm g-brd-gray-light-v4 g-mb-15 g-mb-0--sm" data-card-preset="link">
						<div class="g-pa-10--lg">
							<div class="landing-block-node-card-icon-container d-lg-inline-block g-valign-top g-color-primary g-mr-5 g-font-size-18 g-line-height-1">
								<i class="landing-block-node-card-icon icon icon-envelope"></i>
							</div>
							<div class="landing-block-node-card-text-container d-inline-block">
								<div class="landing-block-node-card-title text-uppercase g-font-size-13">
									Email us
								</div>
								<a class="landing-block-node-card-link g-color-primary g-font-size-14 g-font-weight-700"
									 href="mailto:#EMAIL1#"
									 target="_blank">
									#EMAIL1#
								</a>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="col-sm-3">
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
</header>