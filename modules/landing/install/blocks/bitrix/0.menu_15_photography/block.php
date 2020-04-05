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
		"g-pt-155 g-pt-130--md"
	);
}
?>

<header class="landing-block landing-block-menu landing-ui-pattern-transparent u-header u-header--sticky-top u-header--toggle-section u-header--change-appearance"
		data-header-fix-moment="300">
	<!-- Top Bar -->
	<div class="landing-block-node-top-block u-header__section u-header__section--hidden g-bg-white g-transition-0_3 g-pt-15">
		<div class="container">
			<div class="row flex-column flex-md-row align-items-center justify-content-md-end text-uppercase g-font-weight-700 g-font-size-13 g-mt-minus-10">
				<div class="col-auto text-center text-md-left g-font-size-10 g-color-gray-dark-v5 mr-md-auto g-px-15 g-mt-10">
					<div class="d-inline-block g-mb-0--md g-ml-0--md g-mx-30--xs">
						<div class="d-inline-block landing-block-node-menu-contact-title">
							<p>Phone Number: </p>
						</div>
						<a class="landing-block-node-menu-contact-link" href="tel:+4554554554"><strong>+4 554 554
								554</strong></a>
					</div>

					<div class="d-inline-block">
						<div class="d-inline-block landing-block-node-menu-contact-title">
							<p>Email:</p>
						</div>
						<a class="landing-block-node-menu-contact-link" href="mailto:support@company24.com"><strong>support@company24.com</strong></a>
					</div>
				</div>

				<div class="col-auto g-px-15 g-mt-10">
					<!--					<ul class="list-inline mb-0 g-mx-minus-3 landing-block-node-menu-list-social">-->
					<!--						<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
					<!--							<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--xs g-rounded-50x g-bg-white g-color-gray-dark-v5 g-color-primary--hover" href="#">-->
					<!--								<i class="landing-block-node-menu-social-list-item-img fa fa-twitter"></i>-->
					<!--							</a>-->
					<!--						</li>-->
					<!--						<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
					<!--							<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--xs g-rounded-50x g-bg-white g-color-gray-dark-v5 g-color-primary--hover" href="#">-->
					<!--								<i class="landing-block-node-menu-social-list-item-img fa fa-facebook"></i>-->
					<!--							</a>-->
					<!--						</li>-->
					<!--						<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3 g-mr-minus-2--lg">-->
					<!--							<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--xs g-rounded-50x g-bg-white g-color-gray-dark-v5 g-color-primary--hover" href="#">-->
					<!--								<i class="landing-block-node-menu-social-list-item-img fa fa-google-plus"></i>-->
					<!--							</a>-->
					<!--						</li>-->
					<!--						<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
					<!--							<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--xs g-rounded-50x g-bg-white g-color-gray-dark-v5 g-color-primary--hover" href="#">-->
					<!--								<i class="landing-block-node-menu-social-list-item-img fa fa-instagram"></i>-->
					<!--							</a>-->
					<!--						</li>-->
					<!--						<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
					<!--							<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--xs g-rounded-50x g-bg-white g-color-gray-dark-v5 g-color-primary--hover" href="#">-->
					<!--								<i class="landing-block-node-menu-social-list-item-img fa fa-linkedin"></i>-->
					<!--							</a>-->
					<!--						</li>-->
					<!--					</ul>-->
				</div>
			</div>
		</div>
	</div>
	<!-- End Top Bar -->

	<div class="landing-block-node-bottom-block u-header__section g-bg-gray-light-v5 g-py-30"
		 data-header-fix-moment-classes="u-shadow-v27">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/photography-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-25--lg g-mb-7 g-mb-0--lg active">
							<a href="#home" class="landing-block-node-menu-list-item-link nav-link p-0">Home</a><span
									class="sr-only">(current)</span>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#promo" class="landing-block-node-menu-list-item-link nav-link p-0">Promo</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#topWorks" class="landing-block-node-menu-list-item-link nav-link p-0">Top
								works</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#services" class="landing-block-node-menu-list-item-link nav-link p-0">Services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#gallery" class="landing-block-node-menu-list-item-link nav-link p-0">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#about" class="landing-block-node-menu-list-item-link nav-link p-0">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-25--lg g-mb-7 g-mb-0--lg">
							<a href="#offers" class="landing-block-node-menu-list-item-link nav-link p-0">Offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-25--lg g-mb-7 g-mb-0--lg">
							<a href="#contact" class="landing-block-node-menu-list-item-link nav-link p-0">Contact</a>
						</li>
					</ul>
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