<?php
namespace Bitrix\Socialnetwork\Component\LogList;

class Assets
{
	private static $assetFiles = [
		'/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css',
//		'/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/script.js',
//		'/bitrix/js/socialnetwork/livefeed/dist/livefeed.bundle.js'
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

	public function checkRefreshNeeded(&$result)
	{
		$params = $this->getComponent()->arParams;
		if (empty($params['assetsCheckSum']))
		{
			return true;
		}

		$currentAssetsCheckSum = \Bitrix\Socialnetwork\Component\LogList\Assets::getCheckSum();
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

	public function getAssetsCheckSum(&$result)
	{
		$result['ASSETS_CHECKSUM'] = \Bitrix\Socialnetwork\Component\LogList\Assets::getCheckSum();
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
			$file = new \Bitrix\Main\IO\File($documentRoot.$filePath);
			if (!$file->isExists())
			{
				continue;
			}

			$assetsData[] = [
				$filePath => $file->getModificationTime()
			];
		}

		$result = md5(serialize($assetsData));

		return $result;
	}
}
?>