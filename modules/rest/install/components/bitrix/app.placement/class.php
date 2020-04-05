<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 */

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class CAPConnectComponent extends \CBitrixComponent
{
	protected $placementId = null;
	protected $appList = array();

	protected $currentApp = null;
	protected $currentPlacementOptions = null;

	protected $errors;

	protected $ajaxMode = false;
	protected $templatePage = '';

	public function __construct($component = null)
	{
		$this->errors = new ErrorCollection();

		parent::__construct($component);
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['PLACEMENT'] = trim($arParams['PLACEMENT']);

		if(!isset($arParams['PLACEMENT_OPTIONS']) || !is_array($arParams['PLACEMENT_OPTIONS']))
		{
			$arParams['PLACEMENT_OPTIONS'] = array();
		}

		if(isset($arParams['INTERFACE_EVENT']))
		{
			$arParams['INTERFACE_EVENT'] = trim($arParams['INTERFACE_EVENT']);
		}

		if(!isset($arParams['SAVE_LAST_APP']))
		{
			$arParams['SAVE_LAST_APP'] = 'Y';
		}
		else
		{
			$arParams['SAVE_LAST_APP'] = $arParams['SAVE_LAST_APP'] == 'N' ? 'N' : 'Y';
		}

		if($arParams['PLACEMENT_APP'] > 0)
		{
			$this->currentApp = intval($arParams['PLACEMENT_APP']);
		}

		$this->placementId = $arParams['PLACEMENT'];

		return parent::onPrepareComponentParams($arParams);
	}

	/**
	 * Check Required Modules
	 *
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if(!Loader::includeModule('rest'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Process incoming request
	 * @return void
	 */
	protected function processRequest()
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		if($request->isPost() && isset($request['placement_action']) && check_bitrix_sessid())
		{
			switch($request['placement_action'])
			{
				case 'load':

					$this->currentApp = intval($request['app']);
					$this->currentPlacementOptions = $request['placement_options'];

					$this->ajaxMode = true;
					$this->templatePage = 'layout';

				break;
			}
		}
	}


	/**
	 * Get main data
	 *
	 * @return void
	 */
	protected function prepareData()
	{
		$this->appList = \Bitrix\Rest\PlacementTable::getHandlersList($this->placementId);

		if($this->arParams['SAVE_LAST_APP'] == 'Y')
		{
			$userOption = \CUserOptions::GetOption('rest', 'placement_last', array());

			if($this->currentApp === null)
			{
				if(is_array($userOption) && array_key_exists($this->placementId, $userOption))
				{
					$this->currentApp = $userOption[$this->placementId];
				}
			}
			else
			{
				if(!is_array($userOption))
				{
					$userOption = array();
				}

				if(!array_key_exists($this->placementId, $userOption))
				{
					$userOption[$this->placementId] = array();
				}

				$userOption[$this->placementId] = $this->currentApp;
			}

			$saveOption = false;

			foreach($this->appList as $app)
			{
				if($app['ID'] == $this->currentApp)
				{
					$saveOption = true;
					break;
				}
			}

			if($saveOption)
			{
				\CUserOptions::SetOption('rest', 'placement_last', $userOption);
			}
		}
	}

	/**
	 * Prepare data to render
	 *
	 * @return void
	 */
	protected function formatResult()
	{
		global $APPLICATION;

		$this->arResult['PLACEMENT'] = $this->placementId;
		$this->arResult['APPLICATION_LIST'] = $this->appList;
		$this->arResult['APPLICATION_CURRENT'] = intval($this->currentApp);

		if($this->arResult['APPLICATION_CURRENT'] <= 0)
		{
			$this->arResult['APPLICATION_CURRENT'] = $this->appList[0]['ID'];
		}

		$this->arResult['PLACEMENT_OPTIONS'] = array();
		if(!empty($this->currentPlacementOptions) && is_array($this->currentPlacementOptions))
		{
			$this->arResult['PLACEMENT_OPTIONS'] =  $this->currentPlacementOptions;
		}

		if(is_array($this->arParams['PLACEMENT_OPTIONS']))
		{
			$this->arResult['PLACEMENT_OPTIONS'] = array_merge(
				$this->arResult['PLACEMENT_OPTIONS'],
				$this->arParams['PLACEMENT_OPTIONS']
			);
		}

		$this->arResult['AJAX_URL'] = $APPLICATION->GetCurPageParam('', \Bitrix\Main\HttpRequest::getSystemParameters());
	}

	/**
	 * Extract data from cache
	 *
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		return false;
	}

	protected function putDataToCache()
	{
	}

	protected function abortDataCache()
	{
	}


	public function executeComponent()
	{
		global $APPLICATION;

		if(!$this->checkModules())
		{
			return;
		}

		try
		{
			$this->processRequest();

			if($this->ajaxMode)
			{
				$APPLICATION->RestartBuffer();
			}

			if(!$this->extractDataFromCache())
			{
				$this->prepareData();

				\CJSCore::Init(array('applayout', 'appplacement', 'marketplace'));

				$this->formatResult();
				$this->setResultCacheKeys(array());

				$this->includeComponentTemplate($this->templatePage);
				$this->putDataToCache();
			}
		}
		catch(SystemException $e)
		{
			$this->abortDataCache();

			if($this->ajaxMode)
			{
				$APPLICATION->RestartBuffer();
			}

			ShowError($e->getMessage());
		}

		if($this->ajaxMode)
		{
			\CMain::FinalActions();
			die();
		}
	}
}