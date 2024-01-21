<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '1construction',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/construction/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_CONSTRUCTION_DESCRIPTION'),
		],
	],
	'items' => [
		'0.menu_07_construction' =>
			[
				'CODE' => '0.menu_07_construction',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-12 g-py-20--md" data-header-fix-moment-exclude="g-py-20--md" data-header-fix-moment-classes="u-shadow-v27 g-py-15--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/construction-logo.png"
						 alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav  navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-8--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[01.two_col_with_titles]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[19.1.two_cols_fix_img_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[21.3.two_cols_big_bgimg_title_text_button]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
						</li>

						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.4.one_col_big_with_img]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">RECENT PROJECTS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TESTIMONIALS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">GALLERY</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-8--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CAREER</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-8--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
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
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="container-fluid">
		<div class="row">
			<div class="landing-block-node-img col-md-6 u-bg-overlay d-flex align-items-center g-bg-img-hero g-bg-black-opacity-0_7--after"
				 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/700x800/img1.jpg);">
				<div class="landing-block-node-inner-container g-flex-centered w-100 g-py-40 g-pr-50--md text-md-right">
					<div class="landing-block-node-inner-container-left js-animation fadeInLeft w-100 u-bg-overlay__inner g-pt-100 g-pb-100">
						<h4 class="landing-block-node-small-title landing-block-node-small-title-left g-font-weight-700 g-color-primary g-mb-20">We professionally do</h4>
	
						<div class="landing-block-node-title-container g-brd-left g-brd-left-none--md g-brd-right--md g-brd-7 g-brd-primary g-color-white g-pl-30 g-pr-30 g-pl-0--lg g-mb-30">
							<h2 class="landing-block-node-title landing-block-node-title-left text-uppercase g-line-height-0_9 g-font-weight-700 g-font-size-76 mb-0">Interior<br>works
							</h2>
						</div>
						<div class="landing-block-node-button-container">
							<a class="btn g-btn-white g-btn-size-md g-btn-px-m g-btn-type-outline mx-2 landing-block-node-button" href="#">Read more</a>
						</div>
					</div>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 u-bg-overlay d-flex align-items-center g-bg-img-hero g-bg-black-opacity-0_7--after"
				 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/700x800/img2.jpg);">
				<div class="landing-block-node-inner-container g-flex-centered w-100 g-py-40 g-pl-50--md">
					<div class="landing-block-node-inner-container-right js-animation fadeInRight w-100 u-bg-overlay__inner g-pt-100 g-pb-100">
						<h4 class="landing-block-node-small-title landing-block-node-small-title-right g-font-weight-700 g-color-primary g-mb-20">We professionally do</h4>
						
						<div class="landing-block-node-title-container g-brd-left g-brd-7 g-brd-primary g-color-white g-pl-30 g-mb-30">
							<h2 class="landing-block-node-title landing-block-node-title-right text-uppercase g-line-height-0_9 g-font-weight-700 g-font-size-76 mb-0">Exterior<br>works
							</h2>
						</div>
						<div class="landing-block-node-button-container">
							<a class="btn g-btn-white g-btn-size-md g-btn-px-m g-btn-type-outline mx-2 landing-block-node-button" href="#">Read more</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'18.two_cols_fix_img_text_button' =>
			[
				'CODE' => '18.two_cols_fix_img_text_button',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-bg-primary g-bg-primary-opacity-0_8--after g-pt-20 g-pb-20">
	<div class="container text-center text-lg-left g-color-white">
		<div class="row g-flex-centered">
			<div class="col-lg-3 offset-lg-1">
				<img class="landing-block-node-img img-fluid g-width-200 g-width-auto--lg g-mb-30 g-mb-0--lg mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/874x600/img1.png" alt="" />
			</div>

			<div class="col-lg-6 u-bg-overlay__inner g-flex-centered">
				<div class="w-100">
					<h2 class="landing-block-node-title js-animation fadeInDown text-uppercase g-line-height-1_1 g-font-weight-700 g-font-size-26 g-mb-10 animated">
						Need to do calculations?</h2>
					<div class="landing-block-node-text js-animation fadeIn g-line-height-1_2 g-font-size-18 g-color-white animated">
						<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
							laudantium, totam rem aperiam, eaque ipsa quae ab illo.</p>
					</div>
				</div>
			</div>

			<a class="landing-block-node-button js-animation fadeInUp btn g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m mx-2 g-flex-centered g-flex-right--lg animated" href="#">
				Get a quote
			</a>
		</div>
	</div>
</section>',
			],
		'19.1.two_cols_fix_img_text_blocks' =>
			[
				'CODE' => '19.1.two_cols_fix_img_text_blocks',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block g-py-20 g-pt-60">
        <div class="container">
            <div class="row">

                <div class="col-md-5 g-mb-30 g-mb-0--md">
                    <img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x562/img1.jpg" alt="" />

                    <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Building since 1943</h3>
                    <div class="landing-block-node-text">
						<p class="mb-0">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
					</div>
                </div>

                <div class="col-md-7 g-mb-15 g-mb-0--md">
                    <div aria-multiselectable="true">
                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
								<div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img icon-user g-valign-middle g-font-size-23 g-mr-10"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Who we are</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading1">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text">
										<p>Anim pariatur cliche reprehenderit, 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.
											<br />Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquipex ea commodo consequat.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img icon-calendar g-valign-middle g-font-size-23 g-mr-10"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Our history</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading2">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text">
										<p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Food truck quinoa nesciunt laborum eiusmod. Duis aute
											irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img icon-settings g-valign-middle g-font-size-23 g-mr-10"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Our services</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading3">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text">
										<p>3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. At vero eos et accusamus et iusto odio
											dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
									</div>
                                </div>
                            </div>
                        </div>

                        <div class="landing-block-card-accordeon-element card g-brd-none js-animation fadeInUp animated ">
                            <div class="card-header u-accordion__header g-bg-transparent g-brd-none rounded-0 p-0">
                                <div class="landing-block-card-accordeon-element-title-link d-block text-uppercase g-pos-rel g-font-weight-700 g-font-size-12 g-brd-bottom g-brd-primary g-brd-2 g-py-15">
                                    <span class="landing-block-node-accordeon-element-img-container g-color-primary">
                                    <i class="landing-block-node-accordeon-element-img icon-diamond g-valign-middle g-font-size-23 g-mr-10"></i>
                                    </span>
                                    <div class="d-inline-block landing-block-node-accordeon-element-title">Our values</div>
                                </div>
                            </div>

                            <div class="landing-block-card-accordeon-element-body" aria-labelledby="aboutAccordionHeading4">
                                <div class="card-block u-accordion__body g-pt-20 g-pb-0 px-0">
                                    <div class="landing-block-node-accordeon-element-text">
										<p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard
											dolor brunch. Food truck quinoa nesciunt laborum eiusmod. nesciunt laborum eiusmod.</p>
									</div>
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
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40"
					 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img1.jpg);">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20">Building</h3>
						<div class="landing-block-node-text js-animation fadeIn g-color-white-opacity-0_7">
							<p>1At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button js-animation fadeIn btn btn-lg g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40"
					 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img2.jpg);">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20">Plumbing works</h3>
						<div class="landing-block-node-text js-animation fadeIn g-color-white-opacity-0_7">
							<p>2At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button js-animation fadeIn btn btn-lg g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>

        </div>
    </section>',
			],
		'21.3.two_cols_big_bgimg_title_text_button@2' =>
			[
				'CODE' => '21.3.two_cols_big_bgimg_title_text_button',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block container-fluid px-0">
        <div class="row no-gutters g-overflow-hidden landing-block-inner">
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img3.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20">PAINTING</h3>
						<div class="landing-block-node-text js-animation fadeIn g-color-white-opacity-0_7">
							<p>1At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button js-animation fadeIn btn btn-lg g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>
				<div class="landing-block-card col-lg-6 landing-block-node-img g-min-height-500 g-bg-black-opacity-0_6--after g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation animation-none g-pa-40" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img4.jpg\');" data-fileid="-1">
					<div class="text-center u-bg-overlay__inner">
						<h3 class="landing-block-node-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20">RECONSTRUCTION</h3>
						<div class="landing-block-node-text js-animation fadeIn g-color-white-opacity-0_7">
							<p>2At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.</p>
						</div>
						<div class="landing-block-node-button-container">
							<a class="landing-block-node-button js-animation fadeIn btn btn-lg g-btn-type-outline g-btn-white g-btn-px-m rounded-0 g-btn-size-md mx-2" href="#">
								Read more
							</a>
						</div>
					</div>
				</div>

        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-pb-20 animated g-pt-60 g-bg-main">

	<div class="container text-center g-max-width-800">

		<div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">our services</h4>
			<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">What we can do</h2>
		</div>

		<div class="landing-block-node-text g-pb-1">
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
				dolore magna aliqua. Ut enim ad minim veniam.</p>
		</div>
	</div>

</section>',
			],
		'20.3.four_cols_fix_img_title_text' =>
			[
				'CODE' => '20.3.four_cols_fix_img_title_text',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x397/img1.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">SAEPE EVENIET UT</h3>
				<div class="landing-block-node-text"><p>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p></div>
			</div>

			<div class="landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x397/img2.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">VOLUPTATE VELIT ESSE</h3>
				<div class="landing-block-node-text"><p>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p></div>
			</div>

			<div class="landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x397/img3.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">TEMPORIBUS AUTEM</h3>
				<div class="landing-block-node-text"><p>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p></div>
			</div>

			<div class="landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x397/img4.jpg" alt="" data-fileid="-1" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">QUIBUSDAM</h3>
				<div class="landing-block-node-text"><p>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'04.4.one_col_big_with_img' =>
			[
				'CODE' => '04.4.one_col_big_with_img',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-pb-40 js-animation fadeInLeft animated g-bg-primary-opacity-0_8--after g-pt-60" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1073/img1.jpg);">
        <div class="container g-max-width-800 u-bg-overlay__inner">

            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-white">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-white g-mb-20">Recent projects</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-white g-mb-minus-10">Projects in progress</h2>
            </div>

        </div>
    </section>',
			],
		'22.2.three_cols_fix_bigbgimg' =>
			[
				'CODE' => '22.2.three_cols_fix_bigbgimg',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-py-20 g-bg-primary-opacity-0_8--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1073/img1.jpg);">
        <div class="container u-bg-overlay__inner">
            <div class="row landing-block-inner">

                <div class="landing-block-card d-flex flex-column col-md-4 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
                    <div class="u-heading-v8-1 g-mb-20">
                        <h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7">
                            Building
						</h6>
						<h6 class="landing-block-node-title text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-15">
                            Excepteur sint occaecat cupidatat
						</h6>
                    </div>
                    <img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x565/img1.jpg" alt="" />
                    <div class="landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 g-mb-40">
						<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur, sunt in culpa qui officia deserunt mollit
							anim id est laborum.</p>
					</div>
                    <div class="landing-block-node-button-container">
						<a class="landing-block-node-button btn g-btn-type-solid-double g-btn-black g-btn-size-md g-btn-px-m" href="#">
							View project
						</a>
					</div>
                </div>

                <div class="landing-block-card d-flex flex-column col-md-4 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
                    <div class="u-heading-v8-1 g-mb-20">
                        <h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7">
                            Building
						</h6>
						<h6 class="landing-block-node-title text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-15">
                            Excepteur sint occaecat cupidatat
						</h6>
                    </div>
                    <img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x565/img2.jpg" alt="" />
                    <div class="landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 g-mb-40">
						<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur, sunt in culpa qui officia deserunt mollit
							anim id est laborum.</p>
					</div>
                    <div class="landing-block-node-button-container">
						<a class="landing-block-node-button btn g-btn-type-solid-double g-btn-black g-btn-size-md g-btn-px-m" href="#">
							View project
						</a>
					</div>
                </div>

                <div class="landing-block-card d-flex flex-column col-md-4 g-mb-20 g-mb-0--md js-animation fadeIn animated ">
                    <div class="u-heading-v8-1 g-mb-20">
                        <h6 class="landing-block-node-subtitle text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-11 g-color-white-opacity-0_8 p-0 g-mb-7">
                            Building
						</h6>
						<h6 class="landing-block-node-title text-uppercase u-heading-v8__title g-font-weight-700 g-color-white mb-0 g-font-size-15">
                            Excepteur sint occaecat cupidatat
						</h6>
                    </div>
                    <img class="landing-block-node-img img-fluid g-mb-20 flex-shrink-0" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x565/img3.jpg" alt="" />
                    <div class="landing-block-node-text g-line-height-1_8 g-color-white-opacity-0_8 g-mb-40">
						<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur, sunt in culpa qui officia deserunt mollit
							anim id est laborum.</p>
					</div>
					<div class="landing-block-node-button-container">
						<a class="landing-block-node-button btn g-btn-type-solid-double g-btn-black g-btn-size-md g-btn-px-m" href="#">
							View project
						</a>
					</div>
                </div>

            </div>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp g-bg-gray-light-v5 g-pb-20 animated g-pt-60">

	<div class="container text-center g-max-width-800">

		<div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">
				Testimonials</h4>
			<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">
				What clients say</h2>
		</div>

		<div class="landing-block-node-text g-pb-1">
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
				dolore magna aliqua. Ut enim ad minim veniam.</p>
		</div>
	</div>

</section>',
			],
		'23.big_carousel_blocks' =>
			[
				'CODE' => '23.big_carousel_blocks',
				'SORT' => '5500',
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

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Simone Gomez</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						Anderson industry</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Carla Harris</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						HNN consultation corp</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img3.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Dianna Kimwealth</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						Robo construction</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img4.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">John Wellberg</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						Solid iron corp</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Sarah Rahman</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						South Conton architecture</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img6.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Derek Fineman</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						Fineman construction company
					</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img7.jpg" alt="" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">William Mountcon</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">
						Mountcon brothers</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">Mauris sodales tellus vel felis dapibus, sit amet porta nibh egestas. Sed dignissim tellus quis sapien sagittis cursus. At
                        vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis.
                    </blockquote>
                </div>
            </div>

        </div>

    </section>',
			],
		'24.2.image_carousel_6_cols_fix_3' =>
			[
				'CODE' => '24.2.image_carousel_6_cols_fix_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation fadeIn landing-block-node-bgimg g-bg-img-hero u-bg-overlay g-bg-primary-opacity-0_9--after g-pt-60 g-pb-80"
		style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x350/img1.jpg);">
	<div class="container u-bg-overlay__inner text-center g-px-35 g-px-0--md">
		<div class="js-carousel row"
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
			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img1.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img2.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img3.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img4.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img5.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img6.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img7.png" alt="">
					</a>
				</div>
			</div>

			<div class="landing-block-card-carousel-element js-slide g-pt-20 col-12 col-sm-3 col-lg-2">
				<a href="#" class="landing-block-card-logo-link">
					<div class="landing-block-card-container h-100 d-flex justify-content-center align-items-center flex-column">
						<img class="landing-block-node-img mx-auto g-width-120"
							 src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img8.png" alt="">
					</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-bg-main g-pb-10">

	<div class="container text-center g-max-width-800">

		<div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
			<h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">Gallery</h4>
			<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">Our works</h2>
		</div>

		<div class="landing-block-node-text g-pb-1">
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
				dolore magna aliqua. Ut enim ad minim veniam.</p>
		</div>
	</div>

</section>',
			],
		'32.6.img_grid_4cols_1_no_gutters' =>
			[
				'CODE' => '32.6.img_grid_4cols_1_no_gutters',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img1.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img2.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img3.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img4.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

	</div>

</section>',
			],
		'32.6.img_grid_4cols_1_no_gutters@2' =>
			[
				'CODE' => '32.6.img_grid_4cols_1_no_gutters',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img5.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img6.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img7.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img8.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

	</div>

</section>',
			],
		'32.6.img_grid_4cols_1_no_gutters@3' =>
			[
				'CODE' => '32.6.img_grid_4cols_1_no_gutters',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-pt-0 g-pb-0">

	<div class="row no-gutters js-gallery-cards">

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img9.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img10.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img11.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

		<div class="col-12 col-sm-6 col-md-3">
			<div class="h-100">
				<div class="landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover">
					<img data-fancybox="gallery" class="landing-block-node-img img-fluid g-object-fit-cover h-100 w-100 u-block-hover__main--zoom-v1" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x375/img12.jpg" alt="" data-fileid="-1" />
				</div>
			</div>
		</div>

	</div>

</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '8500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 js-animation fadeInUp animated g-bg-white g-pt-60">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">CAREER</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">WORK WITH US</h2>
            </div>

			<div class="landing-block-node-text">
            	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
			</div>
        </div>

    </section>',
			],
		'25.one_col_fix_texts_blocks_slider' =>
			[
				'CODE' => '25.one_col_fix_texts_blocks_slider',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block">

        <div class="landing-block-node_bgimage u-bg-overlay g-bg-img-hero g-bg-primary-opacity-0_9--after g-py-60" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img2.jpg);">
            <div class="container u-bg-overlay__inner">

                <div class="js-carousel" data-infinite="true" data-arrows-classes="u-arrow-v1 g-pos-abs g-absolute-centered--y--md g-top-0 g-top-50x--md g-width-50 g-height-50 g-color-primary g-bg-gray-dark-v1 g-opacity-0_8--hover g-transition-0_2 g-transition--ease-in" data-arrow-left-classes="fa fa-arrow-left g-left-0 g-ml-30--md" data-arrow-right-classes="fa fa-arrow-right g-right-0 g-mr-30--md">
                    <div class="landing-block-card-slider-element js-slide">
                        <div class="container text-center g-max-width-700">
                            <h2 class="landing-block-node-element-title text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-40 js-animation fadeIn">Welder</h2>
                            <div class="landing-block-node-element-text g-color-white-opacity-0_8 js-animation fadeIn">
								<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
									cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi.</p>
							</div>
							<div class="landing-block-node-button-container">
								<a class="landing-block-node-element-button btn g-btn-type-solid-double g-btn-size-md g-btn-px-m g-btn-primary js-animation fadeInUp" href="#">
									Submit resume
								</a>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element js-slide">
                        <div class="container text-center g-max-width-700">
                            <h2 class="landing-block-node-element-title text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-40 js-animation fadeIn">Mollitia</h2>
                            <div class="landing-block-node-element-text g-color-white-opacity-0_8 js-animation fadeIn">
								<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
									cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi.</p>
							</div>
							<div class="landing-block-node-button-container">
								<a class="landing-block-node-element-button btn g-btn-type-solid-double g-btn-size-md g-btn-px-m g-btn-primary js-animation fadeInUp" href="#">
									Submit resume
								</a>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element js-slide">
                        <div class="container text-center g-max-width-700">
                            <h2 class="landing-block-node-element-title text-uppercase g-font-weight-700 g-font-size-26 g-color-white g-mb-40 js-animation fadeIn">Cupiditate</h2>
                            <div class="landing-block-node-element-text g-color-white-opacity-0_8 js-animation fadeIn">
								<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
									cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi.</p>
							</div>
							<div class="landing-block-node-button-container">
								<a class="landing-block-node-element-button btn g-btn-type-solid-double g-btn-size-md g-btn-px-m g-btn-primary js-animation fadeInUp" href="#">
									Submit resume
								</a>
							</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@5' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '9500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 js-animation fadeInUp animated g-bg-white g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15">Contact us</h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">get in touch</h2>
            </div>

			<div class="landing-block-node-text">
            	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
			</div>
        </div>

    </section>',
			],
		'33.10.form_2_light_left_text' =>
			[
				'CODE' => '33.10.form_2_light_left_text',
				'SORT' => '10000',
				'CONTENT' => '<section class="g-pos-rel landing-block g-pt-20 g-pb-100">

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
				'SORT' => '10500',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-color-white g-bg-primary-opacity-0_8--after g-py-100" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img1.jpg);">
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