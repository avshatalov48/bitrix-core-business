<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_8MARCH3_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_8MARCH3_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'wedding',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.8march3/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_8MARCH3_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_8MARCH3_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_8MARCH3_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_8MARCH3_DESCRIPTION'),
		]
	],
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => ['ru', 'kz', 'by', 'ua'],
		'EXCEPT' => []
	]),
	'items' => [
		'0.menu_12_gym' =>
			[
				'CODE' => '0.menu_12_gym',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float g-z-index-9999">
	<div class="u-header__section g-bg-black-opacity-0_3 g-transition-0_3 g-py-7 g-py-23--md"
		 data-header-fix-moment-exclude="g-bg-black-opacity-0_3 g-py-23--md"
		 data-header-fix-moment-classes="g-bg-black-opacity-0_7 g-py-17--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/gym-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-2 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Home</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[09.1.two_cols_fix_text_and_image_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">new items</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">testimonials</a>
						</li>
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Contact</a>
						</li>
						
						
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-12 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
                <span class="hamburger hamburger--slider hamburger--md">
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
		'01.big_with_text_blocks' =>
			[
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="10000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1265/img5.jpg\');" data-fileid="-1">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig g-mx-0">
					<h2 class="landing-block-node-card-title g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-85"><span style="font-style: italic;">Happy woman day!<br /></span></h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35 g-text-transform-none"><p>Best perfume and cosmetics in out store!</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-rounded-50 g-py-15" href="#" tabindex="-1" target="_self">view more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		
		

		

	</div>
</section>',
			],
		'21.3.two_cols_big_bgimg_title_text_button' =>
			[
				'CODE' => '21.3.two_cols_big_bgimg_title_text_button',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img9.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-75 js-animation fadeIn"><span style="font-style: italic;">Perfume<br /></span></h3>
						<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 g-color-primary js-animation fadeIn" href="#" target="_self">view more</a>
						</div>
					</div>
				</div>
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-img-hero row no-gutters align-items-center justify-content-center g-bg-black-opacity-0_6--after u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img10.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-75 js-animation fadeIn"><span style="font-style: italic;">Make up</span></h3>
						<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 g-color-primary js-animation fadeIn" href="#" target="_self">view more</a>
						</div>
					</div>
				</div>

        </div>
    </section>',
			],
		'04.1.one_col_fix_with_title' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-container g-pt-50 g-pb-0 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">what we offer</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-text-transform-none g-font-size-65"><span style="font-style: italic;">Gifts for any choice!</span></h2>
            </div>
        </div>
    </section>',
			],
		'06.1features_3_cols' =>
			[
				'CODE' => '06.1features_3_cols',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-py-80 g-pt-0 g-pb-80">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">

                

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-medical-022 u-line-icon-pro"></i>
                	</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10 text-center g-letter-spacing-2">perfume</h5>
                        <div class="landing-block-node-element-text text-center"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-media-119 u-line-icon-pro"></i>
                	</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10 text-center g-letter-spacing-2">make up</h5>
                        <div class="landing-block-node-element-text text-center"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div><div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-science-100 u-line-icon-pro"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10 text-center g-letter-spacing-2">nail art</h5>
                        <div class="landing-block-node-element-text text-center"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			],
		'31.2.two_cols_img_text' =>
			[
				'CODE' => '31.2.two_cols_img_text',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-theme-photography-bg-gray-dark-v4">
	<div>
		<div class="row mx-0">
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img6.jpg\');" data-fileid="-1"></div>
			
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title g-font-weight-700 g-color-white g-mb-25 g-text-transform-none g-font-size-46 js-animation fadeInUp"><span style="font-weight: normal;font-style: italic;">Only in our store!</span></h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-light-v4 js-animation fadeInUp"><p><span style="font-size: 1rem;">Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. </span><br /></p><p>Praesent malesuada facilisis maximus.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary g-rounded-50 js-animation fadeInUp" href="#" tabindex="0" target="_self">View more</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'09.1.two_cols_fix_text_and_image_slider' =>
			[
				'CODE' => '09.1.two_cols_fix_text_and_image_slider',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-115 g-pt-80 g-pb-80">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 g-mb-40 g-mb-0--lg landing-block-node-text-container js-animation fadeInLeft">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">new items</h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="font-style: italic;">Don&amp;#039;t miss!</span></h2>
                    </div>

					<div class="landing-block-node-text"><p>Praesent ut ante congue, volutpat urna at, lacinia quam. Nulla non massa eget ante gravida tincidunt non eu quam. Proin in varius leo placerat mi vulputate suscipit<br /><br />Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. </p><p>Praesent malesuada facilisis maximus.</p></div>
                </div>

                <div class="col-lg-8 landing-block-node-carousel-container js-animation fadeInRight">
                    <div class="landing-block-node-carousel js-carousel g-line-height-0"
                         data-infinite="true"
                         data-speed="5000"
                         data-rows="2"
                         data-slides-show="2"
                         data-arrows-classes="u-arrow-v1 g-pos-abs g-bottom-100x g-right-0 g-width-35 g-height-35 g-color-gray g-color-white--hover g-bg-gray-light-v5 g-bg-primary--hover g-mb-5 g-transition-0_2 g-transition--ease-in"
                         data-arrow-left-classes="fa fa-angle-left g-mr-50"
                         data-arrow-right-classes="fa fa-angle-right g-mr-5"
						 data-responsive=\'[{
							 "breakpoint": 1200,
							 "settings": {
							   "slidesToShow": 2
							 }
						   }, {
							 "breakpoint": 768,
							 "settings": {
							   "slidesToShow": 1
							 }
						   }]\'>
                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img17.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>
						
                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img19.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img18.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img20.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img17.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img19.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img18.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img20.jpg" alt="" data-fileid="-1" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">NEW ITEMS!</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>',
			],
		'21.1.four_cols_big_bgimg_title_text_button' =>
			[
				'CODE' => '21.1.four_cols_big_bgimg_title_text_button',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
                <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img1.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn"><span style="font-weight: 400;">Charm</span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40"><p>Praesent malesuada facilisis maximus.</p></div>
                        <div class="landing-block-node-button-container g-px-10 g-pb-40">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self">view more</a>
                    	</div>
                    </div>
                </div>

                <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img3.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn"><span style="font-weight: normal;">Moon</span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40"><p>Praesent malesuada facilisis maximus.</p></div>
                        <div class="landing-block-node-button-container g-px-10 g-pb-40">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self">view more</a>
                    	</div>
                    </div>
                </div>

            	<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img6.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn"><span style="font-weight: 400;">Rose<br /></span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40"><p>Praesent malesuada facilisis maximus.</p></div>
                        <div class="landing-block-node-button-container g-px-10 g-pb-40">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self">view more</a>
                    	</div>
                    </div>
                </div>

            	<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img5.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn"><span style="font-weight: 400;">Sea<br /></span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40"><p>Praesent malesuada facilisis maximus.</p></div>
                        <div class="landing-block-node-button-container g-px-10 g-pb-40">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self">view more</a>
                    	</div>
                    </div>
                </div>


				<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img2.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn" style=""><span style="font-weight: 400;">Gold</span></h3>
						<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40" style=""><p>Praesent malesuada facilisis maximus.</p></div>
						<div class="landing-block-node-button-container g-px-10 g-pb-40">
							<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self" style="">view more</a>
						</div>
					</div>
				</div>

                <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/250x350/img4.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-px-40 g-pt-40 g-mb-20 g-font-size-18 g-letter-spacing-2 js-animation fadeIn"><p><span style="font-weight: 400; font-size: 1.28571rem; letter-spacing: 0.14286rem;">sapphire</span><br /></p></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn g-px-40"><p>Praesent malesuada facilisis maximus.</p></div>
                        <div class="landing-block-node-button-container g-px-10 g-pb-40">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m g-btn-size-md mx-2 g-rounded-50 js-animation fadeIn" href="#" target="_self">view more</a>
                    	</div>
                    </div>
                </div></div>
    </section>',
			],
		'27.one_col_fix_title_and_text_2' =>
			[
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-pt-50 g-pb-2 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-letter-spacing-2 g-text-transform-none g-font-size-60 g-color-primary"><span style="font-weight: bold;font-style: italic;">Happy women!</span></h2>
                <div class="landing-block-node-text g-font-size-14"><p>Praesent ut ante congue, volutpat urna at, lacinia quam. Nulla non massa eget ante gravida tincidunt non eu quam. Proin in varius leo placerat mi vulputate suscipit</p></div>
            </div>
        </div>

    </section>',
			],
		'28.3.team' =>
			[
				'CODE' => '28.3.team',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img5.jpg" alt="" data-fileid="-1" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">&quot;Proin in varius leo placerat mi vulputate suscipit&quot;</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14 g-font-size-11--md"><span style="font-weight: 400;">Praesent malesuada facilisis maximus.</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Helen</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img6.jpg" alt="" data-fileid="-1" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">&quot;Proin in varius leo placerat mi vulputate suscipit&quot;</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14 g-font-size-11--md"><span style="font-weight: 400;">Praesent malesuada facilisis maximus.</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Victoria</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--md fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img7.jpg" alt="" data-fileid="-1" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">&quot;Proin in varius leo placerat mi vulputate suscipit&quot;</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14 g-font-size-11--md"><span style="font-weight: 400;">Praesent malesuada facilisis maximus.</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Angela</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img8.jpg" alt="" data-fileid="-1" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">&quot;Proin in varius leo placerat mi vulputate suscipit&quot;</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14 g-font-size-11--md"><span style="font-weight: 400;">Praesent malesuada facilisis maximus.</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Maria</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="font-style: italic;">Contact us</span></h2>
            </div>

			<div class="landing-block-node-text g-font-size-14"><p><span style="color: rgb(33, 33, 33);">Praesent ut ante congue, volutpat urna at, lacinia quam. Nulla non massa eget ante gravida tincidunt non eu quam. Proin in varius leo placerat mi vulputate suscipit</span></p></div>
        </div>

    </section>',
			],
		'33.13.form_2_light_no_text' =>
			[
				'CODE' => '33.13.form_2_light_no_text',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-pt-100 g-pb-100">

	<div class="container">

		<div class="row">
			<div class="col-md-6 mx-auto">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v4"
					data-b24form-use-style="Y"
					data-b24form-embed
					data-b24form-design=\'{"dark":false,"style":"classic","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#000","background":"#ffffff00","fieldBorder":"#fff","fieldBackground":"#f7f7f7","fieldFocusBackground":"#eee"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'
				>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'35.1.footer_light' =>
			[
				'CODE' => '35.1.footer_light',
				'SORT' => '10000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Contact
					us</h2>
				<div class="landing-block-node-text g-mb-20">
					<p>Lorem ipsum dolor sit amet, consectetur
						adipiscing</p></div>

				<address class="g-mb-20">
					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text">
							Address: <span style="font-weight: bold;">In sed lectus tincidunt</span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-phone"></i>
						</div>
						<div class="landing-block-node-card-contact-text">
							Phone Number: <span style="font-weight: bold;"><a
										href="tel:485552566112">+48 555 2566 112</a></span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-envelope"></i>
						</div>
						<div>
							<div class="landing-block-node-card-contact-text">
								Email: <span style="font-weight: bold;"><a
											href="mailto:info@company24.com">info@company24.com</a></span>
							</div>
						</div>
					</div>
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">
					Categories</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Customer
					Support</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Nunc vitae libero
							lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Top
					Link</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Nunc vitae libero
							lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			],
	],
];