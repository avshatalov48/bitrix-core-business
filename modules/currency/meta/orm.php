<?php

/* ORMENTITYANNOTATION:Bitrix\Currency\CurrencyTable:currency\lib\currency.php */
namespace Bitrix\Currency {
	/**
	 * EO_Currency
	 * @see \Bitrix\Currency\CurrencyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCurrency()
	 * @method \Bitrix\Currency\EO_Currency setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \int getAmountCnt()
	 * @method \Bitrix\Currency\EO_Currency setAmountCnt(\int|\Bitrix\Main\DB\SqlExpression $amountCnt)
	 * @method bool hasAmountCnt()
	 * @method bool isAmountCntFilled()
	 * @method bool isAmountCntChanged()
	 * @method \int remindActualAmountCnt()
	 * @method \int requireAmountCnt()
	 * @method \Bitrix\Currency\EO_Currency resetAmountCnt()
	 * @method \Bitrix\Currency\EO_Currency unsetAmountCnt()
	 * @method \int fillAmountCnt()
	 * @method \float getAmount()
	 * @method \Bitrix\Currency\EO_Currency setAmount(\float|\Bitrix\Main\DB\SqlExpression $amount)
	 * @method bool hasAmount()
	 * @method bool isAmountFilled()
	 * @method bool isAmountChanged()
	 * @method \float remindActualAmount()
	 * @method \float requireAmount()
	 * @method \Bitrix\Currency\EO_Currency resetAmount()
	 * @method \Bitrix\Currency\EO_Currency unsetAmount()
	 * @method \float fillAmount()
	 * @method \int getSort()
	 * @method \Bitrix\Currency\EO_Currency setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Currency\EO_Currency resetSort()
	 * @method \Bitrix\Currency\EO_Currency unsetSort()
	 * @method \int fillSort()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Currency\EO_Currency setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Currency\EO_Currency resetDateUpdate()
	 * @method \Bitrix\Currency\EO_Currency unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getNumcode()
	 * @method \Bitrix\Currency\EO_Currency setNumcode(\string|\Bitrix\Main\DB\SqlExpression $numcode)
	 * @method bool hasNumcode()
	 * @method bool isNumcodeFilled()
	 * @method bool isNumcodeChanged()
	 * @method \string remindActualNumcode()
	 * @method \string requireNumcode()
	 * @method \Bitrix\Currency\EO_Currency resetNumcode()
	 * @method \Bitrix\Currency\EO_Currency unsetNumcode()
	 * @method \string fillNumcode()
	 * @method \boolean getBase()
	 * @method \Bitrix\Currency\EO_Currency setBase(\boolean|\Bitrix\Main\DB\SqlExpression $base)
	 * @method bool hasBase()
	 * @method bool isBaseFilled()
	 * @method bool isBaseChanged()
	 * @method \boolean remindActualBase()
	 * @method \boolean requireBase()
	 * @method \Bitrix\Currency\EO_Currency resetBase()
	 * @method \Bitrix\Currency\EO_Currency unsetBase()
	 * @method \boolean fillBase()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Currency\EO_Currency setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Currency\EO_Currency resetCreatedBy()
	 * @method \Bitrix\Currency\EO_Currency unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Currency\EO_Currency setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Currency\EO_Currency resetDateCreate()
	 * @method \Bitrix\Currency\EO_Currency unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Currency\EO_Currency setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Currency\EO_Currency resetModifiedBy()
	 * @method \Bitrix\Currency\EO_Currency unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \float getCurrentBaseRate()
	 * @method \Bitrix\Currency\EO_Currency setCurrentBaseRate(\float|\Bitrix\Main\DB\SqlExpression $currentBaseRate)
	 * @method bool hasCurrentBaseRate()
	 * @method bool isCurrentBaseRateFilled()
	 * @method bool isCurrentBaseRateChanged()
	 * @method \float remindActualCurrentBaseRate()
	 * @method \float requireCurrentBaseRate()
	 * @method \Bitrix\Currency\EO_Currency resetCurrentBaseRate()
	 * @method \Bitrix\Currency\EO_Currency unsetCurrentBaseRate()
	 * @method \float fillCurrentBaseRate()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Currency\EO_Currency setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_Currency resetCreatedByUser()
	 * @method \Bitrix\Currency\EO_Currency unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Currency\EO_Currency setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_Currency resetModifiedByUser()
	 * @method \Bitrix\Currency\EO_Currency unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang getLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang remindActualLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang requireLangFormat()
	 * @method \Bitrix\Currency\EO_Currency setLangFormat(\Bitrix\Currency\EO_CurrencyLang $object)
	 * @method \Bitrix\Currency\EO_Currency resetLangFormat()
	 * @method \Bitrix\Currency\EO_Currency unsetLangFormat()
	 * @method bool hasLangFormat()
	 * @method bool isLangFormatFilled()
	 * @method bool isLangFormatChanged()
	 * @method \Bitrix\Currency\EO_CurrencyLang fillLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang getCurrentLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang remindActualCurrentLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang requireCurrentLangFormat()
	 * @method \Bitrix\Currency\EO_Currency setCurrentLangFormat(\Bitrix\Currency\EO_CurrencyLang $object)
	 * @method \Bitrix\Currency\EO_Currency resetCurrentLangFormat()
	 * @method \Bitrix\Currency\EO_Currency unsetCurrentLangFormat()
	 * @method bool hasCurrentLangFormat()
	 * @method bool isCurrentLangFormatFilled()
	 * @method bool isCurrentLangFormatChanged()
	 * @method \Bitrix\Currency\EO_CurrencyLang fillCurrentLangFormat()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Currency\EO_Currency set($fieldName, $value)
	 * @method \Bitrix\Currency\EO_Currency reset($fieldName)
	 * @method \Bitrix\Currency\EO_Currency unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Currency\EO_Currency wakeUp($data)
	 */
	class EO_Currency {
		/* @var \Bitrix\Currency\CurrencyTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Currency {
	/**
	 * EO_Currency_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCurrencyList()
	 * @method \int[] getAmountCntList()
	 * @method \int[] fillAmountCnt()
	 * @method \float[] getAmountList()
	 * @method \float[] fillAmount()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getNumcodeList()
	 * @method \string[] fillNumcode()
	 * @method \boolean[] getBaseList()
	 * @method \boolean[] fillBase()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \float[] getCurrentBaseRateList()
	 * @method \float[] fillCurrentBaseRate()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Currency\EO_Currency_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Currency\EO_Currency_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang[] getLangFormatList()
	 * @method \Bitrix\Currency\EO_Currency_Collection getLangFormatCollection()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection fillLangFormat()
	 * @method \Bitrix\Currency\EO_CurrencyLang[] getCurrentLangFormatList()
	 * @method \Bitrix\Currency\EO_Currency_Collection getCurrentLangFormatCollection()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection fillCurrentLangFormat()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Currency\EO_Currency $object)
	 * @method bool has(\Bitrix\Currency\EO_Currency $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Currency\EO_Currency getByPrimary($primary)
	 * @method \Bitrix\Currency\EO_Currency[] getAll()
	 * @method bool remove(\Bitrix\Currency\EO_Currency $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Currency\EO_Currency_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Currency\EO_Currency current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Currency_Collection merge(?EO_Currency_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Currency_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Currency\CurrencyTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyTable';
	}
}
namespace Bitrix\Currency {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Currency_Result exec()
	 * @method \Bitrix\Currency\EO_Currency fetchObject()
	 * @method \Bitrix\Currency\EO_Currency_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Currency_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Currency\EO_Currency fetchObject()
	 * @method \Bitrix\Currency\EO_Currency_Collection fetchCollection()
	 */
	class EO_Currency_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Currency\EO_Currency createObject($setDefaultValues = true)
	 * @method \Bitrix\Currency\EO_Currency_Collection createCollection()
	 * @method \Bitrix\Currency\EO_Currency wakeUpObject($row)
	 * @method \Bitrix\Currency\EO_Currency_Collection wakeUpCollection($rows)
	 */
	class EO_Currency_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Currency\CurrencyLangTable:currency\lib\currencylang.php */
namespace Bitrix\Currency {
	/**
	 * EO_CurrencyLang
	 * @see \Bitrix\Currency\CurrencyLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCurrency()
	 * @method \Bitrix\Currency\EO_CurrencyLang setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string getLid()
	 * @method \Bitrix\Currency\EO_CurrencyLang setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string getFormatString()
	 * @method \Bitrix\Currency\EO_CurrencyLang setFormatString(\string|\Bitrix\Main\DB\SqlExpression $formatString)
	 * @method bool hasFormatString()
	 * @method bool isFormatStringFilled()
	 * @method bool isFormatStringChanged()
	 * @method \string remindActualFormatString()
	 * @method \string requireFormatString()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetFormatString()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetFormatString()
	 * @method \string fillFormatString()
	 * @method \string getFullName()
	 * @method \Bitrix\Currency\EO_CurrencyLang setFullName(\string|\Bitrix\Main\DB\SqlExpression $fullName)
	 * @method bool hasFullName()
	 * @method bool isFullNameFilled()
	 * @method bool isFullNameChanged()
	 * @method \string remindActualFullName()
	 * @method \string requireFullName()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetFullName()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetFullName()
	 * @method \string fillFullName()
	 * @method \string getDecPoint()
	 * @method \Bitrix\Currency\EO_CurrencyLang setDecPoint(\string|\Bitrix\Main\DB\SqlExpression $decPoint)
	 * @method bool hasDecPoint()
	 * @method bool isDecPointFilled()
	 * @method bool isDecPointChanged()
	 * @method \string remindActualDecPoint()
	 * @method \string requireDecPoint()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetDecPoint()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetDecPoint()
	 * @method \string fillDecPoint()
	 * @method \string getThousandsSep()
	 * @method \Bitrix\Currency\EO_CurrencyLang setThousandsSep(\string|\Bitrix\Main\DB\SqlExpression $thousandsSep)
	 * @method bool hasThousandsSep()
	 * @method bool isThousandsSepFilled()
	 * @method bool isThousandsSepChanged()
	 * @method \string remindActualThousandsSep()
	 * @method \string requireThousandsSep()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetThousandsSep()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetThousandsSep()
	 * @method \string fillThousandsSep()
	 * @method \int getDecimals()
	 * @method \Bitrix\Currency\EO_CurrencyLang setDecimals(\int|\Bitrix\Main\DB\SqlExpression $decimals)
	 * @method bool hasDecimals()
	 * @method bool isDecimalsFilled()
	 * @method bool isDecimalsChanged()
	 * @method \int remindActualDecimals()
	 * @method \int requireDecimals()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetDecimals()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetDecimals()
	 * @method \int fillDecimals()
	 * @method \string getThousandsVariant()
	 * @method \Bitrix\Currency\EO_CurrencyLang setThousandsVariant(\string|\Bitrix\Main\DB\SqlExpression $thousandsVariant)
	 * @method bool hasThousandsVariant()
	 * @method bool isThousandsVariantFilled()
	 * @method bool isThousandsVariantChanged()
	 * @method \string remindActualThousandsVariant()
	 * @method \string requireThousandsVariant()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetThousandsVariant()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetThousandsVariant()
	 * @method \string fillThousandsVariant()
	 * @method \boolean getHideZero()
	 * @method \Bitrix\Currency\EO_CurrencyLang setHideZero(\boolean|\Bitrix\Main\DB\SqlExpression $hideZero)
	 * @method bool hasHideZero()
	 * @method bool isHideZeroFilled()
	 * @method bool isHideZeroChanged()
	 * @method \boolean remindActualHideZero()
	 * @method \boolean requireHideZero()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetHideZero()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetHideZero()
	 * @method \boolean fillHideZero()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyLang setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyLang setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyLang resetTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_CurrencyLang resetCreatedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_CurrencyLang resetModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Main\Localization\EO_Language getLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language remindActualLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language requireLanguage()
	 * @method \Bitrix\Currency\EO_CurrencyLang setLanguage(\Bitrix\Main\Localization\EO_Language $object)
	 * @method \Bitrix\Currency\EO_CurrencyLang resetLanguage()
	 * @method \Bitrix\Currency\EO_CurrencyLang unsetLanguage()
	 * @method bool hasLanguage()
	 * @method bool isLanguageFilled()
	 * @method bool isLanguageChanged()
	 * @method \Bitrix\Main\Localization\EO_Language fillLanguage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Currency\EO_CurrencyLang set($fieldName, $value)
	 * @method \Bitrix\Currency\EO_CurrencyLang reset($fieldName)
	 * @method \Bitrix\Currency\EO_CurrencyLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Currency\EO_CurrencyLang wakeUp($data)
	 */
	class EO_CurrencyLang {
		/* @var \Bitrix\Currency\CurrencyLangTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Currency {
	/**
	 * EO_CurrencyLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCurrencyList()
	 * @method \string[] getLidList()
	 * @method \string[] getFormatStringList()
	 * @method \string[] fillFormatString()
	 * @method \string[] getFullNameList()
	 * @method \string[] fillFullName()
	 * @method \string[] getDecPointList()
	 * @method \string[] fillDecPoint()
	 * @method \string[] getThousandsSepList()
	 * @method \string[] fillThousandsSep()
	 * @method \int[] getDecimalsList()
	 * @method \int[] fillDecimals()
	 * @method \string[] getThousandsVariantList()
	 * @method \string[] fillThousandsVariant()
	 * @method \boolean[] getHideZeroList()
	 * @method \boolean[] fillHideZero()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Main\Localization\EO_Language[] getLanguageList()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection getLanguageCollection()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fillLanguage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Currency\EO_CurrencyLang $object)
	 * @method bool has(\Bitrix\Currency\EO_CurrencyLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Currency\EO_CurrencyLang getByPrimary($primary)
	 * @method \Bitrix\Currency\EO_CurrencyLang[] getAll()
	 * @method bool remove(\Bitrix\Currency\EO_CurrencyLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Currency\EO_CurrencyLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Currency\EO_CurrencyLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_CurrencyLang_Collection merge(?EO_CurrencyLang_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_CurrencyLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Currency\CurrencyLangTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyLangTable';
	}
}
namespace Bitrix\Currency {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CurrencyLang_Result exec()
	 * @method \Bitrix\Currency\EO_CurrencyLang fetchObject()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CurrencyLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Currency\EO_CurrencyLang fetchObject()
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection fetchCollection()
	 */
	class EO_CurrencyLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Currency\EO_CurrencyLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection createCollection()
	 * @method \Bitrix\Currency\EO_CurrencyLang wakeUpObject($row)
	 * @method \Bitrix\Currency\EO_CurrencyLang_Collection wakeUpCollection($rows)
	 */
	class EO_CurrencyLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Currency\CurrencyRateTable:currency\lib\currencyrate.php */
namespace Bitrix\Currency {
	/**
	 * EO_CurrencyRate
	 * @see \Bitrix\Currency\CurrencyRateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Currency\EO_CurrencyRate setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCurrency()
	 * @method \Bitrix\Currency\EO_CurrencyRate setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string getBaseCurrency()
	 * @method \Bitrix\Currency\EO_CurrencyRate setBaseCurrency(\string|\Bitrix\Main\DB\SqlExpression $baseCurrency)
	 * @method bool hasBaseCurrency()
	 * @method bool isBaseCurrencyFilled()
	 * @method bool isBaseCurrencyChanged()
	 * @method \Bitrix\Main\Type\Date getDateRate()
	 * @method \Bitrix\Currency\EO_CurrencyRate setDateRate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $dateRate)
	 * @method bool hasDateRate()
	 * @method bool isDateRateFilled()
	 * @method bool isDateRateChanged()
	 * @method \int getRateCnt()
	 * @method \Bitrix\Currency\EO_CurrencyRate setRateCnt(\int|\Bitrix\Main\DB\SqlExpression $rateCnt)
	 * @method bool hasRateCnt()
	 * @method bool isRateCntFilled()
	 * @method bool isRateCntChanged()
	 * @method \int remindActualRateCnt()
	 * @method \int requireRateCnt()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetRateCnt()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetRateCnt()
	 * @method \int fillRateCnt()
	 * @method \float getRate()
	 * @method \Bitrix\Currency\EO_CurrencyRate setRate(\float|\Bitrix\Main\DB\SqlExpression $rate)
	 * @method bool hasRate()
	 * @method bool isRateFilled()
	 * @method bool isRateChanged()
	 * @method \float remindActualRate()
	 * @method \float requireRate()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetRate()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetRate()
	 * @method \float fillRate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetCreatedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyRate setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetDateCreate()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetModifiedBy()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyRate setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyRate resetTimestampX()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyRate setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_CurrencyRate resetCreatedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyRate setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Currency\EO_CurrencyRate resetModifiedByUser()
	 * @method \Bitrix\Currency\EO_CurrencyRate unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Currency\EO_CurrencyRate set($fieldName, $value)
	 * @method \Bitrix\Currency\EO_CurrencyRate reset($fieldName)
	 * @method \Bitrix\Currency\EO_CurrencyRate unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Currency\EO_CurrencyRate wakeUp($data)
	 */
	class EO_CurrencyRate {
		/* @var \Bitrix\Currency\CurrencyRateTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyRateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Currency {
	/**
	 * EO_CurrencyRate_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCurrencyList()
	 * @method \string[] getBaseCurrencyList()
	 * @method \Bitrix\Main\Type\Date[] getDateRateList()
	 * @method \int[] getRateCntList()
	 * @method \int[] fillRateCnt()
	 * @method \float[] getRateList()
	 * @method \float[] fillRate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Currency\EO_CurrencyRate $object)
	 * @method bool has(\Bitrix\Currency\EO_CurrencyRate $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Currency\EO_CurrencyRate getByPrimary($primary)
	 * @method \Bitrix\Currency\EO_CurrencyRate[] getAll()
	 * @method bool remove(\Bitrix\Currency\EO_CurrencyRate $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Currency\EO_CurrencyRate_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Currency\EO_CurrencyRate current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_CurrencyRate_Collection merge(?EO_CurrencyRate_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_CurrencyRate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Currency\CurrencyRateTable */
		static public $dataClass = '\Bitrix\Currency\CurrencyRateTable';
	}
}
namespace Bitrix\Currency {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CurrencyRate_Result exec()
	 * @method \Bitrix\Currency\EO_CurrencyRate fetchObject()
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CurrencyRate_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Currency\EO_CurrencyRate fetchObject()
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection fetchCollection()
	 */
	class EO_CurrencyRate_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Currency\EO_CurrencyRate createObject($setDefaultValues = true)
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection createCollection()
	 * @method \Bitrix\Currency\EO_CurrencyRate wakeUpObject($row)
	 * @method \Bitrix\Currency\EO_CurrencyRate_Collection wakeUpCollection($rows)
	 */
	class EO_CurrencyRate_Entity extends \Bitrix\Main\ORM\Entity {}
}