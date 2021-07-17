<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_VALENTINE1_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_VALENTINE1_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'travel',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.valentine1/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_VALENTINE1_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_VALENTINE1_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_VALENTINE1_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_VALENTINE1_DESCRIPTION')
		]
	],
	'available' => true,
	'items' => [
		'0.menu_18_spa' =>
			[
				'CODE' => '0.menu_18_spa',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float g-z-index-9999">
	<div class="u-header__section g-bg-black-opacity-0_5 g-bg-transparent--lg g-transition-0_3 g-py-6 g-py-14--md"
		 data-header-fix-moment-exclude="g-bg-black-opacity-0_5 g-bg-transparent--lg g-py-14--md"
		 data-header-fix-moment-classes="u-header__section--light u-shadow-v27 g-bg-white g-py-11--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0" target="_self">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main d-block g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-white.png" alt="" data-header-fix-moment-exclude="d-block" data-header-fix-moment-classes="d-none" data-filehash="156980667d0d360623805e29a394c71e" />

					<img class="landing-block-node-menu-logo2 u-header__logo-img u-header__logo-img--main d-none g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-black.png" alt="" data-header-fix-moment-exclude="d-none" data-header-fix-moment-classes="d-block" data-filehash="a340bf4dd98ca5c73c3e40316e3c36c0" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-12--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_3]" class="landing-block-node-menu-list-item-link nav-link p-0">Home</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[43.1.big_tiles_with_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">About us</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Products</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[06.2.features_4_cols]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Specials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-12--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[46.3.cover_with_blocks_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-12--lg">
							<a href="#block@block[47.1.title_with_icon@3]" class="landing-block-node-menu-list-item-link nav-link p-0">Contact us</a>
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
</header>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img4.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-font-size-90 g-text-transform-none g-color-white"><span style="font-style: italic;">Happy Valentine&amp;#039;s day!</span></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">Make a gift to your sweetheart!</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15" target="_self">view more</a>
		</div>
	</div>
</section>',
			],
		'43.1.big_tiles_with_slider' =>
			[
				'CODE' => '43.1.big_tiles_with_slider',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block">
	<div class="container-fluid px-0">
		<div class="row no-gutters">
			<div class="landing-block-node-img1 col-md-6 g-bg-img-hero g-min-height-400 js-animation fadeInLeft" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x781/img6.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>

			<div class="landing-block-node-block-top js-animation fadeInRight col-md-6 d-flex align-items-center text-center g-pa-50">
				<div class="w-100">
					<div class="g-mb-25">
						<h4 class="landing-block-node-subtitle g-font-weight-700 g-color-primary g-mb-25 g-font-size-35"><span style="font-style: italic;">
							About us</span></h4>
						<h2 class="landing-block-node-title g-font-weight-600 mb-0 g-text-transform-none g-font-size-30"><span style="font-style: italic;">
							Perfect place to find present!</span></h2>
					</div>

					<div class="landing-block-node-text g-mb-35">
						<p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim
							justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p>
					</div>
					<div class="landing-block-node-button-container">
						<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#" target="_self">View more</a>
					</div>
				</div>
			</div>
		</div>

		<div class="row no-gutters">
			<div class="landing-block-node-block-bottom js-animation fadeInUp col-md-6 d-flex align-items-center g-max-height-300--md g-max-height-625--lg text-center g-overflow-hidden">
				<img class="landing-block-node-img2 w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x781/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
			</div>

			<div class="landing-block-node-block-bottom js-animation fadeInUp col-md-6">
				<div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-55 g-font-size-12 g-bg-white g-mt-minus-10" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">
					<div class="landing-block-node-card js-slide d-flex align-items-center g-max-height-300 g-max-height-625--lg">
						<img class="landing-block-node-card-img w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x781/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>

					<div class="landing-block-node-card js-slide d-flex align-items-center g-max-height-300 g-max-height-625--lg">
						<img class="landing-block-node-card-img w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x781/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>

					
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'47.1.title_with_icon' =>
			[
				'CODE' => '47.1.title_with_icon',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-color-primary g-font-size-90 js-animation fadeInUp">Small gifts</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			],
		'06.2.features_4_cols' =>
			[
				'CODE' => '06.2.features_4_cols',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pb-80 g-pt-20">
        <div class="container">

            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">
                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-star"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none"><span style="font-style: italic;">Gift set</span></h5>
                        <div class="landing-block-node-element-text"><p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Candy set<br /></li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">flowers</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">plush </li></ul>
                    </div>
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-heart-o"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none"><span style="font-style: italic;">Gift set</span></h5>
                        <div class="landing-block-node-element-text"><p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li></ul>
                    </div>
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-diamond"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none"><span style="font-style: italic;">Gift set</span></h5>
                        <div class="landing-block-node-element-text"><p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li></ul>
                    </div>
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-present"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none"><span style="font-style: italic;">Gift set</span></h5>
                        <div class="landing-block-node-element-text"><p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li></ul>
                    </div>
                </div>
            </div>
        </div>
    </section>',
			],
		'01.big_with_text_3@2' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img5.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-text-transform-none g-color-primary g-font-size-85"><span style="font-style: italic;color: rgb(245, 245, 245);">
			The power of Love</span></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">
			Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.
		</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15" target="_self">View more</a>
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
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-color-primary g-font-size-90 js-animation fadeInUp">Gift sets</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			],
		'44.7.three_columns_with_img_and_price' =>
			[
				'CODE' => '44.7.three_columns_with_img_and_price',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-65 g-pb-65">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article
 class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">Romantic Dinner</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10">
								$350.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#">Order Now</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article
></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article
 class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">Romantic Candy set</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10">
								$600.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#">Order Now</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article
></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article
 class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">Romantic Flower set</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img6.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10">
								$200.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-50" href="#">Order Now</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article
></div>
		</div>
	</div>
</section>',
			],
		'46.3.cover_with_blocks_slider' =>
			[
				'CODE' => '46.3.cover_with_blocks_slider',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-100 g-pb-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img29.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container u-bg-overlay__inner">
		<div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--y--md g-top-100x g-top-50x--md g-width-50 g-height-50 g-rounded-50x g-font-size-12 g-color-white g-bg-white-opacity-0_4 g-bg-white-opacity-0_7--hover g-mt-30 g-mt-0--md" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">
			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x315/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig">Monica</h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">
							March
							15, 2017
						</div>
						<blockquote class="landing-block-node-card-text g-mb-40 js-animation fadeInRightBig">
							<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>
						</blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">View more</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x315/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig">Sofia</h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">
							November
							01, 2017
						</div>
						<blockquote class="landing-block-node-card-text g-mb-40 js-animation fadeInRightBig">
							<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>
						</blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">View more</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block-->
			</div>
		<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x315/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig">Linda</h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">
							November
							01, 2017
						</div>
						<blockquote class="landing-block-node-card-text g-mb-40 js-animation fadeInRightBig">
							<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>
						</blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50 js-animation fadeInRightBig" href="#" target="_self">View more</a>
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
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-color-primary g-font-size-90 js-animation fadeInUp">Get in touch</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			],
		'33.3.form_1_transparent_black_no_text' =>
			[
				'CODE' => '33.3.form_1_transparent_black_no_text',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-bg-primary-dark-v1 g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img30.jpg);">

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