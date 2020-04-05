<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->get("landing_mode") != "edit")
{
	\Bitrix\Landing\Manager::setPageView(
		"MainClass",
		"g-pt-70"
	);
}
?>

<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky-top u-header--change-appearance g-z-index-9999"
		data-header-fix-moment="100">
	<div class="u-header__section u-shadow-v27 g-transition-0_3 g-py-17"
		 data-header-fix-moment-exclude="g-py-17"
		 data-header-fix-moment-classes="g-py-12">
		<nav class="navbar navbar-expand-lg g-py-0 g-mt-10">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/courses-logo-dark.png" alt="Logo">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-12 g-pt-20 g-pt-0--lg g-mb-20 g-mb-0--lg ml-auto g-mr-20">
						<li class="landing-block-node-menu-list-item nav-item g-mr-3--lg g-mb-5 g-mb-0--lg active">
							<a href="#home"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Home</a><span
									class="sr-only">(current)</span>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#about"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#ourCourses"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Our
								courses</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#ourNumbers"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Our
								numbers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#gallery"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#teachers"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Teachers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#offers"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#partners"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Partners</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-3--lg g-mb-5 g-mb-0--lg">
							<a href="#blog"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Blog</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-3--lg g-mb-5 g-mb-0--lg">
							<a href="#contact"
							   class="landing-block-node-menu-list-item-link nav-link g-color-black nav-link-color-hover">Contact</a>
						</li>
					</ul>

					<!--					<ul class="list-inline mb-0 landing-block-node-menu-list-social">-->
					<!--						<li class="list-inline-item landing-block-node-menu-list-social-item g-mr-10">-->
					<!--							<a class="landing-block-node-menu-list-social-item-link u-icon-v3 g-width-35 g-height-35 g-font-size-default g-color-gray-light-v1 g-color-white--hover g-bg-gray-light-v5 g-bg-primary--hover g-rounded-50x g-transition-0_2 g-transition--ease-in" href="#"><i class="fa fa-twitter"></i></a>-->
					<!--						</li>-->
					<!--						<li class="list-inline-item landing-block-node-menu-list-social-item g-mr-10">-->
					<!--							<a class="landing-block-node-menu-list-social-item-link u-icon-v3 g-width-35 g-height-35 g-font-size-default g-color-gray-light-v1 g-color-white--hover g-bg-gray-light-v5 g-bg-primary--hover g-rounded-50x g-transition-0_2 g-transition--ease-in" href="#"><i class="fa fa-pinterest-p"></i></a>-->
					<!--						</li>-->
					<!--						<li class="list-inline-item landing-block-node-menu-list-social-item g-mr-10">-->
					<!--							<a class="landing-block-node-menu-list-social-item-link u-icon-v3 g-width-35 g-height-35 g-font-size-default g-color-gray-light-v1 g-color-white--hover g-bg-gray-light-v5 g-bg-primary--hover g-rounded-50x g-transition-0_2 g-transition--ease-in" href="#"><i class="fa fa-facebook"></i></a>-->
					<!--						</li>-->
					<!--					</ul>-->
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 ml-auto" type="button"
						aria-label="Toggle navigation"
						aria-expanded="false"
						aria-controls="navBar"
						data-toggle="collapse"
						data-target="#navBar">
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
</header>