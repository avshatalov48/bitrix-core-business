<?
use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);
/**
 * Class CCatalogMeasureAll
 */
class CCatalogMeasureAll
{
	const DEFAULT_MEASURE_CODE = 796;

	protected static $defaultMeasure = null;

	/**
	 * @param string $action
	 * @param array $arFields
	 * @param int $id
	 * @return bool
	 */
	protected static function checkFields($action, &$arFields, $id = 0)
	{
		global $APPLICATION;

		$action = strtoupper($action);
		if ($action != 'ADD' && $action != 'UPDATE')
			return false;
		$id = (int)$id;
		if ($action == 'UPDATE' && $id <= 0)
			return false;

		if (!isset($arFields['SYMBOL']) && isset($arFields['SYMBOL_RUS']))
		{
			$arFields['SYMBOL'] = $arFields['SYMBOL_RUS'];
			unset($arFields['SYMBOL_RUS']);
		}
		$whiteList = array(
			'CODE' => true,
			'MEASURE_TITLE' => true,
			'SYMBOL' => true,
			'SYMBOL_INTL' => true,
			'SYMBOL_LETTER_INTL' => true,
			'IS_DEFAULT' => true
		);

		$arFields = array_intersect_key($arFields, $whiteList);

		if (array_key_exists('CODE', $arFields))
		{
			$code = trim($arFields['CODE']);
			if ($code === '')
			{
				$APPLICATION->ThrowException(Loc::getMessage('CAT_MEASURE_ERR_CODE_IS_ABSENT'));
				return false;
			}
			elseif(preg_match('/^[0-9]+$/', $code) !== 1)
			{
				$APPLICATION->ThrowException(Loc::getMessage('CAT_MEASURE_ERR_CODE_IS_BAD'));
				return false;
			}
			else
			{
				$arFields['CODE'] = (int)$code;
			}
		}

		$cnt = 0;
		switch ($action)
		{
			case 'ADD':
				if (!isset($arFields['CODE']))
					return false;
				$cnt = CCatalogMeasure::getList(array(), array("CODE" => $arFields['CODE']), array());
				break;
			case 'UPDATE':
				if (isset($arFields['CODE']))
					$cnt = CCatalogMeasure::getList(array(), array("CODE" => $arFields['CODE'], '!ID' => $id), array(), false, array('ID'));
				break;
		}
		if ($cnt > 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('CAT_MEASURE_ERR_CODE_ALREADY_EXISTS'));
			return false;
		}

		if (isset($arFields["IS_DEFAULT"]) && $arFields["IS_DEFAULT"] == 'Y')
		{
			$filter = array('=IS_DEFAULT' => 'Y');
			if ($action == 'UPDATE')
				$filter['!=ID'] = $id;
			$iterator = Catalog\MeasureTable::getList(array(
				'select' => array('ID'),
				'filter' => $filter
			));
			while ($row = $iterator->fetch())
			{
				$result = Catalog\MeasureTable::update((int)$row['ID'], array('IS_DEFAULT' => 'N'));
				if (!$result->isSuccess())
					return false;
			}
			unset($result, $row, $iterator);
		}

		return true;
	}

	/**
	 * @deprecated deprecated since catalog 17.5.12
	 * @see \Bitrix\Catalog\MeasureTable:add
	 *
	 * @param array $arFields
	 * @return bool|int
	 */
	public static function add($arFields)
	{
		if (!static::checkFields('ADD', $arFields))
			return false;

		if (empty($arFields))
			return false;

		$id = false;
		$result = Catalog\MeasureTable::add($arFields);
		$success = $result->isSuccess();
		if (!$success)
			self::convertErrors($result);
		else
			$id = (int)$result->getId();
		unset($success, $result);

		return $id;
	}

	/**
	 * @deprecated deprecated since catalog 17.5.12
	 * @see \Bitrix\Catalog\MeasureTable:update
	 *
	 * @param int $id
	 * @param array $arFields
	 * @return bool|int
	 */
	public static function update($id, $arFields)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;
		if (!static::checkFields('UPDATE', $arFields, $id))
			return false;

		if (empty($arFields))
			return $id;

		$result = Catalog\MeasureTable::update($id, $arFields);
		$success = $result->isSuccess();
		if (!$success)
			self::convertErrors($result);
		unset($result);

		return ($success ? $id : false);
	}

	/**
	 * @deprecated deprecated since catalog 17.5.12
	 * @see \Bitrix\Catalog\MeasureTable:delete
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function delete($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;

		$result = Catalog\MeasureTable::delete($id);
		$success = $result->isSuccess();
		if (!$success)
			self::convertErrors($result);
		unset($result);

		return $success;
	}

	public static function getDefaultMeasure($getStub = false, $getExt = false)
	{
		$getStub = ($getStub === true);
		$getExt = ($getExt === true);

		if (self::$defaultMeasure === null)
		{
			$measureRes = CCatalogMeasure::getList(
				array(),
				array('IS_DEFAULT' => 'Y'),
				false,
				false,
				array()
			);
			if ($measure = $measureRes->GetNext(true, $getExt))
			{
				$measure['ID'] = (int)$measure['ID'];
				$measure['CODE'] = (int)$measure['CODE'];
				self::$defaultMeasure = $measure;
			}
		}
		if (self::$defaultMeasure === null)
		{
			$measureRes = CCatalogMeasure::getList(
				array(),
				array('CODE' => self::DEFAULT_MEASURE_CODE),
				false,
				false,
				array()
			);
			if ($measure = $measureRes->GetNext(true, $getExt))
			{
				$measure['ID'] = (int)$measure['ID'];
				$measure['CODE'] = (int)$measure['CODE'];
				self::$defaultMeasure = $measure;
			}
		}
		if (self::$defaultMeasure === null)
		{
			if ($getStub)
			{
				$defaultMeasureDescription = CCatalogMeasureClassifier::getMeasureInfoByCode(self::DEFAULT_MEASURE_CODE);
				if ($defaultMeasureDescription !== null)
				{
					self::$defaultMeasure = array(
						'ID' => 0,
						'CODE' => self::DEFAULT_MEASURE_CODE,
						'MEASURE_TITLE' => htmlspecialcharsEx($defaultMeasureDescription['MEASURE_TITLE']),
						'SYMBOL_RUS' => htmlspecialcharsEx($defaultMeasureDescription['SYMBOL_RUS']),
						'SYMBOL' => htmlspecialcharsEx($defaultMeasureDescription['SYMBOL_RUS']),
						'SYMBOL_INTL' => htmlspecialcharsEx($defaultMeasureDescription['SYMBOL_INTL']),
						'SYMBOL_LETTER_INTL' => htmlspecialcharsEx($defaultMeasureDescription['SYMBOL_LETTER_INTL']),
						'IS_DEFAULT' => 'Y'
					);
					if ($getExt)
					{
						self::$defaultMeasure['~ID'] = '0';
						self::$defaultMeasure['~CODE'] = (string)self::DEFAULT_MEASURE_CODE;
						self::$defaultMeasure['~MEASURE_TITLE'] = $defaultMeasureDescription['MEASURE_TITLE'];
						self::$defaultMeasure['~SYMBOL_RUS'] = $defaultMeasureDescription['SYMBOL_RUS'];
						self::$defaultMeasure['~SYMBOL'] = $defaultMeasureDescription['SYMBOL_RUS'];
						self::$defaultMeasure['~SYMBOL_INTL'] = $defaultMeasureDescription['SYMBOL_INTL'];
						self::$defaultMeasure['~SYMBOL_LETTER_INTL'] = $defaultMeasureDescription['SYMBOL_LETTER_INTL'];
						self::$defaultMeasure['~IS_DEFAULT'] = 'Y';
					}
				}
			}
		}
		return self::$defaultMeasure;
	}

	private static function convertErrors(Main\Entity\Result $result)
	{
		global $APPLICATION;

		$oldMessages = array();
		foreach ($result->getErrorMessages() as $errorText)
			$oldMessages[] = array('text' => $errorText);
		unset($errorText);

		if (!empty($oldMessages))
		{
			$error = new CAdminException($oldMessages);
			$APPLICATION->ThrowException($error);
			unset($error);
		}
		unset($oldMessages);
	}
}

/**
 * Class CCatalogMeasureResult
 */
class CCatalogMeasureResult extends CDBResult
{
	/**
	 * @param $res
	 */
	public function __construct($res)
	{
		parent::__construct($res);
	}

	/**
	 * @return array
	 */
	function Fetch()
	{
		$res = parent::Fetch();
		if (!empty($res) && isset($res['CODE']))
		{
			if (array_key_exists('MEASURE_TITLE', $res) && $res["MEASURE_TITLE"] == '')
			{
				$tmpTitle = CCatalogMeasureClassifier::getMeasureTitle($res["CODE"], 'MEASURE_TITLE');
				$res["MEASURE_TITLE"] = ($tmpTitle == '') ? $res["SYMBOL_INTL"] : $tmpTitle;
			}
			if (array_key_exists('SYMBOL_RUS', $res) && $res["SYMBOL_RUS"] == '')
			{
				$tmpSymbol = CCatalogMeasureClassifier::getMeasureTitle($res["CODE"], 'SYMBOL_RUS');
				$res["SYMBOL_RUS"] = ($tmpSymbol == '') ? $res["SYMBOL_INTL"] : $tmpSymbol;
			}
			if (array_key_exists('SYMBOL', $res) && $res['SYMBOL'] == '')
			{
				$tmpSymbol = CCatalogMeasureClassifier::getMeasureTitle($res["CODE"], 'SYMBOL_RUS');
				$res["SYMBOL"] = ($tmpSymbol == '') ? $res["SYMBOL_INTL"] : $tmpSymbol;
			}
		}
		return $res;
	}
}