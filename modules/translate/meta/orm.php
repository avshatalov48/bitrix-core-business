<?php

/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\FileDiffTable:translate/lib/index/internals/filediff.php:2cdcf6d94866835dc2b6908d5f3099f2 */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * FileDiff
	 * @see \Bitrix\Translate\Index\Internals\FileDiffTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\FileDiff setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Translate\Index\FileDiff setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Translate\Index\FileDiff resetFileId()
	 * @method \Bitrix\Translate\Index\FileDiff unsetFileId()
	 * @method \int fillFileId()
	 * @method \int getPathId()
	 * @method \Bitrix\Translate\Index\FileDiff setPathId(\int|\Bitrix\Main\DB\SqlExpression $pathId)
	 * @method bool hasPathId()
	 * @method bool isPathIdFilled()
	 * @method bool isPathIdChanged()
	 * @method \int remindActualPathId()
	 * @method \int requirePathId()
	 * @method \Bitrix\Translate\Index\FileDiff resetPathId()
	 * @method \Bitrix\Translate\Index\FileDiff unsetPathId()
	 * @method \int fillPathId()
	 * @method \string getLangId()
	 * @method \Bitrix\Translate\Index\FileDiff setLangId(\string|\Bitrix\Main\DB\SqlExpression $langId)
	 * @method bool hasLangId()
	 * @method bool isLangIdFilled()
	 * @method bool isLangIdChanged()
	 * @method \string remindActualLangId()
	 * @method \string requireLangId()
	 * @method \Bitrix\Translate\Index\FileDiff resetLangId()
	 * @method \Bitrix\Translate\Index\FileDiff unsetLangId()
	 * @method \string fillLangId()
	 * @method \string getAgainstLangId()
	 * @method \Bitrix\Translate\Index\FileDiff setAgainstLangId(\string|\Bitrix\Main\DB\SqlExpression $againstLangId)
	 * @method bool hasAgainstLangId()
	 * @method bool isAgainstLangIdFilled()
	 * @method bool isAgainstLangIdChanged()
	 * @method \string remindActualAgainstLangId()
	 * @method \string requireAgainstLangId()
	 * @method \Bitrix\Translate\Index\FileDiff resetAgainstLangId()
	 * @method \Bitrix\Translate\Index\FileDiff unsetAgainstLangId()
	 * @method \string fillAgainstLangId()
	 * @method \int getExcessCount()
	 * @method \Bitrix\Translate\Index\FileDiff setExcessCount(\int|\Bitrix\Main\DB\SqlExpression $excessCount)
	 * @method bool hasExcessCount()
	 * @method bool isExcessCountFilled()
	 * @method bool isExcessCountChanged()
	 * @method \int remindActualExcessCount()
	 * @method \int requireExcessCount()
	 * @method \Bitrix\Translate\Index\FileDiff resetExcessCount()
	 * @method \Bitrix\Translate\Index\FileDiff unsetExcessCount()
	 * @method \int fillExcessCount()
	 * @method \int getDeficiencyCount()
	 * @method \Bitrix\Translate\Index\FileDiff setDeficiencyCount(\int|\Bitrix\Main\DB\SqlExpression $deficiencyCount)
	 * @method bool hasDeficiencyCount()
	 * @method bool isDeficiencyCountFilled()
	 * @method bool isDeficiencyCountChanged()
	 * @method \int remindActualDeficiencyCount()
	 * @method \int requireDeficiencyCount()
	 * @method \Bitrix\Translate\Index\FileDiff resetDeficiencyCount()
	 * @method \Bitrix\Translate\Index\FileDiff unsetDeficiencyCount()
	 * @method \int fillDeficiencyCount()
	 * @method \Bitrix\Translate\Index\FileIndex getFile()
	 * @method \Bitrix\Translate\Index\FileIndex remindActualFile()
	 * @method \Bitrix\Translate\Index\FileIndex requireFile()
	 * @method \Bitrix\Translate\Index\FileDiff setFile(\Bitrix\Translate\Index\FileIndex $object)
	 * @method \Bitrix\Translate\Index\FileDiff resetFile()
	 * @method \Bitrix\Translate\Index\FileDiff unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Translate\Index\FileIndex fillFile()
	 * @method \Bitrix\Translate\Index\PathIndex getPath()
	 * @method \Bitrix\Translate\Index\PathIndex remindActualPath()
	 * @method \Bitrix\Translate\Index\PathIndex requirePath()
	 * @method \Bitrix\Translate\Index\FileDiff setPath(\Bitrix\Translate\Index\PathIndex $object)
	 * @method \Bitrix\Translate\Index\FileDiff resetPath()
	 * @method \Bitrix\Translate\Index\FileDiff unsetPath()
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \Bitrix\Translate\Index\PathIndex fillPath()
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
	 * @method \Bitrix\Translate\Index\FileDiff set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\FileDiff reset($fieldName)
	 * @method \Bitrix\Translate\Index\FileDiff unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\FileDiff wakeUp($data)
	 */
	class EO_FileDiff {
		/* @var \Bitrix\Translate\Index\Internals\FileDiffTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\FileDiffTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * FileDiffCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \int[] getPathIdList()
	 * @method \int[] fillPathId()
	 * @method \string[] getLangIdList()
	 * @method \string[] fillLangId()
	 * @method \string[] getAgainstLangIdList()
	 * @method \string[] fillAgainstLangId()
	 * @method \int[] getExcessCountList()
	 * @method \int[] fillExcessCount()
	 * @method \int[] getDeficiencyCountList()
	 * @method \int[] fillDeficiencyCount()
	 * @method \Bitrix\Translate\Index\FileIndex[] getFileList()
	 * @method \Bitrix\Translate\Index\FileDiffCollection getFileCollection()
	 * @method \Bitrix\Translate\Index\FileIndexCollection fillFile()
	 * @method \Bitrix\Translate\Index\PathIndex[] getPathList()
	 * @method \Bitrix\Translate\Index\FileDiffCollection getPathCollection()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\FileDiff $object)
	 * @method bool has(\Bitrix\Translate\Index\FileDiff $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\FileDiff getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\FileDiff[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\FileDiff $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\FileDiffCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\FileDiff current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileDiff_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\FileDiffTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\FileDiffTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileDiff_Result exec()
	 * @method \Bitrix\Translate\Index\FileDiff fetchObject()
	 * @method \Bitrix\Translate\Index\FileDiffCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileDiff_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\FileDiff fetchObject()
	 * @method \Bitrix\Translate\Index\FileDiffCollection fetchCollection()
	 */
	class EO_FileDiff_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\FileDiff createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\FileDiffCollection createCollection()
	 * @method \Bitrix\Translate\Index\FileDiff wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\FileDiffCollection wakeUpCollection($rows)
	 */
	class EO_FileDiff_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\FileIndexTable:translate/lib/index/internals/fileindex.php:4b9eb176b66a0045dca9091d911d3ace */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * FileIndex
	 * @see \Bitrix\Translate\Index\Internals\FileIndexTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\FileIndex setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPathId()
	 * @method \Bitrix\Translate\Index\FileIndex setPathId(\int|\Bitrix\Main\DB\SqlExpression $pathId)
	 * @method bool hasPathId()
	 * @method bool isPathIdFilled()
	 * @method bool isPathIdChanged()
	 * @method \int remindActualPathId()
	 * @method \int requirePathId()
	 * @method \Bitrix\Translate\Index\FileIndex resetPathId()
	 * @method \Bitrix\Translate\Index\FileIndex unsetPathId()
	 * @method \int fillPathId()
	 * @method \string getLangId()
	 * @method \Bitrix\Translate\Index\FileIndex setLangId(\string|\Bitrix\Main\DB\SqlExpression $langId)
	 * @method bool hasLangId()
	 * @method bool isLangIdFilled()
	 * @method bool isLangIdChanged()
	 * @method \string remindActualLangId()
	 * @method \string requireLangId()
	 * @method \Bitrix\Translate\Index\FileIndex resetLangId()
	 * @method \Bitrix\Translate\Index\FileIndex unsetLangId()
	 * @method \string fillLangId()
	 * @method \string getFullPath()
	 * @method \Bitrix\Translate\Index\FileIndex setFullPath(\string|\Bitrix\Main\DB\SqlExpression $fullPath)
	 * @method bool hasFullPath()
	 * @method bool isFullPathFilled()
	 * @method bool isFullPathChanged()
	 * @method \string remindActualFullPath()
	 * @method \string requireFullPath()
	 * @method \Bitrix\Translate\Index\FileIndex resetFullPath()
	 * @method \Bitrix\Translate\Index\FileIndex unsetFullPath()
	 * @method \string fillFullPath()
	 * @method \int getPhraseCount()
	 * @method \Bitrix\Translate\Index\FileIndex setPhraseCount(\int|\Bitrix\Main\DB\SqlExpression $phraseCount)
	 * @method bool hasPhraseCount()
	 * @method bool isPhraseCountFilled()
	 * @method bool isPhraseCountChanged()
	 * @method \int remindActualPhraseCount()
	 * @method \int requirePhraseCount()
	 * @method \Bitrix\Translate\Index\FileIndex resetPhraseCount()
	 * @method \Bitrix\Translate\Index\FileIndex unsetPhraseCount()
	 * @method \int fillPhraseCount()
	 * @method \boolean getIndexed()
	 * @method \Bitrix\Translate\Index\FileIndex setIndexed(\boolean|\Bitrix\Main\DB\SqlExpression $indexed)
	 * @method bool hasIndexed()
	 * @method bool isIndexedFilled()
	 * @method bool isIndexedChanged()
	 * @method \boolean remindActualIndexed()
	 * @method \boolean requireIndexed()
	 * @method \Bitrix\Translate\Index\FileIndex resetIndexed()
	 * @method \Bitrix\Translate\Index\FileIndex unsetIndexed()
	 * @method \boolean fillIndexed()
	 * @method \Bitrix\Main\Type\DateTime getIndexedTime()
	 * @method \Bitrix\Translate\Index\FileIndex setIndexedTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $indexedTime)
	 * @method bool hasIndexedTime()
	 * @method bool isIndexedTimeFilled()
	 * @method bool isIndexedTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualIndexedTime()
	 * @method \Bitrix\Main\Type\DateTime requireIndexedTime()
	 * @method \Bitrix\Translate\Index\FileIndex resetIndexedTime()
	 * @method \Bitrix\Translate\Index\FileIndex unsetIndexedTime()
	 * @method \Bitrix\Main\Type\DateTime fillIndexedTime()
	 * @method \Bitrix\Translate\Index\PhraseIndex getPhrase()
	 * @method \Bitrix\Translate\Index\PhraseIndex remindActualPhrase()
	 * @method \Bitrix\Translate\Index\PhraseIndex requirePhrase()
	 * @method \Bitrix\Translate\Index\FileIndex setPhrase(\Bitrix\Translate\Index\PhraseIndex $object)
	 * @method \Bitrix\Translate\Index\FileIndex resetPhrase()
	 * @method \Bitrix\Translate\Index\FileIndex unsetPhrase()
	 * @method bool hasPhrase()
	 * @method bool isPhraseFilled()
	 * @method bool isPhraseChanged()
	 * @method \Bitrix\Translate\Index\PhraseIndex fillPhrase()
	 * @method \Bitrix\Translate\Index\PathIndex getPath()
	 * @method \Bitrix\Translate\Index\PathIndex remindActualPath()
	 * @method \Bitrix\Translate\Index\PathIndex requirePath()
	 * @method \Bitrix\Translate\Index\FileIndex setPath(\Bitrix\Translate\Index\PathIndex $object)
	 * @method \Bitrix\Translate\Index\FileIndex resetPath()
	 * @method \Bitrix\Translate\Index\FileIndex unsetPath()
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \Bitrix\Translate\Index\PathIndex fillPath()
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
	 * @method \Bitrix\Translate\Index\FileIndex set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\FileIndex reset($fieldName)
	 * @method \Bitrix\Translate\Index\FileIndex unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\FileIndex wakeUp($data)
	 */
	class EO_FileIndex {
		/* @var \Bitrix\Translate\Index\Internals\FileIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\FileIndexTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * FileIndexCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPathIdList()
	 * @method \int[] fillPathId()
	 * @method \string[] getLangIdList()
	 * @method \string[] fillLangId()
	 * @method \string[] getFullPathList()
	 * @method \string[] fillFullPath()
	 * @method \int[] getPhraseCountList()
	 * @method \int[] fillPhraseCount()
	 * @method \boolean[] getIndexedList()
	 * @method \boolean[] fillIndexed()
	 * @method \Bitrix\Main\Type\DateTime[] getIndexedTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillIndexedTime()
	 * @method \Bitrix\Translate\Index\PhraseIndex[] getPhraseList()
	 * @method \Bitrix\Translate\Index\FileIndexCollection getPhraseCollection()
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection fillPhrase()
	 * @method \Bitrix\Translate\Index\PathIndex[] getPathList()
	 * @method \Bitrix\Translate\Index\FileIndexCollection getPathCollection()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\FileIndex $object)
	 * @method bool has(\Bitrix\Translate\Index\FileIndex $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\FileIndex getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\FileIndex[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\FileIndex $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\FileIndexCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\FileIndex current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileIndex_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\FileIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\FileIndexTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileIndex_Result exec()
	 * @method \Bitrix\Translate\Index\FileIndex fetchObject()
	 * @method \Bitrix\Translate\Index\FileIndexCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileIndex_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\FileIndex fetchObject()
	 * @method \Bitrix\Translate\Index\FileIndexCollection fetchCollection()
	 */
	class EO_FileIndex_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\FileIndex createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\FileIndexCollection createCollection()
	 * @method \Bitrix\Translate\Index\FileIndex wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\FileIndexCollection wakeUpCollection($rows)
	 */
	class EO_FileIndex_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\PathIndexTable:translate/lib/index/internals/pathindex.php:7ef77ccf33d472fffa31c829da80a201 */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * PathIndex
	 * @see \Bitrix\Translate\Index\Internals\PathIndexTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\PathIndex setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Translate\Index\PathIndex setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Translate\Index\PathIndex resetParentId()
	 * @method \Bitrix\Translate\Index\PathIndex unsetParentId()
	 * @method \int fillParentId()
	 * @method \string getPath()
	 * @method \Bitrix\Translate\Index\PathIndex setPath(\string|\Bitrix\Main\DB\SqlExpression $path)
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \string remindActualPath()
	 * @method \string requirePath()
	 * @method \Bitrix\Translate\Index\PathIndex resetPath()
	 * @method \Bitrix\Translate\Index\PathIndex unsetPath()
	 * @method \string fillPath()
	 * @method \string getName()
	 * @method \Bitrix\Translate\Index\PathIndex setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Translate\Index\PathIndex resetName()
	 * @method \Bitrix\Translate\Index\PathIndex unsetName()
	 * @method \string fillName()
	 * @method \string getModuleId()
	 * @method \Bitrix\Translate\Index\PathIndex setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Translate\Index\PathIndex resetModuleId()
	 * @method \Bitrix\Translate\Index\PathIndex unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getAssignment()
	 * @method \Bitrix\Translate\Index\PathIndex setAssignment(\string|\Bitrix\Main\DB\SqlExpression $assignment)
	 * @method bool hasAssignment()
	 * @method bool isAssignmentFilled()
	 * @method bool isAssignmentChanged()
	 * @method \string remindActualAssignment()
	 * @method \string requireAssignment()
	 * @method \Bitrix\Translate\Index\PathIndex resetAssignment()
	 * @method \Bitrix\Translate\Index\PathIndex unsetAssignment()
	 * @method \string fillAssignment()
	 * @method \int getDepthLevel()
	 * @method \Bitrix\Translate\Index\PathIndex setDepthLevel(\int|\Bitrix\Main\DB\SqlExpression $depthLevel)
	 * @method bool hasDepthLevel()
	 * @method bool isDepthLevelFilled()
	 * @method bool isDepthLevelChanged()
	 * @method \int remindActualDepthLevel()
	 * @method \int requireDepthLevel()
	 * @method \Bitrix\Translate\Index\PathIndex resetDepthLevel()
	 * @method \Bitrix\Translate\Index\PathIndex unsetDepthLevel()
	 * @method \int fillDepthLevel()
	 * @method \int getSort()
	 * @method \Bitrix\Translate\Index\PathIndex setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Translate\Index\PathIndex resetSort()
	 * @method \Bitrix\Translate\Index\PathIndex unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getIsLang()
	 * @method \Bitrix\Translate\Index\PathIndex setIsLang(\boolean|\Bitrix\Main\DB\SqlExpression $isLang)
	 * @method bool hasIsLang()
	 * @method bool isIsLangFilled()
	 * @method bool isIsLangChanged()
	 * @method \boolean remindActualIsLang()
	 * @method \boolean requireIsLang()
	 * @method \Bitrix\Translate\Index\PathIndex resetIsLang()
	 * @method \Bitrix\Translate\Index\PathIndex unsetIsLang()
	 * @method \boolean fillIsLang()
	 * @method \boolean getIsDir()
	 * @method \Bitrix\Translate\Index\PathIndex setIsDir(\boolean|\Bitrix\Main\DB\SqlExpression $isDir)
	 * @method bool hasIsDir()
	 * @method bool isIsDirFilled()
	 * @method bool isIsDirChanged()
	 * @method \boolean remindActualIsDir()
	 * @method \boolean requireIsDir()
	 * @method \Bitrix\Translate\Index\PathIndex resetIsDir()
	 * @method \Bitrix\Translate\Index\PathIndex unsetIsDir()
	 * @method \boolean fillIsDir()
	 * @method \string getObligatoryLangs()
	 * @method \Bitrix\Translate\Index\PathIndex setObligatoryLangs(\string|\Bitrix\Main\DB\SqlExpression $obligatoryLangs)
	 * @method bool hasObligatoryLangs()
	 * @method bool isObligatoryLangsFilled()
	 * @method bool isObligatoryLangsChanged()
	 * @method \string remindActualObligatoryLangs()
	 * @method \string requireObligatoryLangs()
	 * @method \Bitrix\Translate\Index\PathIndex resetObligatoryLangs()
	 * @method \Bitrix\Translate\Index\PathIndex unsetObligatoryLangs()
	 * @method \string fillObligatoryLangs()
	 * @method \boolean getIndexed()
	 * @method \Bitrix\Translate\Index\PathIndex setIndexed(\boolean|\Bitrix\Main\DB\SqlExpression $indexed)
	 * @method bool hasIndexed()
	 * @method bool isIndexedFilled()
	 * @method bool isIndexedChanged()
	 * @method \boolean remindActualIndexed()
	 * @method \boolean requireIndexed()
	 * @method \Bitrix\Translate\Index\PathIndex resetIndexed()
	 * @method \Bitrix\Translate\Index\PathIndex unsetIndexed()
	 * @method \boolean fillIndexed()
	 * @method \Bitrix\Main\Type\DateTime getIndexedTime()
	 * @method \Bitrix\Translate\Index\PathIndex setIndexedTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $indexedTime)
	 * @method bool hasIndexedTime()
	 * @method bool isIndexedTimeFilled()
	 * @method bool isIndexedTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualIndexedTime()
	 * @method \Bitrix\Main\Type\DateTime requireIndexedTime()
	 * @method \Bitrix\Translate\Index\PathIndex resetIndexedTime()
	 * @method \Bitrix\Translate\Index\PathIndex unsetIndexedTime()
	 * @method \Bitrix\Main\Type\DateTime fillIndexedTime()
	 * @method \Bitrix\Translate\Index\FileIndex getFile()
	 * @method \Bitrix\Translate\Index\FileIndex remindActualFile()
	 * @method \Bitrix\Translate\Index\FileIndex requireFile()
	 * @method \Bitrix\Translate\Index\PathIndex setFile(\Bitrix\Translate\Index\FileIndex $object)
	 * @method \Bitrix\Translate\Index\PathIndex resetFile()
	 * @method \Bitrix\Translate\Index\PathIndex unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Translate\Index\FileIndex fillFile()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree getAncestors()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree remindActualAncestors()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree requireAncestors()
	 * @method \Bitrix\Translate\Index\PathIndex setAncestors(\Bitrix\Translate\Index\Internals\EO_PathTree $object)
	 * @method \Bitrix\Translate\Index\PathIndex resetAncestors()
	 * @method \Bitrix\Translate\Index\PathIndex unsetAncestors()
	 * @method bool hasAncestors()
	 * @method bool isAncestorsFilled()
	 * @method bool isAncestorsChanged()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree fillAncestors()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree getDescendants()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree remindActualDescendants()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree requireDescendants()
	 * @method \Bitrix\Translate\Index\PathIndex setDescendants(\Bitrix\Translate\Index\Internals\EO_PathTree $object)
	 * @method \Bitrix\Translate\Index\PathIndex resetDescendants()
	 * @method \Bitrix\Translate\Index\PathIndex unsetDescendants()
	 * @method bool hasDescendants()
	 * @method bool isDescendantsFilled()
	 * @method bool isDescendantsChanged()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree fillDescendants()
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
	 * @method \Bitrix\Translate\Index\PathIndex set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\PathIndex reset($fieldName)
	 * @method \Bitrix\Translate\Index\PathIndex unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\PathIndex wakeUp($data)
	 */
	class EO_PathIndex {
		/* @var \Bitrix\Translate\Index\Internals\PathIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathIndexTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * PathIndexCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \string[] getPathList()
	 * @method \string[] fillPath()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getAssignmentList()
	 * @method \string[] fillAssignment()
	 * @method \int[] getDepthLevelList()
	 * @method \int[] fillDepthLevel()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getIsLangList()
	 * @method \boolean[] fillIsLang()
	 * @method \boolean[] getIsDirList()
	 * @method \boolean[] fillIsDir()
	 * @method \string[] getObligatoryLangsList()
	 * @method \string[] fillObligatoryLangs()
	 * @method \boolean[] getIndexedList()
	 * @method \boolean[] fillIndexed()
	 * @method \Bitrix\Main\Type\DateTime[] getIndexedTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillIndexedTime()
	 * @method \Bitrix\Translate\Index\FileIndex[] getFileList()
	 * @method \Bitrix\Translate\Index\PathIndexCollection getFileCollection()
	 * @method \Bitrix\Translate\Index\FileIndexCollection fillFile()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree[] getAncestorsList()
	 * @method \Bitrix\Translate\Index\PathIndexCollection getAncestorsCollection()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection fillAncestors()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree[] getDescendantsList()
	 * @method \Bitrix\Translate\Index\PathIndexCollection getDescendantsCollection()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection fillDescendants()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\PathIndex $object)
	 * @method bool has(\Bitrix\Translate\Index\PathIndex $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\PathIndex getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\PathIndex[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\PathIndex $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\PathIndexCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\PathIndex current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PathIndex_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\PathIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathIndexTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PathIndex_Result exec()
	 * @method \Bitrix\Translate\Index\PathIndex fetchObject()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PathIndex_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\PathIndex fetchObject()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fetchCollection()
	 */
	class EO_PathIndex_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\PathIndex createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\PathIndexCollection createCollection()
	 * @method \Bitrix\Translate\Index\PathIndex wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\PathIndexCollection wakeUpCollection($rows)
	 */
	class EO_PathIndex_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\PathLangTable:translate/lib/index/internals/pathlang.php:a346e65b5c3009306f152560cc13e305 */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * EO_PathLang
	 * @see \Bitrix\Translate\Index\Internals\PathLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getPath()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang setPath(\string|\Bitrix\Main\DB\SqlExpression $path)
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \string remindActualPath()
	 * @method \string requirePath()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang resetPath()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang unsetPath()
	 * @method \string fillPath()
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
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang reset($fieldName)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang wakeUp($data)
	 */
	class EO_PathLang {
		/* @var \Bitrix\Translate\Index\Internals\PathLangTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * EO_PathLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getPathList()
	 * @method \string[] fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\Internals\EO_PathLang $object)
	 * @method bool has(\Bitrix\Translate\Index\Internals\EO_PathLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\Internals\EO_PathLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PathLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\PathLangTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathLangTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PathLang_Result exec()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang fetchObject()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PathLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang fetchObject()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang_Collection fetchCollection()
	 */
	class EO_PathLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang_Collection createCollection()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathLang_Collection wakeUpCollection($rows)
	 */
	class EO_PathLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\PathTreeTable:translate/lib/index/internals/pathtree.php:ddbf89fa377b37e6d54ac1150e98f477 */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * EO_PathTree
	 * @see \Bitrix\Translate\Index\Internals\PathTreeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree resetParentId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getPathId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree setPathId(\int|\Bitrix\Main\DB\SqlExpression $pathId)
	 * @method bool hasPathId()
	 * @method bool isPathIdFilled()
	 * @method bool isPathIdChanged()
	 * @method \int remindActualPathId()
	 * @method \int requirePathId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree resetPathId()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree unsetPathId()
	 * @method \int fillPathId()
	 * @method \int getDepthLevel()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree setDepthLevel(\int|\Bitrix\Main\DB\SqlExpression $depthLevel)
	 * @method bool hasDepthLevel()
	 * @method bool isDepthLevelFilled()
	 * @method bool isDepthLevelChanged()
	 * @method \int remindActualDepthLevel()
	 * @method \int requireDepthLevel()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree resetDepthLevel()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree unsetDepthLevel()
	 * @method \int fillDepthLevel()
	 * @method \Bitrix\Translate\Index\PathIndex getPath()
	 * @method \Bitrix\Translate\Index\PathIndex remindActualPath()
	 * @method \Bitrix\Translate\Index\PathIndex requirePath()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree setPath(\Bitrix\Translate\Index\PathIndex $object)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree resetPath()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree unsetPath()
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \Bitrix\Translate\Index\PathIndex fillPath()
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
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree reset($fieldName)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree wakeUp($data)
	 */
	class EO_PathTree {
		/* @var \Bitrix\Translate\Index\Internals\PathTreeTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathTreeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * EO_PathTree_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getPathIdList()
	 * @method \int[] fillPathId()
	 * @method \int[] getDepthLevelList()
	 * @method \int[] fillDepthLevel()
	 * @method \Bitrix\Translate\Index\PathIndex[] getPathList()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection getPathCollection()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\Internals\EO_PathTree $object)
	 * @method bool has(\Bitrix\Translate\Index\Internals\EO_PathTree $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\Internals\EO_PathTree $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PathTree_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\PathTreeTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PathTreeTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PathTree_Result exec()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree fetchObject()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PathTree_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree fetchObject()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection fetchCollection()
	 */
	class EO_PathTree_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection createCollection()
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\Internals\EO_PathTree_Collection wakeUpCollection($rows)
	 */
	class EO_PathTree_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Translate\Index\Internals\PhraseIndexTable:translate/lib/index/internals/phraseindex.php:5e7fd38e9c6f7228a06bd7430b014806 */
namespace Bitrix\Translate\Index\Internals {
	/**
	 * PhraseIndex
	 * @see \Bitrix\Translate\Index\Internals\PhraseIndexTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Translate\Index\PhraseIndex setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Translate\Index\PhraseIndex setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Translate\Index\PhraseIndex resetFileId()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetFileId()
	 * @method \int fillFileId()
	 * @method \string getPathId()
	 * @method \Bitrix\Translate\Index\PhraseIndex setPathId(\string|\Bitrix\Main\DB\SqlExpression $pathId)
	 * @method bool hasPathId()
	 * @method bool isPathIdFilled()
	 * @method bool isPathIdChanged()
	 * @method \string remindActualPathId()
	 * @method \string requirePathId()
	 * @method \Bitrix\Translate\Index\PhraseIndex resetPathId()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetPathId()
	 * @method \string fillPathId()
	 * @method \string getLangId()
	 * @method \Bitrix\Translate\Index\PhraseIndex setLangId(\string|\Bitrix\Main\DB\SqlExpression $langId)
	 * @method bool hasLangId()
	 * @method bool isLangIdFilled()
	 * @method bool isLangIdChanged()
	 * @method \string remindActualLangId()
	 * @method \string requireLangId()
	 * @method \Bitrix\Translate\Index\PhraseIndex resetLangId()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetLangId()
	 * @method \string fillLangId()
	 * @method \string getCode()
	 * @method \Bitrix\Translate\Index\PhraseIndex setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Translate\Index\PhraseIndex resetCode()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetCode()
	 * @method \string fillCode()
	 * @method \string getPhrase()
	 * @method \Bitrix\Translate\Index\PhraseIndex setPhrase(\string|\Bitrix\Main\DB\SqlExpression $phrase)
	 * @method bool hasPhrase()
	 * @method bool isPhraseFilled()
	 * @method bool isPhraseChanged()
	 * @method \string remindActualPhrase()
	 * @method \string requirePhrase()
	 * @method \Bitrix\Translate\Index\PhraseIndex resetPhrase()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetPhrase()
	 * @method \string fillPhrase()
	 * @method \Bitrix\Translate\Index\FileIndex getFile()
	 * @method \Bitrix\Translate\Index\FileIndex remindActualFile()
	 * @method \Bitrix\Translate\Index\FileIndex requireFile()
	 * @method \Bitrix\Translate\Index\PhraseIndex setFile(\Bitrix\Translate\Index\FileIndex $object)
	 * @method \Bitrix\Translate\Index\PhraseIndex resetFile()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Translate\Index\FileIndex fillFile()
	 * @method \Bitrix\Translate\Index\PathIndex getPath()
	 * @method \Bitrix\Translate\Index\PathIndex remindActualPath()
	 * @method \Bitrix\Translate\Index\PathIndex requirePath()
	 * @method \Bitrix\Translate\Index\PhraseIndex setPath(\Bitrix\Translate\Index\PathIndex $object)
	 * @method \Bitrix\Translate\Index\PhraseIndex resetPath()
	 * @method \Bitrix\Translate\Index\PhraseIndex unsetPath()
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \Bitrix\Translate\Index\PathIndex fillPath()
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
	 * @method \Bitrix\Translate\Index\PhraseIndex set($fieldName, $value)
	 * @method \Bitrix\Translate\Index\PhraseIndex reset($fieldName)
	 * @method \Bitrix\Translate\Index\PhraseIndex unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Translate\Index\PhraseIndex wakeUp($data)
	 */
	class EO_PhraseIndex {
		/* @var \Bitrix\Translate\Index\Internals\PhraseIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PhraseIndexTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * PhraseIndexCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \string[] getPathIdList()
	 * @method \string[] fillPathId()
	 * @method \string[] getLangIdList()
	 * @method \string[] fillLangId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getPhraseList()
	 * @method \string[] fillPhrase()
	 * @method \Bitrix\Translate\Index\FileIndex[] getFileList()
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection getFileCollection()
	 * @method \Bitrix\Translate\Index\FileIndexCollection fillFile()
	 * @method \Bitrix\Translate\Index\PathIndex[] getPathList()
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection getPathCollection()
	 * @method \Bitrix\Translate\Index\PathIndexCollection fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Translate\Index\PhraseIndex $object)
	 * @method bool has(\Bitrix\Translate\Index\PhraseIndex $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Translate\Index\PhraseIndex getByPrimary($primary)
	 * @method \Bitrix\Translate\Index\PhraseIndex[] getAll()
	 * @method bool remove(\Bitrix\Translate\Index\PhraseIndex $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Translate\Index\PhraseIndexCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Translate\Index\PhraseIndex current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PhraseIndex_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Translate\Index\Internals\PhraseIndexTable */
		static public $dataClass = '\Bitrix\Translate\Index\Internals\PhraseIndexTable';
	}
}
namespace Bitrix\Translate\Index\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PhraseIndex_Result exec()
	 * @method \Bitrix\Translate\Index\PhraseIndex fetchObject()
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PhraseIndex_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Translate\Index\PhraseIndex fetchObject()
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection fetchCollection()
	 */
	class EO_PhraseIndex_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Translate\Index\PhraseIndex createObject($setDefaultValues = true)
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection createCollection()
	 * @method \Bitrix\Translate\Index\PhraseIndex wakeUpObject($row)
	 * @method \Bitrix\Translate\Index\PhraseIndexCollection wakeUpCollection($rows)
	 */
	class EO_PhraseIndex_Entity extends \Bitrix\Main\ORM\Entity {}
}