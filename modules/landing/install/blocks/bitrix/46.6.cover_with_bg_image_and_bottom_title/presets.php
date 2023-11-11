<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'image' => [
		'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG--IMAGE'),
		'html' =>
			'<div class="landing-block-node-card landing-block-node-card-bgimg js-slide d-flex align-items-end u-bg-overlay g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after"
			 data-card-preset="image"
			 style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img7.jpg);">
			<div class="u-bg-overlay__inner w-100">
				<div class="g-max-width-645 py-0 g-px-30 g-pb-30">
					<h2 class="landing-block-node-card-title js-animation fadeInUp g-line-height-1 g-font-weight-700 g-font-size-90 g-color-white g-mb-15">
						Company24 agency</h2>
					<div class="landing-block-node-card-text-container js-animation fadeInUp row align-items-start">
						<div class="landing-block-node-card-text g-color-white-opacity-0_5 mb-0 col-12 col-md-9">
							<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
								iaculis laoreet.
							</p>
						</div>
						<div class="col-md-3 col-lg-3">
							<a href="#"
							   class="landing-block-node-card-button text-uppercase btn g-btn-outline-white rounded-0 g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m">
								Read more
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>',
		'values' => [
			'.landing-block-node-card-title' => 'Company24 agency',
			'.landing-block-node-card-text' => '<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus iaculis laoreet.</p>',
			'.landing-block-node-card-button' => [
				'href' => '/',
				'text' => 'Read more',
				'target' => '_blank',
			],
			'.landing-block-node-card-bgimg' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img7.jpg',
				'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img7.jpg',
			],
		],
	],
	
	'video' => [
		'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG--VIDEO'),
		'html' =>
			'<div class="landing-block-node-card js-slide d-flex align-items-end u-bg-overlay g-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after bg-video__wrapper" data-card-preset="video">
				<div class="landing-block-node-card-videobg-inner embed-responsive bg-video__inner">
					<div class="landing-block-node-card-videobg embed-responsive-item g-video-preview w-100"
						 data-src="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&controls=0&loop=1&mute=1&rel=0"
						 data-source="https://www.youtube.com/watch?v=q4d8g9Dn3ww"
						 data-preview="//img.youtube.com/vi/q4d8g9Dn3ww/sddefault.jpg"
						 style="background-image:url(//img.youtube.com/vi/q4d8g9Dn3ww/sddefault.jpg)"
					></div>
				</div>
	
				<div class="u-bg-overlay__inner w-100">
					<div class="g-max-width-645 py-0 g-px-30 g-pb-30">
						<h2 class="landing-block-node-card-title js-animation fadeInUp g-line-height-1 g-font-weight-700 g-font-size-90 g-color-white g-mb-15">
							Company24 video</h2>
						<div class="landing-block-node-card-text-container js-animation fadeInUp row align-items-start">
							<div class="landing-block-node-card-text g-color-white-opacity-0_5 mb-0 col-12 col-md-9">
								<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
									finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus
									iaculis laoreet.
								</p>
							</div>
							<div class="col-md-3">
								<a href="#"
								   class="landing-block-node-card-button text-uppercase btn g-btn-outline-white rounded-0 g-btn-type-outline g-btn-white g-btn-size-md g-btn-px-m">
									Read more
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>',
		'values' => [
			'.landing-block-node-card-title' => 'Company24 video',
			'.landing-block-node-card-text' => '<p>Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in finibus dapibus. Ut ac massa sodales, mattis enim id, efficitur tortor. Nullam faucibus iaculis laoreet.</p>',
			'.landing-block-node-card-button' => [
				'href' => '/',
				'text' => 'Read more',
				'target' => '_blank',
			],
			'.landing-block-node-card-videobg' => '//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&controls=0&loop=1&mute=1&rel=0',
		],
	],
];