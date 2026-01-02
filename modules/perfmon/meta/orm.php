<?php

/* ORMENTITYANNOTATION:Bitrix\Perfmon\Model\CacheHitrateTable:perfmon/lib/model/cachehitratetable.php */
namespace Bitrix\Perfmon\Model {
	/**
	 * EO_CacheHitrate
	 * @see \Bitrix\Perfmon\Model\CacheHitrateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getHash()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setHash(\string|\Bitrix\Main\DB\SqlExpression $hash)
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \string remindActualHash()
	 * @method \string requireHash()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetHash()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetHash()
	 * @method \string fillHash()
	 * @method \int getCacheSize()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setCacheSize(\int|\Bitrix\Main\DB\SqlExpression $cacheSize)
	 * @method bool hasCacheSize()
	 * @method bool isCacheSizeFilled()
	 * @method bool isCacheSizeChanged()
	 * @method \int remindActualCacheSize()
	 * @method \int requireCacheSize()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetCacheSize()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetCacheSize()
	 * @method \int fillCacheSize()
	 * @method \string getModuleId()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetModuleId()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getBaseDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setBaseDir(\string|\Bitrix\Main\DB\SqlExpression $baseDir)
	 * @method bool hasBaseDir()
	 * @method bool isBaseDirFilled()
	 * @method bool isBaseDirChanged()
	 * @method \string remindActualBaseDir()
	 * @method \string requireBaseDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetBaseDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetBaseDir()
	 * @method \string fillBaseDir()
	 * @method \string getInitDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setInitDir(\string|\Bitrix\Main\DB\SqlExpression $initDir)
	 * @method bool hasInitDir()
	 * @method bool isInitDirFilled()
	 * @method bool isInitDirChanged()
	 * @method \string remindActualInitDir()
	 * @method \string requireInitDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetInitDir()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetInitDir()
	 * @method \string fillInitDir()
	 * @method \string getFileName()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setFileName(\string|\Bitrix\Main\DB\SqlExpression $fileName)
	 * @method bool hasFileName()
	 * @method bool isFileNameFilled()
	 * @method bool isFileNameChanged()
	 * @method \string remindActualFileName()
	 * @method \string requireFileName()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetFileName()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetFileName()
	 * @method \string fillFileName()
	 * @method \int getReadCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setReadCount(\int|\Bitrix\Main\DB\SqlExpression $readCount)
	 * @method bool hasReadCount()
	 * @method bool isReadCountFilled()
	 * @method bool isReadCountChanged()
	 * @method \int remindActualReadCount()
	 * @method \int requireReadCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetReadCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetReadCount()
	 * @method \int fillReadCount()
	 * @method \int getWriteCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setWriteCount(\int|\Bitrix\Main\DB\SqlExpression $writeCount)
	 * @method bool hasWriteCount()
	 * @method bool isWriteCountFilled()
	 * @method bool isWriteCountChanged()
	 * @method \int remindActualWriteCount()
	 * @method \int requireWriteCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetWriteCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetWriteCount()
	 * @method \int fillWriteCount()
	 * @method \int getCleanCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setCleanCount(\int|\Bitrix\Main\DB\SqlExpression $cleanCount)
	 * @method bool hasCleanCount()
	 * @method bool isCleanCountFilled()
	 * @method bool isCleanCountChanged()
	 * @method \int remindActualCleanCount()
	 * @method \int requireCleanCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetCleanCount()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetCleanCount()
	 * @method \int fillCleanCount()
	 * @method \float getRate()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate setRate(\float|\Bitrix\Main\DB\SqlExpression $rate)
	 * @method bool hasRate()
	 * @method bool isRateFilled()
	 * @method bool isRateChanged()
	 * @method \float remindActualRate()
	 * @method \float requireRate()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate resetRate()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unsetRate()
	 * @method \float fillRate()
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
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate set($fieldName, $value)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate reset($fieldName)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate wakeUp($data)
	 */
	class EO_CacheHitrate {
		/* @var \Bitrix\Perfmon\Model\CacheHitrateTable */
		static public $dataClass = '\Bitrix\Perfmon\Model\CacheHitrateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Perfmon\Model {
	/**
	 * EO_CacheHitrate_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getHashList()
	 * @method \string[] fillHash()
	 * @method \int[] getCacheSizeList()
	 * @method \int[] fillCacheSize()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getBaseDirList()
	 * @method \string[] fillBaseDir()
	 * @method \string[] getInitDirList()
	 * @method \string[] fillInitDir()
	 * @method \string[] getFileNameList()
	 * @method \string[] fillFileName()
	 * @method \int[] getReadCountList()
	 * @method \int[] fillReadCount()
	 * @method \int[] getWriteCountList()
	 * @method \int[] fillWriteCount()
	 * @method \int[] getCleanCountList()
	 * @method \int[] fillCleanCount()
	 * @method \float[] getRateList()
	 * @method \float[] fillRate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Perfmon\Model\EO_CacheHitrate $object)
	 * @method bool has(\Bitrix\Perfmon\Model\EO_CacheHitrate $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate getByPrimary($primary)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate[] getAll()
	 * @method bool remove(\Bitrix\Perfmon\Model\EO_CacheHitrate $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection merge(?\Bitrix\Perfmon\Model\EO_CacheHitrate_Collection $collection)
	 * @method bool isEmpty()
	 * @method array collectValues(int $valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, int $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL, bool $recursive = false)
	 */
	class EO_CacheHitrate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Perfmon\Model\CacheHitrateTable */
		static public $dataClass = '\Bitrix\Perfmon\Model\CacheHitrateTable';
	}
}
namespace Bitrix\Perfmon\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CacheHitrate_Result exec()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate fetchObject()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection fetchCollection()
	 */
	class EO_CacheHitrate_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate fetchObject()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection fetchCollection()
	 */
	class EO_CacheHitrate_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate createObject($setDefaultValues = true)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection createCollection()
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate wakeUpObject($row)
	 * @method \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection wakeUpCollection($rows)
	 */
	class EO_CacheHitrate_Entity extends \Bitrix\Main\ORM\Entity {}
}