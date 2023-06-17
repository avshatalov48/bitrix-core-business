<?php

namespace Bitrix\Socialnetwork\Component\LogList;

class Assets
{
	public $component = null;

	private static $assetFiles = [
		'/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css',
	];

	public function __construct($params)
	{
		if (!empty($params['component']))
		{
			$this->component = $params['component'];
		}
	}

	public function getComponent()
	{
		return $this->component;
	}

	public function checkRefreshNeeded(&$result): bool
	{
		$params = $this->getComponent()->arParams;
		if (empty($params['assetsCheckSum']))
		{
			return true;
		}

		$currentAssetsCheckSum = self::getCheckSum();
		if (empty($currentAssetsCheckSum))
		{
			return true;
		}

		if ($currentAssetsCheckSum === $params['assetsCheckSum'])
		{
			return true;
		}

		$result['FORCE_PAGE_REFRESH'] = 'Y';

		return false;
	}

	public function getAssetsCheckSum(&$result): void
	{
		$result['ASSETS_CHECKSUM'] = self::getCheckSum();
	}

	public static function getCheckSum()
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$assetsData = [];

		$documentRoot = \Bitrix\Main\Application::getDocumentRoot();
		foreach(self::$assetFiles as $filePath)
		{
			$file = new \Bitrix\Main\IO\File($documentRoot . $filePath);
			if (!$file->isExists())
			{
				continue;
			}

			$assetsData[] = [
				$filePath => $file->getModificationTime()
			];
		}

		return md5(serialize($assetsData));
	}
}
