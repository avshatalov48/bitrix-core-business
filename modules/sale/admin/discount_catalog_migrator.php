<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Catalog;
use Bitrix\Main\Config\Option;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
/** @var CAllUser $USER */
/** @var CAllMain $APPLICATION */
global $USER, $APPLICATION;

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin() || !\Bitrix\Main\Loader::includeModule('catalog') || !\Bitrix\Main\Loader::includeModule('sale'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

final class DiscountCatalogMigratorLogger extends \Bitrix\Main\Diag\FileExceptionHandlerLog
{
	const MAX_LOG_SIZE = 10000000;
	const DEFAULT_LOG_FILE = "bitrix/modules/sale_migrator.log";

	public function writeToLog($text)
	{
		return parent::writeToLog($text);
	}
}

final class DiscountCatalogMigrator
{
	const STATUS_FINISH       = 2;
	const STATUS_TIME_EXPIRED = 3;
	const STATUS_ERROR        = 4;

	const TYPE_GENERAL    = 2;
	const TYPE_CUMULATIVE = 3;

	public $countSuccessfulSteps = 0;
	/** @var  DiscountCatalogMigratorLogger */
	protected $logger;

	protected $timeStart = 0;
	/** @var int Seconds */
	protected $maxExecution = 38;
	/** @var \Bitrix\Main\DB\Connection  */
	protected $connection;

	/** @var \Bitrix\Main\DB\SqlHelper */
	protected $sqlHelper;
	protected $isOracle = false;
	protected $isMysql = false;
	protected $isMssql = false;

	private $stepErrors = array();

	public function __construct(array $options = array())
	{
		$this->setTimeStart(time());
		$this->logger = new DiscountCatalogMigratorLogger();
		$this->logger->initialize(array(
			'file' => DiscountCatalogMigratorLogger::DEFAULT_LOG_FILE,
			'log_size' => DiscountCatalogMigratorLogger::MAX_LOG_SIZE,
		));
		$this->connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$this->sqlHelper = $this->connection->getSqlHelper();

		$this->isOracle = $this->connection instanceof \Bitrix\Main\DB\OracleConnection;
		$this->isMysql = $this->connection instanceof \Bitrix\Main\DB\MysqlCommonConnection;
		$this->isMssql = $this->connection instanceof \Bitrix\Main\DB\MssqlConnection;

		\Bitrix\Sale\Discount\Preset\Manager::getInstance()->registerAutoLoader();
	}

	public function log($data)
	{
		if(!is_string($data))
		{
			$data = print_r($data, true);
		}
		$this->logger->writeToLog('Date:' . date('r') . "\n" . $data . "\n\n");
	}

	/**
	 * @param int $timeStart
	 */
	public function setTimeStart($timeStart)
	{
		$this->timeStart = $timeStart;
	}

	/**
	 * @return int
	 */
	public function getTimeStart()
	{
		return $this->timeStart;
	}

	protected function isTimeExpired()
	{
		return (time() - $this->getTimeStart()) > $this->maxExecution;
	}

	/**
	 * Check expired by time and throw exception
	 * @param bool $force
	 * @throws TimeExecutionException
	 */
	protected function abortIfNeeded($force = false)
	{
		if($force || $this->isTimeExpired())
		{
			throw new TimeExecutionException();
		}
	}

	protected function setStepFinished($stepName, $description = '')
	{
		$stepName = strtr($stepName, array(':' => ''));
		Option::set(
			'sale',
			'~sF' . md5($stepName),
			'Y'
		);

		$this->countSuccessfulSteps++;
		$this->log(array(
			"finished",
			"Step {$stepName}",
			$description,
		));

		$this->resetCounter();
		$this->abortIfNeeded();

		return $this;
	}

	protected function isStepFinished($stepName, $description = '')
	{
		$this->abortIfNeeded();

		$stepName = strtr($stepName, array(':' => ''));
		$finished = Option::get('sale', '~sF' . md5($stepName), 'N') == 'Y';

		if(!$finished)
		{
			$this->log(array(
				"Start",
				"Step {$stepName}",
				$description,
			));
		}
		else
		{
			$this->countSuccessfulSteps++;
			$this->log(array(
				"Skip",
				"Step {$stepName}",
			));
		}

		return $finished;
	}

	protected function resetCounter()
	{
		$this->storeCounter(0);
	}

	protected function storeCounter($data)
	{
		Option::set(
			'sale',
			'~migrateStepCounter',
			$data
		);

		$this->abortIfNeeded();
	}

	protected function getCounter()
	{
		return Option::get(
			'sale',
			'~migrateStepCounter',
			0
		);
	}

	protected function checkRequired()
	{
		if(!\Bitrix\Main\Loader::includeModule('catalog'))
		{
			throw new Exception('Bad include catalog');
		}

		if(!\Bitrix\Main\Loader::includeModule('sale'))
		{
			throw new Exception('Bad include sale');
		}
	}

	public function revert()
	{
		$this->checkRequired();
		set_time_limit(0);

		if(!$this->isMysql)
		{
			throw new Exception('Revert command is available only on MySql');
		}

		$this->connection->queryExecute(
			"DELETE FROM b_option WHERE MODULE_ID = 'sale' AND VALUE = 'Y' AND NAME LIKE '~sF%'"
		);

		$query = Catalog\DiscountTable::getList(array(
			'select' => array('ID', 'SALE_ID'),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField('FSALE_ID', 'CASE WHEN SALE_ID IS NOT NULL THEN 1 ELSE 0 END')
			),
			'filter' => array('=FSALE_ID' => true)
		));

		while($row = $query->fetch())
		{
			if(empty($row['SALE_ID']))
			{
				continue;
			}

			Sale\Internals\DiscountTable::delete($row['SALE_ID']);
		}

//		$this->connection->queryExecute(
//			"UPDATE b_sale_discount SET PRIORITY = PRIORITY_BACKUP"
//		);
//
//		$this->connection->queryExecute(
//			"ALTER TABLE b_sale_discount DROP COLUMN PRIORITY_BACKUP"
//		);

		$this->connection->queryExecute("
			UPDATE b_catalog_discount_cond cond SET cond.ACTIVE = 'Y' 
			WHERE EXISTS(
				SELECT 'x' FROM b_catalog_discount WHERE ID = cond.DISCOUNT_ID AND SALE_ID IS NOT NULL
			)
		");

		$this->connection->queryExecute(
			"UPDATE b_catalog_discount SET ACTIVE = 'Y', SALE_ID = null WHERE SALE_ID IS NOT NULL"
		);

		$this->resetCounter();
		Option::set(
			'sale',
			'use_sale_discount_only',
			'N'
		);
		Option::set(
			'sale',
			'process_discount_migrator',
			false
		);

		return true;
	}

	public function run()
	{
		if($this->isStepFinished(__METHOD__))
		{
			return self::STATUS_FINISH;
		}

		define('SALE_DISCOUNT_MIGRATE_MODE', true);
		$this->checkRequired();

		if(Option::get('sale', 'use_sale_discount_only', false) === 'Y')
		{
			return self::STATUS_FINISH;
		}

		if(!Option::get('sale', 'process_discount_migrator', false))
		{
			Option::set('sale', 'process_discount_migrator', true);
			Option::set('sale', 'use_sale_discount_only', 'N');
			Option::set('main', 'site_stopped', 'Y');
		}

//		$this->addBackupPriorityColumn();
//		$this->backupPriorityColumn();
//		$this->recalculatePriority();

		$this->moveDiscounts();
		$this->moveCumulativeDiscounts();
		$this->moveCoupons();
		$this->fillShortDescription();

		$this->processFinallyActions();

		$this->setStepFinished(__METHOD__);

		return self::STATUS_FINISH;
	}

	private function processFinallyActions()
	{
		Option::set('sale', 'use_sale_discount_only', 'Y');
		Option::set('sale', 'process_discount_migrator', false);
		Option::set('main', 'site_stopped', 'N');

		CAdminNotify::deleteByTag('sale_discount_catalog_migrator');
	}

	protected function getMaxPriorityFromOldSaleDiscounts()
	{
		$maxPr = Option::get('sale', '~sF_max_pr', false);
		if($maxPr === false)
		{
			$data = $this->connection->query('SELECT MAX(PRIORITY) max_pr FROM b_sale_discount')->fetch();
			$maxPr = $data['max_pr'];

			Option::set('sale', '~sF_max_pr', $maxPr);
		}

		return $maxPr;
	}

	protected function moveDiscounts()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$counter = $this->getCounter();
		$discountIterator = Catalog\DiscountTable::getList(array(
			'filter' => array(
				'>ID' => $counter,
				'TYPE' => \CCatalogDiscount::ENTITY_ID,
				'=ACTIVE' => 'Y',
			),
		    'order' => array('ID' => 'ASC'),
		));

		$this->migrateDiscounts($discountIterator);

		$this->setStepFinished(__METHOD__);
	}

	private function migrateDiscounts(\Bitrix\Main\DB\Result $discountIterator)
	{
		global $APPLICATION;
		$maxPriority = $this->getMaxPriorityFromOldSaleDiscounts() + 10;

		foreach ($discountIterator as $discount)
		{
			$typeDiscount = $discount['TYPE'] == CCatalogDiscountSave::ENTITY_ID?
				self::TYPE_CUMULATIVE : self::TYPE_GENERAL
			;

			$errors = array();
			$newData = array(
				'XML_ID' => $discount['XML_ID'],
				'LID' => $discount['SITE_ID'],
				'ACTIVE' => $discount['ACTIVE'],
				'ACTIVE_FROM' => $discount['ACTIVE_FROM'],
				'ACTIVE_TO' => $discount['ACTIVE_TO'],
				'NAME' => $discount['NAME'],
				'SORT' => $discount['SORT'],
				'CURRENCY' => $discount['CURRENCY'],
				'TIMESTAMP_X' => $discount['TIMESTAMP_X'],
				'MODIFIED_BY' => $discount['MODIFIED_BY'],
				'DATE_CREATE' => $discount['DATE_CREATE'],
				'CREATED_BY' => $discount['CREATED_BY'],
				'PRIORITY' => $discount['PRIORITY'] + $maxPriority,
				'LAST_DISCOUNT' => $discount['LAST_DISCOUNT'],
				'EXECUTE_MODULE' => 'catalog',
			);

			if ($discount['TYPE'] == \CCatalogDiscountSave::ENTITY_ID)
			{
				$newData['PRESET_ID'] = \Sale\Handlers\DiscountPreset\Cumulative::className();
			}

			$rawFields = $this->createActionAndCondition($discount);
			$rawFields = array_merge($rawFields, array(
				'ID' => $discount['ID'],
				'LID' => $newData['LID'],
				'CURRENCY' => $discount['CURRENCY'],
				'USER_GROUPS' => array(2), //fabrication!
			));

			if (\CSaleDiscount::checkFields('ADD', $rawFields))
			{
				$newData['UNPACK'] = $rawFields['UNPACK'];
				$newData['APPLICATION'] = $rawFields['APPLICATION'];
				if (!is_array($rawFields['ACTIONS']))
					$rawFields['ACTIONS'] = unserialize($rawFields['ACTIONS'], ['allowed_classes' => false]);
				$newData['ACTIONS_LIST'] = $rawFields['ACTIONS'];

				if (!is_array($rawFields['CONDITIONS']))
					$rawFields['CONDITIONS'] = unserialize($rawFields['CONDITIONS'], ['allowed_classes' => false]);
				$newData['CONDITIONS_LIST'] = $rawFields['CONDITIONS'];

				if (isset($rawFields['EXECUTE_MODULE']))
				{
					$newData['EXECUTE_MODULE'] = $rawFields['EXECUTE_MODULE'];
				}

				if ($newData['PRESET_ID'] == \Sale\Handlers\DiscountPreset\Cumulative::className())
				{
					$newData['EXECUTE_MODULE'] = 'sale';
				}

				$addResult = \Bitrix\Sale\Internals\DiscountTable::add($newData);
				if(!$addResult->isSuccess())
				{
					$this->log(array(
						"Can't add",
						$discount['ID'],
						$addResult->getErrorMessages()
					));

					$this->setAsConverted($discount['ID'], 0, $typeDiscount);
				}
				else
				{
					if(isset($rawFields['ENTITIES']))
					{
						Sale\Internals\DiscountEntitiesTable::updateByDiscount($addResult->getId(), $rawFields['ENTITIES'], true);
					}
					if(isset($rawFields['HANDLERS']['MODULES']))
					{
						Sale\Internals\DiscountModuleTable::updateByDiscount($addResult->getId(), $rawFields['HANDLERS']['MODULES'], true);
					}
					$this->moveUserGroupsByDiscount($discount, $addResult->getId(), $typeDiscount);

					$this->setAsConverted($discount['ID'], $addResult->getId(), $typeDiscount);
				}
			}
			else
			{
				if($ex = $APPLICATION->GetException())
				{
					$errors[] = $ex->GetString();
				}
				else
				{
					$errors[] = GetMessage('DISCOUNT_CATALOG_MIGRATOR_UNKNOWN_ERROR');
				}

				$this->stepErrors[] = GetMessage(
					'DISCOUNT_CATALOG_MIGRATOR_ERROR_REPORT',
					array(
						'#URL#' => str_replace('#ID#', $discount['ID'], '/bitrix/admin/cat_discount_edit.php?ID=#ID#&lang='.LANGUAGE_ID),
						'#TITLE#' => (trim((string)$discount['NAME']) != '' ? $discount['NAME'] : $discount['ID']),
						'#ERRORS#' => implode('; ', $errors)
					)
				);
				$this->setAsConverted($discount['ID'], 0, $typeDiscount);

				$this->log(array(
					"Check fields error",
					$discount['ID'],
					$errors
				));
			}

			$this->storeCounter($discount['ID']);
		}
	}

	protected function moveCumulativeDiscounts()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$counter = $this->getCounter();
		$discountIterator = Catalog\DiscountTable::getList(array(
			'filter' => array(
				'>ID' => $counter,
				'TYPE' => \CCatalogDiscountSave::ENTITY_ID,
				'=ACTIVE' => 'Y',
			),
		    'order' => array('ID' => 'ASC'),
		));

		$this->migrateDiscounts($discountIterator);
		$this->recalculatePriorityForCumulativeDiscounts();

		$this->setStepFinished(__METHOD__);
	}

	/**
	 * We are moving cumulative discounts to the end of list with the lowest priority.
	 */
	protected function recalculatePriorityForCumulativeDiscounts()
	{
		if ($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$this->connection->queryExecute("
			UPDATE b_sale_discount discount
			INNER JOIN b_catalog_discount c_discount ON c_discount.SALE_ID = discount.ID
			SET discount.PRIORITY = -1
			WHERE c_discount.TYPE = 1 AND discount.ACTIVE = 'Y'"
		);

		$this->connection->queryExecute("
			UPDATE b_sale_discount discount
			SET discount.PRIORITY = discount.PRIORITY + 100
			WHERE discount.ACTIVE = 'Y' AND discount.PRIORITY <> -1"
		);

		$this->connection->queryExecute("
			UPDATE b_sale_discount discount
			SET discount.PRIORITY = 50
			WHERE discount.ACTIVE = 'Y' AND discount.PRIORITY = -1"
		);

		$this->setStepFinished(__METHOD__);
	}

	private function getActionsAndConditionsForCumulativeDiscount(array $catalogRow)
	{
		if ($catalogRow['TYPE'] != \CCatalogDiscountSave::ENTITY_ID)
		{
			return array();
		}

		$cumulativePreset = new \Sale\Handlers\DiscountPreset\Cumulative();

		$state = new \Bitrix\Sale\Discount\Preset\State($catalogRow);
		$state['discount_ranges'] = $this->getRanges($catalogRow['ID']);
		$state['discount_type_sum_period'] = $this->getTypeSumPeriod($catalogRow);

		$state['discount_sum_order_start'] = $catalogRow['COUNT_FROM'];
		$state['discount_sum_order_end'] = $catalogRow['COUNT_TO'];
		$state['discount_sum_period_value'] = $catalogRow['COUNT_SIZE'];
		$state['discount_sum_period_type'] = $catalogRow['COUNT_TYPE'];

		$discountFields = $cumulativePreset->generateDiscount($state);

		return array(
			'CONDITIONS' => $discountFields['CONDITIONS'],
			'ACTIONS' => $discountFields['ACTIONS'],
		);
	}

	private function getTypeSumPeriod(array $catalogRow)
	{
		$oldType = $catalogRow['COUNT_PERIOD'];
		switch ($oldType)
		{
			case \CCatalogDiscountSave::COUNT_TIME_ALL:
				return \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_ALL_TIME;
			case \CCatalogDiscountSave::COUNT_TIME_INTERVAL:
				return \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_INTERVAL;
			case \CCatalogDiscountSave::COUNT_TIME_PERIOD:
				return \Sale\Handlers\DiscountPreset\Cumulative::TYPE_COUNT_PERIOD_RELATIVE;
		}

		return null;
	}

	private function getRanges($cumulativeDiscountId)
	{
		$cumulativeDiscountId = (int)$cumulativeDiscountId;
		$sql = "SELECT RANGE_FROM, TYPE, VALUE FROM b_catalog_disc_save_range WHERE DISCOUNT_ID = {$cumulativeDiscountId}";
		foreach ($this->connection->query($sql) as $row)
		{
			$matrix[] = array(
				'sum' => $row['RANGE_FROM'],
				'value' => $row['VALUE'],
				'type' => $row['TYPE'],
			);
		}

		\Bitrix\Main\Type\Collection::sortByColumn($matrix, 'sum');

		return $matrix;
	}

	protected function moveCoupons()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		if($this->isMysql)
		{
			$sql = "
				INSERT IGNORE INTO b_sale_discount_coupon 
					(DISCOUNT_ID, ACTIVE, COUPON, TYPE, DATE_APPLY, TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, DESCRIPTION)
					SELECT
						cat_d.SALE_ID,
						cat_coupon.ACTIVE,
						cat_coupon.COUPON,
						CASE cat_coupon.ONE_TIME
						WHEN 'N'
							THEN 4
						WHEN 'Y'
							THEN 1
						WHEN 'O'
							THEN 2
						END,
						cat_coupon.DATE_APPLY,
						cat_coupon.TIMESTAMP_X,
						cat_coupon.MODIFIED_BY,
						cat_coupon.DATE_CREATE,
						cat_coupon.CREATED_BY,
						cat_coupon.DESCRIPTION
					FROM b_catalog_discount_coupon cat_coupon
						INNER JOIN b_catalog_discount cat_d ON cat_coupon.DISCOUNT_ID = cat_d.ID
					WHERE cat_d.SALE_ID IS NOT NULL AND cat_d.SALE_ID <> 0 			
			";
		}
		else
		{
			$sql = "
				INSERT INTO b_sale_discount_coupon 
					(DISCOUNT_ID, ACTIVE, COUPON, TYPE, DATE_APPLY, TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, DESCRIPTION)
					SELECT
						cat_d.SALE_ID,
						cat_coupon.ACTIVE,
						cat_coupon.COUPON,
						CASE cat_coupon.ONE_TIME
						WHEN 'N'
							THEN 4
						WHEN 'Y'
							THEN 1
						WHEN 'O'
							THEN 2
						END,
						cat_coupon.DATE_APPLY,
						cat_coupon.TIMESTAMP_X,
						cat_coupon.MODIFIED_BY,
						cat_coupon.DATE_CREATE,
						cat_coupon.CREATED_BY,
						cat_coupon.DESCRIPTION
					FROM b_catalog_discount_coupon cat_coupon
						INNER JOIN b_catalog_discount cat_d ON cat_coupon.DISCOUNT_ID = cat_d.ID
					WHERE 
						cat_d.SALE_ID IS NOT NULL AND 
						cat_d.SALE_ID <> 0 AND
						NOT EXISTS(SELECT 'x' FROM b_sale_discount_coupon WHERE COUPON = cat_coupon.COUPON)
			";
		}

		$this->connection->queryExecute($sql);

		if($this->isMysql)
		{
			$this->connection->queryExecute("
				UPDATE b_sale_discount sale_d SET USE_COUPONS = 'Y' WHERE EXISTS(
						SELECT 'x' FROM b_sale_discount_coupon WHERE DISCOUNT_ID = sale_d.ID
				)		
			");
		}
		else
		{
			$this->connection->queryExecute("
				UPDATE b_sale_discount  SET USE_COUPONS = 'Y' 
				WHERE ID IN (SELECT c.DISCOUNT_ID FROM b_sale_discount_coupon c)  
			");
		}

		$this->setStepFinished(__METHOD__);
	}

	protected function fillShortDescription()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$counter = $this->getCounter();

		$discountIterator = \Bitrix\Sale\Internals\DiscountTable::getList(array(
			'select' => array('ID', 'ACTIONS_LIST'),
			'filter' => array(
				'>ID' => $counter,
				'=SHORT_DESCRIPTION_STRUCTURE' => null,
			),
		    'order' => array('ID' => 'ASC'),
		));

		while($discount = $discountIterator->fetch())
		{
			if($counter > $discount['ID'])
			{
				continue;
			}

			$actionConfiguration = Sale\Discount\Actions::getActionConfiguration($discount);
			if($actionConfiguration)
			{
				\Bitrix\Sale\Internals\DiscountTable::update($discount['ID'], array(
					'SHORT_DESCRIPTION_STRUCTURE' => $actionConfiguration,
				));
			}

			$this->storeCounter($discount['ID']);
		}

		$this->setStepFinished(__METHOD__);
	}

	protected function recalculatePriority()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$sql = '';
		if($this->isMysql)
		{
			$this->connection->queryExecute('SET @i = 0');
			$sql = "
				UPDATE b_sale_discount t1 
				INNER JOIN (
	                  SELECT (@i := @i + 20) NN, ID
	                  FROM b_sale_discount
	                  ORDER BY PRIORITY_BACKUP DESC, SORT ASC, ID ASC
	            ) t2 ON t1.ID = t2.ID
				SET PRIORITY = NN;			
			";
		}
		elseif($this->isMssql || $this->isOracle)
		{
			$sql = "
				UPDATE b_sale_discount SET PRIORITY = rowNumber FROM b_sale_discount
					INNER JOIN	
						(SELECT ID, row_number() OVER (ORDER BY PRIORITY_BACKUP DESC, SORT ASC, ID ASC) AS rowNumber FROM b_sale_discount) 
							t2 ON t2.ID = b_sale_discount.ID			
			";
		}

		$this->connection->queryExecute($sql);

		$this->setStepFinished(__METHOD__);
	}

	protected function addBackupPriorityColumn()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$sql = '';
		if($this->isMysql)
		{
			$sql = "ALTER TABLE b_sale_discount ADD PRIORITY_BACKUP int";
		}
		elseif($this->isMssql)
		{
			$sql = "ALTER TABLE B_SALE_DISCOUNT ADD PRIORITY_BACKUP int";
		}
		elseif($this->isOracle)
		{
			$sql = "ALTER TABLE B_SALE_DISCOUNT ADD PRIORITY_BACKUP NUMBER(18) NULL";
		}

		$this->connection->queryExecute($sql);

		$this->setStepFinished(__METHOD__);
	}

	protected function backupPriorityColumn()
	{
 		if($this->isStepFinished(__METHOD__))
		{
			return;
		}

		$this->connection->queryExecute("UPDATE b_sale_discount SET PRIORITY_BACKUP = PRIORITY");

		$this->setStepFinished(__METHOD__);
	}

	protected function setAsConverted($catalogDiscountId, $saleDiscountId, $typeDiscount = self::TYPE_GENERAL)
	{
		if ($typeDiscount === self::TYPE_GENERAL)
		{
			CCatalogDiscount::Update($catalogDiscountId, array(
				'ACTIVE' => 'N',
				'SALE_ID' => $saleDiscountId,
			));
		}
		if ($typeDiscount === self::TYPE_CUMULATIVE)
		{
			CCatalogDiscountSave::Update($catalogDiscountId, array(
				'ACTIVE' => 'N',
				'SALE_ID' => $saleDiscountId,
			));
		}
	}

	protected function getAllUserGroups()
	{
		static $groups = null;

		if($groups === null)
		{
			$groupsIterator = \Bitrix\Main\GroupTable::getList(array(
				'select' => array('ID'),
			));
			while($row = $groupsIterator->fetch())
			{
				$groups[] = $row['ID'];
			}
		}

		return $groups;
	}

	protected function getPriceTypesByDiscount($catalogDiscountId)
	{
		$groupDiscountIterator = Catalog\DiscountRestrictionTable::getList(array(
			'select' => array('DISCOUNT_ID', 'PRICE_TYPE_ID'),
			'filter' => array('=DISCOUNT_ID' => $catalogDiscountId, '=ACTIVE' => 'Y'),
		));

		$priceTypeIds = array();
		while($row = $groupDiscountIterator->fetch())
		{
			if($row['PRICE_TYPE_ID'] == -1)
			{
				return array();
			}
			$priceTypeIds[$row['PRICE_TYPE_ID']] = $row['PRICE_TYPE_ID'];
		}

		return $priceTypeIds;
	}

	protected function moveUserGroupsByDiscount(array $catalogRow, $saleDiscountId, $typeDiscount = self::TYPE_GENERAL)
	{
		$catalogDiscountId = (int)$catalogRow['ID'];

		if ($typeDiscount === self::TYPE_CUMULATIVE)
		{
			$groupDiscountIterator = $this->connection->query("
				SELECT GROUP_ID as USER_GROUP_ID FROM b_catalog_disc_save_group WHERE DISCOUNT_ID = {$catalogDiscountId}
			");
		}
		else
		{
			$groupDiscountIterator = Catalog\DiscountRestrictionTable::getList(array(
				'select' => array('USER_GROUP_ID'),
				'filter' => array('=DISCOUNT_ID' => $catalogDiscountId, '=ACTIVE' => 'Y'),
			));
		}

		$groupIds = array();
		foreach ($groupDiscountIterator as $row)
		{
			if($row['USER_GROUP_ID'] == -1)
			{
				$groupIds = $this->getAllUserGroups();
				break;
			}
			$groupIds[$row['USER_GROUP_ID']] = $row['USER_GROUP_ID'];
		}

		\Bitrix\Sale\Internals\DiscountGroupTable::updateByDiscount(
			$saleDiscountId,
			$groupIds,
			'Y',
			false
		);
	}

	protected function createActionAndCondition(array $catalogRow)
	{
		if ($catalogRow['TYPE'] == \CCatalogDiscountSave::ENTITY_ID)
		{
			return array_intersect_key(
				$this->getActionsAndConditionsForCumulativeDiscount($catalogRow),
				array(
					'CONDITIONS' => true,
					'ACTIONS' => true,
				)
			);
		}

		return array(
			'CONDITIONS' => $this->createConditionStructureForGeneralDiscount($catalogRow),
			'ACTIONS' => $this->createApplicationStructureForGeneralDiscount($catalogRow),
		);
	}

	protected function createConditionStructureForGeneralDiscount(array $catalogRow)
	{
		$structure = array(
			'CLASS_ID' => 'CondGroup',
			'DATA' => array(
				'All' => 'AND',
				'True' => 'True',
			),
			'CHILDREN' => array(
				array(
					'CLASS_ID' => 'CondBsktProductGroup',
					'DATA' => array(
						'Found' => 'Found',
						'All' => 'AND',
					),
					'CHILDREN' => array(
						array(
							'CLASS_ID' => 'CondBsktSubGroup',
							'DATA' => $catalogRow['CONDITIONS_LIST']['DATA'],
							'CHILDREN' => $catalogRow['CONDITIONS_LIST']['CHILDREN'],
						),
					),
				),
			),
		);

		if($catalogRow['RENEWAL'] === 'Y')
		{
			$structure['CHILDREN'][] = array(
				'CLASS_ID' => 'CondCatalogRenewal',
				'DATA' => array(
					'value' => 'Y',
				),
			);
		}

		return $structure;
	}

	protected function createApplicationStructureForGeneralDiscount(array $catalogRow)
	{
		$type = '';
		$unit = '';
		if($catalogRow['VALUE_TYPE'] === \CCatalogDiscount::TYPE_PERCENT)
		{
			$type = \CSaleActionCtrlBasketGroup::ACTION_TYPE_DISCOUNT;
			$unit = \CSaleActionCtrlBasketGroup::VALUE_UNIT_PERCENT;
		}
		elseif($catalogRow['VALUE_TYPE'] === \CCatalogDiscount::TYPE_FIX)
		{
			$type = \CSaleActionCtrlBasketGroup::ACTION_TYPE_DISCOUNT;
			$unit = \CSaleActionCtrlBasketGroup::VALUE_UNIT_CURRENCY;
		}
		elseif($catalogRow['VALUE_TYPE'] === \CCatalogDiscount::TYPE_SALE)
		{
			$type = \CSaleActionCtrlBasketGroup::ACTION_TYPE_CLOSEOUT;
			$unit = \CSaleActionCtrlBasketGroup::VALUE_UNIT_CURRENCY;
		}

		$structure = array(
			'CLASS_ID' => 'CondGroup',
			'DATA' => array(
				'All' => 'AND',
			),
			'CHILDREN' => array(
				array(
					'CLASS_ID' => 'ActSaleBsktGrp',
					'DATA' => array(
						'Type' => $type,
						'Value' => $catalogRow['VALUE'],
						'Unit' => $unit,
						'All' => 'AND',
						'True' => 'True',
						'Max' => isset($catalogRow['MAX_DISCOUNT']) ? $catalogRow['MAX_DISCOUNT'] : 0,
					),
					'CHILDREN' => array(
						array(
							'CLASS_ID' => 'ActSaleSubGrp',
							'DATA' => $catalogRow['CONDITIONS_LIST']['DATA'],
							'CHILDREN' => $this->replaceCondGroup($catalogRow['CONDITIONS_LIST']['CHILDREN']),
						),
					),
				),
			),
		);

		$priceTypesByDiscount = $this->getPriceTypesByDiscount($catalogRow['ID']);
		if($priceTypesByDiscount)
		{
			$structure['CHILDREN'][0]['CHILDREN'][] = array(
				'CLASS_ID' => 'CondCatalogPriceType',
				'DATA' => array(
					'logic' => 'Equal',
					'value' => $priceTypesByDiscount,
				),
			);
		}

		return $structure;
	}

	protected function replaceCondGroup($children)
	{
		foreach ($children as $i => $item)
		{
			if (is_array($item) && !empty($item['CLASS_ID']) && $item['CLASS_ID'] === 'CondGroup')
			{
				$children[$i]['CLASS_ID'] = 'ActSaleSubGrp';
			}

			if (!empty($item['CHILDREN']))
			{
				$children[$i]['CHILDREN'] = $this->replaceCondGroup($children[$i]['CHILDREN']);
			}
		}

		return $children;
	}

	protected function rewriteUnpack(array $catalogRow)
	{
		$unpackCode = $catalogRow['UNPACK'];
		$renewalMixin = '';
		if($catalogRow['RENEWAL'] == 'Y')
		{
			$renewalMixin = '(isset($arOrder[\'RECURRING_ID\']) && $arOrder[\'RECURRING_ID\'])&&';
		}

		return 'function ($arOrder)
		{
			$f = function($row){
				$arProduct = \Bitrix\Sale\Compatible\DiscountCompatibility::reformatProductRowToOldCatalog($row);
				return ' . $unpackCode . ';
			};
		
			return ' . $renewalMixin . '((CSaleBasketFilter::ProductFilter($arOrder, $f)));
		}';
	}

	protected function rewriteApplication(array $catalogRow)
	{
		return 'function (&$arOrder)
		{
			$f = function ($row)
			{
				$arProduct = \Bitrix\Sale\Compatible\DiscountCompatibility::reformatProductRowToOldCatalog($row);
				return ' . $catalogRow['UNPACK'] . ';
			};
			\Bitrix\Sale\Discount\Actions::applyToBasket($arOrder, array(
				\'VALUE\' => -' . $catalogRow['VALUE'] . ',
				\'UNIT\' => \'' . $catalogRow['VALUE_TYPE'] . '\',
				\'LIMIT_VALUE\' => ' . (isset($catalogRow['MAX_DISCOUNT'])? $catalogRow['MAX_DISCOUNT'] : 0) . ',
			), $f);
		}';
	}
}

class TimeExecutionException extends \Bitrix\Main\SystemException
{}

IncludeModuleLangFile(__FILE__);

if (!empty($_REQUEST['revert']))
{
	global $APPLICATION;
	$migrator = new DiscountCatalogMigrator();
	if($migrator->revert())
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", array('revert', 'migrator_process')));
	}
}

if (isset($_REQUEST['migrator_process']) && ($_REQUEST['migrator_process'] === 'Y'))
{
	CUtil::JSPostUnescape();

	require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php');

	$totalCount = 8; //count of steps
	$processedSummary = 0;

	$status = false;
	try
	{
		$migrator = new DiscountCatalogMigrator();
		$status = $migrator->run();

		$processedSummary += $migrator->countSuccessfulSteps;
	}
	catch (TimeExecutionException $e)
	{
		$status = DiscountCatalogMigrator::STATUS_TIME_EXPIRED;
		$processedSummary += $migrator->countSuccessfulSteps;
	}
	catch (Exception $e)
	{
		throw $e;
		$status = DiscountCatalogMigrator::STATUS_ERROR;
		$processedSummary += $migrator->countSuccessfulSteps;
	}

	?>
	<script>
		CloseWaitWindow();
	</script>
	<?php

	if ($status === DiscountCatalogMigrator::STATUS_ERROR)
	{
		CAdminMessage::ShowMessage(
			array(
				'MESSAGE' => GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_FAILED'),
				'DETAILS' => '<div id="wd_convert_finish"></div>',
				'HTML'    => true,
				'TYPE'    => 'ERROR'
			)
		);

		?>
		<script>
			StopConvert();
		</script>
		<?php
	}
	elseif ($status === DiscountCatalogMigrator::STATUS_FINISH)
	{
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"HTML" => true,
			"MESSAGE" => GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_COMPLETE'),
			"DETAILS" => "#PROGRESS_BAR#",
			"PROGRESS_TOTAL" => 1,
			"PROGRESS_VALUE" => 1,
		));

		CAdminMessage::ShowMessage(
			array(
				'MESSAGE' => GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_COMPLETE'),
				'DETAILS' => '<div id="wd_convert_finish"></div>',
				'HTML'    => true,
				'TYPE'    => 'OK'
			)
		);

		?>
		<script>
			EndConvert();
		</script>
		<?php
	}
	elseif($status === $migrator::STATUS_TIME_EXPIRED)
	{
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"HTML" => true,
			"MESSAGE" => GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_IN_PROGRESS'),
			"DETAILS" => "#PROGRESS_BAR#",
			"PROGRESS_TOTAL" => $totalCount,
			"PROGRESS_VALUE" => intval($processedSummary),
		));

		?>
		<script>
			DoNext(<?php echo $processedSummary; ?>);
		</script>
		<?php
	}

	require($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php');
}
else
{
	$listNonSupportedFeatures = array();

	$discountWithRelativeActivePeriod = (bool)Catalog\DiscountTable::getList(array(
		'select' => array('ID'),
		'filter' => array(
			'=ACTIVE' => 'Y',
			'>ACTION_SIZE' => 0,
		),
		'limit' => 1,
	))->fetch();

	if($discountWithRelativeActivePeriod)
	{
		$listNonSupportedFeatures[] = Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_RELATIVE_ACTIVE_PERIOD');
	}

	//check currency <> SITE_ID sale currency
	$connection = Application::getConnection();
	$isMysql = $connection instanceof \Bitrix\Main\DB\MysqlCommonConnection;
	$discountWithOtherCurrency = $connection->query('
		SELECT ID FROM b_catalog_discount d 
					INNER JOIN b_sale_lang l ON d.SITE_ID = l.LID 
		WHERE d.ACTIVE = "Y" AND d.CURRENCY <> l.CURRENCY' . ($isMysql? ' LIMIT 1' : ''))->fetch();

	if($discountWithOtherCurrency)
	{
		$listNonSupportedFeatures[] = Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_CURRENCY_SALE_SITE');
	}

	foreach($listNonSupportedFeatures as $i => $feature)
	{
		$listNonSupportedFeatures[$i] = '<span style="margin-left: 10px;">' . $feature . '</span>';
	}

	//check "discount save"
	$isThereSaveDiscounts = false;
	$discountSaveResult = CCatalogDiscountSave::GetList(array(), array('ACTIVE' => 'Y',), false, array("nTopCount" => 1));
	while($discountSaveResult && ($row = $discountSaveResult->fetch()))
	{
		$isThereSaveDiscounts = true;
	}

	$popupText =
		Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_NEW', array('#CUMULATIVE_PART#' => $isThereSaveDiscounts? Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_CUMULATIVE_PART') : '',)) . '<br>' .
		Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_FINAL')
	;

	if($listNonSupportedFeatures)
	{
		$popupText =
			Loc::getMessage('DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_TEXT') .
			implode('<br/>', $listNonSupportedFeatures) . '<br/>';
	}

	$APPLICATION->SetTitle(GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_TITLE'));

	$repeatMigrate = (Option::get('sale', 'process_discount_migrator', '-') !== '-');

	$aTabs = array(
		array(
			'DIV'   => 'edit1',
			'TAB'   => GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB'),
			'ICON'  => 'main_user_edit',
			'CONTENT' => '
				<div style="width: 180px; float: left;">
					<img width="160" height="160" src="/bitrix/images/sale/discount/wizard.png" alt="">
				</div>
				<div>' .
				($repeatMigrate
					? GetMessage('DISCOUNT_CATALOG_MIGRATOR_PAGE_REPEAT_HELLO_TEXT')
					: GetMessage('DISCOUNT_CATALOG_MIGRATOR_PAGE_HELLO_TEXT')
				).
				'</div>'
		)
	);

	$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

	?>
	<style type="text/css">
		.bx-discount-catalog-help-btn {
			background:url("/bitrix/panel/main/images/bx-admin-sprite.png") no-repeat 4px -88px;
			color:#e3ecee;
			cursor:pointer;
			display:inline-block;
			line-height:14px;
			height:24px;
			margin-right:15px;
			padding:5px 0 0 30px;
			text-decoration: none;
		}

		.bx-discount-catalog-help-btn:hover {
			background:url("/bitrix/panel/main/images/bx-admin-sprite.png") no-repeat 4px -88px;
		}
	</style>
	<script type="text/javascript">
	var wd_stop;
	var wd_dialog;

	function ShowConvert()
	{
	<?php
	if ($repeatMigrate)
	{
		?>
		if (!confirm('<?php echo CUtil::JSEscape(GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONFIRM_MESSAGE')); ?>'))
		{
			return;
		}
		<?php
	}
	?>
		var dialog = new BX.CDialog({
			title: '<?= GetMessageJS('DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB_TITLE') ?>',
			width: 450,
			heght: 400,
			buttons: [
				<? if(!$listNonSupportedFeatures){ ?>
				{
					title: '<?= GetMessageJS('DISCOUNT_CATALOG_MIGRATOR_CONVERT_START_BUTTON')?>',
					id: 'run',
					name: 'run',
					className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
					action: function () {

						StartConvert();

						BX.cleanNode(this.parentWindow.PARAMS.content);
						this.parentWindow.Close();
					}
				},
				<? } ?>
				{
					title: BX.message('JS_CORE_WINDOW_CLOSE'),
					id: 'close',
					name: 'close',
					action: function () {
						BX.cleanNode(this.parentWindow.PARAMS.content);

						this.parentWindow.Close();
					}
				}
			],
			content: '<div style="margin-bottom: 10px;"><b></b></div><?= CUtil::JSEscape($popupText) ?><br/>'
		});

		dialog.SetSize({width: 700, height: 430+<?= $isThereSaveDiscounts? 100:0 ?>});
		dialog.Show();

	}

	function RemoveBorder()
	{
		BX('bx-discount-catalog-need-choice').style.border = '';
	}

	function ShowNotice()
	{
		if(wd_dialog)
		{
			wd_dialog.close();
		}
		wd_dialog = new BX.PopupWindow('bx-discount-catalog-notice', BX('bx-discount-catalog-help-btn'), {
			closeByEsc: true,
			autoHide: true,
			buttons: [],
			zIndex: 10000,
			angle: {
				position: 'top',
				offset: 20
			},
			events: {
				onPopupClose: function(){
					this.destroy();
				}
			},
			content: '<div style="width: 400px;"><?= GetMessageJS('DISCOUNT_CATALOG_MIGRATOR_CONVERT_POPUP_NOTICE') ?></div>'
		});
		wd_dialog.show();
	}

	function StartConvert()
	{
		wd_stop = false;
		document.getElementById('convert_result_div').innerHTML = '';
		document.getElementById('start_button').disabled        = true;
		DoNext(0, {});
		BX.remove(BX('tabControl_layout'));
	}

	function StopConvert()
	{
		wd_stop = true;
		document.getElementById('start_button').disabled = false;
	}

	function EndConvert()
	{
		wd_stop = true;
		if(document.getElementById('start_button'))
		{
			document.getElementById('start_button').disabled = true;
		}
		BX.remove(BX('tabControl_layout'));
	}

	function DoNext(processedSummary, options)
	{
		options = options || {};
		var queryString = 'migrator_process=Y&lang=<?php echo htmlspecialcharsbx(LANGUAGE_ID); ?>';

		queryString += '&<?php echo bitrix_sessid_get(); ?>';
		queryString += '&processedSummary=' + parseInt(processedSummary);

		if ( ! wd_stop )
		{
			ShowWaitWindow();
			BX.ajax.post(
				'sale_discount_catalog_migrator.php?' + queryString,
				{},
				function(result)
				{
					document.getElementById('convert_result_div').innerHTML = result;
					if (BX('wd_convert_finish') != null)
					{
						CloseWaitWindow();
						EndConvert();
					}
				}
			);
		}

		return false;
	}
	</script>

	<div id='convert_result_div'>
	</div>

	<form method='POST' action='<?php
		echo $APPLICATION->GetCurPage(); ?>?lang=<?php
		echo htmlspecialcharsbx(LANGUAGE_ID);
	?>'>
		<?php
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		$tabControl->Buttons();
		?>

		<input type='button' id='start_button'
			value='<?php echo GetMessage('DISCOUNT_CATALOG_MIGRATOR_CONVERT_START_BUTTON')?>'
			onclick='ShowConvert();');>
		<?php
		$tabControl->End();
		?>
	</form>

	<?php
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
}