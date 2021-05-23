<?
use Bitrix\Main\localization\Loc,
	Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

class CCatalogStepOperations
{
	const DEFAULT_SESSION_PREFIX = 'CC';

	protected $sessID = '';
	protected $errorCounter = 0;
	protected $errors = array();
	protected $stepErrors = array();
	protected $maxExecutionTime = 0;
	protected $maxOperationCounter = 0;
	protected $startOperationTime = 0;
	protected $lastID = 0;
	protected $allCounter = 0;
	protected $allOperationCounter = 0;
	protected $finishOperation = false;
	protected $defaultProgressTemplate = '#PROGRESS_BAR#';
	protected $progressTemplate = '#PROGRESS_BAR#';
	protected $errorTemplate = '';
	protected $params = null;

	public function __construct($sessID, $maxExecutionTime, $maxOperationCounter)
	{
		$sessID = (string)$sessID;
		if ($sessID == '')
			$sessID = self::DEFAULT_SESSION_PREFIX.time();
		$this->sessID = $sessID;
		$this->errorCounter = 0;
		$this->errors = array();
		$this->stepErrors = array();
		$maxExecutionTime = (int)$maxExecutionTime;
		if ($maxExecutionTime < 0)
			$maxExecutionTime = $this->getDefaultExecutionTime();
		$this->maxExecutionTime = $maxExecutionTime;
		$maxOperationCounter = (int)$maxOperationCounter;
		if ($maxOperationCounter < 0)
			$maxOperationCounter = 10;
		$this->maxOperationCounter = $maxOperationCounter;
		$this->startOperationTime = time();
		$this->finishOperation = false;
		$this->progressTemplate = Loc::getMessage('BX_STEP_OPERATION_PROGRESS_TEMPLATE').$this->defaultProgressTemplate;
	}

	public function __destruct()
	{
		if ($this->sessID != '' && isset($_SESSION[$this->sessID]))
			unset($_SESSION[$this->sessID]);
	}

	public function setParams($params)
	{
		if (!empty($params) && is_array($params))
			$this->params = $params;
	}

	public function initStep($allCount, $allOperationCount, $lastID)
	{
		if (isset($_SESSION[$this->sessID]) && is_array($_SESSION[$this->sessID]))
		{
			if (isset($_SESSION[$this->sessID]['ERRORS_COUNTER']) && (int)$_SESSION[$this->sessID]['ERRORS_COUNTER'] > 0)
				$this->errorCounter = (int)$_SESSION[$this->sessID]['ERRORS_COUNT'];
		}
		$this->stepErrors = array();
		$lastID = (int)$lastID;
		if ($lastID < 0)
			$lastID = 0;
		$this->lastID = $lastID;
		$allCount = (int)$allCount;
		if ($allCount < 0)
			$allCount = 0;
		$this->allCounter = $allCount;
		$allOperationCount = (int)$allOperationCount;
		if ($allOperationCount < 0)
			$allOperationCount = 0;
		$this->allOperationCounter = $allOperationCount;
	}

	public function saveStep()
	{
		if (!isset($_SESSION[$this->sessID]) || !is_array($_SESSION[$this->sessID]))
			$_SESSION[$this->sessID] = array();
		if ($this->errorCounter > 0)
		{
			if (!empty($this->stepErrors))
				$this->errors = $this->stepErrors;
			$_SESSION[$this->sessID]['ERRORS_COUNTER'] = $this->errorCounter;
		}

		$this->calculateNextOperationCounter();

		return array(
			'sessID' => $this->sessID,
			'maxExecutionTime' => $this->maxExecutionTime,
			'maxOperationCounter' => $this->maxOperationCounter,
			'lastID' => $this->lastID,
			'allCounter' => $this->allCounter,
			'counter' => $this->allCounter,
			'allOperationCounter' => $this->allOperationCounter,
			'operationCounter' => $this->allOperationCounter,
			'errorCounter' => $this->errorCounter,
			'errors' => (!empty($this->stepErrors) ? '<p>'.implode('</p><p>', $this->stepErrors).'</p>' : ''),
			'finishOperation' => $this->finishOperation,
			'message' => $this->getMessage()
		);
	}

	public function startOperation()
	{

	}

	public function finalOperation()
	{

	}

	public function runOperation()
	{

	}

	public function run()
	{
		$this->startOperation();
		$this->runOperation();
		$this->finalOperation();
	}

	public function setProgressTemplates($template)
	{
		$template = (string)$template;
		if ($template !== '')
			$this->progressTemplate = $template.$this->defaultProgressTemplate;
	}

	public function getMessage()
	{
		$messageParams = array(
			'MESSAGE' => '',
			'PROGRESS_TOTAL' => $this->allCounter,
			'PROGRESS_VALUE' => $this->allOperationCounter,
			'TYPE' => 'PROGRESS',
			'DETAILS' => str_replace(array('#ALL#', '#COUNT#'), array($this->allCounter, $this->allOperationCounter), $this->progressTemplate),
			'HTML' => true
		);
		$message = new CAdminMessage($messageParams);
		return $message->Show();
	}

	public static function getAllCounter()
	{
		return 0;
	}

	public static function getDefaultExecutionTime()
	{
		$executionTime = (int)ini_get('max_execution_time');
		if ($executionTime <= 0)
			$executionTime = 60;
		return (int)(2*$executionTime/3);
	}

	protected function setLastId($lastId)
	{
		$this->allOperationCounter++;
		$this->lastID = $lastId;
	}

	protected function isStopOperation()
	{
		return ($this->maxExecutionTime > 0 && (time() - $this->startOperationTime > $this->maxExecutionTime));
	}

	protected function setFinishOperation($finish)
	{
		$this->finishOperation = ($finish === true);
	}

	protected function calculateNextOperationCounter()
	{
		if (!$this->finishOperation)
		{
			$period = time() - $this->startOperationTime;
			if ($this->maxExecutionTime > 2*$period)
				$this->maxOperationCounter = $this->maxOperationCounter*2;
			elseif ($period >= $this->maxExecutionTime)
				$this->maxOperationCounter = (int)(($this->maxOperationCounter*2)/3);
			unset($period);
			if ($this->maxOperationCounter < 10)
				$this->maxOperationCounter = 10;
		}
	}

	protected function addError($error)
	{
		$error = (string)$error;
		if ($error === '')
			return;
		$this->stepErrors[] = $error;
		$this->errorCounter++;
	}
}

class CCatalogProductSetAvailable extends CCatalogStepOperations
{
	const SESSION_PREFIX = 'PSA';

	public function __construct($sessID, $maxExecutionTime, $maxOperationCounter)
	{
		$sessID = (string)$sessID;
		if ($sessID == '')
			$sessID = self::SESSION_PREFIX.time();
		parent::__construct($sessID, $maxExecutionTime, $maxOperationCounter);
	}

	public function runOperation()
	{
		global $DB;

		$tableName = '';
		switch (ToUpper($DB->type))
		{
			case 'MYSQL':
				$tableName = 'b_catalog_product_sets';
				break;
			case 'MSSQL':
				$tableName = 'B_CATALOG_PRODUCT_SETS';
				break;
			case 'ORACLE':
				$tableName = 'B_CATALOG_PRODUCT_SETS';
				break;
		}
		if ($tableName == '')
			return;

		$emptyList = true;
		CTimeZone::Disable();
		$filter = array('TYPE' => CCatalogProductSet::TYPE_SET, 'SET_ID' => 0);
		if ($this->lastID > 0)
			$filter['>ID'] = $this->lastID;
		$topCount = ($this->maxOperationCounter > 0 ? array('nTopCount' => $this->maxOperationCounter) : false);
		$productSetsIterator = CCatalogProductSet::getList(
			array('ID' => 'ASC'),
			$filter,
			false,
			$topCount,
			array('ID', 'OWNER_ID', 'ITEM_ID', 'MODIFIED_BY', 'TIMESTAMP_X')
		);
		while ($productSet = $productSetsIterator->Fetch())
		{
			$emptyList = false;
			$productSet['MODIFIED_BY'] = (int)$productSet['MODIFIED_BY'];
			if ($productSet['MODIFIED_BY'] == 0)
				$productSet['MODIFIED_BY'] = false;
			CCatalogProductSet::recalculateSet($productSet['ID'], $productSet['ITEM_ID']);
			$arTimeFields = array(
				'~TIMESTAMP_X' => $DB->CharToDateFunction($productSet['TIMESTAMP_X'], "FULL"),
				'MODIFIED_BY' => $productSet['MODIFIED_BY']
			);
			$strUpdate = $DB->PrepareUpdate($tableName, $arTimeFields);
			if (!empty($strUpdate))
			{
				$strQuery = "update ".$tableName." set ".$strUpdate." where ID = ".$productSet['ID'];
				$DB->Query($strQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			$this->setLastId($productSet['ID']);
			if ($this->isStopOperation())
				break;
		}
		CTimeZone::Enable();
		$this->setFinishOperation($emptyList);
	}

	public static function getAllCounter()
	{
		return (int)CCatalogProductSet::getList(
			array(),
			array('TYPE' => CCatalogProductSet::TYPE_SET, 'SET_ID' => 0),
			array()
		);
	}
}

class CCatalogProductAvailable extends CCatalogStepOperations
{
	const SESSION_PREFIX = 'PA';

	protected $config = array();

	protected $preloadTooLong = false;

	protected $iblockData = null;
	protected $productList = array();
	protected $currentList = array();
	protected $currentIdsList = array();
	private $offersMap = array();
	private $offersIds = array();
	private $prices = array();
	private $calculatePrices = array();
	private $existPriceIds = array();
	private $existIdsByType = array();
	private $measureRatios = array();
	private $currencyReference = array();
	private $measureIds = [
		'DEFAULT' => null,
		'BASE' => null
	];

	/** @deprecated  */
	protected $useSets = false;
	/** @deprecated  */
	protected $separateSkuMode = false;
	/** @deprecated  */
	protected $extendedMode = false;

	public function __construct($sessID, $maxExecutionTime, $maxOperationCounter)
	{
		$sessID = (string)$sessID;
		if ($sessID == '')
			$sessID = self::SESSION_PREFIX.time();
		parent::__construct($sessID, $maxExecutionTime, $maxOperationCounter);
		$this->preloadTooLong = false;
		$this->initConfig();
		$this->setOldConfig();
		$this->initReferences();
	}

	public function isUseSets()
	{
		return $this->config['CHECK_SETS'];
	}

	public function isSeparateSkuMode()
	{
		return $this->config['SEPARATE_SKU_MODE'];
	}

	public function runOperation()
	{
		if (!isset($this->params['IBLOCK_ID']))
			return;
		$this->params['IBLOCK_ID'] = (int)$this->params['IBLOCK_ID'];
		if ($this->params['IBLOCK_ID'] <= 0)
			return;
		$this->iblockData = CCatalogSku::GetInfoByIBlock($this->params['IBLOCK_ID']);
		if (empty($this->iblockData))
			return;

		$this->currentList = array();
		$this->currentIdsList = array();
		$productIterator = $this->getProductIterator(array(), array());
		while ($product = $productIterator->fetch())
		{
			$product['PRODUCT_ID'] = (int)$product['PRODUCT_ID'];
			$product['PRODUCT_EXISTS'] = $product['PRODUCT_ID'] > 0;
			$this->currentList[$product['ID']] = $product;
			$this->currentIdsList[] = $product['ID'];
		}
		unset($product, $productIterator);

		if (!empty($this->currentList))
		{
			switch ($this->iblockData['CATALOG_TYPE'])
			{
				case CCatalogSku::TYPE_CATALOG:
					$this->loadProductPrices();
					break;
				case CCatalogSku::TYPE_OFFERS:
					$this->loadOffersData();
					$this->loadProductPrices();
					break;
				case CCatalogSku::TYPE_FULL:
				case CCatalogSku::TYPE_PRODUCT:
					$this->loadSkuData();
					$this->loadProductPrices();
					$this->loadSkuPrices();
					break;
			}
			$this->loadProductSets();
			$this->loadMeasureRatios();
			if ($this->checkPreloadTime())
				$this->updateProductData();
		}

		$this->setFinishOperation(empty($this->currentIdsList));
		$this->currentList = array();
		$this->currentIdsList = array();
	}

	public function getMessage()
	{
		if (empty($this->iblockData))
			return parent::getMessage();

		$title = '';

		switch ($this->iblockData['CATALOG_TYPE'])
		{
			case CCatalogSku::TYPE_CATALOG:
				$title = Loc::getMessage(
					'BX_STEP_OPERATION_CATALOG_TITLE',
					array(
						'#ID#' => $this->iblockData['IBLOCK_ID'],
						'#NAME#' => htmlspecialcharsbx(CIBlock::GetArrayByID($this->iblockData['IBLOCK_ID'], 'NAME'))
					)
				);
				break;
			case CCatalogSku::TYPE_OFFERS:
				$title = Loc::getMessage(
					'BX_STEP_OPERATION_OFFERS_TITLE',
					array(
						'#ID#' => $this->iblockData['PRODUCT_IBLOCK_ID'],
						'#NAME#' => htmlspecialcharsbx(CIBlock::GetArrayByID($this->iblockData['PRODUCT_IBLOCK_ID'], 'NAME'))
					)
				);
				break;
			case CCatalogSku::TYPE_PRODUCT:
			case CCatalogSku::TYPE_FULL:
				$title = Loc::getMessage(
					'BX_STEP_OPERATION_CATALOG_TITLE',
					array(
						'#ID#' => $this->iblockData['PRODUCT_IBLOCK_ID'],
						'#NAME#' => htmlspecialcharsbx(CIBlock::GetArrayByID($this->iblockData['PRODUCT_IBLOCK_ID'], 'NAME'))
					)
				);
				break;
		}

		$messageParams = array(
			'MESSAGE' => $title,
			'PROGRESS_TOTAL' => $this->allCounter,
			'PROGRESS_VALUE' => $this->allOperationCounter,
			'TYPE' => 'PROGRESS',
			'DETAILS' => str_replace(array('#ALL#', '#COUNT#'), array($this->allCounter, $this->allOperationCounter), $this->progressTemplate),
			'HTML' => true
		);
		$message = new CAdminMessage($messageParams);
		return $message->Show();
	}

	public static function getIblockList($iblockId)
	{
		$result = array();
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return $result;

		$iblockData = CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($iblockData))
			return $result;

		switch ($iblockData['CATALOG_TYPE'])
		{
			case CCatalogSku::TYPE_CATALOG:
				$iblockName = CIBlock::GetArrayByID($iblockData['IBLOCK_ID'], 'NAME');
				$result[] = array(
					'ID' => $iblockData['IBLOCK_ID'],
					'NAME' => $iblockName,
					'TITLE' => Loc::getMessage(
						'BX_STEP_OPERATION_CATALOG_TITLE',
						array(
							'#ID#' => $iblockData['IBLOCK_ID'],
							'#NAME#' => $iblockName
						)
					),
					'COUNT' => static::getIblockCounter($iblockData['IBLOCK_ID'])
				);
				unset($iblockName);
				break;
			case CCatalogSku::TYPE_OFFERS:
				$result[] = array(
					'ID' => $iblockData['IBLOCK_ID'],
					'NAME' => CIBlock::GetArrayByID($iblockData['IBLOCK_ID'], 'NAME'),
					'TITLE' => Loc::getMessage(
						'BX_STEP_OPERATION_OFFERS_TITLE',
						array(
							'#ID#' => $iblockData['PRODUCT_IBLOCK_ID'],
							'#NAME#' => CIBlock::GetArrayByID($iblockData['PRODUCT_IBLOCK_ID'], 'NAME')
						)
					),
					'COUNT' => static::getIblockCounter($iblockData['IBLOCK_ID'])
				);
				break;
			case CCatalogSku::TYPE_PRODUCT:
			case CCatalogSku::TYPE_FULL:
				$iblockName = CIBlock::GetArrayByID($iblockData['PRODUCT_IBLOCK_ID'], 'NAME');
				$result[] = array(
					'ID' => $iblockData['IBLOCK_ID'],
					'NAME' => CIBlock::GetArrayByID($iblockData['IBLOCK_ID'], 'NAME'),
					'TITLE' => Loc::getMessage(
						'BX_STEP_OPERATION_OFFERS_TITLE',
						array(
							'#ID#' => $iblockData['PRODUCT_IBLOCK_ID'],
							'#NAME#' => $iblockName
						)
					),
					'COUNT' => static::getIblockCounter($iblockData['IBLOCK_ID'])
				);
				$result[] = array(
					'ID' => $iblockData['PRODUCT_IBLOCK_ID'],
					'NAME' => $iblockName,
					'TITLE' => Loc::getMessage(
						'BX_STEP_OPERATION_CATALOG_TITLE',
						array(
							'#ID#' => $iblockData['PRODUCT_IBLOCK_ID'],
							'#NAME#' => $iblockName
						)
					),
					'COUNT' => static::getIblockCounter($iblockData['PRODUCT_IBLOCK_ID'])
				);
				unset($iblockName);
				break;
		}
		unset($iblockData);

		return $result;
	}

	protected function checkPreloadTime()
	{
		$this->preloadTooLong = ($this->maxExecutionTime > 0 && (time() - $this->startOperationTime > ($this->maxExecutionTime/2)));
		return !$this->preloadTooLong;
	}

	protected function calculateNextOperationCounter()
	{
		if (!$this->finishOperation)
		{
			if ($this->preloadTooLong)
			{
				$this->maxOperationCounter = (int)(($this->maxOperationCounter*2)/3);
				return;
			}
			$period = time() - $this->startOperationTime;
			if ($this->maxExecutionTime > 2*$period)
				$this->maxOperationCounter = $this->maxOperationCounter*2;
			elseif ($period >= $this->maxExecutionTime)
				$this->maxOperationCounter = (int)(($this->maxOperationCounter*2)/3);
			unset($period);
			if ($this->maxOperationCounter < 10)
				$this->maxOperationCounter = 10;
			elseif($this->maxOperationCounter > 500)
				$this->maxOperationCounter = 500;
		}
	}

	protected function initConfig()
	{
		$this->config = array(
			'SEPARATE_SKU_MODE' => (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') == 'Y',
			'CHECK_AVAILABLE' => true,
			'CHECK_SKU_PRICES' => true,
			'CHECK_PRICES' => false,
			'CHECK_SETS' => Catalog\Config\Feature::isProductSetsEnabled(),
			'CHECK_MEASURE_RATIO' => false,
			'CHECK_MEASURE' => false,
			'UPDATE_ONLY' => false
		);
	}

	protected function setOldConfig()
	{
		$this->useSets = $this->config['CHECK_SETS'];
		$this->separateSkuMode = $this->config['SEPARATE_SKU_MODE'];
		$this->extendedMode = false;
	}

	protected function initReferences()
	{
		$this->initCurrencyReference();
		$this->initMeasures();
	}

	private function initCurrencyReference()
	{
		$this->currencyReference = [];
		if (!$this->config['CHECK_PRICES'])
			return;
		$iterator = Currency\CurrencyTable::getList([
			'select' => ['CURRENCY', 'CURRENT_BASE_RATE']
		]);
		while ($row = $iterator->fetch())
			$this->currencyReference[$row['CURRENCY']] = (float)$row['CURRENT_BASE_RATE'];
		unset($row, $iterator);
	}

	private function initMeasures()
	{
		$measure = CCatalogMeasure::getDefaultMeasure();
		if (!empty($measure))
		{
			if ($measure['ID'] > 0)
				$this->measureIds['DEFAULT'] = $measure['ID'];
		}
		$iterator = CCatalogMeasure::getList(
			array(),
			array('=CODE' => CCatalogMeasure::DEFAULT_MEASURE_CODE),
			false,
			false,
			array('ID', 'CODE')
		);
		$measure = $iterator->Fetch();
		unset($iterator);
		if (!empty($measure))
		{
			$this->measureIds['BASE'] = $measure['ID'];
		}
		unset($measure);
	}

	/** @deprecated */
	protected function runOperationFullCatalog(){}

	/** @deprecated */
	protected function runOperationProductIblock(){}

	/** @deprected */
	protected function runOperationCatalog(){}

	/** @deprected */
	protected function runOperationOfferIblock(){}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 *
	 * @param array $product
	 * @return void
	 */
	protected function runExtendedOperation(array $product){}

	protected function getProductIterator($filter, $select)
	{
		$select[] = 'ID';
		$select[] = 'IBLOCK_ID';
		$select[] = 'ACTIVE';
		$select[] = 'NAME';
		$select['PRODUCT_ID'] = 'PRODUCT.ID';
		$select['QUANTITY'] = 'PRODUCT.QUANTITY';
		$select['QUANTITY_TRACE'] = 'PRODUCT.QUANTITY_TRACE';
		$select['CAN_BUY_ZERO'] = 'PRODUCT.CAN_BUY_ZERO';
		$select['TYPE'] = 'PRODUCT.TYPE';
		$select['MEASURE'] = 'PRODUCT.MEASURE';

		if ($this->lastID > 0)
			$filter['>ID'] = $this->lastID;
		$filter['=IBLOCK_ID'] = $this->params['IBLOCK_ID'];
		$filter['=WF_PARENT_ELEMENT_ID'] = null;
		if ($this->config['UPDATE_ONLY'])
			$filter['!==PRODUCT.ID'] = null;

		$getListParams = array(
			'select' => $select,
			'filter' => $filter,
			'order' => array('ID' => 'ASC'),
			'runtime' => array(
				'PRODUCT' => new Main\Entity\ReferenceField(
					'PRODUCT',
					'Bitrix\Catalog\Product',
					array('=this.ID' => 'ref.ID'),
					array('join_type' => 'LEFT')
				)
			)
		);
		if ($this->maxOperationCounter > 0)
			$getListParams['limit'] = $this->maxOperationCounter;
		return Iblock\ElementTable::getList($getListParams);
	}

	protected static function getIblockCounter($iblockId)
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return 0;

		return Iblock\ElementTable::getCount(array(
			'=IBLOCK_ID' => $iblockId,
			'=WF_PARENT_ELEMENT_ID' => null
		));
	}

	private function loadSkuData()
	{
		if (empty($this->currentList))
			return;
		$offers = \CCatalogSku::getOffersList(
			$this->currentIdsList,
			$this->params['IBLOCK_ID'],
			array(),
			array('ID', 'ACTIVE', 'AVAILABLE')
		);
		foreach ($this->currentIdsList as $id)
		{
			$this->currentList[$id]['SKU_STATE'] = Catalog\Product\Sku::OFFERS_NOT_EXIST;
			$this->currentList[$id]['SET_EXISTS'] = false;
			$this->currentList[$id]['BUNDLE_EXISTS'] = false;
			if (empty($offers[$id]))
				continue;

			$this->currentList[$id]['SKU_STATE'] = Catalog\Product\Sku::OFFERS_NOT_AVAILABLE;
			$allOffers = array();
			$availableOffers = array();
			foreach ($offers[$id] as $offerId => $row)
			{
				$allOffers[] = $offerId;
				if ($row['ACTIVE'] != 'Y' || $row['AVAILABLE'] != 'Y')
					continue;
				$this->currentList[$id]['SKU_STATE'] = Catalog\Product\Sku::OFFERS_AVAILABLE;
				$availableOffers[] = $offerId;
			}

			$this->calculatePrices[$id] = [];
			if ($this->config['CHECK_SKU_PRICES'] && !$this->config['SEPARATE_SKU_MODE'])
			{
				if ($this->currentList[$id]['SKU_STATE'] == Catalog\Product\Sku::OFFERS_AVAILABLE)
				{
					foreach ($availableOffers as $offerId)
					{
						$this->offersMap[$offerId] = $id;
						$this->offersIds[] = $offerId;
					}
				}
				else
				{
					foreach ($allOffers as $offerId)
					{
						$this->offersMap[$offerId] = $id;
						$this->offersIds[] = $offerId;
					}
				}
			}
		}
		unset($offerId, $availableOffers, $allOffers, $id);
	}

	private function loadProductPrices()
	{
		if (empty($this->currentList))
			return;

		$this->prices = [];
		$this->existPriceIds = [];
		$this->existIdsByType = [];

		if (!$this->config['CHECK_PRICES'] && !$this->config['CHECK_SKU_PRICES'])
			return;

		foreach (array_chunk($this->currentIdsList, 500) as $pageIds)
		{
			$iterator = Catalog\PriceTable::getList([
				'select' => ['ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'PRICE_SCALE'],
				'filter' => ['@PRODUCT_ID' => $pageIds],
				'order' => ['PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC']
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['ID'];
				$row['PRICE'] = (float)$row['PRICE'];
				$row['PRICE_SCALE'] = (float)$row['PRICE_SCALE'];
				$productId = (int)$row['PRODUCT_ID'];
				$priceTypeId = (int)$row['CATALOG_GROUP_ID'];

				if (!isset($this->prices[$productId]))
					$this->prices[$productId] = [];
				$this->prices[$productId][$id] = $row;

				if (!isset($this->existPriceIds[$productId]))
					$this->existPriceIds[$productId] = [];
				$this->existPriceIds[$productId][$id] = $id;

				if (!isset($this->existIdsByType[$productId]))
					$this->existIdsByType[$productId] = [];
				if (!isset($this->existIdsByType[$productId][$priceTypeId]))
					$this->existIdsByType[$productId][$priceTypeId] = [];
				$this->existIdsByType[$productId][$priceTypeId][] = $id;
			}
			unset($priceTypeId, $productId, $id, $row, $iterator);
		}
		unset($pageIds);
	}

	private function loadSkuPrices()
	{
		if (empty($this->currentList))
			return;

/*		$this->existPriceIds = array();
		$this->existIdsByType = array(); */

		if (!$this->config['CHECK_SKU_PRICES'] || $this->config['SEPARATE_SKU_MODE'])
			return;

/*		foreach (array_chunk($this->currentIdsList, 500) as $pageIds)
		{
			$iterator = Catalog\PriceTable::getList(array(
				'select' => array('ID', 'CATALOG_GROUP_ID', 'PRODUCT_ID'),
				'filter' => array('@PRODUCT_ID' => $pageIds),
				'order' => array('ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$priceTypeId = (int)$row['CATALOG_GROUP_ID'];
				$productId = (int)$row['PRODUCT_ID'];
				if (!isset($this->existPriceIds[$productId]))
					$this->existPriceIds[$productId] = array();
				$this->existPriceIds[$productId][$row['ID']] = $row['ID'];
				if (!isset($this->existIdsByType[$productId]))
					$this->existIdsByType[$productId] = array();
				if (!isset($this->existIdsByType[$productId][$priceTypeId]))
					$this->existIdsByType[$productId][$priceTypeId] = array();
				$this->existIdsByType[$productId][$priceTypeId][] = $row['ID'];
			}
			unset($row, $iterator);
		}
		unset($pageIds); */

		if (empty($this->offersIds))
			return;

		sort($this->offersIds);
		foreach (array_chunk($this->offersIds, 500) as $pageOfferIds)
		{
			$filter = Main\Entity\Query::filter();
			$filter->whereIn('PRODUCT_ID', $pageOfferIds);
			$filter->where(Main\Entity\Query::filter()->logic('or')->where('QUANTITY_FROM', '<=', 1)->whereNull('QUANTITY_FROM'));
			$filter->where(Main\Entity\Query::filter()->logic('or')->where('QUANTITY_TO', '>=', 1)->whereNull('QUANTITY_TO'));

			$iterator = Catalog\PriceTable::getList(array(
				'select' => array(
					'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY',
					'PRICE_SCALE', 'TMP_ID'
				),
				'filter' => $filter,
				'order' => array('PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				$typeId = (int)$row['CATALOG_GROUP_ID'];
				$offerId = (int)$row['PRODUCT_ID'];
				$productId = $this->offersMap[$offerId];
				unset($row['PRODUCT_ID']);

				if (!isset($this->calculatePrices[$productId][$typeId]))
					$this->calculatePrices[$productId][$typeId] = $row;
				elseif ($this->calculatePrices[$productId][$typeId]['PRICE_SCALE'] > $row['PRICE_SCALE'])
					$this->calculatePrices[$productId][$typeId] = $row;
			}
			unset($row, $iterator);
			unset($filter);
		}
	}

	private function loadOffersData()
	{
		if (empty($this->currentList))
			return;

		$productList = \CCatalogSku::getProductList(
			$this->currentIdsList,
			$this->params['IBLOCK_ID']
		);
		if (!is_array($productList))
			$productList = array();

		foreach ($this->currentIdsList as $id)
			$this->currentList[$id]['PARENT_EXISTS'] = isset($productList[$id]);
		unset($id, $productList);
	}

	private function loadProductSets()
	{
		if (!$this->config['CHECK_SETS'])
			return;
		if (empty($this->currentIdsList))
			return;

		foreach ($this->currentIdsList as $id)
		{
			$this->currentList[$id]['SET_EXISTS'] = false;
			$this->currentList[$id]['BUNDLE_EXISTS'] = false;
		}
		unset($id);

		//TODO: replace sql to api
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$tableName = $helper->quote('b_catalog_product_sets');
		$iterator = $conn->query(
			'select '.$helper->quote('OWNER_ID').', '.$helper->quote('TYPE').' from '.$tableName.
			' where '.$helper->quote('OWNER_ID').' in ('.implode(',', $this->currentIdsList).') and '.
			$helper->quote('OWNER_ID').' = '.$helper->quote('ITEM_ID')
		);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['OWNER_ID'];
			if ($row['TYPE'] == \CCatalogProductSet::TYPE_SET)
				$this->currentList[$id]['SET_EXISTS'] = true;
			if ($row['TYPE'] == \CCatalogProductSet::TYPE_GROUP)
				$this->currentList[$id]['BUNDLE_EXISTS'] = true;
		}
		unset($row, $iterator);
		unset($tableName, $helper, $conn);
	}

	private function loadMeasureRatios()
	{
		$this->measureRatios = array();
		if (!$this->config['CHECK_MEASURE_RATIO'])
			return;
		if (empty($this->currentIdsList))
			return;

		$this->measureRatios = array_fill_keys(
			$this->currentIdsList,
			array(
				'RATIOS' => array(),
				'DEFAULT_EXISTS' => false,
				'DEFAULT_RATIO_ID' => null,
				'DOUBLES' => array()
			)
		);
		foreach (array_chunk($this->currentIdsList, 500) as $pageIds)
		{
			$iterator = Catalog\MeasureRatioTable::getList(array(
				'select' => array('*'),
				'filter' => array('@PRODUCT_ID' => $pageIds),
				'order' => array('PRODUCT_ID' => 'ASC', 'RATIO' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				$productId = (int)$row['PRODUCT_ID'];
				$this->measureRatios[$productId]['RATIOS'][$row['ID']] = $row;
				if ($row['IS_DEFAULT'] == 'Y')
				{
					if ($this->measureRatios[$productId]['DEFAULT_EXISTS'])
					{
						$this->measureRatios[$productId]['DOUBLES'][] = $row['ID'];
					}
					else
					{
						$this->measureRatios[$productId]['DEFAULT_EXISTS'] = true;
						$this->measureRatios[$productId]['DEFAULT_RATIO_ID'] = $row['ID'];
					}
				}
			}
		}
		unset($productId, $row, $iterator, $pageIds);
	}

	private function updateProductData()
	{
		if (empty($this->currentIdsList))
			return;

		$checkMeasure = (
			$this->config['CHECK_MEASURE']
			&& $this->iblockData['CATALOG_TYPE'] != CCatalogSku::TYPE_PRODUCT
			&& (isset($this->iblockData['SUBSCRIPTION']) && $this->iblockData['SUBSCRIPTION'] != 'Y')
		);

		foreach ($this->currentIdsList as $id)
		{
			$product = $this->currentList[$id];
			$product['SUCCESS'] = true;
			if ($this->config['CHECK_AVAILABLE'])
			{
				switch ($this->iblockData['CATALOG_TYPE'])
				{
					case CCatalogSku::TYPE_CATALOG:
						$fields = $this->getCatalogItem($product);
						break;
					case CCatalogSku::TYPE_OFFERS:
						$fields = $this->getOfferIblockItem($product);
						break;
					case CCatalogSku::TYPE_FULL:
						$fields = $this->getFullCatalogItem($product);
						break;
					case CCatalogSku::TYPE_PRODUCT:
						$fields = $this->getProductIblockItem($product);
						break;
					default:
						$fields = array();
						break;
				}

				if ($this->config['CHECK_SETS'])
				{
					$fields['BUNDLE'] = ($product['BUNDLE_EXISTS']
						? Catalog\ProductTable::STATUS_YES
						: Catalog\ProductTable::STATUS_NO
					);
				}
				if ($checkMeasure)
				{
					if ($fields['TYPE'] == Catalog\ProductTable::TYPE_SET)
					{
						if ($this->measureIds['BASE'] !== null)
						{
							$fields['MEASURE'] = $this->measureIds['BASE'];
						}
					}
					else
					{
						if ((int)$product['MEASURE'] <= 0 && $this->measureIds['DEFAULT'] !== null)
						{
							$fields['MEASURE'] = $this->measureIds['DEFAULT'];
						}
					}
				}

				if ($product['PRODUCT_EXISTS'])
				{
					$productResult = Catalog\ProductTable::update($product['ID'], $fields);
				}
				else
				{
					$fields['ID'] = $product['ID'];
					$productResult = Catalog\ProductTable::add($fields);
					$fields['PRODUCT_ID'] = $fields['ID'];
					unset($fields['ID']);
				}

				if ($productResult->isSuccess())
				{
					$product = array_merge($product, $fields);
				}
				else
				{
					$product['SUCCESS'] = false;
					$errorId = 'BX_CATALOG_REINDEX_ERR_PRODUCT_UPDATE_FAIL_EXT';
					if (
						$product['TYPE'] == Catalog\ProductTable::TYPE_OFFER
						|| $product['TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER
					)
						$errorId = 'BX_CATALOG_REINDEX_ERR_OFFER_UPDATE_FAIL_EXT';
					$this->addError(Loc::getMessage(
						$errorId,
						[
							'#ID#' => $id,
							'#NAME#' => $product['NAME'],
							'#ERROR#' => implode('; ', $productResult->getErrorMessages())
						]
					));
					unset($errorId);
				}
				unset($productResult, $fields);
			}

			if ($product['SUCCESS'])
			{
				if ($this->config['CHECK_PRICES'])
					$this->updateProductPrices($id, $product);
				if ($this->config['CHECK_SKU_PRICES'])
				{
					$this->updateSkuPrices($id, $product);
					if ($product['TYPE'] == Catalog\ProductTable::TYPE_SKU)
						Iblock\PropertyIndex\Manager::updateElementIndex($this->params['IBLOCK_ID'], $id);
				}
				if ($this->config['CHECK_MEASURE_RATIO'])
					$this->updateMeasureRatios($id, $product);
			}

			unset($product);
			$this->setLastId($id);
			if ($this->isStopOperation())
				break;
		}
		unset($id);
	}

	private function updateProductPrices($id, array $product)
	{
		if (!$this->config['CHECK_PRICES'])
			return;

		if ($product['TYPE'] == Catalog\ProductTable::TYPE_SKU && !$this->config['SEPARATE_SKU_MODE'])
			return;

		if (empty($this->prices[$id]))
			return;

		if ($product['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
		{
			unset($this->prices[$id]);
			unset($this->existIdsByType[$id]);
			unset($this->existPriceIds[$id]);

			$conn = Main\Application::getConnection();
			$helper = $conn->getSqlHelper();
			$conn->queryExecute(
				'delete from '.$helper->quote(Catalog\PriceTable::getTableName()).
				' where '.$helper->quote('PRODUCT_ID').' = '.$id
			);
			unset($helper, $conn);
			return;
		}

		$success = true;
		$errorMessage = [];
		foreach (array_keys($this->prices[$id]) as $rowId)
		{
			$row = $this->prices[$id][$rowId];
			if (
				!isset($this->currencyReference[$row['CURRENCY']])
				|| $this->currencyReference[$row['CURRENCY']] == 0
			)
				continue;
			$baseRate = $this->currencyReference[$row['CURRENCY']];
			$newScale = $row['PRICE'] * $baseRate;
			if ($newScale == $row['PRICE_SCALE'])
				continue;
			$rowResult = Catalog\PriceTable::update($rowId, ['PRICE_SCALE' => $newScale]);
			if (!$rowResult->isSuccess())
			{
				$success = false;
				$errorMessage = $rowResult->getErrorMessages();
				break;
			}
		}
		unset($rowResult, $newScale, $baseRate, $row, $rowId);

		unset($this->prices[$id]);

		if (!$success)
		{
			$errorId = 'BX_CATALOG_REINDEX_ERR_PRODUCT_PRICE_UPDATE_FAIL_EXT';
			if (
				$product['TYPE'] == Catalog\ProductTable::TYPE_OFFER
				|| $product['TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER
			)
				$errorId = 'BX_CATALOG_REINDEX_ERR_OFFER_PRICE_UPDATE_FAIL_EXT';
			$this->addError(Loc::getMessage(
				$errorId,
				[
					'#ID#' => $id,
					'#NAME#' => $product['NAME'],
					'#ERROR#' => implode('; ', $errorMessage)
				]
			));
			unset($errorId);
		}
		unset($errorMessage, $success);
	}

	private function updateSkuPrices($id, array $product)
	{
		if ($product['TYPE'] != Catalog\ProductTable::TYPE_SKU)
			return;
		if (!$this->config['CHECK_SKU_PRICES'] || $this->config['SEPARATE_SKU_MODE'])
			return;

		$success = true;
		$errorMessage = [];
		if (!empty($this->calculatePrices[$id]))
		{
			foreach (array_keys($this->calculatePrices[$id]) as $resultPriceType)
			{
				$rowId = null;
				$row = $this->calculatePrices[$id][$resultPriceType];
				if (!empty($this->existIdsByType[$id][$resultPriceType]))
				{
					$rowId = array_shift($this->existIdsByType[$id][$resultPriceType]);
					unset($this->existPriceIds[$id][$rowId]);
					unset($this->prices[$id][$rowId]);
				}
				if ($rowId === null)
				{
					$row['PRODUCT_ID'] = $id;
					$row['CATALOG_GROUP_ID'] = $resultPriceType;
					$rowResult = Catalog\PriceTable::add($row);
				}
				else
				{
					$rowResult = Catalog\PriceTable::update($rowId, $row);
				}
				if (!$rowResult->isSuccess())
				{
					$success = false;
					$errorMessage = $rowResult->getErrorMessages();
					break;
				}
			}
		}
		unset($this->calculatePrices[$id]);

		if ($success && !empty($this->existPriceIds[$id]))
		{
			$conn = Main\Application::getConnection();
			$helper = $conn->getSqlHelper();
			$conn->queryExecute(
				'delete from '.$helper->quote(Catalog\PriceTable::getTableName()).
				' where '.$helper->quote('ID').' in ('.implode(',', $this->existPriceIds[$id]).')'
			);
			unset($helper, $conn);
		}

		if (isset($this->existPriceIds[$id]))
			unset($this->existPriceIds[$id]);

		if (!$success)
		{
			$this->addError(Loc::getMessage(
				'BX_CATALOG_REINDEX_ERR_PRODUCT_UPDATE_FAIL_EXT',
				[
					'#ID#' => $id,
					'#NAME#' => $product['NAME'],
					'#ERROR#' => implode('; ', $errorMessage)
				]
			));
		}
		unset($errorMessage, $success);
	}

	private function updateMeasureRatios($id, array $product)
	{
		if (!$this->config['CHECK_MEASURE_RATIO'])
			return;

		if (!isset($this->measureRatios[$id]))
			return;

		$action = '';
		if (isset($this->iblockData['SUBSCRIPTION']) && $this->iblockData['SUBSCRIPTION'] == 'Y')
		{
			if (!empty($this->measureRatios[$id]['RATIOS']))
				$action = 'set';
			else
				$action = 'create';
		}
		elseif (
			$product['TYPE'] == Catalog\ProductTable::TYPE_PRODUCT
			|| $product['TYPE'] == Catalog\ProductTable::TYPE_OFFER
			|| $product['TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER
		)
		{
			if (!empty($this->measureRatios[$id]['RATIOS']))
				$action = 'check';
			else
				$action = 'create';
		}
		elseif ($product['TYPE'] == Catalog\ProductTable::TYPE_SET)
		{
			if (!empty($this->measureRatios[$id]['RATIOS']))
				$action = 'set';
			else
				$action = 'create';
		}
		elseif ($product['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
		{
			if (!empty($this->measureRatios[$id]['RATIOS']))
				$action = 'remove';
		}
		elseif ($product['TYPE'] == Catalog\ProductTable::TYPE_SKU)
		{
			if ($this->config['SEPARATE_SKU_MODE'])
			{
				if (!empty($this->measureRatios[$id]['RATIOS']))
					$action = 'check';
				else
					$action = 'create';
			}
			else
			{
				if (!empty($this->measureRatios[$id]['RATIOS']))
					$action = 'remove';
			}
		}

		switch ($action)
		{
			case 'create':
				$ratioResult = Catalog\MeasureRatioTable::add(array(
					'PRODUCT_ID' => $id,
					'RATIO' => 1,
					'IS_DEFAULT' => 'Y'
				));
				unset($ratioResult);
				break;
			case 'check':
				if (!$this->measureRatios[$id]['DEFAULT_EXISTS'])
				{
					$firstRatio = reset($this->measureRatios[$id]['RATIOS']);
					$ratioResult = Catalog\MeasureRatioTable::update($firstRatio['ID'], array('IS_DEFAULT' => 'Y'));
					unset($ratioResult, $firstRatio);
				}
				if (!empty($this->measureRatios[$id]['DOUBLES']))
				{
					foreach ($this->measureRatios[$id]['DOUBLES'] as $ratioId)
					{
						$ratioResult = Catalog\MeasureRatioTable::update($ratioId, array('IS_DEFAULT' => 'N'));
					}
					unset($ratioResult, $ratioId);
				}
				break;
			case 'set':
				$ratioId = null;
				foreach ($this->measureRatios[$id]['RATIOS'] as $row)
				{
					if ($row['RATIO'] == 1)
					{
						$ratioId = $row['ID'];
						break;
					}
				}
				unset($row);
				if ($ratioId === null && $this->measureRatios[$id]['DEFAULT_RATIO_ID'] !== null)
					$ratioId = $this->measureRatios[$id]['DEFAULT_RATIO_ID'];
				if ($ratioId === null)
				{
					$firstRatio = reset($this->measureRatios[$id]['RATIOS']);
					$ratioId = $firstRatio['ID'];
					unset($firstRatio);
				}
				foreach ($this->measureRatios[$id]['RATIOS'] as $row)
				{
					if ($row['ID'] == $ratioId)
					{
						$ratioResult = Catalog\MeasureRatioTable::update(
							$row['ID'],
							array('RATIO' => 1, 'IS_DEFAULT' => 'Y')
						);
					}
					else
					{
						$ratioResult = Catalog\MeasureRatioTable::delete($row['ID']);
					}
				}
				unset($ratioResult, $row, $ratioId);

				break;
			case 'remove':
				Catalog\MeasureRatioTable::deleteByProduct($id);
				break;
			default:
				break;
		}

		unset($action);
		unset($this->measureRatios[$id]);
	}

	private function getFullCatalogItem(array $product)
	{
		$fields = array(); // only for phpDoc
		switch ($product['SKU_STATE'])
		{
			case Catalog\Product\Sku::OFFERS_AVAILABLE:
			case Catalog\Product\Sku::OFFERS_NOT_AVAILABLE:
				if ($this->config['SEPARATE_SKU_MODE'])
				{
					$fields = array(
						'AVAILABLE' => Catalog\ProductTable::calculateAvailable($product),
						'TYPE' => Catalog\ProductTable::TYPE_SKU,
					);
				}
				else
				{
					$fields = Catalog\Product\Sku::getDefaultParentSettings($product['SKU_STATE'], false);
				}
				break;
			case Catalog\Product\Sku::OFFERS_NOT_EXIST:
				switch ($product['TYPE'])
				{
					case Catalog\ProductTable::TYPE_SKU:
						$fields = Catalog\Product\Sku::getDefaultParentSettings($product['SKU_STATE'], false);
						break;
					case Catalog\ProductTable::TYPE_PRODUCT:
					case Catalog\ProductTable::TYPE_SET:
						$fields['AVAILABLE'] = Catalog\ProductTable::calculateAvailable($product);
						$fields['TYPE'] = (int)$product['TYPE'];
						break;
					default:
						$fields = Catalog\ProductTable::getDefaultAvailableSettings();
						$fields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT;
						break;
				}
				break;
		}
		if ($this->config['CHECK_SETS'])
		{
			if ($fields['TYPE'] == Catalog\ProductTable::TYPE_SET && !$product['SET_EXISTS'])
				$fields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT;
			elseif ($fields['TYPE'] == Catalog\ProductTable::TYPE_PRODUCT && $product['SET_EXISTS'])
				$fields['TYPE'] = Catalog\ProductTable::TYPE_SET;
		}

		return $fields;
	}

	private function getProductIblockItem(array $product)
	{
		return Catalog\Product\Sku::getDefaultParentSettings(
			$product['SKU_STATE'],
			true
		);
	}

	private function getCatalogItem(array $product)
	{
		if ($product['PRODUCT_EXISTS'])
		{
			switch ($product['TYPE'])
			{
				case Catalog\ProductTable::TYPE_PRODUCT:
				case Catalog\ProductTable::TYPE_SET:
					$fields['AVAILABLE'] = Catalog\ProductTable::calculateAvailable($product);
					$fields['TYPE'] = (int)$product['TYPE'];
					break;
				default:
					$fields = array(
						'AVAILABLE' => Catalog\ProductTable::calculateAvailable($product),
						'TYPE' => Catalog\ProductTable::TYPE_PRODUCT,
					);
					break;
			}
		}
		else
		{
			$fields = Catalog\ProductTable::getDefaultAvailableSettings();
			$fields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT;
		}
		if ($this->config['CHECK_SETS'])
		{
			if ($fields['TYPE'] = Catalog\ProductTable::TYPE_SET && !$product['SET_EXISTS'])
				$fields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT;
			elseif ($fields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT && $product['SET_EXISTS'])
				$fields['TYPE'] = Catalog\ProductTable::TYPE_SET;
		}

		return $fields;
	}

	private function getOfferIblockItem(array $product)
	{
		return array(
			'AVAILABLE' => Catalog\ProductTable::calculateAvailable($product),
			'TYPE' => ($product['PARENT_EXISTS'] ? Catalog\ProductTable::TYPE_OFFER : Catalog\ProductTable::TYPE_FREE_OFFER)
		);
	}
}

class CCatalogProductSettings extends CCatalogProductAvailable
{
	const SESSION_PREFIX = 'PS';
	const SETS_ID = 'SETS';

	public function __construct($sessID, $maxExecutionTime, $maxOperationCounter)
	{
		$sessID = (string)$sessID;
		if ($sessID == '')
			$sessID = self::SESSION_PREFIX.time();
		parent::__construct($sessID, $maxExecutionTime, $maxOperationCounter);
	}

	public function runOperation()
	{
		if (!isset($this->params['IBLOCK_ID']))
			return;
		if ($this->params['IBLOCK_ID'] == self::SETS_ID)
			$this->runOperationSets();
		else
			parent::runOperation();
	}

	public function getMessage()
	{
		if ($this->params['IBLOCK_ID'] == self::SETS_ID)
		{
			$messageParams = array(
				'MESSAGE' => Loc::getMessage('BX_STEP_OPERATION_SETS_TITLE'),
				'PROGRESS_TOTAL' => $this->allCounter,
				'PROGRESS_VALUE' => $this->allOperationCounter,
				'TYPE' => 'PROGRESS',
				'DETAILS' => str_replace(array('#ALL#', '#COUNT#'), array($this->allCounter, $this->allOperationCounter), $this->progressTemplate),
				'HTML' => true
			);
			$message = new CAdminMessage($messageParams);
			return $message->Show();
		}
		return parent::getMessage();
	}

	public static function getCatalogList()
	{
		$result = array();

		$catalogList = array();
		$iblockList = array();

		$catalogIterator = Catalog\CatalogIblockTable::getList(array(
			'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID'),
			'order' => array('IBLOCK_ID' => 'ASC')
		));
		while ($catalog = $catalogIterator->fetch())
		{
			$iblockId = (int)$catalog['IBLOCK_ID'];
			$parentIblockID = (int)$catalog['PRODUCT_IBLOCK_ID'];
			$iblockList[$iblockId] = ($parentIblockID > 0 ? $parentIblockID : $iblockId);
			unset($parentIblockID, $iblockId);
		}
		unset($catalog, $catalogIterator);
		if (empty($iblockList))
			return $result;

		$iblockIterator = Catalog\ProductTable::getList(array(
			'select' => array('IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID', new Main\Entity\ExpressionField('CNT', 'COUNT(*)')),
			'filter' => array(static::getProductFilter()),
			'group' => array('IBLOCK_ID'),
			'order' => array('IBLOCK_ID' => 'ASC')
		));
		while ($iblock = $iblockIterator->fetch())
		{
			$iblockId = (int)$iblock['IBLOCK_ID'];
			if (isset($iblockList[$iblockId]))
			{
				$catalogId = $iblockList[$iblockId];
				$catalogList[$catalogId] = $catalogId;
				unset($catalogId);
			}
			unset($iblockId);
		}
		unset($iblock, $iblockIterator);
		unset($iblockList);

		if (empty($catalogList))
			return $result;

		foreach ($catalogList as &$catalogId)
		{
			$iblockList = static::getIblockList($catalogId);
			if (!empty($iblockList))
				$result = array_merge($result, $iblockList);
			unset($iblockList);
		}
		unset($catalogId);

		if (Catalog\Config\Feature::isProductSetsEnabled())
			static::addSetDescription($result);

		return $result;
	}

	protected function initConfig()
	{
		parent::initConfig();
		$this->config['UPDATE_ONLY'] = true;
	}

	protected function runOperationSets()
	{
		global $DB;

		$tableName = '';
		switch (ToUpper($DB->type))
		{
			case 'MYSQL':
				$tableName = 'b_catalog_product_sets';
				break;
			case 'MSSQL':
				$tableName = 'B_CATALOG_PRODUCT_SETS';
				break;
			case 'ORACLE':
				$tableName = 'B_CATALOG_PRODUCT_SETS';
				break;
		}
		if ($tableName == '')
			return;

		Catalog\Product\Sku::disableUpdateAvailable();
		$emptyList = true;
		CTimeZone::Disable();
		$filter = array('TYPE' => CCatalogProductSet::TYPE_SET, 'SET_ID' => 0);
		if ($this->lastID > 0)
			$filter['>ID'] = $this->lastID;
		$topCount = ($this->maxOperationCounter > 0 ? array('nTopCount' => $this->maxOperationCounter) : false);
		$productSetsIterator = CCatalogProductSet::getList(
			array('ID' => 'ASC'),
			$filter,
			false,
			$topCount,
			array('ID', 'OWNER_ID', 'ITEM_ID', 'MODIFIED_BY', 'TIMESTAMP_X')
		);
		while ($productSet = $productSetsIterator->Fetch())
		{
			$emptyList = false;
			$productSet['MODIFIED_BY'] = (int)$productSet['MODIFIED_BY'];
			if ($productSet['MODIFIED_BY'] == 0)
				$productSet['MODIFIED_BY'] = false;
			CCatalogProductSet::recalculateSet($productSet['ID'], $productSet['ITEM_ID']);
			$arTimeFields = array(
				'~TIMESTAMP_X' => $DB->CharToDateFunction($productSet['TIMESTAMP_X'], "FULL"),
				'~MODIFIED_BY' => $productSet['MODIFIED_BY']
			);
			$strUpdate = $DB->PrepareUpdate($tableName, $arTimeFields);
			if (!empty($strUpdate))
			{
				$strQuery = "update ".$tableName." set ".$strUpdate." where ID = ".$productSet['ID'];
				$DB->Query($strQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			$this->setLastId($productSet['ID']);
			if ($this->isStopOperation())
				break;
		}
		CTimeZone::Enable();
		$this->setFinishOperation($emptyList);
		Catalog\Product\Sku::enableUpdateAvailable();
	}

	protected static function addSetDescription(array &$iblockList)
	{
		$counter = (int)CCatalogProductSet::getList(
			array(),
			array('TYPE' => CCatalogProductSet::TYPE_SET, 'SET_ID' => 0),
			array()
		);
		if ($counter <= 0)
			return;
		$iblockList[] = array(
			'ID' => self::SETS_ID,
			'NAME' => Loc::getMessage('BX_STEP_OPERATION_SETS_TITLE'),
			'TITLE' => Loc::getMessage('BX_STEP_OPERATION_SETS_TITLE'),
			'COUNT' => $counter
		);
	}

	protected static function getProductFilter($iblockFilter = false)
	{
		if ($iblockFilter)
		{
			return array(
				'LOGIC' => 'OR',
				'=PRODUCT.QUANTITY_TRACE_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
				'=PRODUCT.CAN_BUY_ZERO_ORIG' => Catalog\ProductTable::STATUS_DEFAULT
			);
		}
		return array(
			'LOGIC' => 'OR',
			'=QUANTITY_TRACE_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
			'=CAN_BUY_ZERO_ORIG' => Catalog\ProductTable::STATUS_DEFAULT
		);
	}
}

class CCatalogIblockReindex extends CCatalogProductAvailable
{
	const NOTIFY_ID = 'CATALOG_REINDEX';

	public function __construct($sessID, $maxExecutionTime, $maxOperationCounter)
	{
		parent::__construct($sessID, $maxExecutionTime, $maxOperationCounter);
	}

	public static function removeNotify()
	{
		// old message from 16.0
		$iterator = \CAdminNotify::GetList([], ['MODULE_ID' => 'catalog', 'TAG' => 'CATALOG_16']);
		while ($row = $iterator->Fetch())
		{
			\CAdminNotify::Delete($row['ID']);
		}

		$iterator = \CAdminNotify::GetList([], ['MODULE_ID' => 'catalog', 'TAG' => self::NOTIFY_ID]);
		while ($row = $iterator->Fetch())
		{
			\CAdminNotify::Delete($row['ID']);
		}
		unset($row, $iterator);
	}

	public static function showNotify()
	{
		self::removeNotify();

		$catalogData = Catalog\CatalogIblockTable::getList([
			'select' => ['CNT'],
			'runtime' => [
				new Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			]
		])->fetch();
		$catalogCount = (isset($catalogData['CNT']) ? (int)$catalogData['CNT'] : 0);
		unset($catalogData);
		if ($catalogCount == 0)
			return;

		$defaultLang = '';
		$messages = [];
		$iterator = Main\Localization\LanguageTable::getList([
			'select' => ['ID', 'DEF'],
			'filter' => ['=ACTIVE' => 'Y']
		]);
		while ($row = $iterator->fetch())
		{
			if ($defaultLang == '')
				$defaultLang = $row['ID'];
			if ($row['DEF'] == 'Y')
				$defaultLang = $row['ID'];
			$languageId = $row['ID'];
			Loc::loadLanguageFile(
				__FILE__,
				$languageId
			);
			$messages[$languageId] = Loc::getMessage(
				'BX_CATALOG_REINDEX_NOTIFY',
				['#LINK#' => '/bitrix/admin/settings.php?lang='.$languageId.'&mid=catalog&mid_menu=1'],
				$languageId
			);
		}
		unset($languageId, $row, $iterator);

		if (empty($messages))
			return;

		\CAdminNotify::Add([
			'MODULE_ID' => 'catalog',
			'TAG' => self::NOTIFY_ID,
			'ENABLE_CLOSE' => 'Y',
			'NOTIFY_TYPE' => \CAdminNotify::TYPE_NORMAL,
			'MESSAGE' => $messages[$defaultLang],
			'LANG' => $messages
		]);
	}

	public static function execAgent()
	{
		self::showNotify();

		return '';
	}

	protected function initConfig()
	{
		parent::initConfig();
		$this->config['CHECK_MEASURE_RATIO'] = true;
		$this->config['CHECK_MEASURE'] = true;
		$this->config['CHECK_PRICES'] = true;
	}

	protected function setOldConfig()
	{
		parent::setOldConfig();
		$this->extendedMode = true;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 *
	 * @param array $product
	 * @return void
	 * @throws Exception
	 * @throws Main\ArgumentException
	 */
	protected function runExtendedOperation(array $product)
	{
		if (!isset($product['TYPE']))
			return;
		if (
			$product['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU
			|| ($product['TYPE'] == Catalog\ProductTable::TYPE_SKU && !$this->isSeparateSkuMode())
		)
			return;

		$ratios = array();
		$defaultExist = false;
		$defaultDoubles = array();
		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('*'),
			'filter' => array('=PRODUCT_ID' => $product['PRODUCT_ID']),
			'order' => array('RATIO' => 'ASC')
		));
		while ($row = $iterator->fetch())
		{
			if ($row['IS_DEFAULT'] == 'Y')
			{
				if (!$defaultExist)
					$defaultExist = true;
				else
					$defaultDoubles[] = $row['ID'];
			}
			$ratios[$row['ID']] = $row;
		}
		unset($row, $iterator);

		if (empty($ratios))
		{
			$ratioResult = Catalog\MeasureRatioTable::add(array(
				'PRODUCT_ID' => $product['PRODUCT_ID'],
				'RATIO' => 1,
				'IS_DEFAULT' => 'Y'
			));
			unset($ratioResult);
		}
		else
		{
			if (!$defaultExist)
			{
				reset($ratios);
				$firstRatio = current($ratios);
				$ratioResult = Catalog\MeasureRatioTable::update($firstRatio['ID'], array('IS_DEFAULT' => 'Y'));
				unset($ratioResult);
				unset($firstRatio);
			}
			if (!empty($defaultDoubles))
			{
				foreach ($defaultDoubles as $ratioId)
				{
					$ratioResult = Catalog\MeasureRatioTable::update($ratioId, array('IS_DEFAULT' => 'N'));
					unset($ratioResult);
				}
				unset($ratioId);
			}
		}
		unset($defaultDoubles, $defaultExist, $ratios);
	}
}