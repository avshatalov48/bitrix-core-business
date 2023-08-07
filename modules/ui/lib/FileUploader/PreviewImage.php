<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\File\Image;
use Bitrix\Main\File\Image\Rectangle;

class PreviewImage
{
	public static function getSize(FileInfo $fileInfo, PreviewImageOptions $options = null): Rectangle
	{
		$previewWidth = 0;
		$previewHeight = 0;
		if ($fileInfo->isImage())
		{
			// Sync with \Bitrix\UI\Controller\FileUploader::previewAction
			$previewWidth = $options ? $options->getWidth() : 300;
			$previewHeight = $options ? $options->getHeight() : 300;
			$previewMode = $options ? $options->getMode() : Image::RESIZE_PROPORTIONAL;

			$destinationRectangle = new Rectangle($previewWidth, $previewHeight);
			$sourceRectangle = new Rectangle($fileInfo->getWidth(), $fileInfo->getHeight());
			$needResize = $sourceRectangle->resize($destinationRectangle, $previewMode);
			if ($needResize)
			{
				$previewWidth = $destinationRectangle->getWidth();
				$previewHeight = $destinationRectangle->getHeight();
			}
			else
			{
				$previewWidth = $sourceRectangle->getWidth();
				$previewHeight = $sourceRectangle->getHeight();
			}
		}

		return new Rectangle($previewWidth, $previewHeight);
	}
}