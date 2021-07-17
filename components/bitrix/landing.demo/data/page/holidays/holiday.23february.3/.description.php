<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(dirname(__FILE__).'/.description-nottranslate.php');

return [
	'name' => Loc::getMessage('LANDING_DEMO_23FEB3_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_23FEB3_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'spa',
		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.23february.3/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_23FEB3_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_23FEB3_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_23FEB3_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_23FEB3_DESCRIPTION')
		]
	],
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => ['ru', 'kz', 'by'],
		'EXCEPT' => []
	]),
	'items' => [
		'0.menu_18_spa' =>
			[
				'CODE' => '0.menu_18_spa',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float g-z-index-9999">'.'
	<div class="u-header__section g-bg-black-opacity-0_5 g-bg-transparent--lg g-transition-0_3 g-py-6 g-py-14--md"
		 data-header-fix-moment-exclude="g-bg-black-opacity-0_5 g-bg-transparent--lg g-py-14--md"
		 data-header-fix-moment-classes="u-header__section--light u-shadow-v27 g-bg-white g-py-11--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main d-block g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/spa-logo-light.png" alt="" data-header-fix-moment-exclude="d-block" data-header-fix-moment-classes="d-none" />

					<img class="landing-block-node-menu-logo2 u-header__logo-img u-header__logo-img--main d-none g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/spa-logo-dark.png" alt="" data-header-fix-moment-exclude="d-none" data-header-fix-moment-classes="d-block" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-12--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT1").'</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[02.three_cols_big_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT2").'</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT3").'</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[46.3.cover_with_blocks_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT4").'</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT").'</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto g-flex-centered-item--center" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
                <span class="hamburger hamburger--slider">
                  <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                  </span>
                </span>
				</button>
				<!-- End Responsive Toggle Button -->
			</div>
		</nav>
	</div>
'.'</header>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img7.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-text-transform-none g-color-white g-font-size-75">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT").'</h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT7").'</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT8").'</a>
		</div>
	</div>
</section>',
			],
		'02.three_cols_big_3' =>
			[
				'CODE' => '02.three_cols_big_3',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters">

            <div class="landing-block-node-left col-md-6 col-lg-4 g-theme-business-bg-blue-dark-v2">
                <div class="js-carousel g-pb-90" data-infinite="true" data-slides-show="1" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    <div class="js-slide landing-block-card-left">
                        <img class="landing-block-node-left-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1600/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-left-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-18 js-animation fadeIn" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT9").'</h3>
                            <div class="landing-block-node-left-text g-color-gray-light-v2 js-animation fadeIn"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT10").'</p></div>'.'
                        '.'</div>
                    </div>
                </div>
            </div>

            <div class="landing-block-node-center col-md-6 col-lg-4 g-flex-centered g-theme-business-bg-blue-dark-v1">
                <div class="text-center g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-center-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 js-animation fadeIn">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT11").'</h6>
                        <h2 class="landing-block-node-center-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-color-white g-mb-minus-10 g-font-size-46 js-animation fadeIn">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT12").'</h2>
                    </div>
                    <div class="landing-block-node-center-text js-animation fadeIn">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT13").'</div>
                </div>
            </div>

            <div class="landing-block-node-right-img g-min-height-300 col-lg-4 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1920/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>

        </div>
    </section>',
			],
		'47.1.title_with_icon' =>
			[
				'CODE' => '47.1.title_with_icon',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pb-20 g-pt-80">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-color-primary g-font-size-60 js-animation fadeInUp"><span style="color: rgb(33, 33, 33); font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT14").'</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp"><p><span style="font-size: 1rem;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT15").'</span><span style="font-size: 1rem;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT16").'</span></p></div>
	</div>
</section>',
			],
		'06.1features_3_cols' =>
			[
				'CODE' => '06.1features_3_cols',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-present"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT17").'</h5>
                        <div class="landing-block-node-element-text"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT18").'</p></div>
                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>
                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT19").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT20").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT21").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT22").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT23").'</li>
                            <li class="landing-block-node-element-list-item g-py-8">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT24").'</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-trophy"></i>
                	</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT25").'</h5>
                        <div class="landing-block-node-element-text"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT26").'</p></div>
                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>
                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT27").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT28").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT29").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT30").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT31").'</li>
                            <li class="landing-block-node-element-list-item g-py-8">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT32").'</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-flag"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT33").'</h5>
                        <div class="landing-block-node-element-text"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT34").'</p></div>
                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>
                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT35").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT36").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT37").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT38").'</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT39").'</li>
                            <li class="landing-block-node-element-list-item g-py-8">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT40").'</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			],
		'01.big_with_text_3@2' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img6.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-text-transform-none g-color-primary g-font-size-75"><span style="color: rgb(245, 245, 245);">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT41").'</span></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT42").'</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT43").'</a>
		</div>
	</div>
</section>',
			],
		'47.1.title_with_icon@2' =>
			[
				'CODE' => '47.1.title_with_icon',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-font-size-60 js-animation fadeInUp"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT44").'<br /></span></h2>
			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
			</div>
		</div>
		<div class="landing-block-node-text mb-0 js-animation fadeInUp"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT45").'</p></div>
	</div>
</section>',
			],
		'44.7.three_columns_with_img_and_price' =>
			[
				'CODE' => '44.7.three_columns_with_img_and_price',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-65 g-pb-65">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-size-24"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT46").'<br /></span></h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT47").'</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/350x230/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT48").'</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10"><span style="color: rgb(33, 33, 33);">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT49").'</span></div>
						</div>

						<div class="landing-block-node-card-text g-mb-40"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT50").'</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT51").'</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-size-24"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT52").'</span></h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT53").'</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/350x230/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT54").'</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10"><span style="color: rgb(33, 33, 33);">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT55").'</span></div>
						</div>
						<div class="landing-block-node-card-text g-mb-40"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT56").'</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT57").'</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-size-24"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT58").'</span></h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT59").'</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/350x230/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT60").'</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10"><span style="color: rgb(33, 33, 33);">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT61").'</span></div>
						</div>
						<div class="landing-block-node-card-text g-mb-40"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT62").'</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT63").'</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>
		</div>
	</div>
</section>',
			],
		'46.3.cover_with_blocks_slider' =>
			[
				'CODE' => '46.3.cover_with_blocks_slider',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-100 g-pb-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img31.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container u-bg-overlay__inner">
		<div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--y--md g-top-100x g-top-50x--md g-width-50 g-height-50 g-rounded-50x g-font-size-12 g-color-white g-bg-white-opacity-0_4 g-bg-white-opacity-0_7--hover g-mt-30 g-mt-0--md" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">
			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img14.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT64").'</span></h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT65").'</div>
						<blockquote class="landing-block-node-card-text g-color-gray-light-v1 g-mb-40 js-animation fadeInRightBig"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT66").'</p></blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT67").'</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img13.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT68").'</span></h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT69").'</div>
						<blockquote class="landing-block-node-card-text g-color-gray-light-v1 g-mb-40 js-animation fadeInRightBig"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT70").'</p></blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT71").'</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block-->
			</div>
		<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img15.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig"><span style="font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT72").'</span></h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT73").'</div>
						<blockquote class="landing-block-node-card-text g-color-gray-light-v1 g-mb-40 js-animation fadeInRightBig"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT74").'</p></blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT75").'</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block-->
			</div></div>
	</div>
</section>',
			],
		'47.1.title_with_icon@3' =>
			[
				'CODE' => '47.1.title_with_icon',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-color-primary g-font-size-60 js-animation fadeInUp"><span style="color: rgb(33, 33, 33); font-style: normal;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT76").'</span></h2>
			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-star"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-star"></i>
			</div>
		</div>
		<div class="landing-block-node-text mb-0 js-animation fadeInUp"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB3_TEXT77").'</p></div>
	</div>
</section>',
			],
		'33.3.form_1_transparent_black_no_text' =>
			[
				'CODE' => '33.3.form_1_transparent_black_no_text',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-bg-primary-dark-v1 g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img32.jpg);">

	<div class="container g-z-index-1 g-pos-rel">
		<div class="row align-items-center">

			<div class="col-12 col-md-10 col-lg-8 mx-auto">
				<div class="bitrix24forms g-brd-none g-brd-around--sm g-brd-white-opacity-0_6 g-px-0 g-px-20--sm g-px-45--lg g-py-0 g-py-30--sm g-py-60--lg u-form-alert-v1"
					data-b24form-use-style="Y"
					data-b24form-embed
					data-b24form-design=\'{"dark":true,"style":"modern","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#fff","background":"#00000000","fieldBorder":"#fff","fieldBackground":"#ffffff00","fieldFocusBackground":"#ffffff00"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'
				>
				</div>
			</div>

		</div>
	</div>
</section>',
			],
	],
];