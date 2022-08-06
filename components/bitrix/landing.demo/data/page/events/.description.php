<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

return [
	'name' => Loc::getMessage('LANDING_DEMO_EVENTS_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_EVENTS_DESCRIPTION'),
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'events',

			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/events/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_EVENTS_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EVENTS_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_EVENTS_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EVENTS_DESCRIPTION')
		]
	],
	'items' => [
		'0.menu_11_event' =>
			[
				'CODE' => '0.menu_11_event',
				'SORT' => '-100',
				'CONTENT' => '<header class="landing-block landing-block u-header u-header--sticky u-header--float g-z-index-9999">
	<div class="u-header__section g-bg-darkblue-opacity-0_7 g-transition-0_3 g-py-10" data-header-fix-moment-exclude="g-bg-darkblue-opacity-0_7" data-header-fix-moment-classes="u-header__section--light g-bg-white-opacity-0_9">
		<nav class="navbar navbar-expand-lg g-py-0 g-px-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main d-block g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/event-logo-light.png" alt="" data-header-fix-moment-exclude="d-block" data-header-fix-moment-classes="d-none" />

					<img class="landing-block-node-menu-logo2 u-header__logo-img u-header__logo-img--main d-none g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/event-logo-dark.png" alt="" data-header-fix-moment-exclude="d-none" data-header-fix-moment-classes="d-block" />
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg ">
							<a href="#block@block[41.3.announcement_with_slider]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">HOME</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[36.1.shedule]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">SCHEDULE</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">about</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[01.big_with_text_3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">GUESTS</a>
						</li>
						
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@3]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">LATEST POSTS</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#block@block[27.one_col_fix_title_and_text_2@4]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">PRICING</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg">
							<a href="#block@block[33.1.form_1_transparent_black_left_text]" class="landing-block-node-menu-list-item-link nav-link p-0" target="_self">CONTACTS</a>
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
			],
		'41.3.announcement_with_slider' =>
			[
				'CODE' => '41.3.announcement_with_slider',
				'SORT' => '500',
				'CONTENT' => '<section class="landing-block">
	<div class="g-pb-70">
		<div class="landing-block-node-bgimg g-pt-150 g-bg-img-hero g-pos-rel u-bg-overlay g-bg-darkblue-opacity-0_7--after" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1281/img1.jpg);">
			<div class="container g-max-width-750 u-bg-overlay__inner g-mb-60 landing-block-node-container js-animation fadeInUp">

				<h2 class="landing-block-node-title text-center text-uppercase g-font-weight-700 g-font-size-60 g-color-white g-mb-30 g-mb-70--md">
					UI &amp; UX Design 2022</h2>

				<div class="row g-mx-minus-5">
					<div class="col-md-4 g-px-5 g-mb-20 g-mb-0--md">
						<div class="media">
							<div class="landing-block-node-date-icon-container g-color-white-opacity-0_5 media-left d-flex align-self-center g-mr-20">
								<i class="landing-block-node-date-icon fa fa-calendar g-font-size-27 "></i>
							</div>

							<div class="media-body text-uppercase">
								<div class="landing-block-node-date-title g-mb-5 g-font-size-14 g-color-white-opacity-0_5"><span style="font-weight: bold;">When</span></div>
								<h3 class="landing-block-node-date-text text-uppercase g-font-size-15 g-color-white mb-0">
									18:30, 12 Jul, 2022</h3>
							</div>
						</div>
					</div>

					<div class="col-md-5 g-px-5 g-mb-20 g-mb-0--md">
						<div class="media">
							<div class="landing-block-node-place-icon-container media-left d-flex align-self-center g-mr-20 g-color-white-opacity-0_5">
								<i class="landing-block-node-place-icon fa fa-map-marker g-font-size-27 "></i>
							</div>

							<div class="media-body text-uppercase">
								<div class="landing-block-node-place-title g-mb-5 g-font-size-14 g-color-white-opacity-0_5">
									<span style="font-weight: bold;">Where</span></div>
								<h3 class="landing-block-node-place-text text-uppercase g-font-size-15 g-color-white mb-0">
									Concert Hall, Los Angeles,
									USA</h3>
							</div>
						</div>
					</div>

					<div class="col-md-3 text-md-right g-px-5">
						<a class="landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-white rounded-0 g-py-18" href="#">Register Now</a>
					</div>
				</div>
			</div>

			<div class="container u-bg-overlay__inner g-bottom-minus-70 px-0 g-z-index-2">
				<div class="row u-shadow-v23 g-theme-event-bg-blue-dark-v2 mx-0">
					<div class="col-md-6 px-0">
						<div class="js-carousel text-center u-carousel-v5 g-overflow-hidden h-100" data-infinite="true" data-arrows-classes="u-arrow-v1 g-absolute-centered--y g-width-40 g-height-40 g-font-size-20 g-color-white g-color-primary--hover g-bg-primary g-bg-white--hover g-transition-0_2 g-transition--ease-in" data-arrow-left-classes="fa fa-angle-left g-left-0" data-arrow-right-classes="fa fa-angle-right g-right-0">
							<div class="landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img1.jpg);"></div>

							<div class="landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh" style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img2.jpg);"></div>
						</div>
					</div>

					<div class="col-md-6 d-flex g-min-height-50vh g-theme-event-color-gray-dark-v1 g-py-80 g-py-20--md g-px-50">
						<div class="align-self-center w-100">
							<h2 class="landing-block-node-block-title text-uppercase g-font-weight-700 g-font-size-30 g-color-primary g-mb-10">
								About The
								Event</h2>
							<h3 class="landing-block-node-block-subtitle text-uppercase g-font-weight-500 g-font-size-13 g-color-white g-mb-20">
								Fusce pretium
								augue quis sem consectetur</h3>
							<div class="landing-block-node-block-text g-font-size-14 mb-0">
								<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in
									blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
								<p>Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros,
									fringilla porttitor lorem eros vel odio. In rutrum tellus vitae blandit lacinia.
									Phasellus
									eget
									sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt. </p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>',
			],
		'36.1.shedule' =>
			[
				'CODE' => '36.1.shedule',
				'SORT' => '1000',
				'CONTENT' => '<section class="landing-block g-py-40">
	<div class="container">
		<div class="tab-pane fade active show" role="tabpanel" aria-expanded="true">
			<div class="u-timeline-v3-wrap">
				<div class="landing-block-node-card js-animation u-timeline-v3 d-block text-center text-md-left g-parent u-link-v5 g-mb-50 fadeInUp" href="#">
					<div class="g-hidden-sm-down u-timeline-v3__icon g-absolute-centered--y g-z-index-3 g-line-height-0 g-width-16 g-height-16 g-ml-minus-8">
						<i class="d-inline-block g-width-16 g-height-16 g-bg-white g-brd-5 g-brd-style-solid g-brd-gray-light-v4 g-rounded-50"></i>
					</div>

					<div class="row mx-0">
						<div class="col-md-5 order-2 order-sm-1 d-flex align-items-center flex-wrap flex-sm-nowrap px-0">
							<div class="u-heading-v1-4 g-brd-gray-light-v2 w-100 g-mb-20 g-mb-0--sm">
								<div class="landing-block-node-card-time text-center g-pos-rel g-width-110 g-line-height-1_6 g-font-weight-600 g-color-white g-font-size-14 g-bg-gray-dark-v1 g-bg-primary--parent-hover g-py-5 g-px-10 mx-auto g-ml-0--md g-transition-0_2 g-transition--ease-in">15:30 - 17:30</div>
							</div>
							<div class="landing-block-node-card-img-container d-md-flex px-0 u-bg-overlay g-width-120 g-width-170--md g-height-120 g-height-170--md g-bg-black-opacity-0_3--after g-bg-white-opacity-0--after--parent-hover g-overflow-hidden g-rounded-50x g-mr-30--md mx-auto g-mb-15 g-mb-0--md g-transition-0_2 g-transition--ease-in col-auto g-pointer-events-before-after-none">
									<img class="landing-block-node-card-img img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img1.jpg" alt="" />
								</div>
						</div>

						<div class="col-md-7 order-1 order-sm-2 px-0 g-mb-15 g-mb-0--md">
							<div class="media d-block d-md-flex">
								<div class="media-body align-self-center">
									<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-5">
										Intro
										to UI/UX Design</h4>
									<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-23 g-mb-10">
										John Doe,
										Co-founder</h3>
									<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum
											dolor
											sit amet, consectetur adipiscing
											elit. Vestibulum ut scelerisque odio, a viverra arcu. Nulla ut suscipit
											velit,
											non
											dictum quam. Proin hendrerit vulputate mauris a imperdiet</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-node-card js-animation u-timeline-v3 d-block text-center text-md-left g-parent u-link-v5 g-mb-50 fadeInUp" href="#">
					<div class="g-hidden-sm-down u-timeline-v3__icon g-absolute-centered--y g-z-index-3 g-line-height-0 g-width-16 g-height-16 g-ml-minus-8">
						<i class="d-inline-block g-width-16 g-height-16 g-bg-white g-brd-5 g-brd-style-solid g-brd-gray-light-v4 g-rounded-50"></i>
					</div>

					<div class="row mx-0">
						<div class="col-md-5 order-2 order-sm-1 d-flex align-items-center flex-wrap flex-sm-nowrap px-0">
							<div class="u-heading-v1-4 g-brd-gray-light-v2 w-100 g-mb-20 g-mb-0--sm">
								<div class="landing-block-node-card-time text-center g-pos-rel g-width-110 g-line-height-1_6 g-font-weight-600 g-color-white g-font-size-14 g-bg-gray-dark-v1 g-bg-primary--parent-hover g-py-5 g-px-10 mx-auto g-ml-0--md g-transition-0_2 g-transition--ease-in">17:45 - 18:45</div>
							</div>
							<div class="landing-block-node-card-img-container d-md-flex px-0 u-bg-overlay g-width-120 g-width-170--md g-height-120 g-height-170--md g-bg-black-opacity-0_3--after g-bg-white-opacity-0--after--parent-hover g-overflow-hidden g-rounded-50x g-mr-30--md mx-auto g-mb-15 g-mb-0--md g-transition-0_2 g-transition--ease-in col-auto g-pointer-events-before-after-none">
									<img class="landing-block-node-card-img img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img2.jpg" alt="" />
								</div>
						</div>

						<div class="col-md-7 order-1 order-sm-2 px-0 g-mb-15 g-mb-0--md">
							<div class="media d-block d-md-flex">
								

								<div class="media-body align-self-center">
									<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-5">
										Design Trands for 2022</h4>
									<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-23 g-mb-10">
										Kate Watson,
										Designer</h3>
									<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum
											dolor
											sit amet, consectetur adipiscing
											elit. Vestibulum ut scelerisque odio, a viverra arcu. Nulla ut suscipit
											velit,
											non
											dictum quam. Proin hendrerit vulputate mauris a imperdiet</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-node-card js-animation u-timeline-v3 d-block text-center text-md-left g-parent u-link-v5 g-mb-50 fadeInUp" href="#">
					<div class="g-hidden-sm-down u-timeline-v3__icon g-absolute-centered--y g-z-index-3 g-line-height-0 g-width-16 g-height-16 g-ml-minus-8">
						<i class="d-inline-block g-width-16 g-height-16 g-bg-white g-brd-5 g-brd-style-solid g-brd-gray-light-v4 g-rounded-50"></i>
					</div>

					<div class="row mx-0">
						<div class="col-md-5 order-2 order-sm-1 d-flex align-items-center flex-wrap flex-sm-nowrap px-0">
							<div class="u-heading-v1-4 g-brd-gray-light-v2 w-100 g-mb-20 g-mb-0--sm">
								<div class="landing-block-node-card-time text-center g-pos-rel g-width-110 g-line-height-1_6 g-font-weight-600 g-color-white g-font-size-14 g-bg-gray-dark-v1 g-bg-primary--parent-hover g-py-5 g-px-10 mx-auto g-ml-0--md g-transition-0_2 g-transition--ease-in">19:00 - 21:00</div>
							</div>
							<div class="landing-block-node-card-img-container d-md-flex px-0 u-bg-overlay g-width-120 g-width-170--md g-height-120 g-height-170--md g-bg-black-opacity-0_3--after g-bg-white-opacity-0--after--parent-hover g-overflow-hidden g-rounded-50x g-mr-30--md mx-auto g-mb-15 g-mb-0--md g-transition-0_2 g-transition--ease-in col-auto g-pointer-events-before-after-none">
									<img class="landing-block-node-card-img img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img3.jpg" alt="" />
								</div>
						</div>

						<div class="col-md-7 order-1 order-sm-2 px-0 g-mb-15 g-mb-0--md">
							<div class="media d-block d-md-flex">
								

								<div class="media-body align-self-center">
									<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-5">
										Digital Marketing</h4>
									<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-23 g-mb-10">
										Sara Woodman,
										Consultant</h3>
									<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum
											dolor
											sit amet, consectetur adipiscing
											elit. Vestibulum ut scelerisque odio, a viverra arcu. Nulla ut suscipit
											velit,
											non
											dictum quam. Proin hendrerit vulputate mauris a imperdiet</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="landing-block-node-card js-animation u-timeline-v3 d-block text-center text-md-left g-parent u-link-v5 g-mb-50 fadeInUp" href="#">
					<div class="g-hidden-sm-down u-timeline-v3__icon g-absolute-centered--y g-z-index-3 g-line-height-0 g-width-16 g-height-16 g-ml-minus-8">
						<i class="d-inline-block g-width-16 g-height-16 g-bg-white g-brd-5 g-brd-style-solid g-brd-gray-light-v4 g-rounded-50"></i>
					</div>

					<div class="row mx-0">
						<div class="col-md-5 order-2 order-sm-1 d-flex align-items-center flex-wrap flex-sm-nowrap px-0">
							<div class="u-heading-v1-4 g-brd-gray-light-v2 w-100 g-mb-20 g-mb-0--sm">
								<div class="landing-block-node-card-time text-center g-pos-rel g-width-110 g-line-height-1_6 g-font-weight-600 g-color-white g-font-size-14 g-bg-gray-dark-v1 g-bg-primary--parent-hover g-py-5 g-px-10 mx-auto g-ml-0--md g-transition-0_2 g-transition--ease-in">21:15 - 22:00</div>
							</div>
							<div class="landing-block-node-card-img-container d-md-flex px-0 u-bg-overlay g-width-120 g-width-170--md g-height-120 g-height-170--md g-bg-black-opacity-0_3--after g-bg-white-opacity-0--after--parent-hover g-overflow-hidden g-rounded-50x g-mr-30--md mx-auto g-mb-15 g-mb-0--md g-transition-0_2 g-transition--ease-in col-auto g-pointer-events-before-after-none">
									<img class="landing-block-node-card-img img-fluid g-rounded-50x" src="https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img4.jpg" alt="" />
								</div>
						</div>

						<div class="col-md-7 order-1 order-sm-2 px-0 g-mb-15 g-mb-0--md">
							<div class="media d-block d-md-flex">
								

								<div class="media-body align-self-center">
									<h4 class="landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-5">
										Photoshop vs Sketch</h4>
									<h3 class="landing-block-node-card-title text-uppercase g-font-weight-700 g-font-size-23 g-mb-10">
										Mark Rayman,
										Photographer</h3>
									<div class="landing-block-node-card-text mb-0"><p>Lorem ipsum
											dolor
											sit amet, consectetur adipiscing
											elit. Vestibulum ut scelerisque odio, a viverra arcu. Nulla ut suscipit
											velit,
											non
											dictum quam. Proin hendrerit vulputate mauris a imperdiet</p>
									</div>
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
		'27.one_col_fix_title_and_text_2' =>
			[
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '1500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pb-20 g-pt-60">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">EVENT | <span style="color: rgb(247, 56, 89);">SCHEDULE</span></span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Nam sed erat aliquet libero aliquet commodo. Donec euismod augue non quam finibus, nec iaculis tellus gravida.</p></div>
            </div>
        </div>

    </section>',
			],
		'01.big_with_text_3' =>
			[
				'CODE' => '01.big_with_text_3',
				'SORT' => '2000',
				'CONTENT' => '<section class="landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero g-bg-black-opacity-0_5--after g-py-80" style="background-image: url(\'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img8.jpg\');" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb">
	<div class="container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown animated">
		<h2 class="landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-font-size-30 g-color-white g-mb-20">PROMO | <span style="color: rgb(247, 56, 89);">VIDEO</span></h2>

		<div class="landing-block-node-text g-color-white-opacity-0_7 g-mb-35">Nam sed erat aliquet libero aliquet commodo. Donec euismod augue non quam finibus, nec iaculis tellus gravida. Integer <p>efficitur eros ut dui laoreet, ut blandit turpis tincidunt.</p></div>
		<div class="landing-block-node-button-container">
			<a href="//www.youtube.com/watch?v=q4d8g9Dn3ww" class="landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md g-btn-primary text-uppercase g-rounded-50 g-py-15 g-mb-15" target="_popup" data-url="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&amp;controls=1&amp;loop=0&amp;rel=0&amp;start=0&amp;html5=1&amp;v=q4d8g9Dn3ww">WATCH VIDEO</a>
		</div>
	</div>
</section>',
			],
		'27.one_col_fix_title_and_text_2@2' =>
			[
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '2500',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">UPCOMING | <span style="color: rgb(247, 56, 89);">EVENTS</span></span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Nam sed erat aliquet libero aliquet commodo. Donec euismod augue non quam finibus, nec iaculis tellus gravida.</p></div>
            </div>
        </div>

    </section>',
			],
		'20.2.three_cols_fix_img_title_text' =>
			[
				'CODE' => '20.2.three_cols_fix_img_title_text',
				'SORT' => '3000',
				'CONTENT' => '<section class="landing-block g-pt-10 g-pb-20">
	<div class="container">
		<div class="row landing-block-inner">

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img1.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-14">UI/UX DESIGN</h3>
				<div class="landing-block-node-text g-font-size-14">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img2.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-14">UI/UX DESIGN</h3>
				<div class="landing-block-node-text g-font-size-14">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

			<div class="landing-block-card landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 js-animation fadeIn">
				<img class="landing-block-node-img img-fluid g-mb-30" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img3.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

				<h3 class="landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-font-size-14">UI/UX DESIGN</h3>
				<div class="landing-block-node-text g-font-size-14">
					<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>
				</div>
			</div>

		</div>
	</div>
</section>',
			],
		'27.one_col_fix_title_and_text_2@3' =>
			[
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '3500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 js-animation fadeInUp animated g-pt-60 g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: bold;">LATEST | <span style="color: rgb(247, 56, 89);">POSTS</span></span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Nam sed erat aliquet libero aliquet commodo. Donec euismod augue non quam finibus, nec iaculis tellus gravida.</p></div>
            </div>
        </div>

    </section>',
			],
		'30.2.three_cols_fix_img_and_links' =>
			[
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '4000',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-30 g-pb-20">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img11.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/800x496/img10.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'30.2.three_cols_fix_img_and_links@2' =>
			[
				'CODE' => '30.2.three_cols_fix_img_and_links',
				'SORT' => '4500',
				'CONTENT' => '<section class="landing-block g-bg-gray-light-v5 g-pt-30 g-pb-20">

        <div class="container">
            <div class="row landing-block-inner">

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x960/img8.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
                    <article class="u-shadow-v28 g-bg-white">
                    <div class="landing-block-node-img-container">
                        <img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x960/img7.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
                                <a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
                            </div>
                        </div>
                    </article>
                </div>


				<div class="landing-block-card col-sm-6 col-md-4 js-animation fadeIn">
					<article class="u-shadow-v28 g-bg-white">
					<div class="landing-block-node-img-container">
						<img class="landing-block-node-img img-fluid w-100" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1200x960/img9.jpg" alt="" data-fileid="-1" data-filehash="9eef207add73028ae50f74a9033c20cb" />

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
								<a class="landing-block-node-link-more u-link-v5 g-color-primary--hover g-font-weight-500" href="#" target="_self">Read More</a>
							</div>
						</div>
					</article>
				</div>

            </div>
        </div>

    </section>',
			],
		'27.one_col_fix_title_and_text_2@4' =>
			[
				'CODE' => '27.one_col_fix_title_and_text_2',
				'SORT' => '5000',
				'CONTENT' => '<section class="landing-block js-animation fadeInUp animated g-pt-60 g-pb-20">

        <div class="container g-max-width-800 g-py-20">
            <div class="text-center g-mb-20">
                <h2 class="landing-block-node-title g-font-weight-400"><span style="font-weight: 700;">PRICING | <span style="color: rgb(247, 56, 89);">REGISTRATION</span></span></h2>
                <div class="landing-block-node-text g-font-size-16"><p>Nam sed erat aliquet libero aliquet commodo. Donec euismod augue non quam finibus, nec iaculis tellus gravida. </p></div>
            </div>
        </div>

    </section>',
			],
		'11.three_cols_fix_tariffs' =>
			[
				'CODE' => '11.three_cols_fix_tariffs',
				'SORT' => '5500',
				'CONTENT' => '<section class="landing-block g-pt-30 g-pb-20">
        <div class="container">

            <div class="row no-gutters landing-block-inner">

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">BASIC PASS</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$25.00</span></div>
                                <div class="landing-block-node-price-text"> </div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Sed arcu erat, facilisis at tortor vel, blandit tristique enim. Donec dapibus neque consectetur tellus pretium, eget lacinia velit ullamcorper.</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">REGISTER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10 g-mt-minus-20">
                        <div class="g-bg-gray-light-v5 g-py-50 g-px-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">Advanced pass</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$50.00</span></div>
                                <div class="landing-block-node-price-text"> </div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Sed arcu erat, facilisis at tortor vel, blandit tristique enim. Donec dapibus neque consectetur tellus pretium, eget lacinia velit ullamcorper.</li></ul>
                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">REGISTER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div>

                <div class="landing-block-card js-animation col-md-4 g-mb-30 g-mb-0--md fadeInUp">
                    <article class="text-center g-brd-around g-brd-gray-light-v5 g-pa-10">
                        <div class="g-bg-gray-light-v5 g-pa-30">
                            <h5 class="landing-block-node-title text-uppercase g-font-weight-500 g-mb-10">FULL PASS</h5>
                            <div class="landing-block-node-subtitle g-font-style-normal"> </div>

                            <hr class="g-brd-gray-light-v3 g-my-10" />

                            <div class="g-color-primary g-my-20">
								<div class="landing-block-node-price g-font-size-30 g-line-height-1_2"><span style="font-weight: bold;">$75.00</span></div>
                                <div class="landing-block-node-price-text"> </div>
                            </div>

                            <hr class="g-brd-gray-light-v3 g-mt-10 mb-0" />

                            <ul class="landing-block-node-price-list list-unstyled g-mb-25"><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12">Sed arcu erat, facilisis at tortor vel, blandit tristique enim. Donec dapibus neque consectetur tellus pretium, eget lacinia velit ullamcorper.</li></ul>

                            <div class="landing-block-node-price-container">
                            	<a class="landing-block-node-price-button btn g-btn-type-solid g-btn-size-md g-btn-px-m text-uppercase g-btn-primary rounded-0" href="#" target="_self">REGISTER NOW</a>
                        	</div>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </section>',
			],
		'24.3.image_gallery_6_cols_fix_3' =>
			[
				'CODE' => '24.3.image_gallery_6_cols_fix_3',
				'SORT' => '6000',
				'CONTENT' => '<section class="landing-block js-animation text-center g-py-90 zoomIn">
	<div class="landing-block-node-container container g-brd-gray-light-v4">
		<div class="row g-brd-top g-brd-left g-brd-color-inherit mx-0">
			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img5.png" alt="" />
				</a>
			</div>

			<div class="landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50">
				<a href="#" class="landing-block-card-logo-link">
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png" alt="" />
				</a>
			</div>
		</div>
	</div>
</section>',
			],
		'33.1.form_1_transparent_black_left_text' =>
			[
				'CODE' => '33.1.form_1_transparent_black_left_text',
				'SORT' => '6500',
				'CONTENT' => '<section class="landing-block landing-block-node-bgimg landing-semantic-color-overlay g-pos-rel g-pt-120 g-pb-120 g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after"
	style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1600x827/img1.jpg);">

	<div class="container g-z-index-1 g-pos-rel">
		<div class="row align-items-center">

			<div class="col-md-4 g-mb-60">
				<h2 class="landing-block-node-main-title landing-semantic-title-medium js-animation fadeInUp h1 g-color-white mb-4"><span style="font-weight: bold;">CONTACT | <span style="color: rgb(247, 56, 89);">INFORMATION</span></span></h2>

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
				'SORT' => '7000',
				'CONTENT' => '<section class="landing-block js-animation animation-none">
	<div class="text-center g-pa-10">
		<div class="g-width-600 mx-auto">
			<div class="landing-block-node-text g-font-size-12  js-animation animation-none">
				<p>&copy; 2022 All rights reserved.</p>
			</div>
		</div>
	</div>
</section>',
			],
	]
];