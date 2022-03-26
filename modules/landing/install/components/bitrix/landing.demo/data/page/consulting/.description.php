<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_CONSULTING_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_CONSULTING_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'consulting',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/consulting/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_CONSULTING_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CONSULTING_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_CONSULTING_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CONSULTING_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_08_consulting' =>
			[
				'CODE' => '0.menu_08_consulting',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float">
	<div class="u-header__section g-bg-black-opacity-0_2 g-transition-0_3 g-py-7 g-py-23--md" data-header-fix-moment-exclude="g-bg-black-opacity-0_2 g-py-23--md" data-header-fix-moment-classes="u-header__section--light u-shadow-v19 g-bg-white g-py-15--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo g-max-width-180 d-block"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/consulting-logo-light.png" alt=""
						 data-header-fix-moment-exclude="d-block"
						 data-header-fix-moment-classes="d-none">
					<img class="landing-block-node-menu-logo2 g-max-width-180 d-none"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/consulting-logo-dark.png" alt=""
						 data-header-fix-moment-exclude="d-none"
						 data-header-fix-moment-classes="d-block">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-1 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[01.big_with_text_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PORTFOLIO</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">NEWS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[33.1.form_1_transparent_black_left_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
						</li>

					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
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
		'01.big_with_text_blocks' =>
			[
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="15000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">



		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img1.jpg\');">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-36 g-color-white g-mb-20">WE ARE CREATIVE TECHNOLOGY COMPANY </h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-font-size-24 g-color-white g-line-height-1_2"><p>Creative freedom matters user experience.<br /><span style="font-size: 1.71429rem;">We minimize the gap between technology and its audience.</span></p></div>
					<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50 g-py-15" href="#" tabindex="-1" target="_self">LEARN MORE</a>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img2.jpg\');">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-36">WE DO THINGS DIFFERENTLY </h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-font-size-24 g-color-white g-line-height-1_2"><p>Creative freedom matters user experience.</p><p>We minimize the gap between technology and its audience.</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50 g-py-15" href="#" tabindex="0" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img3.jpg\');">
				<div class="container text-center g-max-width-800 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-36 g-color-white g-mb-20">DEDICATED ADVANCED TEAM</h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-font-size-24 g-color-white g-line-height-1_2"><p>We are creative technology company providing </p><p>key digital services on web and mobile.</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50 g-py-15" href="#" tabindex="0" target="_self">LEARN MORE</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 g-pt-50 g-pb-50 js-animation fadeInUp animated">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-letter-spacing-3 g-font-size-12"><span style="font-weight: normal;">ABOUT US</span></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10"></h2>
            </div>

			<div class="landing-block-node-text g-line-height-1_4"><p><span style="">Company24 creative technology company providing key digital services. Focused on helping our clients to build a successful business on web and mobile.</span></p></div>
        </div>

    </section>',
			],
		'34.3.four_cols_countdown' =>
			[
				'CODE' => '34.3.four_cols_countdown',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pt-70 g-pb-70 g-bg-main">
	<div class="container">
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg animated  col-lg-4">
					<span class="landing-block-card-contact-icon-container m-auto u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-education-024 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-20"><span style="font-weight: bold;">01.</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-20 g-color-primary">consult</div>
				<div class="landing-block-node-card-text mb-0">
					<p>Sed feugiat porttitor nunc Etiam
						gravida ex justo ac rhoncus purus tristique ut.
					</p>
				</div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg animated  col-lg-4">
					<span class="landing-block-card-contact-icon-container m-auto u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-education-073 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-20"><span style="font-weight: bold;">02.</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-20 g-color-primary">CREATE</div>
				<div class="landing-block-node-card-text mb-0">
					<p>Ivitae blandit massa luctus fermentum
						lorem quis elit maximus, vitae
					</p>
				</div>
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg animated  col-lg-4">
					<span class="landing-block-card-contact-icon-container m-auto u-icon-v1 u-icon-size--lg g-mb-15">
						<i class="landing-block-node-card-icon icon-communication-180 u-line-icon-pro"></i>
					</span>
				<h3 class="landing-block-node-card-number mb-0 g-font-size-20"><span style="font-weight: bold;">03.</span></h3>
				<div class="landing-block-node-card-number-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-20 g-color-primary">RELEASE</div>
				<div class="landing-block-node-card-text mb-0">
					<p>Curabitur eget tortor sed urna
						faucibus iaculis id et nulla sed fringilla quam
					</p>
				</div>
			</div>

			
		</div>
	</div>
</section>',
			],
		'32.2.img_one_big' =>
			[
				'CODE' => '32.2.img_one_big',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-100">
	<div class="container ">
		<img class="landing-block-node-img img-fluid w-100 js-animation zoomIn" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1445x750/img1.png" alt="" />
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-py-20 g-pb-50 g-pt-0 js-animation fadeInUp animated g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10"></h2>
            </div>

			<div class="landing-block-node-text g-font-size-24 text-center"><p><span style="">Our end to end suite includes Customer Support, Responsiveness, 1610+ Ui Elements and more.</span></p></div>
        </div>

    </section>',
			],
		'31.1.two_cols_text_img' =>
			[
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-bg-main">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title g-font-weight-700 g-mb-25 g-font-size-22 g-text-transform-none js-animation fadeInUp animated"><span style="font-weight: normal;">Our Vision and Mission</span></h3>
				<div class="landing-block-node-text g-mb-30 g-line-height-1_5 js-animation fadeInUp animated"><p><span style="">We aim high at being focused on building relationships with our clients and community. Working together on the daily requires each individual to let the greater good of the team&amp;#039;s work surface above their own ego.</span></p><p><span style=""><span style="font-size: 1rem;"><span style="font-weight: bold;">31500+</span> </span><span style="font-size: 1rem;">Happy clients all over the world</span></span></p><p><span style=""><span style="font-weight: bold;">&amp;#8470;1</span> <span style="font-size: 1rem;">WrapBootstrap theme of all time</span></span></p><p><span style=""><span style="font-size: 1rem;"><span style="font-weight: bold;">1610+</span> </span><span style="font-size: 1rem;">UI Elements &amp; Features</span></span></p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m g-rounded-50 js-animation fadeInUp animated" href="#" tabindex="0">Contact us
						for more info</a>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-contain-no-repeat" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x535/img1.png\');"></div>
		</div>
	</div>
</section>',
			],
		'28.2.team' =>
			[
				'CODE' => '28.2.team',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-pt-50 g-pb-0">
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-black-opacity-0_7--after g-pt-30 g-pt-80--md g-pb-250" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img5.jpg\');">
		<div class="container text-center u-bg-overlay__inner g-max-width-800">
			<div class="landing-block-node-header text-uppercase g-brd-primary g-mb-30 u-heading-v2-4--bottom">
				<h3 class="landing-block-node-subtitle g-font-weight-600 g-font-size-12 g-color-primary g-mb-20"></h3>
				<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1 g-font-weight-700 g-font-size-30 g-font-size-40--md g-color-white mb-0 g-text-transform-none g-letter-spacing-1"><span style="font-weight: normal;">&quot;If you can design one thing you can design everything. Just believe it&quot;</span></h2>
			</div>

			<div class="landing-block-node-text mb-0 g-color-white-opacity-0_9 g-font-size-17"><p>WHAT ARE YOU WAITING FOR?</p><p><br /></p><p>Join thousands of users making a difference every day using Company24.</p></div>
		</div>
	</div>

	<div class="container g-mt-minus-200">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse  animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img5.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">alex@company24.com</div>

						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none"><span style="font-weight: normal;">Founder</span></em>
					<h4 class="landing-block-node-employee-name g-font-weight-700 g-font-size-18 g-mb-7 g-text-transform-none">Alex Taylor</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse  animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img6.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">kate@company24.com</div>

						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none"><span style="font-weight: normal;">Manager</span></em>
					<h4 class="landing-block-node-employee-name g-font-weight-700 g-font-size-18 g-mb-7 g-text-transform-none">Kate Metu</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse  animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">daniel@company24.com</div>

						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none"><span style="font-weight: normal;">Developer</span></em>
					<h4 class="landing-block-node-employee-name g-font-weight-700 g-font-size-18 g-mb-7 g-text-transform-none">Daniel Wearne</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 col-lg-3 g-mb-30 g-mb-0--lg pulse  animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-mt-0 g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none">
							<div class="landing-block-node-employee-quote text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white g-pointer-events-all">tina@company24.com</div>

						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block g-font-style-normal g-font-weight-700 g-color-primary g-mb-5 g-text-transform-none"><span style="font-weight: normal;">Designer</span></em>
					<h4 class="landing-block-node-employee-name g-font-weight-700 g-font-size-18 g-mb-7 g-text-transform-none">Tina Krueger</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0"></p>
					<!-- End Figure Info-->
				</div>
			</div>
		</div>
		<!-- End Team Block -->
	</div>
</section>',
			],
		'13.2.one_col_fix_button' =>
			[
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block landing-block-node-container text-center g-py-20 g-pb-60 g-pt-20">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50" href="#" g-font-weight-700="" target="_self">JOIN COMPANY24</a>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 g-pt-50 g-pb-50 g-bg-secondary js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-letter-spacing-2 g-font-size-12"><span style="font-weight: normal;">ARE WE A PERFECT FIT?</span></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10"></h2>
            </div>

			<div class="landing-block-node-text g-font-size-24"><p><span style="">Are Company24 re-usability features <span style="">perfect for You?</span></span></p></div>
        </div>

    </section>',
			],
		'06.1features_3_cols' =>
			[
				'CODE' => '06.1features_3_cols',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-80 g-pt-0 g-pb-0 g-bg-secondary">
        <div class="container">

            <!-- Icon Blocks -->
            <div class="landing-block-node-row row justify-content-center no-gutters landing-block-inner">

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-main js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-fire"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none">Marketing &amp; Consulting</h5>
                        <div class="landing-block-node-element-text"><p>This is where we sit down, grab a cup of coffee and dial in the details.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none">24/7 support</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">1610+ elements</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">400+ pages</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">Unlimited domain or user</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-main js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-energy"></i>
                	</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none">SEO &amp; Advertising</h5>
                        <div class="landing-block-node-element-text"><p>Now that we have aligned the details, it is time to get things organized.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none">24/7 support</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">1610+ elements</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">400+ pages</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">Unlimited domain or user</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

                <div class="landing-block-node-element landing-block-card col-md-4 col-lg-4 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in  g-bg-main js-animation fadeInLeft">
                    <!-- Icon Blocks -->
                    <div class="text-center g-px-10 g-px-30--lg g-py-40 g-pt-25--parent-hover g-transition-0_2 g-transition--ease-in">
					<span class="landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15">
					  <i class="landing-block-node-element-icon icon-layers"></i>
					</span>
                        <h5 class="landing-block-node-element-title g-mb-10 g-text-transform-none">Design &amp; Development</h5>
                        <div class="landing-block-node-element-text"><p>This is where we begin to visualize your sketches and make them into beautiful pixels.</p></div>

                        <div class="landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15"></div>

                        <ul class="landing-block-node-element-list list-unstyled text-uppercase g-mb-0"><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none">24/7 support</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">1610+ elements</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">400+ pages</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-color-gray-dark-v3">Unlimited domain or user</li></ul>
                    </div>
                    <!-- End Icon Blocks -->
                </div>

            </div>
            <!-- End Icon Blocks -->
        </div>
    </section>',
			],
		'13.2.one_col_fix_button@2' =>
			[
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block landing-block-node-container text-center g-py-20 g-bg-secondary g-pb-60 g-pt-20">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-px-15 g-rounded-50" href="#" g-font-weight-700="" target="_self">Purchase Company24</a>
        </div>
    </section>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-py-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x500/img2.jpg\');">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown animated">
		<h2 class="landing-block-node-title g-font-weight-700 g-font-size-30 g-color-white g-mb-20 g-text-transform-none g-line-height-1_8"><p style="text-align: center;"><span style="color: rgb(77, 182, 172);"><span style="background-color: initial;">75</span><span style="background-color: initial;"> clients</span></span></p><p style="text-align: center;"><span style="color: rgb(77, 182, 172);">20 products</span></p><p style="text-align: center;"><span style="color: rgb(77, 182, 172);">15 members</span></p><p style="text-align: center;"><span style="color: rgb(77, 182, 172);">50 projects</span></p></h2>

		<div class="landing-block-node-text g-mb-35 g-color-white">
			<a href="#" target="_self">VIEW ALL OUR WORKS</a>
		</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15 g-color-white" target="_self">VIEW ALL OUR WORKS</a>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 g-pt-50 g-pb-50 js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-line-height-0_7 g-letter-spacing-2 g-font-size-12"><span style="font-weight: normal;">WE HAVE DONE SOME AMAZING JOBS</span></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10"></h2>
            </div>

			<div class="landing-block-node-text g-font-size-24"><p><span style="">Experience a level of quality in both design &amp; customization.</span></p></div>
        </div>

    </section>',
			],
		'20.2.three_cols_fix_img_title_text' =>
			[
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img1.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-mb-20 g-text-transform-none text-center g-line-height-0 g-font-size-20"><span style="font-weight: normal;">Branding work</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Identity, Design</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img2.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-mb-20 g-text-transform-none text-center g-line-height-0 g-font-size-20"><span style="font-weight: normal;">Development</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Design</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img3.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-mb-20 g-text-transform-none text-center g-line-height-0 g-font-size-20"><span style="font-weight: normal;">Project planner</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Graphic, Identity</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'20.2.three_cols_fix_img_title_text@2' =>
			[
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img4.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-text-transform-none text-center g-line-height-0"><span style="font-weight: normal;">Design</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Graphic</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img15.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-text-transform-none text-center g-line-height-0"><span style="font-weight: normal;">Creative agency</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Identity</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img16.jpg" alt="" />

				<h3 class="landing-block-node-title g-font-weight-700 g-font-size-18 g-mb-20 g-text-transform-none text-center g-line-height-0"><span style="font-weight: normal;">Production</span></h3>
				<div class="landing-block-node-text text-center g-line-height-0"><p>Graphic</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'13.2.one_col_fix_button@3' =>
			[
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block landing-block-node-container text-center g-py-20 g-pb-60 g-pt-20">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50" href="#" g-font-weight-700="" target="_self">VIEW ALL COMPANY24 WORKS</a>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@5' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-py-20 g-pt-50 g-pb-50 g-bg-secondary js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-letter-spacing-2 g-font-size-12"><span style="font-weight: normal;">NEWS BLOG</span></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10"></h2>
            </div>

			<div class="landing-block-node-text g-font-size-24"><p><span style="">Read the latest news and blogs.</span></p></div>
        </div>

    </section>',
			],
		'30.3.four_cols_fix_img_and_links' =>
			[
				'CODE' => '30.3.four_cols_fix_img_and_links',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block g-bg-secondary g-pt-30 g-pb-20">

        <div class="container">
            <div class="row">

                <div class="landing-block-card col-sm-6 col-md-3 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x500/img1.jpg" alt="" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">Announcing a free plan for small teams</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-3 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x500/img2.jpg" alt="" />

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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">Exclusive interview with CEO</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-3 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x500/img3.jpg" alt="" />

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
									<a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">Vs Barbershop Opening First Florida Location</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
							</div>
						</div>
					</article>
				</div>


				<div class="landing-block-card col-sm-6 col-md-3 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/400x500/img4.jpg" alt="" />

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
									<a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">The Hypnotic Allure of Cinemagraphic Waves</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'13.2.one_col_fix_button@4' =>
			[
				'CODE' => '13.2.one_col_fix_button',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block landing-block-node-container text-center g-py-20 g-bg-secondary g-pt-20 g-pb-60">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-rounded-50" href="#" g-font-weight-700="" target="_self">READ MORE NEWS</a>
        </div>
    </section>',
			],
		'12.image_carousel_6_cols_fix' =>
			[
				'CODE' => '12.image_carousel_6_cols_fix',
				'SORT' => '10000',
				'CONTENT' => ' <section class="landing-block js-animation zoomIn text-center g-pt-20 g-pb-20">
        <div class="container g-px-35 g-px-0--md">

            <div class="js-carousel row"
                 data-autoplay="true"
				 data-pause-hover="true"
                 data-infinite="true"
                 data-slides-show="6"
				 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-30 g-color-gray-light-v1"
				 data-arrow-left-classes="fa fa-angle-left g-left-minus-35"
				 data-arrow-right-classes="fa fa-angle-right g-right-minus-35"
				 data-responsive=\'[{
					 "breakpoint": 1200,
					 "settings": {
					   "slidesToShow": 5
					 }
				   }, {
					 "breakpoint": 992,
					 "settings": {
					   "slidesToShow": 4
					 }
				   }, {
					 "breakpoint": 768,
					 "settings": {
					   "slidesToShow": 3
					 }
				   }, {
					 "breakpoint": 576,
					 "settings": {
					   "slidesToShow": 2
					 }
				   }]\'
				 data-init-classes-exclude=\'[{
					 "selector": ".landing-block-card-carousel-item",
					 "class": "col-6 col-sm-4 col-lg-2 justify-content-center"
				   }, {
					 "selector": ".js-carousel",
					 "class": "row"
				   }]\'>
                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img1.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img2.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img3.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img4.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img5.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img6.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img7.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img8.png" alt="">
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-transition-0_2 d-flex g-px-10 col-6 col-sm-4 col-lg-2 justify-content-center align-items-center h-auto">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md g-brd-around g-brd-gray-light-v1--hover" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x150/img9.png" alt="">
					</a>
                </div>
            </div>

        </div>
    </section>',
			],
		'29.three_cols_texts_blocks_slider' =>
			[
				'CODE' => '29.three_cols_texts_blocks_slider',
				'SORT' => '10500',
				'CONTENT' => '<section class="landing-block js-animation fadeIn">

	<div class="container g-py-40">

		<div class="js-carousel g-pb-60 row"
			 data-infinite="true"
			 data-autoplay="true"
			 data-pause-hover="true"
			 data-speed="7000"
			 data-lazy-load="progressive"
			 data-slides-show="3"
			 data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-0 text-center"
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
                 "breakpoint": 768,
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
				 "selector": ".landing-block-card-slider-element",
				 "class": "col-12 col-sm-6 col-lg-4"
			   }, {
				 "selector": ".js-carousel",
				 "class": "row"
			   }]\'>
			<div class="landing-block-card-slider-element js-slide g-px-15 mb-1 col-12 col-sm-6 col-lg-4">
				<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
					Dear Company24 team, I just bought your template some weeks ago. The template is really nice and
					offers quite a large set of options.
				</blockquote>
				<div class="media">
					<img class="landing-block-node-element-img g-height-50 d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg" alt="">
					<div class="media-body align-self-center">
						<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Alex
							Pottorf</h4>
						<div class="landing-block-node-element-subtitle g-font-size-13">
							<span class="d-block">Reason: Template Quality</span>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-card-slider-element js-slide g-px-15 mb-1 col-12 col-sm-6 col-lg-4">
				<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
					Hi there purchased a couple of days ago and the site looks great, big thanks to the Company24
					guys, they gave me some great help with some fiddly setup issues.
				</blockquote>
				<div class="media">
					<img class="landing-block-node-element-img g-height-50 d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img5.jpg" alt="">
					<div class="media-body align-self-center">
						<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Bastien
							Rojanawisut</h4>
						<div class="landing-block-node-element-subtitle g-font-size-13">
							<span class="d-block">Reason: Template Quality</span>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-card-slider-element js-slide g-px-15 mb-1 col-12 col-sm-6 col-lg-4">
				<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
					The website package made my life easier. I will advice programmers to buy it even it cost 140$ -
					because it shorten hunderds of hours in front of your pc designing your layout.
				</blockquote>
				<div class="media">
					<img class="landing-block-node-element-img g-height-50 d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg" alt="">
					<div class="media-body align-self-center">
						<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">
							Massalha Shady</h4>
						<div class="landing-block-node-element-subtitle g-font-size-13">
							<span class="d-block">Reason: Code Quality</span>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-card-slider-element js-slide g-px-15 mb-1 col-12 col-sm-6 col-lg-4">
				<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
					New website template looks great!. Love the multiple layout examples for Shortcodes and the new
					Show code Copy code snippet feature is brilliant
				</blockquote>
				<div class="media">
					<img class="landing-block-node-element-img g-height-50 d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img4.jpg" alt="">
					<div class="media-body align-self-center">
						<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Mark
							Mcmanus</h4>
						<div class="landing-block-node-element-subtitle g-font-size-13">
							<span class="d-block">Reason: Code Quality</span>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-card-slider-element js-slide g-px-15 mb-1 col-12 col-sm-6 col-lg-4">
				<blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25">
					Great templates, I\'m currently using them for work. It\'s beautiful and the coding is done
					quickly and seamlessly. Thank you!
				</blockquote>
				<div class="media">
					<img class="landing-block-node-element-img g-height-50 d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img3.jpg" alt="">
					<div class="media-body align-self-center">
						<h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">Zuza
							Muszyska</h4>
						<div class="landing-block-node-element-subtitle g-font-size-13">
							<span class="d-block">Reason: Company24 Quality</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</section>',
			],
		'33.1.form_1_transparent_black_left_text' =>
			[
				'CODE' => '33.1.form_1_transparent_black_left_text',
				'SORT' => '11000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img4.jpg);">

	<div class="container g-z-index-1 g-pos-rel">
		<div class="row align-items-center">

			<div class="col-md-4 g-mb-60">
				<h2 class="landing-block-node-main-title landing-semantic-title-medium js-animation fadeInUp h1 g-color-white mb-4">Contact Us</h2>

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
			],
		'17.copyright' =>
			[
				'CODE' => '17.copyright',
				'SORT' => '11500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="text-center g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12 js-animation animation-none">
				<p>&copy; 2021 All rights reserved.</p>
			</div>
		</div>
	</div>
</section>',
			],
	],
];









