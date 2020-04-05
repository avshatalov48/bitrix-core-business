<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_AGENCY_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_AGENCY_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'agency',
			'THEME_CODE_TYPO' => 'agency',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/agency/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_AGENCY_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_AGENCY_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_AGENCY_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_AGENCY_DESCRIPTION')
		)
	),
	'items' => array (
		'0.menu_02' =>
			array (
				'CODE' => '0.menu_02',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu u-header u-header--floating u-header--floating-relative">
	<div class="u-header__section u-header__section--light g-bg-white g-transition-0_3 g-py-16 g-py-10--md" data-header-fix-moment-exclude="g-bg-white" data-header-fix-moment-classes="u-shadow-v27 g-bg-white-opacity-0_9">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/agency-logo-dark.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 ml-auto g-pt-20 g-pt-0--lg">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[46.6.cover_with_bg_image_and_bottom_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[02.three_cols_big_1]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">WHY WE</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">WORK PROCESS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SKILLS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[28.2.team]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TEAM</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[24.3.image_gallery_6_cols_fix_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TESTIMONIALS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACT US</a>
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
		'46.6.cover_with_bg_image_and_bottom_title' =>
			array (
				'CODE' => '46.6.cover_with_bg_image_and_bottom_title',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel"
		 data-pagi-classes="u-carousel-indicators-v1 g-right-30 g-bottom-30">
		<div class="landing-block-node-card landing-block-node-card-bgimg js-slide d-flex align-items-end u-bg-overlay g-min-height-100vh g-min-height-600 g-bg-img-hero g-bg-black-opacity-0_5--after"
			 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img7.jpg);">
			<div class="u-bg-overlay__inner w-100">
				<div class="g-max-width-645 py-0 g-px-30 g-pb-30">
					<h2 class="landing-block-node-card-title js-animation fadeInUp g-font-montserrat g-line-height-1 g-font-weight-700 g-font-size-90 g-color-white g-mb-15">
						Company24 agency</h2>
					<div class="landing-block-node-card-text-container js-animation fadeInUp row align-items-start">
						<div class="landing-block-node-card-text g-color-white-opacity-0_5 mb-0 col-12 col-md-9">
							<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
								iaculis laoreet.
							</p>
						</div>
						<div class="col-md-3">
							<a href="/"
							   class="landing-block-node-card-button text-uppercase btn u-btn-outline-white btn-md rounded-0">
								Read more
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card landing-block-node-card-bgimg js-slide d-flex align-items-end u-bg-overlay g-min-height-100vh g-min-height-600 g-bg-img-hero g-bg-black-opacity-0_5--after"
			 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img8.jpg);">
			<div class="u-bg-overlay__inner w-100">
				<div class="g-max-width-645 py-0 g-px-30 g-pb-30">
					<h2 class="landing-block-node-card-title js-animation fadeInUp g-font-montserrat g-line-height-1 g-font-weight-700 g-font-size-90 g-color-white g-mb-15">
						So smooth!</h2>
					<div class="landing-block-node-card-text-container js-animation fadeInUp row align-items-start">
						<div class="landing-block-node-card-text g-color-white-opacity-0_5 mb-0 col-12 col-md-9">
							<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
								iaculis laoreet.
							</p>
						</div>
						<div class="col-md-3">
							<a href="/"
							   class="landing-block-node-card-button text-uppercase btn u-btn-outline-white btn-md rounded-0">
								Read more
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
',
			),
		'37.2.four_img_with_text_blocks' =>
			array (
				'CODE' => '37.2.four_img_with_text_blocks',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-40 g-pb-40 g-theme-architecture-bg-blue-dark-v3">
	<div class="container px-0">
		<!-- Row -->
		<div class="row no-gutters">
			<div class="landing-block-node-card col-md-6 col-lg-3 js-animation animation-none">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x390/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<div class="g-theme-architecture-bg-blue-dark-v3 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-picture"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25 g-font-size-16">WE LOVE OUR CUSTOMERS</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2"><p>Nulla cursus orci sed ipsum scelerisque volutpat. Integer quis dapibus leo, maximus ultrices dui. Mauris facilisis, ex sed scelerisque bibendum, tellus leo pharetra augue, sed iaculis felis neque quis magna.</p></div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3 js-animation animation-none">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x390/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<div class="g-theme-architecture-bg-blue-dark-v4 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-loop"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25 g-font-size-16">WE ARE CREATIVE</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2"><p>Nulla cursus orci sed ipsum scelerisque volutpat. Integer quis dapibus leo, maximus ultrices dui. Mauris facilisis, ex sed scelerisque bibendum, tellus leo pharetra augue, sed iaculis felis neque quis magna.</p></div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3 js-animation animation-none">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x390/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<div class="g-theme-architecture-bg-blue-dark-v3 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-note"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25 g-font-size-16">WE ARE PRAGMATIC</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2"><p>Nulla cursus orci sed ipsum scelerisque volutpat. Integer quis dapibus leo, maximus ultrices dui. Mauris facilisis, ex sed scelerisque bibendum, tellus leo pharetra augue, sed iaculis felis neque quis magna.</p></div>
						<div class="landing-block-node-card-link-container mt-auto">
							<a href="#" class="landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-text-underline--none--hover">Read
								More</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-card col-md-6 col-lg-3 js-animation animation-none">
				<div class="landing-block-node-card-bgimg g-bg-img-hero h-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x390/img4.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
					<div class="g-theme-architecture-bg-blue-dark-v4 d-flex flex-column h-100 g-opacity-1 g-opacity-0_8--hover g-py-50 g-px-15 g-pa-100-30--sm g-transition-0_2 g-transition--ease-in">
                  <span class="landing-block-node-card-icon-container d-block g-line-height-1 g-font-size-30 g-color-primary g-mb-20">
                    <i class="landing-block-node-card-icon icon-map"></i>
                  </span>
						<h3 class="landing-block-node-card-title text-uppercase g-line-height-1_2 g-font-weight-700 g-color-white g-mb-25 g-font-size-16">WE ARE PROFESSIONAL</h3>
						<div class="landing-block-node-card-text g-font-size-13 g-mb-30 g-color-gray-light-v2"><p>Nulla cursus orci sed ipsum scelerisque volutpat. Integer quis dapibus leo, maximus ultrices dui. Mauris facilisis, ex sed scelerisque bibendum, tellus leo pharetra augue, sed iaculis felis neque quis magna.</p></div>
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
		'02.three_cols_big_1' =>
			array (
				'CODE' => '02.three_cols_big_1',
				'SORT' => '1500',
				'CONTENT' => '<section class="container-fluid px-0 landing-block">
        <div class="row no-gutters">
            <div class="landing-block-node-left-img g-min-height-300 col-lg-4 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img10.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>

            <div class="landing-block-node-center col-md-6 col-lg-4 g-flex-centered g-bg-secondary">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-center-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 js-animation fadeIn">WHY WE</h4>
                        <h2 class="landing-block-node-center-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-color-black g-font-size-35 js-animation fadeIn">WE ARE MAGICIANS</h2>
                    </div>

                    <div class="landing-block-node-center-text mb-0 g-color-gray-dark-v5 js-animation fadeIn"><p>Donec ut diam risus. Nunc cursus turpis ac erat mollis maximus. Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus iaculis laoreet. Phasellus ac ipsum odio.</p><p><span style="font-size: 1rem;">Nulla cursus orci sed ipsum scelerisque volutpat. Integer quis dapibus leo, maximus ultrices dui. Mauris facilisis, ex sed scelerisque bibendum, tellus leo pharetra augue.</span></p><p>Mauris aliquet, magna nec gravida interdum, magna nibh fringilla nulla, eget egestas sapien orci eget tellus. Pellentesque vulputate posuere libero a varius. Duis feugiat.</p></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 landing-block-node-right g-theme-architecture-bg-blue-dark-v3">
                <div class="js-carousel g-pb-90" data-infinite="true" data-slides-show="true" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img1.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">BRANDING AND IDENTITY</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img2.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">UI/UX AND GRAPHIC DESIGN</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img3.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10 js-animation fadeIn">WEB AND SOFTWARE DEVELOPMENT</h3>
                            <div class="landing-block-node-right-text g-color-gray-light-v2 js-animation fadeIn">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>',
			),
		'13.2.one_col_fix_button' =>
			array (
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block text-center g-py-20 g-bg-secondary g-pb-7">
        <div class="container">
				<a class="landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15" href="#" g-font-weight-700 target="_self">GET IN TOUCH</a>
        </div>
    </section>',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-py-20 g-pt-70 js-animation fadeInUp animated g-bg-main g-pb-10">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">SERVICES</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10">WHAT WE DO</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			),
		'34.3.four_cols_countdown' =>
			array (
				'CODE' => '34.3.four_cols_countdown',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pb-70 g-bg-main g-pt-20">
	<div class="container">
		<div class="row">
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-white-opacity-0_6 g-mb-15 g-color-primary">
						<i class="landing-block-node-card-icon icon-magic-wand"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-color-black g-font-size-15">WEB DESIGN</h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text g-font-size-default mb-0 g-color-black-opacity-0_6"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-white-opacity-0_6 g-mb-15 g-color-primary">
						<i class="landing-block-node-card-icon icon-diamond"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-color-black g-font-size-15">GRAPHIC DESIGN</h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text g-font-size-default mb-0 g-color-black-opacity-0_6"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-white-opacity-0_6 g-mb-15 g-color-primary">
						<i class="landing-block-node-card-icon icon-calculator"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-color-black g-font-size-15">UI/UX</h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text g-font-size-default mb-0 g-color-black-opacity-0_6"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis</p></div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-3 text-center g-mb-40 g-mb-0--lg animated ">
					<span class="landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-white-opacity-0_6 g-mb-15 g-color-primary">
						<i class="landing-block-node-card-icon icon-badge"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-color-black g-font-size-15">BRANDING</h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20"> </div>
				<div class="landing-block-node-card-text g-font-size-default mb-0 g-color-black-opacity-0_6"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis</p></div>
			</div>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-theme-architecture-bg-blue-dark-v3 g-pt-60">

	<div class="container text-center g-max-width-800">

		<div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">WORK PROCESS</h4>
			<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-white">HOW WE WORK</h2>
		</div>

		<div class="landing-block-node-text g-color-white-opacity-0_8"><p>Praesent eu nibh malesuada, condimentum nibh hendrerit, viverra sem. Nulla porttitor eget ante ullamcorper convallis. Integer dictum lorem arcu, eget tempus nulla accumsan id.</p></div>
	</div>

</section>',
			),
		'31.1.two_cols_text_img' =>
			array (
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-theme-architecture-bg-blue-dark-v3">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default g-color-white g-mb-25 js-animation fadeInUp">USER INTERFACE</h3>
				<div class="landing-block-node-text g-mb-30 g-font-size-13 g-color-gray-light-v2 js-animation fadeInUp"><p><span style="font-weight: bold;"><span style="color: rgb(245, 245, 245);">1. DISCUSS WITH CLIENT</span><br /></span><span style="">Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis. Donec vel pharetra tellus. Sed non est lacus.<br /><br /></span><span style="font-weight: bold;">2. WIREFRAME<br /></span><span style="">Cras sit amet varius velit. Maecenas porta condimentum tortor at sagittis. Cum sociis natoque penatibus et magnis dis. Donec vel pharetra tellus. Sed non est lacus.<br /></span><span style="font-weight: bold;"><br />3. CREATIVE CONCEPT<br /></span><span style="">Nam in nisl volutpat ex bibendum sollicitudin. Praesent ac magna convallis, sagittis erat in, dapibus mauris. Donec vel pharetra tellus. Sed non est lacus.</span></p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn btn-xl u-btn-primary g-font-weight-700 g-font-size-12 g-rounded-50 js-animation fadeInUp" href="#" tabindex="0">Contact us
						<span>for more info</span></a>
				</div>
			</div>
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-contain--xs g-bg-size-cover--sm" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x372/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-py-20 g-pt-70 g-pb-0 js-animation fadeInUp animated g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">SKILLS</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10">OUR ADVANTAGES</h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v5"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio. Donec eu nulla leo. Vivamus risus lacus, viverra eu maximus non, tincidunt sodales massa</p></div>
        </div>

    </section>',
			),
		'08.1.three_cols_fix_title_and_text' =>
			array (
				'CODE' => '08.1.three_cols_fix_title_and_text',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-70">
        <div class="container">
            <div class="row">

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-15 g-text-break-word">84 HAPPY CLIENTS</h2>
                    </div>

                    <div class="landing-block-node-text g-color-gray-dark-v5"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficiturInteger accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio maximus efficitur</p></div>
                </div>

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-15 g-text-break-word">34 COMPLETED PROJECTS</h2>
                    </div>

                    <div class="landing-block-node-text g-color-gray-dark-v5"><p>Quisque vestibulum sem eget nibh commodo, non elementum nibh pulvinar. Duis mattis venenatis tortor iaculis ultriciesQuisque vestibulum sem eget nibh commodo, non elementum nibh pulvinar. Duis mattis venenatis tortor iaculis ultricies</p></div>
                </div>

                <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-15 g-text-break-word">35 OUR TEAM</h2>
                    </div>

                    <div class="landing-block-node-text g-color-gray-dark-v5"><p>Nullam in diam arcu. Etiam nisl justo, tempor scelerisque sagittis vel, bibendum vestibulum metus. Donec eget nunc nequeNullam in diam arcu. Etiam nisl justo, tempor scelerisque sagittis vel, bibendum vestibulum metus. Donec eget nunc neque</p></div>
                </div>

            <div class="landing-block-card g-mb-40 g-mb-0--lg  col-lg-3 js-animation fadeIn">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h4>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-15 g-text-break-word">67 COUNTRIES</h2>
                    </div>

                    <div class="landing-block-node-text g-color-gray-dark-v5"><p>Rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum fringilla velit rhoncus euismod rhoncus turpis</p></div>
                </div></div>
        </div>
    </section>',
			),
		'28.2.team' =>
			array (
				'CODE' => '28.2.team',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md">
	
	
	
	
	
	
	
	
	
	
	
	
	
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-black-opacity-0_7--after g-pt-30 g-pt-80--md g-pb-250" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x585/img1.jpg);">
		<div class="container text-center u-bg-overlay__inner g-max-width-800">
			<div class="landing-block-node-header text-uppercase g-brd-primary g-mb-30 u-heading-v2-4--bottom">
				<h3 class="landing-block-node-subtitle g-font-weight-600 g-font-size-12 g-color-primary g-mb-20">Our
					team</h3>
				<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1 g-letter-spacing-2 g-font-weight-700 g-font-size-30 g-font-size-40--md g-color-white mb-0">
					Work with professionals</h2>
			</div>

			<div class="landing-block-node-text mb-0 g-color-gray-light-v2">
				<p>Praesent eu nibh malesuada, condimentum nibh hendrerit, viverra
					sem. Nulla porttitor eget ante ullamcorper convallis. Integer dictum lorem arcu, eget tempus nulla
					accumsan id.</p>
			</div>
		</div>
	</div>

	<div class="container g-mt-minus-200">
		<!-- Team Block -->
		<div class="row">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img1.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Photographer</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">
						Ralf
						Smith</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0 g-color-gray-dark-v5">head
						photographer</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img2.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Designer</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">
						Monica
						Gaudy</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0 g-color-gray-dark-v5">head
						photographer</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--md pulse">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img3.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Co-founder</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">
						Julia
						Exon</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0 g-color-gray-dark-v5">head
						photographer</div>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 pulse">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img4.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white">Changing
								your mind and changing world</div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<div class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5">Co-founder</div>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-color-gray-dark-v2 g-mb-7">
						Jacob
						Assange</h4>
					<div class="landing-block-node-employee-subtitle g-font-size-13 g-color-gray-dark-v5 mb-0 g-color-gray-dark-v5">head
						photographer</div>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pb-0 g-bg-secondary g-pt-70 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">WANT TO JOIN OUR TEAM?</h2>
            </div>
        </div>
    </section>',
			),
		'13.1.one_col_fix_text_and_button' =>
			array (
				'CODE' => '13.1.one_col_fix_text_and_button',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block text-center g-py-20 g-pt-0 g-bg-secondary g-pb-70">
	<div class="container g-max-width-800">

		<div class="landing-block-node-text g-color-gray-dark-v5"><p>Sed eget aliquet nisl. Proin laoreet accumsan nisl non vestibulum. Donec molestie, lorem nec sollicitudin elementum, mi justo posuere lectus, vitae ullamcorper orci mi vel massa. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p></div>

		<div class="landing-block-node-button-container">
			<a class="landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15" href="#" g-font-weight-700 target="_self">SEND YOUR RESUME</a>
		</div>
	</div>
</section>',
			),
		'24.3.image_gallery_6_cols_fix_3' =>
			array (
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block js-animation text-center g-py-90 g-theme-architecture-bg-blue-dark-v3 zoomIn">
	<div class="container">
		<div class="row g-brd-top g-brd-left g-brd-gray-light-v4 mx-0">
			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img1.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img2.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img3.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img4.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img5.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img6.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</a>
			</div>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title@2' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-pt-70 g-pb-60 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">CONTACT US</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10">GET IN TOUCH</h2>
            </div>
        </div>
    </section>',
			),
		'14.1.contacts_4_cols' =>
			array (
				'CODE' => '14.1.contacts_4_cols',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-pt-40 g-pb-25 text-center g-theme-architecture-bg-blue-dark-v3">
		<div class="container">
			<div class="row justify-content-center">
	
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover"
					   href="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2304.457421907711!2d20.486353716222904!3d54.71916848028964!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x284054d2fac2875d!2z0JHQuNGC0YDQuNC60YEyNA!5e0!3m2!1sru!2sru!4v1536590497258"
					   target="_popup">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-globe"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
							Address</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ">
							61 Oxford str., London, 3DG
						</span>
					</a>
				</div>
	
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
						   data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover" href="tel:1-800-643-4500">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-call-in"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
							Phone number</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ">
							1-800-643-4500
						</span>
					</a>
				</div>
	
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-link">
					<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover" href="mailto:info@company24.com" target="_blank">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-linkcontact-icon icon-envelope"></i>
						</span>
						<span class="landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
							Email</span>
						<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ">
							info@company24.com
						</span>
					</a>
				</div>
	
				<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-3 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					 data-card-preset="contact-text">
					<div class="landing-block-node-contact-container">
						<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
							<i class="landing-block-node-contact-icon icon-earphones-alt"></i>
						</span>
						<span class="landing-block-node-contact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
							Toll free</span>
						<span class="landing-block-node-contact-text g-font-size-14 g-font-weight-700 ">
							@company24
						</span>
					</div>
				</div>
				
				
				
			</div>
		</div>
    </section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-bg-main g-pb-0 g-pt-70 js-animation fadeInUp">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">HAVE QUESTIONS?</span></h2>
                <div class="landing-block-node-text g-font-size-16 g-color-gray-dark-v5"><p>Sed eget aliquet nisl. Proin laoreet accumsan nisl non vestibulum.</p></div>
            </div>
        </div>

    </section>',
			),
		'33.13.form_2_light_no_text' =>
			array (
				'CODE' => '33.13.form_2_light_no_text',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-color-gray-dark-v1 g-py-100 g-bg-main g-pt-0 g-pb-0">

	<div class="container">

		<div class="landing-block-form-styles" hidden="">
			<div class="g-bg-transparent h1 g-color-white g-brd-none g-pa-0" data-form-style-wrapper-padding="1" data-form-style-bg="1" data-form-style-bg-content="1" data-form-style-bg-block="1" data-form-style-header-font-size="1" data-form-style-main-font-weight="1" data-form-style-button-font-color="1" data-form-style-border-block="1">
			</div>
			<div class="g-bg-primary g-color-primary g-brd-primary" data-form-style-main-bg="1" data-form-style-main-border-color="1" data-form-style-main-font-color-hover="1">
			</div>
			<div class="g-bg-gray-light-v5 g-brd-around g-brd-white rounded-0" data-form-style-input-bg="1" data-form-style-input-select-bg="1" data-form-style-input-border="1" data-form-style-input-border-radius="1">
			</div>
			<div class="g-brd-around g-brd-gray-light-v2 g-brd-bottom g-bg-black-opacity-0_7" data-form-style-input-border-color="1" data-form-style-input-border-hover="1">
			</div>

				<p class="g-color-gray-dark-v5" data-form-style-main-font-family="1" data-form-style-main-font-weight="1" data-form-style-header-text-font-size="1">
				</p>

			<h3 class="g-font-size-11 g-color-gray-dark-v5" data-form-style-label-font-weight="1" data-form-style-label-font-size="1" data-form-style-second-font-color="1">
				</h3>

			<div class="g-font-size-11" data-form-style-main-font-color="1">
			</div>
		</div>

		<div class="row">
			<div class="col-md-6 mx-auto">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v4" data-b24form="" data-b24form-use-style="Y" data-b24form-show-header="N" data-b24form-original-domain=""></div>
			</div>
		</div>
	</div>
</section>',
			),
		'17.2.copyright_with_bgimg' =>
			array (
				'CODE' => '17.2.copyright_with_bgimg',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-color-white g-bg-primary-opacity-0_8--after g-py-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x500/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
		<div class="container text-center text-md-left u-bg-overlay__inner">
			<div class="row">
				<div class="col-md-4 col-lg-5 d-flex align-items-center g-mb-20 g-mb-0--md">
					<div class="w-100 g-font-size-13 g-color-white mb-0 landing-block-node-copy">
						&copy; 2018 All rights reserved.
					</div>
				</div>

				<div class="col-md-4 col-lg-2 d-flex text-center align-items-center g-mb-20 g-mb-0--md">
					<div>
						<div class="w-100 text-uppercase g-font-size-11 g-color-white mb-0 landing-block-node-phone-subtitle js-animation fadeInRight">
							Support 24/7
						</div>
						<div class="d-block g-font-size-22 g-mt-5">
							<a href="tel:+458 669 221" class="landing-block-node-phone-link g-font-weight-700 g-color-white js-animation d-block fadeInLeft">+458 669 221</a>
						</div>
					</div>
				</div>

				<div class="col-md-4 col-lg-5 g-flex-centered">
					<div class="w-100">
												<ul class="list-inline float-md-right mb-0">
							<li class="landing-block-card-social list-inline-item g-mr-10"
								data-card-preset="facebook">
								<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-white g-color-white--hover g-bg-primary--hover g-brd-white g-brd-primary--hover g-rounded-50x"
								   href="https://facebook.com">
									<i class="landing-block-card-social-icon fa fa-facebook"></i>
								</a>
							</li>

							<li class="landing-block-card-social list-inline-item g-mr-10"
								data-card-preset="instagram">
								<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-white g-color-white--hover g-bg-primary--hover g-brd-white g-brd-primary--hover g-rounded-50x"
								   href="https://instagram.com">
									<i class="landing-block-card-social-icon fa fa-instagram"></i>
								</a>
							</li>
							<li class="landing-block-card-social list-inline-item g-mr-10"
								data-card-preset="twitter">
								<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-white g-color-white--hover g-bg-primary--hover g-brd-white g-brd-primary--hover g-rounded-50x"
								   href="https://twitter.com">
									<i class="landing-block-card-social-icon fa fa-twitter"></i>
								</a>
							</li>
							<li class="landing-block-card-social list-inline-item g-mr-10"
								data-card-preset="youtube">
								<a class="landing-block-card-social-icon-link u-icon-v2 g-width-35 g-height-35 g-font-size-16 g-color-white g-color-white--hover g-bg-primary--hover g-brd-white g-brd-primary--hover g-rounded-50x"
								   href="https://youtube.com">
									<i class="landing-block-card-social-icon fa fa-youtube"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
	)
);