<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_GYM_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_GYM_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'gym',
			'THEME_CODE_TYPO' => 'gym',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/gym/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_GYM_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_GYM_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_GYM_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_GYM_DESCRIPTION'),
		),
	),
	'items' => array (
		'0.menu_12_gym' =>
			array (
				'CODE' => '0.menu_12_gym',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu landing-ui-pattern-transparent u-header u-header--floating g-z-index-9999">
	<div class="u-header__section g-bg-black-opacity-0_3 g-transition-0_3 g-py-7 g-py-23--md"
		 data-header-fix-moment-exclude="g-bg-black-opacity-0_3 g-py-23--md"
		 data-header-fix-moment-classes="g-bg-black-opacity-0_7 g-py-17--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/gym-logo.png"
						 alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-2 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[0.menu_12_gym]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[46.8.cover_bgimg_title_with_icons]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[21.1.four_cols_big_bgimg_title_text_button]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">STUDIOS</a>
						</li>
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">LESSONS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TEAM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-12 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
			),
		'46.8.cover_bgimg_title_with_icons' =>
			array (
				'CODE' => '46.8.cover_bgimg_title_with_icons',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block g-pos-rel">
	<div class="landing-block-node-bgimg js-carousel" data-autoplay="true" data-infinite="true" data-fade="true" data-speed="5000">
		<div class="js-slide u-bg-overlay g-bg-black-opacity-0_2--after landing-block-node-card landing-block-node-card-bgimg g-bg-img-hero g-min-height-100vh slick-slide  slick-current slick-active" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1600x927/img1.jpg);"></div>

		<div class="js-slide u-bg-overlay g-bg-black-opacity-0_2--after landing-block-node-card landing-block-node-card-bgimg g-bg-img-hero g-min-height-100vh slick-slide  slick-current slick-active" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1600x927/img2.jpg);"></div>
	</div>

	<div class="u-bg-overlay__inner g-absolute-centered w-100 g-width-80x--md">
		<div class="landing-block-node-text-container js-animation fadeIn container text-center g-color-white animated">
			<h2 class="landing-block-node-subtitle text-uppercase u-heading-v3__title g-letter-spacing-1 g-font-weight-700 g-font-size-18 g-brd-3 g-brd-primary g-pb-5 g-mb-20">
				Fitness program for everybody</h2>
			<h3 class="landing-block-node-title text-uppercase g-line-height-1_4 g-letter-spacing-5 g-font-weight-700 g-font-size-40 g-mb-20">
				Easy to be perfect</h3>

			<ul class="list-inline g-font-size-16 g-mb-30">
				<li class="landing-block-node-icon-container landing-block-node-card-list-item list-inline-item g-mx-20 g-mb-10 g-mb-0--md">
					<i class="landing-block-node-icon fa fa-cutlery g-font-size-24 g-color-primary g-valign-middle g-mr-5"></i>
					<div class="landing-block-node-icon-text d-inline-block g-font-weight-200 g-valign-middle">
						<span style="font-weight: bold;">2150</span> Kkal
					</div>
				</li>
				<li class="landing-block-node-icon-container landing-block-node-card-list-item list-inline-item g-mx-20 g-mb-10 g-mb-0--md">
					<i class="landing-block-node-icon fa fa-calendar g-font-size-24 g-color-primary g-valign-middle g-mr-5"></i>
					<div class="landing-block-node-icon-text d-inline-block g-font-weight-200 g-valign-middle">
						<span style="font-weight: bold;">7</span> Weeks
					</div>
				</li>
				<li class="landing-block-node-icon-container landing-block-node-card-list-item list-inline-item g-mx-20 g-mb-10 g-mb-0--md">
					<i class="landing-block-node-icon fa fa-clock-o g-font-size-24 g-color-primary g-valign-middle g-mr-5"></i>
					<div class="landing-block-node-icon-text d-inline-block g-font-weight-200 g-valign-middle">
						<span style="font-weight: bold;">1.5</span> per/day
					</div>
				</li>
				<li class="landing-block-node-icon-container landing-block-node-card-list-item list-inline-item g-mx-20">
					<i class="landing-block-node-icon fa fa-universal-access g-font-size-24 g-color-primary g-valign-middle g-mr-5"></i>
					<div class="landing-block-node-icon-text d-inline-block g-font-weight-200 g-valign-middle">
						<span style="font-weight: bold;">3</span> times/week
					</div>
				</li>
			</ul>

			<div class="landing-block-node-button-container">
				<a href="#" class="landing-block-node-button btn btn-md text-uppercase u-btn-outline-white g-letter-spacing-1 g-font-weight-700 g-font-size-11 g-rounded-50 g-px-35 g-py-14">
					Learn More</a>
			</div>
		</div>
	</div>
</section>',
			),
		'21.3.two_cols_big_bgimg_title_text_button' =>
			array (
				'CODE' => '21.3.two_cols_big_bgimg_title_text_button',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img1.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
						<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20 js-animation fadeIn animated">YOUR PERFECT BODY</h3>
						<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>PILATES</p><p>YOGA</p><p>CROSSFIT</p><p>WOMEN\'S BOXING</p><p>CYCLING</p><p>FITNESS</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img2.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
						<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20 js-animation fadeIn animated">FOR EVERYBODY</h3>
						<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>ZUMBA</p><p>TRX</p><p>STEP</p><p>CARDIO</p><p>STRETCHING</p><p>ZUMBA</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>

        </div>
    </section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block landing-block-container g-pb-0 js-animation fadeInUp animated g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our services</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-35">QUALITY RESULTS WITH US</h2>
            </div>
        </div>
    </section>',
			),
		'06.1features_3_cols' =>
			array (
				'CODE' => '06.1features_3_cols',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-py-80 g-pt-0 g-pb-0">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">

                

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-001"></i>
                	</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-letter-spacing-2">FUNCTIONAL TRAININGS</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-067"></i>
                	</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-letter-spacing-2">CARDIO TRAININGS</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Donec sed lobortis tortor. Ut nec lacinia sapien, sit amet dapibus magna. Vestibulum nunc ex.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div><div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-078"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-letter-spacing-2">MUSCULATION</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Sed ultricies luctus ipsum in placerat. Mauris ultrices pharetra lectus sit amet commodo. Fusce ac sagittis.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			),
		'06.1features_3_cols@2' =>
			array (
				'CODE' => '06.1features_3_cols',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-py-80 g-pt-0 g-pb-50">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-118"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-line-height-1 g-letter-spacing-2">PERSONAL TRAININGS</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien cursus.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-185"></i>
                	</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-line-height-1 g-letter-spacing-2">CROSSFIT</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Integer blandit velit nec purus convallis ullamcorper. Pellentesque habitant morbi tristique senectus et netuss</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-sport-172"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 text-center g-line-height-1 g-letter-spacing-2">SPORTS NUTRITION</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 text-center"><p>Proin sollicitudin turpis in massa rutrum, id tincidunt justo fermentum. Vestibulum semper, urna eu egestas.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			),
		'21.1.four_cols_big_bgimg_title_text_button' =>
			array (
				'CODE' => '21.1.four_cols_big_bgimg_title_text_button',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
                <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img1.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">CROSSFIT</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
                        </div>
                    </div>
                </div>

                <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img2.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">Women\'s boxing</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
                        </div>
                    </div>
                </div>

            	<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img3.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">YOGA</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
                        </div>
                    </div>
                </div>

            	<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img4.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">Men\'s boxing</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
                        </div>
                    </div>
                </div>

        		<div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img5.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">trainings</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
						</div>
                    </div>
                </div>
            <div class="landing-block-card col-lg-2 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover  g-bg-black-opacity-0_2--after js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/280x500/img6.jpg);">
					<div class="text-center u-bg-overlay__inner" data-stop-propagation>
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-letter-spacing-2 js-animation fadeIn animated g-font-size-16">others</h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn animated"><p>Sed feugiat porttitor nunc, non dignis sim ipsum vestibulum in.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn btn-lg u-btn-inset mx-2 js-animation fadeIn animated g-rounded-50 u-btn-primary" href="#" target="_self">Read more</a>
                        </div>
                    </div>
                </div></div>
    </section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-bg-black-opacity-0_8 js-animation fadeInUp animated g-pt-60 g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-color-white g-font-size-25 g-letter-spacing-2"><span style="font-weight: bold;">YOU MUST KNOW IT</span></h2>
                <div class="landing-block-node-text g-color-gray-light-v1 g-font-size-14"><p>Praesent ut ante congue, volutpat urna at, lacinia quam. Nulla non massa eget ante gravida tincidunt non eu quam. Proin in varius leo placerat mi vulputate suscipit</p></div>
            </div>
        </div>

    </section>',
			),
		'31.2.two_cols_img_text' =>
			array (
				'CODE' => '31.2.two_cols_img_text',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-bg-black-opacity-0_8">
	<div>
		<div class="row mx-0">
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/570x321/img1.jpg\');"></div>
			
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default g-color-white g-mb-25 js-animation fadeInUp">DAILY CROSSFIT WORKOUT</h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-light-v1 js-animation fadeInUp"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus. Donec sed lobortis tortor. Ut nec lacinia sapien, sit amet dapibus magna. Vestibulum nunc ex, tempus et volutpat nec, convallis ut massa. Sed ultricies luctus ipsum in placerat.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn btn-xl u-btn-primary g-font-weight-700 g-font-size-12 g-rounded-50 js-animation fadeInUp" href="#" tabindex="0" target="_self">VIEW OUR SCHEDULE</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2@2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 text-uppercase g-letter-spacing-2 g-font-size-25"><span style="font-weight: bold;">MEET OUR TEAM</span></h2>
                <div class="landing-block-node-text g-font-size-14"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.</p></div>
            </div>
        </div>

    </section>',
			),
		'28.3.team' =>
			array (
				'CODE' => '28.3.team',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x400/img1.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">tammy@company24.com<p>+ 44 555 2566 112</p></div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14"><span style="font-weight: normal;">Yoga, Cardio, Pilates, Crossfit</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">TAMMY EXON</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x400/img2.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">jacob@company24.com<p>+ 44 555 2566 113</p></div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14"><span style="font-weight: normal;">Gym, Boxing, Crossfit, Cardio</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">JACOB BARTON</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--md fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x400/img3.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">monica@company24.com<p>+ 44 555 2566 114</p></div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14"><span style="font-weight: normal;">Cardio, Pilates, Zumba</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">MONICA NOTROM</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x400/img4.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">tom@company24.com<p>+ 44 555 2566 115</p></div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none g-font-size-14"><span style="font-weight: normal;">Box, Kickboxing, Gym, Crossfit</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">TOM SOWYER</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2@3' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-primary">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 text-uppercase g-letter-spacing-2 g-font-size-25 g-color-white"><span style="font-weight: 700;">CONTACT US</span></h2>
                <div class="landing-block-node-text g-font-size-14 g-color-white"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p></div>
            </div>
        </div>

    </section>',
			),
		'33.23.form_2_themecolor_no_text' =>
			array (
				'CODE' => '33.23.form_2_themecolor_no_text',
				'SORT' => '6000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-bg-primary g-pb-60 g-pt-20">

	<div class="container">

		<div class="landing-block-form-styles" hidden="">
			<div class="g-bg-transparent h1 g-color-white g-brd-none g-pa-0" data-form-style-wrapper-padding="1" data-form-style-bg="1" data-form-style-bg-content="1" data-form-style-bg-block="1" data-form-style-header-font-size="1" data-form-style-main-font-weight="1" data-form-style-border-block="1">
			</div>

			<div class="g-bg-white g-color-primary g-brd-primary" data-form-style-main-bg="1" data-form-style-main-border-color="1" data-form-style-main-font-color-hover="1">
			</div>
			<div class="g-bg-primary-dark-v2 u-theme-restaurant-shadow-v1 g-brd-around g-color-gray-dark-v2 rounded-0" data-form-style-input-bg="1" data-form-style-input-box-shadow="1" data-form-style-input-select-bg="1" data-form-style-input-border="1" data-form-style-input-border-radius="1" data-form-style-button-font-color="1">
			</div>
			<div class="g-brd-around g-brd-gray-light-v2 g-brd-bottom g-bg-black-opacity-0_7" data-form-style-input-border-color="1" data-form-style-input-border-hover="1">
			</div>

			<p class="g-color-white-opacity-0_7" data-form-style-second-font-color="1" data-form-style-main-font-family="1" data-form-style-main-font-weight="1" data-form-style-header-text-font-size="1">
			</p>

			<h3 class="g-font-size-11 g-color-white" data-form-style-label-font-weight="1" data-form-style-label-font-size="1" data-form-style-main-font-color="1">
			</h3>
		</div>

		<div class="row">
			<div class="col-md-6 mx-auto">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v3" data-b24form="" data-b24form-use-style="Y" data-b24form-show-header="N" data-b24form-original-domain=""></div>
			</div>
		</div>
	</div>
</section>',
			),
		'35.1.footer_light' =>
			array (
				'CODE' => '35.1.footer_light',
				'SORT' => '6500',
				'CONTENT' => '<section class="g-pt-60 g-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Contact us</h2>
				<p class="landing-block-node-text g-font-size-default g-color-gray-dark-v2 g-mb-20">Lorem ipsum dolor sit amet, consectetur
					adipiscing</p>

				<address class="g-mb-20">
					<div class="landing-block-card-contact g-pos-rel g-pl-20 g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-dark-v2 g-absolute-centered--y g-left-0">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text g-color-gray-dark-v2">
							Address: <span style="font-weight: bold;">In sed lectus tincidunt</span>
						</div>
					</div>

					<div class="landing-block-card-contact g-pos-rel g-pl-20 g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-dark-v2 g-absolute-centered--y g-left-0">
							<i class="landing-block-node-card-contact-icon fa fa-phone"></i>
						</div>
						<div class="landing-block-node-card-contact-text g-color-gray-dark-v2">
							Phone Number: <span style="font-weight: bold;"><a
										href="tel:485552566112">+48 555 2566 112</a></span>
						</div>
					</div>

					<div class="landing-block-card-contact g-pos-rel g-pl-20 g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-dark-v2 g-absolute-centered--y g-left-0">
							<i class="landing-block-node-card-contact-icon fa fa-envelope"></i>
						</div>
						<div>
							<div class="landing-block-node-card-contact-text g-color-gray-dark-v2">
								Email: <span style="font-weight: bold;"><a
											href="mailto:info@company24.com">info@company24.com</a></span>
							</div>
						</div>
					</div>
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Useful links</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Sed convallis nec enim</a>
					</li>
					
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Nunc vitae libero lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Nunc vitae libero lacus</a>
					</li>
					
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			),
	),
);