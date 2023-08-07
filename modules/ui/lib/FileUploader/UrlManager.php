<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

class UrlManager
{
	public static function getDownloadUrl(UploaderController $controller, FileInfo $fileInfo): Uri
	{
		$uri = self::getActionUrl($controller, 'download');
		$uri->addParams(['fileId' => $fileInfo->getId()]);

		return $uri;
	}

	public static function getPreviewUrl(UploaderController $controller, FileInfo $fileInfo): Uri
	{
		$uri = self::getActionUrl($controller, 'preview');
		$uri->addParams(['fileId' => $fileInfo->getId()]);

		return $uri;
	}

	private static function getActionUrl(UploaderController $controller, string $actionName): Uri
	{
		return \Bitrix\Main\Engine\UrlManager::getInstance()->create(
			"ui.fileuploader.{$actionName}",
			[
				'controller' => $controller->getName(),
				'controllerOptions' => Json::encode($controller->getOptions()),
			]
		);
	}
}