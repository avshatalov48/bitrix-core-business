<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__DIR__.'/.description-nottranslate.php');

return [
	'name' => Loc::getMessage('LANDING_DEMO_23FEB1_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_23FEB1_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'spa',

		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.23february.1/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_23FEB1_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_23FEB1_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_23FEB1_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_23FEB1_DESCRIPTION')
		]
	],
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => ['ru', 'kz', 'by'],
		'EXCEPT' => []
	]),
	'items' => [
		'0.menu_15_photography' =>
			[
				'CODE' => '0.menu_15_photography',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu u-header u-header--sticky u-header--relative" >
	<!-- Top Bar -->
	<div class="landing-block-node-top-block u-header__section u-header__section--hidden g-bg-white g-transition-0_3 g-pt-15 g-pb-15">
		<div class="container">
			<div class="row flex-column flex-md-row align-items-center justify-content-md-end text-uppercase g-font-weight-700 g-font-size-13 g-mt-minus-10">
				<div class="col-auto text-center text-md-left g-font-size-10 mr-md-auto g-mt-10">
					<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm">
						<div class="landing-block-node-menu-contact-title d-inline-block">
							Phone Number:
						</div>
						<div class="landing-block-node-menu-contact-text d-inline-block g-font-weight-900">
							<a href="tel:+4554554554">+4 554 554 554</a>
						</div>
					</div>

					<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm">
						<div class="landing-block-node-menu-contact-title d-inline-block">
							Email:
						</div>
						<div class="landing-block-node-menu-contact-text d-inline-block g-font-weight-900">
							<a href="mailto:support@company24.com">support@company24.com</a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
	<!-- End Top Bar -->

	<div class="landing-block-node-bottom-block u-header__section u-header__section--light g-bg-gray-light-v5 g-py-30"
		 data-header-fix-moment-classes="u-shadow-v27">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/photography-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto g-mr-20">
						<li class="landing-block-node-menu-list-item nav-item g-mr-20--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[46.1.cover_with_bg_image_and_big_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT1").'</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-20--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[19.6.features_two_cols_with_bg_pattern]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT2").'</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-20--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[11.2.three_cols_fix_tariffs_with_img]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT3").'</a>
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
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-5 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'46.1.cover_with_bg_image_and_big_title' =>
			[
				'CODE' => '46.1.cover_with_bg_image_and_big_title',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block g-pb-30 g-bg-main">
	<div class="container">
		<div class="landing-block-node-bgimg g-bg-cover g-bg-pos-top-center g-bg-img-hero g-bg-black-opacity-0_1--after g-px-20 g-py-200" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1068/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
			<div class="text-center g-pos-rel g-z-index-1 landing-block-node-container js-animation zoomIn">
				<div class="landing-block-node-subtitle g-color-white g-font-size-20 text-uppercase g-letter-spacing-5 g-mb-50"> </div>
				<h2 class="landing-block-node-title d-inline-block g-brd-around g-brd-2 g-brd-white g-color-white g-font-weight-700 g-font-size-40 text-uppercase g-line-height-1_2 g-letter-spacing-5 g-py-12 g-px-20 g-mb-50">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT4").'</h2>
				<div class="landing-block-node-text g-color-white g-font-size-20 text-uppercase g-letter-spacing-5 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT5").'</p></div>
			</div>
		</div>
	</div>
</section>',
			],
		'46.2.cover_with_2_big_images_cols' =>
			[
				'CODE' => '46.2.cover_with_2_big_images_cols',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-30">
	<div class="container">
		<div class="row">

			<div class="col-12 col-md-6 g-min-height-540 g-max-height-810">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover js-animation fadeIn">
						<img class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<article class="landing-block-node-img-title-border h-100 g-flex-middle text-center g-brd-around g-brd-white-opacity-0_2 text-uppercase g-color-white">
								<div class="g-flex-middle-item">
									<h3 class="landing-block-node-img-title g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT6").'</h3>
									<div class="landing-block-node-img-text g-letter-spacing-3 g-font-weight-300 g-mb-40">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT7").'</div>
									<div class="landing-block-node-button-container">
										<a class="landing-block-node-img-button btn bg-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT8").'</a>
									</div>
								</div>
							</article>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 g-min-height-540 g-max-height-810">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover js-animation fadeIn">
						<img class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<article class="landing-block-node-img-title-border h-100 g-flex-middle text-center g-brd-around g-brd-white-opacity-0_2 text-uppercase g-color-white">
								<div class="g-flex-middle-item">
									<h3 class="landing-block-node-img-title g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT9").'</h3>
									<div class="landing-block-node-img-text g-letter-spacing-3 g-font-weight-300 g-mb-40">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT10").'</div>
									<div class="landing-block-node-button-container">
										<a class="landing-block-node-img-button btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT11").'</a>
									</div>
								</div>
							</article>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pb-20 g-bg-main g-pt-60 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">
            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-33">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT12").'<br /></h2>
            </div>
			<div class="landing-block-node-text g-letter-spacing-3"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT13").'</p></div>
        </div>
    </section>',
			],
		'45.1.gallery_app_wo_slider' =>
			[
				'CODE' => '45.1.gallery_app_wo_slider',
				'SORT' => '2000',
				'CONTENT' => '<div class="landing-block g-pt-80 g-pb-80">
	<div class="container">
		<div class="js-gallery-cards row">
			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/530x960/img1.jpg" alt="" class="landing-block-node-card-img g-min-height-380 g-object-fit-cover h-100 w-100" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title g-color-white g-font-size-12">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT14").'</h3>
						<div class="landing-block-node-card-subtitle g-color-white"> </div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/530x960/img2.jpg" alt="" class="landing-block-node-card-img g-min-height-380 g-object-fit-cover h-100 w-100" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title g-color-white g-font-size-12">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT15").'</h3>
						<div class="landing-block-node-card-subtitle g-color-white"> </div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/530x960/img3.jpg" alt="" class="landing-block-node-card-img g-min-height-380 g-object-fit-cover h-100 w-100" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title g-color-white g-font-size-12">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT16").'</h3>
						<div class="landing-block-node-card-subtitle g-color-white"> </div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/530x960/img4.jpg" alt="" class="landing-block-node-card-img g-min-height-380 g-object-fit-cover h-100 w-100" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title g-color-white g-font-size-12">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT17").'</h3>
						<div class="landing-block-node-card-subtitle g-color-white"> </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>',
			],
		'19.6.features_two_cols_with_bg_pattern' =>
			[
				'CODE' => '19.6.features_two_cols_with_bg_pattern',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80 g-bg-pattern-dark-v1">
	<div class="container">
		<div class="text-uppercase text-center g-mb-70">
			<h2 class="landing-block-node-title d-inline-block g-letter-spacing-0_5 g-font-weight-700 g-font-size-12 g-color-white g-brd-bottom g-brd-5 g-brd-white g-pb-8 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT18").'</h2>
			<div class="landing-block-node-text text-uppercase g-letter-spacing-3 g-font-size-12 g-color-gray-dark-v4 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT19").'</p></div>
		</div>

		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img13.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT20").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT21").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT22").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT23").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
			
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img14.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT24").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT25").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img15.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT27").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT26").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
			
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img16.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT28").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT29").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img17.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h6 class="landing-block-node-card-title text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT29").'</h6>
						<div class="landing-block-node-card-text mb-0">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT30").'</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pb-20 g-bg-main g-pt-60 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">
            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-font-size-12">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT31").'</h2>
            </div>
			<div class="landing-block-node-text g-letter-spacing-3"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT32").'</p></div>
        </div>
    </section>',
			],
		'32.5.img_grid_3cols_1' =>
			[
				'CODE' => '32.5.img_grid_3cols_1',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-30">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/960x600/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT33").'</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/960x600/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transiti	on--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT34").'</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/960x600/img6.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT35").'</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			],
		'44.6.two_columns_with_peoples' =>
			[
				'CODE' => '44.6.two_columns_with_peoples',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-30">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 font-italic g-font-weight-600 g-mb-20"'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT36").'</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT37").'</div>
					<div class="landing-block-node-card-text g-mb-40"><p>
							'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT38").'</p></div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 font-italic g-font-weight-600 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT39").'</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT40").'</div>
					<div class="landing-block-node-card-text g-mb-40"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT41").'</p></div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			],
		'11.2.three_cols_fix_tariffs_with_img' =>
			[
				'CODE' => '11.2.three_cols_fix_tariffs_with_img',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-100 g-pb-100">
	<div class="container">
		<div class="text-uppercase text-center g-mb-70">
			<h2 class="landing-block-node-title d-inline-block g-letter-spacing-0_5 g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-5 g-brd-primary g-pb-8 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT42").'</h2>
			<div class="landing-block-node-text text-uppercase g-letter-spacing-3 g-font-size-12 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT43").'</p></div>
		</div>

		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x200/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-camera"></i>
						</span>
						<h6 class="landing-block-node-card-title g-color-white g-mt-minus-25 g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT44").'</h6>
						<div class="landing-block-node-card-text g-font-size-12 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT45").'</p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT46").'</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT47").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT48").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT49").'</li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT50").'</li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0 g-letter-spacing-1" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT51").'</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x200/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-film"></i>
						</span>
						<h6 class="landing-block-node-card-title g-color-white g-mt-minus-25 g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT52").'</h6>
						<div class="landing-block-node-card-text g-font-size-12 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT53").'</p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT54").'</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT55").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT56").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT57").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT58").'</span></li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0 g-letter-spacing-1" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT59").'</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/370x200/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container m-auto u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-star"></i>
						</span>
						<h6 class="landing-block-node-card-title g-color-white g-mt-minus-25 g-mb-10">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT60").'</h6>
						<div class="landing-block-node-card-text g-font-size-12 mb-0"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT61").'</p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT62").'</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT63").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT64").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT65").'</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT66").'</span></li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m rounded-0 g-letter-spacing-1" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT67").'</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			],
		'35.1.footer_light' =>
			[
				'CODE' => '35.1.footer_light',
				'SORT' => '5000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT68").'</h2>
				<div class="landing-block-node-text g-mb-20"><p>'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT69").'</p></div>

				<address class="g-mb-20">
					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text">
							'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT70").'<span style="font-weight: bold;">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT71").'</span>
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
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT72").'</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item " href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT73").'</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT74").'</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT75").'</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT76").'</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT77").'</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT78").'</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT79").'</h2>
				<ul class="list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT80").'</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#" target="_self">'. Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_23FEB1__TEXT81").'</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			],
		'17.copyright' =>
			[
				'CODE' => '17.copyright',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="text-center g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12  js-animation animation-none">
				<p>&copy; 2022 All right reserved.</p>
			</div>
		</div>
	</div>
</section>',
			],
	],
];