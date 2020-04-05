<?php

namespace Bitrix\Main\UI\Viewer\Renderer;

use Bitrix\Main\Loader;

class Video extends Renderer
{
	const WIDTH  = 700;
	const HEIGHT = 700;

	const JS_TYPE_VIDEO = 'video';

	public function getWidth()
	{
		return $this->getOption('width', self::WIDTH);
	}

	public function getHeight()
	{
		return $this->getOption('height', self::HEIGHT);
	}

	public static function getJsType()
	{
		return self::JS_TYPE_VIDEO;
	}

	public static function getAllowedContentTypes()
	{
		return [
			'video/mp4',
			'video/x-flv',
			'video/webm',
			'video/ogg',
			'video/quicktime',
		];
	}

	public function render()
	{
		Loader::includeModule('fileman');

		return \CJSCore::getHTML(['player']);
	}

	public function getData()
	{
		$data = [
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
			'contentType' => $this->getOption('contentType'),
			'src' => $this->sourceUri,
		];

		$sources = [
			[
				'src' => $this->sourceUri,
				'type' => $this->getOption('contentType'),
			]
		];
		$sources = $this->modifySourcesByDirtyHacks($sources);

		$altSrc = $this->getOption('alt.sourceUri');
		if ($altSrc)
		{
			array_unshift($sources, [
				'src' => $altSrc,
				'type' => $this->getOption('alt.contentType'),
			]);
		}
		$data['sources'] = $sources;

		return $data;
	}

	protected function modifySourcesByDirtyHacks(array $sources)
	{
		$updatedSources = $sources;
		foreach ($sources as $source)
		{
			if ($source['type'] === 'video/quicktime')
			{
				//some browser can work with quicktime :)
				$updatedSources[] = [
					'src' => $source['src'],
					'type' => 'video/mp4',
				];
			}
		}

		return $updatedSources;
	}
}
