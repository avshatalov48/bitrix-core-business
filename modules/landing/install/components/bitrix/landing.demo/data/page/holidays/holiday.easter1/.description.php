<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_EASTER1_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_EASTER1_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'accounting',
			'THEME_CODE_TYPO' => 'accounting',
		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.easter1/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_EASTER1_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EASTER1_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_EASTER1_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EASTER1_DESCRIPTION')
		)
	),
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(3,13,4,25) ? 71 : -141,
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array(),
		'EXCEPT' => array('kz','ua', 'cn', 'tr')
	)),
	'items' => array (
		'0.menu_17_restaurant' =>
			array (
				'CODE' => '0.menu_17_restaurant',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--floating u-header--floating-relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-16"
		 data-header-fix-moment-exclude="g-py-16"
		 data-header-fix-moment-classes="u-shadow-v27 g-py-6">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/restaurant-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[41.1.big_image_slider_with_texts]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[10.2.two_cols_big_img_text_and_text_blocks_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Products</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[41.2.text_bolcks_slider_on_color_bg]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SPECIALS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BOOKING FORM</a>
						</li>
						
						
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-25 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'41.1.big_image_slider_with_texts' =>
			array (
				'CODE' => '41.1.big_image_slider_with_texts',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel" data-autoplay="true" data-infinite="true" data-speed="5000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">
		

		

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-flex-centered g-min-height-100vh h-100 g-bg-pos-center g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/993x655/img1.jpg\');" data-fileid="-1">
				<div class="text-center g-pos-rel container g-max-width-800 g-z-index-1 landing-block-node-card-container js-animation fadeInLeft">
					<span class="landing-block-node-card-icon-container g-color-white-opacity-0_7">
						<i class="landing-block-node-card-icon g-font-size-60 g-mb-10 icon-heart"></i>
					</span>
					<h2 class="landing-block-node-card-title g-line-height-1_2 g-font-weight-700 g-color-white g-mb-10 g-mb-15--md g-font-cormorant-infant g-text-transform-none g-font-size-180 g-font-size-90--md"><span style="font-style: italic; color: rgb(255, 255, 255);">Happy Easter!</span></h2>
					<div class="landing-block-node-card-subtitle text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-40 g-mb-50--md">easter day<br /></div>
					<img class="landing-block-node-card-photo g-width-130 g-height-130 g-brd-around g-brd-10 g-brd-white g-rounded-50x mx-auto g-mb-20 g-mb-25--md" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x250/img1.jpg" alt="" data-fileid="-1" />
					<p class="landing-block-node-card-name text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-10 g-mb-15--md">Wide choice of tasty pastry and chocolate eggs</p>
					<div class="landing-block-node-card-text g-line-height-1_1 g-font-weight-100 g-font-size-default g-color-white g-mb-10 g-mb-20--md"><p><span style="color: rgb(245, 245, 245);">Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</span></p></div>
					<div class="landing-block-node-card-price text-uppercase g-font-weight-700 g-font-size-16 g-color-primary mb-0">from $55</div>
				</div>
			</div>
		</div>

		
	</div>
</section>',
			),
		'10.2.two_cols_big_img_text_and_text_blocks_2' =>
			array (
				'CODE' => '10.2.two_cols_big_img_text_and_text_blocks_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block row no-gutters">
        <div class="landing-block-node-texts col-lg-7 g-pt-100 g-pb-80 g-px-15 g-px-40--md g-bg-primary">
            <header class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white-opacity-0_8 text-left">About us</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-color-white g-mb-minus-10 text-left g-font-size-50--md g-font-montserrat text-uppercase g-font-size-35">we are the best<br /></h2>
            </header>

			<div class="landing-block-node-text g-color-white-opacity-0_8 text-left"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p><p><span style="font-size: 1rem;">Fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</span><br /></p></div>

            <div class="row align-items-stretch">

            

            

            

            </div>
        </div>

		<div class="landing-block-node-img col-lg-5 g-min-height-360 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/844x526/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
    </section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-55 g-pb-30 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our products</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-font-montserrat text-uppercase">Choose your egg<br /></h2>
            </div>
        </div>
    </section>',
			),
		'42.1.rest_menu' =>
			array (
				'CODE' => '42.1.rest_menu',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-20">
	<div class="container">
		<div class="tab-content g-pt-20">
			<div class="tab-pane fade show active">
				<!-- Products Block -->
				<div class="row">
					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img24.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">with flowers</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$12</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img25.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">blue-green streaks<br /></h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$10</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img26.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">multicolor streaks</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$11</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img27.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">blue<br /></h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$16</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img28.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">polka-dot</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$14</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img29.jpg" alt="" data-fileid="-1" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">pink</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$15</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

				
				</div>
				<!-- End Products Block -->
			</div>
		</div>
	</div>
</section>',
			),
		'41.2.text_bolcks_slider_on_color_bg' =>
			array (
				'CODE' => '41.2.text_bolcks_slider_on_color_bg',
				'SORT' => '3000',
				'CONTENT' => '<section class="g-bg-primary g-py-100">
	<div class="container">
		<section class="js-carousel" data-vertical="true" data-pagi-classes="u-carousel-indicators-v7 text-center">
			

			

			

			

			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-color-white g-bg-white--before g-pb-40 g-font-size-38 text-uppercase g-font-montserrat js-animation fadeInLeft">sugar rabbit and cake<br /></h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price font-weight-bold g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn text-uppercase u-btn-white g-font-weight-700 g-font-size-11 g-color-gray-dark-v2 g-brd-none rounded-0 g-px-30 g-py-25 js-animation fadeIn" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/709x547/img1.jpg" alt="" data-fileid="-1" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>
		</section>
	</div>
</section>',
			),
		'05.features_4_cols_with_title' =>
			array (
				'CODE' => '05.features_4_cols_with_title',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-py-80">
        <div class="container">
            <div class="landing-block-node-header text-uppercase text-center u-heading-v2-4--bottom g-brd-primary g-mb-80 js-animation fadeIn">
                <h4 class="landing-block-node-title h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">other cake&amp;#039;s shapes</h4>
                <h2 class="landing-block-node-subtitle h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-38 g-font-montserrat">CHOOSE ANYTHING YOU LIKE</h2>
            </div>

            <!-- Icon Blocks -->
            <div class="row no-gutters">
                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-science-100 u-line-icon-pro"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10">moon shape</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md"><span style="color: rgb(167, 167, 167); font-family: Montserrat, Helvetica, Arial, sans-serif;"> Vivamus a fringilla lorem, vel faucibus ante.</span></div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><br /></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-media-119 u-line-icon-pro"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10">star shape</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md"><span style="color: rgb(167, 167, 167); font-family: Montserrat, Helvetica, Arial, sans-serif;"> Vivamus a fringilla lorem, vel faucibus ante.</span></div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><br /></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-medical-022 u-line-icon-pro"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10">heart shape</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md"><span style="color: rgb(167, 167, 167); font-family: Montserrat, Helvetica, Arial, sans-serif;"> Vivamus a fringilla lorem, vel faucibus ante.</span></div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><br /></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--md g-ml-minus-1 g-transition-0_2 g-transition--ease-in js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-finance-145 u-line-icon-pro"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10">diamond shape</h3>
                        <div class="landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md"><span style="color: rgb(167, 167, 167); font-family: Montserrat, Helvetica, Arial, sans-serif;"> Vivamus a fringilla lorem, vel faucibus ante.</span></div>

                        <div class="landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><br /></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>
            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			),
		'04.1.one_col_fix_with_title@2' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-bg-primary g-pt-55 g-pb-30 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white-opacity-0_8">special offers</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-white g-font-montserrat g-font-size-38">easter cookies<br /></h2>
            </div>
        </div>
    </section>',
			),
		'28.3.team' =>
			array (
				'CODE' => '28.3.team',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-pb-0 g-pt-65">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img40.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">green</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">$20</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">green cookie</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img41.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">yellow</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">$35</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">yellow cookie</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--md fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img42.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">blue</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">$40</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">blue cookie</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 fadeIn">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img43.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">pink</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">$15</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">pink cookie</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-20 g-pb-30 g-bg-main g-pt-0 js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15 g-color-primary">BOOKING FORM</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-color-black-opacity-0_8 g-font-montserrat g-font-size-38">Contact us</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Etiam ultrices lacus ut ligula vestibulum, sit amet mattis nunc elementum. Nam arcu enim, euismod nec purus non, aliquam congue ante. Nulla faucibus enim mauris, fringilla mollis ligula sollicitudin mollis.</p></div>
        </div>

    </section>',
			),
		'33.23.form_2_themecolor_no_text' =>
			array (
				'CODE' => '33.23.form_2_themecolor_no_text',
				'SORT' => '5500',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-py-80 g-bg-primary g-pt-0 g-pb-0">

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
		'17.copyright' =>
			array (
				'CODE' => '17.copyright',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="text-center g-color-gray-dark-v3 g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12  js-animation animation-none">
				<p>&copy; 2018 all rights reserved.</p>
			</div>
		</div>
	</div>
</section>',
			),
	),
);