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
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\TypeHelper;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;

use Bitrix\Sale\Location\Import;
use Bitrix\Sale\Location\Search\Finder;

Loc::loadMessages(__FILE__);

class CBitrixSaleLocationImportComponent extends CBitrixComponent
{
	protected $componentData = 	array();
	protected $dbResult = 		array();
	protected $errors = 		array('FATAL' => array(), 'NONFATAL' => array());

	protected $import = 		null;

	const LOC2_IMPORT_PERFORMED_OPTION = 'sale_locationpro_import_performed';

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $arParams List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams['INITIAL_TIME'] = (int)($arParams['INITIAL_TIME'] ?? 0);

		if (
			isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y" &&
			isset($_REQUEST["IFRAME_TYPE"]) && $_REQUEST["IFRAME_TYPE"] === "SIDE_SLIDER" &&
			isset($_REQUEST["publicSidePanel"]) && $_REQUEST["publicSidePanel"] === "Y"
		)
		{
			$arParams['PATH_TO_IMPORT'] = \CHTTP::urlAddParams($arParams['PATH_TO_IMPORT'], array(
				"IFRAME" => "Y", "IFRAME_TYPE" => "Y", "publicSidePanel" => "Y"
			));
		}



		return $arParams;
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Exception
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		$result = true;

		if(!Loader::includeModule('sale'))
		{
			$this->errors['FATAL'][] = Loc::getMessage("SALE_SLI_SALE_MODULE_NOT_INSTALL");
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

		$errors = array();

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
	 * @return void
	 */
	protected function checkPermissions()
	{
		$errors = static::checkAccessPermissions();
		if(is_array($errors))
		{
			$this->errors['FATAL'] = array_merge($this->errors['FATAL'], $errors);
		}

		return count($errors) == 0;
	}

	/**
	 * Additional parameters check, if needed.
	 * @return void
	 */
	protected function checkParameters()
	{
		return true;
	}

	protected function prepareInstances()
	{
		$this->import = $this->getImportInstance(array(
			'INITIAL_TIME' => $this->arParams['INITIAL_TIME']
		));
	}

	/**
	 * Function makes some actions based on what is in $this->request
	 * @return void
	 */
	protected function performAction()
	{
		$this->dbResult['REQUEST'] = 	$this->getRequest();
		$requestMethod = 				$this->getRequestMethod();

		$this->dbResult['DISPLAY_FILE_UPLOAD_RESPONCE'] = false;

		if($requestMethod == 'POST')
		{
			// action: file process
			if(isset($this->dbResult['REQUEST']['POST']['FILE_UPLOAD']))
			{
				try
				{
					$this->import->saveUserFile('IMPORT_FILE');
				}
				catch(Exception $e)
				{
					$this->errors['FATAL'][] =  $e->getMessage();
				}

				$this->dbResult['DISPLAY_FILE_UPLOAD_RESPONCE'] = true;
				$this->dbResult['FILE_UPLOAD_ID'] = preg_replace('#^[^a-zA-Z0-9-_]+$#', '', $this->dbResult['REQUEST']['POST']['FILE_UPLOAD']);
			}

			// action: drop all locations by pressing button
			if(isset($this->dbResult['REQUEST']['POST']['DROP_ALL']))
			{
				$this->import->deleteAll();
				LocalRedirect($this->arParams['PATH_TO_IMPORT']); // preserve from accidental pressing F5 in browser
			}
		}
	}

	/**
	 * Here we get some data that cannot be cached for a long time
	 * @return boolean
	 */
	protected function obtainNonCachedData()
	{
		$this->import->turnOffCache();

		$this->dbResult['LAYOUT'] = 		$this->import->getRemoteLayout();
		$this->resortLayoutBundleAlphabetically('');

		$this->dbResult['TYPE_LEVELS'] = 	$this->import->getTypeLevels();
		$this->dbResult['STATISTICS'] = 	$this->import->getStatisticsAll();

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
			'IMPORT' => 				$this->arParams['PATH_TO_IMPORT'],
			'IMPORT_AJAX' => 			$this->getPath().'/get.php',
			'TYPE_LIST' => 				TypeHelper::getListUrl(),
			'EXTERNAL_SERVICE_LIST' => 	ExternalServiceHelper::getListUrl()
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
			$this->prepareInstances();
			$this->performAction();
			$this->obtainData();
		}

		$this->arResult['ALLOW_SOURCE_REMOTE'] = self::checkRegion();
		$this->formatResult();

		$this->includeComponentTemplate();
	}

	public static function checkRegion(): bool
	{
		$region = Application::getInstance()->getLicense()->getRegion();
		$isBitrixSiteManagementOnly = !Loader::includeModule('bitrix24') && !Loader::includeModule('intranet');

		return $region === 'ru' || $region === 'by' || $region === 'kz' || $isBitrixSiteManagementOnly;
	}

	public static function doAjaxStuff($parameters = array())
	{
		$errors = static::checkAccessPermissions(array('CHECK_CSRF' => true));
		$data = 	array();

		$options = Application::getInstance()->getContext()->getRequest()->get('OPTIONS') ?? [];
		$source = $options['SOURCE'] ?? null;
		if ($source === Import\ImportProcess::SOURCE_REMOTE && !self::checkRegion())
		{
			$errors[] = new Error('Region is not allowed');
		}

		if(count($errors) == 0)
		{
			$import = 	static::getImportInstance($parameters);

			$request =	static::getRequest();

			// action: restore indexes
			if(isset($request['POST']['RESTORE_INDEXES']))
			{
				$import->restoreIndexes();
				$import->unLockProcess();
			}

			// action: process ajax
			if(isset($request['POST']['AJAX_CALL']))
			{
				$data = array();

				if($request['POST']['step'] == 0)
					$import->reset();

				try
				{
					@set_time_limit(0);

					$data['PERCENT'] = $import->performStage();
					$data['NEXT_STAGE'] = $import->getStageCode();

					if($data['PERCENT'] == 100)
					{
						$import->logFinalResult();
						$data['STAT'] = array_values($import->getStatisticsAll()); // to force to [] in json

						Finder::setIndexInvalid(); // drop search index
						LocationHelper::deleteInformer('SALE_LOCATIONPRO_DATABASE_FAILURE'); // delete database failure messages, if any

						$GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');

						if (
							(int)($request['POST']['OPTIONS']['DROP_ALL'] ?? null) === 1
							|| (int)($request['POST']['ONLY_DELETE_ALL'] ?? null) === 1
						)
						{
							Main\Config\Option::set('sale', self::LOC2_IMPORT_PERFORMED_OPTION, 'Y');
						}
					}
				}
				catch(Main\SystemException $e)
				{
					$errors[] = $e->getMessage();
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

		$this->dbResult['FIRST_IMPORT'] = Main\Config\Option::get('sale', self::LOC2_IMPORT_PERFORMED_OPTION, '') != 'Y';
	}

	protected static function getImportInstance($parameters)
	{
		$request = static::getRequest();

		// you must pass all parameters here, Import\ImportProcess should not know about $_REQUEST
		return new Import\ImportProcess(array(

			// system parameters
			'INITIAL_TIME' => intval($parameters['INITIAL_TIME']),
			'ONLY_DELETE_ALL' => !!($request['POST']['ONLY_DELETE_ALL']),
			'USE_LOCK' => true,

			// parameters from the form
			'REQUEST' => $request['POST']->toArray(),
			'LANGUAGE_ID' => isset($request['GET']['lang']) && (string) $request['GET']['lang'] != '' ? $request['GET']['lang'] : LANGUAGE_ID
		));
	}

	protected static function getRequest()
	{
		$request = Main\Context::getCurrent()->getRequest();

		return [
			'GET' => $request->getQueryList(),
			'POST' => $request->getPostList(),
		];
	}

	protected static function getRequestMethod()
	{
		return Main\Context::getCurrent()->getServer()->getRequestMethod();
	}

	// for building tree at import page
	public static function renderLayout($parameters)
	{
		$html = '';
		$alreadyPrinted = array();

		foreach($parameters['LAYOUT'][''] as $item)
		{
			$html .= self::renderLayoutNode($item['CODE'], $item['NAME'], $parameters, $alreadyPrinted);
		}

		return $html;
	}

	private function resortLayoutBundleAlphabetically($code)
	{
		if (!isset($this->dbResult['LAYOUT'][$code]))
		{
			return;
		}

		$sortedChildren = array();
		foreach($this->dbResult['LAYOUT'][$code] as $item)
		{
			$name = $item['NAME'][mb_strtoupper(LANGUAGE_ID)]['NAME'];
			$sortedChildren[$name] = $item;

			$this->resortLayoutBundleAlphabetically($item['CODE']);
		}

		ksort($sortedChildren, SORT_STRING);

		$this->dbResult['LAYOUT'][$code] = array_values($sortedChildren);
	}

	private static function renderLayoutNode($pCode, $pName, $parameters, &$alreadyPrinted)
	{
		// cycle prevention

		if(isset($alreadyPrinted[$pCode]))
			return '';

		$alreadyPrinted[$pCode] = true;

		$childrenHtml = '';

		if (!empty($parameters['LAYOUT'][$pCode]) && is_array($parameters['LAYOUT'][$pCode]))
		{
			foreach ($parameters['LAYOUT'][$pCode] as $item)
			{
				$childrenHtml .= self::renderLayoutNode(
					$item['CODE'],
					$item['NAME'],
					$parameters,
					$alreadyPrinted
				);
			}
		}

		return str_replace(
			[
				'{{CODE}}',
				'{{NAME}}',
				'{{CHILDREN}}',
				'{{INPUT_NAME}}',
				'{{EXPANDER_CLASS}}',
			],
			[
				$pCode === 'WORLD' ? '' : $pCode, // a little mixin with view, actually temporal
				(string) $pName[mb_strtoupper(LANGUAGE_ID)]['NAME'] != '' ? $pName[mb_strtoupper(LANGUAGE_ID)]['NAME'] : $pName['EN']['NAME'],
				$childrenHtml,
				$parameters['INPUT_NAME'], //!strlen($childrenHtml) ? $parameters['INPUT_NAME'] : '',
				$childrenHtml !== '' ? $parameters['EXPANDER_CLASS'] : '',
			],
			$parameters['TEMPLATE']
		);
	}

	/**
	 * @deprecated
	 *
	 * Function reduces input value to integer type, and, if gets null, passes the default value
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