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
		"g-pt-70 g-pt-55--md"
	);
}
?>

<header class="landing-block landing-block-menu u-header u-header--static u-header--show-hide u-header--change-appearance"
		data-header-fix-moment="100"
		data-header-fix-effect="slide">
	<div class="u-header__section g-bg-white g-transition-0_3 g-py-16 g-py-10--md"
		 data-header-fix-moment-exclude="g-bg-white"
		 data-header-fix-moment-classes="u-shadow-v27 g-bg-white-opacity-0_9">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/agency-logo-dark.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 ml-auto g-pt-20 g-pt-0--lg">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg active">
							<a href="#about" class="landing-block-node-menu-list-item-link nav-link p-0">About</a><span class="sr-only">(current)</span>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#whyWe" class="landing-block-node-menu-list-item-link nav-link p-0">Why we</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#services" class="landing-block-node-menu-list-item-link nav-link p-0">Services</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#workProcess" class="landing-block-node-menu-list-item-link nav-link p-0">Work process</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#skills" class="landing-block-node-menu-list-item-link nav-link p-0">Skills</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#team" class="landing-block-node-menu-list-item-link nav-link p-0">Team</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#testimonials" class="landing-block-node-menu-list-item-link nav-link p-0">Testimonials</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg g-mb-7 g-mb-0--lg">
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
                    <span class="hamburger-inner g-bg-black g-bg-black--after g-bg-black--before"></span>
                  </span>
                </span>
				</button>
				<!-- End Responsive Toggle Button -->
			</div>
		</nav>
	</div>
</header>