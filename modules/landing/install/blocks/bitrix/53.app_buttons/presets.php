<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use Bitrix\Landing\Manager;

$result = [
	'app-store' => [
		'name' => 'Download app from App Store',
		'html' => '
			<div class="landing-block-node-card g-px-10 g-py-10"
				data-card-preset="app-store">
				<a href="#" class="landing-block-node-button">
					<img class="landing-block-node-button-img g-height-42"
							src="https://cdn.bitrix24.site/bitrix/images/landing/app-store-badge.svg"
							alt="Download app from App Store">
				</a>
			</div>',
		'values' => [
			'.landing-block-node-button' => [
				'href' => '#',
			],
			'.landing-block-node-button-img' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/app-store-badge.svg',
				'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/app-store-badge.svg',
			],
		],
		'disallow' => ['.landing-block-node-button-img'],
	],
	'play-market' => [
		'name' => 'Download app from Play Market',
		'html' => '
			<div class="landing-block-node-card g-px-10 g-py-10"
				 data-card-preset="play-market">
				<a href="#" class="landing-block-node-button">
					<img class="landing-block-node-button-img g-height-42" 
							src="https://cdn.bitrix24.site/bitrix/images/landing/google-play-badge.svg"
							alt="Download app from Play Market">
				</a>
			</div>',
		'values' => [
			'.landing-block-node-button' => [
				'href' => '#',
			],
			'.landing-block-node-button-img' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/google-play-badge.svg',
				'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/google-play-badge.svg',
			],
		],
		'disallow' => ['.landing-block-node-button-img'],
	],
	'custom-picture' => [
		'name' => Loc::getMessage('LANDING_BLOCK_53_CUSTOM_PICTURE'),
		'html' => '
			<div class="landing-block-node-card g-px-10 g-py-10"
				 data-card-preset="custom-picture">
				<a href="#" class="landing-block-node-button">
					<img class="landing-block-node-button-img-custom g-height-42" 
							src="https://cdn.bitrix24.site/bitrix/images/landing/custom-badge.png"
							alt="">
				</a>
			</div>',
		'values' => [
			'.landing-block-node-button' => [
				'href' => '#',
			],
			'.landing-block-node-button-img-custom' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/custom-badge.png',
				'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/custom-badge.png',
			],
		],
		'disallow' => [''],
	],
];

return $result;