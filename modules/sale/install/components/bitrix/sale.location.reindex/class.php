<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2014 Bitrix
 */

//use Bitrix\Main\Config;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\TypeHelper;

use Bitrix\Sale\Location\Search;

Loc::loadMessages(__FILE__);

class CBitrixSaleLocationReindexComponent extends CBitrixComponent
{
	protected $componentData = 	array();
	protected $dbResult = 		array();
	protected $errors = 		array('FATAL' => array(), 'NONFATAL' => array());

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $arParams List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($arParams)
	{
		if (!is_array($arParams))
		{
			$arParams = [];
		}

		$arParams['INITIAL_TIME'] = (int)($arParams['INITIAL_TIME'] ?? 0);

		return $arParams;
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Exception
	 * @return bool
	 */
	protected function checkRequiredModules(): bool
	{
		$result = true;

		if(!Loader::includeModule('sale'))
		{
			$this->errors['FATAL'][] = Loc::getMessage("SALE_SLRI_SALE_MODULE_NOT_INSTALL");
			$result = false;
		}

		return $result;
	}

	protected static function checkAccessPermissions($parameters = array())
	{
		if (!is_array($parameters))
		{
			$parameters = [];
		}
		$parameters['CHECK_CSRF'] ??= false;

		$errors = [];

		if ($GLOBALS['APPLICATION']->GetGroupRight("sale") < "W")
		{
			$errors[] = Loc::getMessage("SALE_SLI_SALE_MODULE_WRITE_ACCESS_DENIED");
		}

		if (!LocationHelper::checkLocationEnabled())
		{
			$errors[] = 'Locations were disabled or data has not been converted';
		}

		if ($parameters['CHECK_CSRF'])
		{
			$csrf = (string)\Bitrix\Main\Context::getCurrent()->getRequest()->getPost('csrf');
			if ($csrf === '' || bitrix_sessid() !== $csrf)
			{
				$errors[] = 'CSRF token is not valid';
			}
		}

		return $errors;
	}

	/**
	 * Function checks if user have basic permissions to launch the component
	 * @throws Exception
	 * @return bool
	 */
	protected function checkPermissions(): bool
	{
		$errors = static::checkAccessPermissions();
		if(is_array($errors))
		{
			$this->errors['FATAL'] = array_merge($this->errors['FATAL'], $errors);
		}

		return empty($errors);
	}

	/**
	 * Additional parameters check, if needed.
	 * @return bool
	 */
	protected function checkParameters(): bool
	{
		return true;
	}

	/**
	 * Function makes some actions based on what is in $this->request
	 * @return void
	 */
	protected function performAction()
	{
		/*
		$this->dbResult['REQUEST'] = 	$this->getRequest();
		$requestMethod = 				$this->getRequestMethod();

		if($requestMethod == 'POST')
		{
		}
		*/
	}

	/**
	 * Here we get some data that cannot be cached for a long time
	 * @return boolean
	 */
	protected function obtainNonCachedData()
	{
		// types
		$types = LocationHelper::getTypeList();
		$selectedTypes = array_flip(Search\Finder::getIndexedTypes());

		$this->dbResult['TYPES'] = array();
		foreach($types as $id => $name)
		{
			$this->dbResult['TYPES'][$id] = array(
				'NAME' => $name,
				'SELECTED' => isset($selectedTypes[$id])
			);
		}

		// langs
		$langs = TypeHelper::getLanguageList();
		$selectedLangs = array_flip(Search\Finder::getIndexedLanguages());

		$this->dbResult['LANGS'] = array();
		foreach($langs as $id => $name)
		{
			$this->dbResult['LANGS'][$id] = array(
				'NAME' => $name,
				'SELECTED' => isset($selectedLangs[$id])
			);
		}

		return true;
	}

	/**
	 * Move data read from database to a specially formatted $arResult
	 * @return void
	 */
	protected function formatResult()
	{
		$this->arResult =& $this->dbResult;
		$this->arResult['ERRORS'] =& $this->errors;

		$this->arResult['URLS'] = array(
			//'URL' => 				$this->arParams['PATH_TO_REINDEX'],
			'AJAX_URL' => 			$this->getPath().'/get.php'
		);

		unset($this->componentData);
	}

	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		if($this->checkRequiredModules() && $this->checkPermissions() && $this->checkParameters())
		{
			//$this->prepareInstances();
			$this->performAction();
			$this->obtainData();
		}

		$this->formatResult();

		$this->includeComponentTemplate();
	}

	/**
	 * Do smth when called over ajax
	 * @return mixed[]
	 */
	public static function doAjaxStuff($parameters = array())
	{
		$errors = static::checkAccessPermissions(['CHECK_CSRF' => true]);
		$data = 	array();

		if(count($errors) == 0)
		{
			$request = static::getRequest();

			// action: process ajax
			if(isset($request['POST']['AJAX_CALL']))
			{
				if($request['POST']['ACT'] == 'REINDEX')
					$process = new Search\ReindexProcess($request['POST']['ACT_DATA']);

				if($request['POST']['step'] == 0)
				{
					if(is_array($request['POST']['ACT_DATA']['TYPES']))
					{
						$all = false;
						foreach($request['POST']['ACT_DATA']['TYPES'] as $k => $type)
						{
							if($type == '')
							{
								$all = true;
								break;
							}

							$request['POST']['ACT_DATA']['TYPES'][$k] = intval($type);
						}

						$optValue = array();
						if(!$all)
						{
							$optValue = array_unique($request['POST']['ACT_DATA']['TYPES']);
						}

						Search\Finder::setIndexedTypes($optValue);
					}

					if(is_array($request['POST']['ACT_DATA']['LANG']))
					{
						$langs = TypeHelper::getLanguageList();

						$all = false;
						foreach($request['POST']['ACT_DATA']['LANG'] as $k => $lang)
						{
							if($lang == '')
							{
								$all = true;
								break;
							}

							if(!isset($langs[$lang]))
								unset($request['POST']['ACT_DATA']['LANG'][$k]);
						}

						$optValue = array();
						if(!$all)
						{
							$optValue = array_unique($request['POST']['ACT_DATA']['LANG']);
						}

						Search\Finder::setIndexedLanguages($optValue);
					}

					$process->reset();
				}

				try
				{
					@set_time_limit(0);

					$data['PERCENT'] = $process->performStage();
					$data['NEXT_STAGE'] = $process->getStageCode();
				}
				catch(Main\SystemException $e)
				{
					$errors[] = $e->getMessage();
				}

				if($data['PERCENT'] == 100)
				{
					//$GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');
				}
			}
		}

		return array(
			'ERRORS' => $errors,
			'DATA' => $data
		);
	}

	protected function checkHasErrors($fatalOnly = false)
	{
		return count($this->errors['FATAL']) || (!$fatalOnly && count($this->errors['NONFATAL']));
	}

	/**
	 * Fetches all required data from database. Everyting that connected with data fetch lies here.
	 * @return void
	 */
	protected function obtainData()
	{
		$this->obtainNonCachedData();
	}

	protected static function getRequest(): array
	{
		$request = Main\Context::getCurrent()->getRequest();

		return [
			'GET' => $request->getQueryList()->toArray(),
			'POST' => $request->getPostList()->toArray(),
		];
	}

	protected static function getRequestMethod(): string
	{
		return (string)Main\Context::getCurrent()->getServer()->getRequestMethod();
	}

	/**
	 * @deprecated
	 *
	 * Function reduces input value to integer type, and, if it gets null, passes the default value
	 * @param mixed $fld Field value
	 * @param int $default Default value
	 * @param int $allowZero Allows zero-value of the parameter
	 * @return int Parsed value
	 */
	public static function tryParseInt(&$fld, $default = false, $allowZero = false)
	{
		$fld = intval($fld);
		if(!$allowZero && !$fld && $default !== false)
			$fld = $default;

		return $fld;
	}
}