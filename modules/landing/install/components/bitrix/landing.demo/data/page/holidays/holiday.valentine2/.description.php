<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_VALENTINE2_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_VALENTINE2_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'travel',

		    'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.valentine2/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_VALENTINE2_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_VALENTINE2_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_VALENTINE2_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_VALENTINE2_DESCRIPTION')
		]
	],
	'available' => true,
	'items' => [
		'0.menu_04' =>
			[
				'CODE' => '0.menu_04',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float" >
	<div class="u-header__section g-bg-black-opacity-0_4 g-transition-0_3 g-py-8 g-py-17--md" data-header-fix-moment-exclude="g-bg-black-opacity-0_4 g-py-17--md" data-header-fix-moment-classes="u-theme-architecture-shadow-v1 g-bg-white g-py-10--md">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<a href="#" class="landing-block-node-menu-logo-link-small g-hidden-lg-up navbar-brand mr-0 p-0" target="_self">
					<img class="landing-block-node-menu-logo-small d-block g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-white.png" alt="" data-header-fix-moment-exclude="d-block" data-header-fix-moment-classes="d-none" />

					<img class="landing-block-node-menu-logo-small-2 d-none g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-black.png" alt="" data-header-fix-moment-exclude="d-none" data-header-fix-moment-classes="d-block" />
				</a>

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav align-items-lg-center text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg mx-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-30--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.two_col_with_titles]" class="landing-block-node-menu-list-item-link nav-link p-0">Home</a></li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[21.3.two_cols_big_bgimg_title_text_button]" class="landing-block-node-menu-list-item-link nav-link p-0">Services</a>
						</li>
						

						<!-- Logo -->
						<li class="g-hidden-md-down nav-logo-item g-mx-15--lg">
							<a href="#team" class="landing-block-node-menu-logo-link navbar-brand mr-0" target="_self">
								<img class="landing-block-node-menu-logo d-block g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-white.png" alt="" data-header-fix-moment-exclude="d-block" data-header-fix-moment-classes="d-none" data-filehash="1f93fef6cfc352d763bd97c358efc5d2" />

								<img class="landing-block-node-menu-logo-2 d-none g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/valentine-logo-black.png" alt="" data-header-fix-moment-exclude="d-none" data-header-fix-moment-classes="d-block" data-filehash="bb21fd9632f1997bd4a83c6a9f06f301" />
							</a>
						</li>
						<!-- End Logo -->

						
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[43.2.three_tiles_with_img_zoom]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-30--lg">
							<a href="#block@block[04.1.one_col_fix_with_title@2]" class="landing-block-node-menu-list-item-link nav-link p-0">Contacts</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto g-flex-centered-item--center" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navBar" data-toggle="collapse" data-target="#navBar">
                <span class="hamburger hamburger--slider hamburger--md">
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
		'01.two_col_with_titles' =>
			[
				'CODE' => '01.two_col_with_titles',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block">
	<div class="container-fluid">
		<div class="row">
			<div class="landing-block-node-img col-md-6 u-bg-overlay g-bg-img-hero d-flex align-items-center g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/700x800/img3.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="landing-block-node-inner-container g-flex-centered w-100 g-py-40 g-pr-50--md text-md-right">
					<div class="w-100 u-bg-overlay__inner g-pt-100 g-pb-100 js-animation landing-block-node-inner-container-left landing-block-node-inner-container-right fadeInLeft">
						<h4 class="landing-block-node-small-title landing-block-node-small-title-left g-font-weight-700 g-mb-20 g-color-white g-line-height-1_3 g-letter-spacing-5">You can find</h4>
	
						<div class="landing-block-node-title-container g-brd-left g-brd-left-none--md g-brd-right--md g-brd-7 g-brd-primary g-color-white g-pl-30 g-pr-30 g-pl-0--lg g-mb-30">
							<h2 class="landing-block-node-title landing-block-node-title-left g-line-height-0_9 g-font-weight-700 g-font-size-76 mb-0 g-text-transform-none"><span style="font-style: italic;">Gifts for her</span></h2>
						</div>
						<div class="landing-block-node-button-container">
							<a class="btn g-btn-type-solid g-btn-size-sm g-btn-px-l mx-2 landing-block-node-button g-rounded-50 g-btn-primary" href="#">Read more</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 u-bg-overlay g-bg-img-hero d-flex align-items-center g-bg-black-opacity-0_5--after" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/700x800/img4.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
				<div class="landing-block-node-inner-container g-flex-centered w-100 g-py-40 g-pl-50--md">
					<div class="w-100 u-bg-overlay__inner g-pt-100 g-pb-100 js-animation landing-block-node-inner-container-right fadeInRight">
						<h4 class="landing-block-node-small-title landing-block-node-small-title-left g-font-weight-700 g-mb-20 g-color-white g-line-height-1_3 g-letter-spacing-5">You can find</h4>
						
						<div class="landing-block-node-title-container g-brd-left g-brd-7 g-brd-primary g-color-white g-pl-30 g-mb-30">
							<h2 class="landing-block-node-title landing-block-node-title-left g-line-height-0_9 g-font-weight-700 g-font-size-76 mb-0 g-text-transform-none"><span style="font-style: italic;">Gifts for him</span></h2>
						</div>
						<div class="landing-block-node-button-container">
							<a class="btn g-btn-type-solid g-btn-size-sm g-btn-px-l mx-2 landing-block-node-button g-rounded-50 g-btn-primary" href="#">Read more</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-20 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">Our services</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="font-style: italic;">What we do</span></h2>
            </div>
        </div>
    </section>',
			],
		'19.1.two_cols_fix_img_text_blocks' =>
			[
				'CODE' => '19.1.two_cols_fix_img_text_blocks',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-20">
        <div class="container">
            <div class="row">

                <div class="col-md-5 g-mb-30 g-mb-0--md">
                    <img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x562/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h3 class="landing-block-node-title g-font-weight-700 g-mb-20 g-text-transform-none g-theme-event-color-gray-dark-v1 g-font-size-33"><span style="font-style: italic;">Working since 2011</span></h3>
                    <div class="landing-block-node-text">
						<p class="mb-0">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
					</div>
                </div>

                <div class="col-md-7 g-mb-15 g-mb-0--md">
                    <div aria-multiselectable="true">
                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
								<div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 icon-diamond"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title g-font-size-18 text-uppercase">Who we are</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. </p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 icon-present"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title g-font-size-18 text-uppercase">Our history</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Food truck quinoa nesciunt laborum eiusmod. </p></div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img g-valign-middle g-font-size-23 g-mr-10 icon-heart"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title g-font-size-18 text-uppercase">Our services</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text"><p>3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. </p></div>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </div>

            </div>
        </div>
    </section>',
			],
		'21.3.two_cols_big_bgimg_title_text_button' =>
			[
				'CODE' => '21.3.two_cols_big_bgimg_title_text_button',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
       <div class="row no-gutters g-overflow-hidden landing-block-inner">

			<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img5.jpg);">
				<div class="text-center u-bg-overlay__inner">
					<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-28 js-animation fadeIn"><span style="font-style: italic;">Flowers</span></h3>
					<div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn"><p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p></div>
					<div class="landing-block-node-button-container">
						<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2 g-btn-primary g-rounded-50 js-animation fadeIn" href="#" target="_self">READ MORE</a>
					</div>
				</div>
            </div>

			<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-img-hero row no-gutters align-items-center justify-content-center g-bg-black-opacity-0_6--after u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img6.jpg);">
					<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-28 js-animation fadeIn"><span style="font-style: italic;">travelling</span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn"><p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p></div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2 g-btn-primary g-rounded-50 js-animation fadeIn" href="#" target="_self">READ MORE</a>
                    	</div>
                    </div>
            </div>

        </div>
    </section>',
			],
		'21.3.two_cols_big_bgimg_title_text_button@2' =>
			[
				'CODE' => '21.3.two_cols_big_bgimg_title_text_button',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
       <div class="row no-gutters g-overflow-hidden landing-block-inner">

            <div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img7.jpg);">
				<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-28 js-animation fadeIn"><span style="font-style: italic;">plush</span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn">
							<p>1At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2 g-btn-primary g-rounded-50 js-animation fadeIn" href="#" target="_self">Read more</a>
                		</div>
                </div>
            </div>

            <div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img8.jpg);">
				<div class="text-center u-bg-overlay__inner">
                        <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-28 js-animation fadeIn"><span style="font-style: italic;">Candies</span></h3>
                        <div class="landing-block-node-text g-color-white-opacity-0_7 js-animation fadeIn">
							<p>2At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
                        <div class="landing-block-node-button-container">
                        	<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2 g-btn-primary g-rounded-50 js-animation fadeIn" href="#" target="_self">Read more</a>
                		</div>
                </div>
            </div>
			
        </div>
    </section>',
			],
		'18.two_cols_fix_img_text_button' =>
			[
				'CODE' => '18.two_cols_fix_img_text_button',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-bg-primary g-bg-primary-opacity-0_8--after g-pt-40 g-pb-40">
	<div class="container text-center text-lg-left g-color-white">
		<div class="row g-flex-centered">
			<div class="col-lg-3 offset-lg-1">
				<img class="landing-block-node-img img-fluid g-width-200 g-width-auto--lg g-mb-30 g-mb-0--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/874x600/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
			</div>

			<div class="col-lg-6 u-bg-overlay__inner g-flex-centered">
				<div class="w-100">
					<h2 class="landing-block-node-title g-line-height-1_1 g-font-weight-700 g-mb-10 g-text-transform-none g-font-size-38 js-animation fadeInDown"><span style="font-style: italic;">
						Need to do calculations?</span></h2>
					<div class="landing-block-node-text g-line-height-1_2 g-font-size-18 g-color-white js-animation fadeIn">
						<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
							laudantium, totam rem aperiam, eaque ipsa quae ab illo.</p>
					</div>
				</div>
			</div>

			<a class="landing-block-node-button btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m mx-2 g-flex-centered g-flex-right--lg g-btn-white g-color-primary g-rounded-50 js-animation fadeInUp" href="#" target="_self">Get a quote</a>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pb-20 g-pt-40 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">Testimonials</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="font-style: italic;">What our clients say</span></h2>
            </div>

			<div class="landing-block-node-text g-font-size-15">
            	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
			</div>
        </div>

    </section>',
			],
		'23.big_carousel_blocks' =>
			[
				'CODE' => '23.big_carousel_blocks',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block js-animation g-bg-gray-light-v5 g-py-20 fadeIn">

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

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Simone</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought flower set</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Carla</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought candies set</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img3.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Dianna</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought travel certificate</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img4.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">John</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought flower set</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">SaraH</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought candies set</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img6.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Derek</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought flower set</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img7.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">William</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Bought travel certificate</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

        </div>

    </section>',
			],
		'43.2.three_tiles_with_img_zoom' =>
			[
				'CODE' => '43.2.three_tiles_with_img_zoom',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-pt-80 g-pb-80">
	<div class="container">
		<div class="row align-items-stretch">
			<div class="col-md-6 col-lg-4 g-mb-30 g-mb-0--lg">
				<article class="landing-block-node-block h-100 js-animation fadeInUp text-center u-block-hover u-bg-overlay g-color-white g-bg-black-opacity-0_3--after">
					<div class="landing-block-node-img1 h-100 w-100 g-bg-img-hero u-block-hover__main--zoom-v1"
						style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/800x867/img4.jpg);"
					></div>

					<div class="u-block-hover__additional u-bg-overlay__inner g-pos-abs g-flex-middle g-brd-around g-brd-2 g-brd-white-opacity-0_3 g-pa-15 g-ma-20">
						<div class="text-uppercase g-flex-middle-item">
							<h5 class="landing-block-node-subtitle1 g-font-weight-700 g-font-size-18 g-color-white g-brd-bottom g-brd-2 g-brd-primary g-mb-20">
								Travel certificates
							</h5>
							<h4 class="landing-block-node-title1 text-uppercase g-line-height-1 g-font-weight-700 g-font-size-40 g-mb-30">
								<span style="font-weight: bold;">40% Off</span>
							</h4>
							<div class="landing-block-node-button1-container">
								<a class="landing-block-node-button1 btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary rounded-0 g-py-10"
								   href="#">Contact us</a>
							</div>
						</div>
					</div>
				</article>
			</div>

			<div class="col-md-6 col-lg-4 g-mb-30 g-mb-0--lg">
				<article class="landing-block-node-block h-100 js-animation fadeInUp text-center u-block-hover u-bg-overlay g-color-white g-bg-black-opacity-0_3--after">
					<div class="landing-block-node-img2 h-100 w-100 g-bg-img-hero u-block-hover__main--zoom-v1"
						style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/800x867/img3.jpg);"
					></div>

					<div class="u-block-hover__additional u-bg-overlay__inner g-pos-abs g-flex-middle g-brd-around g-brd-2 g-brd-white-opacity-0_3 g-pa-15 g-ma-20">
						<div class="text-uppercase g-flex-middle-item">
							<h5 class="landing-block-node-subtitle2 g-font-weight-700 g-font-size-18 g-color-white g-mb-5">
								20% Discount</h5>
							<h4 class="landing-block-node-title2 text-uppercase g-line-height-1 g-font-weight-700 g-font-size-28 g-mb-10">
								On candy sets
							</h4>
							<div class="landing-block-node-text2 g-font-weight-700 g-color-white mb-0">
								When you buy over 100$</div>
						</div>
					</div>
				</article>
			</div>

			<div class="col-lg-4">
				<article class="landing-block-node-bg-mini js-animation fadeInUp text-center u-block-hover g-color-white g-bg-primary g-mb-30">
					<div class="g-brd-around g-brd-2 g-brd-white-opacity-0_3 g-pa-30 g-ma-20">
						<div class="g-flex-middle-item">
							<h4 class="landing-block-node-title-mini text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-10">
								Romantic dinner</h4>
							<div class="landing-block-node-text-mini g-font-size-12 g-color-white mb-0">
								<p>Morbi ex urna, porttitor vel consequat non</p>
							</div>
						</div>
					</div>
				</article>

				<article class="landing-block-node-block js-animation fadeInUp text-center u-block-hover u-bg-overlay g-color-white g-bg-img-hero g-bg-black-opacity-0_3--after">
					<img class="landing-block-node-img-mini w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x401/img2.jpg"
						 alt="">

					<div class="u-block-hover__additional u-bg-overlay__inner g-pos-abs g-flex-middle g-brd-around g-brd-2 g-brd-white-opacity-0_3 g-pa-15 g-ma-20">
						<div class="g-flex-middle-item">
							<h4 class="landing-block-node-title-mini text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-5">
								1+1=1</h4>
							<div class="landing-block-node-text-mini g-font-size-12 g-color-white mb-0">
								<p>Morbi ex urna, porttitor vel consequat non</p>
							</div>
						</div>
					</div>
				</article>
			</div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-50 g-pb-45 js-animation fadeInUp">

        <div class="container text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">CAREER</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="font-style: italic;">Our partners<br /></span></h2>
            </div>
			<div class="landing-block-node-text"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p></div>
        </div>

    </section>',
			],
		'24.2.image_carousel_6_cols_fix_3' =>
			[
				'CODE' => '24.2.image_carousel_6_cols_fix_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg g-bg-img-hero u-bg-overlay js-animation g-bg-primary-opacity-0_9--after g-pt-60 g-pb-80 fadeIn" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x350/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container u-bg-overlay__inner text-center g-px-35 g-px-0--md">
		<div class="js-carousel"
			 data-autoplay="true"
			 data-pause-hover="true"
			 data-infinite="true"
			 data-slides-show="6"
			 data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-45 g-height-45 g-font-size-30 g-color-white"
			 data-arrow-left-classes="fa fa-angle-left g-left-minus-35"
			 data-arrow-right-classes="fa fa-angle-right g-right-minus-35"
			 data-responsive=\'[{
                 "breakpoint": 1200,
                 "settings": {
                   "slidesToShow": 6
                 }
               }, {
                 "breakpoint": 992,
                 "settings": {
                   "slidesToShow": 4
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
				 "selector": ".landing-block-card-carousel-element",
				 "class": "col-12 col-sm-3 col-lg-2"
			   }, {
				 "selector": ".js-carousel",
				 "class": "row"
			   }]\'>
			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img1.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img2.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img3.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img4.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img5.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img6.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img7.png" alt="" />
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-mx-15 g-pt-20 col-12 col-sm-3 col-lg-2">
				<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
					<a href="#" class="landing-block-card-logo-link" target="_self">
						<img class="landing-block-node-img mx-auto g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img8.png" alt="" />
					</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'04.1.one_col_fix_with_title@2' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block landing-block-container g-pb-20 g-pt-50 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20">CONTACT US</h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-text-transform-none g-font-size-60"><span style="color: rgb(33, 33, 33); font-style: italic;">Get in touch</span></h2>
            </div>
        </div>
    </section>',
			],
		'33.10.form_2_light_left_text' =>
			[
				'CODE' => '33.10.form_2_light_left_text',
				'SORT' => '7000',
				'CONTENT' => '<section class="g-pos-rel landing-block g-pt-100 g-pb-100">

	<div class="container">

		<div class="row">
			<div class="col-md-6">
				<div class="text-center g-overflow-hidden">
					<h3 class="landing-block-node-main-title text-uppercase g-font-weight-700 g-mb-20">
						Contact Us</h3>

					<div class="landing-block-node-text g-line-height-1_5 text-left g-mb-40">
						<p>
							Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor.
							Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum,
							enim orci viverra eros, fringilla porttitor lorem eros vel odio.
						</p>
					</div>
					<div class="g-mx-minus-2 g-my-minus-2">
						<div class="row mx-0">

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="text">
								<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
									<i class="landing-block-card-contact-icon icon-anchor"></i>
								</span>
								<span class="landing-block-card-contact-title h3 d-block text-uppercase g-font-size-11 mb-0">
									Address</span>
								<span class="landing-block-card-contact-text g-font-weight-700 g-font-size-11">
									Sit amet adipiscing
								</span>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-call-in"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Phone number
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmPhoneTitle1
									</span>
								</a>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="mailto:#crmEmail1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-line icon-envelope-letter"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Email
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmEmailTitle1
									</span>
								</a>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"
								 data-card-preset="link">
								<a href="tel:#crmPhone1" class="landing-block-card-linkcontact-link g-text-decoration-none--hover">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-earphones-alt"></i>
									</span>
									<span class="landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0">
										Toll free
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11">
										#crmPhoneTitle1
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v4"
					data-b24form-use-style="Y"
					data-b24form-embed
					data-b24form-design=\'{"dark":false,"style":"classic","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#000","background":"#ffffff00","fieldBorder":"#fff","fieldBackground":"#f7f7f7","fieldFocusBackground":"#eee"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'
				>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'17.2.copyright_with_bgimg' =>
			[
				'CODE' => '17.2.copyright_with_bgimg',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-color-white g-bg-primary-opacity-0_8--after g-py-100" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img30.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
		<div class="container text-center text-md-left u-bg-overlay__inner">
			<div class="row">
				<div class="col-md-4 col-lg-5 d-flex align-items-center g-mb-20 g-mb-0--md">
					<p class="w-100 g-font-size-13 g-color-white mb-0 landing-block-node-copy">&copy; 2022 All rights reserved</p>
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
			],
	],
];