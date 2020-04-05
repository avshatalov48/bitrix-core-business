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
		"g-pt-65 g-pt-55--md"
	);
}
?>

<header class="landing-block landing-block-menu g-bg-gray-dark-v1 u-header u-header--sticky-top u-header--change-appearance"
		data-header-fix-moment="600">
	<div class="u-header__section g-transition-0_3 g-py-16 g-py-10--md"
		 data-header-fix-moment-classes="u-shadow-v27">
		<nav class="navbar navbar-expand-lg p-0 g-px-15">
			<div class="container">
				<!-- Logo -->
				<a href="#" class="navbar-brand landing-block-node-menu-logo-link u-header__logo">
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180"
						 src="/bitrix/templates/landing24/assets/img/music-logo.png" alt="">
				</a>
				<!-- End Logo -->

				<!-- Navigation -->
				<div class="collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
					<ul class="landing-block-node-menu-list js-scroll-nav navbar-nav text-uppercase g-font-weight-700 g-font-size-11 g-pt-20 g-pt-0--lg ml-auto">
						<li class="nav-item landing-block-node-menu-list-item g-mr-15--lg g-mb-7 g-mb-0--lg active">
							<a href="#home" class="landing-block-node-menu-list-item-link nav-link p-0">Home</a><span
									class="sr-only">(current)</span>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#musicAlbums" class="landing-block-node-menu-list-item-link nav-link p-0">Music
								albums</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#events" class="landing-block-node-menu-list-item-link nav-link p-0">Events</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#tours" class="landing-block-node-menu-list-item-link nav-link p-0">Tours</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#videos" class="landing-block-node-menu-list-item-link nav-link p-0">Videos</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#gallery" class="landing-block-node-menu-list-item-link nav-link p-0">Gallery</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#twitterFeeds" class="landing-block-node-menu-list-item-link nav-link p-0">Twitter
								feeds</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#musicList" class="landing-block-node-menu-list-item-link nav-link p-0">Music
								list</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-mx-15--lg g-mb-7 g-mb-0--lg">
							<a href="#blog" class="landing-block-node-menu-list-item-link nav-link p-0">Blog</a>
						</li>
						<li class="nav-item landing-block-node-menu-list-item g-ml-15--lg g-mb-7 g-mb-0--lg">
							<a href="#contact" class="landing-block-node-menu-list-item-link nav-link p-0">Contact</a>
						</li>
					</ul>
				</div>
				<!-- End Navigation -->

				<!-- Responsive Toggle Button -->
				<button class="navbar-toggler btn g-line-height-1 g-brd-none g-pa-0 g-mt-9 ml-auto" type="button"
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