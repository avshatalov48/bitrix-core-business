<?php

namespace Bitrix\Main\UI\Viewer\Renderer;

class Code extends Renderer
{
	const JS_TYPE_CODE = 'code';

	public static function getJsType()
	{
		return self::JS_TYPE_CODE;
	}

	public static function getAllowedContentTypes()
	{
		return [
			'application/javascript',
			'application/ecmascript',
			'application/x-ecmascript',
			'application/x-javascript',
			'text/ecmascript',
			'text/javascript1.0',
			'text/javascript1.1',
			'text/javascript1.2',
			'text/javascript1.3',
			'text/javascript1.4',
			'text/javascript1.5',
			'text/jscript',
			'text/livescript',
			'text/x-ecmascript',
			'text/x-javascript',
			'text/javascript',
			'application/json',
			'text/html',
			'text/css',
			'text/php',
			'application/x-php',
			'application/x-httpd-php',
			'application/x-httpd-php',
			'application/x-httpd-php',
			'application/x-httpd-php',
			'text/x-comma-separated-values',
			'text/tab-separated-values',
			'text/csv',
			'text/csv-schema',
			'application/xhtml+xml',
			'text/plain',
			'application/xml',
			'text/markdown',
			'text/x-script.phyton',
			'application/x-bsh',
			'application/x-sh',
			'application/x-shar',
			'text/x-script.sh',
			'text/x-c',
		];
	}

	public function render()
	{
		return null;
	}

	public function getData()
	{
		return [
			'src' => $this->sourceUri,
		];
	}
}