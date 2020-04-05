<?php

namespace Bitrix\Report\VisualConstructor;



use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetMode;

class BoardComponentButton extends BoardButton
{
	private $componentName;
	private $componentTemplateName = '';
	private $componentParams = [];
	private $jsPathList = array();
	private $cssPathList = array();
	private $processed = false;


	public function __construct($componentName, $componentTemplateName = '', $componentParams = [])
	{
		$this->setComponentName($componentName);
		$this->setComponentTemplateName($componentTemplateName);
		$this->setComponentParams($componentParams);
	}

	/**
	 * @return mixed
	 */
	public function getComponentName()
	{
		return $this->componentName;
	}

	/**
	 * @param mixed $componentName
	 */
	public function setComponentName($componentName)
	{
		$this->componentName = $componentName;
	}

	/**
	 * @return string
	 */
	public function getComponentTemplateName()
	{
		return $this->componentTemplateName;
	}

	/**
	 * @param string $componentTemplateName
	 */
	public function setComponentTemplateName($componentTemplateName)
	{
		$this->componentTemplateName = $componentTemplateName;
	}

	/**
	 * @return array
	 */
	public function getComponentParams()
	{
		return $this->componentParams;
	}

	/**
	 * @param array $componentParams
	 */
	public function setComponentParams($componentParams)
	{
		$this->componentParams = $componentParams;
	}

	/**
	 * @return $this|BoardButton
	 */
	public function process()
	{
		if ($this->isProcessed())
		{
			return $this;
		}

		ob_start();
		$this->flush();
		$componentContent = ob_get_clean();

		//$this->collectAssetsPathList();


		$this->setHtml($componentContent);
		$this->setJsList($this->getComponentJsList());
		$this->setCssList($this->getComponentCssList());
		$this->setStringList($this->getComponentstringList());

		$this->setProcessed(true);
		return $this;
	}


	public function flush()
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			$this->getComponentName(),
			$this->getComponentTemplateName(),
			$this->getComponentParams()
		);
	}

	private function collectAssetsPathList()
	{
		Asset::getInstance()->getJs();
		Asset::getInstance()->getCss();

		$this->jsPathList = Asset::getInstance()->getTargetList('JS');
		$this->cssPathList = Asset::getInstance()->getTargetList('CSS');
	}


	/**
	 * @return array
	 */
	private function getComponentJsList()
	{
		$jsList = array();

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
	private function getComponentCssList()
	{
		$cssList = array();

		foreach($this->cssPathList as $targetAsset)
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
	private function getComponentStringList()
	{
		$stringList = array();
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
		return $stringList;
	}

	/**
	 * @return bool
	 */
	public function isProcessed()
	{
		return $this->processed;
	}

	/**
	 * @param bool $processed
	 */
	public function setProcessed($processed)
	{
		$this->processed = $processed;
	}

}

