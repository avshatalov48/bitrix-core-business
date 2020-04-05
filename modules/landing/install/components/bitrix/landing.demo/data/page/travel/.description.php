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
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/travel/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
		),
	),
	'items' => array (
		'0.menu_19_travel' =>
			array (
				'CODE' => '0.menu_19_travel',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-ui-pattern-transparent landing-block-menu u-header u-header--floating">
	<div class="u-header__section g-bg-black-opacity-0_5 g-bg-transparent--lg g-transition-0_3 g-py-12"
		 data-header-fix-moment-exclude="g-bg-black-opacity-0_5 g-bg-transparent--lg g-py-12"
		 data-header-fix-moment-classes="g-theme-travel-bg-black-v1-opacity-0_8 g-py-7">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0" target="_self">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/travel-logo.png" alt="" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Our
								tours</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Popular
								tours</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[01.big_with_text_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Discount</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Contacts</a>
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
		'43.4.cover_with_price_text_button_bgimg' =>
			array (
				'CODE' => '43.4.cover_with_price_text_button_bgimg',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden" data-autoplay="true" data-infinite="true" data-fade="true" data-speed="5000" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
		
		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img19.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$150.00</span></div>
						
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Karlovy Vary, Czech
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac
									massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
								<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img11.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$550.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							London, Great Britain
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus.
									Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
								<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img20.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$360.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Crete, Greece
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
								<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img17.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$1300.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Langkwai, Malaysia
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
								<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img14.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$1300.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Bavaria, Germany
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
								<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img13.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$610.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Paris, France
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante.
									Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur
									tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img18.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$340.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Hong Kong
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante.
									Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur
									tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img15.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$2400.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Venice, Italy
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img16.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$540.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							Madrid, Spain
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide">
			<div class="landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh g-bg-black-opacity-0_4--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img12.jpg)">
				<div class="u-bg-overlay__inner">
					<div class="landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800" data-stop-propagation>
						<div class="landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10">
							Only From
							<span style="color: #ee4136;">$5240.00</span></div>
	
						<h3 class="landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-font-size-75 g-font-roboto-slab g-color-white g-mb-10">
							New York, USA
						</h3>
	
						<div class="g-pos-rel g-line-height-1_2 g-max-width-550">
							<div class="landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8">
								<p>
									Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor.
								</p>
							</div>
							<div class="landing-block-node-card-button-container">
							<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Take a tour</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			),
		'04.1.one_col_fix_with_title' =>
			array (
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-bg-gray-light-v4 js-animation fadeInUp animated g-pb-20">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle h6 g-font-weight-800 g-letter-spacing-1 g-mb-20 g-color-black g-font-size-14">OUR TOURS</h4>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-color-primary"><span style="">WE CAN</span> <span style="color: rgb(33, 33, 33);">OFFER</span></h2>
            </div>
        </div>
    </section>',
			),
		'44.4.slider_5_cols_with_prices' =>
			array (
				'CODE' => '44.4.slider_5_cols_with_prices',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v4 g-pt-90 g-pb-90">
	<!-- Product Blocks -->
	<div class="js-carousel g-px-25 row"
		 data-infinite="true"
		 data-slides-show="5"
		 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-default g-color-white g-bg-primary g-color-primary--hover g-bg-white--hover"
		 data-arrow-left-classes="fa fa-chevron-left g-left-0"
		 data-arrow-right-classes="fa fa-chevron-right g-right-0"
		 data-responsive=\'[{
               "breakpoint": 1200,
               "settings": {
                 "slidesToShow": 5
               }
             }, {
               "breakpoint": 992,
               "settings": {
                 "slidesToShow": 3
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
				 "selector": ".landing-block-node-card",
				 "class": "col-12 col-sm-4 col-lg-3"
			   }, {
				 "selector": ".js-carousel",
				 "class": "row"
			   }]\'>
		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Crete</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img2.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Langkawi</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img3.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Paris</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img4.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Venice</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img	5.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							London</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Rome</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Crete</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img2.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Langkawi</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img3.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Paris</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img4.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Venice</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							London</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>

		<div class="landing-block-node-card js-slide g-px-7 col-12 col-sm-4 col-lg-3">
			<div class="landing-block-node-card-container h-100 g-pos-rel g-text-underline--none--hover g-parent g-theme-travel-bg-black-v1 g-bg-primary--hover g-color-white g-transition-0_3"
				 href="#">
				<img class="landing-block-node-card-img img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg"
					 alt="">

				<div class="g-pa-20">
					<div class="g-mb-20">
						<h3 class="landing-block-node-card-title js-animation fadeInRight text-uppercase g-font-weight-700 g-font-roboto-slab g-font-size-default g-color-white g-mb-5">
							Rome</h3>

						<div class="landing-block-node-card-text js-animation fadeInLeft g-color-white-opacity-0_8">
							<p>dapibus quis sapien id phar etra iaculis est</p>
						</div>
					</div>

					<div class="landing-block-node-card-price js-animation fadeInRight d-inline-block g-line-height-1 g-font-weight-700 g-font-size-11 g-bg-primary g-color-primary--parent-hover g-bg-white--parent-hover g-pa-10"
						 href="#">$350.00
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Product Blocks -->
</section>
',
			),
		'04.7.one_col_fix_with_title_and_text_2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-bg-white js-animation fadeInUp animated g-pb-20">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-color-black g-font-size-14">POPULAR TOUR</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-primary">SOMETHING <span style="color: rgb(33, 33, 33);">INTERESTING</span></h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v3"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			),
		'44.5.three_cols_images_with_price' =>
			array (
				'CODE' => '44.5.three_cols_images_with_price',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-pt-90 g-pb-90">
	<div class="container">
		<!-- Product Blocks -->
		<div class="row">
			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img1.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$780.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Hong Kong</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									King Way
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									1 person, 4 days, 3 nights, 3 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img2.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$2350.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Venice</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Relax tour
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 7 days, 7 nights, 5 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img3.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$4320.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Karlovy Vary</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Heaven on Earth
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 14 days, 15 nights, 5 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img4.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$760.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Madrid</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Lovers tour
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 4 days, 5 nights, 5 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img5.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$2440.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Bavaria</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Road of gladiators
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 4 days, 5 nights, 4 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img6.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$2530.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">New York</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Road of gods
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 4 days, 5 nights, 4 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>
		</div>
		<!-- End Product Blocks -->
	</div>
</section>
',
			),
		'01.big_with_text_3' =>
			array (
				'CODE' => '01.big_with_text_3',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x534/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-mb-20 g-font-size-60 g-color-primary">GET 10% <span style="color: rgb(245, 245, 245);">DISCOUNT</span></h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35">Donec eleifend mauris eu leo varius consectetur. Aliquam luctus a lorem ac rutrum. Cras in nulla id mi ornare vestibulum. Donec et magna nulla. Pellentesque ut ipsum id nibh pretium blandit quis ac erat.</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn btn-xl u-btn-primary text-uppercase g-font-weight-700 g-font-size-12 g-py-15 g-px-40 g-mb-15 rounded-0" target="_self">CONTACT US</a>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pb-20 g-bg-white g-pt-60 js-animation fadeInUp animated">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-color-black g-font-size-14">SPECIAL OFFERS</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-color-primary g-font-size-40">DON&amp;#039;T MISS <span style="color: rgb(33, 33, 33);">THIS</span></h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v3"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			),
		'44.5.three_cols_images_with_price@2' =>
			array (
				'CODE' => '44.5.three_cols_images_with_price',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-90 g-pb-90">
	<div class="container">
		<!-- Product Blocks -->
		<div class="row">
			<div class="landing-block-node-card js-animation fadeInUp col-lg-6 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img1.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$780.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Hong Kong</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									King Way
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									1 person, 4 days, 3 nights, 3 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-lg-6 col-md-6 g-mb-30">
				<!-- Article -->
				<div class="landing-block-node-card-container u-bg-overlay g-pointer-events-before-after-none h-100 g-bg-black-opacity-0_3--after g-parent g-text-underline--none--hover"
				   href="#">
					<img class="landing-block-node-card-bgimg h-100 w-100 g-object-fit-cover img-fluid" src="https://cdn.bitrix24.site/bitrix/images/landing/business/740x480/img2.jpg" alt="">

					<!-- Article Content -->
					<div class="u-bg-overlay__inner g-pointer-events-none--public-mode g-pos-abs g-top-0 g-left-0 w-100 h-100 g-pa-10">
						<div class="landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8"></div>
						<div class="landing-block-node-card-inner-container g-pa-20 g-pos-abs g-top-0 g-left-0 h-100 g-flex-middle">
							<div class="g-flex-middle-item--top g-pointer-events-all">
								<div class="landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5"
										href="#">From
									<span style="color: #ee4136;">$2350.00</span>
								</div>
							</div>

							<div class="text-uppercase g-flex-middle-item--bottom g-pointer-events-all">
								<div class="landing-block-node-card-subtitle g-font-roboto-slab g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10">Venice</div>
								<h3 class="landing-block-node-card-title g-font-roboto-slab h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
									Relax tour
								</h3>
								<div class="landing-block-node-card-text small g-color-white-opacity-0_8">
									2 persons, 7 days, 7 nights, 5 stars hotel
								</div>
							</div>
						</div>
					</div>
					<!-- End Article Content -->
				</div>
				<!-- End Article -->
			</div>

		</div>
		<!-- End Product Blocks -->
	</div>
</section>
',
			),
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-bg-gray-light-v5 js-animation fadeInUp animated g-pb-20">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-color-black g-font-size-14">OUR SERVICES</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-primary">WHAT WE <span style="color: rgb(33, 33, 33);">OFFER</span></h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v3"><p>Donec eleifend mauris eu leo varius consectetur. Aliquam luctus a lorem ac rutrum. Cras in nulla id mi ornare vestibulum. Donec et magna nulla. Pellentesque ut ipsum id nibh pretium blandit quis ac erat.</p></div>
        </div>

    </section>',
			),
		'44.3.four_columns_text_with_img' =>
			array (
				'CODE' => '44.3.four_columns_text_with_img',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block w-100 no-gutters">
	<div class="js-carousel carouselTravel1 u-carousel-v5" data-infinite="true" data-slides-scroll="true" data-slides-show="4" data-arrows-classes="u-arrow-v1 g-pos-abs g-top-100 g-width-45 g-height-45 g-font-size-default g-color-white g-bg-primary g-color-primary--hover g-bg-white--hover" data-arrow-left-classes="fa fa-chevron-left g-left-0" data-arrow-right-classes="fa fa-chevron-right g-right-0">

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img5.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Nullam lobortis bibendum eros nec ultricies</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>Cras sit amet varius velit. Maecenas porta
							condimentum tortor at sagittis. Cum sociis natoque penatibus et magnis dis
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img6.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Aliquam dapibus quis sapien id pharetra</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>In finibus vehicula lorem, in tempor diam convallis
							non. Curabitur vel risus vitae urna auctor aliquam.
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img7.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Duis sagittis purus a pretium tincidunt</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>Cras sit amet varius velit. Maecenas porta
							condimentum tortor at sagittis. Cum sociis natoque penatibus et magnis dis
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img8.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Nullam lobortis bibendum eros nec ultricies</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>Maecenas tempor arcu eget gravida sagittis. In
							hendrerit libero ligula, ac pharetra libero dapibus id. Cras iaculis purus sit
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img5.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Aliquam dapibus quis sapien id pharetra</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>In finibus vehicula lorem, in tempor diam convallis
							non. Curabitur vel risus vitae urna auctor aliquam.
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>

		<div class="landing-block-node-card js-slide g-theme-travel-bg-black-v1 g-bg-primary--hover ">
			<article class="u-shadow-v26 g-parent g-transition-0_2 g-transition--ease-in">
				<img class="landing-block-node-card-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/570x436/img6.jpg" alt="" />

				<div class="text-center g-pa-45 landing-block-node-card-text-container js-animation fadeInUp">
					<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-default g-font-roboto-slab g-color-white g-mb-15">
						Aliquam dapibus quis sapien id pharetra</h3>
					<div class="landing-block-node-card-text g-color-white-opacity-0_8 g-mb-35">
						<p>In finibus vehicula lorem, in tempor diam convallis
							non. Curabitur vel risus vitae urna auctor aliquam.
						</p>
					</div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 rounded-0 g-py-10 g-px-25" href="#">Learn more</a>
					</div>
				</div>
			</article>
		</div>
	</div>
</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-60 g-bg-white js-animation fadeInUp animated g-pb-20">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-font-size-14 g-color-black">OUR GALLERY</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-primary">INTERESTING <span style="color: rgb(33, 33, 33);">SHOTS</span></h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v3"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			),
		'32.3.img_grid_1_2cols_1_no_gutters' =>
			array (
				'CODE' => '32.3.img_grid_1_2cols_1_no_gutters',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">
		<div class="row no-gutters js-gallery-cards">

			<div class="col-12 col-md-6 g-min-height-540 g-max-height-810">
				<div class="h-100">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img-big img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img20.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 g-min-height-540 g-max-height-810">
				<div class="h-50">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img-small img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img19.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
				</div>

				<div class="h-50">
					<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover">
						<img data-fancybox="gallery" class="landing-block-node-img-small img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img18.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</div>
				</div>
			</div>
		</div>
</section>',
			),
		'32.5.img_grid_3cols_1_wo_gutters' =>
			array (
				'CODE' => '32.5.img_grid_3cols_1_wo_gutters',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-center js-animation fadeInLeft">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-center js-animation fadeInLeft">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-4">
			<div class="h-100">
				<div class="landing-block-node-img-container h-100 g-pos-rel g-parent u-block-hover landing-block-node-img-container-center js-animation fadeInLeft">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x600/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
				</div>
			</div>
		</div>

	</div>

</section>',
			),
		'04.7.one_col_fix_with_title_and_text_2@5' =>
			array (
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-60 g-bg-gray-light-v4 js-animation fadeInUp animated g-pb-20">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-mb-15 g-font-size-14 g-color-black">Testimonials</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-primary">CUSTOMERS <span style="">say</span></h2>
            </div>

			<div class="landing-block-node-text g-color-gray-dark-v3"><p>Vestibulum at turpis enim. Aliquam dapibus quis sapien id pharetra. Vivamus iaculis est vitae libero tempus, in sollicitudin est consectetur porttitor iaculis pretium</p></div>
        </div>

    </section>',
			),
		'23.big_carousel_blocks' =>
			array (
				'CODE' => '23.big_carousel_blocks',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block js-animation g-pt-20 g-bg-gray-light-v4 fadeIn animated g-pb-60">

         <div class="js-carousel"
             data-autoplay="true"
			 data-pause-hover="true"
             data-infinite="true"
			 data-speed="10000"
             data-slides-show="4"
             data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-60 g-font-size-60 g-color-white g-bg-primary"
			 data-arrow-left-classes="fa fa-angle-left g-left-10"
			 data-arrow-right-classes="fa fa-angle-right g-right-10"
			 data-responsive=\'[{
               "breakpoint": 1200,
               "settings": {
                 "slidesToShow": 4
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
             }]\'>
            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img1.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">Simone Gomez</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						Anderson industry</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">Carla Harris</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						HNN consultation corp</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img3.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">Dianna Kimwealth</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						Robo construction</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img4.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">John Wellberg</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						Solid iron corp</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img6.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">Derek Fineman</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						Fineman construction company
					</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img7.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-default mb-0">William Mountcon</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10 g-color-gray-dark-v5">
						Mountcon brothers</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-color-gray-dark-v5 g-bg-primary--before mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>
			
        </div>

    </section>',
			),
		'27.one_col_fix_title_and_text_2' =>
			array (
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-bg-primary g-pt-60 js-animation fadeInUp animated g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400 g-font-size-14 g-color-white"><span style="font-weight: bold;">CONTACT US</span></h2>
                <div class="landing-block-node-text g-font-size-40 g-color-white"><p><span style="font-weight: bold;">GET IN <span style="">TOUCH</span></span></p></div>
            </div>
        </div>

    </section>',
			),
		'33.23.form_2_themecolor_no_text' =>
			array (
				'CODE' => '33.23.form_2_themecolor_no_text',
				'SORT' => '8500',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-bg-primary g-pt-20 g-pb-60">

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
				'SORT' => '9000',
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