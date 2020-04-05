<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'app',
			'THEME_CODE_TYPO' => 'app',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/app/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
		),
	),
	'items' => array (
		'0.menu_03' =>
			array (
				'CODE' => '0.menu_03',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--fixed">
	<div class="u-header__section u-header__section--light u-shadow-v27 g-transition-0_3 g-py-12 g-py-20--md">
		<nav class="navbar navbar-expand-lg py-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/app-logo.png" width="131" alt="" />
				</a>
				<!-- End Logo -->

				<div id="navBar" class="collapse navbar-collapse">
					<!-- Navigation -->
					<div class="align-items-center flex-sm-row w-100">
						<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav g-flex-right--xs text-uppercase w-100 g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg">
							<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
								<a href="#block@block[19.5.cover_with_img_text_and_buttons]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
 </li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[44.2.four_columns_with_img_and_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BENEFITS</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">WHY WE</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[19.2.features_with_img]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">FEATURES</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[40.4.slider_blocks_with_img_and_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOW IT WORKS</a>
							</li>
							
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[19.3.text_blocks_faq]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">FAQ</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg g-mb-7 g-mb-0--lg">
								<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACT US</a>
							</li>
						</ul>
					</div>
					<!-- End Navigation -->

				</div>

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
		'19.5.cover_with_img_text_and_buttons' =>
			array (
				'CODE' => '19.5.cover_with_img_text_and_buttons',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-90 g-pb-0">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-lg-5 offset-lg-1 d-flex text-center text-md-left">
				<div class="align-self-center">
					<h2 class="landing-block-node-title text-uppercase g-line-height-1_3 g-font-size-36 g-mb-20 g-mb-30--lg">
						We created
						<br /><span style="font-weight: bold;">revolution in app</span></h2>
					<div class="g-mb-20 g-mb-35--lg">
						<div class="landing-block-node-text">
							Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
							Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis
							parturient montes, nascetur ridiculus mus.
						</div>
					</div>


					<div class="container-fluid px-0">
						<div class="row no-gutters">
							<div class="landing-block-node-card g-mb-12 g-mr-12">
								<a href="#" class="landing-block-node-card-button">
									<img class="landing-block-node-card-button-img g-height-42" src="https://cdn.bitrix24.site/bitrix/images/landing/app-store-badge.svg" alt="Download app from App Store" />
								</a>
							</div>

							<div class="landing-block-node-card g-mb-12 g-mr-12">
								<a href="#" class="landing-block-node-card-button">
									<img class="landing-block-node-card-button-img g-height-42" src="https://cdn.bitrix24.site/bitrix/images/landing/google-play-badge.svg" alt="Download app from Play Market" />
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-6 g-overflow-hidden align-self-end">
				<img class="landing-block-node-img img-fluid js-animation slideInUp" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup1.png" alt="" />
			</div>
		</div>
	</div>
</section>',
			),
		'44.2.four_columns_with_img_and_text' =>
			array (
				'CODE' => '44.2.four_columns_with_img_and_text',
				'SORT' => '1000',
				'CONTENT' => '<section class="g-pt-90 g-pb-90">
	<div class="container text-center g-max-width-750 g-mb-30">
		<div class="text-uppercase g-line-height-1_3 g-mb-20">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-mb-15">
				<span class="g-color-primary">01.</span> About app</h4>
			<h2 class="landing-block-node-title g-line-height-1_3 g-font-size-36 mb-0 js-animation fadeIn">We are
				<span style="font-weight: bold;">innovators</span></h2>
		</div>

		<div class="landing-block-node-text mb-0 js-animation fadeIn">
			<p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut,
				imperdiet a, venenatis vitae, justo.</p>
		</div>
	</div>

	<div class="container px-0">
		<div class="row no-gutters">
			<div class="landing-block-node-card col-sm-6 col-lg-3 g-bg-primary js-animation fadeInUp">
				<!-- Article -->
				<article class="u-block-hover">
					<figure class="g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img1.jpg" alt="" />
					</figure>

					<div class="g-color-white g-pa-40-30-30">
						<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-color-white g-mb-25">
							Innovative</h3>
						<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 mb-0">
							<p>Aliquam lorem ante, dapibus in,
								viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet.
								Quisque rutrum.
							</p>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-sm-6 col-lg-3 g-bg-darkpurple js-animation fadeInUp">
				<!-- Article -->
				<article class="u-block-hover">
					<figure class="g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img2.jpg" alt="" />
					</figure>

					<div class="g-color-white g-pa-40-30-30">
						<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-color-white g-mb-25">
							Easy</h3>
						<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 mb-0">
							<p>Aliquam lorem ante, dapibus in,
								viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet.
								Quisque rutrum.
							</p>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-sm-6 col-lg-3 g-bg-pink js-animation fadeInUp">
				<!-- Article -->
				<article class="u-block-hover">
					<figure class="g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img3.jpg" alt="" />
					</figure>

					<div class="g-color-white g-pa-40-30-30">
						<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-color-white g-mb-25">
							Modern</h3>
						<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 mb-0">
							<p>Aliquam lorem ante, dapibus in,
								viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet.
								Quisque rutrum.
							</p>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card col-sm-6 col-lg-3 g-bg-purple js-animation fadeInUp">
				<!-- Article -->
				<article class="u-block-hover">
					<figure class="g-overflow-hidden">
						<img class="landing-block-node-card-img img-fluid u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img4.jpg" alt="" />
					</figure>

					<div class="g-color-white g-pa-40-30-30">
						<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-color-white g-mb-25">
							Simple</h3>
						<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 mb-0">
							<p>Aliquam lorem ante, dapibus in,
								viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet.
								Quisque rutrum.
							</p>
						</div>
					</div>
				</article>
				<!-- End Article -->
			</div>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pb-20 g-bg-main g-pt-90 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-color-primary g-mb-15 g-font-size-11">02. <span style="color: rgb(33, 33, 33);">OUR BENEFITS</span></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-black g-mb-minus-10 g-font-size-36"><span style="font-weight: normal;">ONE TIME USED &amp;mdash;</span> USE FOREVER</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5">
            	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
			</div>
        </div>

    </section>',
			),
		'06.1features_3_cols' =>
			array (
				'CODE' => '06.1features_3_cols',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-30">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="row no-gutters">

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-search"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-color-black g-font-size-11"><p>LOOK FOR YOUR FAVORITE <br /><span style="font-size: 0.78571rem;">MUSIC EASILY</span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-sliders"></i>
                	</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-color-black g-font-size-11"><p>MANY DIFFERENT FILTERS FOR<br /><span style="font-size: 0.78571rem;">EASY MUSIC SEARCH</span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-cloud"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-color-black g-font-size-11"><p>ALL YOUR MUSIC ON OUR<br /><span style="font-size: 0.78571rem;">CLOUD HOSTING</span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			),
		'06.1features_3_cols@2' =>
			array (
				'CODE' => '06.1features_3_cols',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pb-80 g-pt-0">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="row no-gutters">

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-mobile"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-font-size-11 g-color-black"><p><span style="font-weight: bold;">OFFLINE PLAYLIST ON YOUR <br />P<span style="">HONE</span></span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-user"></i>
                	</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-font-size-11 g-color-black"><p><span style="font-weight: bold;">SHARE YOUR MUCIS AND<br /><span style="">PLAYLISTS WITH FRIENDS</span></span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-gray-light-v5 js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon fa fa-lock"></i>
					</span>
                        <h3 class="landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10"> </h3>
                        <div class="landing-block-node-element-text g-font-size-11 g-color-black"><p><span style="font-weight: bold;">HIGH LEVEL OF PROTECTION OF<br /><span style="">YOUR PERSONAL DATA</span></span></p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 g-color-gray-dark-v5">Fusce mauris eros, ullamcorper in gravida a, feugiat in mauris. Curabitur ac scelerisque nisi. Vivamus accumsan in purus et egestas.</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			),
		'01.big_with_text_3' =>
			array (
				'CODE' => '01.big_with_text_3',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-20 g-font-size-11"><span style="color: rgb(77, 182, 172);">03.</span> presentation</h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35">Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Nam placerat efficitur dui, quis mattis magna.</div>
		<div class="landing-block-node-button-container">
			<a href="//www.youtube.com/watch?v=q4d8g9Dn3ww" class="landing-block-node-button btn btn-xl u-btn-primary text-uppercase g-font-weight-700 g-font-size-12 g-rounded-50 g-py-15 g-px-40 g-mb-15" target="_popup" data-embed="//www.youtube.com/embed/-MUtzI7vK-o?autoplay=1&amp;controls=1&amp;loop=0&amp;rel=0&amp;start=0&amp;html5=1" data-url="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&amp;controls=1&amp;loop=0&amp;rel=0&amp;start=0&amp;html5=1&amp;v=q4d8g9Dn3ww">WATCH VIDEO</a>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-90 g-pb-0 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-letter-spacing-1 g-color-primary g-mb-20 g-font-size-11">04. <span style="color: rgb(33, 33, 33);">What is new</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-36"><span style="font-weight: normal;">WE&amp;#039;RE ALWAYS</span> IN TREND</h2>
            </div>
        </div>
    </section>',
			),
		'45.2.gallery_app_with_slider' =>
			array (
				'CODE' => '45.2.gallery_app_with_slider',
				'SORT' => '4000',
				'CONTENT' => '<div class="landing-block g-pt-80 g-pb-80">
	<div class="container">
		<div class="js-carousel js-gallery-cards landing-gallery-app-slider row g-pb-20" data-infinite="true" data-slides-show="3" data-slides-scroll="3" data-pagi-classes="text-center u-carousel-indicators-v1 g-absolute-centered--x g-bottom-0 g-wid">
			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img1.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300  js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img2.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img3.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img4.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img5.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img6.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img7.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img8.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img9.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img10.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img11.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center g-mb-30 g-min-width-300 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img12.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>',
			),
		'19.2.features_with_img' =>
			array (
				'CODE' => '19.2.features_with_img',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-90 g-pb-90">
	<div class="container">
		<div class="row">
			<div class="col-md-5 text-center g-overflow-hidden g-mb-50 g-mb-0--md">
				<img class="landing-block-node-img img-fluid js-animation slideInLeft" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup2.png" alt="" />
			</div>
			
			<div class="col-md-7 d-flex text-center text-md-left">
				<div class="align-self-center">
					<div class="text-uppercase g-mb-20">
						<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-mb-15">
							<span class="g-color-primary">05.</span> Awesome features</h4>
						<h2 class="landing-block-node-title g-line-height-1_3 g-font-size-36 mb-0">
							<span style="font-weight: bold;">Just try</span> and <span style="font-weight: bold;">use always</span>
						</h2>
					</div>

					<div class="landing-block-node-text g-mb-65">
						<p>Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent
							per conubia nostra, per inceptos himenaeos. Donec ullamcorper.</p>
					</div>

					<div class="landing-block-node-card media d-block d-md-flex text-center text-md-left g-mb-30">
						<div class="d-md-flex align-self-center g-mb-30 g-mb-0--md g-mr-30--md">
							<span class="landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-color-primary g-rounded-50x">
								<i class="landing-block-node-card-icon fa fa-flask"></i>
							</span>
						</div>

						<div class="media-body align-self-center">
							<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15">Awesome features</h4>
							<div class="landing-block-node-card-text g-font-size-default mb-0">
								<p>Vestibulum vulputate lobortis tortor non tempus. Proin
									in ex blandit velit imperdiet tincidunt sit amet at quam. Nam ac ultrices urna, sit
									amet fermentum magna. Nulla eu mattis augue.</p>
							</div>
						</div>
					</div>

					<div class="landing-block-node-card media d-block d-md-flex text-center text-md-left g-mb-30">
						<div class="d-md-flex align-self-center g-mb-30 g-mb-0--md g-mr-30--md">
							<span class="landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-color-primary g-rounded-50x">
								<i class="landing-block-node-card-icon fa fa-magic"></i>
							</span>
						</div>

						<div class="media-body align-self-center">
							<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15">Beautiful and modern
								design</h4>
							<div class="landing-block-node-card-text g-font-size-default mb-0">
								<p>Araesent blandit hendrerit justo sed egestas. Proin
									tincidunt purus in tortor cursus fermentum. Proin laoreet erat vitae dui blandit,
									vitae faucibus lacus auctor. Proin ornare sit amet arcu at aliquam.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'19.4.features_with_img' =>
			array (
				'CODE' => '19.4.features_with_img',
				'SORT' => '5000',
				'CONTENT' => '<section class="g-pt-90 g-pb-0">
	<div class="container">
		<div class="row">
			<div class="col-lg-7 g-mb-30">
				<div class="align-self-center">
					<div class="text-uppercase g-line-height-1_3 g-mb-20">
						<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-mb-15">
							<span class="g-color-primary">06.</span> Why our app</h4>
						<h2 class="landing-block-node-title g-line-height-1_3 g-font-size-36 mb-0">
							<span style="font-weight: bold;">Just try</span>
						</h2>
					</div>

					<div class="landing-block-node-text g-mb-65">
						<p>Praesent blandit hendrerit justo sed egestas. Proin tincidunt purus in tortor cursus
							fermentum. Proin laoreet erat vitae dui blandit, vitae faucibus lacus auctor. Proin ornare
							sit amet
							arcu at aliquam.</p>
					</div>

					<div class="u-accordion" aria-multiselectable="true">
						<!-- Card -->
						<div class="landing-block-node-card card rounded-0 g-bg-primary g-color-white g-brd-none">
							<div class="u-accordion__header g-pa-20">
								<h5 class="landing-block-node-card-title mb-0 text-uppercase g-font-size-default g-font-weight-700 g-color-white">
									Awesome features
								</h5>
							</div>

							<div>
								<div class="landing-block-node-card-text u-accordion__body g-font-size-default g-pa-0-20-20">
									Anim pariatur cliche
									reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf
									moon
									officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
									laborum
									eiusmod.
								</div>
							</div>
						</div>
						<!-- End Card -->

						<!-- Card -->
						<div class="landing-block-node-card card rounded-0 g-bg-primary-dark-v2 g-color-white g-brd-none">
							<div class="u-accordion__header g-pa-20">
								<h5 class="landing-block-node-card-title mb-0 text-uppercase g-font-size-default g-font-weight-700 g-color-white">
									Modern and creative design
								</h5>
							</div>

							<div>
								<div class="landing-block-node-card-text u-accordion__body g-font-size-default g-pa-0-20-20">
									Anim pariatur cliche
									reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf
									moon
									officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
									laborum
									eiusmod.
								</div>
							</div>
						</div>
						<!-- End Card -->

						<!-- Card -->
						<div class="landing-block-node-card card rounded-0 g-bg-primary g-color-white g-brd-none">
							<div class="u-accordion__header g-pa-20">
								<h5 class="landing-block-node-card-title mb-0 text-uppercase g-font-size-default g-font-weight-700 g-color-white">
									Regular updates
								</h5>
							</div>

							<div>
								<div class="landing-block-node-card-text u-accordion__body g-font-size-default g-pa-0-20-20">
									Anim pariatur cliche
									reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf
									moon
									officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
									laborum
									eiusmod.
								</div>
							</div>
						</div>
						<!-- End Card -->

						<!-- Card -->
						
						<!-- End Card -->
					</div>
				</div>
			</div>

			<div class="col-lg-5 text-center g-overflow-hidden align-self-end">
				<img class="landing-block-node-img img-fluid js-animation slideInUp" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup3.png" alt="" />
			</div>
		</div>
	</div>
</section>',
			),
		'40.4.slider_blocks_with_img_and_text' =>
			array (
				'CODE' => '40.4.slider_blocks_with_img_and_text',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-bg-primary g-pt-90 g-pb-90">
	<div class="container text-center g-max-width-750 g-mb-65">
		<div class="text-uppercase g-line-height-1_3 g-mb-20">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-color-white g-mb-15">07. How it
				works</h4>
			<h2 class="landing-block-node-title g-line-height-1_3 g-font-size-36 g-color-white mb-0 js-animation fadeInLeft">One time used &amp;mdash;
				<span style="font-weight: bold;">use forever</span>
			</h2>
		</div>

		<div class="landing-block-node-text g-color-white mb-0 js-animation fadeInLeft">
			<p>Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per
				conubia nostra, per inceptos himenaeos.</p>
		</div>
	</div>

	<div class="container">
		<!-- Carousel -->
		<div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-pos-abs g-top-35x g-width-45 g-height-45 g-color-primary g-bg-white g-rounded-50x g-transition-0_2 g-transition--ease-in" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">
			<div class="landing-block-node-card js-slide">
				<div class="container text-center g-max-width-750">
					<div class="g-mb-20">
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup5.png" alt="" />
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup6.png" alt="" />
					</div>

					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-white g-mb-15">
						User Manual</h4>
					<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 g-mb-30 js-animation fadeInLeft">
						<p>Sed feugiat porttitor nunc, non
							dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-lg text-uppercase u-btn-white g-font-weight-700 g-font-size-12 g-rounded-10 g-px-25 g-py-12 mb-0 js-animation fadeInLeft" href="#">Learn more</a>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card js-slide">
				<div class="container text-center g-max-width-750">
					<div class="g-mb-20">
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup5.png" alt="" />
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup6.png" alt="" />
					</div>

					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-white g-mb-15">
						Made with love</h4>
					<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 g-mb-30 js-animation fadeInLeft">
						<p>Sed feugiat porttitor nunc, non
							dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel
							faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros,
							fringilla
							porttitor lorem eros vel odio.</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-lg text-uppercase u-btn-white g-font-weight-700 g-font-size-12 g-rounded-10 g-px-25 g-py-12 mb-0 js-animation fadeInLeft" href="#">Learn more</a>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card js-slide">
				<div class="container text-center g-max-width-750">
					<div class="g-mb-20">
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup5.png" alt="" />
						<img class="landing-block-node-card-img d-inline-block g-mw-45" src="https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup6.png" alt="" />
					</div>

					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-white g-mb-15">
						Usability and progresion</h4>
					<div class="landing-block-node-card-text g-font-size-default g-color-white-opacity-0_8 g-mb-30 js-animation fadeInLeft">
						<p>Sed feugiat porttitor nunc, non
							dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel
							faucibus ante. Nunc ullamcorper.</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-lg text-uppercase u-btn-white g-font-weight-700 g-font-size-12 g-rounded-10 g-px-25 g-py-12 mb-0 js-animation fadeInLeft" href="#">Learn more</a>
					</div>
				</div>
			</div>
		</div>
		<!-- End Carousel -->
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@2' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-90 g-pb-0 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-letter-spacing-1 g-color-primary g-mb-20 g-font-size-11">08. <span style="color: rgb(33, 33, 33);">app screens</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-36"><span style="font-weight: normal;">LOOK</span> HOW IT WORKS</h2>
            </div>
        </div>
    </section>',
			),
		'45.1.gallery_app_wo_slider' =>
			array (
				'CODE' => '45.1.gallery_app_wo_slider',
				'SORT' => '6500',
				'CONTENT' => '<div class="landing-block g-pt-80 g-pb-80">
	<div class="container">
		<div class="js-gallery-cards row">
			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img1.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img2.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img3.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img4.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img5.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img6.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img7.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img8.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img9.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img10.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img11.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card text-center col-lg-3 col-md-4 col-sm-6 g-mb-30 js-animation slideInUp">
				<div class="g-pos-rel g-parent d-inline-block h-100">
					<img data-fancybox="gallery" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x481/img12.jpg" alt="" class="landing-block-node-card-img g-object-fit-cover h-100 w-100" />
					<div class="landing-block-node-card-title-container w-100 g-pos-abs g-bottom-0 g-left-0 g-flex-middle g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
						<h3 class="landing-block-node-card-title h3 g-color-white">Title</h3>
						<div class="landing-block-node-card-subtitle g-color-white">Text</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>',
			),
		'19.3.text_blocks_faq' =>
			array (
				'CODE' => '19.3.text_blocks_faq',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-90 g-pb-90">
	<div class="container">
		<div class="text-uppercase g-line-height-1_3 g-mb-20">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-mb-15"><span style="color: rgb(77, 182, 172);">09.</span> FAQ</h4>
			<h2 class="landing-block-node-title g-font-size-36 mb-0 js-animation fadeIn">Have any <span style="font-weight: bold;">problems?</span>
			</h2>
		</div>

		<div class="landing-block-node-text g-mb-65 js-animation fadeIn">
			<p>Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per
				conubia
				nostra, per inceptos himenaeos.</p>
		</div>

		<!-- Tab panes -->
		<div>
			<div class="fade show active">
				<div class="landing-block-node-card g-brd-bottom g-brd-gray-light-v5 g-py-40 js-animation slideInUp">
					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-mb-10">Integer ut sollicitudin justo</h4>
					<p class="landing-block-node-card-text g-font-size-default g-mb-30">Vivamus imperdiet condimentum diam, eget placerat felis
						consectetur id. Donec eget orci metus, ac adipiscing nunc. Pellentesque fermentum ivamus
						imperdiet condimentum diam, eget placerat felis consectetur id. Donec eget orci metus, ac
						adipiscing nunc.</p>
					<a class="landing-block-node-card-link text-uppercase g-font-size-11 g-font-weight-700" href="#">Read more</a>
				</div>

				<div class="landing-block-node-card g-brd-bottom g-brd-gray-light-v5 g-py-40 js-animation slideInUp">
					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-mb-10">Vestibulum ante ipsum primis in faucibus
						orci luctus et ultrices</h4>
					<p class="landing-block-node-card-text g-font-size-default g-mb-30">Vivamus imperdiet condimentum diam, eget placerat felis
						consectetur id. Donec eget orci metus, ac adipiscing nunc. Pellentesque fermentum ivamus
						imperdiet condimentum diam, eget placerat felis consectetur id. Donec eget orci metus, ac
						adipiscing nunc.</p>
					<a class="landing-block-node-card-link text-uppercase g-font-size-11 g-font-weight-700" href="#">Read more</a>
				</div>

				<div class="landing-block-node-card g-brd-bottom g-brd-gray-light-v5 g-py-40 js-animation slideInUp">
					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-mb-10">Maecenas ac nulla vehicula risus pulvinar
						feugiat ullamcorper sit amet mi</h4>
					<p class="landing-block-node-card-text g-font-size-default g-mb-30">Vivamus imperdiet condimentum diam, eget placerat felis
						consectetur id. Donec eget orci metus, ac adipiscing nunc. Pellentesque fermentum ivamus
						imperdiet condimentum diam, eget placerat felis consectetur id. Donec eget orci metus, ac
						adipiscing nunc.</p>
					<a class="landing-block-node-card-link text-uppercase g-font-size-11 g-font-weight-700" href="#">Read more</a>
				</div>

				<div class="landing-block-node-card g-brd-bottom g-brd-gray-light-v5 g-py-40 js-animation slideInUp">
					<h4 class="landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-mb-10">Praesent blandit hendrerit justo sed
						egestas</h4>
					<p class="landing-block-node-card-text g-font-size-default g-mb-30">Vivamus imperdiet condimentum diam, eget placerat felis
						consectetur id. Donec eget orci metus, ac adipiscing nunc. Pellentesque fermentum ivamus
						imperdiet condimentum diam, eget placerat felis consectetur id. Donec eget orci metus, ac
						adipiscing nunc.</p>
					<a class="landing-block-node-card-link text-uppercase g-font-size-11 g-font-weight-700" href="#">Read more</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-bg-primary js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-color-white g-font-size-11"><span style="font-weight: bold;">10. CONTACT US</span></h2>
                <div class="landing-block-node-text g-color-white g-font-size-36"><p>ANSWERS TO <span style="font-weight: bold;">YOUR QUESTIONS</span></p></div>
            </div>
        </div>

    </section>',
			),
		'33.23.form_2_themecolor_no_text' =>
			array (
				'CODE' => '33.23.form_2_themecolor_no_text',
				'SORT' => '8000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-bg-primary g-pt-0 g-pb-0">

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
	),
);