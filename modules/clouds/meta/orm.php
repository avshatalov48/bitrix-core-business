<?php

/* ORMENTITYANNOTATION:Bitrix\Clouds\CopyQueueTable:clouds/lib/copyqueue.php:d7dd9335c99db06c388752ce9a4e2cd8 */
namespace Bitrix\Clouds {
	/**
	 * EO_CopyQueue
	 * @see \Bitrix\Clouds\CopyQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Clouds\EO_CopyQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Clouds\EO_CopyQueue setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetTimestampX()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getOp()
	 * @method \Bitrix\Clouds\EO_CopyQueue setOp(\string|\Bitrix\Main\DB\SqlExpression $op)
	 * @method bool hasOp()
	 * @method bool isOpFilled()
	 * @method bool isOpChanged()
	 * @method \string remindActualOp()
	 * @method \string requireOp()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetOp()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetOp()
	 * @method \string fillOp()
	 * @method \int getSourceBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue setSourceBucketId(\int|\Bitrix\Main\DB\SqlExpression $sourceBucketId)
	 * @method bool hasSourceBucketId()
	 * @method bool isSourceBucketIdFilled()
	 * @method bool isSourceBucketIdChanged()
	 * @method \int remindActualSourceBucketId()
	 * @method \int requireSourceBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetSourceBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetSourceBucketId()
	 * @method \int fillSourceBucketId()
	 * @method \string getSourceFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue setSourceFilePath(\string|\Bitrix\Main\DB\SqlExpression $sourceFilePath)
	 * @method bool hasSourceFilePath()
	 * @method bool isSourceFilePathFilled()
	 * @method bool isSourceFilePathChanged()
	 * @method \string remindActualSourceFilePath()
	 * @method \string requireSourceFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetSourceFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetSourceFilePath()
	 * @method \string fillSourceFilePath()
	 * @method \int getTargetBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue setTargetBucketId(\int|\Bitrix\Main\DB\SqlExpression $targetBucketId)
	 * @method bool hasTargetBucketId()
	 * @method bool isTargetBucketIdFilled()
	 * @method bool isTargetBucketIdChanged()
	 * @method \int remindActualTargetBucketId()
	 * @method \int requireTargetBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetTargetBucketId()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetTargetBucketId()
	 * @method \int fillTargetBucketId()
	 * @method \string getTargetFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue setTargetFilePath(\string|\Bitrix\Main\DB\SqlExpression $targetFilePath)
	 * @method bool hasTargetFilePath()
	 * @method bool isTargetFilePathFilled()
	 * @method bool isTargetFilePathChanged()
	 * @method \string remindActualTargetFilePath()
	 * @method \string requireTargetFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetTargetFilePath()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetTargetFilePath()
	 * @method \string fillTargetFilePath()
	 * @method \int getFileSize()
	 * @method \Bitrix\Clouds\EO_CopyQueue setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetFileSize()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \int getFilePos()
	 * @method \Bitrix\Clouds\EO_CopyQueue setFilePos(\int|\Bitrix\Main\DB\SqlExpression $filePos)
	 * @method bool hasFilePos()
	 * @method bool isFilePosFilled()
	 * @method bool isFilePosChanged()
	 * @method \int remindActualFilePos()
	 * @method \int requireFilePos()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetFilePos()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetFilePos()
	 * @method \int fillFilePos()
	 * @method \int getFailCounter()
	 * @method \Bitrix\Clouds\EO_CopyQueue setFailCounter(\int|\Bitrix\Main\DB\SqlExpression $failCounter)
	 * @method bool hasFailCounter()
	 * @method bool isFailCounterFilled()
	 * @method bool isFailCounterChanged()
	 * @method \int remindActualFailCounter()
	 * @method \int requireFailCounter()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetFailCounter()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetFailCounter()
	 * @method \int fillFailCounter()
	 * @method \string getStatus()
	 * @method \Bitrix\Clouds\EO_CopyQueue setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetStatus()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetStatus()
	 * @method \string fillStatus()
	 * @method \string getErrorMessage()
	 * @method \Bitrix\Clouds\EO_CopyQueue setErrorMessage(\string|\Bitrix\Main\DB\SqlExpression $errorMessage)
	 * @method bool hasErrorMessage()
	 * @method bool isErrorMessageFilled()
	 * @method bool isErrorMessageChanged()
	 * @method \string remindActualErrorMessage()
	 * @method \string requireErrorMessage()
	 * @method \Bitrix\Clouds\EO_CopyQueue resetErrorMessage()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetErrorMessage()
	 * @method \string fillErrorMessage()
	 * @method \Bitrix\Clouds\EO_FileBucket getSourceBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket remindActualSourceBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket requireSourceBucket()
	 * @method \Bitrix\Clouds\EO_CopyQueue setSourceBucket(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method \Bitrix\Clouds\EO_CopyQueue resetSourceBucket()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetSourceBucket()
	 * @method bool hasSourceBucket()
	 * @method bool isSourceBucketFilled()
	 * @method bool isSourceBucketChanged()
	 * @method \Bitrix\Clouds\EO_FileBucket fillSourceBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket getTargetBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket remindActualTargetBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket requireTargetBucket()
	 * @method \Bitrix\Clouds\EO_CopyQueue setTargetBucket(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method \Bitrix\Clouds\EO_CopyQueue resetTargetBucket()
	 * @method \Bitrix\Clouds\EO_CopyQueue unsetTargetBucket()
	 * @method bool hasTargetBucket()
	 * @method bool isTargetBucketFilled()
	 * @method bool isTargetBucketChanged()
	 * @method \Bitrix\Clouds\EO_FileBucket fillTargetBucket()
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
	 * @method \Bitrix\Clouds\EO_CopyQueue set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_CopyQueue reset($fieldName)
	 * @method \Bitrix\Clouds\EO_CopyQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_CopyQueue wakeUp($data)
	 */
	class EO_CopyQueue {
		/* @var \Bitrix\Clouds\CopyQueueTable */
		static public $dataClass = '\Bitrix\Clouds\CopyQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_CopyQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getOpList()
	 * @method \string[] fillOp()
	 * @method \int[] getSourceBucketIdList()
	 * @method \int[] fillSourceBucketId()
	 * @method \string[] getSourceFilePathList()
	 * @method \string[] fillSourceFilePath()
	 * @method \int[] getTargetBucketIdList()
	 * @method \int[] fillTargetBucketId()
	 * @method \string[] getTargetFilePathList()
	 * @method \string[] fillTargetFilePath()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \int[] getFilePosList()
	 * @method \int[] fillFilePos()
	 * @method \int[] getFailCounterList()
	 * @method \int[] fillFailCounter()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \string[] getErrorMessageList()
	 * @method \string[] fillErrorMessage()
	 * @method \Bitrix\Clouds\EO_FileBucket[] getSourceBucketList()
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection getSourceBucketCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fillSourceBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket[] getTargetBucketList()
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection getTargetBucketCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fillTargetBucket()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_CopyQueue $object)
	 * @method bool has(\Bitrix\Clouds\EO_CopyQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_CopyQueue getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_CopyQueue[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_CopyQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_CopyQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_CopyQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CopyQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\CopyQueueTable */
		static public $dataClass = '\Bitrix\Clouds\CopyQueueTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CopyQueue_Result exec()
	 * @method \Bitrix\Clouds\EO_CopyQueue fetchObject()
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CopyQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_CopyQueue fetchObject()
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection fetchCollection()
	 */
	class EO_CopyQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_CopyQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_CopyQueue wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_CopyQueue_Collection wakeUpCollection($rows)
	 */
	class EO_CopyQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\DeleteQueueTable:clouds/lib/deletequeue.php:caa32fb4bf1178fd850b0a1078c1bfaf */
namespace Bitrix\Clouds {
	/**
	 * EO_DeleteQueue
	 * @see \Bitrix\Clouds\DeleteQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Clouds\EO_DeleteQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Clouds\EO_DeleteQueue setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Clouds\EO_DeleteQueue resetTimestampX()
	 * @method \Bitrix\Clouds\EO_DeleteQueue unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getBucketId()
	 * @method \Bitrix\Clouds\EO_DeleteQueue setBucketId(\int|\Bitrix\Main\DB\SqlExpression $bucketId)
	 * @method bool hasBucketId()
	 * @method bool isBucketIdFilled()
	 * @method bool isBucketIdChanged()
	 * @method \int remindActualBucketId()
	 * @method \int requireBucketId()
	 * @method \Bitrix\Clouds\EO_DeleteQueue resetBucketId()
	 * @method \Bitrix\Clouds\EO_DeleteQueue unsetBucketId()
	 * @method \int fillBucketId()
	 * @method \string getFilePath()
	 * @method \Bitrix\Clouds\EO_DeleteQueue setFilePath(\string|\Bitrix\Main\DB\SqlExpression $filePath)
	 * @method bool hasFilePath()
	 * @method bool isFilePathFilled()
	 * @method bool isFilePathChanged()
	 * @method \string remindActualFilePath()
	 * @method \string requireFilePath()
	 * @method \Bitrix\Clouds\EO_DeleteQueue resetFilePath()
	 * @method \Bitrix\Clouds\EO_DeleteQueue unsetFilePath()
	 * @method \string fillFilePath()
	 * @method \Bitrix\Clouds\EO_FileBucket getBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket remindActualBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket requireBucket()
	 * @method \Bitrix\Clouds\EO_DeleteQueue setBucket(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method \Bitrix\Clouds\EO_DeleteQueue resetBucket()
	 * @method \Bitrix\Clouds\EO_DeleteQueue unsetBucket()
	 * @method bool hasBucket()
	 * @method bool isBucketFilled()
	 * @method bool isBucketChanged()
	 * @method \Bitrix\Clouds\EO_FileBucket fillBucket()
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
	 * @method \Bitrix\Clouds\EO_DeleteQueue set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_DeleteQueue reset($fieldName)
	 * @method \Bitrix\Clouds\EO_DeleteQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_DeleteQueue wakeUp($data)
	 */
	class EO_DeleteQueue {
		/* @var \Bitrix\Clouds\DeleteQueueTable */
		static public $dataClass = '\Bitrix\Clouds\DeleteQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_DeleteQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getBucketIdList()
	 * @method \int[] fillBucketId()
	 * @method \string[] getFilePathList()
	 * @method \string[] fillFilePath()
	 * @method \Bitrix\Clouds\EO_FileBucket[] getBucketList()
	 * @method \Bitrix\Clouds\EO_DeleteQueue_Collection getBucketCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fillBucket()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_DeleteQueue $object)
	 * @method bool has(\Bitrix\Clouds\EO_DeleteQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_DeleteQueue getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_DeleteQueue[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_DeleteQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_DeleteQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_DeleteQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DeleteQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\DeleteQueueTable */
		static public $dataClass = '\Bitrix\Clouds\DeleteQueueTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DeleteQueue_Result exec()
	 * @method \Bitrix\Clouds\EO_DeleteQueue fetchObject()
	 * @method \Bitrix\Clouds\EO_DeleteQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DeleteQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_DeleteQueue fetchObject()
	 * @method \Bitrix\Clouds\EO_DeleteQueue_Collection fetchCollection()
	 */
	class EO_DeleteQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_DeleteQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_DeleteQueue_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_DeleteQueue wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_DeleteQueue_Collection wakeUpCollection($rows)
	 */
	class EO_DeleteQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\FileBucketTable:clouds/lib/filebucket.php:702ecfe46f3f0c5a25903eabfafc18e8 */
namespace Bitrix\Clouds {
	/**
	 * EO_FileBucket
	 * @see \Bitrix\Clouds\FileBucketTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Clouds\EO_FileBucket setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getActive()
	 * @method \Bitrix\Clouds\EO_FileBucket setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Clouds\EO_FileBucket resetActive()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Clouds\EO_FileBucket setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Clouds\EO_FileBucket resetSort()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getReadOnly()
	 * @method \Bitrix\Clouds\EO_FileBucket setReadOnly(\boolean|\Bitrix\Main\DB\SqlExpression $readOnly)
	 * @method bool hasReadOnly()
	 * @method bool isReadOnlyFilled()
	 * @method bool isReadOnlyChanged()
	 * @method \boolean remindActualReadOnly()
	 * @method \boolean requireReadOnly()
	 * @method \Bitrix\Clouds\EO_FileBucket resetReadOnly()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetReadOnly()
	 * @method \boolean fillReadOnly()
	 * @method \string getServiceId()
	 * @method \Bitrix\Clouds\EO_FileBucket setServiceId(\string|\Bitrix\Main\DB\SqlExpression $serviceId)
	 * @method bool hasServiceId()
	 * @method bool isServiceIdFilled()
	 * @method bool isServiceIdChanged()
	 * @method \string remindActualServiceId()
	 * @method \string requireServiceId()
	 * @method \Bitrix\Clouds\EO_FileBucket resetServiceId()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetServiceId()
	 * @method \string fillServiceId()
	 * @method \string getBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket setBucket(\string|\Bitrix\Main\DB\SqlExpression $bucket)
	 * @method bool hasBucket()
	 * @method bool isBucketFilled()
	 * @method bool isBucketChanged()
	 * @method \string remindActualBucket()
	 * @method \string requireBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket resetBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetBucket()
	 * @method \string fillBucket()
	 * @method \string getLocation()
	 * @method \Bitrix\Clouds\EO_FileBucket setLocation(\string|\Bitrix\Main\DB\SqlExpression $location)
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \string remindActualLocation()
	 * @method \string requireLocation()
	 * @method \Bitrix\Clouds\EO_FileBucket resetLocation()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetLocation()
	 * @method \string fillLocation()
	 * @method \string getCname()
	 * @method \Bitrix\Clouds\EO_FileBucket setCname(\string|\Bitrix\Main\DB\SqlExpression $cname)
	 * @method bool hasCname()
	 * @method bool isCnameFilled()
	 * @method bool isCnameChanged()
	 * @method \string remindActualCname()
	 * @method \string requireCname()
	 * @method \Bitrix\Clouds\EO_FileBucket resetCname()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetCname()
	 * @method \string fillCname()
	 * @method \int getFileCount()
	 * @method \Bitrix\Clouds\EO_FileBucket setFileCount(\int|\Bitrix\Main\DB\SqlExpression $fileCount)
	 * @method bool hasFileCount()
	 * @method bool isFileCountFilled()
	 * @method bool isFileCountChanged()
	 * @method \int remindActualFileCount()
	 * @method \int requireFileCount()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFileCount()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFileCount()
	 * @method \int fillFileCount()
	 * @method \float getFileSize()
	 * @method \Bitrix\Clouds\EO_FileBucket setFileSize(\float|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \float remindActualFileSize()
	 * @method \float requireFileSize()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFileSize()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFileSize()
	 * @method \float fillFileSize()
	 * @method \int getLastFileId()
	 * @method \Bitrix\Clouds\EO_FileBucket setLastFileId(\int|\Bitrix\Main\DB\SqlExpression $lastFileId)
	 * @method bool hasLastFileId()
	 * @method bool isLastFileIdFilled()
	 * @method bool isLastFileIdChanged()
	 * @method \int remindActualLastFileId()
	 * @method \int requireLastFileId()
	 * @method \Bitrix\Clouds\EO_FileBucket resetLastFileId()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetLastFileId()
	 * @method \int fillLastFileId()
	 * @method \string getPrefix()
	 * @method \Bitrix\Clouds\EO_FileBucket setPrefix(\string|\Bitrix\Main\DB\SqlExpression $prefix)
	 * @method bool hasPrefix()
	 * @method bool isPrefixFilled()
	 * @method bool isPrefixChanged()
	 * @method \string remindActualPrefix()
	 * @method \string requirePrefix()
	 * @method \Bitrix\Clouds\EO_FileBucket resetPrefix()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetPrefix()
	 * @method \string fillPrefix()
	 * @method \string getSettings()
	 * @method \Bitrix\Clouds\EO_FileBucket setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Clouds\EO_FileBucket resetSettings()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetSettings()
	 * @method \string fillSettings()
	 * @method \string getFileRules()
	 * @method \Bitrix\Clouds\EO_FileBucket setFileRules(\string|\Bitrix\Main\DB\SqlExpression $fileRules)
	 * @method bool hasFileRules()
	 * @method bool isFileRulesFilled()
	 * @method bool isFileRulesChanged()
	 * @method \string remindActualFileRules()
	 * @method \string requireFileRules()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFileRules()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFileRules()
	 * @method \string fillFileRules()
	 * @method \boolean getFailoverActive()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverActive(\boolean|\Bitrix\Main\DB\SqlExpression $failoverActive)
	 * @method bool hasFailoverActive()
	 * @method bool isFailoverActiveFilled()
	 * @method bool isFailoverActiveChanged()
	 * @method \boolean remindActualFailoverActive()
	 * @method \boolean requireFailoverActive()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverActive()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverActive()
	 * @method \boolean fillFailoverActive()
	 * @method \int getFailoverBucketId()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverBucketId(\int|\Bitrix\Main\DB\SqlExpression $failoverBucketId)
	 * @method bool hasFailoverBucketId()
	 * @method bool isFailoverBucketIdFilled()
	 * @method bool isFailoverBucketIdChanged()
	 * @method \int remindActualFailoverBucketId()
	 * @method \int requireFailoverBucketId()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverBucketId()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverBucketId()
	 * @method \int fillFailoverBucketId()
	 * @method \boolean getFailoverCopy()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverCopy(\boolean|\Bitrix\Main\DB\SqlExpression $failoverCopy)
	 * @method bool hasFailoverCopy()
	 * @method bool isFailoverCopyFilled()
	 * @method bool isFailoverCopyChanged()
	 * @method \boolean remindActualFailoverCopy()
	 * @method \boolean requireFailoverCopy()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverCopy()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverCopy()
	 * @method \boolean fillFailoverCopy()
	 * @method \boolean getFailoverDelete()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverDelete(\boolean|\Bitrix\Main\DB\SqlExpression $failoverDelete)
	 * @method bool hasFailoverDelete()
	 * @method bool isFailoverDeleteFilled()
	 * @method bool isFailoverDeleteChanged()
	 * @method \boolean remindActualFailoverDelete()
	 * @method \boolean requireFailoverDelete()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverDelete()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverDelete()
	 * @method \boolean fillFailoverDelete()
	 * @method \int getFailoverDeleteDelay()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverDeleteDelay(\int|\Bitrix\Main\DB\SqlExpression $failoverDeleteDelay)
	 * @method bool hasFailoverDeleteDelay()
	 * @method bool isFailoverDeleteDelayFilled()
	 * @method bool isFailoverDeleteDelayChanged()
	 * @method \int remindActualFailoverDeleteDelay()
	 * @method \int requireFailoverDeleteDelay()
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverDeleteDelay()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverDeleteDelay()
	 * @method \int fillFailoverDeleteDelay()
	 * @method \Bitrix\Clouds\EO_FileBucket getFailoverBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket remindActualFailoverBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket requireFailoverBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket setFailoverBucket(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method \Bitrix\Clouds\EO_FileBucket resetFailoverBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket unsetFailoverBucket()
	 * @method bool hasFailoverBucket()
	 * @method bool isFailoverBucketFilled()
	 * @method bool isFailoverBucketChanged()
	 * @method \Bitrix\Clouds\EO_FileBucket fillFailoverBucket()
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
	 * @method \Bitrix\Clouds\EO_FileBucket set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_FileBucket reset($fieldName)
	 * @method \Bitrix\Clouds\EO_FileBucket unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_FileBucket wakeUp($data)
	 */
	class EO_FileBucket {
		/* @var \Bitrix\Clouds\FileBucketTable */
		static public $dataClass = '\Bitrix\Clouds\FileBucketTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_FileBucket_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getReadOnlyList()
	 * @method \boolean[] fillReadOnly()
	 * @method \string[] getServiceIdList()
	 * @method \string[] fillServiceId()
	 * @method \string[] getBucketList()
	 * @method \string[] fillBucket()
	 * @method \string[] getLocationList()
	 * @method \string[] fillLocation()
	 * @method \string[] getCnameList()
	 * @method \string[] fillCname()
	 * @method \int[] getFileCountList()
	 * @method \int[] fillFileCount()
	 * @method \float[] getFileSizeList()
	 * @method \float[] fillFileSize()
	 * @method \int[] getLastFileIdList()
	 * @method \int[] fillLastFileId()
	 * @method \string[] getPrefixList()
	 * @method \string[] fillPrefix()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 * @method \string[] getFileRulesList()
	 * @method \string[] fillFileRules()
	 * @method \boolean[] getFailoverActiveList()
	 * @method \boolean[] fillFailoverActive()
	 * @method \int[] getFailoverBucketIdList()
	 * @method \int[] fillFailoverBucketId()
	 * @method \boolean[] getFailoverCopyList()
	 * @method \boolean[] fillFailoverCopy()
	 * @method \boolean[] getFailoverDeleteList()
	 * @method \boolean[] fillFailoverDelete()
	 * @method \int[] getFailoverDeleteDelayList()
	 * @method \int[] fillFailoverDeleteDelay()
	 * @method \Bitrix\Clouds\EO_FileBucket[] getFailoverBucketList()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection getFailoverBucketCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fillFailoverBucket()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method bool has(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileBucket getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileBucket[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_FileBucket_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_FileBucket current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileBucket_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\FileBucketTable */
		static public $dataClass = '\Bitrix\Clouds\FileBucketTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileBucket_Result exec()
	 * @method \Bitrix\Clouds\EO_FileBucket fetchObject()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileBucket_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_FileBucket fetchObject()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fetchCollection()
	 */
	class EO_FileBucket_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_FileBucket createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection wakeUpCollection($rows)
	 */
	class EO_FileBucket_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\FileHashTable:clouds/lib/filehash.php:d4ac34d8953167e754bb4c5782f6287a */
namespace Bitrix\Clouds {
	/**
	 * EO_FileHash
	 * @see \Bitrix\Clouds\FileHashTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getBucketId()
	 * @method \Bitrix\Clouds\EO_FileHash setBucketId(\int|\Bitrix\Main\DB\SqlExpression $bucketId)
	 * @method bool hasBucketId()
	 * @method bool isBucketIdFilled()
	 * @method bool isBucketIdChanged()
	 * @method \string getFilePath()
	 * @method \Bitrix\Clouds\EO_FileHash setFilePath(\string|\Bitrix\Main\DB\SqlExpression $filePath)
	 * @method bool hasFilePath()
	 * @method bool isFilePathFilled()
	 * @method bool isFilePathChanged()
	 * @method \int getFileSize()
	 * @method \Bitrix\Clouds\EO_FileHash setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Clouds\EO_FileHash resetFileSize()
	 * @method \Bitrix\Clouds\EO_FileHash unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \Bitrix\Main\Type\DateTime getFileMtime()
	 * @method \Bitrix\Clouds\EO_FileHash setFileMtime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $fileMtime)
	 * @method bool hasFileMtime()
	 * @method bool isFileMtimeFilled()
	 * @method bool isFileMtimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFileMtime()
	 * @method \Bitrix\Main\Type\DateTime requireFileMtime()
	 * @method \Bitrix\Clouds\EO_FileHash resetFileMtime()
	 * @method \Bitrix\Clouds\EO_FileHash unsetFileMtime()
	 * @method \Bitrix\Main\Type\DateTime fillFileMtime()
	 * @method \string getFileHash()
	 * @method \Bitrix\Clouds\EO_FileHash setFileHash(\string|\Bitrix\Main\DB\SqlExpression $fileHash)
	 * @method bool hasFileHash()
	 * @method bool isFileHashFilled()
	 * @method bool isFileHashChanged()
	 * @method \string remindActualFileHash()
	 * @method \string requireFileHash()
	 * @method \Bitrix\Clouds\EO_FileHash resetFileHash()
	 * @method \Bitrix\Clouds\EO_FileHash unsetFileHash()
	 * @method \string fillFileHash()
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
	 * @method \Bitrix\Clouds\EO_FileHash set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_FileHash reset($fieldName)
	 * @method \Bitrix\Clouds\EO_FileHash unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_FileHash wakeUp($data)
	 */
	class EO_FileHash {
		/* @var \Bitrix\Clouds\FileHashTable */
		static public $dataClass = '\Bitrix\Clouds\FileHashTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_FileHash_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getBucketIdList()
	 * @method \string[] getFilePathList()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \Bitrix\Main\Type\DateTime[] getFileMtimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFileMtime()
	 * @method \string[] getFileHashList()
	 * @method \string[] fillFileHash()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_FileHash $object)
	 * @method bool has(\Bitrix\Clouds\EO_FileHash $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileHash getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileHash[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_FileHash $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_FileHash_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_FileHash current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileHash_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\FileHashTable */
		static public $dataClass = '\Bitrix\Clouds\FileHashTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileHash_Result exec()
	 * @method \Bitrix\Clouds\EO_FileHash fetchObject()
	 * @method \Bitrix\Clouds\EO_FileHash_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileHash_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_FileHash fetchObject()
	 * @method \Bitrix\Clouds\EO_FileHash_Collection fetchCollection()
	 */
	class EO_FileHash_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_FileHash createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_FileHash_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_FileHash wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_FileHash_Collection wakeUpCollection($rows)
	 */
	class EO_FileHash_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\FileResizeTable:clouds/lib/fileresize.php:90d8fef504afcfd054d55550fb5df15f */
namespace Bitrix\Clouds {
	/**
	 * EO_FileResize
	 * @see \Bitrix\Clouds\FileResizeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Clouds\EO_FileResize setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Clouds\EO_FileResize setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Clouds\EO_FileResize resetTimestampX()
	 * @method \Bitrix\Clouds\EO_FileResize unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getErrorCode()
	 * @method \Bitrix\Clouds\EO_FileResize setErrorCode(\string|\Bitrix\Main\DB\SqlExpression $errorCode)
	 * @method bool hasErrorCode()
	 * @method bool isErrorCodeFilled()
	 * @method bool isErrorCodeChanged()
	 * @method \string remindActualErrorCode()
	 * @method \string requireErrorCode()
	 * @method \Bitrix\Clouds\EO_FileResize resetErrorCode()
	 * @method \Bitrix\Clouds\EO_FileResize unsetErrorCode()
	 * @method \string fillErrorCode()
	 * @method \int getFileId()
	 * @method \Bitrix\Clouds\EO_FileResize setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Clouds\EO_FileResize resetFileId()
	 * @method \Bitrix\Clouds\EO_FileResize unsetFileId()
	 * @method \int fillFileId()
	 * @method \string getParams()
	 * @method \Bitrix\Clouds\EO_FileResize setParams(\string|\Bitrix\Main\DB\SqlExpression $params)
	 * @method bool hasParams()
	 * @method bool isParamsFilled()
	 * @method bool isParamsChanged()
	 * @method \string remindActualParams()
	 * @method \string requireParams()
	 * @method \Bitrix\Clouds\EO_FileResize resetParams()
	 * @method \Bitrix\Clouds\EO_FileResize unsetParams()
	 * @method \string fillParams()
	 * @method \string getFromPath()
	 * @method \Bitrix\Clouds\EO_FileResize setFromPath(\string|\Bitrix\Main\DB\SqlExpression $fromPath)
	 * @method bool hasFromPath()
	 * @method bool isFromPathFilled()
	 * @method bool isFromPathChanged()
	 * @method \string remindActualFromPath()
	 * @method \string requireFromPath()
	 * @method \Bitrix\Clouds\EO_FileResize resetFromPath()
	 * @method \Bitrix\Clouds\EO_FileResize unsetFromPath()
	 * @method \string fillFromPath()
	 * @method \string getToPath()
	 * @method \Bitrix\Clouds\EO_FileResize setToPath(\string|\Bitrix\Main\DB\SqlExpression $toPath)
	 * @method bool hasToPath()
	 * @method bool isToPathFilled()
	 * @method bool isToPathChanged()
	 * @method \string remindActualToPath()
	 * @method \string requireToPath()
	 * @method \Bitrix\Clouds\EO_FileResize resetToPath()
	 * @method \Bitrix\Clouds\EO_FileResize unsetToPath()
	 * @method \string fillToPath()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\Clouds\EO_FileResize setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Clouds\EO_FileResize resetFile()
	 * @method \Bitrix\Clouds\EO_FileResize unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Main\EO_File fillFile()
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
	 * @method \Bitrix\Clouds\EO_FileResize set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_FileResize reset($fieldName)
	 * @method \Bitrix\Clouds\EO_FileResize unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_FileResize wakeUp($data)
	 */
	class EO_FileResize {
		/* @var \Bitrix\Clouds\FileResizeTable */
		static public $dataClass = '\Bitrix\Clouds\FileResizeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_FileResize_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getErrorCodeList()
	 * @method \string[] fillErrorCode()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \string[] getParamsList()
	 * @method \string[] fillParams()
	 * @method \string[] getFromPathList()
	 * @method \string[] fillFromPath()
	 * @method \string[] getToPathList()
	 * @method \string[] fillToPath()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\Clouds\EO_FileResize_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_FileResize $object)
	 * @method bool has(\Bitrix\Clouds\EO_FileResize $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileResize getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileResize[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_FileResize $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_FileResize_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_FileResize current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileResize_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\FileResizeTable */
		static public $dataClass = '\Bitrix\Clouds\FileResizeTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileResize_Result exec()
	 * @method \Bitrix\Clouds\EO_FileResize fetchObject()
	 * @method \Bitrix\Clouds\EO_FileResize_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileResize_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_FileResize fetchObject()
	 * @method \Bitrix\Clouds\EO_FileResize_Collection fetchCollection()
	 */
	class EO_FileResize_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_FileResize createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_FileResize_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_FileResize wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_FileResize_Collection wakeUpCollection($rows)
	 */
	class EO_FileResize_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\FileSaveTable:clouds/lib/filesave.php:14eb9a250d9e7026d3e9d10fc638fa1c */
namespace Bitrix\Clouds {
	/**
	 * EO_FileSave
	 * @see \Bitrix\Clouds\FileSaveTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Clouds\EO_FileSave setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Clouds\EO_FileSave setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Clouds\EO_FileSave resetTimestampX()
	 * @method \Bitrix\Clouds\EO_FileSave unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getBucketId()
	 * @method \Bitrix\Clouds\EO_FileSave setBucketId(\int|\Bitrix\Main\DB\SqlExpression $bucketId)
	 * @method bool hasBucketId()
	 * @method bool isBucketIdFilled()
	 * @method bool isBucketIdChanged()
	 * @method \int remindActualBucketId()
	 * @method \int requireBucketId()
	 * @method \Bitrix\Clouds\EO_FileSave resetBucketId()
	 * @method \Bitrix\Clouds\EO_FileSave unsetBucketId()
	 * @method \int fillBucketId()
	 * @method \string getSubdir()
	 * @method \Bitrix\Clouds\EO_FileSave setSubdir(\string|\Bitrix\Main\DB\SqlExpression $subdir)
	 * @method bool hasSubdir()
	 * @method bool isSubdirFilled()
	 * @method bool isSubdirChanged()
	 * @method \string remindActualSubdir()
	 * @method \string requireSubdir()
	 * @method \Bitrix\Clouds\EO_FileSave resetSubdir()
	 * @method \Bitrix\Clouds\EO_FileSave unsetSubdir()
	 * @method \string fillSubdir()
	 * @method \string getFileName()
	 * @method \Bitrix\Clouds\EO_FileSave setFileName(\string|\Bitrix\Main\DB\SqlExpression $fileName)
	 * @method bool hasFileName()
	 * @method bool isFileNameFilled()
	 * @method bool isFileNameChanged()
	 * @method \string remindActualFileName()
	 * @method \string requireFileName()
	 * @method \Bitrix\Clouds\EO_FileSave resetFileName()
	 * @method \Bitrix\Clouds\EO_FileSave unsetFileName()
	 * @method \string fillFileName()
	 * @method \string getExternalId()
	 * @method \Bitrix\Clouds\EO_FileSave setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Clouds\EO_FileSave resetExternalId()
	 * @method \Bitrix\Clouds\EO_FileSave unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \int getFileSize()
	 * @method \Bitrix\Clouds\EO_FileSave setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Clouds\EO_FileSave resetFileSize()
	 * @method \Bitrix\Clouds\EO_FileSave unsetFileSize()
	 * @method \int fillFileSize()
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
	 * @method \Bitrix\Clouds\EO_FileSave set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_FileSave reset($fieldName)
	 * @method \Bitrix\Clouds\EO_FileSave unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_FileSave wakeUp($data)
	 */
	class EO_FileSave {
		/* @var \Bitrix\Clouds\FileSaveTable */
		static public $dataClass = '\Bitrix\Clouds\FileSaveTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_FileSave_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getBucketIdList()
	 * @method \int[] fillBucketId()
	 * @method \string[] getSubdirList()
	 * @method \string[] fillSubdir()
	 * @method \string[] getFileNameList()
	 * @method \string[] fillFileName()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_FileSave $object)
	 * @method bool has(\Bitrix\Clouds\EO_FileSave $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileSave getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileSave[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_FileSave $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_FileSave_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_FileSave current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileSave_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\FileSaveTable */
		static public $dataClass = '\Bitrix\Clouds\FileSaveTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileSave_Result exec()
	 * @method \Bitrix\Clouds\EO_FileSave fetchObject()
	 * @method \Bitrix\Clouds\EO_FileSave_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileSave_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_FileSave fetchObject()
	 * @method \Bitrix\Clouds\EO_FileSave_Collection fetchCollection()
	 */
	class EO_FileSave_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_FileSave createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_FileSave_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_FileSave wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_FileSave_Collection wakeUpCollection($rows)
	 */
	class EO_FileSave_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Clouds\FileUploadTable:clouds/lib/fileupload.php:ee9c972b70ee447c2dbe0c8ab97298d8 */
namespace Bitrix\Clouds {
	/**
	 * EO_FileUpload
	 * @see \Bitrix\Clouds\FileUploadTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Clouds\EO_FileUpload setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Clouds\EO_FileUpload setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Clouds\EO_FileUpload resetTimestampX()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getFilePath()
	 * @method \Bitrix\Clouds\EO_FileUpload setFilePath(\string|\Bitrix\Main\DB\SqlExpression $filePath)
	 * @method bool hasFilePath()
	 * @method bool isFilePathFilled()
	 * @method bool isFilePathChanged()
	 * @method \string remindActualFilePath()
	 * @method \string requireFilePath()
	 * @method \Bitrix\Clouds\EO_FileUpload resetFilePath()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetFilePath()
	 * @method \string fillFilePath()
	 * @method \int getFileSize()
	 * @method \Bitrix\Clouds\EO_FileUpload setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Clouds\EO_FileUpload resetFileSize()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getTmpFile()
	 * @method \Bitrix\Clouds\EO_FileUpload setTmpFile(\string|\Bitrix\Main\DB\SqlExpression $tmpFile)
	 * @method bool hasTmpFile()
	 * @method bool isTmpFileFilled()
	 * @method bool isTmpFileChanged()
	 * @method \string remindActualTmpFile()
	 * @method \string requireTmpFile()
	 * @method \Bitrix\Clouds\EO_FileUpload resetTmpFile()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetTmpFile()
	 * @method \string fillTmpFile()
	 * @method \int getBucketId()
	 * @method \Bitrix\Clouds\EO_FileUpload setBucketId(\int|\Bitrix\Main\DB\SqlExpression $bucketId)
	 * @method bool hasBucketId()
	 * @method bool isBucketIdFilled()
	 * @method bool isBucketIdChanged()
	 * @method \int remindActualBucketId()
	 * @method \int requireBucketId()
	 * @method \Bitrix\Clouds\EO_FileUpload resetBucketId()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetBucketId()
	 * @method \int fillBucketId()
	 * @method \int getPartSize()
	 * @method \Bitrix\Clouds\EO_FileUpload setPartSize(\int|\Bitrix\Main\DB\SqlExpression $partSize)
	 * @method bool hasPartSize()
	 * @method bool isPartSizeFilled()
	 * @method bool isPartSizeChanged()
	 * @method \int remindActualPartSize()
	 * @method \int requirePartSize()
	 * @method \Bitrix\Clouds\EO_FileUpload resetPartSize()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetPartSize()
	 * @method \int fillPartSize()
	 * @method \int getPartNo()
	 * @method \Bitrix\Clouds\EO_FileUpload setPartNo(\int|\Bitrix\Main\DB\SqlExpression $partNo)
	 * @method bool hasPartNo()
	 * @method bool isPartNoFilled()
	 * @method bool isPartNoChanged()
	 * @method \int remindActualPartNo()
	 * @method \int requirePartNo()
	 * @method \Bitrix\Clouds\EO_FileUpload resetPartNo()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetPartNo()
	 * @method \int fillPartNo()
	 * @method \int getPartFailCounter()
	 * @method \Bitrix\Clouds\EO_FileUpload setPartFailCounter(\int|\Bitrix\Main\DB\SqlExpression $partFailCounter)
	 * @method bool hasPartFailCounter()
	 * @method bool isPartFailCounterFilled()
	 * @method bool isPartFailCounterChanged()
	 * @method \int remindActualPartFailCounter()
	 * @method \int requirePartFailCounter()
	 * @method \Bitrix\Clouds\EO_FileUpload resetPartFailCounter()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetPartFailCounter()
	 * @method \int fillPartFailCounter()
	 * @method \string getNextStep()
	 * @method \Bitrix\Clouds\EO_FileUpload setNextStep(\string|\Bitrix\Main\DB\SqlExpression $nextStep)
	 * @method bool hasNextStep()
	 * @method bool isNextStepFilled()
	 * @method bool isNextStepChanged()
	 * @method \string remindActualNextStep()
	 * @method \string requireNextStep()
	 * @method \Bitrix\Clouds\EO_FileUpload resetNextStep()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetNextStep()
	 * @method \string fillNextStep()
	 * @method \Bitrix\Clouds\EO_FileBucket getBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket remindActualBucket()
	 * @method \Bitrix\Clouds\EO_FileBucket requireBucket()
	 * @method \Bitrix\Clouds\EO_FileUpload setBucket(\Bitrix\Clouds\EO_FileBucket $object)
	 * @method \Bitrix\Clouds\EO_FileUpload resetBucket()
	 * @method \Bitrix\Clouds\EO_FileUpload unsetBucket()
	 * @method bool hasBucket()
	 * @method bool isBucketFilled()
	 * @method bool isBucketChanged()
	 * @method \Bitrix\Clouds\EO_FileBucket fillBucket()
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
	 * @method \Bitrix\Clouds\EO_FileUpload set($fieldName, $value)
	 * @method \Bitrix\Clouds\EO_FileUpload reset($fieldName)
	 * @method \Bitrix\Clouds\EO_FileUpload unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Clouds\EO_FileUpload wakeUp($data)
	 */
	class EO_FileUpload {
		/* @var \Bitrix\Clouds\FileUploadTable */
		static public $dataClass = '\Bitrix\Clouds\FileUploadTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Clouds {
	/**
	 * EO_FileUpload_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getFilePathList()
	 * @method \string[] fillFilePath()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getTmpFileList()
	 * @method \string[] fillTmpFile()
	 * @method \int[] getBucketIdList()
	 * @method \int[] fillBucketId()
	 * @method \int[] getPartSizeList()
	 * @method \int[] fillPartSize()
	 * @method \int[] getPartNoList()
	 * @method \int[] fillPartNo()
	 * @method \int[] getPartFailCounterList()
	 * @method \int[] fillPartFailCounter()
	 * @method \string[] getNextStepList()
	 * @method \string[] fillNextStep()
	 * @method \Bitrix\Clouds\EO_FileBucket[] getBucketList()
	 * @method \Bitrix\Clouds\EO_FileUpload_Collection getBucketCollection()
	 * @method \Bitrix\Clouds\EO_FileBucket_Collection fillBucket()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Clouds\EO_FileUpload $object)
	 * @method bool has(\Bitrix\Clouds\EO_FileUpload $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileUpload getByPrimary($primary)
	 * @method \Bitrix\Clouds\EO_FileUpload[] getAll()
	 * @method bool remove(\Bitrix\Clouds\EO_FileUpload $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Clouds\EO_FileUpload_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Clouds\EO_FileUpload current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileUpload_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Clouds\FileUploadTable */
		static public $dataClass = '\Bitrix\Clouds\FileUploadTable';
	}
}
namespace Bitrix\Clouds {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileUpload_Result exec()
	 * @method \Bitrix\Clouds\EO_FileUpload fetchObject()
	 * @method \Bitrix\Clouds\EO_FileUpload_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileUpload_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Clouds\EO_FileUpload fetchObject()
	 * @method \Bitrix\Clouds\EO_FileUpload_Collection fetchCollection()
	 */
	class EO_FileUpload_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Clouds\EO_FileUpload createObject($setDefaultValues = true)
	 * @method \Bitrix\Clouds\EO_FileUpload_Collection createCollection()
	 * @method \Bitrix\Clouds\EO_FileUpload wakeUpObject($row)
	 * @method \Bitrix\Clouds\EO_FileUpload_Collection wakeUpCollection($rows)
	 */
	class EO_FileUpload_Entity extends \Bitrix\Main\ORM\Entity {}
}