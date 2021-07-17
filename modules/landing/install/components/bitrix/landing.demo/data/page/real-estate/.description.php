<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_REALESTATE_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_REALESTATE_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'real-estate',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/real-estate/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_REALESTATE_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_REALESTATE_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_REALESTATE_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_REALESTATE_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_16' =>
			[
				'CODE' => '0.menu_16',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--relative">
	<div class="landing-block-node-top-block u-header__section u-header__section--hidden u-header__section--light g-bg-white g-brd-bottom g-brd-gray-light-v4 g-py-10 g-py-20--sm">
		<div class="container">
			<div class="row no-gutters flex-lg-row align-items-center justify-content-lg-start">
				<div class="col-12 col-sm-3 col-lg-2 text-center text-md-left">
					<!-- Logo -->
					<a href="#system_mainpage" class="navbar-brand landing-block-node-menu-logo-link g-mb-10 g-mb-0--sm g-mr-0">
						<img class="landing-block-node-menu-logo img-fluid g-max-width-100x"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/real-estate-logo.png"
							 alt="Logo">
					</a>
					<!-- End Logo -->
				</div>

				<div class="col-12 col-sm-9 col-lg-10">
					<div class="row g-ml-20--sm">
						<div class="landing-block-card-menu-contact-container col-sm-8 col-md-9">
							<div class="landing-block-card-menu-contact-container-inner row">
								<div class="landing-block-card-menu-contact col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
									 data-card-preset="contact-link">

									<a href="tel:+469548521" class="landing-block-node-menu-contactlink-link g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start g-text-decoration-none--hover">
										<span class="landing-block-node-menu-contact-img-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
											<i class="landing-block-node-menu-contact-img icon icon-screen-smartphone"></i>
										</span>
										<span class="landing-block-node-menu-contactlink-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
											<span class="landing-block-node-menu-contactlink-title  landing-block-node-menu-contact-title-style d-block text-uppercase g-font-size-13">
												Call Us
											</span>
											<span class="landing-block-node-menu-contactlink-text landing-block-node-menu-contact-text-style d-block g-font-weight-700 g-text-decoration-none g-text-underline--hover">
												+469 548 521
											</span>
										</span>
									</a>
								</div>

								<div class="landing-block-card-menu-contact col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
									 data-card-preset="contact-text">
									<div class="g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start">
										<div class="landing-block-node-menu-contact-img-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
											<i class="landing-block-node-menu-contact-img icon icon-clock"></i>
										</div>
										<div class="landing-block-node-menu-contact-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
											<div class="landing-block-node-menu-contact-title landing-block-node-menu-contact-title-style text-uppercase g-font-size-13">
												Opening time
											</div>
											<div class="landing-block-node-menu-contact-value landing-block-node-menu-contact-text-style g-font-weight-700">
												Mon-Sat: 08.00 -18.00
											</div>
										</div>
									</div>
								</div>

								<div class="landing-block-card-menu-contact col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
									 data-card-preset="contact-link">

									<a href="mailto:info@company24.com" class="landing-block-node-menu-contactlink-link g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start g-text-decoration-none--hover">
										<span class="landing-block-node-menu-contact-img-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
											<i class="landing-block-node-menu-contactlink-img icon icon-envelope"></i>
										</span>
										<span class="landing-block-node-menu-contactlink-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
											<span class="landing-block-node-menu-contactlink-title  landing-block-node-menu-contact-title-style d-block text-uppercase g-font-size-13">
												Email us
											</span>
											<span class="landing-block-node-menu-contactlink-text landing-block-node-menu-contact-text-style d-block g-font-weight-700 g-text-decoration-none g-text-underline--hover">
												info@company24.com
											</span>
										</span>
									</a>
									
								</div>
							</div>
						</div>

						<div class="landing-block-socials-container col-sm-4 col-md-3 g-mb-10 g-mb-0--md">
							<ul class="landing-block-cards-social list-inline g-pa-10--md g-mb-0 row align-items-center justify-content-center justify-content-sm-start">
								<li class="landing-block-card-social list-inline-item g-valign-middle g-mx-3 g-mb-6"
									data-card-preset="facebook">
									<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
									   href="https://facebook.com">
										<i class="landing-block-card-social-icon fa fa-facebook"></i>
									</a>
								</li>
								<li class="landing-block-card-social list-inline-item g-valign-middle g-mx-3 g-mb-6"
									data-card-preset="twitter">
									<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
									   href="https://twitter.com">
										<i class="landing-block-card-social-icon fa fa-twitter"></i>
									</a>
								</li>
								<li class="landing-block-card-social list-inline-item g-valign-middle g-mx-3 g-mb-6"
									data-card-preset="instagram">
									<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
									   href="https://instagram.com">
										<i class="landing-block-card-social-icon fa fa-instagram"></i>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="landing-block-node-bottom-block u-header__section g-bg-black g-py-15--lg g-py-10"
		 data-header-fix-moment-classes="u-shadow-v18">
		<nav class="navbar navbar-expand-lg py-0 g-px-10">
			<div class="container">
				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row g-mr-40--sm" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav w-100 g-ml-minus-15--lg text-uppercase g-font-weight-700 g-font-size-13 g-py-10--md">
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[46.9.cover_bgimg_vertical_slider]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Home
							</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Flats
								for rent</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.1.one_col_fix_with_title]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Special offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Our houses</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[01.big_with_text_blocks]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Agents</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[01.big_with_text_3]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Discount</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[33.3.form_1_transparent_black_no_text]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Contact</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-pos-rel g-line-height-1 g-brd-none g-pa-0 ml-auto" type="button"
						aria-label="Toggle navigation"
						aria-expanded="false"
						aria-controls="navBar"
						data-toggle="collapse"
						data-target="#navBar">
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
		'46.9.cover_bgimg_vertical_slider' =>
			[
				'CODE' => '46.9.cover_bgimg_vertical_slider',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel carouselVertical002 g-overflow-hidden" data-autoplay="true" data-infinite="true" data-speed="10000" data-vertical="true" data-pagi-classes="u-carousel-indicators-v7 text-center g-ml-30">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh h-100 g-pt-10 g-pb-30 g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_2--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img25.jpg);">
				<div class="landing-block-node-text-container js-animation fadeIn container text-center g-z-index-1 animated g-mx-0">
					<h6 class="landing-block-node-card-subtitle g-font-size-32 g-color-white g-mb-10 g-mb-25--md">
						<span style="font-weight: bold;">$3.500</span> / per month
					</h6>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-20 g-font-size-46 g-color-white mb-0 g-mb-35--md g-text-break-word">
						Apartment in<br />London\'s center
					</h2>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-l g-mt-20 g-mt-0--md text-uppercase g-btn-primary g-py-15 g-rounded-4" href="#">Learn more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh h-100 g-pt-10 g-pb-30 g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_2--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img26.jpg);">
				<div class="landing-block-node-text-container js-animation fadeIn container text-center g-z-index-1 animated g-mx-0">
					<h6 class="landing-block-node-card-subtitle g-font-size-32 g-color-white g-mb-10 g-mb-25--md">
						<span style="font-weight: bold;">$3.500</span> / per month
					</h6>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-20 g-font-size-46 g-color-white mb-0 g-mb-35--md g-text-break-word">
						Apartment in<br />London\'s center
					</h2>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-l g-mt-20 g-mt-0--md text-uppercase g-btn-primary g-py-15 g-rounded-4" href="#">Learn more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh h-100 g-pt-10 g-pb-30 g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_2--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img27.jpg);">
				<div class="landing-block-node-text-container js-animation fadeIn container text-center g-z-index-1 animated g-mx-0">
					<h6 class="landing-block-node-card-subtitle g-font-size-32 g-color-white g-mb-10 g-mb-25--md">
						<span style="font-weight: bold;">$3.500</span> / per month
					</h6>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-20 g-font-size-46 g-color-white mb-0 g-mb-35--md g-text-break-word">
						Apartment in<br />London\'s center
					</h2>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-l g-mt-20 g-mt-0--md text-uppercase g-btn-primary g-py-15 g-rounded-4" href="#">Learn more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh h-100 g-pt-10 g-pb-30 g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_2--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img28.jpg);">
				<div class="landing-block-node-text-container js-animation fadeIn container text-center g-z-index-1 animated g-mx-0">
					<h6 class="landing-block-node-card-subtitle g-font-size-32 g-color-white g-mb-10 g-mb-25--md">
						<span style="font-weight: bold;">$3.500</span> / per month
					</h6>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-20 g-font-size-46 g-color-white mb-0 g-mb-35--md g-text-break-word">
						Apartment in<br />London\'s center
					</h2>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-l g-mt-20 g-mt-0--md text-uppercase g-btn-primary g-py-15 g-rounded-4" href="#">Learn more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-pt-60 g-bg-main g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">POPULAR APARTMENTS FOR RENT</h2>
            </div>

			<div class="landing-block-node-text"><p>Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor auctor sapien eu tempus nunc placerat</p></div>
        </div>

    </section>',
			],
		'39.1.five_blocks_carousel' =>
			[
				'CODE' => '39.1.five_blocks_carousel',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="js-carousel"
		 data-infinite="true"
		 data-slides-show="5"
		 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-60 g-font-size-60 g-color-white g-bg-primary"
		 data-arrow-left-classes="fa fa-angle-left g-left-10"
		 data-arrow-right-classes="fa fa-angle-right g-right-10"
		 data-responsive=\'[{
               "breakpoint": 1200,
               "settings": {
                 "slidesToShow": 5
               }
             }, {
               "breakpoint": 992,
               "settings": {
                 "slidesToShow": 3
               }
             }, {
               "breakpoint": 446,
               "settings": {
                 "slidesToShow": 1
               }
             }]\'>
		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img3.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img4.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img5.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img6.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img7.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img8.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pb-20 js-animation fadeInUp animated g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">SPECIAL OFFER</h2>
            </div>
        </div>
    </section>',
			],
		'31.2.two_cols_img_text' =>
			[
				'CODE' => '31.2.two_cols_img_text',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-theme-architecture-bg-blue-dark-v1">
	<div>
		<div class="row mx-0">
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x460/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
			
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-25 js-animation fadeInUp animated">Ut pulvinar tellus sed elit luctus</h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-light-v2 js-animation fadeInUp animated"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi lobortis.<br /><br />FOR $1.500.000 INSTEAD $1.750.000! </p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary js-animation fadeInUp animated g-rounded-4" href="#" tabindex="0" target="_self">LEARN MORE</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR HOUSES</h2>
            </div>

			<div class="landing-block-node-text"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.</p></div>
        </div>

    </section>',
			],
		'39.1.five_blocks_carousel@2' =>
			[
				'CODE' => '39.1.five_blocks_carousel',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-20">
	<div class="js-carousel"
		 data-infinite="true"
		 data-slides-show="5"
		 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-60 g-font-size-60 g-color-white g-bg-primary"
		 data-arrow-left-classes="fa fa-angle-left g-left-10"
		 data-arrow-right-classes="fa fa-angle-right g-right-10"
		 data-responsive=\'[{
               "breakpoint": 1200,
               "settings": {
                 "slidesToShow": 5
               }
             }, {
               "breakpoint": 992,
               "settings": {
                 "slidesToShow": 3
               }
             }, {
               "breakpoint": 446,
               "settings": {
                 "slidesToShow": 1
               }
             }]\'>
		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img3.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img4.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img5.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img6.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img7.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img8.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>
	</div>
</section>',
			],
		'39.1.five_blocks_carousel@3' =>
			[
				'CODE' => '39.1.five_blocks_carousel',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="js-carousel"
		 data-infinite="true"
		 data-slides-show="5"
		 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-60 g-font-size-60 g-color-white g-bg-primary"
		 data-arrow-left-classes="fa fa-angle-left g-left-10"
		 data-arrow-right-classes="fa fa-angle-right g-right-10"
		 data-responsive=\'[{
               "breakpoint": 1200,
               "settings": {
                 "slidesToShow": 5
               }
             }, {
               "breakpoint": 992,
               "settings": {
                 "slidesToShow": 3
               }
             }, {
               "breakpoint": 446,
               "settings": {
                 "slidesToShow": 1
               }
             }]\'>
		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img3.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img4.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img5.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img6.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img7.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>

		<div class="landing-block-node-card js-slide g-px-15">
			<!-- Article -->
			<article class="landing-block-node-card-bg js-animation fadeInUp text-center g-bg-white g-brd-around g-brd-gray-light-v3 g-rounded-4 g-my-2">
				<!-- Article Header -->
				<header class="g-pa-25">
					<div class="landing-block-node-card-subtitle text-uppercase g-letter-spacing-1 g-mb-15">
						<span style="font-weight: bold;">$3.500</span>
						/ per month
					</div>
					<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_4 g-font-weight-700 g-font-size-16 g-mb-10">
						Ut pulvinar tellus sed elit luctus
					</h3>
					<div class="text-uppercase g-font-size-12">
						<a class="landing-block-node-card-link g-font-size-10 g-color-primary--hover g-text-underline--none--hover"
						   href="#">12 Reviews</a>
					</div>
				</header>
				<!-- End Article Header -->

				<!-- Article Image -->
				<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x534/img8.jpg" alt="">
				<!-- End Article Image -->

				<!-- Article Footer -->
				<footer class="d-table w-100">
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon1 u-line-icon-pro icon-hotel-restaurant-022"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text1 g-font-size-12 g-valign-middle d-inline">4</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-brd-right g-brd-gray-light-v3 g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon2 u-line-icon-pro icon-hotel-restaurant-158"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text2 g-font-size-12 g-valign-middle d-inline">2</div>
					</div>
					<div class="landing-block-node-card-texticon-container d-table-cell g-px-10 g-pt-15 g-pb-10">
						<div class="landing-block-node-card-icon-container g-mr-5 g-font-size-18 d-inline-block g-valign-middle">
							<i class="landing-block-node-card-icon3 u-line-icon-pro icon-real-estate-017"></i>
						</div>
						<div class="landing-block-node-card-icon-text landing-block-node-card-icon-text3 g-font-size-12 g-valign-middle d-inline">130 sqft</div>
					</div>
				</footer>
				<!-- End Article Footer -->
			</article>
			<!-- End Article -->
		</div>
	</div>
</section>',
			],
		'01.big_with_text_blocks' =>
			[
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="10000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x802/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">Nunc sed trisrique mi</h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.<br /><br /><span style="font-weight: bold;">For $2.500.000</span></p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-4" href="#" tabindex="-1" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x802/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">Nunc sed trisrique mi</h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.</p><p><span style="font-weight: bold;">For $2.500.000</span></p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-4" href="#" tabindex="-1" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide" >
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x802/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">NUNC SED TRISRIQUE MI</h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.</p><p><span style="font-weight: bold; color: rgb(244, 81, 30);">For $2.500.000</span></p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-4" href="#" tabindex="0" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x802/img4.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">Nunc sed trisrique mi</h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"><p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent malesuada facilisis maximus.</p><p><span style="font-weight: bold;">For $2.500.000</span></p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-4" href="#" tabindex="0" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR AGENTS</h2>
            </div>

			<div class="landing-block-node-text"><p>Ut pulvinar tellus sed elit luctus aliquet. Suspendisse hendrerit sapien a aliquet porttitor. In hendrerit consequat neque eget egestas. In a consectetur felis.</p></div>
        </div>

    </section>',
			],
		'28.3.team' =>
			[
				'CODE' => '28.3.team',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-pt-20 g-pb-60">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img1.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Mauris sodales</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">TOMAS SOWYER</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0"> </div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img2.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Integer blandit </div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">SAMINA KINGSTAR</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0"> </div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img3.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Proin sollicitudin</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">SAMANTHA FELLY</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0"> </div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img4.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Vestibulum pulvinar</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">ERICA PYTON</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0"> </div>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-py-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x934/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown animated">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-30 g-color-white g-mb-20">CONTACT US AND <span style="color: rgb(230, 74, 25);">GET 10%</span> DISCOUNT</h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35">Donec eleifend mauris eu leo varius consectetur. Aliquam luctus a lorem ac rutrum. Cras in nulla id mi ornare vestibulum. <p>Donec et magna nulla. Pellentesque ut ipsum id nibh pretium blandit quis ac erat.</p></div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md g-btn-primary text-uppercase g-mb-15 g-rounded-4" target="_self">CONTACT US</a>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-main g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">FROM OUR CLIENTS</h2>
            </div>

			<div class="landing-block-node-text"><p>Ut pulvinar tellus sed elit luctus aliquet. Suspendisse hendrerit sapien a aliquet porttitor. In hendrerit consequat neque eget egestas. In a consectetur felis.</p></div>
        </div>

    </section>',
			],
		'08.2.two_cols_fix_title_and_text' =>
			[
				'CODE' => '08.2.two_cols_fix_title_and_text',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pb-60 g-pt-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn animated ">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-20 g-text-break-word">Spencer Family</h2>
				</div>

				<div class="landing-block-node-text g-font-size-14"><p>Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis.</p></div>
			</div>

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn animated ">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-20 g-text-break-word">Melani Shnaider</h2>
				</div>

				<div class="landing-block-node-text g-font-size-14"><p>Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Morbi aliquam leo id ipsum tempus mollis.</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'33.3.form_1_transparent_black_no_text' =>
			[
				'CODE' => '33.3.form_1_transparent_black_no_text',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-bg-primary-dark-v1 g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1275/img1.jpg);">

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
		'35.2.footer_dark' =>
			[
				'CODE' => '35.2.footer_dark',
				'SORT' => '8000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60 g-theme-architecture-bg-blue-dark-v1">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<div class="landing-block-node-text g-mb-20"> </div>
				<address class="g-color-gray-light-v1 g-mb-20">
				</address>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">
					Categories</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">TOP lINKS</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Nunc vitae libero
							lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">USEFUL Links</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Nunc vitae libero
							lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v2" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			],
		'17.1.copyright_with_social' =>
			[
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-theme-architecture-bg-blue-dark-v1 js-animation animation-none">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 g-color-gray-light-v2 js-animation animation-none">
					&copy; 2021 All rights reserved.
				</div>
			</div>

			<div class="col-md-6">
			
				<ul class="list-inline float-md-right mb-0">
					<li class="landing-block-card-social list-inline-item g-mr-10"
						data-card-preset="facebook">
						<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-gray-light-v1 g-color-white--hover g-bg-primary--hover g-brd-gray-dark-v5 g-brd-primary--hover g-rounded-50x"
						   href="https://facebook.com">
							<i class="landing-block-card-social-icon fa fa-facebook"></i>
						</a>
					</li>

					<li class="landing-block-card-social list-inline-item g-mr-10"
						data-card-preset="instagram">
						<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-gray-light-v1 g-color-white--hover g-bg-primary--hover g-brd-gray-dark-v5 g-brd-primary--hover g-rounded-50x"
						   href="https://instagram.com">
							<i class="landing-block-card-social-icon fa fa-instagram"></i>
						</a>
					</li>
					<li class="landing-block-card-social list-inline-item g-mr-10"
						data-card-preset="twitter">
						<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-gray-light-v1 g-color-white--hover g-bg-primary--hover g-brd-gray-dark-v5 g-brd-primary--hover g-rounded-50x"
						   href="https://twitter.com">
							<i class="landing-block-card-social-icon fa fa-twitter"></i>
						</a>
					</li>
					<li class="landing-block-card-social list-inline-item g-mr-10"
						data-card-preset="youtube">
						<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-gray-light-v1 g-color-white--hover g-bg-primary--hover g-brd-gray-dark-v5 g-brd-primary--hover g-rounded-50x"
						   href="https://youtube.com">
							<i class="landing-block-card-social-icon fa fa-youtube"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>',
			],
	]
];