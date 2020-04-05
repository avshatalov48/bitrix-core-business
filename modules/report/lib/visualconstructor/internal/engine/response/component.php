<?php
namespace Bitrix\Report\VisualConstructor\Internal\Engine\Response;

use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetMode;
use Bitrix\Main\Web\HttpHeaders;

/**
 * Response type for rendering ajax components from action
 * @package Bitrix\Report\VisualConstructor\Internal\Engine\Response
 */
final class Component extends Json implements Errorable
{
	const STATUS_SUCCESS = 'success';
	const STATUS_DENIED  = 'denied';
	const STATUS_ERROR   = 'error';

	private $jsPathList = array();
	private $cssPathList = array();
	private $stringPathList = array();

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var ErrorCollection
	 */
	private $errorCollection;


	/**
	 * Component constructor.
	 * @param string $componentName
	 * @param string $templateName
	 * @param array $params
	 * @param array $parentComponent
	 * @param array $functionParams
	 * @param array $additionalResponseParams
	 * @param string $status
	 * @param ErrorCollection|null $errorCollection
	 */
	public function __construct($componentName, $templateName = '', $params = array(), $additionalResponseParams = array(), $parentComponent = array(), $functionParams = array(), $status = self::STATUS_SUCCESS, ErrorCollection $errorCollection = null)
	{
		$this->setHeaders(new HttpHeaders());
		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			$componentName,
			$templateName,
			$params,
			$parentComponent,
			$functionParams
		);
		$componentContent = ob_get_clean();

		$this->status = $status?: self::STATUS_SUCCESS;
		$this->errorCollection = $errorCollection?: new ErrorCollection;

		$this->collectAssetsPathList();



		$this->setData(array(
			'status' => $this->status,
			'data' => $componentContent,
			'assets' => array(
				'js' => $this->getJsList(),
				'css' => $this->getCssList(),
				'string' => $this->getStringList()
			),
			'additionalParams' => $additionalResponseParams,
			'errors' => $this->getErrorsToResponse(),
		));
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
	private function getJsList()
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
	private function getCssList()
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
	private function getStringList()
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
	 * @return array
	 */
	protected function getErrorsToResponse()
	{
		$errors = array();
		foreach ($this->errorCollection as $error)
		{
			/** @var Error $error */
			$errors[] = array(
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			);
		}

		return $errors;
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}