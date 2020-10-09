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
	const STATUS_DENIED = 'denied';
	const STATUS_ERROR = 'error';

	private $jsPathList = [];
	private $cssPathList = [];
	private $asset;

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
	public function __construct($componentName, $templateName = '', $params = [], $additionalResponseParams = [], $parentComponent = [], $functionParams = [], $status = self::STATUS_SUCCESS, ErrorCollection $errorCollection = null)
	{
		$this->asset = Asset::getInstance();

		// Temporary fix
		$this->asset->disableOptimizeCss();
		$this->asset->disableOptimizeJs();

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
		$this->setData([
			'status' => $this->status,
			'data' => $componentContent,
			'assets' => [
				'js' => $this->getJsList(),
				'css' => $this->getCssList(),
				'string' => $this->getStringList()
			],
			'additionalParams' => $additionalResponseParams,
			'errors' => $this->getErrorsToResponse(),
		]);
	}

	private function collectAssetsPathList()
	{
		$this->asset->getCss();
		$this->asset->getJs();
		$this->asset->getStrings();

		$this->jsPathList = $this->asset->getTargetList('JS');
		$this->cssPathList = $this->asset->getTargetList('CSS');
	}

	/**
	 * @return array
	 */
	private function getJsList()
	{
		$jsList = [];

		foreach($this->jsPathList as $targetAsset)
		{
			$assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
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
		$cssList = [];

		foreach($this->cssPathList as $targetAsset)
		{
			$assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
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
		$strings = [];
		foreach($this->cssPathList as $targetAsset)
		{
			$assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['STRINGS']))
			{
				$strings = array_merge($strings, $assetInfo['STRINGS']);
			}
		}

		foreach($this->jsPathList as $targetAsset)
		{
			$assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
			if (!empty($assetInfo['STRINGS']))
			{
				$strings = array_merge($strings, $assetInfo['STRINGS']);
			}
		}

		$strings[] = $this->asset->showFilesList();
		return $strings;
	}

	/**
	 * @return array
	 */
	protected function getErrorsToResponse()
	{
		$errors = [];
		foreach ($this->errorCollection as $error)
		{
			/** @var Error $error */
			$errors[] = [
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			];
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