<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_COURSES_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_COURSES_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'courses',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/courses/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_COURSES_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_COURSES_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_COURSES_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_COURSES_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_10_courses' =>
			[
				'CODE' => '0.menu_10_courses',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--float g-z-index-9999" >
	<div class="u-header__section u-header__section--light u-shadow-v27 g-transition-0_3 g-py-17" data-header-fix-moment-exclude="g-py-17" data-header-fix-moment-classes="g-py-12">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/courses-logo-dark.png" alt="Logo">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg g-mb-20 g-mb-0--lg ml-auto g-mr-20">
						<li class="landing-block-node-menu-list-item nav-item g-mr-3--lg g-mb-5 g-mb-0--lg ">
							<a href="#block@block[40.1.three_cols_carousel]" class="landing-block-node-menu-list-item-link nav-link" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[31.1.two_cols_text_img]" class="landing-block-node-menu-list-item-link nav-link" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link" target="_self">OUR COURSES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link" target="_self">OUR NUMBERS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@2]" class="landing-block-node-menu-list-item-link nav-link" target="_self">GALLERY</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link" target="_self">TEACHERS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@3]" class="landing-block-node-menu-list-item-link nav-link" target="_self">OFFERS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link" target="_self">PARTNERS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#block@block[40.2.two_cols_carousel]" class="landing-block-node-menu-list-item-link nav-link" target="_self">BLOG</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-3--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@4]" class="landing-block-node-menu-list-item-link nav-link" target="_self">CONTACTS</a>
						</li>
					</ul>
					<ul class="list-inline mb-0 landing-block-node-menu-list-social">
						<li class="list-inline-item landing-block-card-social g-mr-10"
							data-card-preset="facebook">
							<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
							   href="https://facebook.com">
								<i class="landing-block-card-social-icon fa fa-facebook"></i>
							</a>
						</li>
						<li class="landing-block-card-social list-inline-item g-mr-10"
							data-card-preset="instagram">
							<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
							   href="https://instagram.com">
								<i class="landing-block-card-social-icon fa fa-instagram"></i>
							</a>
						</li>
						<li class="landing-block-card-social list-inline-item g-mr-10"
							data-card-preset="twitter">
							<a class="landing-block-card-social-icon-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
							   href="https://twitter.com">
								<i class="landing-block-card-social-icon fa fa-twitter"></i>
							</a>
						</li>
					</ul>

				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto g-flex-centered-item--center" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'40.1.three_cols_carousel' =>
			[
				'CODE' => '40.1.three_cols_carousel',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-30">
	<div class="container-fluid g-px-30">
		<div class="js-carousel g-pos-rel g-mx-minus-15 row"
			 data-infinite="true"
			 data-autoplay="false"
			 data-slides-show="3"
			 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-40 g-color-white g-bg-primary"
			 data-arrow-left-classes="fa fa-angle-left g-left-minus-15"
			 data-arrow-right-classes="fa fa-angle-right g-right-minus-15"
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
                 "breakpoint": 576,
                 "settings": {
                   "slidesToShow": 1
                 }
               }]\'
			 data-init-classes-exclude=\'[{
                 "selector": ".landing-block-node-card",
                 "class": "col-12 col-sm-6 col-lg-4"
               }, {
                 "selector": ".js-carousel",
                 "class": "row"
               }]\'>
			<div class="landing-block-node-card js-slide g-px-15 col-12 col-sm-6 col-lg-4">
				<article class="g-pos-rel">
					<figure class="landing-block-node-card-img-container u-bg-overlay g-bg-black-opacity-0_5--after g-pointer-events-before-after-none">
						<img class="landing-block-node-card-img img-fluid w-100"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/695x1135/img1.jpg" alt="">
					</figure>

					<header class="g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-30">
						<div>
							<span class="landing-block-node-card-icon-container g-color-primary g-font-size-22 g-mr-10">
								<i class="landing-block-node-card-icon fa fa-calendar"></i>
							</span>
							<div class="landing-block-node-card-subtitle d-inline-block text-uppercase g-font-weight-700 g-font-size-12 g-color-white g-mb-10">
								<p>Duration, 3 Months.</p>
							</div>
						</div>
						<h2 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-10">
							Dance courses
						</h2>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-30">
							<p>Curabitur eget tortor sed urna
								faucibus iaculis id et nulla.</p>
						</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-bg-transparent--hover rounded-0 g-py-15"
							   href="#">Learn More</a>
						</div>
					</header>
				</article>
			</div>

			<div class="landing-block-node-card js-slide g-px-15 col-12 col-sm-6 col-lg-4">
				<article class="g-pos-rel">
					<figure class="landing-block-node-card-img-container u-bg-overlay g-bg-black-opacity-0_5--after g-pointer-events-before-after-none">
						<img class="landing-block-node-card-img img-fluid w-100"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/695x1135/img2.jpg" alt="">
					</figure>

					<header class="g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-30">
						<div>
							<span class="landing-block-node-card-icon-container g-color-primary g-font-size-22 g-mr-10">
								<i class="landing-block-node-card-icon fa fa-calendar"></i>
							</span>
							<div class="landing-block-node-card-subtitle d-inline-block text-uppercase g-font-weight-700 g-font-size-12 g-color-white g-mb-10">
								<p>Duration, 4 Months.</p>
							</div>
						</div>
						<h2 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-10">
							Creative photos
						</h2>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-30">
							<p>Curabitur eget tortor sed urna
								faucibus iaculis id et nulla.</p>
						</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-bg-transparent--hover rounded-0 g-py-15"
							   href="#">Learn More</a>
						</div>
					</header>
				</article>
			</div>

			<div class="landing-block-node-card js-slide g-px-15 col-12 col-sm-6 col-lg-4">
				<article class="g-pos-rel">
					<figure class="landing-block-node-card-img-container u-bg-overlay g-bg-black-opacity-0_5--after g-pointer-events-before-after-none">
						<img class="landing-block-node-card-img img-fluid w-100"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/695x1135/img3.jpg" alt="">
					</figure>

					<header class="g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-30">
						<div>
							<span class="landing-block-node-card-icon-container g-color-primary g-font-size-22 g-mr-10">
								<i class="landing-block-node-card-icon fa fa-calendar"></i>
							</span>
							<div class="landing-block-node-card-subtitle d-inline-block text-uppercase g-font-weight-700 g-font-size-12 g-color-white g-mb-10">
								<p>Duration, 10 Months.</p>
							</div>
						</div>
						<h2 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-10">
							Art for all
						</h2>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-30">
							<p>Curabitur eget tortor sed urna
								faucibus iaculis id et nulla.</p>
						</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-bg-transparent--hover rounded-0 g-py-15"
							   href="#">Learn More</a>
						</div>
					</header>
				</article>
			</div>

			<div class="landing-block-node-card js-slide g-px-15 col-12 col-sm-6 col-lg-4">
				<article class="g-pos-rel">
					<figure class="landing-block-node-card-img-container u-bg-overlay g-bg-black-opacity-0_5--after g-pointer-events-before-after-none">
						<img class="landing-block-node-card-img img-fluid w-100"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/695x1135/img4.jpg" alt="">
					</figure>

					<header class="g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-30">
						<div>
							<span class="landing-block-node-card-icon-container g-color-primary g-font-size-22 g-mr-10">
								<i class="landing-block-node-card-icon fa fa-calendar"></i>
							</span>
							<div class="landing-block-node-card-subtitle d-inline-block text-uppercase g-font-weight-700 g-font-size-12 g-color-white g-mb-10">
								<p>Duration, 8 Months.</p>
							</div>
						</div>
						<h2 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-10">
							Painting
						</h2>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-30">
							<p>Curabitur eget tortor sed urna
								faucibus iaculis id et nulla.</p>
						</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-bg-transparent--hover rounded-0 g-py-15"
							   href="#">Learn More</a>
						</div>
					</header>
				</article>
			</div>

			<div class="landing-block-node-card js-slide g-px-15 col-12 col-sm-6 col-lg-4">
				<article class="g-pos-rel">
					<figure class="landing-block-node-card-img-container u-bg-overlay g-bg-black-opacity-0_5--after g-pointer-events-before-after-none">
						<img class="landing-block-node-card-img img-fluid w-100"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/695x1135/img5.jpg" alt="">
					</figure>

					<header class="g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-30">
						<div>
							<span class="landing-block-node-card-icon-container g-color-primary g-font-size-22 g-mr-10">
								<i class="landing-block-node-card-icon fa fa-calendar"></i>
							</span>
							<div class="landing-block-node-card-subtitle d-inline-block text-uppercase g-font-weight-700 g-font-size-12 g-color-white g-mb-10">
								<p>Duration, 7 Months.</p>
							</div>
						</div>
						<h2 class="landing-block-node-card-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-color-white g-mb-10">
							Courses for elderly
						</h2>
						<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-30">
							<p>Curabitur eget tortor sed urna
								faucibus iaculis id et nulla.</p>
						</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-bg-transparent--hover rounded-0 g-py-15"
							   href="#">Learn More</a>
						</div>
					</header>
				</article>
			</div>
		</div>
	</div>
</section>
',
			],
		'31.1.two_cols_text_img' =>
			[
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-bg-main">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-25 g-font-size-27 js-animation fadeInUp animated">QUALITY RESULTS WITH US</h3>
				<div class="landing-block-node-text g-mb-30 js-animation fadeInUp animated"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary js-animation fadeInUp animated rounded-0" href="#" tabindex="0" target="_self">VIEW OUR PROMO VIDEO</a>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1158x764/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-bg-pattern-gray-v1 js-animation fadeInUp animated g-pb-20 g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20">OUR COURSES</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-27">LEARN SOMETHING NEW</h2>
            </div>
        </div>
    </section>',
			],
		'20.3.four_cols_fix_img_title_text' =>
			[
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-bg-pattern-gray-v1 g-pb-30">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation animated  fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/642x818/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-18">BALLET LESSONS</h3>
				<div class="landing-block-node-text g-font-size-14"><p><span style="font-weight: bold;color: rgb(97, 97, 97);">FROM $150 PER COURSE</span><br /><br />This is where we really begin to visualize your napkin sketches and make them into beautiful pixels.<br /><br /><span style="font-weight: bold;color: rgb(97, 97, 97);">DURATION: 6 MONTHS<br />DEGREE LEVEL: ADVANCED</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation animated  fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/642x818/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-18">MUSIC LESSONS</h3>
				<div class="landing-block-node-text g-font-size-14"><p><span style="font-weight: bold;color: rgb(97, 97, 97);">FROM $150 PER COURSE</span><br /><br /><span style="">This is where we really begin to visualize your napkin sketches and make them into beautiful pixels.<br /><br /></span><span style="font-weight: bold;color: rgb(97, 97, 97);"><span style="">DURATION: 6 MONTHS<br /></span><span style="">DEGREE LEVEL: ADVANCED</span></span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation animated  fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/642x818/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-18">GUITAR FOR BEGGINERS</h3>
				<div class="landing-block-node-text g-font-size-14"><p><span style="font-weight: bold;color: rgb(97, 97, 97);">FROM $150 PER COURSE</span><br /><br /><span style="">This is where we really begin to visualize your napkin sketches and make them into beautiful pixels.<br /><br /></span><span style="font-weight: bold;color: rgb(97, 97, 97);"><span style="">DURATION: 6 MONTHS<br /></span><span style="">DEGREE LEVEL: ADVANCED</span></span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation animated  fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/642x818/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-18">CREATIVE PHOTOGRAPHS</h3>
				<div class="landing-block-node-text g-font-size-14"><p><span style="font-weight: bold;color: rgb(97, 97, 97);">FROM $150 PER COURSE</span><br /><br /><span style="">This is where we really begin to visualize your napkin sketches and make them into beautiful pixels.<br /><br /></span><span style="font-weight: bold;color: rgb(97, 97, 97);"><span style="">DURATION: 6 MONTHS<br /></span><span style="">DEGREE LEVEL: ADVANCED</span></span></p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-main g-pb-20 g-pt-60">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15">OUR NUMBERS</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-27">WE WORK HARD</h2>
            </div>

			<div class="landing-block-node-text"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id.</p></div>
        </div>

    </section>',
			],
		'08.2.two_cols_fix_title_and_text' =>
			[
				'CODE' => '08.2.two_cols_fix_title_and_text',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-18 g-text-break-word">70 COURSES IN OUR SCHOOL</h2>
				</div>

				<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum.</p></div>
			</div>

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-18 g-text-break-word">32 PROFESSIONAL TEACHERS</h2>
				</div>

				<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum.</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'08.2.two_cols_fix_title_and_text@2' =>
			[
				'CODE' => '08.2.two_cols_fix_title_and_text',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-18 g-text-break-word">2780 HAPPY STUDENTS</h2>
				</div>

				<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum.</p></div>
			</div>

			<div class="landing-block-card col-lg-6 g-mb-40 g-mb-0--lg js-animation fadeIn">
				<div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
					<h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
					<h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-18 g-text-break-word">192 DIFFERENT COURSES</h2>
				</div>

				<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum.</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title@2' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20">GALLERY</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-27">VIEW HOW IT LOOKS</h2>
            </div>
        </div>
    </section>',
			],
		'32.5.img_grid_3cols_1' =>
			[
				'CODE' => '32.5.img_grid_3cols_1',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-20">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img1.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">COURSES FOR KIDS</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container landing-block-node-img-container-center js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img2.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transiti	on--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">COOKING CLASS</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img3.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">PERSONALIZED LESSONS</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			],
		'32.5.img_grid_3cols_1@2' =>
			[
				'CODE' => '32.5.img_grid_3cols_1',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-pb-60 g-pt-20">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img4.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">DISCUSSIONS IN CLASS</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container landing-block-node-img-container-center js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img5.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transiti	on--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">GYMNASTICS FOR ELDERLY</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1413x837/img6.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-font-size-18 g-letter-spacing-1">GYMNASTICS FOR ELDERLY</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-pattern-green-v1 g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-white">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15 g-color-white-opacity-0_8">OUR TEACHERS</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-27 g-color-white">MEET THE PROFESSIONALS</h2>
            </div>

			<div class="landing-block-node-text g-color-white-opacity-0_8"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id.</p></div>
        </div>

    </section>',
			],
		'28.3.team' =>
			[
				'CODE' => '28.3.team',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-bg-pattern-green-v1">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/558x758/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">rebecca@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-mb-5 g-color-white g-font-size-15"><span style="font-weight: normal;">COOK</span></div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-mb-7 g-font-size-22 g-color-white">Rebecca<br />Smithmann<br /></h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0 g-color-white">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor auctor sapien, eu tempus nunc.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/558x758/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">monica@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-mb-5 g-color-white g-font-size-15"><span style="font-weight: normal;">ARTIST, SCULPTOR</span></div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-mb-7 g-font-size-22 g-color-white">Monica
<br />BLACKWATER</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0 g-color-white">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor auctor sapien, eu tempus nunc.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/558x758/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">lara@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-mb-5 g-color-white g-font-size-15"><span style="font-weight: normal;">ARTIST, SINGER</span></div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-mb-7 g-font-size-22 g-color-white">LARA<br />WIscinson</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0 g-color-white">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor auctor sapien, eu tempus nunc.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/558x758/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">simon@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-mb-5 g-color-white g-font-size-15"><span style="font-weight: normal;">Teacher, piano</span></div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-mb-7 g-font-size-22 g-color-white">SIMON<br />RUBApA</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 mb-0 g-color-white">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor auctor sapien, eu tempus nunc.</div>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title@3' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-bg-pattern-gray-v1 js-animation fadeInUp animated g-pt-60 g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20">OUR OFFERS</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-27">THE BEST OFFERS WITH US</h2>
            </div>
        </div>
    </section>',
			],
		'11.three_cols_fix_tariffs' =>
			[
				'CODE' => '11.three_cols_fix_tariffs',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-bg-pattern-gray-v1 g-pb-60 g-pt-20">
        <div class="container">

            <div class="row no-gutters landing-block-inner">

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp animated ">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">FOR PHOTOGRAPHERS</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$750</span></div>
                                <div class="landing-block-node-price-text">per course</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget. Praesent malesuada facilisis maximus. Integer blandit velit nec purus convallis.</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
                        	</div>
                        </div>
                    </article>
                </div>

                

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp animated ">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">COOKING</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$75.00</span></div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget. Praesent malesuada facilisis maximus. Integer blandit velit nec purus convallis.</li></ul>

							<div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
                        	</div>
                        </div>
                    </article>
                </div>

            <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp animated ">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Professional DESIGN</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$1220</span></div>
                                <div class="landing-block-node-price-text">per course</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum turpis eget. Praesent malesuada facilisis maximus. Integer blandit velit nec purus convallis.</li></ul>

							<div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#">Order Now</a>
                        	</div>
                        </div>
                    </article>
                </div></div>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15">OUR PARTNERS</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-27">WHO TRUSTS US</h2>
            </div>

			<div class="landing-block-node-text"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id.</p></div>
        </div>

    </section>',
			],
		'24.3.image_gallery_6_cols_fix_3' =>
			[
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block js-animation text-center g-py-90 g-pt-0 g-pb-0 zoomIn">
	<div class="landing-block-node-container container g-brd-gray-light-v4">
		<div class="row g-brd-top g-brd-left g-brd-color-inherit mx-0">
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img9.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img10.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img11.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img12.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img13.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img14.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img15.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img16.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img17.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img18.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img19.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img20.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
		</div>
	</div>
</section>',
			],
		'40.2.two_cols_carousel' =>
			[
				'CODE' => '40.2.two_cols_carousel',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-pt-100">
	<div class="container-fluid g-px-30">
		<div class="js-carousel g-pos-rel g-mx-minus-15"
			 data-infinite="true"
			 data-slides-show="2"
			 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-40 g-color-white g-bg-primary"
			 data-arrow-left-classes="fa fa-angle-left g-left-minus-25"
			 data-arrow-right-classes="fa fa-angle-right g-right-minus-25"
			 data-responsive=\'[{
                 "breakpoint": 1200,
                 "settings": {
                   "slidesToShow": 2,
                   "slidesToScroll": 2
                 }
               }, {
                 "breakpoint": 576,
                 "settings": {
                   "slidesToShow": 1,
                   "slidesToScroll": 1
                 }
               }]\'>
			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="g-parent g-pos-rel">
					<figure class="u-bg-overlay g-bg-black-opacity-0_5--after g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid w-100 g-grayscale-100x g-grayscale-0--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1332x806/img1.jpg" alt="" />
					</figure>

					<div class="media d-block d-md-flex u-bg-overlay__inner g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-10 g-pa-50--lg landing-block-node-container js-animation fadeIn">
						<div class="landing-block-node-card-date g-line-height-1 g-font-weight-700 g-font-size-36 g-color-primary g-mr-20">
							02.07
						</div>

						<div class="media-body g-color-white">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-10">
								Aliquam erat volutpat
							</h3>
							<div class="landing-block-node-card-text g-font-size-16 g-color-white-opacity-0_7 mb-0">
								<p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum
									turpis eget nisi facilisis lobortis.
								</p>
							</div>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="g-parent g-pos-rel">
					<figure class="u-bg-overlay g-bg-black-opacity-0_5--after g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid w-100 g-grayscale-100x g-grayscale-0--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1332x806/img2.jpg" alt="" />
					</figure>

					<div class="media d-block d-md-flex u-bg-overlay__inner g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-10 g-pa-50--lg landing-block-node-container js-animation fadeIn">
						<div class="landing-block-node-card-date g-line-height-1 g-font-weight-700 g-font-size-36 g-color-primary g-mr-20">
							01.07
						</div>

						<div class="media-body g-color-white">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-10">
								Aliquam erat volutpat
							</h3>
							<div class="landing-block-node-card-text g-font-size-16 g-color-white-opacity-0_7 mb-0">
								<p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum
									turpis eget nisi facilisis lobortis.
								</p>
							</div>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="g-parent g-pos-rel">
					<figure class="u-bg-overlay g-bg-black-opacity-0_5--after g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid w-100 g-grayscale-100x g-grayscale-0--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1332x806/img3.jpg" alt="" />
					</figure>

					<div class="media d-block d-md-flex u-bg-overlay__inner g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-10 g-pa-50--lg landing-block-node-container js-animation fadeIn">
						<div class="landing-block-node-card-date media-left g-line-height-1 g-font-weight-700 g36ont-size-18 g-color-primary g-mr-20">
							04.06
						</div>
						<div class="media-body g-color-white">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-10">
								Aliquam erat volutpat
							</h3>
							<div class="landing-block-node-card-text g-font-size-16 g-color-white-opacity-0_7 mb-0">
								<p>Fusce dolor libero, efficitur et lobortis at,
									faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis.
								</p>
							</div>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide g-px-15">
				<!-- Article -->
				<article class="g-parent g-pos-rel">
					<figure class="u-bg-overlay g-bg-black-opacity-0_5--after g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid w-100 g-grayscale-100x g-grayscale-0--parent-hover g-transition-0_2 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1332x806/img4.jpg" alt="" />
					</figure>

					<div class="media d-block d-md-flex u-bg-overlay__inner g-pos-abs g-bottom-0 g-left-0 w-100 g-pa-10 g-pa-50--lg landing-block-node-container js-animation fadeIn">
						<div class="landing-block-node-card-date media-left g-line-height-1 g-font-weight-700 g36ont-size-18 g-color-primary g-mr-20">
							02.05
						</div>
						<div class="media-body g-color-white">
							<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-10">
								Aliquam erat volutpat
							</h3>
							<div class="landing-block-node-card-text g-font-size-16 g-color-white-opacity-0_7 mb-0">
								<p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc. Proin fermentum
									turpis eget nisi facilisis lobortis.
								</p>
							</div>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title@4' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pb-20 g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-27">CONTACT US</h2>
            </div>
        </div>
    </section>',
			],
		'33.13.form_2_light_no_text' =>
			[
				'CODE' => '33.13.form_2_light_no_text',
				'SORT' => '9500',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-pt-20 g-pb-60">

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
		'35.2.footer_dark' =>
			[
				'CODE' => '35.2.footer_dark',
				'SORT' => '10000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60 g-pt-55 g-pb-30 g-bg-pattern-green-v1">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">Company24 courses</h2>
				<div class="landing-block-node-text g-mb-20 g-color-gray-light-v5"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae est lorem. Aenean imperdiet nisi a dolor condimentum, id ullamcorper lacus vestibulum.</p></div>
				<address class="g-color-gray-light-v1 g-mb-20">
				</address>
			</div>
			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20"> </h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">USEFUL Links</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Pellentesque a tristique risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Nunc vitae libero lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Integer commodo est</a>
					</li>
				<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Proin sollicitudin turpis in massa rutrum</a>
					</li><li class="landing-block-card-list-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-light-v5" href="#" target="_self">Vestibulum semper</a>
					</li></ul>
			</div>

		</div>
	</div>
</section>',
			],
	]
];