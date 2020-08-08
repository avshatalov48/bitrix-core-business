<?php
namespace Bitrix\Main\Engine\Response;

use Bitrix\Main\Engine\Response\ContentArea\DataSectionInterface;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetMode;
use Bitrix\Main\Engine\Response\ContentArea\ContentAreaInterface;

/**
 * Response type for rendering ajax html content from action
 */
class HtmlContent extends AjaxJson
{
	private $jsPathList = [];
	private $cssPathList = [];

	/**
	 * @param ContentAreaInterface $content
	 * @param string $status
	 * @param ErrorCollection|null $errorCollection
	 * @param array $additionalResponseParams
	 */
	public function __construct(ContentAreaInterface $content, $status = self::STATUS_SUCCESS, ErrorCollection $errorCollection = null, array $additionalResponseParams = [])
	{
		$html = $content->getHtml();

		$this->collectAssetsPathList();

		$result = [
			'html' => $html,
			'assets' => [
				'css' => $this->getCssList(),
				'js' => $this->getJsList(),
				'string' => $this->getStringList()
			],
			'additionalParams' => $additionalResponseParams,
		];
		if($content instanceof DataSectionInterface)
		{
			$result[$content->getSectionName()] = $content->getSectionData();
		}

		parent::__construct($result, $status, $errorCollection);

		$this->addHeader('X-Process-Assets', 'assets');
	}

	final protected function collectAssetsPathList()
	{
		Asset::getInstance()->getCss();
		Asset::getInstance()->getJs();
		Asset::getInstance()->getStrings();

		$this->jsPathList = Asset::getInstance()->getTargetList('JS');
		$this->cssPathList = Asset::getInstance()->getTargetList('CSS');
	}

	/**
	 * @return array
	 */
	final protected function getJsList()
	{
		$jsList = [];
		foreach($this->jsPathList as $targetAsset)
		{
			$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['JS']))
			{
				$jsList = array_merge($jsList, $assetInfo['JS']);
			}
		}

		return $jsList;
	}

	/**
	 * @return array
	 */
	final protected function getCssList()
	{
		$cssList = [];
		foreach ($this->cssPathList as $targetAsset)
		{
			$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['CSS']))
			{
				$cssList = array_merge($cssList, $assetInfo['CSS']);
			}
		}

		return $cssList;
	}

	/**
	 * @return array
	 */
	final protected function getStringList()
	{
		$stringList = [];
		foreach($this->cssPathList as $targetAsset)
		{
			$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['STRINGS']))
			{
				$stringList = array_merge($stringList, $assetInfo['STRINGS']);
			}
		}

		foreach($this->jsPathList as $targetAsset)
		{
			$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['STRINGS']))
			{
				$stringList = array_merge($stringList, $assetInfo['STRINGS']);
			}
		}

		$stringList[] = Asset::getInstance()->showFilesList();

		return $stringList;
	}
}