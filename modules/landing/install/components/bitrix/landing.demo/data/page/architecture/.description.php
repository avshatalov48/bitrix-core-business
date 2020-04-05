<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'architecture',
			'THEME_CODE_TYPO' => 'architecture',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/architecture/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_ARCHITECTURE_DESCRIPTION')
		)
	),
	'items' => array(
		'0.menu_04' =>
			array (
				'CODE' => '0.menu_04',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header landing-ui-pattern-transparent u-header--floating">
	<div class="u-header__section g-bg-black-opacity-0_4 g-transition-0_3 g-py-8 g-py-17--md" data-header-fix-moment-exclude="g-bg-black-opacity-0_4 g-py-17--md" data-header-fix-moment-classes="u-header__section--light u-theme-architecture-shadow-v1 g-bg-white g-py-10--md">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<a href="#" class="landing-block-node-menu-logo-link-small g-hidden-lg-up navbar-brand mr-0 p-0">
					<img class="landing-block-node-menu-logo-small d-block g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-light.png"
						 alt=""
						 data-header-fix-moment-exclude="d-block"
						 data-header-fix-moment-classes="d-none">

					<img class="landing-block-node-menu-logo-small-2 d-none g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-dark.png" alt=""
						 data-header-fix-moment-exclude="d-none"
						 data-header-fix-moment-classes="d-block">
				</a>

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav align-items-lg-center text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg mx-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[01.big_with_text_3_1]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.3.one_col_fix_with_title_and_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.1.one_col_fix_with_title@2]" class="landing-block-node-menu-list-item-link nav-link  g-color-white p-0" target="_self">PROJECTS</a>
						</li>

						<!-- Logo -->
						<li class="landing-block-node-menu-list-item landing-block-node-menu-list-logo g-hidden-lg-down nav-logo-item g-mx-15--lg" data-card-preset="logo">
							<a href="#team" class="landing-block-node-menu-logo-link navbar-brand mr-0">
								<img class="landing-block-node-menu-logo d-block g-max-width-180"
									 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-light.png"
									 alt=""
									 data-header-fix-moment-exclude="d-block"
									 data-header-fix-moment-classes="d-none">

								<img class="landing-block-node-menu-logo-2 d-none g-max-width-180"
									 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-dark.png"
									 alt=""
									 data-header-fix-moment-exclude="d-none"
									 data-header-fix-moment-classes="d-block">
							</a>
						</li>
						<!-- End Logo -->

						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.1.one_col_fix_with_title@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TEAM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[09.1.two_cols_fix_text_and_image_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PROCESSES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">AWARDS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="#block@block[04.1.one_col_fix_with_title@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
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
		'01.big_with_text_3_1' =>
			array (
				'CODE' => '01.big_with_text_3_1',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1060/img1.jpg\');" data-fileid="-1">
	<div class="landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner animated g-mx-0">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-20 g-mt-20 g-font-size-70 g-letter-spacing-3">WE ARE COMPANY24</h2>

		<div class="landing-block-node-text g-mb-35 g-letter-spacing-6 g-color-gray-light-v2 g-font-size-12">ARCHITECTURE COMPANY</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block landing-block-container js-animation fadeInUp animated g-pt-60 g-pb-30">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-color-primary g-mb-20 g-line-height-0_9 g-letter-spacing-6 g-font-size-11"><span style="font-weight: normal;">what we offer</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-40 g-letter-spacing-3"><span style="font-weight: bold;">EVERYTHING FOR YOUR COMFORT</span></h2>
            </div>
        </div>
    </section>',
			),
		'37.2.four_img_with_text_blocks' =>
			array (
				'CODE' => '37.2.four_img_with_text_blocks',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pb-40 g-pt-30">
	<div class="container px-0">
		<!-- Row -->
		<div class="row no-gutters landing-block-inner">
			<div class="landing-block-node-card js-animation animation-none col-md-6 col-lg-3 ">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img1.jpg);">
					<div class="g-theme-architecture-bg-blue-dark-v3 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-picture"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25">
							Exterior
							<br /> Design</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2">
							<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget
								dolor. Aenean massa. Omom sociis natoque penatibus.</p>
						</div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card js-animation animation-none col-md-6 col-lg-3 ">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img2.jpg);">
					<div class="g-theme-architecture-bg-blue-dark-v4 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-loop"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25">
							Interior
							<br /> Design</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2">
							<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget
								dolor. Aenean massa. Omom sociis natoque penatibus.</p>
						</div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card js-animation animation-none col-md-6 col-lg-3 ">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img3.jpg);">
					<div class="g-theme-architecture-bg-blue-dark-v3 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-note"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25">
							Project
							<br /> Documentation</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2">
							<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget
								dolor. Aenean massa. Omom sociis natoque penatibus.</p>
						</div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card js-animation animation-none col-md-6 col-lg-3 ">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img4.jpg);">
					<div class="g-theme-architecture-bg-blue-dark-v4 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-map"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25">
							Land
							<br /> acquisition</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2">
							<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget
								dolor. Aenean massa. Omom sociis natoque penatibus.</p>
						</div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Row -->
	</div>
</section>',
			),
		'04.3.one_col_fix_with_title_and_text' =>
			array (
				'CODE' => '04.3.one_col_fix_with_title_and_text',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block js-animation slideInRight g-pb-20 animated g-theme-architecture-bg-blue-dark-v3 g-pt-60">

        <div class="container text-center g-max-width-800 g-color-gray-light-v2">
            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-color-primary g-mb-20 g-letter-spacing-6 g-line-height-1_3"><span style="font-weight: normal;">WHAT WE DO</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10 g-letter-spacing-3">WE CREATE AMAZING THINGS</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-light-v2"><p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.</p></div>
        </div>

    </section>',
			),
		'31.2.two_cols_img_text' =>
			array (
				'CODE' => '31.2.two_cols_img_text',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-theme-architecture-bg-blue-dark-v3">
	<div>
		<div class="row mx-0">
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/597x354/img1.png\');"></div>
			
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default g-color-white g-mb-25 js-animation fadeInUp animated"><p style="text-align: left;"><span style="font-family: inherit;font-size: 1rem;">RESIDENTIAL BUILDINGS PROJECTS</span></p></h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-light-v2 js-animation fadeInUp animated"><p style="text-align: left;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.</p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn btn-xl u-btn-primary g-font-weight-700 g-font-size-12 g-rounded-50 js-animation fadeInUp animated" href="#" tabindex="0" target="_self">VIEW PROJECT</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@2' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-color-primary g-mb-20 g-letter-spacing-6"><span style="font-weight: normal;">OUR WORKS</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-letter-spacing-3">What we did</h2>
            </div>
        </div>
    </section>',
			),
		'20.2.three_cols_fix_img_title_text' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img5.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-11 text-center g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">BUILDINGS</span></h3>
				<div class="landing-block-node-text text-center g-color-black g-line-height-0_9 g-letter-spacing-1_5"><p><span style="font-weight: bold;">ARCHITECTURAL, BEAUTIFUL EXTERIORS</span></p></div>
			</div><div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img6.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-11 text-center g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">HI TECH</span></h3>
				<div class="landing-block-node-text text-center g-color-black g-line-height-0_9 g-letter-spacing-1_5"><p><span style="font-weight: bold;">MODERN APARTMENTS</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img7.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-11 text-center g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">BUSINESS</span></h3>
				<div class="landing-block-node-text text-center g-color-black g-line-height-0_9 g-letter-spacing-1_5"><p><span style="font-weight: bold;">ARCHITECTURE IN THE BUSINESS DISTRICT</span></p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'20.2.three_cols_fix_img_title_text@2' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img8.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 text-center g-font-size-11 g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">BUSINESS CITY</span></h3>
				<div class="landing-block-node-text text-center g-letter-spacing-3 g-line-height-0_9"><p><span style="font-weight: bold; color: rgb(33, 33, 33);">FOR LARGE CORPORATIONS</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img9.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 text-center g-font-size-11 g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">CITY</span></h3>
				<div class="landing-block-node-text text-center g-letter-spacing-3 g-line-height-0_9"><p><span style="font-weight: bold; color: rgb(33, 33, 33);">MODERN TWIN HOUSES</span></p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x683/img10.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 text-center g-font-size-11 g-line-height-0 g-letter-spacing-6"><span style="font-weight: normal;color: rgb(97, 97, 97);">BUSINESS</span></h3>
				<div class="landing-block-node-text text-center g-letter-spacing-3 g-line-height-0_9"><p><span style="font-weight: bold; color: rgb(33, 33, 33);">UNIQUE ARCHITECTURE</span></p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@3' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block landing-block-container g-pt-60 g-pb-60 js-animation fadeInUp animated g-theme-architecture-bg-blue-dark-v3">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-color-primary g-mb-20 g-line-height-0_9 g-font-size-11 g-letter-spacing-6"><span style="font-weight: normal;">OUR TEAM</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-color-white g-letter-spacing-3">WORK WITH PROFESSIONALS</h2>
            </div>
        </div>
    </section>',
			),
		'28.3.team' =>
			array (
				'CODE' => '28.3.team',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-theme-architecture-bg-blue-dark-v3">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img5.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">
							<span style="color: rgb(245, 245, 245);">james@company24.com<br />+ 44 (555) 2566 112</span>
							</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-line-height-1 g-font-size-11 g-color-gray-light-v1 g-letter-spacing-3"><span style="font-weight: normal;">Technical SUPERVISOR</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7 g-line-height-1_5 g-color-gray-light-v5"><span style="">JAMES NOVEL</span></h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">
							<span style="color: rgb(245, 245, 245);">catrina@company24.com<br />+ 44 (555) 2566 113</span>
							</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-line-height-1 g-font-size-11 g-color-gray-light-v1 g-letter-spacing-3"><span style="font-weight: normal;">TECHNICAL DIRECTOR</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7 g-line-height-1_5 g-color-gray-light-v5">CATRINA WEARNER</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pointer-events-none g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">
							<span style="color: rgb(245, 245, 245);">fiona@company24.com<br />+ 44 (555) 2566 114</span>
							</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-line-height-1 g-font-size-11 g-color-gray-light-v1 g-letter-spacing-3"><span style="font-weight: normal;">technical manager</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7 g-line-height-1_5 g-color-gray-light-v5">FIONA BILOTI</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			),
		'09.1.two_cols_fix_text_and_image_slider' =>
			array (
				'CODE' => '09.1.two_cols_fix_text_and_image_slider',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-115 g-pb-80">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 g-mb-40 g-mb-0--lg landing-block-node-text-container js-animation fadeInLeft">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-primary g-mb-20 g-line-height-0_9 g-letter-spacing-6"><span style="font-weight: normal;">work process</span></h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-33"><span style="font-weight: bold;">HOW WE WORK</span></h2>
                    </div>

					<div class="g-color-main-p landing-block-node-text"><p>Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Etiam sit amet orci eget eros.</p><p><span style="font-family: -apple-system, system-ui, BlinkMacSystemFont,;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Etiam sit amet orci eget eros.</span><br /></p><p><span style="font-family: -apple-system, system-ui, BlinkMacSystemFont,;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Etiam sit amet orci eget eros.</span><br /></p></div>
                </div>

                <div class="col-lg-8 landing-block-node-carousel-container js-animation fadeInRight">
                    <div class="landing-block-node-carousel js-carousel g-line-height-0"
                         data-infinite="true"
                         data-speed="5000"
                         data-rows="2"
                         data-slides-show="2"
                         data-arrows-classes="u-arrow-v1 g-pos-abs g-bottom-100x g-right-0 g-width-35 g-height-35 g-font-size-default g-color-gray g-color-white--hover g-bg-gray-light-v5 g-bg-primary--hover g-mb-5 g-transition-0_2 g-transition--ease-in"
                         data-arrow-left-classes="fa fa-angle-left g-mr-50"
                         data-arrow-right-classes="fa fa-angle-right g-mr-5"
						 data-responsive=\'[{
							 "breakpoint": 1200,
							 "settings": {
							   "slidesToShow": 2
							 }
						   }, {
							 "breakpoint": 768,
							 "settings": {
							   "slidesToShow": 1
							 }
						   }]\'>
                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img9.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>
						
                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img10.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img11.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img12.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img5.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-node-carousel-element landing-block-card-carousel-element js-slide g-pa-5">
                            <div class="g-parent g-pos-rel g-overflow-hidden">
                                <img class="landing-block-node-carousel-element-img img-fluid g-transform-scale-1_1--parent-hover g-transition-0_3 g-transition--ease-in" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x269/img6.jpg" alt="" />
                                <div class="landing-block-node-carousel-element-img-hover g-pos-abs g-top-0 g-left-0 w-100 h-100 g-bg-primary-opacity-0_8 g-color-white opacity-0 g-opacity-1--parent-hover g-pa-25 g-transition-0_3 g-transition--ease-in">
                                    <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white mb-0 g-font-size-14 g-line-height-1_3 g-letter-spacing-1_5">RESIDENTIAL BUILDINGS PROJECTS</h3>
                                    <div class="landing-block-node-carousel-element-text g-line-height-1_5--hover g-font-size-12 g-transition-0_3 g-transition--ease-in g-color-gray-light-v4"><p><br /></p></div>
                                </div>
                            </div>
                        </div>

                        

                        
                    </div>
                </div>

            </div>
        </div>
    </section>',
			),
		'04.3.one_col_fix_with_title_and_text@2' =>
			array (
				'CODE' => '04.3.one_col_fix_with_title_and_text',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation slideInRight g-pb-20 animated g-theme-architecture-bg-blue-dark-v3 g-pt-60">

        <div class="container text-center g-max-width-800 g-color-gray-light-v2">
            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-color-primary g-mb-20 g-letter-spacing-6"><span style="font-weight: normal;">OUR TECHNOLOGIESss</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10 g-letter-spacing-3">HOW WE CREATE</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-light-v2"><p>Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.</p></div>
        </div>

    </section>',
			),
		'20.2.three_cols_fix_img_title_text@3' =>
			array (
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20 g-theme-architecture-bg-blue-dark-v3">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x574/img1.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-color-gray-light-v5 g-font-size-14"><p style="text-align: center;"><span style="color: rgb(247, 247, 247);font-family: inherit;">RESIDENTIAL BUILDINGS PROJECTS</span></p></h3>
				<div class="landing-block-node-text g-color-gray-light-v2"><p style="text-align: center;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x574/img2.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-color-gray-light-v5 g-font-size-14"><p style="text-align: center;"><span style="color: rgb(247, 247, 247);font-family: inherit;">RESIDENTIAL BUILDINGS PROJECTS</span></p></h3>
				<div class="landing-block-node-text g-color-gray-light-v2"><p style="text-align: center;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn animated ">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1024x574/img3.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-color-gray-light-v5 g-font-size-14"><p style="text-align: center;"><span style="color: rgb(247, 247, 247);font-family: inherit;">RESIDENTIAL BUILDINGS PROJECTS</span></p></h3>
				<div class="landing-block-node-text g-color-gray-light-v2"><p style="text-align: center;">Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p></div>
			</div>

		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-main g-pt-60 g-pb-10">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-color-primary g-mb-15 g-font-size-11 g-letter-spacing-6"><p style=""><span style="font-weight: normal;">OUR AWARDS</span></p></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10">WE ARE THE BEST</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero.</p><p>Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.Etiam rhoncus.</p></div>
        </div>

    </section>',
			),
		'24.3.image_gallery_6_cols_fix_3' =>
			array (
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block js-animation zoomIn text-center g-pb-90 animated g-pt-10">
	<div class="container">
		<div class="row g-brd-top g-brd-left g-brd-gray-light-v4 mx-0">
			<div class="landing-block-node-card col-md-4 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50  col-lg-4">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx350/img1.png" alt="" data-fileid="-1" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50  col-lg-4">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx350/img2.png" alt="" data-fileid="-1" />
				</a>
			</div>

			

			<div class="landing-block-node-card col-md-4 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50  col-lg-4">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx350/img3.png" alt="" data-fileid="-1" />
				</a>
			</div>

			

			
		</div>
	</div>
</section>',
			),
		'24.3.image_gallery_6_cols_fix_3@2' =>
			array (
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block js-animation text-center g-py-90 zoomIn animated g-theme-architecture-bg-blue-dark-v3">
	<div class="container">
		<div class="row g-brd-top g-brd-left g-brd-gray-light-v4 mx-0">
			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img1.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img2.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img3.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img4.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img5.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50 ">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/xx72/img6.png" alt="" />
				</a>
			</div>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@4' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block landing-block-container g-pt-60 g-pb-60 js-animation fadeInUp animated g-bg-main">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary ">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-color-primary g-mb-20 g-line-height-0_9 g-font-size-11 g-letter-spacing-6"><span style="font-weight: normal;">CONTACT US</span></h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-letter-spacing-3"><span style="font-weight: bold;">KEEP IN TOUCH</span></h2>
            </div>
        </div>
    </section>',
			),
		'33.12.form_2_light_right_text' =>
			array (
				'CODE' => '33.12.form_2_light_right_text',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-pt-100 g-pb-100">

	<div class="container">

		<div class="landing-block-form-styles" hidden="">
			<div class="g-bg-transparent h1 g-color-white g-brd-none g-pa-0" data-form-style-wrapper-padding="1" data-form-style-bg="1" data-form-style-bg-content="1" data-form-style-bg-block="1" data-form-style-header-font-size="1" data-form-style-main-font-weight="1" data-form-style-button-font-color="1" data-form-style-border-block="1">
			</div>
			<div class="g-bg-primary g-color-primary g-brd-primary" data-form-style-main-bg="1" data-form-style-main-border-color="1" data-form-style-main-font-color-hover="1">
			</div>
			<div class="g-bg-gray-light-v5 g-color-gray-dark-v1 g-brd-around g-brd-white rounded-0" data-form-style-input-bg="1" data-form-style-input-select-bg="1" data-form-style-input-border="1" data-form-style-input-border-radius="1" data-form-style-main-font-color="1">
			</div>
			<div class="g-brd-around g-brd-gray-light-v2 g-color-gray-dark-v5 g-brd-bottom g-bg-black-opacity-0_7" data-form-style-input-border-color="1" data-form-style-input-border-hover="1" data-form-style-icon-font-color="1">
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v4" data-b24form="" data-b24form-use-style="Y" data-b24form-show-header="N" data-b24form-original-domain=""></div>
			</div>
			
			<div class="col-md-6">
				<div class="text-center g-overflow-hidden">
					<h3 class="landing-block-node-main-title h3 text-uppercase g-font-weight-700 g-mb-20 g-color-black"> </h3>
					
					<div class="landing-block-node-text g-line-height-1_5 text-left g-mb-40 g-color-gray-dark-v5" data-form-style-main-font-family="1" data-form-style-main-font-weight="1" data-form-style-header-text-font-size="1"> </div>
					<div class="g-mx-minus-2 g-my-minus-2">
						<div class="row mx-0">
							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-anchor d-inline-block g-font-size-50  g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0"
									data-form-style-label-font-weight="1"
									data-form-style-label-font-size="1"
									data-form-style-second-font-color="1"
								>
									Address</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;">Sit amet adipiscing</span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-call-in d-inline-block g-font-size-50  g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0"
									data-form-style-label-font-weight="1"
									data-form-style-label-font-size="1"
									data-form-style-second-font-color="1"
								>
									Phone
									number</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="tel:+4025448569">+402 5448 569</a></span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-line icon-envelope-letter d-inline-block g-font-size-50  g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0"
									data-form-style-label-font-weight="1"
									data-form-style-label-font-size="1"
									data-form-style-second-font-color="1"
								>
									Email</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="mailto:info@company24.com">info@company24.com</a></span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-earphones-alt d-inline-block g-font-size-50  g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0"
									data-form-style-label-font-weight="1"
									data-form-style-label-font-size="1"
									data-form-style-second-font-color="1"
								>
									Toll free</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="tel:+402 5897 660">+402 5897 660</a></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'17.1.copyright_with_social' =>
			array (
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 js-animation animation-none g-theme-architecture-bg-blue-dark-v3">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 js-animation animation-none">
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
	),
);