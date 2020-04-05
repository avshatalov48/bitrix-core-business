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
		"g-pt-50 g-pt-65--md"
	);
}
?>

<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky-top u-header--show-hide u-header--toggle-section"
		data-header-fix-moment="100"
		data-header-fix-effect="slide">
	<div class="u-header__section u-shadow-v27 g-transition-0_3 g-py-12 g-py-20--md">
		<nav class="navbar navbar-expand-lg py-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo p-0">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/app-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<div id="navBar" class="collapse navbar-collapse">
					<!-- Navigation -->
					<div class="navbar-collapse align-items-center flex-sm-row">
						<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav g-flex-right--xs text-uppercase w-100 g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg">
							<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg active">
								<a href="#home"
								   class="landing-block-node-menu-list-item-link nav-link p-0">Home</a><span
										class="sr-only">(current)</span>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#about" class="landing-block-node-menu-list-item-link nav-link p-0">About</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#benefits"
								   class="landing-block-node-menu-list-item-link nav-link p-0">Benefits</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#whyWe" class="landing-block-node-menu-list-item-link nav-link p-0">Why we</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#features"
								   class="landing-block-node-menu-list-item-link nav-link p-0">Features</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#howItWorks" class="landing-block-node-menu-list-item-link nav-link p-0">How it
									works</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#subscribe" class="landing-block-node-menu-list-item-link nav-link p-0">Subscribe</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
								<a href="#FAQ" class="landing-block-node-menu-list-item-link nav-link p-0">FAQ</a>
							</li>
							<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg g-mb-7 g-mb-0--lg">
								<a href="#contact"
								   class="landing-block-node-menu-list-item-link nav-link p-0">Contact</a>
							</li>
						</ul>
					</div>
					<!-- End Navigation -->

				</div>

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