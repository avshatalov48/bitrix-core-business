<?php

namespace Bitrix\Main\UI\Viewer\Transformation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\MimeType;
use Bitrix\Transformer\VideoTransformer;

class Video extends Transformation
{
	public static function getInputContentTypes()
	{
		return [
			'video/avi',
			'video/mp4',
			'video/webm',
			'video/ogg',
			'video/3gpp',
			'video/quicktime',
			'video/x-flv',
			'video/x-msvideo',
			'video/x-matroska',
			'video/x-m4v',
			'video/h264',
			'video/x-ms-wmv',
		];
	}

	public function getOutputContentType()
	{
		return MimeType::getByFileExtension($this->getOutputExtension());
	}

	public function getOutputExtension()
	{
		return 'mp4';
	}

	public function buildTransformer()
	{
		return new VideoTransformer();
	}

	public function getInputMaxSize()
	{
		if (Loader::includeModule('bitrix24') && \CBitrix24::isLicensePaid())
		{
			return (int)(Option::get('main', 'max_size_for_video_transformation_paid', 3072)) * 1024 * 1024;
		}

		return (int)(Option::get('main', 'max_size_for_video_transformation', 300)) * 1024 * 1024;
	}
}