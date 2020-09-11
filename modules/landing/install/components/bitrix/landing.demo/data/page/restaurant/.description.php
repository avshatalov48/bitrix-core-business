<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_RESTAURANT_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_RESTAURANT_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'restaurant',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/restaurant/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_RESTAURANT_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_RESTAURANT_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_RESTAURANT_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_RESTAURANT_DESCRIPTION')
		)
	),
	'items' => array (
		'0.menu_17_restaurant' =>
			array (
				'CODE' => '0.menu_17_restaurant',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-16"
		 data-header-fix-moment-exclude="g-py-16"
		 data-header-fix-moment-classes="u-shadow-v27 g-py-6">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/restaurant-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[41.1.big_image_slider_with_texts]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[10.2.two_cols_big_img_text_and_text_blocks_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">MENU</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[41.2.text_bolcks_slider_on_color_bg]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SPECIAL</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">OUR FOOD</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">OUR TEAM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BOOKING FORM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-10--lg">
							<a href="#block@block[33.23.form_2_themecolor_no_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
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
		'41.1.big_image_slider_with_texts' =>
			array (
				'CODE' => '41.1.big_image_slider_with_texts',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel" data-autoplay="true" data-infinite="true" data-speed="5000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">
		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-flex-centered g-min-height-100vh h-100 g-bg-pos-center g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img21.jpg);">
				<div class="text-center g-pos-rel container g-max-width-800 g-z-index-1 landing-block-node-card-container js-animation fadeInLeft">
					<span class="landing-block-node-card-icon-container g-color-white-opacity-0_7">
						<i class="landing-block-node-card-icon icon-food-026 g-font-size-60 g-mb-10"></i>
					</span>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-55 g-color-white g-mb-10 g-mb-15--md">
						Meat dishes</h2>
					<div class="landing-block-node-card-subtitle text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-40 g-mb-50--md">
						Only fresh meat
					</div>
					<img class="landing-block-node-card-photo g-width-130 g-height-130 g-brd-around g-brd-10 g-brd-white g-rounded-50x mx-auto g-mb-20 g-mb-25--md" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img21.jpg" alt="" />
					<p class="landing-block-node-card-name text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-10 g-mb-15--md">
						Juicy steak with special sauce</p>
					<div class="landing-block-node-card-text g-line-height-1_1 g-font-weight-100 g-color-white g-mb-10 g-mb-20--md">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p>
					</div>
					<div class="landing-block-node-card-price text-uppercase g-font-weight-700 g-font-size-16 g-color-primary mb-0">
						$9.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-flex-centered g-min-height-100vh h-100 g-bg-pos-center g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img22.jpg);">
				<div class="text-center g-pos-rel container g-max-width-800 g-z-index-1 landing-block-node-card-container js-animation fadeInLeft">
					<span class="landing-block-node-card-icon-container g-color-white-opacity-0_7">
						<i class="landing-block-node-card-icon icon-food-119 g-font-size-60 g-mb-10"></i>
					</span>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-55 g-color-white g-mb-10 g-mb-15--md">
						Fish dishes</h2>
					<div class="landing-block-node-card-subtitle text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-40 g-mb-50--md">
						Fresh from the ocean
					</div>
					<img class="landing-block-node-card-photo g-width-130 g-height-130 g-brd-around g-brd-10 g-brd-white g-rounded-50x mx-auto g-mb-20 g-mb-25--md" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img22.jpg" alt="" />
					<p class="landing-block-node-card-name text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-10 g-mb-15--md">
						Sushi rolls</p>
					<div class="landing-block-node-card-text g-line-height-1_1 g-font-weight-100 g-color-white g-mb-10 g-mb-20--md">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p>
					</div>
					<div class="landing-block-node-card-price text-uppercase g-font-weight-700 g-font-size-16 g-color-primary mb-0">
						$10.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-flex-centered g-min-height-100vh h-100 g-bg-pos-center g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img23.jpg);">
				<div class="text-center g-pos-rel container g-max-width-800 g-z-index-1 landing-block-node-card-container js-animation fadeInLeft">
					<span class="landing-block-node-card-icon-container g-color-white-opacity-0_7">
						<i class="landing-block-node-card-icon icon-food-187 g-font-size-60 g-mb-10"></i>
					</span>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-55 g-color-white g-mb-10 g-mb-15--md">
						Dishes for vegans</h2>
					<div class="landing-block-node-card-subtitle text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-40 g-mb-50--md">
						Green nature
					</div>
					<img class="landing-block-node-card-photo g-width-130 g-height-130 g-brd-around g-brd-10 g-brd-white g-rounded-50x mx-auto g-mb-20 g-mb-25--md" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img23.jpg" alt="" />
					<p class="landing-block-node-card-name text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-10 g-mb-15--md">
						Special Vegan tomato soup</p>
					<div class="landing-block-node-card-text g-line-height-1_1 g-font-weight-100 g-color-white g-mb-10 g-mb-20--md">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p>
					</div>
					<div class="landing-block-node-card-price text-uppercase g-font-weight-700 g-font-size-16 g-color-primary mb-0">
						$7.39
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-flex-centered g-min-height-100vh h-100 g-bg-pos-center g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img24.jpg);">
				<div class="text-center g-pos-rel container g-max-width-800 g-z-index-1 landing-block-node-card-container js-animation fadeInLeft">
					<span class="landing-block-node-card-icon-container g-color-white-opacity-0_7">
						<i class="landing-block-node-card-icon icon-food-228 g-font-size-60 g-mb-10"></i>
					</span>
					<h2 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-font-size-55 g-color-white g-mb-10 g-mb-15--md">
						Desserts</h2>
					<div class="landing-block-node-card-subtitle text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-40 g-mb-50--md">
						Sweet love
					</div>
					<img class="landing-block-node-card-photo g-width-130 g-height-130 g-brd-around g-brd-10 g-brd-white g-rounded-50x mx-auto g-mb-20 g-mb-25--md" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img24.jpg" alt="" />
					<p class="landing-block-node-card-name text-uppercase g-line-height-1 g-font-weight-700 g-font-size-20 g-color-white g-mb-10 g-mb-15--md">
						Fruity caramel</p>
					<div class="landing-block-node-card-text g-line-height-1_1 g-font-weight-100 g-color-white g-mb-10 g-mb-20--md">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.</p>
					</div>
					<div class="landing-block-node-card-price text-uppercase g-font-weight-700 g-font-size-16 g-color-primary mb-0">
						$2.50
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
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block row no-gutters">
        <div class="landing-block-node-texts col-lg-7 g-pt-100 g-pb-80 g-px-15 g-px-40--md g-bg-primary">
            <header class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-white">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white-opacity-0_8 text-left">ABOUT US</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10 text-left">WE ARE THE BEST</h2>
            </header>

			<div class="landing-block-node-text g-color-white-opacity-0_8 text-left"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p><p>Fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p></div>

            <div class="row align-items-stretch">

            </div>
        </div>

		<div class="landing-block-node-img col-lg-5 g-min-height-360 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img21.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
    </section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our MENU</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">TASTE THIS</h2>
            </div>
        </div>
    </section>',
			),
		'42.1.rest_menu' =>
			array (
				'CODE' => '42.1.rest_menu',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="container">
		<div class="tab-content g-pt-20">
			<div class="tab-pane fade show active">
				<!-- Products Block -->
				<div class="row landing-block-inner">
					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img25.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img26.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>
				
					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img27.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img28.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img29.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img30.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>
				
					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img31.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>

					<div class="landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp animated ">
						<!-- Article -->
						<article class="media">
							<!-- Article Image -->
							<a class="g-width-100" href="#">
								<img class="landing-block-node-card-photo g-width-100 img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img32.jpg" alt="" />
							</a>
							<!-- End Article Image -->

							<!-- Article Content -->
							<div class="media-body align-self-center g-pl-10">
								<div class="d-flex justify-content-between u-heading-v1-4 g-bg-main g-brd-gray-light-v4 g-mb-8">
									<h3 class="landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0">
										Croissants</h3>

									<div class="align-self-center g-pos-rel g-bg-main g-pl-15">
										<div class="landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12">$1.20</div>
									</div>
								</div>

								<div class="landing-block-node-card-text mb-0">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
								</div>
							</div>
							<!-- End Article Content -->
						</article>
						<!-- End Article -->
					</div>
				</div>
				<!-- End Products Block -->
			</div>
		</div>
	</div>
</section>',
			),
		'41.2.text_bolcks_slider_on_color_bg' =>
			array (
				'CODE' => '41.2.text_bolcks_slider_on_color_bg',
				'SORT' => '2500',
				'CONTENT' => '<section class="g-bg-primary g-py-100 g-pt-60">
	<div class="container">
		<section class="js-carousel" data-vertical="true" data-pagi-classes="u-carousel-indicators-v7 text-center">
			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">Chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-font-size-40 g-color-white g-bg-white--before g-pb-40 js-animation fadeInLeft animated">
								Green soup with croutons</h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn animated">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price g-font-weight-700 g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-special g-btn-px-m g-btn-white text-uppercase g-color-gray-dark-v2 rounded-0 g-py-25 js-animation fadeIn animated" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img1.png" alt="" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">Chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-font-size-40 g-color-white g-bg-white--before g-pb-40 js-animation fadeInLeft animated">
								Spaghetti</h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn animated">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price g-font-weight-700 g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-special g-btn-px-m g-btn-white text-uppercase g-color-gray-dark-v2 rounded-0 g-py-25 js-animation fadeIn animated" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img2.png" alt="" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">Chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-font-size-40 g-color-white g-bg-white--before g-pb-40 js-animation fadeInLeft animated">
								Green soup with croutons</h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn animated">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price g-font-weight-700 g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-special g-btn-px-m g-btn-white text-uppercase g-color-gray-dark-v2 rounded-0 g-py-25 js-animation fadeIn animated" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img3.png" alt="" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">Chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-font-size-40 g-color-white g-bg-white--before g-pb-40 js-animation fadeInLeft animated">
								Green soup with croutons</h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn animated">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price g-font-weight-700 g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-special g-btn-px-m g-btn-white text-uppercase g-color-gray-dark-v2 rounded-0 g-py-25 js-animation fadeIn animated" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img4.png" alt="" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-slide">
				<!-- Article -->
				<article class="row flex-items-middle text-center">
					<div class="col-lg-6 align-self-center g-mb-50">
						<div class="text-uppercase text-center u-heading-v5-3 u-heading-v5-color-primary u-heading-v5-rounded-50x g-mb-20">
							<h4 class="landing-block-node-card-subtitle g-font-weight-700 g-font-size-12 g-color-white-opacity-0_7 g-mb-15">Chef\'s special</h4>
							<h2 class="landing-block-node-card-title u-heading-v5__title g-line-height-1_2 g-font-weight-700 g-font-size-40 g-color-white g-bg-white--before g-pb-40 js-animation fadeInLeft animated">
								Green soup with croutons</h2>
						</div>

						<div class="landing-block-node-card-text g-px-70--md g-color-white-opacity-0_7 g-mb-25 js-animation fadeIn animated">
							<p>Sed feugiat porttitor nunc, non dignissim ipsum
								vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
						</div>

						<div class="landing-block-node-card-price g-font-weight-700 g-color-white g-font-size-26 g-mb-30">$14.00</div>
						<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-special g-btn-px-m g-btn-white text-uppercase g-color-gray-dark-v2 rounded-0 g-py-25 js-animation fadeIn animated" href="#">Book now
							</a>
						</div>
					</div>

					<div class="col-lg-6">
						<!-- Article Image -->
						<img class="landing-block-node-card-photo img-fluid g-width-360 g-width-auto--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/700x700/img5.png" alt="" />
						<!-- End Article Image -->
					</div>
				</article>
				<!-- End Article -->
			</div>
		</section>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@2' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our FOOD</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">TRY THIS YUMMY</h2>
            </div>
        </div>
    </section>',
			),
		'20.3.four_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-center">CHICKEN WITH SALAD</h3>
				<div class="landing-block-node-text text-center"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;">$8.50</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-center">bacon WITH TOMATOES</h3>
				<div class="landing-block-node-text text-center"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;">$11.50</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-center">RAspberry DESSERT</h3>
				<div class="landing-block-node-text text-center"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;">$12.00</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-center">Blueberry dessert</h3>
				<div class="landing-block-node-text text-center"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;">$9.50</span></p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@3' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">services</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">WHAT DO WE PROPOSE</h2>
            </div>
        </div>
    </section>',
			),
		'34.3.four_cols_countdown' =>
			array (
				'CODE' => '34.3.four_cols_countdown',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-main g-pt-20 g-pb-60">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-primary g-mb-15">
						<i class="landing-block-node-card-icon icon-food-039 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-18 g-color-black-opacity-0_9"><span style="font-weight: bold;">CATERING</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text mb-0 g-color-gray-light-v1"><p>Nunc ligula nulla, efficitur et eros ut, vulputate gravida leo. Vestibulum ante ipsum primis in faucibus orci luctus et.</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-primary g-mb-15">
						<i class="landing-block-node-card-icon icon-food-039 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-18 g-color-black-opacity-0_9"><span style="font-weight: bold;">WINE COLLECTION</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text mb-0 g-color-gray-light-v1"><p>Nunc ligula nulla, efficitur et eros ut, vulputate gravida leo. Vestibulum ante ipsum primis in faucibus orci luctus et.</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-primary g-mb-15">
						<i class="landing-block-node-card-icon icon-food-039 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-18 g-color-black-opacity-0_9"><span style="font-weight: bold;">CUSTOM ORDERS</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text mb-0 g-color-gray-light-v1"><p>Nunc ligula nulla, efficitur et eros ut, vulputate gravida leo. Vestibulum ante ipsum primis in faucibus orci luctus et.</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-primary g-mb-15">
						<i class="landing-block-node-card-icon icon-food-039 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-18 g-color-black-opacity-0_9"><span style="font-weight: bold;">WINE COLLECTION</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text mb-0 g-color-gray-light-v1"><p>Nunc ligula nulla, efficitur et eros ut, vulputate gravida leo. Vestibulum ante ipsum primis in faucibus orci luctus et.</p></div>
			</div>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@4' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-bg-primary js-animation fadeInUp animated g-pt-60 g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-white">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white-opacity-0_8">Food gallery</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-color-white">LOOK AT THESE DISHES</h2>
            </div>
        </div>
    </section>',
			),
		'32.6.img_grid_4cols_1_no_gutters' =>
			array (
				'CODE' => '32.6.img_grid_4cols_1_no_gutters',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img1.jpg"/>
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img2.jpg"/>
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img3.jpg"/>
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery"
						 class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img4.jpg"/>
				</div>
			</div>
		</div>

	</div>

</section>',
			),
		'32.6.img_grid_4cols_1_no_gutters@2' =>
			array (
				'CODE' => '32.6.img_grid_4cols_1_no_gutters',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img5.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img6.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img7.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/560x560/img8.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

	</div>

</section>',
			),
		'04.1.one_col_fix_with_title@5' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our team</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">meet the profesionals</h2>
            </div>
        </div>
    </section>',
			),
		'28.3.team' =>
			array (
				'CODE' => '28.3.team',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-pt-20 g-pb-60">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">ralf@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Chef</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">Ralf
						Smith</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">monica@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">CHEF</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">Monica
						Gaudy</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">julia@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">CHEF</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">Julia
						Exon</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg fadeIn animated ">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">jacob@company24.com</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">CHEF</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">Jacob
						Assange</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 g-pt-55 g-pb-30 js-animation fadeInUp animated">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">CUSTOMER CHOICE</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10">GOOD TASTE</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Etiam ultrices lacus ut ligula vestibulum, sit amet mattis nunc elementum. Nam arcu enim, euismod nec purus non, aliquam congue ante. Nulla faucibus enim mauris, fringilla mollis ligula sollicitudin mollis.</p></div>
        </div>

    </section>',
			),
		'20.3.four_cols_fix_img_title_text@2' =>
			array (
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20 g-bg-secondary">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-left">CHICKEN WITH SALAD</h3>
				<div class="landing-block-node-text text-left"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;font-size: 0.92857rem;">$8.50</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-left">BACON WITH TOMATOES</h3>
				<div class="landing-block-node-text text-left"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;font-size: 0.92857rem;">$9.50</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-left">RASPBERRY DESSERT</h3>
				<div class="landing-block-node-text text-left"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;font-size: 0.92857rem;">$11.50</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeInUp animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/810x600/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-14 text-left">BLUEBERRY DESSERT</h3>
				<div class="landing-block-node-text text-left"><p>Sed nec iaculis libero, vel ornare dui. Curabitur vitae nisl lorem.<br /><span style="color: rgb(233, 30, 99);font-weight: bold;font-size: 0.92857rem;">$10.50</span></p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-primary g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-white">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15 g-color-white-opacity-0_8">BOOKING FORM</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-white">RESERVATION</h2>
            </div>

			<div class="landing-block-node-text g-color-white-opacity-0_8"><p>Etiam ultrices lacus ut ligula vestibulum, sit amet mattis nunc elementum. Nam arcu enim, euismod nec purus non, aliquam congue ante. Nulla faucibus enim mauris, fringilla mollis ligula sollicitudin mollis.</p></div>
        </div>

    </section>',
			),
		'33.23.form_2_themecolor_no_text' =>
			array (
				'CODE' => '33.23.form_2_themecolor_no_text',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-py-80 g-bg-primary g-pt-20 g-pb-60">

	<div class="container">

		<div class="landing-block-form-styles" hidden="">
			<div class="g-bg-transparent h1 g-color-white g-brd-none g-pa-0" data-form-style-wrapper-padding="1" data-form-style-bg="1" data-form-style-bg-content="1" data-form-style-bg-block="1" data-form-style-header-font-size="1" data-form-style-main-font-weight="1" data-form-style-border-block="1">
			</div>

			<div class="g-bg-white g-color-primary g-brd-primary" data-form-style-main-bg="1" data-form-style-main-border-color="1" data-form-style-main-font-color-hover="1">
			</div>
			<div class="g-bg-primary-dark-v2 u-theme-restaurant-shadow-v1 g-brd-around g-color-gray-dark-v2 rounded-0" data-form-style-input-bg="1" data-form-style-input-box-shadow="1" data-form-style-input-select-bg="1" data-form-style-input-border="1" data-form-style-input-border-radius="1" data-form-style-button-font-color="1">
			</div>
			<div class="g-brd-around g-brd-gray-light-v2 g-brd-bottom g-bg-black-opacity-0_7" data-form-style-input-border-color="1" data-form-style-input-border-hover="1">
			</div>

			<p class="g-color-white-opacity-0_7" data-form-style-second-font-color="1" data-form-style-main-font-family="1" data-form-style-main-font-weight="1" data-form-style-header-text-font-size="1">
			</p>

			<h3 class="g-font-size-11 g-color-white" data-form-style-label-font-weight="1" data-form-style-label-font-size="1" data-form-style-main-font-color="1">
			</h3>
		</div>

		<div class="row">
			<div class="col-md-6 mx-auto">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v3" data-b24form="" data-b24form-use-style="Y" data-b24form-show-header="N" data-b24form-original-domain=""></div>
			</div>
		</div>
	</div>
</section>',
			),
		'17.copyright' =>
			array (
				'CODE' => '17.copyright',
				'SORT' => '9500',
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
	)
);