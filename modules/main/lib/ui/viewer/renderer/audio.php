<?php

namespace Bitrix\Main\UI\Viewer\Renderer;

use Bitrix\Main\Loader;

class Audio extends Renderer
{
	const JS_TYPE_AUDIO = 'audio';

	public static function getJsType()
	{
		return self::JS_TYPE_AUDIO;
	}

	public static function getAllowedContentTypes()
	{
		return [
			'audio/mp3',
			'audio/ogg',
			'audio/mpeg'
		];
	}

	public function render()
	{
		Loader::includeModule('fileman');

		return \CJSCore::getHTML(['player']);
	}

	public function getData()
	{
		return [
			'src' => $this->sourceUri,
		];
	}
}