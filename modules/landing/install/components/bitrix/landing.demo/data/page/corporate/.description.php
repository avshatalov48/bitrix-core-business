<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_CORPORATE_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_CORPORATE_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => '3corporate',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/corporate/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_CORPORATE_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CORPORATE_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_CORPORATE_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CORPORATE_DESCRIPTION')
		)
	),
	'items' => array (
		'0.menu_09_corporate' =>
			array (
				'CODE' => '0.menu_09_corporate',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--relative">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-7 g-py-23--md" data-header-fix-moment-exclude="g-py-23--md" data-header-fix-moment-classes="g-py-17--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/corporate-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-1 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[46.7.cover_bgimg_text_blocks_with_icons]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[02.three_cols_big_1]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PORTFOLIO</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[11.three_cols_fix_tariffs]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PRICING</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TEAM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BLOG</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[01.big_with_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
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
		'46.7.cover_bgimg_text_blocks_with_icons' =>
			array (
				'CODE' => '46.7.cover_bgimg_text_blocks_with_icons',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="landing-block-node-bgimg g-min-height-100vh u-bg-overlay g-flex-centered g-bg-img-hero g-bg-black-opacity-0_2--after g-mb-minus-140"
		 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img6.jpg);">
		<div class="u-bg-overlay__inner w-100">
			<div class="container g-pos-rel g-z-index-1 g-mt-50--md">
				<div class="row align-items-center">
					<div class="landing-block-node-container js-animation fadeInLeft col-sm-10 col-lg-8">
						<h1 class="landing-block-node-title g-color-white g-font-weight-300 g-font-size-50 g-mb-30 g-mb-50--sm">
							Company24 Responsive
							Template</h1>

						<div class="row">
							<div class="landing-block-node-card col-md-6 g-mb-10 g-mb-30--md">
								<div class="media">
                      <span class="landing-block-node-card-icon-container d-flex u-icon-v1 g-width-50 g-height-50 g-color-white g-bg-white-opacity-0_1 g-font-size-26 g-line-height-1 rounded-circle g-pos-rel g-pa-12 mr-3">
                        <i class="landing-block-node-card-icon icon-communication-114 u-line-icon-pro"></i>
                      </span>
									<div class="media-body">
										<div class="landing-block-node-card-title g-color-white g-font-weight-500 g-font-size-30">
											31,500+
										</div>
										<h2 class="landing-block-node-card-text lead g-color-white-opacity-0_9">Happy
											clients all over the world</h2>
									</div>
								</div>
							</div>

							<div class="landing-block-node-card col-md-6 g-mb-10 g-mb-30--md">
								<div class="media">
                      <span class="landing-block-node-card-icon-container d-flex u-icon-v1 g-width-50 g-height-50 g-color-white g-bg-white-opacity-0_1 g-font-size-26 g-line-height-1 rounded-circle g-pos-rel g-pa-12 mr-3">
                        <i class="landing-block-node-card-icon icon-communication-116 u-line-icon-pro"></i>
                      </span>
									<div class="media-body">
										<div class="landing-block-node-card-title g-color-white g-font-weight-500 g-font-size-30">
											1610+
										</div>
										<h2 class="landing-block-node-card-text lead g-color-white-opacity-0_9">UI
											Elements &amp; Features</h2>
									</div>
								</div>
							</div>

							<div class="landing-block-node-card col-md-6 g-mb-10 g-mb-30--md">
								<div class="media">
                      <span class="landing-block-node-card-icon-container d-flex u-icon-v1 g-width-50 g-height-50 g-color-white g-bg-white-opacity-0_1 g-font-size-26 g-line-height-1 rounded-circle g-pos-rel g-pa-12 mr-3">
                        <i class="landing-block-node-card-icon icon-finance-091 u-line-icon-pro"></i>
                      </span>
									<div class="media-body">
										<div class="landing-block-node-card-title g-color-white g-font-weight-500 g-font-size-30">
											No. 1
										</div>
										<h2 class="landing-block-node-card-text lead g-color-white-opacity-0_9">
											WrapBootstrap theme of all time</h2>
									</div>
								</div>
							</div>

							<div class="landing-block-node-card col-md-6 g-mb-10 g-mb-30--md">
								<div class="media">
                      <span class="landing-block-node-card-icon-container d-flex u-icon-v1 g-width-50 g-height-50 g-color-white g-bg-white-opacity-0_1 g-font-size-26 g-line-height-1 rounded-circle g-pos-rel g-pa-12 mr-3">
                        <i class="landing-block-node-card-icon icon-education-085 u-line-icon-pro"></i>
                      </span>
									<div class="media-body">
										<div class="landing-block-node-card-title g-color-white g-font-weight-500 g-font-size-30">
											AAA
										</div>
										<h2 class="landing-block-node-card-text lead g-color-white-opacity-0_9">Maximum
											reliability rating</h2>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<svg class="g-pos-rel" version="1.1" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"
		 xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px" viewBox="20 -20 300 100">
		<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729
      c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z" opacity="0.4" fill="#f0f1f3"></path>
		<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729
      c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z" opacity="0.4" fill="#f0f1f3"></path>
		<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716
      H42.401L43.415,98.342z" opacity="0" fill="#fafbfc"></path>
		<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428
      c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z" fill="#fafbfc"></path>
	</svg>
</section>
',
			),
		'02.three_cols_big_1' =>
			array (
				'CODE' => '02.three_cols_big_1',
				'SORT' => '1000',
				'CONTENT' => '<section class="container-fluid px-0 landing-block g-bg-secondary">
        <div class="row no-gutters">
            <div class="landing-block-node-left-img g-min-height-300 col-lg-4 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/190x471/img1.png\');"></div>

            <div class="landing-block-node-center col-md-6 col-lg-4 g-flex-centered g-bg-secondary">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-center-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h4>
                        <h2 class="landing-block-node-center-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-color-white g-mb-minus-10 g-font-size-27 g-text-transform-none"><span style="font-weight: normal;">What Does Creative Digital Agency Company24 Do?</span></h2>
                    </div>

                    <p class="landing-block-node-center-text g-color-gray-light-v2 mb-0"></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 landing-block-node-right">
                <div class="js-carousel g-pb-90" data-infinite="true" data-slides-show="true" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    <div class="js-slide landing-block-card-right slick-slide ">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img11.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title g-font-weight-700 g-font-size-20 g-mb-10 g-color-black g-text-transform-none">Consult</h3>
                            <div class="landing-block-node-right-text g-color-black-opacity-0_6"><p>This is where we sit down, grab a cup of coffee and dial in the details. Understanding the task at hand and ironing out the wrinkles is key.</p></div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right slick-slide ">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img12.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title g-font-weight-700 g-font-size-20 g-mb-10 g-color-black g-text-transform-none">Plan</h3>
                            <div class="landing-block-node-right-text g-color-black-opacity-0_6"><p>Now that we have aligned the details, it is time to get things mapped out and organized. This part is really crucial in keeping the project in line to completion.</p></div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right slick-slide ">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img6.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title g-font-weight-700 g-font-size-20 g-mb-10 g-color-black g-text-transform-none">Create</h3>
                            <div class="landing-block-node-right-text g-color-black-opacity-0_6"><p>The time has come to bring those ideas and plans to life. This is where we really begin to visualize your napkin sketches and make them into beautiful pixels.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>',
			),
		'31.1.two_cols_text_img' =>
			array (
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-bg-secondary">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title g-font-weight-700 g-mb-25 g-color-black g-text-transform-none g-font-size-20">Release</h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-dark-v2"><p>Now that your brand is all dressed up and ready to party, it i<span style="font-size: 1rem;">s time to release it to the world. By the way, let&prime;s celebrate already.</span></p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-rounded-50 g-btn-primary" href="#" tabindex="0" target="_self">LEARN MORE</a>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img2.jpg\');"></div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60">

        <div class="container g-max-width-800">
            <div class="text-center">
                <h2 class="landing-block-node-title g-font-weight-400">We&amp;#039;ve Done some Amazing Jobs</h2>
                <div class="landing-block-node-text g-font-size-16 g-pb-1"><p>Experience a level of quality in both design &amp; customization.</p></div>
            </div>
        </div>

    </section>',
			),
		'20.2.three_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img5.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="color: rgb(0, 0, 0);font-family: inherit;font-weight: normal;">Design</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Graphic</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img6.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="font-weight: normal;color: rgb(0, 0, 0);font-family: inherit;">Creative agency</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Identity</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img10.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="font-weight: normal;color: rgb(0, 0, 0);font-family: inherit;">Production</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Graphic</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'20.2.three_cols_fix_img_title_text@2' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img8.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="color: rgb(0, 0, 0);font-family: inherit;font-weight: normal;">Design</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Graphic</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img9.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="font-weight: normal;color: rgb(0, 0, 0);font-family: inherit;">Creative agency</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Identity</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img7.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-17 g-text-transform-none"><p style="text-align: center;"><span style="font-weight: normal;color: rgb(0, 0, 0);font-family: inherit;">Production</span></p></h3>
				<div class="landing-block-node-text"><p style="text-align: center;">Graphic</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'13.2.one_col_fix_button' =>
			array (
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block landing-block-node-container text-center g-py-20 g-pt-20 g-pb-60">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50" href="#" g-font-weight-700="" target="_self">VIEW ALL WORKS</a>
        </div>
    </section>',
			),
		'48.2.video_button_on_bgimg' =>
			array (
				'CODE' => '48.2.video_button_on_bgimg',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block js-animation fadeIn">
	<div class="landing-block-node-bgimg g-bg-size-cover g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_1--after g-mb-minus-140 d-flex align-items-center justify-content-center g-min-height-50vh g-pt-90 g-pb-120"
		 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x800/img2.jpg);">
		<div class="landing-block-node-text-container container text-center u-bg-overlay__inner">
			<a class="landing-block-node-button u-icon-v3 u-icon-size--xl u-block-hover--scale g-overflow-inherit g-bg-white g-color-gray-dark-v1 g-color-primary--hover g-font-size-20 rounded-circle g-text-underline--none--hover g-cursor-pointer mb-3"
			   href="//www.youtube.com/watch?v=q4d8g9Dn3ww" target="_popup"
			   data-url="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&controls=1&loop=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww">
				<img class="landing-block-node-card-icon d-block g-height-20 g-left-2 g-relative-centered--y mr-auto g-ml-37"
					 src="https://cdn.bitrix24.site/bitrix/images/landing/play-black.png"/>
			</a>
			<div class="landing-block-node-text lead d-block g-color-white g-font-weight-400 g-font-size-22">
				Watch Company24 Video
			</div>
		</div>
	</div>

	<svg class="g-pos-rel" version="1.1" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"
		 xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="140px" viewBox="20 -20 300 100">
		<path d="M30.913,43.944c0,0,42.911-34.464,87.51-14.191c77.31,35.14,113.304-1.952,146.638-4.729
      c48.654-4.056,69.94,16.218,69.94,16.218v54.396H30.913V43.944z" opacity="0.4" fill="#f0f1f3"></path>
		<path d="M-35.667,44.628c0,0,42.91-34.463,87.51-14.191c77.31,35.141,113.304-1.952,146.639-4.729
      c48.653-4.055,69.939,16.218,69.939,16.218v54.396H-35.667V44.628z" opacity="0.4" fill="#f0f1f3"></path>
		<path d="M43.415,98.342c0,0,48.283-68.927,109.133-68.927c65.886,0,97.983,67.914,97.983,67.914v3.716
      H42.401L43.415,98.342z" opacity="0" fill="#fafbfc"></path>
		<path d="M-34.667,62.998c0,0,56-45.667,120.316-27.839C167.484,57.842,197,41.332,232.286,30.428
      c53.07-16.399,104.047,36.903,104.047,36.903l1.333,36.667l-372-2.954L-34.667,62.998z" fill="#fafbfc"></path>
	</svg>
</section>',
			),
		'03.1.three_cols_big_with_text_and_titles' =>
			array (
				'CODE' => '03.1.three_cols_big_with_text_and_titles',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block container-fluid px-0 g-bg-secondary">
        <div class="landing-block-inner-container row no-gutters align-items-start">

            <div class="landing-block-card col-md-6 g-flex-centered  col-lg-3">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-blue g-font-size-32"><span style="font-weight: normal;">&#8470;1</span></h2>
                    </div>

                    <div class="landing-block-node-text g-color-black-opacity-0_5"><p><span style="color: rgb(117, 117, 117);">Theme on WrapBootstrap</span></p></div>
                </div>
            </div>

            <div class="landing-block-card col-md-6 g-flex-centered  col-lg-3">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-blue g-font-size-32"><span style="font-weight: normal;">4</span></h2>
                    </div>

                    <div class="landing-block-node-text g-color-black-opacity-0_5"><p><span style="color: rgb(117, 117, 117);">Years in Business</span></p></div>
                </div>
            </div>

            <div class="landing-block-card col-md-6 g-flex-centered  col-lg-3">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-blue g-font-size-32"><span style="font-weight: normal;">10</span></h2>
                    </div>

                    <div class="landing-block-node-text g-color-black-opacity-0_5"><p><span style="color: rgb(117, 117, 117);">Creative Workers</span></p></div>
                </div>
            </div>

        <div class="landing-block-card col-md-6 g-flex-centered  col-lg-3">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20" ></h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-blue g-font-size-32"><span style="font-weight: normal;">50</span></h2>
                    </div>

                    <div class="landing-block-node-text g-color-black-opacity-0_5"><p><span style="color: rgb(117, 117, 117);">Projects Completed</span></p></div>
                </div>
            </div></div>
    </section>',
			),
		'11.three_cols_fix_tariffs' =>
			array (
				'CODE' => '11.three_cols_fix_tariffs',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-30 g-pb-20 g-bg-secondary">
        <div class="container">

            <div class="row no-gutters landing-block-inner">

                <div class="landing-block-card js-animation fadeInUp col-md-4 g-mb-30 g-mb-0--md  col-lg-6">
                    <article class="text-center g-brd-around g-color-gray g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h4 class="landing-block-node-title text-uppercase h5 g-color-gray-dark-v3 g-font-weight-500 g-mb-10 g-color-black-opacity-0_9"><span style="font-weight: bold;">SINGLE</span></h4>
                            <div class="landing-block-node-subtitle g-font-style-normal" />

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2 g-font-size-16">$25.00</div>
                                <div class="landing-block-node-price-text" />
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">400+ pages<br /></li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5"><span style="font-size: 1rem;">1610+ elements</span></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">24/7 support</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">Limited domain and users</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button g-color-white-opacity-0_9 btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
							</div>
                        </div>
                    </article>
                </div>



                <div class="landing-block-card js-animation fadeInUp col-md-4 g-mb-30 g-mb-0--md  col-lg-6">
                    <article class="text-center g-brd-around g-color-gray g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h4 class="landing-block-node-title text-uppercase h5 g-color-gray-dark-v3 g-font-weight-500 g-mb-10 g-color-black-opacity-0_9"><span style="font-weight: bold;">MULTIPLE</span></h4>
                            <div class="landing-block-node-subtitle g-font-style-normal" />

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2 g-font-size-16">$125.00</div>
                                <div class="landing-block-node-price-text" />
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">400+ pages<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5"><span style="font-size: 1rem;">1610+ elements</span></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">24/7 support</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black-opacity-0_5">Unlimited domain or user</li></ul>

                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button g-color-white-opacity-0_9 btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
							</div>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </section>',
			),
		'12.image_carousel_6_cols_fix' =>
			array (
				'CODE' => '12.image_carousel_6_cols_fix',
				'SORT' => '5500',
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
			),
		'27.one_col_fix_title_and_text_2@2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60">

        <div class="container g-max-width-800">
            <div class="text-center">
                <h2 class="landing-block-node-title g-font-weight-400">The Creative Team</h2>
                <div class="landing-block-node-text g-font-size-16 g-pb-1">
					<p>We\'re an ambitious workaholic, but apart from that, pretty simple persons.</p>
				</div>
            </div>
        </div>

    </section>',
			),
		'28.personal_slider_2' =>
			array (
				'CODE' => '28.personal_slider_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block js-animation fadeInLeft">

        <div class="container g-py-20">

            <!-- Carousel -->
            <div class="js-carousel g-mb-50--lg"
                 data-infinite="true"
                 data-fade="true"
                 data-lazy-load="ondemand"
                 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-30 g-color-text g-color-primary--hover"
                 data-arrow-left-classes="fa fa-angle-left g-left-0 g-left-40--lg"
                 data-arrow-right-classes="fa fa-angle-right g-right-0 g-right-40--lg">

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img1.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">Jessica Lisbon</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Support Manager</span>
								</div>
								<div class="landing-block-node-person-text mb-4">
									<p>I\'ve got your front end and back end needs covered. I\'m always looking for strategies to make your brand\'s needs fit with today\'s development languages.</p>
								</div>

								<a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>

                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img3.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">David Case</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Web Developer</span>
								</div>
                                <div class="landing-block-node-person-text mb-4">
									<p>I am a 32-year old man from Canada and I am passionate about everything related to web development. I strive to figure out the right solutions for your look to stand out amongst the rest.</p>
								</div>

                                <a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img4.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">Maria Olsson</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Technical Director</span>
								</div>
                                <div class="landing-block-node-person-text mb-4">
									<p>I am an ambitious workaholic, but apart from that, pretty simple person. Whether it\'s branding, print, UI + UX I\'ve got you covered.</p>
								</div>

                                <a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img2.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">Tina Krueger</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Lead Designer</span>
								</div>
                                <div class="landing-block-node-person-text mb-4">
									<p>I\'m Tina Krueger, an excitable lead designer. I live with an intense passion for web development.</p>
								</div>

                                <a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img5.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">John Watson</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Marketing Manager</span>
								</div>
                                <div class="landing-block-node-person-text mb-4">
									<p>Understanding who you are and what you want is my strategy for your brand. I am always figuring out ways to capture your vision, so people can get on board.</p>
								</div>

                                <a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

                <div class="landing-block-card-person landing-block-card-top-slider-element js-slide">
                    <!-- Team -->
                    <div class="row justify-content-center align-items-center no-gutters">
                        <div class="landing-block-node-person-photo col-sm-6 col-lg-4 g-bg-size-cover g-bg-pos-top-center g-min-height-400" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/400x450/img6.jpg)"></div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="g-px-30 g-px-50--lg g-py-60">
                                <h3 class="landing-block-node-person-name h4 mb-1">Monica Gaudy</h3>
                                <div class="landing-block-node-person-post">
									<span class="d-block mb-4">Sales Manager</span>
								</div>
                                <div class="landing-block-node-person-text mb-4">
									<p>I am Monica and I aim high at being focused on building relationships with our clients and community.</p>
								</div>

                                <!-- Social Icons -->
                                <!-- End Social Icons -->

                                <a class="landing-block-node-person-link u-link-v5 g-color-text g-color-primary--hover g-font-weight-700" href="#">See Projects</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Team -->
                </div>

            </div>

			<div class="carContTest"></div>
            <!-- End Carousel -->

        </div>

    </section>',
			),
		'27.one_col_fix_title_and_text_2@3' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60 g-bg-secondary">

        <div class="container g-max-width-800">
            <div class="text-center">
                <h2 class="landing-block-node-title g-font-weight-400">Client Testimonials</h2>
                <div class="landing-block-node-text g-font-size-16 g-pb-1"><p>Unify is trusted by over 31,500 happy users all around the world.</p></div>
            </div>
        </div>

    </section>',
			),
		'29.three_cols_texts_blocks_slider' =>
			array (
				'CODE' => '29.three_cols_texts_blocks_slider',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block js-animation zoomIn">

	<div>
		<div class="container g-py-40">

			<div class="js-carousel g-pb-60"
			 data-infinite="true"
			 data-autoplay="true"
			 data-pause-hover="true"
			 data-speed="7000"
			 data-lazy-load="progressive"
			 data-slides-show="3"
			 data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-0 text-center"
			 data-responsive=\'[{
                 "breakpoint": 1200,
                 "settings": {
                   "slidesToShow": 3
                 }
               }, {
                 "breakpoint": 992,
                 "settings": {
                   "slidesToShow": 2
                 }
               }, {
                 "breakpoint": 768,
                 "settings": {
                   "slidesToShow": 2
                 }
               }, {
                 "breakpoint": 576,
                 "settings": {
                   "slidesToShow": 1
                 }
               }]\'>
				<div class="landing-block-card-slider-element js-slide g-px-15 mb-1">
					<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
						Dear Company24 team, I just bought your template some weeks ago. The template is really nice and
						offers quite a large set of options.
					</blockquote>
					<div class="media">
						<img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg" alt="">
						<div class="media-body align-self-center">
							<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Alex
								Pottorf</h4>
							<div class="landing-block-node-element-subtitle g-color-main g-font-size-13">
								<span class="">Reason: Template Quality</span>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-card-slider-element js-slide g-px-15 mb-1">
					<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
						Hi there purchased a couple of days ago and the site looks great, big thanks to the Company24
						guys, they gave me some great help with some fiddly setup issues.
					</blockquote>
					<div class="media">
						<img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img5.jpg" alt="">
						<div class="media-body align-self-center">
							<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Bastien
								Rojanawisut</h4>
							<div class="landing-block-node-element-subtitle g-color-main g-font-size-13">
								<span class="">Reason: Template Quality</span>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-card-slider-element js-slide g-px-15 mb-1">
					<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
						The website package made my life easier. I will advice programmers to buy it even it cost 140$ -
						because it shorten hunderds of hours in front of your pc designing your layout.
					</blockquote>
					<div class="media">
						<img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg" alt="">
						<div class="media-body align-self-center">
							<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">
								Massalha Shady</h4>
							<div class="landing-block-node-element-subtitle g-color-main g-font-size-13">
								<span class="">Reason: Code Quality</span>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-card-slider-element js-slide g-px-15 mb-1">
					<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
						New website template looks great!. Love the multiple layout examples for Shortcodes and the new
						Show code Copy code snippet feature is brilliant
					</blockquote>
					<div class="media">
						<img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img4.jpg" alt="">
						<div class="media-body align-self-center">
							<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Mark
								Mcmanus</h4>
							<div class="landing-block-node-element-subtitle g-color-main g-font-size-13">
								<span class="">Reason: Code Quality</span>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-card-slider-element js-slide g-px-15 mb-1">
					<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
						Great templates, I\'m currently using them for work. It\'s beautiful and the coding is done
						quickly and seamlessly. Thank you!
					</blockquote>
					<div class="media">
						<img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img3.jpg" alt="">
						<div class="media-body align-self-center">
							<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Zuza
								Muszyska</h4>
							<div class="landing-block-node-element-subtitle g-color-main g-font-size-13">
								<span class="">Reason: Company24 Quality</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</section>',
			),
		'27.one_col_fix_title_and_text_2@4' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-pb-20 g-pt-60">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400">Blog News</h2>
                <div class="landing-block-node-text"><p>Our duty towards you is to share our experience <span style="font-size: 1rem;">we`re reaching in our work path with you.</span></p></div>
            </div>
        </div>

    </section>',
			),
		'30.2.three_cols_fix_img_and_links' =>
			array (
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-20">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4">
                    <article class="u-shadow-v28 g-bg-white">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img13.jpg" alt="" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#">Free virtual conference to take your agile ux skills to the next</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4">
                    <article class="u-shadow-v28 g-bg-white">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img1.jpg" alt="" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#">Free virtual conference to take your agile ux skills to the next</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4">
					<article class="u-shadow-v28 g-bg-white">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img14.jpg" alt="" />

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
									<a class="landing-block-node-link u-link-v5 g-color-main g-color-primary--hover" href="#">Multi-Swipe: New UX to act on many items (emails) really fast</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-text g-color-primary--hover g-font-weight-500" href="#">Read More</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			),
		'01.big_with_text_2' =>
			array (
				'CODE' => '01.big_with_text_2',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block landing-block-img g-bg-size-cover g-bg-pos-center g-pt-120 g-pb-120 d-flex align-items-center" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x800/img1.jpg);">
	<div class="g-max-width-800 container text-center g-pos-rel g-z-index-1 mx-auto g-px-30">
		<h2 class="landing-block-title h1 g-font-weight-400 mb-5 g-color-black">Company24 completes every project with experience hand.</h2>
		<div class="landing-block-container">
			<a class="landing-block-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-l text-uppercase g-rounded-30 g-py-14" href="#">Get Started</a>
		</div>
	</div>
</section>',
			),
		'35.1.footer_light' =>
			array (
				'CODE' => '35.1.footer_light',
				'SORT' => '9500',
				'CONTENT' => '<section class="g-pt-60 g-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20"></h2>
				<p class="landing-block-node-text g-color-gray-dark-v2 g-mb-20" ></p>

				<address class="g-color-gray-dark-v2 g-mb-20">
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">ABOUT</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">About</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Services</a>
					</li>


				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">DEPARTMENTS</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Sales Department</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Management</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Customer Department</a>
					</li>

				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">HIRE</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Apply for Company24 Job</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">How it Works for Employees</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Working Parameters</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">General FAQs</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			),
		'17.copyright' =>
			array (
				'CODE' => '17.copyright',
				'SORT' => '10000',
				'CONTENT' => '<section class="landing-block">
	<div class="text-center g-color-gray-dark-v3 g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12 ">
				<p>&copy; 2018 All rights reserved.</p>
			</div>
		</div>
	</div>
</section>',
			),
	),
);