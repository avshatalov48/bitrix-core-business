<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '2business',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/business/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
		],
	],
	'items' => [
		'0.menu_05' => [
			'CODE' => '0.menu_05',
			'SORT' => '-100',
			'CONTENT' => '<header class="landing-block g-theme-business-bg-blue-dark-v1-opacity-0_9 u-header u-header--sticky u-header--float">
		<div class="u-header__section g-transition-0_3 g-py-25"
		 data-header-fix-moment-exclude="g-py-25"
		 data-header-fix-moment-classes="g-py-20">
		<nav class="navbar navbar-expand-lg py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#system_mainpage" class="navbar-brand landing-block-node-menu-logo-link u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/business-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-2 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="nav-item landing-block-node-menu-list-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">HOME</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[02.three_cols_big_1]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">ABOUT</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[05.features_4_cols_with_title]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">SERVICES</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[07.3.two_col_fix_text_with_icon_title_and_text]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">PROCESSES</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[09.1.two_cols_fix_text_and_image_slider]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">PROJECTS</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[10.1.two_cols_big_img_text_and_text_blocks]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">HOW WE WORK</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[03.3.one_col_big_with_text_and_title]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">NEWS</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-ml-15--lg">
							<a href="#block@block[04.2.one_col_fix_with_title_2]" class="landing-block-node-menu-list-item-link nav-link g-color-white p-0" target="_self">CONTACTS</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-8 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'01.big_with_text' => [
			'CODE' => '01.big_with_text',
			'SORT' => '500',
			'CONTENT' => '<section class="g-pos-rel landing-block g-overflow-hidden">

	<div class="js-carousel g-overflow-hidden g-max-height-100vh" data-autoplay="true" data-infinite="true" data-fade="true" data-speed="5000">
		<div class="landing-block-card-img js-slide g-bg-img-hero g-min-height-100vh" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1075/img1.jpg\');"></div>
		<div class="landing-block-card-img js-slide g-bg-img-hero g-min-height-100vh" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1075/img2.jpg\');"></div>
	</div>

	<div class="g-absolute-centered g-width-80x--md">
		<div class="container text-center g-max-width-800">
			<div class="landing-block-node-text-container info-v3-4 g-bg-primary-opacity-0_9 g-pa-20 g-pa-60--md js-animation fadeInLeft">
				<div class="g-pos-rel g-z-index-3">
					<h3 class="landing-block-node-small-title text-uppercase g-letter-spacing-3 g-color-white g-mb-10">We are Company24</h3>
					<h2 class="landing-block-node-title text-uppercase g-color-white g-letter-spacing-5 g-font-weight-400 g-font-size-25 g-font-size-35--md g-mb-20">BUSINESS &amp; CORPORATION</h2>
					<div class="landing-block-node-text g-line-height-1_8 g-letter-spacing-3 g-color-white g-mb-20">Sed feugiat porttitor nunc, non dignissim
						<br /> ipsum vestibulum in. Donec in blandit dolor.</div>
					<a href="#" class="landing-block-node-button btn text-uppercase g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0" target="_self">LEARN MORE</a>
				</div>
			</div>
		</div>
	</div>
</section>',
		],
		'02.three_cols_big_1' => [
			'CODE' => '02.three_cols_big_1',
			'SORT' => '1000',
			'CONTENT' => '<section class="container-fluid px-0 landing-block">
        <div class="row no-gutters">
            <div class="landing-block-node-left-img g-min-height-300 col-lg-4 g-bg-img-hero" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1920/img2.jpg);"></div>

            <div class="landing-block-node-center col-md-6 col-lg-4 g-flex-centered g-theme-business-bg-blue-dark-v1">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-center-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 js-animation fadeIn">About us</h6>
                        <h2 class="landing-block-node-center-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10 js-animation fadeIn">Help you make
                            <br /> money</h2>
                    </div>

                    <div class="landing-block-node-center-text g-color-gray-light-v2 mb-0 js-animation fadeIn"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
                        viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 g-theme-business-bg-blue-dark-v2 landing-block-node-right">
                <div class="js-carousel g-pb-90" data-infinite="true" data-slides-show="1" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img1.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">Since 2008</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img2.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">Past 2012</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img3.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">Present 2022</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>',
		],
		'05.features_4_cols_with_title' => [
			'CODE' => '05.features_4_cols_with_title',
			'SORT' => '1500',
			'CONTENT' => '<section class="landing-block g-py-80">
        <div class="container">
            <div class="landing-block-node-header text-uppercase text-center u-heading-v2-4--bottom g-brd-primary g-mb-80 js-animation fadeIn">
                <h6 class="landing-block-node-title g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our services</h6>
                <h2 class="landing-block-node-subtitle h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">What we do</h2>
            </div>

            <!-- Icon Blocks -->
            <div class="landing-block-node-row justify-content-center row no-gutters landing-block-inner">
                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-fire"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">Analysis</h5>
                        <div class="landing-block-node-element-text">
							<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. Fusce dapibus</p>
						</div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Responsive Web Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">E-commerce</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">App &amp; Icon Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Logo &amp; Brand Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Mobile Development</li>
                            <li class="landing-block-node-element-list-item g-py-8">UI/UX Design</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-energy"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">Strategy</h5>
                        <div class="landing-block-node-element-text">
							<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine usce dapibus elit nondapibus</p>
						</div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Analysis &amp; Consulting</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Email Marketing</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">App &amp; Icon Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Responsive Web Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Social Networking</li>
                            <li class="landing-block-node-element-list-item g-py-8">Documentation</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-layers"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">Social media</h5>
                        <div class="landing-block-node-element-text">
							<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine usce dapibus elit nondapibus</p>
						</div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Display Advertising</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">App &amp; Icon Design</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Analysis &amp; Consulting</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Ad services</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Social Media</li>
                            <li class="landing-block-node-element-list-item g-py-8">Analysis</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-social-youtube"></i>
					</span>
                        <h5 class="landing-block-node-element-title text-uppercase g-mb-10">Marketing</h5>
                        <div class="landing-block-node-element-text">
							<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. Fusce dapibus</p>
						</div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0">
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Ad services</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Social Media</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Analysis</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Ad services</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Social Media</li>
                            <li class="landing-block-node-element-list-item g-py-8">Analysis &amp; Consulting</li>
                        </ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>
            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
		],
		'07.3.two_col_fix_text_with_icon_title_and_text' => [
			'CODE' => '07.3.two_col_fix_text_with_icon_title_and_text',
			'SORT' => '2000',
			'CONTENT' => '<section class="landing-block g-theme-business-bg-blue-dark-v1 g-py-20">

		<div class="container text-center g-max-width-800 g-color-gray-light-v2 g-mb-20">
			<div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
				<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Work process</h6>
				<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10">Step by step</h2>
			</div>
			<div class="landing-block-node-text g-color-gray-light-v2">
				<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
					eros, fringilla porttitor lorem eros vel odio.</p>
			</div>
		</div>
        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card js-animation col-lg-6 g-px-30 g-mb-10 fadeIn">
                    <div class="landing-block-card-container g-pos-rel g-parent g-theme-business-bg-blue-dark-v2 g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm">
                        <div class="g-absolute-centered--y g-left-0">
                            <div class="landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-primary">
                                <span class="landing-block-node-element-icon-hover d-block g-pos-abs g-top-0 g-left-0 g-width-85 g-height-85 g-rounded-50x opacity-0 g-opacity-1--parent-hover g-transition-0_1 g-transition--ease-in g-bg-size-cover" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg);"></span>
								<span class="u-icon-v3 u-icon-size--xl g-width-85 g-height-85 g-bg-transparent g-opacity-1 opacity-0--parent-hover g-transition-0_1 g-transition--ease-in">
								  <i class="landing-block-node-element-icon icon-fire"></i>
								</span>
                            </div>
                        </div>

                        <h5 class="landing-block-node-element-title text-uppercase g-color-gray-light-v2 g-mb-10">Step 1. Analysis</h5>
                        <div class="landing-block-node-element-text g-color-gray-light-v2">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>
                    </div>
                </div>

                <div class="landing-block-card js-animation col-lg-6 g-px-30 g-mb-10 fadeIn">
                    <div class="landing-block-card-container g-pos-rel g-parent g-theme-business-bg-blue-dark-v2 g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm">
                        <div class="g-absolute-centered--y g-left-0">
                            <div class="landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-primary">
                                <span class="landing-block-node-element-icon-hover d-block g-pos-abs g-top-0 g-left-0 g-width-85 g-height-85 g-rounded-50x opacity-0 g-opacity-1--parent-hover g-transition-0_1 g-transition--ease-in g-bg-size-cover" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg);"></span>
								<span class="u-icon-v3 u-icon-size--xl g-width-85 g-height-85 g-bg-transparent g-opacity-1 opacity-0--parent-hover g-transition-0_1 g-transition--ease-in">
								  <i class="landing-block-node-element-icon icon-energy"></i>
								</span>
                            </div>
                        </div>

                        <h5 class="landing-block-node-element-title text-uppercase g-color-gray-light-v2 g-mb-10">Step 2. Creative concept</h5>
                        <div class="landing-block-node-element-text g-color-gray-light-v2">
							<p>We strive to embrace and drive change in our industry which allows us to keep our clients relevant.</p>
						</div>
                    </div>
                </div>
            </div>
        </div>
    </section>',
		],
		'07.1.two_col_fix_text_with_icon' => [
			'CODE' => '07.1.two_col_fix_text_with_icon',
			'SORT' => '2500',
			'CONTENT' => '<section class="landing-block g-theme-business-bg-blue-dark-v1 g-py-20">
        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card js-animation col-lg-6 g-px-30 g-mb-10 fadeIn animated ">
                    <div class="landing-block-card-container g-pos-rel g-parent g-theme-business-bg-blue-dark-v2 g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm">
                        <div class="g-absolute-centered--y g-left-0">
                            <div class="landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-primary">
                                <span class="landing-block-node-icon-hover d-block g-pos-abs g-top-0 g-left-0 g-width-85 g-height-85 g-rounded-50x opacity-0 g-opacity-1--parent-hover g-transition-0_1 g-transition--ease-in g-bg-size-cover" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg);"></span>
								<span class="u-icon-v3 u-icon-size--xl g-width-85 g-height-85 g-bg-transparent g-opacity-1 opacity-0--parent-hover g-transition-0_1 g-transition--ease-in">
								  <i class="landing-block-node-icon icon-fire"></i>
								</span>
                            </div>
                        </div>

                        <h5 class="landing-block-node-title text-uppercase g-color-gray-light-v2 g-mb-10">Step 1. Analysis</h5>
                        <div class="landing-block-node-text g-color-gray-light-v2">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>
                    </div>
                </div>

                <div class="landing-block-card js-animation col-lg-6 g-px-30 g-mb-10 fadeIn animated ">
                    <div class="landing-block-card-container g-pos-rel g-parent g-theme-business-bg-blue-dark-v2 g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm">
                        <div class="g-absolute-centered--y g-left-0">
                            <div class="landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-primary">
                                <span class="landing-block-node-icon-hover d-block g-pos-abs g-top-0 g-left-0 g-width-85 g-height-85 g-rounded-50x opacity-0 g-opacity-1--parent-hover g-transition-0_1 g-transition--ease-in" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg);"></span>
								<span class="u-icon-v3 u-icon-size--xl g-width-85 g-height-85 g-bg-transparent g-opacity-1 opacity-0--parent-hover g-transition-0_1 g-transition--ease-in">
								  <i class="landing-block-node-icon icon-energy"></i>
								</span>
                            </div>
                        </div>

                        <h5 class="landing-block-node-title text-uppercase g-color-gray-light-v2 g-mb-10">Step 2. Creative concept</h5>
                        <div class="landing-block-node-text g-color-gray-light-v2">
							<p>We strive to embrace and drive change in our industry which allows us to keep our clients relevant.</p>
						</div>
                    </div>
                </div>
            </div>
        </div>
    </section>',
		],
		'13.1.one_col_fix_text_and_button' => [
			'CODE' => '13.1.one_col_fix_text_and_button',
			'SORT' => '3000',
			'CONTENT' => '<section class="landing-block text-center g-pt-20 g-theme-business-bg-blue-dark-v1 g-pb-60">
	<div class="container g-max-width-800">

		<div class="landing-block-node-text g-color-gray-light-v2">
			<p>Sed eget aliquet nisl. Proin laoreet accumsan nisl non vestibulum. Donec molestie, lorem nec sollicitudin elementum
				<br /> tristique senectus et netus et malesuada fames ac turpis egestas.</p>
		</div>
		<div class="landing-block-node-button-container">
			<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">Learn more</a>
		</div>
	</div>
</section>',
		],
		'09.1.two_cols_fix_text_and_image_slider' => [
			'CODE' => '09.1.two_cols_fix_text_and_image_slider',
			'SORT' => '3500',
			'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-115 g-pb-80">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 g-mb-40 g-mb-0--lg landing-block-node-text-container js-animation fadeInLeft">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our projects</h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">We are the
                            <br /> best</h2>
                    </div>

					<div class="landing-block-node-text">
						<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla
							porttitor lorem eros vel odio.</p>
						<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>
					</div>
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
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img9.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">The moon ltd</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img10.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Support &amp; development</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in nulla non arcu at lectus.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img11.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Boston global</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img12.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Marketing group</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img5.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">The moon ltd</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img6.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Support &amp; development</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in nulla non arcu at lectus.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img7.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Boston global</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid w-100 g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img8.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-font-size-16 g-color-white mb-0">Marketing group</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-color-gray-light-v4 g-transition-0_3 g-transition--ease-in">
										<p>Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor.</p>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>',
		],
		'10.1.two_cols_big_img_text_and_text_blocks' => [
			'CODE' => '10.1.two_cols_big_img_text_and_text_blocks',
			'SORT' => '4000',
			'CONTENT' => '<section class="landing-block row no-gutters">
        <div class="landing-block-node-img col-lg-5 g-min-height-360 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1920/img1.jpg\');"></div>

        <div class="landing-block-node-texts col-lg-7 g-theme-business-bg-blue-dark-v1 g-pt-100 g-pb-80 g-px-15 g-px-40--md">
            <header class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">How we work</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10">Do all the best</h2>
            </header>

			<div class="landing-block-node-text g-color-white-opacity-0_7">
				<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
					viverra eros, fringilla porttitor lorem eros vel odio.</p>
			</div>

            <div class="row align-items-stretch">

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle landing-block-card-text-inner g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-theme-business-bg-blue-dark-v2 g-transition-0_3 g-pa-20 js-animation fadeIn">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-color-white g-font-weight-600 text-uppercase g-mb-10">Agency Search</h6>
                            <div class="landing-block-node-text-block-text g-color-white-opacity-0_7">
								<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum ipsum vestibulum velit.</p>
							</div>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle landing-block-card-text-inner g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-theme-business-bg-blue-dark-v2 g-transition-0_3 g-pa-20 js-animation fadeIn">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-color-white g-font-weight-600 text-uppercase g-mb-10">Management &amp; Marketing</h6>
                            <div class="landing-block-node-text-block-text g-color-white-opacity-0_7">
								<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum ipsum vestibulum velit.</p>
							</div>
                        </div>
                    </article>
                </div>


                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle landing-block-card-text-inner g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-theme-business-bg-blue-dark-v2 g-transition-0_3 g-pa-20 js-animation fadeIn">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-color-white g-font-weight-600 text-uppercase g-mb-10">Coaching &amp; Planning</h6>
                            <div class="landing-block-node-text-block-text g-color-white-opacity-0_7">
								<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum ipsum vestibulum velit.</p>
							</div>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle landing-block-card-text-inner g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-theme-business-bg-blue-dark-v2 g-transition-0_3 g-pa-20 js-animation fadeIn">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-color-white g-font-weight-600 text-uppercase g-mb-10">Consultation Services</h6>
                            <div class="landing-block-node-text-block-text g-color-white-opacity-0_7">
								<p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum ipsum vestibulum velit.</p>
							</div>
                        </div>
                    </article>
                </div>

            </div>

        </div>
    </section>',
		],
		'04.7.one_col_fix_with_title_and_text_2' => [
			'CODE' => '04.7.one_col_fix_with_title_and_text_2',
			'SORT' => '4500',
			'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-bg-main g-pt-60 g-pb-10">

	<div class="container text-center g-max-width-800">

		<div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">OUR OFFERS</h4>
			<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">BEST OFFERS FOR YOU</h2>
		</div>

		<div class="landing-block-node-text g-pb-1"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
	</div>

</section>',
		],
		'11.three_cols_fix_tariffs' => [
			'CODE' => '11.three_cols_fix_tariffs',
			'SORT' => '5000',
			'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-30 g-pb-20">
        <div class="container">

            <div class="row no-gutters landing-block-inner">

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Starter</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal">
								<em>ed feugiat porttitor nunc, non</em>
							</div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$25.00</span></div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25">
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper justo..</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>10%</b> In hac habitasse platea</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>10gb</b> Praesent egestas ac arcu</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>25</b> emails Sed eget aliquet nisl</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>No</b> Proin laoreet accumsan nisl non</li>
                            </ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
                        	</div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10 g-mt-minus-20">
                        <div class="g-bg-gray-light-v5 g-py-50 g-px-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Advanced</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal">
								<em>ed feugiat porttitor nunc, non</em>
							</div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$50.00</span></div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25">
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in...</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>20%</b> In hac habitasse platea</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>20gb</b> Praesent egestas ac arcu</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>50</b> emails Sed eget aliquet nisl</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>YES</b> Proin laoreet accumsan nisl non</li>
                            </ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
							</div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Professional</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal">
								<em>ed feugiat porttitor nunc, non</em>
							</div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$75.00</span></div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25">
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper justo..</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>30%</b> In hac habitasse platea</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>40gb</b> Praesent egestas ac arcu</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>100</b> emails Sed eget aliquet nisl</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12"><b>YES</b> Proin laoreet accumsan nisl non</li>
                            </ul>
							<div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
                        	</div>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </section>',
		],
		'03.3.one_col_big_with_text_and_title' => [
			'CODE' => '03.3.one_col_big_with_text_and_title',
			'SORT' => '5500',
			'CONTENT' => '<section class="landing-block container-fluid px-0 g-theme-business-bg-blue-dark-v1 g-pt-60">
        <div class="landing-block-inner-container row no-gutters">

            <div class="landing-block-card col-md-12 col-lg-12 g-flex-centered js-animation fadeIn animated ">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">FROM OUR BLOG</h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10">LATEST NEWS</h2>
                    </div>

					<div class="landing-block-node-text g-color-gray-light-v2">
						<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
							viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
					</div>
                </div>
            </div>

        </div>
    </section>',
		],
		'20.3.four_cols_fix_img_title_text' => [
			'CODE' => '20.3.four_cols_fix_img_title_text',
			'SORT' => '6000',
			'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img1.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Building since 1943</h3>
				<div class="landing-block-node-text">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img2.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Building since 1943</h3>
				<div class="landing-block-node-text">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img3.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Building since 1943</h3>
				<div class="landing-block-node-text">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img4.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Building since 1943</h3>
				<div class="landing-block-node-text">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

		</div>
	</div>
</section>',
		],
		'04.1.one_col_fix_with_title' => [
			'CODE' => '04.1.one_col_fix_with_title',
			'SORT' => '6500',
			'CONTENT' => '<section class="landing-block landing-block-container js-animation fadeInUp animated g-pb-20 g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">OUR CLIENTS</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">WHO WORKS WITH US</h2>
            </div>
        </div>
    </section>',
		],
		'12.image_carousel_6_cols_fix' => [
			'CODE' => '12.image_carousel_6_cols_fix',
			'SORT' => '7000',
			'CONTENT' => ' <section class="landing-block js-animation zoomIn text-center g-pt-20 g-pb-20">
        <div class="container g-px-35 g-px-0--md">

            <div class="js-carousel row"
                 data-autoplay="true"
				 data-pause-hover="true"
                 data-infinite="true"
                 data-slides-show="6"
				 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-30 g-color-gray-light-v1"
				 data-arrow-left-classes="fa fa-angle-left g-left-minus-35"
				 data-arrow-right-classes="fa fa-angle-right g-right-minus-35"
				 data-responsive=\'[{
					 "breakpoint": 1200,
					 "settings": {
					   "slidesToShow": 5
					 }
				   }, {
					 "breakpoint": 992,
					 "settings": {
					   "slidesToShow": 4
					 }
				   }, {
					 "breakpoint": 768,
					 "settings": {
					   "slidesToShow": 3
					 }
				   }, {
					 "breakpoint": 576,
					 "settings": {
					   "slidesToShow": 2
					 }
				   }]\'
				 data-init-classes-exclude=\'[{
					 "selector": ".landing-block-card-carousel-item",
					 "class": "col-6 col-sm-4 col-lg-2 justify-content-center"
				   }, {
					 "selector": ".js-carousel",
					 "class": "row"
				   }]\'>
                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img1.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img2.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img3.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img4.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img5.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img6.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img7.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img8.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img9.png" alt="">
					</a>
                </div>
            </div>

        </div>
    </section>',
		],
		'13.1.one_col_fix_text_and_button@2' => [
			'CODE' => '13.1.one_col_fix_text_and_button',
			'SORT' => '7500',
			'CONTENT' => '<section class="landing-block text-center g-pt-20 g-pb-60">
	<div class="container g-max-width-800">

		<div class="landing-block-node-text">
			<p>Sed eget aliquet nisl. Proin laoreet accumsan nisl non vestibulum. Donec molestie, lorem nec sollicitudin elementum
				<br /> tristique senectus et netus et malesuada fames ac turpis egestas.</p>
		</div>
		<div class="landing-block-node-button-container">
			<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0 g-px-15 g-font-weight-700" href="#">Projects</a>
		</div>
	</div>
</section>',
		],
		'04.2.one_col_fix_with_title_2' => [
			'CODE' => '04.2.one_col_fix_with_title_2',
			'SORT' => '8000',
			'CONTENT' => '<section class="landing-block landing-block-container g-theme-business-bg-blue-dark-v1 g-pt-20 g-pb-20 js-animation slideInLeft">
	<div class="container g-max-width-800">
		<div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
			<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">CONTACTS</h6>
			<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10">OUR CONTACTS</h2>
		</div>
	</div>
</section>',
		],
		'14.1.contacts_4_cols' => [
			'CODE' => '14.1.contacts_4_cols',
			'SORT' => '8500',
			'CONTENT' => '<section class="landing-block g-pt-40 g-pb-25 text-center g-theme-business-bg-blue-dark-v1">
			<div class="container">
			<div class="row justify-content-center">
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover"
					   href="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2304.457421907711!2d20.486353716222904!3d54.71916848028964!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x284054d2fac2875d!2z0JHQuNGC0YDQuNC60YEyNA!5e0!3m2!1sru!2sru!4v1536590497258"
					   target="_popup">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-globe"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-color-gray-light-v2 g-mb-5">
							Address</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 ">
							61 Oxford str., London, 3DG
						</span>
					</a>
				</div>
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
						   data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover" href="tel:1-800-643-4500" target="_blank">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-call-in"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-color-gray-light-v2 g-mb-5">
							Phone number</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 ">
							1-800-643-4500
						</span>
					</a>
				</div>
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover" href="mailto:info@company24.com" target="_blank">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-envelope"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-color-gray-light-v2 g-mb-5">
							Email</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 ">
							info@company24.com
						</span>
					</a>
				</div>
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-text">
					<div class="landing-block-node-contact-container">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-contact-icon icon-earphones-alt"></i>
						</span>
						<span class="landing-block-node-contact-title d-block text-uppercase g-color-gray-light-v2 g-mb-5">
							Toll free</span>
						<span class="landing-block-node-contact-text g-font-weight-700 g-color-gray-light-v2">
							@company24
						</span>
					</div>
				</div>
			</div>
		</div>
    </section>',
		],
		'17.copyright' => [
			'CODE' => '17.copyright',
			'SORT' => '9000',
			'CONTENT' => '<section class="landing-block js-animation animation-none">
        <div class="text-center g-color-gray-dark-v3 g-pa-10">
            <div class="g-width-600 mx-auto">
                <div class="landing-block-node-text g-font-size-12  js-animation animation-none">
					<p>&copy; 2022 All rights reserved.</p>
				</div>
            </div>
        </div>
    </section>',
		],
	],
];