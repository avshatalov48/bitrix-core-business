<?php

namespace Bitrix\Main\UI\Viewer\Renderer;

class Pdf extends Renderer
{
	const WIDTH  = 900;
	const HEIGHT = 800;

	const JS_TYPE_DOCUMENT = 'document';

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
		return self::JS_TYPE_DOCUMENT;
	}

	public static function getAllowedContentTypes()
	{
		return [
			'application/pdf',
		];
	}

	public function render()
	{
		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:pdf.viewer',
			'',
			[
				'TITLE' => $this->name,
				'PATH' => $this->sourceUri,
				'HEIGHT' => $this->getHeight(),
				'WIDTH' => $this->getWidth(),
				'sizeType' => $this->getOption('sizeType', 'absolute'),
			]
		);

		return ob_get_clean();
	}

	public function getData()
	{
		return [
			'src' => $this->sourceUri,
		];
	}
}