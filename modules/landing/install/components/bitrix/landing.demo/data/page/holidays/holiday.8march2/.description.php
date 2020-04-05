<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_8MARCH2_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_8MARCH2_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.8march2/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_8MARCH2_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_8MARCH2_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_8MARCH2_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_8MARCH2_DESCRIPTION'),
		)
	),
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(2,15,3,8) ? 92 : -122,
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array('ru', 'kz', 'by', 'ua'),
		'EXCEPT' => array()
	)),
	'items' => array (
		'0.menu_20_wedding' =>
			array (
				'CODE' => '0.menu_20_wedding',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--floating u-header--floating-relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-25"
		 data-header-fix-moment-exclude="g-py-25"
		 data-header-fix-moment-classes="u-shadow-v27 g-py-15">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo" target="_self">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/wedding-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Home</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Products</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@2]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Our team</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@3]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Specials</a>
						</li><li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@4]" class="landing-block-node-menu-list-item-link nav-link p-0 g-font-roboto-slab" target="_self">Contact</a>
						</li>
						
						
						
						
						
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-17 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'01.big_with_text_blocks' =>
			array (
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="10000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1265/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig g-mx-0">
					<h2 class="landing-block-node-card-title g-font-weight-700 g-mb-20 g-font-cormorant-infant g-text-transform-none g-font-size-120 g-color-primary"><span style="color: rgb(245, 245, 245); font-style: italic;">Happy woman day!</span></h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"> </div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-lg u-btn-primary g-font-weight-700 text-uppercase g-px-25 g-py-15 g-rounded-10 g-font-size-12" href="#" tabindex="-1" target="_self">view more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		
		

		

	</div>
</section>',
			),
		'47.1.title_with_icon' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-10">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp">Variety of flowers<br /></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-default fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
			</div>
		</div>

		<div class="landing-block-node-text g-color-gray-dark-v5 mb-0 g-font-open-sans g-font-size-14 js-animation fadeInUp"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
	</div>
</section>',
			),
		'32.6.img_grid_4cols_1' =>
			array (
				'CODE' => '32.6.img_grid_4cols_1',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-95">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/480x480/img1.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3 g-color-white g-line-height-1_4 g-font-cormorant-infant g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-style: italic; font-weight: 700;">Yellow</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/480x480/img2.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3 g-color-white g-line-height-1_4 g-font-cormorant-infant g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-style: italic; font-weight: 700;">Orange</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/480x480/img3.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3 g-color-white g-line-height-1_4 g-font-cormorant-infant g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-style: italic; font-weight: 700;">Pink</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/480x480/img4.jpg" alt="" data-fileid="-1" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0
							g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20
							g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-middle g-brd-around g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title g-flex-middle-item text-center h3 g-color-white g-line-height-1_4 g-font-cormorant-infant g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-style: italic; font-weight: 700;">Sunny</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			),
		'01.big_with_text_3' =>
			array (
				'CODE' => '01.big_with_text_3',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pb-70 g-pt-70" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x669/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-color-primary g-font-cormorant-infant g-text-transform-none g-font-size-80"><span style="font-style: italic;"><span style="color: rgb(245, 245, 245);">The best only</span> for you!</span></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn btn-xl u-btn-primary text-uppercase g-font-weight-700 g-font-size-12 g-py-15 g-px-40 g-mb-15 g-rounded-10" target="_self">view more</a>
		</div>
	</div>
</section>',
			),
		'47.1.title_with_icon@2' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-10">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp">Our team</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-default fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
			</div>
		</div>

		<div class="landing-block-node-text g-color-gray-dark-v5 mb-0 g-font-open-sans g-font-size-14 js-animation fadeInUp"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
	</div>
</section>',
			),
		'44.6.two_columns_with_peoples' =>
			array (
				'CODE' => '44.6.two_columns_with_peoples',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pb-30 g-pt-20">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/300x300/img1.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20">Samantha Fox</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30 g-font-open-sans">florist</div>
					<div class="landing-block-node-card-text g-font-size-default g-color-gray-dark-v5 g-mb-40 g-font-open-sans g-font-size-14"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/300x300/img4.jpg" alt="" data-fileid="-1" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20">Victoria Page</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30 g-font-open-sans">florist</div>
					<div class="landing-block-node-card-text g-font-size-default g-color-gray-dark-v5 g-mb-40 g-font-open-sans g-font-size-14"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-pb-10 g-pt-15 g-bg-primary js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-font-cormorant-infant g-font-size-60 g-line-height-2 g-letter-spacing-1 g-color-white"><span style="font-style: italic; font-weight: 700;">Testimonials<br /></span></h2>
                <div class="landing-block-node-text g-color-white-opacity-0_8 g-font-size-14"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
            </div>
        </div>

    </section>',
			),
		'20.3.four_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20 g-bg-primary">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img15.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-color-white g-text-transform-none g-font-cormorant-infant g-font-size-27--md"><span style="font-style: italic;">Angela</span></h3>
				<div class="landing-block-node-text g-color-white"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img16.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-color-white g-text-transform-none g-font-cormorant-infant g-font-size-27--md"><span style="font-style: italic;">Monica</span></h3>
				<div class="landing-block-node-text g-color-white"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img17.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-color-white g-text-transform-none g-font-cormorant-infant g-font-size-27--md"><span style="font-style: italic;">Victoria</span></h3>
				<div class="landing-block-node-text g-color-white"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img18.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-color-white g-text-transform-none g-font-cormorant-infant g-font-size-27--md"><span style="font-style: italic;">Elizabeth</span></h3>
				<div class="landing-block-node-text g-color-white"><p>Sed feugiat porttitor nunc Etiam gravida ex justo ac rhoncus purus tristique ut.</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'47.1.title_with_icon@3' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-10">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp">Special offers</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-default fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
			</div>
		</div>

		<div class="landing-block-node-text g-color-gray-dark-v5 mb-0 g-font-size-14 g-font-open-sans js-animation fadeInUp"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
	</div>
</section>',
			),
		'44.7.three_columns_with_img_and_price' =>
			array (
				'CODE' => '44.7.three_columns_with_img_and_price',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block g-pt-65 g-pb-65">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 g-font-cormorant-infant font-italic g-font-weight-700 g-color-white mb-0 g-font-size-23--md">Pink flower set</h4>
						<div class="landing-block-node-card-subtitle g-font-size-default g-color-white-opacity-0_6 g-font-open-sans">Only the best!</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x400/img1.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle g-font-size-default g-color-gray-light-v1 g-font-open-sans">From</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22 g-font-open-sans">$200</div>
						</div>

						<div class="landing-block-node-card-text g-font-size-default g-color-gray-light-v1 g-mb-40 g-font-open-sans g-font-size-14"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn text-uppercase u-btn-primary g-font-weight-700 g-rounded-20 g-py-15 g-brd-0 g-font-size-11" href="#" target="_self">make order</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 g-font-cormorant-infant font-italic g-font-weight-700 g-color-white mb-0 g-font-size-23--md">Orange-red flower set</h4>
						<div class="landing-block-node-card-subtitle g-font-size-default g-color-white-opacity-0_6 g-font-open-sans">Only the best!</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x400/img2.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle g-font-size-default g-color-gray-light-v1 g-font-open-sans">From</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22 g-font-open-sans">$50</div>
						</div>

						<div class="landing-block-node-card-text g-font-size-default g-color-gray-light-v1 g-mb-40 g-font-open-sans g-font-size-14"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn text-uppercase u-btn-primary g-font-weight-700 g-rounded-20 g-py-15 g-brd-0 g-font-size-11" href="#" target="_self">make order</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 g-font-cormorant-infant font-italic g-font-weight-700 g-color-white mb-0 g-font-size-23--md">White-purple flower set</h4>
						<div class="landing-block-node-card-subtitle g-font-size-default g-color-white-opacity-0_6 g-font-open-sans">Only the best!</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x400/img3.jpg" alt="" data-fileid="-1" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle g-font-size-default g-color-gray-light-v1 g-font-open-sans">From</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22 g-font-open-sans">$150</div>
						</div>

						<div class="landing-block-node-card-text g-font-size-default g-color-gray-light-v1 g-mb-40 g-font-open-sans g-font-size-14"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p></div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn text-uppercase u-btn-primary g-font-weight-700 g-rounded-20 g-py-15 g-brd-0 g-font-size-11" href="#" target="_self">make order</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>
		</div>
	</div>
</section>',
			),
		'47.1.title_with_icon@4' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '11500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-80">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp">Contact us</h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-default fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-11 fa fa-diamond"></i>
				<i class="landing-block-node-icon g-font-size-8 fa fa-diamond"></i>
			</div>
		</div>

		<div class="landing-block-node-text g-color-gray-dark-v5 mb-0 g-font-size-14 g-font-open-sans js-animation fadeInUp"><p>Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
	</div>
</section>',
			),
		'33.1.form_1_transparent_black_left_text' =>
			array (
				'CODE' => '33.1.form_1_transparent_black_left_text',
				'SORT' => '12000',
				'CONTENT' => '<section class="landing-block g-pos-rel g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img10.jpg\');" data-fileid="-1">
		<div class="container g-pos-rel g-z-index-1">
			<div class="row align-items-center">

				<div class="landing-block-form-styles" hidden="">
					<div class="g-bg-transparent h1 g-color-white g-brd-none g-pa-0" data-form-style-wrapper-padding="1" data-form-style-bg="1" data-form-style-bg-content="1" data-form-style-bg-block="1" data-form-style-header-font-size="1" data-form-style-header-font-weight="1" data-form-style-button-font-color="1" data-form-style-border-block="1">
					</div>
					<div class="g-bg-primary g-color-primary g-brd-primary" data-form-style-main-bg="1" data-form-style-main-border-color="1" data-form-style-main-font-color-hover="1">
					</div>
					<div class="g-bg-transparent g-brd-none g-brd-bottom g-brd-white" data-form-style-input-bg="1" data-form-style-input-border="1" data-form-style-input-border-radius="1" data-form-style-input-border-color="1">
					</div>
					<div class="g-brd-primary g-brd-none g-brd-bottom g-bg-black-opacity-0_7" data-form-style-input-border-hover="1" data-form-style-input-border-color-hover="1" data-form-style-input-select-bg="1">
					</div>
				</div>


				<div class="col-md-4 g-mb-60">
					<h2 class="landing-block-node-main-title h1 g-color-white mb-4 g-font-cormorant-infant js-animation fadeInUp"><span style="font-style: italic; font-weight: 700;">Contact Us</span></h2>
					
					<div class="landing-block-node-text g-line-height-1_5 text-left g-mb-40 g-color-white g-font-size-13 g-font-open-sans js-animation fadeInUp" data-form-style-main-font-family="1" data-form-style-main-font-weight="1" data-form-style-header-text-font-size="1" data-selector=".landing-block-node-text@0"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>

					<h3 class="h4 g-color-white mb-4 landing-block-node-title" data-form-style-main-font-color="1" data-form-style-main-font-family="1"> </h3>

					<!-- Icon Block -->
				<div class="landing-block-node-card-contact" data-card-preset="text">
					<div class="media align-items-center mb-4">
						<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-hotel-restaurant-235 u-line-icon-pro"></i>
								</span>
						</div>
						<div class="media-body">
							<div class="landing-block-node-contact-text g-color-white-opacity-0_6 mb-0"
								 data-form-style-main-font-weight="1"
								 data-form-style-header-text-font-size="1"
								 data-form-style-label-font-weight="1"
								 data-form-style-label-font-size="1"
								 data-form-style-second-font-color="1"
							>5B Streat, City 50987 New Town US
							</div>
						</div>
					</div>
				</div>
				<!-- End Icon Block -->

				<!-- Icon Block -->
				<div class="landing-block-node-card-contact" data-card-preset="link">
					<div class="media align-items-center mb-4">
						<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-communication-033 u-line-icon-pro"></i>
								</span>
						</div>
						<div class="media-body">
							<a href="tel:+32(0)333444555" class="landing-block-card-linkcontact-link g-color-white-opacity-0_6"
							 data-form-style-main-font-weight="1"
							 data-form-style-header-text-font-size="1"
							 data-form-style-label-font-weight="1"
							 data-form-style-label-font-size="1"
							 data-form-style-second-font-color="1">+32 (0) 333 444 555</a>
						</div>
					</div>
				</div>
				<!-- End Icon Block -->

				<!-- Icon Block -->
				<div class="landing-block-node-card-contact" data-card-preset="link">
					<div class="media align-items-center mb-4">
						<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-communication-033 u-line-icon-pro"></i>
								</span>
						</div>
						<a href="tel:+32(0)333444666" class="landing-block-card-linkcontact-link g-color-white-opacity-0_6"
						data-form-style-main-font-weight="1"
						 data-form-style-header-text-font-size="1"
						 data-form-style-label-font-weight="1"
						 data-form-style-label-font-size="1"
						 data-form-style-second-font-color="1">+32 (0) 333 444 666</a>
					</div>
				</div>
				<!-- End Icon Block -->

				<!-- Icon Block -->
				<div class="landing-block-node-card-contact" data-card-preset="link">
					<div class="media align-items-center mb-4">
						<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-communication-033 u-line-icon-pro"></i>
								</span>
						</div>
						<a href="tel:+32(0)333444777" class="landing-block-card-linkcontact-link g-color-white-opacity-0_6"
						data-form-style-main-font-weight="1"
						 data-form-style-header-text-font-size="1"
						 data-form-style-label-font-weight="1"
						 data-form-style-label-font-size="1"
						 data-form-style-second-font-color="1">+32 (0) 333 444 777</a>
					</div>
				</div>
				<!-- End Icon Block -->

				<!-- Icon Block -->
				<div class="landing-block-node-card-contact" data-card-preset="link">
					<div class="media align-items-center mb-4">
						<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-communication-062 u-line-icon-pro"></i>
								</span>
						</div>
						<a href="mailto:info@company24.com" class="landing-block-card-linkcontact-link g-color-white-opacity-0_6"
						data-form-style-main-font-weight="1"
						 data-form-style-header-text-font-size="1"
						 data-form-style-label-font-weight="1"
						 data-form-style-label-font-size="1"
						 data-form-style-second-font-color="1">info@company24.com</a>
					</div>
				</div>
				<!-- End Icon Block -->
				</div>


				<div class="col-md-8">
					<div class="bitrix24forms g-brd-none g-brd-around--sm g-brd-white-opacity-0_6 g-px-0 g-px-20--sm g-px-45--lg g-py-0 g-py-30--sm g-py-60--lg u-form-alert-v1" data-b24form="" data-form-style-input-border-color="1" data-b24form-use-style="Y" data-b24form-show-header="N" data-b24form-original-domain=""></div>
				</div>

			</div>
		</div>

</section>',
			),
		'17.1.copyright_with_social' =>
			array (
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '12500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-white js-animation animation-none">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 g-color-gray-dark-v3 g-font-cormorant-open-sans g-font-open-sans js-animation animation-none">
					&copy; 2018 All right reserved.
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
	),
);