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
		"g-pt-130 g-pt-170--lg"
	);
}
?>

<header class="landing-block landing-block-menu u-header u-header--sticky-top u-header--toggle-section u-header--change-appearance"
		data-header-fix-moment="200" >
	<div class="landing-block-node-top-block text-center text-lg-left u-header__section u-header__section--hidden u-header__section--light g-bg-white g-brd-bottom g-brd-gray-light-v4 g-py-20">
		<div class="container">
			<div class="row flex-lg-row align-items-center justify-content-lg-start">
				<div class="col-6 col-sm-3 col-lg-2">
					<!-- Logo -->
					<a href="#" class="navbar-brand landing-block-node-menu-logo-link">
						<img class="landing-block-node-menu-logo img-fluid g-max-width-180"
							 src="/bitrix/templates/landing24/assets/img/real-estate-logo.png" alt="Logo">
					</a>
					<!-- End Logo -->
				</div>

				<div class="col-6 col-sm-9 col-lg-10">
					<div class="row">
						<div class="landing-block-card-menu-contact col-sm g-brd-right--sm g-brd-gray-light-v4">
							<div class="g-pa-10--lg">
								<span class="landing-block-node-menu-contact-img icon icon-screen-smartphone g-valign-middle g-font-size-18 g-color-primary g-mr-5"></span>
								<div class="landing-block-node-menu-contact-title d-inline-block text-uppercase g-font-size-13">
									Call Us
								</div>
								<a href="tel:+469548521" class="landing-block-node-menu-contact-link d-block g-pl-25 g-color-gray-dark-v2 g-font-weight-700">+469 548 521</a>
							</div>
						</div>

						<div class="landing-block-card-menu-contact col-sm g-hidden-md-down g-brd-right--sm g-brd-gray-light-v4">
							<div class="g-pa-10--lg">
								<span class="landing-block-node-menu-contact-img icon icon-clock g-valign-middle g-font-size-18 g-color-primary g-mr-5"></span>
								<div class="landing-block-node-menu-contact-title d-inline-block text-uppercase g-font-size-13">
									Opening time
								</div>
								<strong class="landing-block-node-menu-contact-value d-block g-color-gray-dark-v2 g-pl-25">Mon-Sat: 08.00 -
									18.00</strong>
							</div>
						</div>

						<div class="landing-block-card-menu-contact col-sm g-hidden-sm-down g-brd-right--sm g-brd-gray-light-v4">
							<div class="g-pa-10--lg">
								<span class="landing-block-node-menu-contact-img icon icon-envelope g-valign-middle g-font-size-18 g-color-primary g-mr-5"></span>
								<div class="landing-block-node-menu-contact-title d-inline-block text-uppercase g-font-size-13">
									Email us
								</div>
								<a href="mailto:market@info.com" class="landing-block-node-menu-contact-link d-block g-pl-25 g-color-gray-dark-v2 g-font-weight-700">market@info.com</a>
							</div>
						</div>

						<div class="col-sm g-hidden-sm-down">
							<!--							<ul class="list-inline mb-0 g-pa-10--lg landing-block-node-menu-list-social">-->
							<!--								<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
							<!--									<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover" href="#">-->
							<!--										<i class="landing-block-node-menu-social-list-item-img fa fa-facebook g-font-size-default"></i>-->
							<!--									</a>-->
							<!--								</li>-->
							<!--								<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
							<!--									<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover" href="#">-->
							<!--										<i class="landing-block-node-menu-social-list-item-img fa fa-twitter g-font-size-default"></i>-->
							<!--									</a>-->
							<!--								</li>-->
							<!--								<li class="landing-block-node-menu-social-list-item list-inline-item g-valign-middle g-mx-3">-->
							<!--									<a class="landing-block-node-menu-social-list-item-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover" href="#">-->
							<!--										<i class="landing-block-node-menu-social-list-item-img fa fa-instagram g-font-size-default"></i>-->
							<!--									</a>-->
							<!--								</li>-->
							<!--							</ul>-->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="landing-block-node-bottom-block u-header__section u-header__section--dark g-bg-black g-py-15"
		 data-header-fix-moment-classes="u-shadow-v18">
		<nav class="navbar navbar-expand-lg py-0">
			<div class="container">
				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row g-mr-40--sm" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-13 g-py-10--md mr-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg active">
							<a href="#home"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Home
							</a><span class="sr-only">(current)</span>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#flatsForRent"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Flats
								for rent</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#specialOffers"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Special
								offers</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#ourHouses"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Our
								houses</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#gallery"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Gallery</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#agents"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Agents</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#discount"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Discount</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#testimonials"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg g-mb-7 g-mb-0--lg">
							<a href="#contact"
							   class="landing-block-node-menu-list-item-link nav-link g-color-primary--hover p-0">Contact</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-pos-rel g-line-height-1 g-brd-none g-pa-0 ml-auto" type="button"
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