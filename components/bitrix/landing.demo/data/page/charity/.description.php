<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_CHARITY_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_CHARITY_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'charity',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/charity/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_CHARITY_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CHARITY_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_CHARITY_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CHARITY_DESCRIPTION')
		)
	),
	'items' => array (
		'0.menu_06_charity' =>
			array (
				'CODE' => '0.menu_06_charity',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float">
	<div class="u-header__section g-transition-0_3 g-py-15" data-header-fix-moment-exclude="g-bg-black-opacity-0_5 g-bg-transparent--lg g-py-15" data-header-fix-moment-classes="u-header__section--light u-shadow-v27 g-bg-white g-py-12">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main d-block g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/charity-logo-light.png" alt=""
						 data-header-fix-moment-exclude="d-block"
						 data-header-fix-moment-classes="d-none">

					<img class="landing-block-node-menu-logo2 u-header__logo-img u-header__logo-img--main d-none g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/charity-logo-dark.png" alt=""
						 data-header-fix-moment-exclude="d-none"
						 data-header-fix-moment-classes="d-block">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_blocks_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">YOUR HELP</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">OUR PROJECTS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[40.3.text_blocks_carousel_with_bgimg]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SUCCESS STORIES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">DONATORS</a>
						</li>
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BLOG&amp;NEWS</a>
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
			),
		'01.big_with_text_blocks_2' =>
			array (
				'CODE' => '01.big_with_text_blocks_2',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel"
		 data-infinite="1"
		 data-fade="1"
		 data-speed="5000"
		 data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
		<div class="landing-block-node-card-block js-slide">
			<div class="landing-block-node-card-bgimg h-100 d-flex align-items-center u-bg-overlay g-color-white g-bg-black-opacity-0_5--after g-bg-img-hero g-min-height-100vh"
				 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img3.jpg);">
				<div class="landing-block-card-container u-bg-overlay__inner w-100 d-flex justify-content-center g-py-40">
					<div class="container g-mr-0 g-ml-0 g-pl-0 g-pr-0">
						<h2 class="landing-block-node-card-title landing-semantic-title-image-medium js-animation slideInUp h2 text-uppercase g-line-height-1_2 g-letter-spacing-1 g-font-size-65 g-color-white g-mb-40">
							For some,
							<br> <span style="font-weight: bold;">food is a luxury</span></h2>

						<div class="landing-block-node-card-buttons js-animation slideInUp g-mb-45">
							<div class="landing-node-card-buttons-container d-inline-flex g-mr-10 g-mb-10 g-mb-0--md">
								<div class="landing-block-node-card-label-title landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label u-ns-bg-v7-right g-bg-black g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									Need
								</div>
								<div class="landing-block-node-card-label-text landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label g-color-gray-dark-v1 g-font-weight-700 g-bg-primary g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									$1 250 000
								</div>
							</div>

							<div class="landing-node-card-buttons-container2 d-inline-flex g-mb-10 g-mb-0--md">
								<div class="landing-block-node-card-label-title2 landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label u-ns-bg-v7-right g-bg-black g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									We have
								</div>
								<div class="landing-block-node-card-label-text2 landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label g-color-gray-dark-v1 g-font-weight-700 g-bg-primary g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									$175 586
								</div>
							</div>
						</div>

						<div class="landing-block-node-card-text landing-semantic-text-image-small js-animation slideInUp g-max-width-800 g-color-white-opacity-0_7 g-mb-45">
							<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus
								dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor</p>
						</div>

						<div class="landing-block-node-card-buttons2 js-animation slideInUp">
							<a href="#"
							   class="landing-block-node-card-link landing-block-node-card-link1 landing-semantic-link-image-medium btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 g-py-20--md g-mr-10">
								Donate now</a>
							<a href="#"
							   class="landing-block-node-card-link landing-block-node-card-link1 landing-semantic-link-image-medium btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 g-py-20--md g-mr-10">Learn
								more</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card-block js-slide">
			<div class="landing-block-node-card-bgimg h-100 d-flex align-items-center u-bg-overlay g-color-white g-bg-black-opacity-0_5--after g-bg-img-hero g-min-height-100vh"
				 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img4.jpg);">
				<div class="landing-block-card-container u-bg-overlay__inner w-100 d-flex justify-content-center g-py-40">
					<div class="container g-mr-0 g-ml-0 g-pl-0 g-pr-0">
						<h2 class="landing-block-node-card-title landing-semantic-title-image-medium js-animation slideInUp h2 text-uppercase g-line-height-1_2 g-letter-spacing-1 g-font-size-65 g-color-white g-mb-40">
							For some,
							<br><span style="font-weight: bold;">support is a necessity</span></h2>

						<div class="landing-block-node-card-buttons js-animation slideInUp g-mb-45">
							<div class="landing-node-card-buttons-container d-inline-flex g-mr-10 g-mb-10 g-mb-0--md">
								<div class="landing-block-node-card-label-title landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label u-ns-bg-v7-right g-bg-black g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									Need
								</div>
								<div class="landing-block-node-card-label-text landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label g-color-gray-dark-v1 g-font-weight-700 g-bg-primary g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									$1 250 000
								</div>
							</div>

							<div class="landing-node-card-buttons-container2 d-inline-flex g-mb-10 g-mb-0--md">
								<div class="landing-block-node-card-label-title2 landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label u-ns-bg-v7-right g-bg-black g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									We have
								</div>
								<div class="landing-block-node-card-label-text2 landing-semantic-text-image-small g-font-size-11 text-uppercase g-font-weight-700 u-label g-color-gray-dark-v1 g-font-weight-700 g-bg-primary g-pl-20 g-pr-20 g-pt-14 g-pb-14">
									$175 586
								</div>
							</div>
						</div>

						<div class="landing-block-node-card-text landing-semantic-text-image-small js-animation slideInUp g-max-width-800 g-color-white-opacity-0_7 g-mb-45">
							<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus
								dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor</p>
						</div>

						<div class="landing-block-node-card-buttons2 js-animation slideInUp">
							<a href="#"
							   class="landing-block-node-card-link landing-block-node-card-link1 landing-semantic-link-image-medium btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 g-py-20--md g-mr-10">
								Donate now</a>
							<a href="#"
							   class="landing-block-node-card-link landing-block-node-card-link1 landing-semantic-link-image-medium btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 g-py-20--md g-mr-10">Learn
								more</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-65 g-pb-20 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">HOW YOU CAN HELP</span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which do not look even slightly believable.</p></div>
            </div>
        </div>

    </section>',
			),
		'37.1.two_img_with_text_blocks' =>
			array (
				'CODE' => '37.1.two_img_with_text_blocks',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-py-15 g-pt-0 g-pb-65">
	<div class="container">
		<div class="row">
			<div class="col-lg-6">
				<!-- Article -->
				<article class="row align-items-stretch text-center g-color-gray-dark-v5 g-bg-black mx-0">
					<!--Article Content-->
					<div class="landing-block-node-block col-sm-6 u-ns-bg-v1-bottom u-ns-bg-bottom u-ns-bg-v1-right--md g-bg-black g-px-30 g-py-45">
						<h3 class="landing-block-node-title text-uppercase g-font-size-20 g-color-white g-mb-25 js-animation flipInX">Become
							a
							<span style="font-weight: bold;" class="landing-block-node-title-add d-block g-color-primary">Volunteer</span></h3>
						<div class="landing-block-node-text g-mb-25 js-animation fadeIn"><p>Praesent pulvinar gravida efficitur. Aenean
								bibendum purus eu nisi pulvinar
								venenatis vitae non velit.</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn g-btn-type-solid g-btn-primary g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 js-animation fadeIn" href="#">Join Us</a>
						</div>
					</div>
					<!-- End Article Content -->

					<!-- Article Image -->
					<div class="landing-block-node-img col-sm-6 px-0 u-bg-overlay g-bg-black-opacity-0_2--after g-bg-img-hero g-min-height-300" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x842/img1.jpg\');"></div>
					<!-- End Article Image -->
				</article>
				<!-- End Article -->
			</div>

			<div class="col-lg-6">
				<!-- Article -->
				<article class="row align-items-stretch text-center g-color-gray-dark-v5 g-bg-black mx-0">
					<!-- Article Image -->
					<div class="landing-block-node-img col-sm-6 px-0 u-bg-overlay g-bg-black-opacity-0_2--after g-bg-img-hero g-min-height-300" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x842/img2.jpg\');"></div>
					<!-- End Article Image -->

					<!--Article Content-->
					<div class="landing-block-node-block col-sm-6 u-ns-bg-v1-top u-ns-bg-v1-left--md g-bg-black g-px-30 g-py-45">
						<h3 class="landing-block-node-title text-uppercase g-font-size-20 g-color-white g-mb-25 js-animation flipInX">Make a
							<span style="font-weight: bold;" class="landing-block-node-title-add d-block g-color-primary">Donation</span></h3>
						<div class="landing-block-node-text g-mb-25 js-animation fadeIn"><p>Praesent pulvinar gravida efficitur. Aenean
								bibendum purus eu nisi pulvinar
								venenatis vitae non velit.</p></div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button btn g-btn-type-solid g-btn-primary g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-color-gray-dark-v1 g-color-gray-dark-v1--hover rounded-0 g-py-10 js-animation fadeIn" href="#">Join Us</a>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2@2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-65 g-pb-20 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">OUR PROJECTS</span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Nullam in diam arcu. Etiam nisl justo, tempor scelerisque sagittis vel, bibendum vestibulum metus. Donec eget nunc neque.</p></div>
            </div>
        </div>

    </section>',
			),
		'30.2.three_cols_fix_img_and_links' =>
			array (
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-30 g-pb-20">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pos-rel">
                            <!-- SVG Background -->
                            <svg class="g-hidden-col-1 g-hidden-col-2 g-pos-abs g-left-0 g-right-0" version="1.1" preserveaspectratio="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px" viewbox="20 -20 300 100" style="top: -70%;">
                                <path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729
              c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z" opacity="0.4" fill="#f0f1f3" />
                                <path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729
              c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z" opacity="0.4" fill="#f0f1f3" />
                                <path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716
              H42.401L43.415,98.342z" opacity="0" fill="#fff" />
                                <path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428
              c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z" fill="#fff" />
                            </svg>
                            <!-- End SVG Background -->

                            <div class="g-pos-rel g-z-index-1 g-pa-30">
                                <h3 class="h5 mb-3">
                                    <a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#" target="_self">Aenean bibendum purus eu nisi pulvinar venenatis vitae</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#" target="_self">DONATE NOW</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img6.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pos-rel">
                            <!-- SVG Background -->
                            <svg class="g-hidden-col-1 g-hidden-col-2 g-pos-abs g-left-0 g-right-0" version="1.1" preserveaspectratio="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px" viewbox="20 -20 300 100" style="top: -70%;">
                                <path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729
              c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z" opacity="0.4" fill="#f0f1f3" />
                                <path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729
              c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z" opacity="0.4" fill="#f0f1f3" />
                                <path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716
              H42.401L43.415,98.342z" opacity="0" fill="#fff" />
                                <path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428
              c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z" fill="#fff" />
                            </svg>
                            <!-- End SVG Background -->

                            <div class="g-pos-rel g-z-index-1 g-pa-30">
                                <h3 class="h5 mb-3">
                                    <a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#" target="_self">Aenean bibendum purus eu nisi pulvinar venenatis vitae</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#" target="_self">DONATE NOW</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

						<div class="g-pos-rel">
							<!-- SVG Background -->
							<svg class="g-hidden-col-1 g-hidden-col-2 g-pos-abs g-left-0 g-right-0" version="1.1" preserveaspectratio="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px" viewbox="20 -20 300 100" style="top: -70%;">
								<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729
              c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z" opacity="0.4" fill="#f0f1f3" />
								<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729
              c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z" opacity="0.4" fill="#f0f1f3" />
								<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716
              H42.401L43.415,98.342z" opacity="0" fill="#fff" />
								<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428
              c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z" fill="#fff" />
							</svg>
							<!-- End SVG Background -->

							<div class="g-pos-rel g-z-index-1 g-pa-30">
								<h3 class="h5 mb-3">
									<a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#" target="_self">Aenean bibendum purus eu nisi pulvinar venenatis vitae</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#" target="_self">DONATE NOW</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			),
		'38.1.text_with_bgimg_img_and_text_blocks' =>
			array (
				'CODE' => '38.1.text_with_bgimg_img_and_text_blocks',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-black-opacity-0_7--after g-bg-img-hero g-py-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x773/img1.jpg);">
	<div class="u-bg-overlay__inner">
		<div class="container">
			<!-- Products Block -->
			<div class="row">
				<div class="col-md-6 col-lg-4">
					<!-- Article -->
					<article class="u-shadow-v19 landing-block-node-leftblock g-bg-primary js-animation fadeInLeft">
						<!-- Article Image -->
						<img class="landing-block-node-leftblock-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x811/img1.jpg" alt="" />
						<!-- End Article Image -->

						<!-- Article Content -->
						<div class="g-py-40 g-px-35">
							<h3 class="landing-block-node-leftblock-subtitle h6 text-uppercase g-font-weight-700 g-mb-15">
								In total we collected
							</h3>
							<h3 class="landing-block-node-leftblock-title d-block g-color-black g-line-height-1_2 g-letter-spacing-minus-2 g-font-size-46">
								$8 789 576.00
							</h3>
							<div class="landing-block-node-leftblock-text">
								<p class="g-color-black-opacity-0_5 g-mb-35">Runc ullamcorper, justo a iaculis
									elementum,
									enim orci viverra eros, fringilla porttitor lorem eros vel odio gravida sollicitudin
									tortor.</p>
							</div>
							<a class="landing-block-node-leftblock-button g-valign-middle btn g-btn-size-sm g-btn-type-solid g-width-100x text-uppercase g-btn-black rounded-0 g-py-16" href="#">
								Donate Now
							</a>
						</div>
						<!-- End Article Content -->
					</article>
					<!-- End Article -->
				</div>

				<div class="col-md-6 col-lg-8 g-py-20 landing-block-node-rightblock js-animation fadeInRight">
					<div class="g-color-white g-mb-45">
						<h4 class="landing-block-node-subtitle text-uppercase g-font-weight-700 g-font-size-26 g-color-primary mb-0">
							We make the world better</h4>
						<h2 class="landing-block-node-title display-5 text-uppercase g-color-white g-font-weight-700 g-font-size-46 g-mb-30">
							Let\'s do this together</h2>
						<div class="landing-block-node-text">
							<p class="mb-0">Aenean bibendum purus eu nisi pulvinar venenatis vitae non velit. Sed et
								eleifend mi. Fusce dictum orci libero, congue scelerisque lectus pulvinar eu.
								Suspendisse pulvinar facilisis ligula vel pharetra. Vestibulum volutpat porttitor ex a
								rutrum. Aenean consectetur risus ultricies enim finibus lobortis non at ipsum.</p>
						</div>
					</div>

					<!-- Icon Blocks -->
					<div class="row g-mb-90">
						<div class="landing-block-node-card col-md-6 g-mb-30">
							<!-- Icon Blocks -->
							<div class="media g-mb-25">
								<div class="d-flex align-self-center g-mr-5">
                        <span class="landing-block-node-card-icon-container d-block g-color-primary g-font-size-38">
                          <i class="landing-block-node-card-icon icon-education-024"></i>
                        </span>
								</div>

								<div class="media-body align-self-center">
									<h3 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-white mb-0">
										Education</h3>
								</div>
							</div>

							<div class="landing-block-node-card-text">
								<p class="mb-0">Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum
									fringilla velit rhoncus euismod rhoncus turpis. Mauris molestie ullamcorper nisl
									eget
									hendrerit.</p>
							</div>
							<!-- End Icon Blocks -->
						</div>

						<div class="landing-block-node-card col-md-6 g-mb-30">
							<!-- Icon Blocks -->
							<div class="media g-mb-25">
								<div class="d-flex align-self-center g-mr-5">
                        <span class="landing-block-node-card-icon-container d-block g-color-primary g-font-size-38">
                          <i class="landing-block-node-card-icon icon-medical-004"></i>
                        </span>
								</div>

								<div class="media-body align-self-center">
									<h3 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-white mb-0">
										Health</h3>
								</div>
							</div>

							<div class="landing-block-node-card-text">
								<p class="mb-0">Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum
									viverra justo odio purus a libero luctus. Proin tempor dolor ac dolor feugiat,
									placerat
									malesuada.</p>
							</div>
							<!-- End Icon Blocks -->
						</div>
					</div>
					<!-- End Icon Blocks -->

					<div class="clearfix text-uppercase g-color-white g-font-size-11">
						<div class="landing-block-node-label-right float-right">Our Goal:
							<span style="font-weight: bold; color:#f5f219 !important">$15 500 000</span>
						</div>
						<div class="landing-block-node-label-left">
							Time left: <span style="font-weight: bold; color:#f5f219 !important">365</span> Days
						</div>
					</div>
				</div>
			</div>
			<!-- End Products Block -->
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2@3' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-65 g-pb-20 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">EMERGENCY HELP</span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus</p></div>
            </div>
        </div>

    </section>',
			),
		'37.3.two_cols_blocks_carousel' =>
			array (
				'CODE' => '37.3.two_cols_blocks_carousel',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-40 g-pb-40 g-bg-gray-light-v5">
	<div class="container">
		<!-- Carousel -->
		<div class="js-carousel u-carousel-v5 g-pb-85 g-mx-minus-15"
			 data-slides-show="2"
			 data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--x--md g-bottom-0 g-width-45 g-height-45 g-color-white g-color-black--hover g-bg-gray-light-v3 g-bg-primary--hover g-transition-0_2 g-transition--ease-in"
			 data-arrow-left-classes="fa fa-chevron-left g-left-15 g-left-50x--md g-ml-minus-40--md"
			 data-arrow-right-classes="fa fa-chevron-right g-right-15 g-left-50x--md g-ml-40--md"
			 data-responsive=\'[{
                 "breakpoint": 1200,
                 "settings": {
                   "slidesToShow": 2
                 }
               }, {
                 "breakpoint": 992,
                 "settings": {
                   "slidesToShow": 1
                 }
               }]\'>
			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img4.jpg\');">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h4 class="landing-block-node-card-title text-uppercase g-font-weight-700 h6 g-color-white g-mb-15 js-animation fadeInLeft">
							Education Project</h4>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight">
							<p>Mauris molestie ullamcorper nisl eget hendrerit. Sed faucibus suscipit justo,
								eu dignissim tellus pretium et.
							</p>
						</div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right">
								<span style="font-weight: bold;color: #f5f219 !important;">274</span> Days Left
							</div>
							<div class="landing-block-node-card-label-left">
								Our Goal: <span style="font-weight: bold;color: #f5f219 !important;">$760 000</span>
							</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle btn-block text-uppercase g-btn-primary g-color-gray-dark-v1 rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#">
							Donate Now
						</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img5.jpg\');">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h4 class="landing-block-node-card-title text-uppercase g-font-weight-700 h6 g-color-white g-mb-15 js-animation fadeInLeft">
							Water Project</h4>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight">
							<p>Mauris molestie ullamcorper nisl eget hendrerit. Sed faucibus suscipit justo,
								eu dignissim tellus pretium et.
							</p>
						</div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right">
								<span style="font-weight: bold;color: #f5f219 !important;">199</span> Days Left
							</div>
							<div class="landing-block-node-card-label-left">
								Our Goal: <span style="font-weight: bold;color: #f5f219 !important;">$2 600 000</span>
							</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle btn-block text-uppercase g-btn-primary g-color-gray-dark-v1 rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#">
							Donate Now
						</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img6.jpg\');">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h4 class="landing-block-node-card-title text-uppercase g-font-weight-700 h6 g-color-white g-mb-15 js-animation fadeInLeft">
							Education Project</h4>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight">
							<p>Mauris molestie ullamcorper nisl eget hendrerit. Sed faucibus suscipit justo,
								eu dignissim tellus pretium et.
							</p>
						</div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right">
								<span style="font-weight: bold;color: #f5f219 !important;">274</span> Days Left
							</div>
							<div class="landing-block-node-card-label-left">
								Our Goal: <span style="font-weight: bold;color: #f5f219 !important;">$760 000</span>
							</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle btn-block text-uppercase g-btn-primary g-color-gray-dark-v1 rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#">
							Donate Now
						</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img7.jpg\');">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h4 class="landing-block-node-card-title text-uppercase g-font-weight-700 h6 g-color-white g-mb-15 js-animation fadeInLeft">
							Water Project</h4>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight">
							<p>Mauris molestie ullamcorper nisl eget hendrerit. Sed faucibus suscipit justo,
								eu dignissim tellus pretium et.
							</p>
						</div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right">
								<span style="font-weight: bold;color: #f5f219 !important;">199</span> Days Left
							</div>
							<div class="landing-block-node-card-label-left">
								Our Goal: <span style="font-weight: bold;color: #f5f219 !important;">$2 600 000</span>
							</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle btn-block text-uppercase g-btn-primary g-color-gray-dark-v1 rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#">
							Donate Now
						</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>
		</div>
		<!-- End Carousel -->
	</div>
</section>',
			),
		'40.3.text_blocks_carousel_with_bgimg' =>
			array (
				'CODE' => '40.3.text_blocks_carousel_with_bgimg',
				'SORT' => '4500',
				'CONTENT' => '<section
		class="landing-block js-animation fadeIn landing-block-node-bgimg u-bg-overlay g-bg-black-opacity-0_7--after g-bg-img-hero g-pt-100 g-pb-100"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x964/img1.jpg);">
	<div class="u-bg-overlay__inner">
		<div class="container g-max-width-780 text-center g-mb-60">
			<div class="text-center u-heading-v8-1 g-mb-35">
				<h2 class="landing-block-node-title h3 text-uppercase u-heading-v8__title g-font-weight-700 g-font-size-26 g-color-white mb-0">
					Success
					<span style="font-weight: bold;" class="g-color-primary">stories</span></h2>
			</div>

			<div class="landing-block-node-text mb-0 g-color-white">
				<p>Sed faucibus suscipit justo, eu dignissim tellus pretium et. Nam volutpat placerat libero
					sit amet elementum. Curabitur et neque et mauris maximus efficitur.</p>
			</div>
		</div>

		<div class="container">
			<!-- End Carousel v19 -->
			<section class="js-carousel g-pt-60 g-pt-0--md"
					 data-infinite="1"
					 data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--x--md g-top-0 g-width-40 g-height-40 g-color-white g-color-black--hover g-bg-black g-bg-primary--hover g-transition-0_2 g-transition--ease-in"
					 data-arrow-left-classes="fa fa-chevron-left g-left-0 g-left-50x--md g-ml-35--md"
					 data-arrow-right-classes="fa fa-chevron-right g-right-0 g-right-50x--md g-ml-85--md">
				<div class="landing-block-node-card js-slide">
					<div class="row justify-content-end">
						<div class="col-md-6 g-color-white g-pt-80--md">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-5">
								Catherine
								Cameron</h3>
							<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-10 g-color-primary g-mb-16">
								Molestie
								ullamcorper nisl eget</h4>
							<div class="landing-block-node-card-text g-mb-0">
								<p>Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus, viverra eu
									maximus non, tincidunt sodales massa. Duis vulputate purus a libero luctus, sed
									dictum
									ante interdum. Nam vel leo ultricies, pretium magna at, consequat augue. Quisque
									bibendum vel enim quis pulvinar. Proin ac quam erat.</p>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-node-card js-slide">
					<div class="row justify-content-end">

						<div class="col-md-6 g-color-white g-pt-80--md">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-5">
								Catherine
								Cameron</h3>
							<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-10 g-color-primary g-mb-16">
								Molestie
								ullamcorper nisl eget</h4>
							<div class="landing-block-node-card-text g-mb-0">
								<p>Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus, viverra eu
									maximus non, tincidunt sodales massa. Duis vulputate purus a libero luctus, sed
									dictum
									ante interdum. Nam vel leo ultricies, pretium magna at, consequat augue. Quisque
									bibendum vel enim quis pulvinar. Proin ac quam erat.</p>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- End Carousel v19 -->
		</div>
	</div>
</section>
',
			),
		'27.one_col_fix_title_and_text_2@4' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-65 g-pb-20 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">BEST DONATORS</span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus</p></div>
            </div>
        </div>

    </section>',
			),
		'20.3.four_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20 g-bg-gray-light-v5">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20 g-line-height-0">MARK SPENCER</h3>
				<div class="landing-block-node-text"><p>molestie ullamcorper<br /><span style="font-weight: bold;">$11 250 000</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20 g-line-height-0">REBECCA KENTON</h3>
				<div class="landing-block-node-text"><p>molestie ullamcorper<br /><span style="font-weight: bold;">$690 000</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20 g-line-height-0">DAVID CASE</h3>
				<div class="landing-block-node-text"><p>molestie ullamcorper<br /><span style="font-weight: bold;">$420 000</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20 g-line-height-0">MONICA GRAY</h3>
				<div class="landing-block-node-text"><p>molestie ullamcorper<br /><span style="font-weight: bold;">$1 250 000</span></p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'13.2.one_col_fix_button' =>
			array (
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block text-center g-py-20 g-bg-gray-light-v5 g-pb-60">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-btn-primary rounded-0 g-color-black-opacity-0_9" href="#" g-font-weight-700="" target="_self">BECOME A DONATOR</a>
        </div>
    </section>',
			),
		'27.one_col_fix_title_and_text_2@5' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-pt-65 g-pb-20 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">LATEST POSTS</span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus</p></div>
            </div>
        </div>

    </section>',
			),
		'20.2.three_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x450/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text"><p>Integer vitae dolor eleifend, congue neque id, elementum mauris. Nullam molestie pretium velit, ut iaculis mauris hendrerit sedeget nibh commodo.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x450/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text"><p>Integer vitae dolor eleifend, congue neque id, elementum mauris. Nullam molestie pretium velit, ut iaculis mauris hendrerit sedeget nibh commodo.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x450/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text"><p>Integer vitae dolor eleifend, congue neque id, elementum mauris. Nullam molestie pretium velit, ut iaculis mauris hendrerit sedeget nibh commodo.</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'13.2.one_col_fix_button@2' =>
			array (
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block text-center g-py-20 g-pb-60">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-btn-primary rounded-0 g-color-black" href="#" g-font-weight-700="" target="_self">VIEW ALL POSTS</a>
        </div>
    </section>',
			),
		'35.2.footer_dark' =>
			array (
				'CODE' => '35.2.footer_dark',
				'SORT' => '8000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60 g-bg-gray-dark-v1 g-pb-0">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">TEXT WIDGET</h2>
				<p class="landing-block-node-text g-mb-20 g-color-gray-dark-v5">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae est lorem. Aenean imperdiet nisi a dolor condimentum, id ullamcorper lacus vestibulum. Praesent pulvinar gravida. Aenean lobortis ante ac porttitor eleifend.</p>

				<address class="g-color-gray-light-v1 g-mb-20">
				

				

				
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">USEFUL LINKS</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Pellentesque a tristique risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Nunc vitae libero lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Pellentesque a tristique risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Nunc vitae libero lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			),
		'17.1.copyright_with_social' =>
			array (
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-black js-animation animation-none">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 g-color-white js-animation animation-none">
					&copy; 2018 All rights reserved.
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
			),
	)
);