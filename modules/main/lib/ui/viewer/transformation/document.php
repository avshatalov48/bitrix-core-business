<?php

namespace Bitrix\Main\UI\Viewer\Transformation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\MimeType;
use Bitrix\Transformer\DocumentTransformer;

class Document extends Transformation
{
	public static function getInputContentTypes()
	{
		return [
			'text/html',
			'text/html',
			'text/plain',
			'application/xml',
			'application/pdf',
			'application/msword',
			'application/vnd.ms-excel',
			'application/vnd.ms-powerpoint',
			'application/vnd.ms-visio',
			'application/vnd.ms-visio.drawing',
			'application/vnd.ms-word.document.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.ms-word.template.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.template',
			'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.ms-excel.addin.macroEnabled.12',
			'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'application/vnd.ms-excel.sheet.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.ms-excel.template.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'application/msword',
			'application/vnd.ms-excel',
			'application/xml',
			'image/vnd.djvu',
			'application/epub+zip',
			'message/rfc822',
			'message/rfc822',
			'application/vnd.oasis.opendocument.text',
		];
	}

	public function getOutputContentType()
	{
		return MimeType::getByFileExtension($this->getOutputExtension());
	}

	public function getOutputExtension()
	{
		return 'pdf';
	}

	public function buildTransformer()
	{
		return new DocumentTransformer();
	}

	public function getInputMaxSize()
	{
		$defaultValue = ModuleManager::isModuleInstalled('bitrix24')? 40 : 10;

		return intval(Option::get('main', 'max_size_for_document_transformation', $defaultValue)) * 1024 * 1024;
	}
}
