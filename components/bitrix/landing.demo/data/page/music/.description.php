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
			'THEME_CODE' => 'music',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/music/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
		],
	],
	'items' => [
		'0.menu_14_music' =>
			[
				'CODE' => '0.menu_14_music',
				'SORT' => '-100',
				'CONTENT' => '

<header class="landing-block landing-block-menu g-bg-gray-dark-v1 u-header u-header--sticky u-header--relative">
	<div class="u-header__section g-transition-0_3 g-py-16 g-py-10--md"
		 data-header-fix-moment-classes="u-shadow-v27">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo" target="_self">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/music-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="nav-item landing-block-node-menu-list-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[46.4.cover_with_slider_bgimg_right_buttons]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Home</a></li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Music
								albums</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[46.5.cover_with_date_countdown]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Events</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Tours</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[48.slider_with_video_on_bgimg]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Videos</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Gallery</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[43.5.cover_with_feedback_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Twitter
								feeds</a>
						</li>
						
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Blog</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-ml-15--lg">
							<a href="#block@block[33.3.form_1_transparent_black_no_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Contact us</a>
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
			],
		'46.4.cover_with_slider_bgimg_right_buttons' =>
			[
				'CODE' => '46.4.cover_with_slider_bgimg_right_buttons',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel"
		 data-arrows-classes="u-arrow-v1 u-arrow-v1-1 g-color-white g-bg-primary g-opacity-0_8--hover g-transition-0_2 g-transition--ease-in"
		 data-arrow-left-classes="fa fa-chevron-left"
		 data-arrow-right-classes="fa fa-chevron-right">
		<div class="landing-block-node-card landing-block-node-card-bgimg js-slide d-flex align-items-center u-bg-overlay g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after"
			 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x891/img1.jpg);">
			<div class="u-bg-overlay__inner">
				<div class="landing-block-node-card-text-container js-animation fadeInLeft u-heading-v4-1 g-max-width-645 g-brd-7 g-brd-primary g-pl-30">
					<h3 class="landing-block-node-card-subtitle landing-semantic-subtitle-image-medium text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-15 g-mb-25--md">
						Company24 new album</h3>
					<h2 class="landing-block-node-card-title landing-semantic-title-image-medium u-heading-v4__title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-65 g-color-white g-mb-15">
						Reincarnation</h2>
					<div class="landing-block-node-card-text landing-semantic-text-image-medium mb-0 g-color-white" data-auto-font-scale>
						<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
							finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
							iaculis laoreet.
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card landing-block-node-card-bgimg js-slide d-flex align-items-center u-bg-overlay g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after"
			 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x891/img2.jpg);">
			<div class="u-bg-overlay__inner">
				<div class="landing-block-node-card-text-container js-animation fadeInLeft u-heading-v4-1 g-max-width-645 g-brd-7 g-brd-primary g-pl-30">
					<h3 class="landing-block-node-card-subtitle landing-semantic-subtitle-image-medium text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-15 g-mb-25--md">
						Company24 new album</h3>
					<h2 class="landing-block-node-card-title landing-semantic-title-image-medium u-heading-v4__title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-65 g-color-white g-mb-15">
						Umbrella</h2>
					<div class="landing-block-node-card-text landing-semantic-text-image-medium mb-0 g-color-white" data-auto-font-scale>
						<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
							finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
							iaculis laoreet.
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pb-20 g-pt-60 g-bg-gray-light-v5 js-animation fadeInUp animated">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">BEST MUSIC ALBUMS</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			],
		'30.2.three_cols_fix_img_and_links' =>
			[
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-30 g-pb-20">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x233/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">RADIO MUSIC SOCIETY</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">LEARN MORE</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x233/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">GIRLS</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">LEARN MORE</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x233/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
									<a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">BREAK THROUGH THE SILENCE</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">LEARN MORE</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'46.5.cover_with_date_countdown' =>
			[
				'CODE' => '46.5.cover_with_date_countdown',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-bg-black-opacity-0_6--after g-pt-100 g-pb-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x588/img1.jpg);">
	<div class="container u-bg-overlay__inner">
		<div class="container text-center g-width-780 g-mb-65">
			<div class="landing-block-node-header u-heading-v2-2--bottom g-brd-primary">
				<h2 class="landing-block-node-title text-uppercase u-heading-v2__title g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-0 js-animation slideInDown">
					Upcoming events</h2>
			</div>
		</div>

		<div class="row text-center text-md-left">
			<div class="col-md-5 offset-md-1 landing-block-node-subtitle-container js-animation slideInLeft">
				<h3 class="landing-block-node-subtitle text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-5">
					New album presetnation</h3>
				<div class="landing-block-node-text g-font-size-13 g-color-white g-mb-60 g-mb-0--md">
					<p>
						8 Rue de Montpensier 75001, Paris, France, 18:00
					</p>
				</div>
			</div>

			<div class="col-md-6 landing-block-node-date-container js-animation slideInRight">
				<!-- Countdown -->
				<div class="u-countdown-v1 g-color-white">
					<div class="d-inline-block text-center g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-pa-15-20-10 g-mx-15 g-mb-30">
						<div class="landing-block-node-date-value g-line-height-1 g-font-size-40 g-font-weight-700 g-mb-2">12</div>
						<div class="landing-block-node-date-text text-uppercase text-center g-color-white-opacity-0_7 g-font-size-12">Day</div>
					</div>

					<div class="d-inline-block g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-pa-15-20-10 g-mx-15 g-mb-30">
						<div class="landing-block-node-date-value g-line-height-1 g-font-size-40 g-font-weight-700 g-mb-2">02</div>
						<div class="landing-block-node-date-text text-uppercase text-center g-color-white-opacity-0_7 g-font-size-12">Month</div>
					</div>

					<div class="d-inline-block g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-pa-15-20-10 g-mx-15 g-mb-30">
						<div class="landing-block-node-date-value g-line-height-1 g-font-size-40 g-font-weight-700 g-mb-2">2022</div>
						<div class="landing-block-node-date-text text-uppercase text-center g-color-white-opacity-0_7 g-font-size-12">Year</div>
					</div>
					
				</div>
				<!-- End Countdown -->
			</div>
		</div>
		
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pb-20 g-pt-60 g-bg-gray-light-v5 js-animation fadeInUp animated">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">TOUR DATES</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			],
		'36.2.concertes_dates_with_button' =>
			[
				'CODE' => '36.2.concertes_dates_with_button',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-100 g-pb-100">
	<div class="container g-font-size-13">
		<!-- Article -->
		<article class="landing-block-node-card js-animation fadeInUp d-flex align-items-center text-md-left text-center w-100 g-bg-white g-mb-1 flex-column flex-md-row">
			<!-- Date -->
			<div class="text-center g-valign-middle g-width-125--md g-py-10 g-px-20 flex-shrink-0">
				<div class="landing-block-node-card-date-value g-font-weight-700 g-font-size-40 g-line-height-1">27</div>
				<div class="landing-block-node-card-date-text">Jun, 2015</div>
			</div>
			<!-- End Date -->

			<!-- Article Image -->
			
				<img class="landing-block-node-card-img g-valign-middle g-width-130 info-v5-2__image g-ml-minus-1 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img13.jpg"
					 alt="">
			<!-- End Article Image -->

			<!-- Article Content -->
			<div class="g-valign-middle g-py-15 g-px-20 g-max-width-570">
				<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700">
					Nam Enim Eros Rhoncus
				</h6>
				<div class="landing-block-node-card-text">8 Rue de Montpensier 75001, Paris, France, 18:00</div>
			</div>
			<!-- End Article Content -->

			<!-- Price -->
			<div class="g-valign-middle g-py-5 g-px-20 ml-auto ml-md-auto">
				<div class="landing-block-node-card-price g-font-weight-700">$15</div>

				<div class="landing-block-node-card-price-text text-uppercase g-font-size-11">Per Ticket</div>
			</div>
			<!-- End Price -->

			<!-- Actions -->
			<div class="text-md-right g-valign-middle g-pa-20 flex-shrink-0">
				<div class="g-mt-minus-10 g-mx-minus-5">
					<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase g-mt-10"
					   href="#">Buy Ticket</a>
				</div>
			</div>
			<!-- End Actions -->
		</article>
		<!-- End Article -->

		<!-- Article -->
		<article class="landing-block-node-card js-animation fadeInUp d-flex align-items-center text-md-left text-center w-100 g-bg-white g-mb-1 flex-column flex-md-row">
			<!-- Date -->
			<div class="text-center g-valign-middle g-width-125--md g-py-10 g-px-20 flex-shrink-0">
				<div class="landing-block-node-card-date-value g-font-weight-700 g-font-size-40 g-line-height-1">01</div>
				<div class="landing-block-node-card-date-text">Aug, 2015</div>
			</div>
			<!-- End Date -->

			<!-- Article Image -->
			
				<img class="landing-block-node-card-img g-valign-middle g-width-130 info-v5-2__image g-ml-minus-1 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img14.jpg"
					 alt="">
			<!-- End Article Image -->

			<!-- Article Content -->
			<div class="g-valign-middle g-py-15 g-px-20 g-max-width-570">
				<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700">
					Nulla lobortis arcu ex
				</h6>
				<div class="landing-block-node-card-text">8 Rue de Montpensier 75001, Paris, France, 18:00</div>
			</div>
			<!-- End Article Content -->

			<!-- Price -->
			<div class="g-valign-middle g-py-5 g-px-20 ml-auto ml-md-auto">
				<div class="landing-block-node-card-price g-font-weight-700">$15</div>

				<div class="landing-block-node-card-price-text text-uppercase g-font-size-11">Per Ticket</div>
			</div>
			<!-- End Price -->

			<!-- Actions -->
			<div class="d-md-table-cell text-md-right g-valign-middle g-pa-20 flex-shrink-0">
				<div class="g-mt-minus-10 g-mx-minus-5">
					<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase g-mt-10"
					   href="#">Buy Ticket</a>
				</div>
			</div>
			<!-- End Actions -->
		</article>
		<!-- End Article -->

		<!-- Article -->
		<article class="landing-block-node-card js-animation fadeInUp d-flex align-items-center text-md-left text-center w-100 g-bg-white g-mb-1 flex-column flex-md-row">
			<!-- Date -->
			<div class="text-center g-valign-middle g-width-125--md g-py-10 g-px-20 flex-shrink-0">
				<div class="landing-block-node-card-date-value g-font-weight-700 g-font-size-40 g-line-height-1">05</div>
				<div class="landing-block-node-card-date-text">Oct, 2015</div>
			</div>
			<!-- End Date -->

			<!-- Article Image -->
			
				<img class="landing-block-node-card-img g-valign-middle g-width-130 info-v5-2__image g-ml-minus-1 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img15.jpg"
					 alt="">
			<!-- End Article Image -->

			<!-- Article Content -->
			<div class="g-valign-middle g-py-15 g-px-20 g-max-width-570">
				<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700">
					Etiam varius sit amet est a varius
				</h6>
				<div class="landing-block-node-card-text">8 Rue de Montpensier 75001, Paris, France, 18:00</div>
			</div>
			<!-- End Article Content -->

			<!-- Price -->
			<div class="g-valign-middle g-py-5 g-px-20 ml-auto ml-md-auto">
				<div class="landing-block-node-card-price g-font-weight-700">$20</div>

				<div class="landing-block-node-card-price-text text-uppercase g-font-size-11">Per Ticket</div>
			</div>
			<!-- End Price -->

			<!-- Actions -->
			<div class="d-md-table-cell text-md-right g-valign-middle g-pa-20 flex-shrink-0">
				<div class="g-mt-minus-10 g-mx-minus-5">
					<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase g-mt-10"
					   href="#">Buy Ticket</a>
				</div>
			</div>
			<!-- End Actions -->
		</article>
		<!-- End Article -->

		<!-- Article -->
		<article class="landing-block-node-card js-animation fadeInUp d-flex align-items-center text-md-left text-center w-100 g-bg-white g-mb-1 flex-column flex-md-row">
			<!-- Date -->
			<div class="text-center g-valign-middle g-width-125--md g-py-10 g-px-20 flex-shrink-0">
				<div class="landing-block-node-card-date-value g-font-weight-700 g-font-size-40 g-line-height-1">22</div>
				<div class="landing-block-node-card-date-text">Nov, 2015</div>
			</div>
			<!-- End Date -->

			<!-- Article Image -->
			
				<img class="landing-block-node-card-img g-valign-middle g-width-130 info-v5-2__image g-ml-minus-1 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img16.jpg"
					 alt="">
			<!-- End Article Image -->

			<!-- Article Content -->
			<div class="g-valign-middle g-py-15 g-px-20 g-max-width-570">
				<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700">
					Aliquam dignissim non nisi in tristique
				</h6>
				<div class="landing-block-node-card-text">8 Rue de Montpensier 75001, Paris, France, 18:00</div>
			</div>
			<!-- End Article Content -->

			<!-- Price -->
			<div class="g-valign-middle g-py-5 g-px-20 ml-auto ml-md-auto">
				<div class="landing-block-node-card-price g-font-weight-700">$15</div>

				<div class="landing-block-node-card-price-text text-uppercase g-font-size-11">Per Ticket</div>
			</div>
			<!-- End Price -->

			<!-- Actions -->
			<div class="d-md-table-cell text-md-right g-valign-middle g-pa-20 flex-shrink-0">
				<div class="g-mt-minus-10 g-mx-minus-5">
					<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase g-mt-10"
					   href="#">Buy Ticket</a>
				</div>
			</div>
			<!-- End Actions -->
		</article>
		<!-- End Article -->
	</div>
</section>
',
			],
		'48.slider_with_video_on_bgimg' =>
			[
				'CODE' => '48.slider_with_video_on_bgimg',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-bg-black-opacity-0_6--after g-pt-100 js-animation slideInRight" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x645/img1.jpg);">
	<div class="js-carousel container u-bg-overlay__inner g-pb-140" data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-40 text-center">
		

		<div class="landing-block-node-card js-slide g-py-20">
			<div class="container text-center g-max-width-570">
				<div class="landing-block-node-card-button-container mb-3">
					<a class="landing-block-node-card-button m-auto u-icon-v2 g-text-underline--none--hover u-block-hover--scale g-overflow-inherit g-bg-primary--hover rounded-circle g-cursor-pointer g-brd-around g-brd-5 g-brd-primary" href="//www.youtube.com/watch?v=q4d8g9Dn3ww" target="_popup" data-url="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&amp;controls=1&amp;loop=0&amp;rel=0&amp;start=0&amp;html5=1&amp;v=q4d8g9Dn3ww">
						<img class="landing-block-node-card-icon d-block g-ml-4 g-height-14" src="https://cdn.bitrix24.site/bitrix/images/landing/play.png" />
					</a>
				</div>
				
				<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-20">
					You\'re on my mind official video
				</h2>

				<div class="landing-block-node-card-text g-mb-30">
					<p>
						Etiam varius sit amet est a varius. Nullam pharetra non diam non mollis. Pellentesque
						congue quam ipsum, non tempor placerat ante volutpat est
					</p>
				</div>
				<a class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-color-primary" href="#">View on iTunes</a>
			</div>
		</div>
	<div class="landing-block-node-card js-slide g-py-20">
			<div class="container text-center g-max-width-570">
				<div class="landing-block-node-card-button-container mb-3">
					<a class="landing-block-node-card-button m-auto u-icon-v2 g-text-underline--none--hover u-block-hover--scale g-overflow-inherit g-bg-primary--hover rounded-circle g-cursor-pointer g-brd-around g-brd-5 g-brd-primary" href="//www.youtube.com/watch?v=q4d8g9Dn3ww" target="_popup" data-url="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&amp;controls=1&amp;loop=0&amp;rel=0&amp;start=0&amp;html5=1&amp;v=q4d8g9Dn3ww">
						<img class="landing-block-node-card-icon d-block g-ml-4 g-height-14" src="https://cdn.bitrix24.site/bitrix/images/landing/play.png" />
					</a>
				</div>
				
				<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-20">
					You\'re on my mind official video
				</h2>

				<div class="landing-block-node-card-text g-mb-30">
					<p>
						Etiam varius sit amet est a varius. Nullam pharetra non diam non mollis. Pellentesque
						congue quam ipsum, non tempor placerat ante volutpat est
					</p>
				</div>
				<a class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-color-primary" href="#">View on iTunes</a>
			</div>
		</div></div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pb-20 g-pt-60 g-bg-main js-animation fadeInUp animated">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR MEDIA GALLERY</h2>
            </div>

			<div class="landing-block-node-text"><p>Etiam varius sit amet est a varius. Nullam pharetra non diam non mollis. Pellentesque congue quam ipsum, non tempor arcu egestas non. Nullam placerat ante volutpat est scelerisque consequat.<br /></p></div>
        </div>

    </section>',
			],
		'45.3.gallery_6cols_2row' =>
			[
				'CODE' => '45.3.gallery_6cols_2row',
				'SORT' => '4500',
				'CONTENT' => '<section class="g-py-100 g-bg-main">

	<div class="container">
		<div>
			<div class="js-carousel carouselMusic1 js-gallery-cards g-line-height-0 g-pb-40" data-slides-scroll="6" data-rows="2" data-slides-show="6" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-0 text-center">
				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img1.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img2.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img3.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img4.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img5.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img6.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img7.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img8.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img9.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img10.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img11.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img12.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img13.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>

				<div class="landing-block-node-card js-slide g-pb-15 g-pa-15--sm js-animation fadeIn">
					<div class="g-parent g-pos-rel g-brd-around g-brd-gray-light-v5 g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid g-grayscale-100x g-grayscale-0--parent-hover g-transform-scale-0_85--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600_music/img14.jpg" alt="" data-fancybox="gallery" />
					</div>
				</div>
			</div>
		</div>
	</div>

</section>',
			],
		'43.5.cover_with_feedback_2' =>
			[
				'CODE' => '43.5.cover_with_feedback_2',
				'SORT' => '5000',
				'CONTENT' => '<section
		class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-bg-black-opacity-0_6--after g-pt-100"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x645/img2.jpg);">
	<div class="js-carousel container u-bg-overlay__inner g-pb-120"
		 data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-60 text-center">
		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-container js-animation fadeIn container text-center g-max-width-670">
				<div class="row justify-content-center g-mb-25">
					<img class="landing-block-node-card-photo g-brd-around g-height-50 g-width-50 g-brd-3 g-brd-white-opacity-0_3 g-rounded-50x g-mr-20"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg">

					<a href="#" class="landing-block-node-card-name g-color-white g-color-primary--hover align-self-center text-uppercase g-font-size-13 g-font-weight-700">
						@company24
					</a>
				</div>

				<blockquote class="landing-block-node-card-text g-line-height-1_6 g-font-weight-600 g-font-size-27 g-color-white g-mb-30">
					In eu augue massa. Phasellus
					<a class="g-color-primary" href="#!">#rutrum</a>
					velit diam, quis pellentesque libero hendrerit a.
				</blockquote>
				<div class="landing-block-node-card-date g-color-white-opacity-0_7">12:35 Pm, June 12, 2017</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-container js-animation fadeIn container text-center g-max-width-670">
				<div class="row justify-content-center g-mb-25">
					<img class="landing-block-node-card-photo g-brd-around g-height-50 g-width-50 g-brd-3 g-brd-white-opacity-0_3 g-rounded-50x g-mr-20"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg">

					<a href="#" class="landing-block-node-card-name g-color-white g-color-primary--hover align-self-center text-uppercase g-font-size-13 g-font-weight-700">
						@company24
					</a>
				</div>

				<blockquote class="landing-block-node-card-text g-line-height-1_6 g-font-weight-600 g-font-size-27 g-color-white g-mb-30">
					In eu augue massa. Phasellus
					<a class="g-color-primary" href="#!">#rutrum</a>
					velit diam, quis pellentesque libero hendrerit a.
				</blockquote>
				<div class="landing-block-node-card-date g-color-white-opacity-0_7">12:35 Pm, June 12, 2017</div>
			</div>
		</div>
	</div>
</section>
',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-60 js-animation fadeInUp animated g-pb-20">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">LATEST POSTS</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			],
		'37.3.two_cols_blocks_carousel' =>
			[
				'CODE' => '37.3.two_cols_blocks_carousel',
				'SORT' => '6000',
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
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/870x428/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-15 js-animation fadeInLeft">Nunc vehicula diam non tempor lacinia</h6>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight"><p>Morbi et convallis metus, in congue mi. Nam placerat augue nec justo luctus, id lobortis augue tempor. In feugiat ipsum a quam lacinia eleifend sem dapibus a. </p></div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right"> </div>
							<div class="landing-block-node-card-label-left g-color-primary">April 27, 2022</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle text-uppercase g-btn-primary g-color-white rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#" target="_self">read more</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/870x428/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-15 js-animation fadeInLeft">MAURIS TELLUS MAGNA, PRETIUM</h6>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight"><p>Morbi et convallis metus, in congue mi. Nam placerat augue nec justo luctus, id lobortis augue tempor. In feugiat ipsum a quam lacinia eleifend sem dapibus a.<br /></p></div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right"> </div>
							<div class="landing-block-node-card-label-left g-color-primary">APRIL 27, 2022</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle text-uppercase g-btn-primary g-color-white rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#" target="_self">read more</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="landing-block-node-card-bgimg clearfix g-bg-size-cover g-pos-rel g-width-100x--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/870x428/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<!-- Article Content -->
					<div class="landing-block-node-card-text-bg float-right g-color-gray-light-v1 g-bg-black-opacity-0_7 g-width-50x--sm g-pa-30 g-height-100x d-flex flex-column">
						<h6 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-15 js-animation fadeInLeft">CRAS VOLUTPAT SED LEO UT TEMPOR</h6>
						<div class="landing-block-node-card-text g-mb-45 js-animation fadeInRight"><p>Morbi et convallis metus, in congue mi. Nam placerat augue nec justo luctus, id lobortis augue tempor. In feugiat ipsum a quam lacinia eleifend sem dapibus a. </p></div>

						<div class="clearfix text-uppercase g-color-white g-font-size-11 g-mb-35">
							<div class="float-right landing-block-node-card-label-right"> </div>
							<div class="landing-block-node-card-label-left g-color-primary">APRIL 27, 2022</div>
						</div>

						<a class="btn landing-block-node-card-button landing-semantic-link-medium-white js-animation fadeInLeft g-valign-middle text-uppercase g-btn-primary g-color-white rounded-0 g-py-10 g-py-20--md mt-auto g-btn-type-solid g-btn-size-sm g-btn-px-m rounded-0" href="#" target="_self">read more</a>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article -->
			</div>

			
		</div>
		<!-- End Carousel -->
	</div>
</section>',
			],
		'33.3.form_1_transparent_black_no_text' =>
			[
				'CODE' => '33.3.form_1_transparent_black_no_text',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-bg-primary-dark-v1 g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1155/img1.jpg);">

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
				'SORT' => '7000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60 g-bg-black">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">
					Contact us</h2>
				<div class="landing-block-node-text g-color-gray-light-v1 g-mb-20"><p>Lorem ipsum
						dolor sit amet, consectetur
						adipiscing</p></div>

				<address class="g-mb-20">
					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-light-v1 text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text g-color-gray-light-v1">
							Address: <span style="font-weight: bold;">In sed lectus tincidunt</span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-light-v1 text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-phone"></i>
						</div>
						<div class="landing-block-node-card-contact-text g-color-gray-light-v1">
							Phone Number: <span style="font-weight: bold;"><a
										href="tel:485552566112">+48 555 2566 112</a></span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-light-v1 text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-envelope"></i>
						</div>
						<div>
							<div class="landing-block-node-card-contact-text g-color-gray-light-v1">
								Email: <span style="font-weight: bold;"><a
											href="mailto:info@company24.com">info@company24.com</a></span>
							</div>
						</div>
					</div>
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">Links</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">About us</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">News</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">My blog</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Events</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">music</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">My albums</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Pop</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Rock n\'Roll</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Blues and Jazz</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">useful Links</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Rap and Hip-Hop</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Classic</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Morbi massa justo</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#" target="_self">Praesent nec consecteth</a>
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
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-black js-animation animation-none">
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