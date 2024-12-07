<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$smileys = [];
$gallery = \CSmile::getByGalleryId(\CSmile::TYPE_SMILE);
foreach ($gallery as $smile)
{
	$typings = explode(' ', $smile['TYPING']);
	foreach ($typings as $typing)
	{
		$smileys[] = [
			'name' => $smile['NAME'],
			'image' => \CSmile::PATH_TO_SMILE . $smile['SET_ID'] . '/' . $smile['IMAGE'],
			'typing' => $typing,
			'width' => (int)$smile['IMAGE_WIDTH'],
			'height' => (int)$smile['IMAGE_HEIGHT'],
		];
	}
}

return [
	'js' => 'dist/smiley.bundle.js',
	'rel' => [
		'main.core',
		'ui.text-parser',
	],
	'settings' => [
		'smileys' => $smileys,
	],
	'skip_core' => false,
];
