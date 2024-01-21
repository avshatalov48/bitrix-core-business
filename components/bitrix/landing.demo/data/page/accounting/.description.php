<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'accounting',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/accounting/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_ACCOUNTING_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_01' =>
			[
				'CODE' => '0.menu_01',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block-menu u-header u-header--sticky u-header--float">
	<div class="u-header__section  g-bg-black-opacity-0_3 g-transition-0_3 g-py-20" data-header-fix-moment-exclude="g-bg-black-opacity-0_3 g-py-20" data-header-fix-moment-classes="g-bg-black-opacity-0_7 g-py-10">
		<nav class="navbar navbar-expand-lg py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#"
				   class="landing-block-node-menu-logo-link navbar-brand u-header__logo g-max-width-180 g-mr-60 p-0">
					<img class="landing-block-node-menu-logo img-fluid u-header__logo-img u-header__logo-img--main"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/accounting-logo-light.png" alt="">
				</a>
				<!-- End Logo -->

				<div id="navBar" class="collapse navbar-collapse">
					<!-- Navigation -->
					<div class="align-items-center flex-sm-row w-100">
						<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-13 g-py-20 g-py-0--lg">
							<li class="landing-block-node-menu-list-item nav-item g-mr-10--lg g-mr-15--xl g-my-7 g-mb-0--lg ">
								<a href="#block@block[01.big_with_text_blocks]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mx-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[02.three_cols_big_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">ABOUT</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mx-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SERVICES</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mx-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PROJECTS</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mx-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">TESTOMONIALS</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-10--lg g-mx-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[04.3.one_col_fix_with_title_and_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">BEST OFFERS</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-ml-10--lg g-ml-15--xl g-my-7 g-mb-0--lg">
								<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@5]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACT US</a>
							</li>
						</ul>
					</div>
					<!-- End Navigation -->
				</div>
				
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
		'01.big_with_text_blocks' =>
			[
				'CODE' => '01.big_with_text_blocks',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="js-carousel g-overflow-hidden g-max-height-100vh " data-autoplay="true" data-infinite="true" data-speed="15000"
	data-pagi-classes="u-carousel-indicators-v1--white g-absolute-centered--x g-bottom-20">


		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1265/img1.jpg);">
				<div class="container text-center g-max-width-840 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">We are an accounting firm company24
						<br /> providing <span style="color: rgb(165, 195, 60);">tax</span> and <span style="color: rgb(165, 195, 60);">accounting</span> services</h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-color-white"><p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.
						Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.
						Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-rounded-50 g-py-15" href="#" tabindex="-1" target="_self">VIEW PRESENTATION</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1265/img2.jpg);">
				<div class="container text-center g-max-width-840 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">We are an accounting firm company24
						<br /> providing <span style="color: rgb(165, 195, 60);">tax</span> and <span style="color: rgb(165, 195, 60);">accounting</span> services</h2>
					<div class="landing-block-node-card-text g-hidden-xs-down g-max-width-645 mx-auto g-mb-35 g-color-white"><p>
					Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.
						Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.
						Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.<br /></p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-rounded-50 g-py-15" href="#" tabindex="-1" target="_self">VIEW PRESENTATION</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>
		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-min-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1265/img3.jpg);">
				<div class="container text-center g-max-width-840 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">We are an accounting firm company24
						<br /> providing <span style="color: rgb(165, 195, 60);">tax</span> and <span style="color: rgb(165, 195, 60);">accounting</span> services</h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-color-white"><p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.
						Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.
						Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-rounded-50 g-py-15" href="#" tabindex="0" target="_self">VIEW PRESENTATION</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

		<div class="landing-block-node-card js-slide">
			<!-- Promo Block -->
			<div class="landing-block-node-card-img g-flex-centered g-height-100vh g-min-height-500--md g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1265/img4.jpg);">
				<div class="container text-center g-max-width-840 g-z-index-1 js-animation landing-block-node-container fadeInLeftBig animated g-mx-0">
					<h2 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-22 g-font-size-36--md g-color-white g-mb-20">We are an accounting firm company24
						<br /> providing <span style="color: rgb(165, 195, 60);">tax</span> and <span style="color: rgb(165, 195, 60);">accounting</span> services</h2>
					<div class="landing-block-node-card-text g-max-width-645 mx-auto g-mb-35 g-color-white"><p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.
						Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.
						Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.</p></div>
					<div class="landing-block-node-card-button-container">
						<a class="landing-block-node-card-button btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-rounded-50 g-py-15" href="#" tabindex="0" target="_self">VIEW PRESENTATION</a>
					</div>
				</div>
			</div>
			<!-- End Promo Block -->
		</div>

	</div>
</section>',
			],
		'02.three_cols_big_2' =>
			[
				'CODE' => '02.three_cols_big_2',
				'SORT' => '1000',
				'CONTENT' => '<section class="container-fluid px-0 landing-block">
        <div class="row no-gutters">

            <div class="landing-block-node-left order-2 order-lg-1 col-md-6 col-lg-4 g-bg-black-opacity-0_7">
                <div class="js-carousel carouselLeft g-pb-90" data-infinite="true" data-slides-show="1" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">

                    <div class="js-slide landing-block-card-left">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-16 js-animation fadeIn">Present 2022</h3>
							<div class="landing-block-node-carousel-element-text g-color-gray-light-v1 js-animation fadeIn">
                            	<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="landing-block-node-center order-1 order-lg-2 col-12 col-lg-4 g-flex-centered g-bg-main">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-center-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 js-animation fadeIn"> </h6>
                        <h2 class="landing-block-node-center-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-36 js-animation fadeIn">WE KNOW OUR JOB</h2>
                    </div>

                    <div class="landing-block-node-center-text js-animation fadeIn"><p>Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros.</p><p><br /></p><p>Maecenas sit amet lacinia lectus. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros.</p></div>
                </div>
            </div>

            <div class="landing-block-node-right order-3 col-md-6 col-lg-4 g-bg-black-opacity-0_7">
                <div class="js-carousel carouselRight g-pb-90" data-infinite="true" data-slides-show="1" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img11.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-16 js-animation fadeIn">Since 2008</h3>
							<div class="landing-block-node-carousel-element-text g-color-gray-light-v1 js-animation fadeIn">
                            	<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-16 js-animation fadeIn">QUALITY SOLUTIONS</h3>
							<div class="landing-block-node-carousel-element-text g-color-gray-light-v1 js-animation fadeIn"><p>Cras ultricies nisl a leo tempus rhoncus. Nam mauris tellus, molestie quis purus sed, maximus vulputate lorem. Proin augue neque, mattis vel leo ac, porttitor laoreet felis.</p></div>
                        </div>
                    </div><div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-16 js-animation fadeIn">PROFESSIONAL STAFF</h3>
							<div class="landing-block-node-carousel-element-text g-color-gray-light-v1 js-animation fadeIn"><p>Cras sit amet varius velit. Maecenas porta condimentum tortor at sagittis. Cum sociis natoque penatibus et magnis disvarius velit. Class aptent taciti sociosqu ad litor.</p></div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/450x300/img1.jpg" alt="" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-carousel-element-title text-uppercase g-font-weight-700 g-color-white g-mb-10 g-font-size-16 js-animation fadeIn">QUICK RESPONSE</h3>
							<div class="landing-block-node-carousel-element-text g-color-gray-light-v1 js-animation fadeIn"><p>Morbi gravida magna vel odio accumsan, eu auctor diam sollicitudin. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec lacinia consequat.</p></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-bg-gray-light-v5 g-py-20 g-pt-60 g-pb-60 js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">WHAT WE OFFER</h2>
            </div>

			<div class="landing-block-node-text"><p>Cras luctus blandit sapien eget varius. Ut egestas justo faucibus laoreet fringilla. Pellentesque dictum, massa ut consequat euismod, tortor diam cursus nulla, a rhoncus justo mi ut diam.</p></div>
        </div>

    </section>',
			],
		'31.1.two_cols_text_img' =>
			[
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-bg-black-opacity-0_7">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-25 g-font-size-16 js-animation fadeInUp animated">
					Part-time
					cfo/controller</h3>
				<div class="landing-block-node-text g-mb-30 js-animation fadeInUp animated g-font-size-14 g-color-gray-light-v1">
					<p>Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit
						nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget
						metus. Nam et massa eros. Maecenas sit amet lacinia lectus.</p>
					<p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.
						Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.
						Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula.
						Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas
						ac
						nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.</p>
				</div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-primary g-btn-type-solid g-btn-size-md g-btn-px-m g-rounded-50 js-animation fadeInUp animated" href="#" tabindex="0">Contact us
						for more info</a>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1200x960/img1.jpg);"></div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-bg-gray-light-v5 g-py-20 g-bg-secondary g-pt-60 g-pb-60 js-animation fadeInUp">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR PROJECTS</h2>
            </div>

			<div class="landing-block-node-text"><p>Cras luctus blandit sapien eget varius. Ut egestas justo faucibus laoreet fringilla. Pellentesque dictum, massa ut consequat euismod, tortor diam cursus nulla, a rhoncus justo mi ut diam.</p></div>
        </div>

    </section>',
			],
		'20.2.three_cols_fix_img_title_text' =>
			[
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img1.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">AENEAN BIBENDUM PURUS EU NISI PULVINAR VENENATIS VITAE</h3>
				<div class="landing-block-node-text"><p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et. Vivamus sit amet scelerisque leo.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img2.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">AENEAN BIBENDUM PURUS EU NISI PULVINAR VENENATIS VITAE</h3>
				<div class="landing-block-node-text"><p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et. Vivamus sit amet scelerisque leo.</p></div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img3.jpg" alt="" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">AENEAN BIBENDUM PURUS EU NISI PULVINAR VENENATIS VITAE</h3>
				<div class="landing-block-node-text"><p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et. Vivamus sit amet scelerisque leo.</p></div>
			</div>

		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 js-animation fadeInUp animated g-bg-secondary">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800 g-mb-20 g-bg-secondary">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR TAX AND ACCOUNTING SYSTEM</h2>
            </div>

			<div class="landing-block-node-text"><p>Nulla facilisi. Integer consectetur elit sit amet urna sollicitudin bibendum. Morbi a suscipit ipsum. Suspendisse mollis libero ante. Pellentesque finibus convallis nulla vel placerat.</p></div>
        </div>

    </section>',
			],
		'08.1.three_cols_fix_title_and_text' =>
			[
				'CODE' => '08.1.three_cols_fix_title_and_text',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-25 g-pb-80 g-bg-secondary">
        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-lg-4 g-mb-40 g-mb-0--lg  js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-primary">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word text-left">MEET YOUR ACCOUNTANT</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Integer accumsan maximus leo, et consectetur metus vestibulum in. Vestibulum viverra justo odio. Donec eu nulla leo. Nullam eget felis non sapien blandit efficitur</p></div>
                </div>

                <div class="landing-block-card col-lg-4 g-mb-40 g-mb-0--lg  js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-primary">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word text-left">CONNECT BANK &amp; CREDIT CARDS</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Aenean volutpat erat quis mollis accumsan. Mauris at cursus ipsum. Praesent molestie imperdiet purus. Nullam eget felis non sapien blandit efficitur</p></div>
                </div>

                <div class="landing-block-card col-lg-4 g-mb-40 g-mb-0--lg  js-animation fadeIn animated">
                    <div class="landing-block-card-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-primary">
                        <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h6>
                        <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-17 g-text-break-word text-left">SET UP SYSTEM</h2>
                    </div>

                    <div class="landing-block-node-text"><p>Duis tristique bibendum nunc ut semper. Phasellus bibendum semper lectus, in ornare erat tempus eget. Nullam eget felis non sapien blandit efficitur</p></div>
                </div>

            </div>
        </div>
    </section>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-py-80" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img1.jpg);">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-30 g-color-white g-mb-20">High quality solutions</h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35">
			Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.
		</div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15">Buy
				full version</a>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 g-pt-60 g-pb-60 js-animation fadeInUp animated g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">WHAT OUR CUSTOMERS SAY</h2>
            </div>

			<div class="landing-block-node-title"><p>Praesent blandit hendrerit justo sed egestas. Proin tincidunt purus in tortor cursus fermentum. Proin laoreet erat vitae dui blandit, vitae faucibus lacus auctor. Proin ornare sit amet arcu at aliquam.</p></div>
        </div>

    </section>',
			],
		'29.three_cols_texts_blocks_slider' =>
			[
				'CODE' => '29.three_cols_texts_blocks_slider',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block js-animation fadeIn animated g-bg-main">

        <div>
            <div class="container g-py-40">

                <div class="js-carousel g-pb-60"
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
               }]\'>
                    <div class="landing-block-card-slider-element element-original js-slide g-px-15 mb-1">
                        <blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 rounded g-pa-25 g-mb-25">Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis. Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per.</blockquote>
                        <div class="media">
                            <img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3" src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img10.jpg" alt="" />
                            <div class="media-body align-self-center">
                                <h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">MARY BROWN</h4>
                                <div class="landing-block-node-element-subtitle g-color-primary"><span style="">CTO JIOPE</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element element-original js-slide g-px-15 mb-1">
                        <blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 rounded g-pa-25 g-mb-25">Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis. Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per.</blockquote>
                        <div class="media">
                            <img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3" src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img6.jpg" alt="" />
                            <div class="media-body align-self-center">
                                <h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">BOB CHRISTOPHER</h4>
                                <div class="landing-block-node-element-subtitle g-color-primary"><span><span style="">CEO UNIFEX</span></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element element-original js-slide g-px-15 mb-1">
                        <blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 rounded g-pa-25 g-mb-25">Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis. Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per.</blockquote>
                        <div class="media">
                            <img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3" src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img7.jpg" alt="" />
                            <div class="media-body align-self-center">
                                <h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">HELENA JORDANY</h4>
                                <div class="landing-block-node-element-subtitle g-color-primary"><span><span style="">SMM AQUAWATER</span></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element element-original js-slide g-px-15 mb-1">
                        <blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 rounded g-pa-25 g-mb-25">Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis. Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per.</blockquote>
                        <div class="media">
                            <img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3" src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img8.jpg" alt="" />
                            <div class="media-body align-self-center">
                                <h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">REBECCA KENTON</h4>
                                <div class="landing-block-node-element-subtitle g-color-primary"><span><span style="">MOLESTIE ULLAMCORPER</span></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="landing-block-card-slider-element element-original js-slide g-px-15 mb-1">
                        <blockquote class="landing-block-node-element-text u-blockquote-v8 g-font-weight-300 rounded g-pa-25 g-mb-25">Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis. Integer ut sollicitudin justo. Class aptent taciti sociosqu ad litora torquent per.</blockquote>
                        <div class="media">
                            <img class="landing-block-node-element-img d-flex align-self-center rounded-circle u-shadow-v19 g-brd-around g-brd-3 g-brd-white g-width-50 mr-3" src="https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img9.jpg" alt="" />
                            <div class="media-body align-self-center">
                                <h4 class="landing-block-node-element-title g-font-weight-400 g-font-size-15 g-mb-0">MELISSA ARMSTRONG</h4>
                                <div class="landing-block-node-element-subtitle g-color-primary"><span><span style="">MANAGER MOOGLE</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>',
			],
		'24.3.image_gallery_6_cols_fix_3' =>
			[
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation zoomIn text-center g-pt-30 g-pb-30">
	<div class="landing-block-node-container container g-brd-gray-light-v4">
		<div class="row g-brd-top g-brd-left g-brd-color-inherit mx-0">
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png" alt="">
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png" alt="">
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png" alt="">
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="">
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="">
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png" alt="">
				</a>
			</div>
		</div>
	</div>
</section>',
			],
		'04.3.one_col_fix_with_title_and_text' =>
			[
				'CODE' => '04.3.one_col_fix_with_title_and_text',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block g-bg-gray-dark-v3 g-pt-60 g-pb-60 js-animation slideInRight">

        <div class="container text-center g-max-width-800 g-color-gray-light-v2">
            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"></h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-color-gray-light-v5"><span style="">BEST OFFERS</span></h2>
            </div>

			<div class="landing-block-node-text g-color-black-opacity-0_1"><p>Praesent blandit hendrerit justo sed egestas. Proin tincidunt purus in tortor cursus fermentum. Proin laoreet erat vitae dui blandit, vitae faucibus lacus auctor. Proin ornare sit amet arcu at aliquam.</p></div>
        </div>

    </section>',
			],
		'11.three_cols_fix_tariffs' =>
			[
				'CODE' => '11.three_cols_fix_tariffs',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-pt-30 g-pb-20 g-bg-gray-dark-v3">
        <div class="container">

            <div class="row no-gutters landing-block-inner">

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md  col-lg-3 fadeInUp animated">
                    <article class="text-center g-brd-around g-color-gray g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-color-gray-dark-v3 g-font-weight-500 g-mb-10">Starter</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"><br /></div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2">$10.00</div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">ETIAM MOLLIS IACULIS</li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM<br /></li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">SED PELLENTESQUE VELIT ANTE<br /></li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM NEC<br /></li>
                                <li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">NULLAM VEL TURPIS<br /></li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">ORDER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md  col-lg-3 fadeInUp animated">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Starter</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"><br /></div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2">$10.00</div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">ETIAM MOLLIS IACULIS<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">SED PELLENTESQUE VELIT ANTE<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM NEC<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">NULLAM VEL TURPIS</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">ORDER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div><div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md  col-lg-3 fadeInUp animated">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Starter</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"><br /></div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2">$10.00</div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">ETIAM MOLLIS IACULIS<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">SED PELLENTESQUE VELIT ANTE<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM NEC<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">NULLAM VEL TURPIS</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">ORDER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div><div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md  col-lg-3 fadeInUp animated">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Starter</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"><br /></div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
                                <div class="landing-block-node-price g-font-size-30 g-line-height-1_2">$10.00</div>
                                <div class="landing-block-node-price-text">per month</div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">ETIAM MOLLIS IACULIS<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">SED PELLENTESQUE VELIT ANTE<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">FUSCE FRINGILLA IPSUM NEC<br /></li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">NULLAM VEL TURPIS</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">ORDER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div>

                

            </div>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@5' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block g-py-20 g-pt-60 g-pb-60 js-animation fadeInUp animated g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">CONTACT US</h2>
            </div>

			<div class="landing-block-node-text"><p>Cras luctus blandit sapien eget varius. Ut egestas justo faucibus laoreet fringilla. Pellentesque dictum, massa ut consequat euismod, tortor diam cursus nulla, a rhoncus justo mi ut diam.</p></div>
        </div>

    </section>',
			],
		'33.13.form_2_light_no_text' =>
			[
				'CODE' => '33.13.form_2_light_no_text',
				'SORT' => '8000',
				'CONTENT' => '<section class="g-pos-rel landing-block text-center g-pt-100 g-pb-100">

	<div class="container">

		<div class="row">
			<div class="col-md-6 mx-auto">
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
		'35.2.footer_dark' =>
			[
				'CODE' => '35.2.footer_dark',
				'SORT' => '8500',
				'CONTENT' => '<section class="g-pt-60 g-pb-60 g-bg-gray-dark-v2">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">TEXT WIDGET</h2>
				<p class="landing-block-node-text g-color-gray-light-v1 g-mb-20 g-font-size-14">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae est lorem. Aenean imperdiet nisi a dolor condimentum, id ullamcorper lacus vestibulum. Praesent pulvinar gravida. Aenean lobortis ante ac porttitor eleifend.</p>
				<address class="g-mb-20">
				</address>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">Categories</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">Customer Support</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Pellentesque a tristique risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Nunc vitae libero lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2">
				<h2 class="landing-block-node-title text-uppercase g-color-white g-font-weight-700 g-font-size-16 g-mb-20">Top Link</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Pellentesque a tristique risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Nunc vitae libero lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Praesent pulvinar gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			],
		'17.1.copyright_with_social' =>
			[
				'CODE' => '17.1.copyright_with_social',
				'SORT' => '9000',
				'CONTENT' => '<section class="landing-block g-brd-top g-brd-gray-dark-v2 g-bg-black-opacity-0_8 js-animation animation-none">
	<div class="text-center text-md-left g-py-40 g-color-gray-dark-v5 container">
		<div class="row">
			<div class="col-md-6 d-flex align-items-center g-mb-15 g-mb-0--md w-100 mb-0">
				<div class="landing-block-node-text mr-1 js-animation animation-none">
					&copy; 2022 All rights reserved.
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
			],
	],
];