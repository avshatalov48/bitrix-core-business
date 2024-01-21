<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_LAWYER_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_LAWYER_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'lawyer',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/lawyer/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_LAWYER_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_LAWYER_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_LAWYER_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_LAWYER_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_13_lawyer' =>
			[
				'CODE' => '0.menu_13_lawyer',
				'SORT' => '-100',
				'CONTENT' => '
<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky u-header--relative g-z-index-9999">
	<div class="u-header__section u-header__section--light g-transition-0_3 g-py-6 g-py-18--md"
		 data-header-fix-moment-exclude="g-py-18--md"
		 data-header-fix-moment-classes="u-shadow-v27 g-py-13--md">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
					<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/lawyer-logo.png" alt="">
				</a>
				<!-- End Logo -->
				

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[02.three_cols_big_1]" class="landing-block-node-menu-list-item-link nav-link p-0">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0">Services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[31.1.two_cols_text_img]" class="landing-block-node-menu-list-item-link nav-link p-0">Why we</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[31.2.two_cols_img_text]" class="landing-block-node-menu-list-item-link nav-link p-0">Benefits</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@2]" class="landing-block-node-menu-list-item-link nav-link p-0">Our cases</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.7.one_col_fix_with_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0">Team</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[04.1.one_col_fix_with_title]" class="landing-block-node-menu-list-item-link nav-link p-0">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[30.2.three_cols_fix_img_and_links]" class="landing-block-node-menu-list-item-link nav-link p-0">Blog</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg">
							<a href="#block@block[33.10.form_2_light_left_text]" class="landing-block-node-menu-list-item-link nav-link p-0">Contacts</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto g-flex-centered-item--center" type="button"
						aria-label="Toggle navigation"
						aria-expanded="false"
						aria-controls="navBar"
						data-toggle="collapse"
						data-target="#navBar">
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
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-py-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1066/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown animated">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-30 g-color-white g-mb-20 g-font-size-55">LAWS <br />MUST WORK</h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35 text-uppercase g-font-size-13 g-letter-spacing-4">Donec erat urna, tincidunt at leo non, blandit finibus ante.<br /> Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, <br />&amp;nbsp;mattis enim id, efficitur tortor. Nullam </div>
		<div class="landing-block-node-button-container">
			<a href="#" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-mb-15 rounded-0" target="_self">LEARN MORE</a>
		</div>
	</div>
</section>',
			],
		'02.three_cols_big_1' =>
			[
				'CODE' => '02.three_cols_big_1',
				'SORT' => '1000',
				'CONTENT' => '<section class="container-fluid px-0 landing-block">
        <div class="row no-gutters">
            <div class="landing-block-node-left-img g-min-height-300 col-lg-4 g-bg-img-hero" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img1.jpg\');" data-fileid="-1"></div>

            <div class="landing-block-node-center col-md-6 col-lg-4 g-flex-centered g-theme-lawyer-bg-gray-dark-v1">
                <div class="text-center g-color-gray-light-v2 g-pa-30">
                    <div class="landing-block-node-header text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40">
                        <h6 class="landing-block-node-center-subtitle js-animation fadeIn g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 animated">About us</h6>
                        <h2 class="landing-block-node-center-title js-animation fadeIn h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-color-white g-mb-minus-10 animated g-font-size-30">WE KNOW OUR JOB</h2>
                    </div>

                    <div class="landing-block-node-center-text js-animation fadeIn g-color-gray-light-v2 mb-0 animated"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
                        viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 landing-block-node-right g-bg-main">
                <div class="js-carousel g-pb-90" data-infinite="true" data-slides-show="1" data-pagi-classes="u-carousel-indicators-v1 g-absolute-centered--x g-bottom-30">
                    

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img2.jpg" alt="" data-fileid="-1" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-20 g-mb-10 animated">WE ARE WINNERS</h3>
                            <div class="landing-block-node-right-text js-animation fadeIn animated">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
                        </div>
                    </div>

                    <div class="js-slide landing-block-card-right">
                        <img class="landing-block-node-right-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img3.jpg" alt="" data-fileid="-1" />

                        <div class="g-pa-30">
                            <h3 class="landing-block-node-right-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-20 g-mb-10 animated">WE ARE THE BEST</h3>
                            <div class="landing-block-node-right-text js-animation fadeIn animated">
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
                            	<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							</div>
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
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">WHAT WE DO</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel</p></div>
        </div>

    </section>',
			],
		'34.4.two_cols_with_text_and_icons' =>
			[
				'CODE' => '34.4.two_cols_with_text_and_icons',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block g-pb-60 g-pt-20">
	<div class="container">
		<!-- Icon Blocks -->
		<div class="row landing-block-inner">
			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-6 g-mb-40 g-px-20 animated ">
				<!-- Icon Blocks -->
				<div class="media">
					<div class="d-flex g-mt-25 g-mr-40 g-width-64 justify-content-center">
                  <span class="landing-block-node-card-icon g-color-primary d-block g-font-size-48 g-line-height-1">
                    <i class="landing-block-node-card-icon  fa fa-user-secret"></i>
                  </span>
					</div>

					<div class="media-body align-self-center">
						<h5 class="landing-block-node-card-title text-uppercase g-font-weight-800">Criminal law</h5>
						<div class="landing-block-node-card-text mb-0">
							<p>Proin dignissim eget enim id aliquam.
								Proin ornare dictum leo, non elementum tellus molestie et. Vivamus sit amet scelerisque
								leo.
								In eu commodo est. Sed bibendum a metus ac sollicitudin. Curabitur elementum placerat
								elit
								vel accumsan.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-6 g-mb-40 g-px-20 animated ">
				<!-- Icon Blocks -->
				<div class="media">
					<div class="d-flex g-mt-25 g-mr-40 g-width-64 justify-content-center">
                  <span class="landing-block-node-card-icon g-color-primary d-block g-font-size-48 g-line-height-1">
                    <i class="landing-block-node-card-icon  fa fa-institution"></i>
                  </span>
					</div>

					<div class="media-body align-self-center">
						<h5 class="landing-block-node-card-title text-uppercase g-font-weight-800">Civil law</h5>
						<div class="landing-block-node-card-text mb-0">
							<p>Nteger commodo est id erat bibendum, eu
								convallis dolor tempus. Fusce mollis blandit eros. Nunc quis sapien in massa varius
								convallis at sed justo. Praesent nec consectetur nibh, sed lobortis turpis.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-6 g-mb-40 g-px-20 animated ">
				<!-- Icon Blocks -->
				<div class="media">
					<div class="d-flex g-mt-25 g-mr-40 g-width-64 justify-content-center">
                  <span class="landing-block-node-card-icon g-color-primary d-block g-font-size-48 g-line-height-1">
                    <i class="landing-block-node-card-icon  fa fa-suitcase"></i>
                  </span>
					</div>

					<div class="media-body align-self-center">
						<h5 class="landing-block-node-card-title text-uppercase g-font-weight-800">Business law</h5>
						<div class="landing-block-node-card-text mb-0">
							<p>Aenean lobortis ante ac porttitor
								eleifend. Morbi massa justo, gravida sollicitudin tortor vel, dignissim viverra lectus.
								In
								varius blandit condimentum. Pellentesque rutrum mauris ornare libero imperdiet
								pellentesque.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>

			<div class="landing-block-node-card js-animation fadeInUp col-md-6 col-lg-6 g-mb-40 g-px-20 animated ">
				<!-- Icon Blocks -->
				<div class="media">
					<div class="d-flex g-mt-25 g-mr-40 g-width-64 justify-content-center">
                  <span class="landing-block-node-card-icon g-color-primary d-block g-font-size-48 g-line-height-1">
                    <i class="landing-block-node-card-icon  fa fa-money"></i>
                  </span>
					</div>

					<div class="media-body align-self-center">
						<h5 class="landing-block-node-card-title text-uppercase g-font-weight-800">Tax law</h5>
						<div class="landing-block-node-card-text mb-0">
							<p>Nam et nulla rutrum, dignissim eros
								quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam
								et
								tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis
								elementu.</p>
						</div>
					</div>
				</div>
				<!-- End Icon Blocks -->
			</div>
		</div>
		<!-- End Icon Blocks -->
	</div>
</section>',
			],
		'31.1.two_cols_text_img' =>
			[
				'CODE' => '31.1.two_cols_text_img',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block g-theme-lawyer-bg-gray-dark-v1">
	<div>
		<div class="row mx-0">
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-25 g-font-size-35 js-animation fadeInUp">WHY CHOOSE US</h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-dark-v5 js-animation fadeInUp"><p><span style="color: rgb(245, 245, 245);">Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel</span></p><p><span style="font-size: 1rem;"><span style="font-weight: bold;"><span style="color: rgb(244, 67, 54);">+</span><span style="color: rgb(245, 245, 245);"> PROFESSIONAL STAFF</span></span></span></p><p><span style="color: rgb(245, 245, 245);">Cras sit amet varius velit. Maecenas porta condimentum tortor at sagittis. Cum sociis natoque penatibus et magnis disvarius velit</span></p><p><span style="font-weight: bold;"><span style="color: rgb(244, 67, 54);">+</span><span style="color: rgb(245, 245, 245);"> GREAT EXPERIENCE</span></span></p><p><span style="font-size: 1rem;color: rgb(245, 245, 245);">Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et rutrum mauris ornare.</span></p><p><span style="font-weight: bold;"><span style="color: rgb(244, 67, 54);">+</span><span style="color: rgb(245, 245, 245);"> QUALIFIED SUPPORT</span></span></p><p><span style="font-size: 1rem;color: rgb(245, 245, 245);">Integer commodo est id erat bibendum, eu convallis dolor tempus. Fusce mollis blandit eros. Nunc quis sapien in massa varius convallis at sed justo.</span><br /></p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary g-rounded-50 js-animation fadeInUp" href="#" tabindex="0">Contact us
						for more info</a>
				</div>
			</div>

			<div class="landing-block-node-img col-md-6 g-min-height-360 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x914/img1.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
		</div>
	</div>
</section>',
			],
		'31.2.two_cols_img_text' =>
			[
				'CODE' => '31.2.two_cols_img_text',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-theme-lawyer-bg-gray-dark-v1">
	<div>
		<div class="row mx-0">
			<div class="landing-block-node-img col-md-6 g-min-height-300 g-bg-img-hero g-px-0 g-bg-size-cover" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x914/img2.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb"></div>
			
			<div class="col-md-6 text-center text-md-left g-py-50 g-py-100--md g-px-15 g-px-50--md">
				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-25 g-font-size-35 js-animation fadeInUp">OUR BENEFITS</h3>
				<div class="landing-block-node-text g-mb-30 g-color-gray-light-v2 js-animation fadeInUp"><p><span style="color: rgb(245, 245, 245);">Aenean lobortis ante ac porttitor eleifend. Morbi massa justo, gravida sollicitudin tortor vel, dignissim viverra lectus. In varius blandit condimentum. Pellentesque rutrum mauris ornare libero imperdiet pellentesque.</span><span style="font-size: 1.14286rem;font-weight: bold;color: rgb(244, 67, 54);"><br /><br />+</span><span style="font-size: 1.14286rem;font-weight: bold;color: rgb(245, 245, 245);"> FREE ADVICE</span><span style="color: rgb(245, 245, 245);"><br /></span></p><p><span style="color: rgb(245, 245, 245);">Praesent pulvinar gravida efficitur. Aenean bibendum purus eu nisi pulvinar venenatis vitae non velit. Sed et eleifend mi. Fusce dictum orci libero.</span></p><p><span style="font-weight: bold;color: rgb(244, 67, 54);">+</span><span style="font-weight: bold;color: rgb(245, 245, 245);"> DOCUMENTATION SUPPORT</span></p><p><span style="color: rgb(245, 245, 245);">Suspendisse pulvinar facilisis ligula vel pharetra. Vestibulum volutpat porttitor ex a rutrum. Aenean consectetur risus ultricies enim finibus lobortis non at ipsum.</span></p><p><span style="font-weight: bold;color: rgb(244, 67, 54);">+</span><span style="font-weight: bold;color: rgb(245, 245, 245);"> PROFESSIONAL TEAMS</span></p><p><span style="color: rgb(245, 245, 245);">Integer commodo est id erat bibendum, eu convallis dolor tempus. Fusce mollis blandit eros. Nunc quis sapien in massa varius convallis at sed justo.</span></p><p><span style="font-weight: bold;color: rgb(244, 67, 54);">+</span><span style="font-weight: bold;color: rgb(245, 245, 245);"> 60 YEARS OF EXPERIENCE</span></p><p><span style="color: rgb(245, 245, 245);">Ut a libero magna. Aenean sagittis nisi non ex venenatis, vel commodo tortor eleifend. Nunc feugiat, est quis rutrum sodales, nunc nibh pharetra nibh</span></p></div>
				<div class="landing-block-node-button-container">
					<a class="landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary g-font-weight-700 g-font-size-12 g-rounded-50 js-animation fadeInUp" href="#" tabindex="0" target="_self">CONTACT US FOR MORE INFO</a>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@2' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"></h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">OUR CASES</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p></div>
        </div>

    </section>',
			],
		'20.1.two_cols_fix_img_title_text' =>
			[
				'CODE' => '20.1.two_cols_fix_img_title_text',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">
        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card landing-block-node-block col-md-6 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn animated ">
                    <img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Fusce dictum orci libero</h3>
                    <div class="landing-block-node-text"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus.</p></div>
                </div>

                <div class="landing-block-card landing-block-node-block col-md-6 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn animated ">
                    <img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x800/img5.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-mb-20">Integer commodo</h3>
                    <div class="landing-block-node-text"><p>Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus.</p></div>
                </div>

            </div>
        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@3' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-py-20 js-animation fadeInUp animated g-bg-main g-pt-60 g-pb-20">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">MEET OUR ATTORNEYS</h2>
            </div>

			<div class="landing-block-node-text"><p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et. Vivamus sit amet scelerisque leo. In eu commodo est. Sed bibendum a metus ac sollicitudin.</p><p>Curabitur elementum placerat elit vel accumsan. Quisque fermentum libero sit amet condimentum tincidunt. Proin hendrerit nec turpis sit amet aliquet. Integer libero velit, molestie et sagittis non, maximus nec turpis.</p></div>
        </div>

    </section>',
			],
		'28.3.team' =>
			[
				'CODE' => '28.3.team',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block g-py-30 g-pb-80--md g-pb-0 g-pt-20">
	
	<div class="container">
		<!-- Team Block -->
		<div class="row landing-block-inner">
			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/600x996/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white"><br /></div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5"><span style="font-weight: normal;">PARTNER</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Ralf
						Smith</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0">civil and criminal law</p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white"> </div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5"><span style="font-weight: normal;">PARTNER</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Monica
						Gaudy</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0">business and criminal law</p>
					<!-- End Figure Info-->
				</div>
			</div>

			<div class="landing-block-card-employee js-animation col-md-6 g-mb-30 g-mb-0--lg  col-lg-4 fadeIn animated">
				<div class="text-center">
					<!-- Figure -->
					<figure class="g-pos-rel g-parent g-mb-30">
						<!-- Figure Image -->
						<img class="landing-block-node-employee-photo w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/270x450/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
						<!-- End Figure Image -->

						<!-- Figure Caption -->
						<figcaption class="g-pos-abs g-top-0 g-left-0 g-flex-middle w-100 h-100 g-bg-primary-opacity-0_8 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in g-pointer-events-none g-mt-0">
							<div class="landing-block-node-employee-quote g-pointer-events-all text-uppercase g-flex-middle-item g-line-height-1_4 g-font-weight-700 g-font-size-16 g-color-white"> </div>
						
						<!-- End Figure Caption -->
					</figcaption></figure>
					<!-- End Figure -->

					<!-- Figure Info -->
					<em class="landing-block-node-employee-post d-block text-uppercase g-font-style-normal g-font-weight-700 g-font-size-11 g-color-primary g-mb-5"><span style="font-weight: normal;">PARTNER</span></em>
					<h4 class="landing-block-node-employee-name text-uppercase g-font-weight-700 g-font-size-18 g-mb-7">Julia
						Exon</h4>
					<p class="landing-block-node-employee-subtitle g-font-size-13 mb-0">criminal law</p>
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
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block text-center g-py-20 g-pt-0 g-pb-50">
        <div class="container">
				<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-btn-primary text-uppercase g-btn-primary rounded-0" href="#" g-font-weight-700 target="_self">CONTACT US</a>
        </div>
    </section>',
			],
		'04.1.one_col_fix_with_title' =>
			[
				'CODE' => '04.1.one_col_fix_with_title',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block landing-block-container g-pt-20 g-pb-20 g-theme-lawyer-bg-gray-dark-v1 js-animation fadeInUp">
        <div class="container">
            <div class="landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary">
                <h6 class="landing-block-node-subtitle g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20"> </h6>
                <h2 class="landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10"><span style="color: rgb(245, 245, 245);">FROM OUR CLIENTS</span></h2>
            </div>
        </div>
    </section>',
			],
		'23.big_carousel_blocks' =>
			[
				'CODE' => '23.big_carousel_blocks',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block js-animation g-py-20 fadeIn animated g-theme-lawyer-bg-gray-dark-v1">

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
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Fred Penner</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Ruma</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">&quot;Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar.&quot;</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Amy Clayton</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Abibas</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">&quot;Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Martina Saiz</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Jonda</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">&quot;Ut augue diam, lacinia fringilla erat eu, vehicula commodo quam. Aliquam eget accumsan ligula. Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar.&quot;</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img4.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Joseph B. Seward</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Aodi</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">&quot;Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt.&quot;</blockquote>
                </div>
            </div>

            <div class="landing-block-card-carousel-element js-slide g-pt-60 g-pb-5 g-px-15">
                <div class="text-center u-shadow-v10 g-bg-white g-pa-0-35-35--sm g-pa-0-20-20">
                    <img class="landing-block-node-img rounded-circle mx-auto g-width-100 g-brd-10 g-brd-around g-brd-gray-light-v5 g-pull-50x-up" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x500/img10.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

                    <h4 class="landing-block-node-title text-uppercase g-font-weight-700 mb-0">Julia B. Mcraflane</h4>
                    <div class="landing-block-node-subtitle text-uppercase g-font-style-normal g-font-weight-700 g-font-size-10">Spencet Group</div>
                    <blockquote class="landing-block-node-text u-blockquote-v7 g-line-height-1_5 g-bg-primary--before mb-0">&quot;Maecenas sit amet consectetur lectus. Suspendisse commodo et magna non pulvinar. Quisque et ultricies sem, et vulputate dui. Morbi aliquam leo id ipsum tempus mollis.&quot;</blockquote>
                </div>
            </div>
        </div>
    </section>',
			],
		'12.image_carousel_6_cols_fix' =>
			[
				'CODE' => '12.image_carousel_6_cols_fix',
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block landing-block-node-container js-animation text-center g-py-20 g-theme-lawyer-bg-gray-dark-v1 zoomIn">
        <div class="container g-px-35 g-px-0--md">

            <div class="js-carousel g-mb-20"
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
				   }]\'>
                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img1.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img2.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img3.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img4.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img5.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img6.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
					<a href="#" class="landing-block-card-logo-link">
						<img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img7.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
					</a>
                </div>

                <div class="landing-block-card-carousel-item js-slide g-brd-around g-brd-gray-light-v1--hover g-transition-0_2 g-mx-10">
                <a href="#" class="landing-block-card-logo-link">
                    <img class="landing-block-node-carousel-img img-fluid g-max-width-170--md mx-auto" src="https://cdn.bitrix24.site/bitrix/images/landing/business/250x200/img8.png" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
                </a>
                </div>
            </div>

        </div>
    </section>',
			],
		'04.7.one_col_fix_with_title_and_text_2@4' =>
			[
				'CODE' => '04.7.one_col_fix_with_title_and_text_2',
				'SORT' => '7500',
				'CONTENT' => '<section class="landing-block landing-block-node-container g-py-20 js-animation fadeInUp animated g-pt-60 g-pb-20 g-bg-main">

        <div class="container landing-block-node-subcontainer text-center g-max-width-800">

            <div class="landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary">
                <h4 class="landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15"> </h4>
                <h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10">LATEST POSTS</h2>
            </div>

			<div class="landing-block-node-text"><p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel</p></div>
        </div>

    </section>',
			],
		'30.2.three_cols_fix_img_and_links' =>
			[
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '8000',
				'CONTENT' => '<section class="landing-block g-pt-20 g-pb-60">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">MAURIS TELLUS MAGNA, PRETIUM</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">READ MORE</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
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
                                    <a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">PROIN EGESTAS PURUS EGET PULVINAR</a>
                                </h3>
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">READ MORE</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn animated ">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/500x335/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />
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
									<a class="landing-block-node-link u-link-v5 g-color-primary--hover" href="#" target="_self">PROIN EGESTAS PURUS EGET PULVINAR</a>
								</h3>
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">READ MORE</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'33.10.form_2_light_left_text' =>
			[
				'CODE' => '33.10.form_2_light_left_text',
				'SORT' => '8500',
				'CONTENT' => '<section class="g-pos-rel landing-block g-py-100 g-theme-lawyer-bg-gray-dark-v1">

	<div class="container">

		<div class="row">
			<div class="col-md-6">
				<div class="text-center g-overflow-hidden">
					<div class="landing-block-node-text g-line-height-1_5 text-left g-mb-40 g-color-lightblue"><p style="text-align: center;"><span style="font-weight: bold;">CONTACT US</span></p></div>
					<div class="g-mx-minus-2 g-my-minus-2">
						<div class="row mx-0">
							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-anchor d-inline-block g-font-size-50 g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
									Address</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;">Sit amet adipiscing</span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-call-in d-inline-block g-font-size-50 g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
									Phone
									number</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="tel:+4025448569">+402 5448 569</a></span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-line icon-envelope-letter d-inline-block g-font-size-50 g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
									Email</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="mailto:info@company24.com">info@company24.com</a></span>
								</div>
							</div>

							<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25">
							<span class="landing-block-card-contact-icon-container g-color-primary">
								<i class="landing-block-card-contact-icon icon-earphones-alt d-inline-block g-font-size-50 g-mb-30"></i>
								</span>
								<h3 class="landing-block-card-contact-title text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
									Toll free</h3>
								<div class="landing-block-card-contact-text g-font-size-11 g-color-gray-dark-v1">
									<span style="font-weight: bold;"><a href="tel:+4025897660">+402 5897 660</a></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="bitrix24forms g-brd-white-opacity-0_6 u-form-alert-v4" data-b24form-embed data-b24form-use-style="Y" data-b24form-design=\'{"dark":false,"style":"classic","shadow":false,"compact":false,"color":{"primary":"--primary","primaryText":"#fff","text":"#000","background":"#ffffff00","fieldBorder":"#fff","fieldBackground":"#f7f7f7","fieldFocusBackground":"#eee"},"border":{"top":false,"bottom":false,"left":false,"right":false}}\'></div>
			</div>
		</div>
	</div>
</section>',
			],
		'35.1.footer_light' =>
			[
				'CODE' => '35.1.footer_light',
				'SORT' => '9000',
				'CONTENT' => '<section class="g-py-60">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Contact
					us</h2>
				<div class="landing-block-node-text g-mb-20">
					<p>Lorem ipsum dolor sit amet, consectetur
						adipiscing</p></div>

				<address class="g-mb-20">
					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text">
							Address: <span style="font-weight: bold;">In sed lectus tincidunt</span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-phone"></i>
						</div>
						<div class="landing-block-node-card-contact-text">
							Phone Number: <span style="font-weight: bold;"><a
										href="tel:485552566112">+48 555 2566 112</a></span>
						</div>
					</div>

					<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container text-left g-width-20">
							<i class="landing-block-node-card-contact-icon fa fa-envelope"></i>
						</div>
						<div>
							<div class="landing-block-node-card-contact-text">
								Email: <span style="font-weight: bold;"><a
											href="mailto:info@company24.com">info@company24.com</a></span>
							</div>
						</div>
					</div>
				</address>

			</div>


			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">
					Categories</h2>
				<ul class="landing-block-card-list1 list-unstyled g-mb-30">
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Proin vitae est lorem</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Aenean imperdiet nisi</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list1-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Customer
					Support</h2>
				<ul class="landing-block-card-list2 list-unstyled g-mb-30">
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Vivamus egestas sapien</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Sed convallis nec enim</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list2-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Nunc vitae libero
							lacus</a>
					</li>
				</ul>
			</div>

			<div class="col-sm-12 col-md-2 col-lg-2 g-mb-25 g-mb-0--lg">
				<h2 class="landing-block-node-title text-uppercase g-font-weight-700 g-font-size-16 g-mb-20">Top
					Link</h2>
				<ul class="landing-block-card-list3 list-unstyled g-mb-30">
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Pellentesque a tristique
							risus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Nunc vitae libero
							lacus</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Praesent pulvinar
							gravida</a>
					</li>
					<li class="landing-block-card-list3-item g-mb-10">
						<a class="landing-block-node-list-item" href="#">Integer commodo est</a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</section>',
			],
	]
];