<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_WEDDING_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_WEDDING_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'wedding',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wedding/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_WEDDING_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_WEDDING_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_WEDDING_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_WEDDING_DESCRIPTION'),
		),
	),
	'items' => array (
		'0.menu_20_wedding' =>
			array (
				'CODE' => '0.menu_20_wedding',
				'SORT' => '-100',
				'CONTENT' => '

<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-25"
		 data-header-fix-moment-exclude="g-py-25"
		 data-header-fix-moment-classes="u-shadow-v27 g-py-15">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0" target="_self">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/wedding-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Home</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Presentation</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[46.3.cover_with_blocks_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">News</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[47.1.title_with_icon@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Booking
								form</a>
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
		'01.big_with_text_blocks' =>
			array (
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="10000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img23.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title g-font-weight-700 g-mb-20 g-text-transform-none g-font-size-120 g-color-primary"><span style="font-style: italic;"><span style="color: rgb(245, 245, 245);">Tom</span> <span style="">&amp;</span> <span style="color: rgb(245, 245, 245);">Anna</span></span></h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"> </div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-10" href="#" tabindex="-1" target="_self">View more</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		
		

		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img24.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title g-font-weight-700 g-mb-20 g-text-transform-none g-font-size-120 g-color-primary"><span style="font-style: italic;"><span style="color: rgb(245, 245, 245);">Tom</span> <span style="">&amp;</span> <span style="color: rgb(245, 245, 245);">Anna</span></span></h2>
					<div class="landing-block-node-card-text g-max-width-645 g-color-white-opacity-0_9 mx-auto g-mb-35"> </div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-10" href="#" tabindex="0" target="_self">View more</a>
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
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp animated">About
				<span class="g-color-primary">Us</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp animated">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			),
		'44.6.two_columns_with_peoples' =>
			array (
				'CODE' => '44.6.two_columns_with_peoples',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn animated ">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/300x300/img1.jpg" alt="" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 font-italic g-font-weight-600 g-mb-20">
						Samantha
						Fox</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30">
						Stylist, Co-Founder
					</div>
					<div class="landing-block-node-card-text g-mb-40">
						<p>
							Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla.
							Nunc vitae maximus ipsum. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc
							diam tellus.
						</p>
					</div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation col-md-6 col-lg-6 g-pt-30 g-mb-50 g-mb-0--md fadeIn animated ">
				<!-- Article -->
				<article class="text-center">
					<!-- Article Image -->
					<div class="g-height-200 d-flex align-items-center justify-content-center">
						<img class="landing-block-node-card-photo g-max-width-200 g-rounded-50x g-mb-20 g-max-height-200" src="https://cdn.bitrix24.site/bitrix/images/landing/business/300x300/img2.jpg" alt="" />
					</div>
					<!-- End Article Image -->

					<!-- Article Title -->
					<h4 class="landing-block-node-card-name g-line-height-1 g-font-size-40 font-italic g-font-weight-600 g-mb-20">
						Dorian
						Black</h4>
					<!-- End Article Title -->
					<!-- Article Body -->
					<div class="landing-block-node-card-post text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-30">
						Photography, Co-Founder
					</div>
					<div class="landing-block-node-card-text g-mb-40">
						<p>
							Etiam dolor tortor, egestas a libero eget, sollicitudin maximus nulla. Nunc vitae maximus
							ipsum.
							Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.
						</p>
					</div>

					<!-- End Article Body -->
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			),
		'34.3.four_cols_countdown' =>
			array (
				'CODE' => '34.3.four_cols_countdown',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block landing-semantic-background-color g-pt-60 g-pb-60 g-bg-primary">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg">
					<span class="landing-block-node-card-icon-container m-auto landing-semantic-color-primary g-color-white-opacity-0_6 u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-badge"></i>
					</span>
				<h3 class="landing-block-node-card-number landing-semantic-subtitle-big g-font-size-35 g-color-white mb-0">10</h3>
				<div class="landing-block-node-card-number-title landing-semantic-subtitle-medium text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20">
					Years
				</div>
				<div class="landing-block-node-card-text landing-semantic-text-small g-color-white-opacity-0_6 mb-0">
					<p>Sed feugiat porttitor nunc Etiam
						gravida ex justo ac rhoncus purus tristique ut.
					</p>
				</div>
			</div>
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg">
					<span class="landing-block-node-card-icon-container m-auto landing-semantic-color-primary g-color-white-opacity-0_6 u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-picture"></i>
					</span>
				<h3 class="landing-block-node-card-number landing-semantic-subtitle-big g-font-size-35 g-color-white mb-0">99000</h3>
				<div class="landing-block-node-card-number-title landing-semantic-subtitle-medium text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20">
					Photos
				</div>
				<div class="landing-block-node-card-text landing-semantic-text-small g-color-white-opacity-0_6 mb-0">
					<p>Ivitae blandit massa luctus fermentum
						lorem quis elit maximus, vitae
					</p>
				</div>
			</div>
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--md">
					<span class="landing-block-node-card-icon-container m-auto landing-semantic-color-primary g-color-white-opacity-0_6 u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-camera"></i>
					</span>
				<h3 class="landing-block-node-card-number landing-semantic-subtitle-big g-font-size-35 g-color-white mb-0">20</h3>
				<div class="landing-block-node-card-number-title landing-semantic-subtitle-medium text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20">
					Photographers
				</div>
				<div class="landing-block-node-card-text landing-semantic-text-small g-color-white-opacity-0_6 mb-0">
					<p>Curabitur eget tortor sed urna
						faucibus iaculis id et nulla sed fringilla quam
					</p>
				</div>
			</div>
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center">
					<span class="landing-block-node-card-icon-container m-auto landing-semantic-color-primary g-color-white-opacity-0_6 u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-heart"></i>
					</span>
				<h3 class="landing-block-node-card-number landing-semantic-subtitle-big g-font-size-35 g-color-white mb-0">238</h3>
				<div class="landing-block-node-card-number-title landing-semantic-subtitle-medium text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20">
					Weddings
				</div>
				<div class="landing-block-node-card-text landing-semantic-text-small g-color-white-opacity-0_6 mb-0">
					<p>Duis dui turpis, consectetur non
						ultrices vitae, lacinia aliquam sapien
					</p>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'01.big_with_text_3' =>
			array (
				'CODE' => '01.big_with_text_3',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img22.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown animated">
		<h2 class="landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-color-primary g-text-transform-none g-font-size-80"><span style="font-style: italic;"><span style="color: rgb(245, 245, 245);">The Power</span> Of Love</span></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">
			Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.
		</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-mb-15 g-rounded-10" target="_self">View our presentation</a>
		</div>
	</div>
</section>',
			),
		'47.1.title_with_icon@2' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp animated">Our <span style="color: rgb(214, 87, 121);">Photos</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp animated">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			),
		'32.6.img_grid_4cols_1' =>
			array (
				'CODE' => '32.6.img_grid_4cols_1',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-20">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img1.jpg" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-style: italic;font-weight: bold;">Susan and Carl</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img2.jpg" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Debra and Sam</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img3.jpg" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Derek and Hellen</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img4.jpg" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">John and Rebecca</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

</section>',
			),
		'32.6.img_grid_4cols_1@2' =>
			array (
				'CODE' => '32.6.img_grid_4cols_1',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">

	<div class="container">
		<div class="row js-gallery-cards">

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Denis and Jasmin</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--md">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img6.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Peter and Lia</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-15 g-pb-0--sm">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Frank and Rachel</span></h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-md-3">
				<div class="h-100 g-pb-0">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-left js-animation fadeInLeft animated">
						<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<div class="landing-block-node-img-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-top-0 g-flex-middle g-bg-black-opacity-0_5 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="h-100 g-flex-centered flex-column g-brd-white-opacity-0_2 text-uppercase">
								<h3 class="landing-block-node-img-title text-center g-color-white g-line-height-1_4 g-text-transform-none g-letter-spacing-1 g-font-size-32"><span style="font-weight: bold;font-style: italic;">Amer and Hanna</span></h3>
							</div>
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
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-60 js-animation fadeInUp animated g-pb-20 g-bg-primary">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-color-white g-font-size-60 g-line-height-2 g-letter-spacing-1"><span style="font-weight: bold;font-style: italic;">What We Do</span></h2>
                <div class="landing-block-node-text g-color-white-opacity-0_8"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p></div>
            </div>
        </div>

    </section>',
			),
		'22.1.four_cols_fix_bigbgimg' =>
			array (
				'CODE' => '22.1.four_cols_fix_bigbgimg',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-primary-opacity-0_9--after g-pt-30 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container u-bg-overlay__inner">
		<div class="row landing-block-inner">

			<div class="landing-block-card d-flex flex-column col-md-3 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
				<div class="u-heading-v8-1 g-mb-20">
					<h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7"> </h6>
					<h6 class="landing-block-node-title u-heading-v8__title g-font-weight-700 g-color-white mb-0 text-left g-text-transform-none g-font-size-30"><span style="font-style: italic;">Professional </span><p><span style="font-style: italic;">Photos</span></p></h6>
				</div>
				<img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/315x435/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				<div class="g-mb-40 landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 text-left"><p>Nunc vitae maximus. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-white g-color-primary g-rounded-25" href="#" target="_self">View More</a>
				</div>
			</div>

			<div class="landing-block-card d-flex flex-column col-md-3 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
				<div class="u-heading-v8-1 g-mb-20">
					<h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7"> </h6>
					<h6 class="landing-block-node-title u-heading-v8__title g-font-weight-700 g-color-white mb-0 text-left g-text-transform-none g-font-size-30"><span style="font-style: italic;">Wedding </span><p><span style="font-style: italic;">Makeup</span></p></h6>
				</div>
				<img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/315x435/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				<div class="g-mb-40 landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 text-left"><p>Nunc vitae maximus. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-white g-color-primary g-rounded-25" href="#" target="_self">View More</a>
				</div>
			</div>

			<div class="landing-block-card d-flex flex-column col-md-3 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
				<div class="u-heading-v8-1 g-mb-20">
					<h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7"> </h6>
					<h6 class="landing-block-node-title u-heading-v8__title g-font-weight-700 g-color-white mb-0 text-left g-text-transform-none g-font-size-30"><span style="font-style: italic;">Bridal </span><p><span style="font-style: italic;">Bouquet</span></p></h6>
				</div>
				<img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/315x435/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				<div class="g-mb-40 landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 text-left"><p>Nunc vitae maximus. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-white g-color-primary g-rounded-25" href="#" target="_self">View More</a>
				</div>
			</div>

			<div class="landing-block-card d-flex flex-column col-md-3 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
				<div class="u-heading-v8-1 g-mb-20">
					<h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7"> </h6>
					<h6 class="landing-block-node-title u-heading-v8__title g-font-weight-700 g-color-white mb-0 text-left g-text-transform-none g-font-size-30"><span style="font-style: italic;">Creative </span><p><span style="font-style: italic;">Proposals</span></p></h6>
				</div>
				<img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/315x435/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				<div class="g-mb-40 landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 text-left"><p>Nunc vitae maximus. Vestibulum sodales nisi massa, vitae blandit massa luctus id. Nunc diam tellus.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-m g-btn-white g-color-primary g-rounded-25" href="#" target="_self">View More</a>
				</div>
			</div>

		</div>
	</div>
</section>',
			),
		'46.3.cover_with_blocks_slider' =>
			array (
				'CODE' => '46.3.cover_with_blocks_slider',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-100 g-pb-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img21.jpg);">
	<div class="container u-bg-overlay__inner">
		<div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--y--md g-top-100x g-top-50x--md g-width-50 g-height-50 g-rounded-50x g-font-size-12 g-color-white g-bg-white-opacity-0_4 g-bg-white-opacity-0_7--hover g-mt-30 g-mt-0--md" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">
			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x315/img1.jpg" alt="" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig">
							Monica and Eddie</h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">
							March
							15, 2017
						</div>
						<blockquote class="landing-block-node-card-text g-mb-40 js-animation fadeInRightBig">
							<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>
						</blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-rounded-20 g-pa-15 js-animation fadeInRightBig" href="#">View Photos</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Testimonial Block -->
				<div class="text-center g-max-width-600 mx-auto">
					<img class="landing-block-node-card-photo w-100 img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x315/img2.jpg" alt="" />

					<div class="landing-block-node-card-text-container g-bg-white g-pa-40">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-mb-20 js-animation fadeInRightBig">
							Sofia and Carl</h4>
						<div class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25">
							November
							01, 2017
						</div>
						<blockquote class="landing-block-node-card-text g-mb-40 js-animation fadeInRightBig">
							<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>
						</blockquote>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-rounded-20 g-pa-15 js-animation fadeInRightBig" href="#">View Photos</a>
						</div>
					</div>
				</div>
				<!-- End Testimonial Block-->
			</div>
		</div>
	</div>
</section>',
			),
		'47.1.title_with_icon@3' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp animated">Best <span style="color: rgb(214, 87, 121);">Offers</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp animated">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			),
		'44.7.three_columns_with_img_and_price' =>
			array (
				'CODE' => '44.7.three_columns_with_img_and_price',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 ">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">
							Love Story</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img1.jpg" alt="" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22">
								$350.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-20 g-py-15" href="#">Order Now</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 ">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">
							Wedding Photos</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img2.jpg" alt="" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22">
								$600.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-20 g-py-15" href="#">Order Now</a>
						</div>
					</div>
					<!-- End Article Content -->
				
				<!-- End Article -->
			</article></div>

			<div class="landing-block-node-card col-md-6 col-lg-4 g-mb-30 ">
				<!-- Article -->
				<article class="h-100 text-center u-block-hover u-block-hover__additional--jump g-brd-around g-bg-gray-light-v5 g-brd-gray-light-v4 d-flex flex-column">
					<!-- Article Header -->
					<header class="landing-block-node-card-container-top g-bg-primary g-pa-20">
						<h4 class="landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 g-color-white mb-0">
							Makeup</h4>
						<div class="landing-block-node-card-subtitle g-color-white-opacity-0_6">
							Fringilla
							porttitor
						</div>
					</header>
					<!-- End Article Header -->

					<!-- Article Image -->
					<img class="landing-block-node-card-img g-height-230 w-100 g-object-fit-cover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x380/img3.jpg" alt="" />
					<!-- End Article Image -->

					<!-- Article Content -->
					<div class="landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column">
						<div class="g-mb-15">
							<div class="landing-block-node-card-price-subtitle">
								From
							</div>
							<div class="landing-block-node-card-price g-font-weight-700 g-color-primary g-mt-10 g-font-size-22">
								$200.00
							</div>
						</div>

						<div class="landing-block-node-card-text g-mb-40">
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						</div>
						<div class="landing-block-node-card-button-container mt-auto">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-rounded-20 g-py-15" href="#">Order Now</a>
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
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-bg-secondary g-pt-60 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp animated">Latest <span style="color: rgb(214, 87, 121);">Posts</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp animated">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			),
		'20.3.four_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-bg-secondary g-pt-20 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x400/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-montserrat g-font-size-13">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text g-font-size-12"><p>Curabitur eget tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla quam.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x400/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-montserrat g-font-size-13">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text g-font-size-12"><p>Curabitur eget tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla quam.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x400/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-montserrat g-font-size-13">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text g-font-size-12"><p>Curabitur eget tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla quam.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/540x400/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-montserrat g-font-size-13">Mauris tellus magna, pretium</h3>
				<div class="landing-block-node-text g-font-size-12"><p>Curabitur eget tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla quam.</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'13.2.one_col_fix_button' =>
			array (
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block text-center g-pt-20 g-bg-secondary g-pb-60">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-10" href="#" target="_self">View more</a>
        </div>
    </section>',
			),
		'47.1.title_with_icon@5' =>
			array (
				'CODE' => '47.1.title_with_icon',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-pb-20">
	<div class="container text-center g-max-width-800">
		<div class="u-heading-v7-3 g-mb-30">
			<h2 class="landing-block-node-title u-heading-v7__title g-font-size-60 font-italic g-font-weight-600 g-mb-20 js-animation fadeInUp animated">Planning a <span style="color: rgb(214, 87, 121);">Wedding?</span></h2>

			<div class="landing-block-node-icon-container u-heading-v7-divider g-color-primary g-brd-gray-light-v4">
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-11"></i>
				<i class="landing-block-node-icon fa fa-heart g-font-size-8"></i>
			</div>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeInUp animated">
			<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>
		</div>
	</div>
</section>',
			),
		'33.1.form_1_transparent_black_left_text' =>
			array (
				'CODE' => '33.1.form_1_transparent_black_left_text',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-pos-rel g-pt-20 g-pb-60 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-cover g-bg-primary-opacity-0_4--after"
	style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/975x650/img1.jpg);">

	<div class="container g-z-index-1 g-pos-rel">
		<div class="row align-items-center">

			<div class="col-md-4 g-mb-60">
				<h2 class="landing-block-node-main-title landing-semantic-title-medium js-animation fadeInUp h1 g-color-white mb-4 font-italic">Contact Us</h2>

				<div class="landing-block-node-text landing-semantic-text-medium js-animation fadeInUp g-line-height-1_5 text-left g-mb-40 g-color-white-opacity-0_6">
					<p>
						Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor.
						Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum,
						enim orci viverra eros, fringilla porttitor lorem eros vel odio.
					</p>
				</div>

				<h4 class="landing-block-node-title landing-semantic-subtitle-medium g-color-white mb-4">Contact Info</h4>

				<div class="landing-block-node-card-contact-container">
					<!-- Icon Block -->
					<div class="landing-block-node-card-contact" data-card-preset="text">
						<div class="media align-items-center mb-4">
							<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
									<i class="landing-block-card-contact-icon icon-hotel-restaurant-235 u-line-icon-pro"></i>
								</span>
							</div>
							<div class="media-body">
								<div class="landing-block-node-contact-text landing-semantic-text-medium g-color-white-opacity-0_6 mb-0">5B Streat, City
									50987 New Town US
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
								<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link landing-semantic-link-medium g-color-white-opacity-0_6">#crmPhoneTitle1</a>
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
							<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link landing-semantic-link-medium g-color-white-opacity-0_6">#crmPhoneTitle1</a>
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
							<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link landing-semantic-link-medium g-color-white-opacity-0_6">#crmPhoneTitle1</a>
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
							<a href="mailto:#crmEmail1" class="landing-block-card-linkcontact-link landing-semantic-link-medium g-color-white-opacity-0_6">#crmEmailTitle1</a>
						</div>
					</div>
					<!-- End Icon Block -->
				</div>
			</div>

			<div class="col-md-8">
				<div class="bitrix24forms landing-block-node-form js-animation fadeInUp g-brd-none g-brd-around--sm g-brd-white-opacity-0_6 g-px-0 g-px-20--sm g-px-45--lg g-py-0 g-py-30--sm g-py-60--lg u-form-alert-v1"
					data-b24form-use-style="Y"
					data-b24form-design=\'{"dark":true,"style":"modern","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#fff","background":"#00000000","fieldBorder":"#fff","fieldBackground":"#ffffff00","fieldFocusBackground":"#ffffff00"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'
					data-b24form-embed
				>
				</div>
			</div>

		</div>
	</div>
</section>',
			),
		'17.1.copyright_with_social' =>
			array (
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '10000',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-white js-animation animation-none">
	<div class="text-center text-md-left g-py-40 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 js-animation animation-none">
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
			),
	),
);