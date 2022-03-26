<?php
namespace Bitrix\Landing\Block;

class DesignerRepo extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'DesignerRepoTable';

	/**
	 * Installs repo data.
	 * @return void
	 */
	public static function installRepo(): void
	{
		Designer::registerRepoElement([
			'XML_ID' => 'text',
			'SORT' => 100,
			'HTML' => '
<div class="landing-block-node-text g-pointer-events-all g-pb-1 text-left">
	<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-text' => [
						'type' => 'text'
					]
				],
				'style' => [
					'.landing-block-node-text' => [
						'type' => ['typo']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'title',
			'SORT' => 200,
			'HTML' => '<h2 class="landing-block-node-title g-pointer-events-all h2 text-center">The Title</h2>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-title' => [
						'type' => 'text'
					]
				],
				'style' => [
					'.landing-block-node-title' => [
						'type' => ['typo']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'text_title',
			'SORT' => 300,
			'HTML' => '
<div class="landing-block-node-containertext g-pointer-events-all">
	<h2 class="landing-block-node-title h2 text-center">The Title with text</h2>
	<div class="landing-block-node-text g-pb-1 text-left">
		<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
	</div>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-title' => [
						'type' => 'text'
					],
					'.landing-block-node-text' => [
						'type' => 'text'
					]
				],
				'style' => [
					'.landing-block-node-containertext' => [
						'type' => ['margins']
					],
					'.landing-block-node-title' => [
						'type' => ['typo']
					],
					'.landing-block-node-text' => [
						'type' => ['typo']
					]
				]
			]
		]);
		// todo: full width?
		Designer::registerRepoElement([
			'XML_ID' => 'img',
			'SORT' => 400,
			'HTML' => '
<div class="landing-block-node-containerimg g-pointer-events-all text-center">
	<img class="landing-block-node-img img-fluid d-inline" src="https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img12.jpg" alt="">
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-img' => [
						'type' => 'img'
					]
				],
				'style' => [
					'.landing-block-node-containerimg' => [
						'type' => ['text-align', 'margins']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'video',
			'SORT' => 500,
			'HTML' => '
<div class="landing-block-node-video-container g-pointer-events-all embed-responsive embed-responsive-16by9 mx-auto w-100">
	<div class="landing-block-node-embed embed-responsive-item g-video-preview w-100"
		data-src="//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=0&controls=1&loop=1&mute=0&rel=0"
		data-source="https://www.youtube.com/watch?v=q4d8g9Dn3ww"
		data-preview="//img.youtube.com/vi/q4d8g9Dn3ww/sddefault.jpg"
		style="background-image:url(//img.youtube.com/vi/q4d8g9Dn3ww/sddefault.jpg)"
	></div>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-embed' => [
						'type' => 'embed'
					]
				],
				'assets' => [
					'ext' => ['landing_inline_video']
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'button',
			'SORT' => 600,
			'HTML' => '
<div class="landing-block-node-containerbutton g-pointer-events-all g-flex-centered g-pt-10 g-pb-10">
	<a href="#" class="landing-block-node-button btn text-uppercase g-btn-primary rounded-0 g-btn-type-solid g-btn-size-md g-btn-px-m g-ml-10 g-mr-10">Button</a>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-button' => [
						'type' => 'link'
					]
				],
				'style' => [
					'.landing-block-node-containerbutton' => [
						'type' => ['row-align', 'paddings']
					],
					'.landing-block-node-button' => [
						'type' => ['button', 'margin-left', 'margin-right']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'double_button',
			'SORT' => 700,
			'HTML' => '
<div class="landing-block-node-containerbuttons g-pointer-events-all g-flex-centered g-pt-10 g-pb-10">
	<a href="#" class="landing-block-node-button btn text-uppercase g-btn-primary rounded-0 g-btn-type-solid g-btn-size-md g-btn-px-m g-ml-10 g-mr-10">Button</a>
	<a href="#" class="landing-block-node-button2 btn text-uppercase g-btn-primary rounded-0 g-btn-type-outline g-btn-size-md g-btn-px-m g-ml-10 g-mr-10">Button</a>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-button' => [
						'type' => 'link'
					],
					'.landing-block-node-button2' => [
						'type' => 'link'
					]
				],
				'style' => [
					'.landing-block-node-containerbuttons' => [
						'type' => ['row-align', 'paddings']
					],
					'.landing-block-node-button' => [
						'type' => ['button', 'margin-left', 'margin-right']
					],
					'.landing-block-node-button2' => [
						'type' => ['button', 'margin-left', 'margin-right']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'icon',
			'SORT' => 800,
			'HTML' => '
<div class="landing-block-node-containericon g-pointer-events-all g-color-primary g-font-size-24">
	<i class="landing-block-node-icon fa fa-cog"></i>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-icon' => [
						'type' => 'icon'
					]
				],
				'style' => [
					'.landing-block-node-containericon' => [
						'type' => ['text-align', 'color', 'font-size', 'margins']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'icon_text',
			'SORT' => 900,
			'HTML' => '
<div class="d-flex">
	<div class="landing-block-node-containericon g-pointer-events-all g-color-primary g-font-size-24 g-mr-10">
		<i class="landing-block-node-icon fa fa-cog"></i>
	</div>
	<div class="landing-block-node-text g-pointer-events-all g-pb-1 text-left">
		<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
	</div>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-icon' => [
						'type' => 'icon'
					],
					'.landing-block-node-text' => [
						'type' => 'text'
					]
				],
				'style' => [
					'.landing-block-node-containericon' => [
						'type' => ['text-align', 'color', 'font-size', 'margins']
					],
					'.landing-block-node-text' => [
						'type' => ['typo']
					]
				]
			]
		]);
		Designer::registerRepoElement([
			'XML_ID' => 'icon_title',
			'SORT' => 1000,
			'HTML' => '
<div class="d-flex justify-content-center">
	<div class="landing-block-node-containericon g-pointer-events-all g-color-primary g-font-size-24 g-mr-10">
		<i class="landing-block-node-icon fa fa-cog"></i>
	</div>
	<h2 class="landing-block-node-title g-pointer-events-all h2 text-left">The Title with icon</h2>
</div>',
			'MANIFEST' => [
				'nodes' => [
					'.landing-block-node-icon' => [
						'type' => 'icon'
					],
					'.landing-block-node-title' => [
						'type' => 'text'
					],
				],
				'style' => [
					'.landing-block-node-containericon' => [
						'type' => ['text-align', 'color', 'font-size', 'margins']
					],
					'.landing-block-node-title' => [
						'type' => ['typo']
					]
				]
			]
		]);
	}
}
