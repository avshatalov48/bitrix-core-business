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
		"g-pt-50 g-pt-80--md"
	);
}
?>

<header class="landing-block landing-block-menu g-bg-white u-header u-header--sticky-top u-header--change-appearance"
		data-header-fix-moment="100">
	<div class="u-header__section g-transition-0_3 g-py-7 g-py-23--md"
		 data-header-fix-moment-exclude="g-py-23--md"
		 data-header-fix-moment-classes="g-py-17--md">
		<nav class="navbar navbar-expand-lg g-py-0">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="landing-block-node-menu-logo-link navbar-brand u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/corporate-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-letter-spacing-1 g-font-size-12 g-pt-20 g-pt-0--lg ml-auto">
						<li class="landing-block-node-menu-list-item nav-item g-mr-15--lg g-mb-7 g-mb-0--lg active">
							<a href="#home-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Home
								</a><span class="sr-only">(current)</span>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#about-section"
							   class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">About</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#portfolio-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Portfolio</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#pricing-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Pricing</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#team-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Team</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#blog-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Blog</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#contact-section" class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Contact</a>
						</li>
						<li class="landing-block-node-menu-list-item nav-item g-ml-15--lg g-mb-7 g-mb-0--lg">
							<a href="#"
							   class="landing-block-node-menu-list-item-link nav-link p-0 g-color-black">Main</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-8 ml-auto" type="button"
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