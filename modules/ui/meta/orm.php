<?php

/* ORMENTITYANNOTATION:Bitrix\Ui\EntityForm\EntityFormConfigAcTable:ui/lib/entityform/entityformconfigactable.php */
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfigAc
	 * @see \Bitrix\Ui\EntityForm\EntityFormConfigAcTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess getUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess remindActualUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess requireUserAccess()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setUserAccess(\Bitrix\Main\EO_UserAccess $object)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetUserAccess()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetUserAccess()
	 * @method bool hasUserAccess()
	 * @method bool isUserAccessFilled()
	 * @method bool isUserAccessChanged()
	 * @method \Bitrix\Main\EO_UserAccess fillUserAccess()
	 * @method \int getConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setConfigId(\int|\Bitrix\Main\DB\SqlExpression $configId)
	 * @method bool hasConfigId()
	 * @method bool isConfigIdFilled()
	 * @method bool isConfigIdChanged()
	 * @method \int remindActualConfigId()
	 * @method \int requireConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetConfigId()
	 * @method \int fillConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig getConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig remindActualConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig requireConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setConfig(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetConfig()
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fillConfig()
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
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc set($fieldName, $value)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc reset($fieldName)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc wakeUp($data)
	 */
	class EO_EntityFormConfigAc {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigAcTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfigAc_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess[] getUserAccessList()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection getUserAccessCollection()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fillUserAccess()
	 * @method \int[] getConfigIdList()
	 * @method \int[] fillConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig[] getConfigList()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection getConfigCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method bool has(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc getByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc[] getAll()
	 * @method bool remove(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EntityFormConfigAc_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigAcTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable';
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EntityFormConfigAc_Result exec()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EntityFormConfigAc_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection fetchCollection()
	 */
	class EO_EntityFormConfigAc_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc createObject($setDefaultValues = true)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection createCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc wakeUpObject($row)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection wakeUpCollection($rows)
	 */
	class EO_EntityFormConfigAc_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Ui\EntityForm\EntityFormConfigTable:ui/lib/entityform/entityformconfigtable.php */
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfig
	 * @see \Bitrix\Ui\EntityForm\EntityFormConfigTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setCategory(\string|\Bitrix\Main\DB\SqlExpression $category)
	 * @method bool hasCategory()
	 * @method bool isCategoryFilled()
	 * @method bool isCategoryChanged()
	 * @method \string remindActualCategory()
	 * @method \string requireCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetCategory()
	 * @method \string fillCategory()
	 * @method \string getEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setEntityTypeId(\string|\Bitrix\Main\DB\SqlExpression $entityTypeId)
	 * @method bool hasEntityTypeId()
	 * @method bool isEntityTypeIdFilled()
	 * @method bool isEntityTypeIdChanged()
	 * @method \string remindActualEntityTypeId()
	 * @method \string requireEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetEntityTypeId()
	 * @method \string fillEntityTypeId()
	 * @method \string getName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetName()
	 * @method \string fillName()
	 * @method \string getConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setConfig(\string|\Bitrix\Main\DB\SqlExpression $config)
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \string remindActualConfig()
	 * @method \string requireConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetConfig()
	 * @method \string fillConfig()
	 * @method \boolean getCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setCommon(\boolean|\Bitrix\Main\DB\SqlExpression $common)
	 * @method bool hasCommon()
	 * @method bool isCommonFilled()
	 * @method bool isCommonChanged()
	 * @method \boolean remindActualCommon()
	 * @method \boolean requireCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetCommon()
	 * @method \boolean fillCommon()
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
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig set($fieldName, $value)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig reset($fieldName)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig wakeUp($data)
	 */
	class EO_EntityFormConfig {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfig_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCategoryList()
	 * @method \string[] fillCategory()
	 * @method \string[] getEntityTypeIdList()
	 * @method \string[] fillEntityTypeId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getConfigList()
	 * @method \string[] fillConfig()
	 * @method \boolean[] getCommonList()
	 * @method \boolean[] fillCommon()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method bool has(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig getByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig[] getAll()
	 * @method bool remove(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EntityFormConfig_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigTable';
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EntityFormConfig_Result exec()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EntityFormConfig_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fetchCollection()
	 */
	class EO_EntityFormConfig_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig createObject($setDefaultValues = true)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection createCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig wakeUpObject($row)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection wakeUpCollection($rows)
	 */
	class EO_EntityFormConfig_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\UI\FileUploader\TempFileTable:ui/lib/fileuploader/tempfiletable.php */
namespace Bitrix\UI\FileUploader {
	/**
	 * TempFile
	 * @see \Bitrix\UI\FileUploader\TempFileTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\UI\FileUploader\TempFile setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getGuid()
	 * @method \Bitrix\UI\FileUploader\TempFile setGuid(\string|\Bitrix\Main\DB\SqlExpression $guid)
	 * @method bool hasGuid()
	 * @method bool isGuidFilled()
	 * @method bool isGuidChanged()
	 * @method \string remindActualGuid()
	 * @method \string requireGuid()
	 * @method \Bitrix\UI\FileUploader\TempFile resetGuid()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetGuid()
	 * @method \string fillGuid()
	 * @method \int getFileId()
	 * @method \Bitrix\UI\FileUploader\TempFile setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\UI\FileUploader\TempFile resetFileId()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetFileId()
	 * @method \int fillFileId()
	 * @method \string getFilename()
	 * @method \Bitrix\UI\FileUploader\TempFile setFilename(\string|\Bitrix\Main\DB\SqlExpression $filename)
	 * @method bool hasFilename()
	 * @method bool isFilenameFilled()
	 * @method bool isFilenameChanged()
	 * @method \string remindActualFilename()
	 * @method \string requireFilename()
	 * @method \Bitrix\UI\FileUploader\TempFile resetFilename()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetFilename()
	 * @method \string fillFilename()
	 * @method \int getSize()
	 * @method \Bitrix\UI\FileUploader\TempFile setSize(\int|\Bitrix\Main\DB\SqlExpression $size)
	 * @method bool hasSize()
	 * @method bool isSizeFilled()
	 * @method bool isSizeChanged()
	 * @method \int remindActualSize()
	 * @method \int requireSize()
	 * @method \Bitrix\UI\FileUploader\TempFile resetSize()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetSize()
	 * @method \int fillSize()
	 * @method \string getPath()
	 * @method \Bitrix\UI\FileUploader\TempFile setPath(\string|\Bitrix\Main\DB\SqlExpression $path)
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \string remindActualPath()
	 * @method \string requirePath()
	 * @method \Bitrix\UI\FileUploader\TempFile resetPath()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetPath()
	 * @method \string fillPath()
	 * @method \string getMimetype()
	 * @method \Bitrix\UI\FileUploader\TempFile setMimetype(\string|\Bitrix\Main\DB\SqlExpression $mimetype)
	 * @method bool hasMimetype()
	 * @method bool isMimetypeFilled()
	 * @method bool isMimetypeChanged()
	 * @method \string remindActualMimetype()
	 * @method \string requireMimetype()
	 * @method \Bitrix\UI\FileUploader\TempFile resetMimetype()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetMimetype()
	 * @method \string fillMimetype()
	 * @method \int getReceivedSize()
	 * @method \Bitrix\UI\FileUploader\TempFile setReceivedSize(\int|\Bitrix\Main\DB\SqlExpression $receivedSize)
	 * @method bool hasReceivedSize()
	 * @method bool isReceivedSizeFilled()
	 * @method bool isReceivedSizeChanged()
	 * @method \int remindActualReceivedSize()
	 * @method \int requireReceivedSize()
	 * @method \Bitrix\UI\FileUploader\TempFile resetReceivedSize()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetReceivedSize()
	 * @method \int fillReceivedSize()
	 * @method \int getWidth()
	 * @method \Bitrix\UI\FileUploader\TempFile setWidth(\int|\Bitrix\Main\DB\SqlExpression $width)
	 * @method bool hasWidth()
	 * @method bool isWidthFilled()
	 * @method bool isWidthChanged()
	 * @method \int remindActualWidth()
	 * @method \int requireWidth()
	 * @method \Bitrix\UI\FileUploader\TempFile resetWidth()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetWidth()
	 * @method \int fillWidth()
	 * @method \int getHeight()
	 * @method \Bitrix\UI\FileUploader\TempFile setHeight(\int|\Bitrix\Main\DB\SqlExpression $height)
	 * @method bool hasHeight()
	 * @method bool isHeightFilled()
	 * @method bool isHeightChanged()
	 * @method \int remindActualHeight()
	 * @method \int requireHeight()
	 * @method \Bitrix\UI\FileUploader\TempFile resetHeight()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetHeight()
	 * @method \int fillHeight()
	 * @method \int getBucketId()
	 * @method \Bitrix\UI\FileUploader\TempFile setBucketId(\int|\Bitrix\Main\DB\SqlExpression $bucketId)
	 * @method bool hasBucketId()
	 * @method bool isBucketIdFilled()
	 * @method bool isBucketIdChanged()
	 * @method \int remindActualBucketId()
	 * @method \int requireBucketId()
	 * @method \Bitrix\UI\FileUploader\TempFile resetBucketId()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetBucketId()
	 * @method \int fillBucketId()
	 * @method \string getModuleId()
	 * @method \Bitrix\UI\FileUploader\TempFile setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\UI\FileUploader\TempFile resetModuleId()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getController()
	 * @method \Bitrix\UI\FileUploader\TempFile setController(\string|\Bitrix\Main\DB\SqlExpression $controller)
	 * @method bool hasController()
	 * @method bool isControllerFilled()
	 * @method bool isControllerChanged()
	 * @method \string remindActualController()
	 * @method \string requireController()
	 * @method \Bitrix\UI\FileUploader\TempFile resetController()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetController()
	 * @method \string fillController()
	 * @method \boolean getCloud()
	 * @method \Bitrix\UI\FileUploader\TempFile setCloud(\boolean|\Bitrix\Main\DB\SqlExpression $cloud)
	 * @method bool hasCloud()
	 * @method bool isCloudFilled()
	 * @method bool isCloudChanged()
	 * @method \boolean remindActualCloud()
	 * @method \boolean requireCloud()
	 * @method \Bitrix\UI\FileUploader\TempFile resetCloud()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetCloud()
	 * @method \boolean fillCloud()
	 * @method \boolean getUploaded()
	 * @method \Bitrix\UI\FileUploader\TempFile setUploaded(\boolean|\Bitrix\Main\DB\SqlExpression $uploaded)
	 * @method bool hasUploaded()
	 * @method bool isUploadedFilled()
	 * @method bool isUploadedChanged()
	 * @method \boolean remindActualUploaded()
	 * @method \boolean requireUploaded()
	 * @method \Bitrix\UI\FileUploader\TempFile resetUploaded()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetUploaded()
	 * @method \boolean fillUploaded()
	 * @method \boolean getDeleted()
	 * @method \Bitrix\UI\FileUploader\TempFile setDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \boolean remindActualDeleted()
	 * @method \boolean requireDeleted()
	 * @method \Bitrix\UI\FileUploader\TempFile resetDeleted()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetDeleted()
	 * @method \boolean fillDeleted()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\UI\FileUploader\TempFile setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\UI\FileUploader\TempFile resetCreatedBy()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getCreatedAt()
	 * @method \Bitrix\UI\FileUploader\TempFile setCreatedAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdAt)
	 * @method bool hasCreatedAt()
	 * @method bool isCreatedAtFilled()
	 * @method bool isCreatedAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedAt()
	 * @method \Bitrix\UI\FileUploader\TempFile resetCreatedAt()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedAt()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\UI\FileUploader\TempFile setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\UI\FileUploader\TempFile resetFile()
	 * @method \Bitrix\UI\FileUploader\TempFile unsetFile()
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
	 * @method \Bitrix\UI\FileUploader\TempFile set($fieldName, $value)
	 * @method \Bitrix\UI\FileUploader\TempFile reset($fieldName)
	 * @method \Bitrix\UI\FileUploader\TempFile unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\UI\FileUploader\TempFile wakeUp($data)
	 */
	class EO_TempFile {
		/* @var \Bitrix\UI\FileUploader\TempFileTable */
		static public $dataClass = '\Bitrix\UI\FileUploader\TempFileTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\UI\FileUploader {
	/**
	 * EO_TempFile_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getGuidList()
	 * @method \string[] fillGuid()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \string[] getFilenameList()
	 * @method \string[] fillFilename()
	 * @method \int[] getSizeList()
	 * @method \int[] fillSize()
	 * @method \string[] getPathList()
	 * @method \string[] fillPath()
	 * @method \string[] getMimetypeList()
	 * @method \string[] fillMimetype()
	 * @method \int[] getReceivedSizeList()
	 * @method \int[] fillReceivedSize()
	 * @method \int[] getWidthList()
	 * @method \int[] fillWidth()
	 * @method \int[] getHeightList()
	 * @method \int[] fillHeight()
	 * @method \int[] getBucketIdList()
	 * @method \int[] fillBucketId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getControllerList()
	 * @method \string[] fillController()
	 * @method \boolean[] getCloudList()
	 * @method \boolean[] fillCloud()
	 * @method \boolean[] getUploadedList()
	 * @method \boolean[] fillUploaded()
	 * @method \boolean[] getDeletedList()
	 * @method \boolean[] fillDeleted()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedAt()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\UI\FileUploader\EO_TempFile_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\UI\FileUploader\TempFile $object)
	 * @method bool has(\Bitrix\UI\FileUploader\TempFile $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\UI\FileUploader\TempFile getByPrimary($primary)
	 * @method \Bitrix\UI\FileUploader\TempFile[] getAll()
	 * @method bool remove(\Bitrix\UI\FileUploader\TempFile $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\UI\FileUploader\EO_TempFile_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\UI\FileUploader\TempFile current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TempFile_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\UI\FileUploader\TempFileTable */
		static public $dataClass = '\Bitrix\UI\FileUploader\TempFileTable';
	}
}
namespace Bitrix\UI\FileUploader {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TempFile_Result exec()
	 * @method \Bitrix\UI\FileUploader\TempFile fetchObject()
	 * @method \Bitrix\UI\FileUploader\EO_TempFile_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TempFile_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\UI\FileUploader\TempFile fetchObject()
	 * @method \Bitrix\UI\FileUploader\EO_TempFile_Collection fetchCollection()
	 */
	class EO_TempFile_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\UI\FileUploader\TempFile createObject($setDefaultValues = true)
	 * @method \Bitrix\UI\FileUploader\EO_TempFile_Collection createCollection()
	 * @method \Bitrix\UI\FileUploader\TempFile wakeUpObject($row)
	 * @method \Bitrix\UI\FileUploader\EO_TempFile_Collection wakeUpCollection($rows)
	 */
	class EO_TempFile_Entity extends \Bitrix\Main\ORM\Entity {}
}