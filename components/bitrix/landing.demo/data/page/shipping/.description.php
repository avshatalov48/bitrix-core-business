<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'shipping',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/shipping/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
		],
	],
	'items' => [
		'0.menu_16' =>
			[
				'CODE' => '0.menu_16',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu u-header u-header--sticky u-header--relative">
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
							<a href="#block@block[33.32.form_light_bgimg_right_text]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Home
							</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[30.2.three_cols_fix_img_and_links]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">about</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[41.4.cover_with_text_columns_on_bgimg]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">skills</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[11.2.three_cols_fix_tariffs_with_img]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">faq</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[43.3.cover_with_feedback]"
							   class="landing-block-node-menu-list-item-link nav-link p-0">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg g-max-width-120">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@5]"
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
		'33.32.form_light_bgimg_right_text' =>
			[
				'CODE' => '33.32.form_light_bgimg_right_text',
				'SORT' => '500',
				'CONTENT' => '<section class="g-pos-rel landing-block g-bg-primary-dark-v1">
	<div class="landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_3--after g-py-20"
		 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1732x1155/img1.jpg);">

		<div class="container g-pos-rel g-z-index-1 g-py-100">
			<div class="row">
				<div class="col-sm-12 col-lg-6 js-animation fadeInLeft landing-block-node-form align-self-center">
					<div class="g-bg-white g-rounded-5 g-pa-0">
						<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v2"
							data-b24form-use-style="Y"
							data-b24form-embed
							data-b24form-design=\'{"dark":false,"style":"classic","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#000","background":"#ffffff00","fieldBorder":"#fff","fieldBackground":"#f7f7f7","fieldFocusBackground":"#eee"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'
						></div>
					</div>
				</div>

				<!-- Promo Block - Info -->
				<div class="col-sm-12 col-lg-6 g-pt-30 g-pt-0--lg align-self-center">
					<h2 class="landing-block-node-title js-animation fadeInRight text-uppercase g-line-height-1 g-font-weight-700 g-font-size-55 g-color-white g-mb-30">
						Planning <br>&amp; Shipping</h2>
					<h3 class="landing-block-node-subtitle text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20">
						Delivering
						anything to anywhere</h3>
					<div class="landing-block-node-text js-animation fadeInRight g-color-white-opacity-0_8 g-mb-35">
						<p>Maecenas lacus magna, pretium in congue a, pharetra at lacus. Nulla neque justo, sodales
							vitae dui non, imperdiet luctus libero.</p>
					</div>
					<a class="landing-block-node-button js-animation fadeInRight btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-4 g-py-12"
					   href="#">Learn more</a>
				</div>
				<!-- End Promo Block - Info -->
			</div>
		</div>
	</div>
</section>',
			],
		'30.2.three_cols_fix_img_and_links' =>
			[
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-60">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="landing-block-node-svg-container g-pointer-events-none g-pos-rel">
							<svg class="g-hidden-col-1 g-hidden-col-2 g-hidden-col-3--md g-pos-abs g-left-0 g-right-0 g-bottom-0"
								 version="1.1" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"
								 xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px"
								 viewBox="20 -20 300 100">
								<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729 c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729 c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716 H42.401L43.415,98.342z"
									  opacity="0" fill="#fff"/>
								<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428 c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z"
									  fill="#fff"/>
							</svg>
						</div>
					</div>
					<div class="g-pos-rel">
                            <div class="g-pos-rel g-z-index-1 g-pa-30">
                                <h3 class="h5 mb-3">
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">CARGO</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="landing-block-node-svg-container g-pointer-events-none g-pos-rel">
							<svg class="g-hidden-col-1 g-hidden-col-2 g-hidden-col-3--md g-pos-abs g-left-0 g-right-0 g-bottom-0"
								 version="1.1" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"
								 xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px"
								 viewBox="20 -20 300 100">
								<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729 c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729 c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716 H42.401L43.415,98.342z"
									  opacity="0" fill="#fff"/>
								<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428 c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z"
									  fill="#fff"/>
							</svg>
						</div>
					</div>
					<div class="g-pos-rel">
                            <div class="g-pos-rel g-z-index-1 g-pa-30">
                                <h3 class="h5 mb-3">
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">LOGISTIC SERVICES</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

						<div class="landing-block-node-svg-container g-pointer-events-none g-pos-rel">
							<svg class="g-hidden-col-1 g-hidden-col-2 g-hidden-col-3--md g-pos-abs g-left-0 g-right-0 g-bottom-0"
								 version="1.1" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"
								 xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px"
								 viewBox="20 -20 300 100">
								<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729 c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729 c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z"
									  opacity="0.4" fill="#f0f1f3"/>
								<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716 H42.401L43.415,98.342z"
									  opacity="0" fill="#fff"/>
								<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428 c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z"
									  fill="#fff"/>
							</svg>
						</div>
					</div>
					<div class="g-pos-rel">
							<div class="g-pos-rel g-z-index-1 g-pa-30">
								<h3 class="h5 mb-3">
									<a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">STORAGE</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-bg-gray-light-v5 g-pt-20 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-26">TYPES OF SHIPPING</h2>
            </div>

			<div class="landing-block-node-text"><p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.</p></div>
        </div>

    </section>',
			],
		'44.1.four_columns_with_img_and_text' =>
			[
				'CODE' => '44.1.four_columns_with_img_and_text',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block">
	<div class="container-fluid px-0">
		<!-- Banners -->
		<div class="row no-gutters landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-3">
				<!-- Article -->
				<article
						class="landing-block-node-card-inner h-100 d-flex align-items-center justify-content-center text-center info-v3-3 g-parent g-bg-gray-light-v5 g-bg-cover g-bg-primary-opacity-0_6--after g-color-gray-dark-v3 g-color-white--hover g-py-30">
					<!-- Article Image -->
					<img class="landing-block-node-card-img info-v3-3__img"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/166x319/img1.png" alt="">
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="info-v3-3__description-sm u-link-v2 g-pos-cover g-flex-middle g-white-space-normal">
						<div class="g-flex-middle-item g-pa-30">
							<h3 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-mb-20 info-v3-3__title g-color-gray-dark-v2 g-color-white--parent-hover g-text-underline--none--hover g-transition-0_3">
								<span style="font-weight: bold;" class="g-transition-0_3 g-color-gray-dark-v2 g-color-white--parent-hover">Small</span><br>
								Objects
							</h3>
							<div class="landing-block-node-card-text-unhover info-v3-3__category g-font-size-11 text-uppercase">
								Shipping & package
							</div>

							<div class="info-v3-3__content g-opacity-0_7">
								<div class="landing-block-node-card-text g-color-white--parent-hover mb-0">
									Maecenas tempus, tellus eget condimentum rhoncus.
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3">
				<!-- Article -->
				<article
						class="landing-block-node-card-inner h-100 d-flex align-items-center justify-content-center text-center info-v3-3 g-parent g-bg-gray-light-v5 g-bg-cover g-bg-primary-opacity-0_6--after g-color-gray-dark-v3 g-color-white--hover g-py-30">
					<!-- Article Image -->
					<img class="landing-block-node-card-img info-v3-3__img"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/166x319/img2.png" alt="">
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="info-v3-3__description-sm u-link-v2 g-pos-cover g-flex-middle g-white-space-normal">
						<div class="g-flex-middle-item g-pa-30">
							<h3 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-mb-20 info-v3-3__title g-color-gray-dark-v2 g-color-white--parent-hover g-text-underline--none--hover g-transition-0_3">
								<span style="font-weight: bold;" class="g-transition-0_3 g-color-gray-dark-v2 g-color-white--parent-hover">Medium</span><br>
								Objects
							</h3>
							<div class="landing-block-node-card-text-unhover info-v3-3__category g-font-size-11 text-uppercase">
								Shipping & package
							</div>

							<div class="info-v3-3__content g-opacity-0_7">
								<div class="landing-block-node-card-text g-color-white--parent-hover mb-0">
									Maecenas tempus, tellus eget condimentum rhoncus.
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3">
				<!-- Article -->
				<article
						class="landing-block-node-card-inner h-100 d-flex align-items-center justify-content-center text-center info-v3-3 g-parent g-bg-gray-light-v5 g-bg-cover g-bg-primary-opacity-0_6--after g-color-gray-dark-v3 g-color-white--hover g-py-30">
					<!-- Article Image -->
					<img class="landing-block-node-card-img info-v3-3__img"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/166x319/img3.png" alt="">
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="info-v3-3__description-sm u-link-v2 g-pos-cover g-flex-middle g-white-space-normal">
						<div class="g-flex-middle-item g-pa-30">
							<h3 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-mb-20 info-v3-3__title g-color-gray-dark-v2 g-color-white--parent-hover g-text-underline--none--hover g-transition-0_3">
								<span style="font-weight: bold;" class="g-transition-0_3 g-color-gray-dark-v2 g-color-white--parent-hover">Large</span><br>
								Objects
							</h3>
							<div class="landing-block-node-card-text-unhover info-v3-3__category g-font-size-11 text-uppercase">
								Shipping & package
							</div>

							<div class="info-v3-3__content g-opacity-0_7">
								<div class="landing-block-node-card-text g-color-white--parent-hover mb-0">
									Maecenas tempus, tellus eget condimentum rhoncus.
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3">
				<!-- Article -->
				<article
						class="landing-block-node-card-inner h-100 d-flex align-items-center justify-content-center text-center info-v3-3 g-parent g-bg-gray-light-v5 g-bg-cover g-bg-primary-opacity-0_6--after g-color-gray-dark-v3 g-color-white--hover g-py-30">
					<!-- Article Image -->
					<img class="landing-block-node-card-img info-v3-3__img"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/166x319/img4.png" alt="">
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="info-v3-3__description-sm u-link-v2 g-pos-cover g-flex-middle g-white-space-normal">
						<div class="g-flex-middle-item g-pa-30">
							<h3 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-mb-20 info-v3-3__title g-color-gray-dark-v2 g-color-white--parent-hover g-text-underline--none--hover g-transition-0_3">
								<span style="font-weight: bold;" class="g-transition-0_3 g-color-gray-dark-v2 g-color-white--parent-hover">XXXXL</span><br>
								Objects
							</h3>
							<div class="landing-block-node-card-text-unhover info-v3-3__category g-font-size-11 text-uppercase">
								Shipping & package
							</div>

							<div class="info-v3-3__content g-opacity-0_7">
								<div class="landing-block-node-card-text g-color-white--parent-hover mb-0">
									Maecenas tempus, tellus eget condimentum rhoncus.
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>
		</div>
		<!-- End Banners -->
	</div>
</section>
',
			],
		'41.4.cover_with_text_columns_on_bgimg' =>
			[
				'CODE' => '41.4.cover_with_text_columns_on_bgimg',
				'SORT' => '2500',
				'CONTENT' => '<section
		class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_6--after g-pt-85 g-pb-85"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1732x1155/img2.jpg);">
	<div class="container u-bg-overlay__inner g-max-width-800">
		<div class="landing-block-node-header text-center mx-auto u-heading-v2-2--bottom g-brd-primary g-mb-70">
			<h2 class="landing-block-node-title text-uppercase g-line-height-1_1 g-font-weight-700 g-font-size-26 g-color-white g-mb-15">
				Tour
				services</h2>
			<div class="landing-block-node-text g-color-white-opacity-0_8 mb-0">
				<p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.
					Maecenas tempus.</p>
			</div>
		</div>
	</div>

	<div class="container u-bg-overlay__inner">
		<!-- Icon Blocks -->
		<div class="row">
			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-transport-026 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							International Shipping</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-christmas-090 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							Packaging</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-travel-044 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							Competitive
							rates</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-hotel-restaurant-249 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							Quick
							shipping</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-hotel-restaurant-211 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							Quality
							protection</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 g-mb-80">
				<!-- Icon Blocks -->
				<div class="u-info-v2-2 h-100 g-color-white text-center">
					<div class="u-info-v2-2__item h-100 g-brd-around g-brd-top-none g-brd-white-opacity-0_2 g-px-20 g-pb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--2xl g-line-height-1 g-color-white g-pull-50x-up">
							  <i class="landing-block-node-card-icon icon-hotel-restaurant-234 u-line-icon-pro"></i>
						</span>
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mt-minus-35 g-mb-15">
							Shipping
							anywhere</h6>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 mb-0">
							<p>Fusce mauris eros, ullamcorper in gravida a, feugiat
								in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>
		</div>
		<!-- End Icon Blocks -->
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 js-animation fadeInUp g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-26">OUR SKILLS</h2>
            </div>
			<div class="landing-block-node-text"><p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.</p></div>
        </div>

    </section>',
			],
		'08.1.three_cols_fix_title_and_text' =>
			[
				'CODE' => '08.1.three_cols_fix_title_and_text',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pb-60 g-pt-20">
        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word">84 HAPPY CLIENTS</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficitur</p></div>
                </div>

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word">34 COMPLETED PROJECTS</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficitur</p></div>
                </div>

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word">35 OUR TEAM</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficitur</p></div>
                </div>

            <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 	g-font-size-17 g-text-break-word">67 COUNTRIES</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficitur</p></div>
                </div></div>
        </div>
    </section>',
			],
		'10.1.two_cols_big_img_text_and_text_blocks' =>
			[
				'CODE' => '10.1.two_cols_big_img_text_and_text_blocks',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block row no-gutters g-pt-0">
        <div class="landing-block-node-img col-lg-5 g-min-height-360 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/600x985/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>

        <div class="landing-block-node-texts col-lg-7 g-pt-100 g-pb-80 g-px-15 g-px-40--md g-bg-gray-light-v5">
            <header class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-26">PACKING FRAGILE ITEMS</h2>
            </header>

			<div class="landing-block-node-text"><p>Aenean volutpat erat quis mollis accumsan. Mauris at cursus ipsum. Praesent molestie imperdiet purus in finibus. Pellentesque elit enim, malesuada a varius elementum, sodales id turpis. Maecenas interdum enim egestas risus semper, consectetur auctor metus rhoncus.<br /><br /><span style="font-size: 1rem;">Proin tempus tincidunt nunc sed pellentesque. Vivamus suscipit, tellus nec auctor egestas, urna augue hendrerit est, vel luctus nisl leo ut sem. Suspendisse sed tincidunt risus.</span></p></div>

            <div class="row align-items-stretch">

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">01. FUSCE ACCUMSAN FAUCIBUS</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">02. MAECENAS AUCTOR MAURIS</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">03. SUSPENDISSE PHARETRA ELIT AC</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">04. VESTIBULUM FRINGILLA RISUS EGE</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div><div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">05. ENIM EGESTAS RISUS SEMPER</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div>

            <div class="col-sm-6 g-mb-30 landing-block-card-text-block">
                    <article class="h-100 g-flex-middle g-brd-left g-brd-3 g-brd-primary g-brd-white--hover g-transition-0_3 g-pa-20 g-bg-main js-animation fadeIn animated">
                        <div class="g-flex-middle-item">
                            <h6 class="landing-block-node-text-block-title g-font-weight-600 text-uppercase g-mb-10">06. EU VENENATIS NULLA PORTTITOR</h6>
                            <div class="landing-block-node-text-block-text"><p>Curabitur sit amet fringilla mi. Etiam ac massa sit amet nulla eleifend rutrum vitae non sem. Fusce accumsan faucibus laoreet. Maecenas auctor mauris erat quis mollis.</p></div>
                        </div>
                    </article>
                </div></div>

        </div>
    </section>',
			],
		'11.2.three_cols_fix_tariffs_with_img' =>
			[
				'CODE' => '11.2.three_cols_fix_tariffs_with_img',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-60 g-pb-60">
	<div class="container">
		<div class="text-uppercase text-center g-mb-70">
			<h2 class="landing-block-node-title d-inline-block g-letter-spacing-0_5 g-font-weight-700 g-brd-bottom g-brd-5 g-brd-primary g-pb-8 g-mb-20 g-font-size-26">Best offers</h2>
			<div class="landing-block-node-text mb-0 g-text-transform-none g-font-size-14 g-letter-spacing-0"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p></div>
		</div>

		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 g-mb-30 g-mb-0--md  col-lg-3">
				<!-- Article -->
				<article
 class="landing-block-node-card-container js-animation fadeInRight text-center text-uppercase g-color-white-opacity-0_5 animated g-bg-main">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img3.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-tag"></i>
						</span>
						<h6 class="landing-block-node-card-title g-mt-minus-25 g-mb-10 g-letter-spacing-0 g-font-size-20"><span style="font-weight: bold;">
							small</span></h4>
						<div class="landing-block-node-card-text mb-0 g-text-transform-none g-letter-spacing-0 g-font-size-14"><p><span style="font-style: italic;">Dimensions: 10x10x15cm</span></p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-mb-20 g-color-primary g-font-size-30 g-letter-spacing-0">$10.00</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0"><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">CURABITUR SIT AMET</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">ETIAM AC MASSA SIT</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">FUSCE ACCUMSAN FAUCIBUS</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS TRISTIQUE BIBENDUM</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS VEHICULA</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DONEC FRINGILLA</span></li></ul>

					<footer class="g-pa-40 landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-size-md g-btn-px-m g-brd-2 g-letter-spacing-1 g-btn-primary g-rounded-4" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				
				<!-- End Article -->
			</article
></div>

			<div class="landing-block-node-card col-md-6 g-mb-30 g-mb-0--md  col-lg-3">
				<!-- Article -->
				<article
 class="landing-block-node-card-container js-animation fadeInRight text-center text-uppercase g-color-white-opacity-0_5 animated g-bg-main">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img4.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-bag"></i>
						</span>
						<h6 class="landing-block-node-card-title g-mt-minus-25 g-mb-10 g-letter-spacing-0 g-font-size-20"><span style="font-weight: bold;">medium</span></h4>
						<div class="landing-block-node-card-text mb-0 g-text-transform-none g-letter-spacing-0 g-font-size-14"><p><span style="font-style: italic;">Dimensions: 10x10x15cm</span></p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-mb-20 g-color-primary g-font-size-30 g-letter-spacing-0">$20.00</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0"><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">CURABITUR SIT AMET</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">ETIAM AC MASSA SIT</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">FUSCE ACCUMSAN FAUCIBUS</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS TRISTIQUE BIBENDUM</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS VEHICULA</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DONEC FRINGILLA</span></li></ul>

					<footer class="g-pa-40 landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-size-md g-btn-px-m g-brd-2 g-letter-spacing-1 g-btn-primary g-rounded-4" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				
				<!-- End Article -->
			</article
></div>

			<div class="landing-block-node-card col-md-6 g-mb-30 g-mb-0--md  col-lg-3">
				<!-- Article -->
				<article
 class="landing-block-node-card-container js-animation fadeInRight text-center text-uppercase g-color-white-opacity-0_5 animated g-bg-main">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img5.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-briefcase"></i>
						</span>
						<h6 class="landing-block-node-card-title g-mt-minus-25 g-mb-10 g-letter-spacing-0 g-font-size-20"><span style="font-weight: bold;">large</span></h4>
						<div class="landing-block-node-card-text mb-0 g-text-transform-none g-letter-spacing-0 g-font-size-14"><p><span style="font-style: italic;">Dimensions: 10x10x15cm</span></p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-mb-20 g-color-primary g-font-size-30 g-letter-spacing-0">$40.00</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0"><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">CURABITUR SIT AMET</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">ETIAM AC MASSA SIT</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">FUSCE ACCUMSAN FAUCIBUS</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS TRISTIQUE BIBENDUM</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS VEHICULA</span></li><li class="g-py-10 g-px-30 g-color-black g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DONEC FRINGILLA</span></li></ul>

					<footer class="g-pa-40 landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-size-md g-btn-px-m g-brd-2 g-letter-spacing-1 g-btn-primary g-rounded-4" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				
				<!-- End Article -->
			</article
></div>
		<div class="landing-block-node-card col-md-6 g-mb-30 g-mb-0--md  col-lg-3">
				<!-- Article -->
				<article
 class="landing-block-node-card-container js-animation fadeInRight text-center text-uppercase g-color-white-opacity-0_5 animated g-bg-main">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img6.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-present"></i>
						</span>
						<h6 class="landing-block-node-card-title g-mt-minus-25 g-mb-10 g-letter-spacing-0 g-font-size-20"><span style="font-weight: 700;">extra large</span></h4>
						<div class="landing-block-node-card-text mb-0 g-text-transform-none g-letter-spacing-0 g-font-size-14"><p><span style="font-style: italic;">Dimensions: 10x10x15cm</span></p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-mb-20 g-color-primary g-font-size-30 g-letter-spacing-0">$100.00</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0"><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">CURABITUR SIT AMET</span></li>
					<li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">ETIAM AC MASSA SIT</span></li><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">FUSCE ACCUMSAN FAUCIBUS</span></li>
					<li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS TRISTIQUE BIBENDUM</span></li><li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DUIS VEHICULA</span></li>
					<li class="g-py-10 g-px-30 g-bg-main"><span style="font-weight: 700;color: rgb(33, 33, 33);">DONEC FRINGILLA</span></li></ul>

					<footer class="g-pa-40 landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-size-md g-btn-px-m g-brd-2 g-letter-spacing-1 g-btn-primary g-rounded-4" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				
				<!-- End Article -->
			</article
></div></div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-bg-gray-light-v5 g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-26"><p style="text-align: center;"><span style="font-family: inherit;">Gallery</span></p></h2>
            </div>

			<div class="landing-block-node-text"><p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, Etiam rhoncus. Maecenas tempus.</p></div>
        </div>

    </section>',
			],
		'32.5.img_grid_3cols_1_wo_gutters' =>
			[
				'CODE' => '32.5.img_grid_3cols_1_wo_gutters',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img7.jpg"/>
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-center js-animation fadeInUp h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img8.jpg"/>
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img9.jpg"/>
				</div>
			</div>
		</div>

	</div>

</section>',
			],
		'32.5.img_grid_3cols_1_wo_gutters@2' =>
			[
				'CODE' => '32.5.img_grid_3cols_1_wo_gutters',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img10.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-center js-animation fadeInUp h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img11.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x400/img12.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

	</div>

</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-bg-gray-light-v5 g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-26">FAQ</h2>
            </div>

			<div class="landing-block-node-text"><p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.</p></div>
        </div>

    </section>',
			],
		'19.1.two_cols_fix_img_text_blocks' =>
			[
				'CODE' => '19.1.two_cols_fix_img_text_blocks',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-py-20 g-bg-gray-light-v5 g-pt-20">
        <div class="container">
            <div class="row">

                <div class="col-md-5 g-mb-30 g-mb-0--md">
                    <img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/348x660/img1.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-40"> </h3>
                    <div class="landing-block-node-text"><br /></div>
                </div>

                <div class="col-md-7 g-mb-15 g-mb-0--md">
                    <div aria-multiselectable="true">
                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
								<div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 fa fa-plus-square-o"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Phasellus bibendum semper lectus, in ornare erat tempus eget?</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading1">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 fa fa-plus-square-o"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Duis vehicula turpis tincidunt, malesuada mauris et, tincidunt nisl?</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading2">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Food truck quinoa nesciunt laborum eiusmod.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 fa fa-plus-square-o"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Mauris et lacus ut massa luctus varius?</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading3">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Food truck quinoa nesciunt laborum eiusmod.</p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 fa fa-plus-square-o"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Curabitur id elit lobortis, malesuada nibh in, fringilla metus?</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading4">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Food truck quinoa nesciunt laborum eiusmod.</p></div>
                                </div>
                            </div>
                        </div>
                    <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 fa fa-plus-square-o"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Fusce accumsan faucibus laoreet?</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading4">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute.</p></div>
                                </div>
                            </div>
                        </div></div>
                </div>

            </div>
        </div>
    </section>',
			],
		'43.3.cover_with_feedback' =>
			[
				'CODE' => '43.3.cover_with_feedback',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block">
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_5--after g-py-90"
		 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img10.jpg);">
		<div class="container u-bg-overlay__inner g-max-width-800">
			<div class="landing-block-node-header js-animation fadeInUp text-center mx-auto u-heading-v2-2--bottom g-brd-primary g-mb-70">
				<h2 class="landing-block-node-title text-uppercase g-line-height-1_1 g-font-weight-700 g-font-size-26 g-color-white g-mb-15">
					What
					do people say about us?</h2>
				<div class="landing-block-node-text g-color-white-opacity-0_8 mb-0">
					<p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam
						rhoncus. Nam eget dui. Etiam rhoncus. ullamcorper ultricies nisi, ullamcorper ultricies nisi</p>
				</div>
			</div>
		</div>

		<div class="container u-bg-overlay__inner g-width-900">
			<div class="js-carousel g-pb-70"
				 data-infinite="true"
				 data-arrows-classes="u-arrow-v1 g-absolute-centered--x g-bottom-0 g-width-40 g-height-40 g-rounded-50x g-color-gray-light-v2 g-color-white--hover g-bg-white g-bg-primary--hover g-transition-0_2 g-transition--ease-in"
				 data-arrow-left-classes="fa fa-angle-left g-ml-minus-30"
				 data-arrow-right-classes="fa fa-angle-right g-ml-30">
				<div class="landing-block-node-card js-slide">
					<!-- Testimonial Block -->
					<div class="landing-block-node-card-container js-animation fadeIn media d-block d-md-flex">
						<div class="g-mb-30 g-mb-0--md g-pr-30--sm">
							<img class="landing-block-node-card-photo img-fluid rounded-circle img-bordered g-brd-white mx-auto"
								 src="https://cdn.bitrix24.site/bitrix/images/landing/business/120x120/img11.jpg" alt="">
						</div>

						<div class="media-body align-self-center text-sm-left text-center g-color-white">
							<div class="landing-block-node-card-text g-mb-25">The customisation options you implemented are countless, and I
								feel sorry I can\'t use them all. Good job, and keep going! are countless, and I feel
							</div>
							<h6 class="landing-block-node-card-name text-uppercase g-font-weight-700 g-color-white mb-0">Someone someone</h6>
						</div>
					</div>
					<!-- End Testimonial Block -->
				</div>

				<div class="landing-block-node-card js-slide">
					<!-- Testimonial Block -->
					<div class="landing-block-node-card-container js-animation fadeIn media d-block d-md-flex">
						<div class="g-mb-30 g-mb-0--md g-pr-30--sm">
							<img class="landing-block-node-card-photo img-fluid rounded-circle img-bordered g-brd-white mx-auto"
								 src="https://cdn.bitrix24.site/bitrix/images/landing/business/120x120/img12.jpg" alt="">
						</div>

						<div class="media-body align-self-center text-center text-sm-left g-color-white">
							<div class="landing-block-node-card-text g-mb-25">The customisation options you implemented are countless, and I
								feel sorry I can\'t use them all. Good job, and keep going! are countless, and I feel
							</div>
							<h6 class="landing-block-node-card-name text-uppercase g-font-weight-700 g-color-white mb-0">Someone someone</h6>
						</div>
					</div>
					<!-- End Testimonial Block -->
				</div>

				<div class="landing-block-node-card js-slide">
					<!-- Testimonial Block -->
					<div class="landing-block-node-card-container js-animation fadeIn media d-block d-md-flex">
						<div class="g-mb-30 g-mb-0--md g-pr-30--sm">
							<img class="landing-block-node-card-photo img-fluid rounded-circle img-bordered g-brd-white mx-auto"
								 src="https://cdn.bitrix24.site/bitrix/images/landing/business/120x120/img13.jpg" alt="">
						</div>

						<div class="media-body align-self-center text-center text-sm-left g-color-white">
							<div class="landing-block-node-card-text g-mb-25">The customisation options you implemented are countless, and I
								feel sorry I can\'t use them all. Good job, and keep going! are countless, and I feel
							</div>
							<h6 class="landing-block-node-card-name text-uppercase g-font-weight-700 g-color-white mb-0">Someone someone</h6>
						</div>
					</div>
					<!-- End Testimonial Block -->
				</div>
			</div>
		</div>
	</div>

</section>
',
			],
		'12.image_carousel_6_cols_fix' =>
			[
				'CODE' => '12.image_carousel_6_cols_fix',
				'SORT' => '8000',
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
		'04.7.one_col_fix_with_title_and_text_2@5' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-bg-gray-light-v5 g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-26">CONTACT US</h2>
            </div>

			<div class="landing-block-node-text"><p>Curabitur ullamcorper ultricies nisi. Nam eget dui.</p></div>
        </div>

    </section>',
			],
		'33.10.form_2_light_left_text' =>
			[
				'CODE' => '33.10.form_2_light_left_text',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-pos-rel landing-block g-pt-20 g-pb-60">

	<div class="container">

		<div class="row">
			<div class="col-md-6">
				<div class="text-center g-overflow-hidden">
					<h3 class="landing-block-node-main-title text-uppercase g-font-weight-700 g-mb-20"></h3>

					<div class="landing-block-node-text g-line-height-1_5 text-left g-mb-40">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor.
							Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum,
							enim orci viverra eros, fringilla porttitor lorem eros vel odio.
						</p>
					</div>
					<div class="g-mx-minus-2 g-my-minus-2">
						<div class="row mx-0">

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="text">
								<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
									<i class="landing-block-card-contact-icon icon-anchor"></i>
								</span>
								<span class="landing-block-card-contact-title h3 d-block text-uppercase g-font-size-11 mb-0">
									Address</span>
								<span class="landing-block-card-contact-text g-font-weight-700 g-font-size-11">
									Sit amet adipiscing
								</span>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-call-in"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Phone number
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmPhoneTitle1
									</span>
								</a>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="mailto:#crmEmail1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-line icon-envelope-letter"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Email
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmEmailTitle1
									</span>
								</a>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-earphones-alt"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Toll free
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmPhoneTitle1
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
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
		'17.1.copyright_with_social' =>
			[
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-black-opacity-0_8 js-animation animation-none">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 js-animation animation-none">
					&copy; 2022 All rights reserved.
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
	],
];