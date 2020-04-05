<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => '1construction',
			'THEME_CODE_TYPO' => '1construction',
		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.april-fool/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_APRIL_FOOL_DESCRIPTION')
		)
	),
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(3,11,4,1) ? 81 : -131,
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array(),
		'EXCEPT' => array()
	)),
	'items' => array (
		'0.menu_15_photography' =>
			array (
				'CODE' => '0.menu_15_photography',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu landing-ui-pattern-transparent u-header u-header--floating u-header--floating-relative">
	<!-- Top Bar -->
	<div class="landing-block-node-top-block u-header__section u-header__section--hidden g-bg-white g-transition-0_3 g-pt-15 g-pb-15">
		<div class="container">
			<div class="row flex-column flex-md-row align-items-center justify-content-md-end text-uppercase g-font-weight-700 g-font-size-13 g-mt-minus-10">
				<div class="col-auto text-center text-md-left g-font-size-10 mr-md-auto g-mt-10">
					<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm">
						<div class="landing-block-node-menu-contact-title d-inline-block g-color-gray-dark-v5">
							Phone Number:
						</div>
						<div class="landing-block-node-menu-contact-text d-inline-block g-font-weight-900 g-color-gray-dark-v2">
							<a href="tel:+4554554554">+4 554 554 554</a>
						</div>
					</div>

					<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm">
						<div class="landing-block-node-menu-contact-title d-inline-block g-color-gray-dark-v5">
							Email:
						</div>
						<div class="landing-block-node-menu-contact-text d-inline-block g-font-weight-900 g-color-gray-dark-v2">
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
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/photography-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto g-mr-20">
						<li class="landing-block-node-menu-list-item nav-item g-mr-20--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[46.2.cover_with_2_big_images_cols]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-size-13" target="_self">Home</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-20--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[19.6.features_two_cols_with_bg_pattern]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-size-13" target="_self">Products</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-20--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-size-13" target="_self">portfolio</a>
						</li>
						
						
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-20--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[11.2.three_cols_fix_tariffs_with_img]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-size-13" target="_self">Offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-20--lg">
							<a href="#block@block[35.1.footer_light]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-size-13" target="_self">Contacts</a>
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
		'46.1.cover_with_bg_image_and_big_title' =>
			array (
				'CODE' => '46.1.cover_with_bg_image_and_big_title',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pb-30">
	<div class="container">
		<div class="landing-block-node-bgimg g-bg-cover g-bg-pos-top-center g-bg-img-hero g-bg-black-opacity-0_1--after g-px-20 g-py-200" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x869/img1.jpg\');" data-fileid="-1">
			<div class="text-center g-pos-rel g-z-index-1 landing-block-node-container js-animation zoomIn">
				<div class="landing-block-node-subtitle g-color-white g-font-size-20 text-uppercase g-letter-spacing-5 g-mb-50">the best day is</div>
				<h2 class="landing-block-node-title h2 d-inline-block g-brd-around g-brd-2 g-brd-white g-color-white g-font-weight-700 g-font-size-40 text-uppercase g-line-height-1_2 g-letter-spacing-5 g-py-12 g-px-20 g-mb-50">April Fools&amp;#039; Day</h2>
				<div class="landing-block-node-text g-color-white g-font-size-20 text-uppercase g-letter-spacing-5 mb-0"> </div>
			</div>
		</div>
	</div>
</section>',
			),
		'46.2.cover_with_2_big_images_cols' =>
			array (
				'CODE' => '46.2.cover_with_2_big_images_cols',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-75">
	<div class="container">
		<div class="row">

			<div class="col-12 col-md-6 g-min-height-540 g-max-height-810">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover js-animation fadeIn">
						<img class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x751/img1.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<article class="landing-block-node-img-title-border h-100 g-flex-middle text-center
								g-brd-around g-brd-white-opacity-0_2 text-uppercase g-color-white">
								<div class="g-flex-middle-item">
									<h3 class="landing-block-node-img-title g-color-white h3 g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-mb-20">Surprise!</h3>
									<div class="landing-block-node-img-text g-letter-spacing-3 g-font-weight-300 g-mb-40">UT PULVINAR TELLUS JHSED SED ELIT</div>
									<div class="landing-block-node-button-container">
										<a class="landing-block-node-img-button btn btn-md u-btn-outline-white g-font-size-11 g-brd-2 rounded-0 g-px-25" href="#">Learn More</a>
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
						<img class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x751/img2.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<article class="landing-block-node-img-title-border h-100 g-flex-middle text-center
								g-brd-around g-brd-white-opacity-0_2 text-uppercase g-color-white">
								<div class="g-flex-middle-item">
									<h3 class="landing-block-node-img-title g-color-white h3 g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-mb-20">funny presents!</h3>
									<div class="landing-block-node-img-text g-letter-spacing-3 g-font-weight-300 g-mb-40">
										Ut pulvinar tellus jhsed sed elit
									</div>
									<div class="landing-block-node-button-container">
										<a class="landing-block-node-img-button btn btn-md u-btn-outline-white g-font-size-11 g-brd-2 rounded-0 g-px-25" href="#">Learn More</a>
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
			),
		'19.6.features_two_cols_with_bg_pattern' =>
			array (
				'CODE' => '19.6.features_two_cols_with_bg_pattern',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80 g-bg-pattern-dark-v1">
	<div class="container">
		<div class="text-uppercase text-center g-mb-70">
			<h2 class="landing-block-node-title d-inline-block g-letter-spacing-0_5 g-font-weight-700 g-font-size-12 g-color-white g-brd-bottom g-brd-5 g-brd-white g-pb-8 g-mb-20">Our products</h2>
			<div class="landing-block-node-text text-uppercase g-letter-spacing-3 g-font-size-12 g-color-gray-dark-v4 mb-0">
				<p>Atveroreas Pin sdf hero vero eos et accusamus</p>
			</div>
		</div>

		<div class="row">
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img18.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">red baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Lorem ipsum dolor sit amet, consectetur sdgaaa
								adipiscing elit, sed do eius fgtrrwe mod tempor incididunt ut labore et dolore magna aliqua.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img19.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">purple baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Fusce dolor libero, efficitur et lobortis at,
								faucibus nec nunc. Proin fermentum turpis eget nisi facilisis lobortis. Praesent
								malesuada facilisis maximus.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
			
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img20.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">yellow baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Mauris sodales tellus vel felis dapibus, sit amet
								porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. Cras porttitor
								auctor sapien, eu tempus nunc placerat nec.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img21.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">blue baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Donec metus tortor, dignissim at vehicula ac,
								lacinia vel massa. Quisque mollis dui lacus, et fermentum erat euismod in. Integer sit
								amet augue ligula.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
			
			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img22.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">Orange baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Efficitur ipsum dolor sit amet, consectetur
								adipiscing elit. Praesent efficitur tristique felis, nec malesuada neque. Nulla nulla
								ante, dictum at tempor eget.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>

			<div class="landing-block-node-card col-lg-6 g-pl-100--md g-mb-30 js-animation slideInUp">
				<article class="landing-block-node-card-container media d-block d-md-flex h-100 g-bg-white">
					<!-- Article Image -->
					<div class="d-md-flex align-self-center g-mr-30--md g-ml-minus-82--md">
						<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/169x169/img23.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<div class="media-body align-self-center g-py-50 g-pl-40 g-pl-0--md g-pr-40">
						<h3 class="landing-block-node-card-title h6 text-uppercase g-letter-spacing-4 g-font-weight-700 g-mb-20">green baloons</h3>
						<div class="landing-block-node-card-text g-color-gray-dark-v5 mb-0">
							<p>Lobortis usce dolor libero, efficitur et lobortis
								at, faucibus nec nunc. Proin eget nisi facilisis lobortis. Praesent malesuada facilisis
								maximus.
							</p>
						</div>
					</div>
					<!-- End Article Content -->
				</article>
			</div>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-pb-20 g-bg-main g-pt-60 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-black g-mb-minus-10 g-font-size-12">OUR PORTFOLIO</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5 g-letter-spacing-3"><p>FOGNKE TGDL VERO EOS ET ACCUSAMUS</p></div>
        </div>

    </section>',
			),
		'32.5.img_grid_3cols_1' =>
			array (
				'CODE' => '32.5.img_grid_3cols_1',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-80">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img6.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3
							g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">surpise!</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img7.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transiti	on--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3
							g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">SURPISE!</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-4">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img8.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3
							g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20">SURPISE!</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			),
		'10.2.two_cols_big_img_text_and_text_blocks_2' =>
			array (
				'CODE' => '10.2.two_cols_big_img_text_and_text_blocks_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block row no-gutters g-bg-main">
        <div class="landing-block-node-texts col-lg-7 g-pt-100 g-pb-80 g-px-15 g-px-40--md g-bg-pattern-dark-v1">
            <header class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white-opacity-0_9">ABOUT US</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-white g-text-transform-none g-letter-spacing-3 g-font-size-27"><span style="font-weight: 400;">WE ARE THE BEST</span></h2>
            </header>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Lorem ipsum dolor sit amet, Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec massa ngilla vel, aliquet necmassa quis enim. Donec pede justo, fringilla vel, aliquet nec.<br /></p><p>Vulputate eget, arcuiet a, venenatis vitae, justo. Nullam dictum felis eu pede eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend m lorem ante.</p><p><span style="font-size: 0.92857rem;">Dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorperringilla vel, aliquet necNam eget dui.</span><br /></p></div>

            <div class="row align-items-stretch">

            

            


            

            </div>
        </div>

		<div class="landing-block-node-img col-lg-5 g-min-height-360 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img7.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
    </section>',
			),
		'11.2.three_cols_fix_tariffs_with_img' =>
			array (
				'CODE' => '11.2.three_cols_fix_tariffs_with_img',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-100 g-pb-100">
	<div class="container">
		<div class="text-uppercase text-center g-mb-70">
			<h2 class="landing-block-node-title d-inline-block g-letter-spacing-0_5 g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-5 g-brd-primary g-pb-8 g-mb-20">
				Our offers
			</h2>
			<div class="landing-block-node-text text-uppercase g-letter-spacing-3 g-font-size-12 g-color-gray-dark-v5 mb-0">
				<p>Tgsdgwe sfgdrss dfw vero eos et accusamus</p>
			</div>
		</div>

		<div class="row">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x400/img1.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-emotsmile"></i>
						</span>
						<h4 class="landing-block-node-card-title h6 g-color-white g-mt-minus-25 g-mb-10">party glasses</h4>
						<div class="landing-block-node-card-text g-font-size-12 g-color-gray-dark-v4 mb-0">
							<p>Fusce dolor libero, efficitur et lobortis at, faucibus nec nunc.</p>
						</div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">
						$100.00
					</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">for<span style="font-weight: 700;"> fun</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">different </span>colors</li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">best gift for<span style="font-weight: 700;"> anyone</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><b>no</b> Proin laoreet accumsan
							nisl
						</li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn btn-md rounded-0 u-btn-outline-white g-brd-2 g-font-size-12 g-font-weight-600 g-letter-spacing-1" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x400/img2.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-emotsmile"></i>
						</span>
						<h4 class="landing-block-node-card-title h6 g-color-white g-mt-minus-25 g-mb-10">Confetti</h4>
						<div class="landing-block-node-card-text g-font-size-12 g-color-gray-dark-v4 mb-0"><p>FUSCE DOLOR LIBERO, EFFICITUR ET LOBORTIS AT, FAUCIBUS NEC NUNC.</p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">
						$150.00
					</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">FOR<span style="font-weight: 700;"> FUN</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">DIFFERENT </span>COLORS</li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">BEST GIFT FOR<span style="font-weight: 700;"> ANYONE</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><b>no</b> Proin laoreet accumsan
							nisl
						</li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn btn-md rounded-0 u-btn-outline-white g-brd-2 g-font-size-12 g-font-weight-600 g-letter-spacing-1" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 g-mb-0--md">
				<!-- Article -->
				<article class="landing-block-node-card-container js-animation text-center text-uppercase g-theme-photography-bg-gray-dark-v2 g-color-white-opacity-0_5 fadeInRight">
					<!-- Article Image -->
					<img class="landing-block-node-card-img w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x400/img3.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<header class="g-letter-spacing-3 g-pos-rel g-px-40 g-mb-30">
						<span class="landing-block-node-card-icon-container u-icon-v3 u-icon-size--xl g-rounded-50x g-font-size-26 g-bg-gray-dark-v1 g-color-white g-pull-50x-up">
							<i class="landing-block-node-card-icon icon-emotsmile"></i>
						</span>
						<h4 class="landing-block-node-card-title h6 g-color-white g-mt-minus-25 g-mb-10">greasepaint</h4>
						<div class="landing-block-node-card-text g-font-size-12 g-color-gray-dark-v4 mb-0"><p>FUSCE DOLOR LIBERO, EFFICITUR ET LOBORTIS AT, FAUCIBUS NEC NUNC.</p></div>
					</header>

					<div class="landing-block-node-card-price g-font-weight-700 d-block g-color-white g-font-size-26 g-letter-spacing-5 g-mb-20">
						$200.00
					</div>
					<ul class="landing-block-node-card-price-list list-unstyled g-letter-spacing-0_5 g-font-size-12 mb-0">
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">FOR<span style="font-weight: 700;"> FUN</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><span style="font-weight: 700;">DIFFERENT </span>COLORS</li>
						<li class="g-theme-photography-bg-gray-dark-v3 g-py-10 g-px-30">BEST GIFT FOR<span style="font-weight: 700;"> ANYONE</span></li>
						<li class="g-theme-photography-bg-gray-dark-v4 g-py-10 g-px-30"><b>no</b> Proin laoreet accumsan
							nisl
						</li>
					</ul>

					<footer class="g-pa-40 landing-block-node-card-button-containe">
						<a class="landing-block-node-card-button btn btn-md rounded-0 u-btn-outline-white g-brd-2 g-font-size-12 g-font-weight-600 g-letter-spacing-1" href="#">Order Now</a>
					</footer>
					<!-- End of Article Content -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			),
		'35.1.footer_light' =>
			array (
				'CODE' => '35.1.footer_light',
				'SORT' => '8000',
				'CONTENT' => '<section class="g-pt-60 g-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Contact
					us</h2>
				<div class="landing-block-node-text g-font-size-default g-color-gray-dark-v2 g-mb-20">
					<p>Lorem ipsum dolor sit amet, consectetur
						adipiscing</p></div>

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
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">
					Categories</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Customer
					Support</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Nunc vitae libero
							lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Top
					Link</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Nunc vitae libero
							lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5" href="#">Praesent pulvinar
							gravida</a>
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
		'17.copyright' =>
			array (
				'CODE' => '17.copyright',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="text-center g-color-gray-dark-v3 g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12  js-animation animation-none">
				<p>&copy; 2018 All rights reserved.</p>
			</div>
		</div>
	</div>
</section>',
			),
	),
);