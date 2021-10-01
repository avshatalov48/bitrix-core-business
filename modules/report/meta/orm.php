<?php

/* ORMENTITYANNOTATION:Bitrix\Report\Internals\SharingTable:report/lib/internals/sharing.php:5e884b55a4bb55b52ec09f15e9640803 */
namespace Bitrix\Report\Internals {
	/**
	 * EO_Sharing
	 * @see \Bitrix\Report\Internals\SharingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\Internals\EO_Sharing setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getReportId()
	 * @method \Bitrix\Report\Internals\EO_Sharing setReportId(\int|\Bitrix\Main\DB\SqlExpression $reportId)
	 * @method bool hasReportId()
	 * @method bool isReportIdFilled()
	 * @method bool isReportIdChanged()
	 * @method \int remindActualReportId()
	 * @method \int requireReportId()
	 * @method \Bitrix\Report\Internals\EO_Sharing resetReportId()
	 * @method \Bitrix\Report\Internals\EO_Sharing unsetReportId()
	 * @method \int fillReportId()
	 * @method \Bitrix\Report\EO_Report getLinkReport()
	 * @method \Bitrix\Report\EO_Report remindActualLinkReport()
	 * @method \Bitrix\Report\EO_Report requireLinkReport()
	 * @method \Bitrix\Report\Internals\EO_Sharing setLinkReport(\Bitrix\Report\EO_Report $object)
	 * @method \Bitrix\Report\Internals\EO_Sharing resetLinkReport()
	 * @method \Bitrix\Report\Internals\EO_Sharing unsetLinkReport()
	 * @method bool hasLinkReport()
	 * @method bool isLinkReportFilled()
	 * @method bool isLinkReportChanged()
	 * @method \Bitrix\Report\EO_Report fillLinkReport()
	 * @method \string getEntity()
	 * @method \Bitrix\Report\Internals\EO_Sharing setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Report\Internals\EO_Sharing resetEntity()
	 * @method \Bitrix\Report\Internals\EO_Sharing unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getRights()
	 * @method \Bitrix\Report\Internals\EO_Sharing setRights(\string|\Bitrix\Main\DB\SqlExpression $rights)
	 * @method bool hasRights()
	 * @method bool isRightsFilled()
	 * @method bool isRightsChanged()
	 * @method \string remindActualRights()
	 * @method \string requireRights()
	 * @method \Bitrix\Report\Internals\EO_Sharing resetRights()
	 * @method \Bitrix\Report\Internals\EO_Sharing unsetRights()
	 * @method \string fillRights()
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
	 * @method \Bitrix\Report\Internals\EO_Sharing set($fieldName, $value)
	 * @method \Bitrix\Report\Internals\EO_Sharing reset($fieldName)
	 * @method \Bitrix\Report\Internals\EO_Sharing unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\Internals\EO_Sharing wakeUp($data)
	 */
	class EO_Sharing {
		/* @var \Bitrix\Report\Internals\SharingTable */
		static public $dataClass = '\Bitrix\Report\Internals\SharingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\Internals {
	/**
	 * EO_Sharing_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getReportIdList()
	 * @method \int[] fillReportId()
	 * @method \Bitrix\Report\EO_Report[] getLinkReportList()
	 * @method \Bitrix\Report\Internals\EO_Sharing_Collection getLinkReportCollection()
	 * @method \Bitrix\Report\EO_Report_Collection fillLinkReport()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getRightsList()
	 * @method \string[] fillRights()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\Internals\EO_Sharing $object)
	 * @method bool has(\Bitrix\Report\Internals\EO_Sharing $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\Internals\EO_Sharing getByPrimary($primary)
	 * @method \Bitrix\Report\Internals\EO_Sharing[] getAll()
	 * @method bool remove(\Bitrix\Report\Internals\EO_Sharing $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\Internals\EO_Sharing_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\Internals\EO_Sharing current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Sharing_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\Internals\SharingTable */
		static public $dataClass = '\Bitrix\Report\Internals\SharingTable';
	}
}
namespace Bitrix\Report\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Sharing_Result exec()
	 * @method \Bitrix\Report\Internals\EO_Sharing fetchObject()
	 * @method \Bitrix\Report\Internals\EO_Sharing_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Sharing_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\Internals\EO_Sharing fetchObject()
	 * @method \Bitrix\Report\Internals\EO_Sharing_Collection fetchCollection()
	 */
	class EO_Sharing_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\Internals\EO_Sharing createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\Internals\EO_Sharing_Collection createCollection()
	 * @method \Bitrix\Report\Internals\EO_Sharing wakeUpObject($row)
	 * @method \Bitrix\Report\Internals\EO_Sharing_Collection wakeUpCollection($rows)
	 */
	class EO_Sharing_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\ReportTable:report/lib/report.php:104a7bc4daa5991734c1a2a8002e6c7e */
namespace Bitrix\Report {
	/**
	 * EO_Report
	 * @see \Bitrix\Report\ReportTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\EO_Report setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Report\EO_Report setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Report\EO_Report resetOwnerId()
	 * @method \Bitrix\Report\EO_Report unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \string getTitle()
	 * @method \Bitrix\Report\EO_Report setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Report\EO_Report resetTitle()
	 * @method \Bitrix\Report\EO_Report unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDescription()
	 * @method \Bitrix\Report\EO_Report setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Report\EO_Report resetDescription()
	 * @method \Bitrix\Report\EO_Report unsetDescription()
	 * @method \string fillDescription()
	 * @method \Bitrix\Main\Type\DateTime getCreatedDate()
	 * @method \Bitrix\Report\EO_Report setCreatedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedDate()
	 * @method \Bitrix\Report\EO_Report resetCreatedDate()
	 * @method \Bitrix\Report\EO_Report unsetCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedDate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Report\EO_Report setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Report\EO_Report resetCreatedBy()
	 * @method \Bitrix\Report\EO_Report unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Report\EO_Report setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Report\EO_Report resetCreatedByUser()
	 * @method \Bitrix\Report\EO_Report unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \string getSettings()
	 * @method \Bitrix\Report\EO_Report setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Report\EO_Report resetSettings()
	 * @method \Bitrix\Report\EO_Report unsetSettings()
	 * @method \string fillSettings()
	 * @method \int getMarkDefault()
	 * @method \Bitrix\Report\EO_Report setMarkDefault(\int|\Bitrix\Main\DB\SqlExpression $markDefault)
	 * @method bool hasMarkDefault()
	 * @method bool isMarkDefaultFilled()
	 * @method bool isMarkDefaultChanged()
	 * @method \int remindActualMarkDefault()
	 * @method \int requireMarkDefault()
	 * @method \Bitrix\Report\EO_Report resetMarkDefault()
	 * @method \Bitrix\Report\EO_Report unsetMarkDefault()
	 * @method \int fillMarkDefault()
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
	 * @method \Bitrix\Report\EO_Report set($fieldName, $value)
	 * @method \Bitrix\Report\EO_Report reset($fieldName)
	 * @method \Bitrix\Report\EO_Report unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\EO_Report wakeUp($data)
	 */
	class EO_Report {
		/* @var \Bitrix\Report\ReportTable */
		static public $dataClass = '\Bitrix\Report\ReportTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report {
	/**
	 * EO_Report_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedDate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Report\EO_Report_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 * @method \int[] getMarkDefaultList()
	 * @method \int[] fillMarkDefault()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\EO_Report $object)
	 * @method bool has(\Bitrix\Report\EO_Report $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\EO_Report getByPrimary($primary)
	 * @method \Bitrix\Report\EO_Report[] getAll()
	 * @method bool remove(\Bitrix\Report\EO_Report $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\EO_Report_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\EO_Report current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Report_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\ReportTable */
		static public $dataClass = '\Bitrix\Report\ReportTable';
	}
}
namespace Bitrix\Report {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Report_Result exec()
	 * @method \Bitrix\Report\EO_Report fetchObject()
	 * @method \Bitrix\Report\EO_Report_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Report_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\EO_Report fetchObject()
	 * @method \Bitrix\Report\EO_Report_Collection fetchCollection()
	 */
	class EO_Report_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\EO_Report createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\EO_Report_Collection createCollection()
	 * @method \Bitrix\Report\EO_Report wakeUpObject($row)
	 * @method \Bitrix\Report\EO_Report_Collection wakeUpCollection($rows)
	 */
	class EO_Report_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable:report/lib/visualconstructor/internal/configurationsetting.php:3c3dc26059f19fad83810b75edd9dd60 */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_ConfigurationSetting
	 * @see \Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setWeight(\int|\Bitrix\Main\DB\SqlExpression $weight)
	 * @method bool hasWeight()
	 * @method bool isWeightFilled()
	 * @method bool isWeightChanged()
	 * @method \int remindActualWeight()
	 * @method \int requireWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetWeight()
	 * @method \int fillWeight()
	 * @method \string getGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setGid(\string|\Bitrix\Main\DB\SqlExpression $gid)
	 * @method bool hasGid()
	 * @method bool isGidFilled()
	 * @method bool isGidChanged()
	 * @method \string remindActualGid()
	 * @method \string requireGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetGid()
	 * @method \string fillGid()
	 * @method \string getUkey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setUkey(\string|\Bitrix\Main\DB\SqlExpression $ukey)
	 * @method bool hasUkey()
	 * @method bool isUkeyFilled()
	 * @method bool isUkeyChanged()
	 * @method \string remindActualUkey()
	 * @method \string requireUkey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetUkey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetUkey()
	 * @method \string fillUkey()
	 * @method \string getConfigurationFieldClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setConfigurationFieldClass(\string|\Bitrix\Main\DB\SqlExpression $configurationFieldClass)
	 * @method bool hasConfigurationFieldClass()
	 * @method bool isConfigurationFieldClassFilled()
	 * @method bool isConfigurationFieldClassChanged()
	 * @method \string remindActualConfigurationFieldClass()
	 * @method \string requireConfigurationFieldClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetConfigurationFieldClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetConfigurationFieldClass()
	 * @method \string fillConfigurationFieldClass()
	 * @method \string getSettings()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetSettings()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetSettings()
	 * @method \string fillSettings()
	 * @method \Bitrix\Main\Type\Date getCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setCreatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\Date requireCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetCreatedDate()
	 * @method \Bitrix\Main\Type\Date fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date getUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting setUpdatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $updatedDate)
	 * @method bool hasUpdatedDate()
	 * @method bool isUpdatedDateFilled()
	 * @method bool isUpdatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualUpdatedDate()
	 * @method \Bitrix\Main\Type\Date requireUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting resetUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unsetUpdatedDate()
	 * @method \Bitrix\Main\Type\Date fillUpdatedDate()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting wakeUp($data)
	 */
	class EO_ConfigurationSetting {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_ConfigurationSetting_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getWeightList()
	 * @method \int[] fillWeight()
	 * @method \string[] getGidList()
	 * @method \string[] fillGid()
	 * @method \string[] getUkeyList()
	 * @method \string[] fillUkey()
	 * @method \string[] getConfigurationFieldClassList()
	 * @method \string[] fillConfigurationFieldClass()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 * @method \Bitrix\Main\Type\Date[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date[] getUpdatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillUpdatedDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ConfigurationSetting_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ConfigurationSetting_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ConfigurationSetting_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection fetchCollection()
	 */
	class EO_ConfigurationSetting_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection wakeUpCollection($rows)
	 */
	class EO_ConfigurationSetting_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\DashboardTable:report/lib/visualconstructor/internal/dashboard.php:810c301275ec542ed13665425da46c9d */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Dashboard
	 * @see \Bitrix\Report\VisualConstructor\Internal\DashboardTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setGid(\string|\Bitrix\Main\DB\SqlExpression $gid)
	 * @method bool hasGid()
	 * @method bool isGidFilled()
	 * @method bool isGidChanged()
	 * @method \string remindActualGid()
	 * @method \string requireGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetGid()
	 * @method \string fillGid()
	 * @method \string getBoardKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setBoardKey(\string|\Bitrix\Main\DB\SqlExpression $boardKey)
	 * @method bool hasBoardKey()
	 * @method bool isBoardKeyFilled()
	 * @method bool isBoardKeyChanged()
	 * @method \string remindActualBoardKey()
	 * @method \string requireBoardKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetBoardKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetBoardKey()
	 * @method \string fillBoardKey()
	 * @method \int getUserId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetUserId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getVersion()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetVersion()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetVersion()
	 * @method \string fillVersion()
	 * @method \Bitrix\Main\Type\Date getCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setCreatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\Date requireCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetCreatedDate()
	 * @method \Bitrix\Main\Type\Date fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date getUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard setUpdatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $updatedDate)
	 * @method bool hasUpdatedDate()
	 * @method bool isUpdatedDateFilled()
	 * @method bool isUpdatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualUpdatedDate()
	 * @method \Bitrix\Main\Type\Date requireUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard resetUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unsetUpdatedDate()
	 * @method \Bitrix\Main\Type\Date fillUpdatedDate()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard wakeUp($data)
	 */
	class EO_Dashboard {
		/* @var \Bitrix\Report\VisualConstructor\Internal\DashboardTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\DashboardTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Dashboard_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getGidList()
	 * @method \string[] fillGid()
	 * @method \string[] getBoardKeyList()
	 * @method \string[] fillBoardKey()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \Bitrix\Main\Type\Date[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date[] getUpdatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillUpdatedDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_Dashboard $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_Dashboard $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_Dashboard $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Dashboard_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\DashboardTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\DashboardTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Dashboard_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Dashboard_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection fetchCollection()
	 */
	class EO_Dashboard_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection wakeUpCollection($rows)
	 */
	class EO_Dashboard_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\DashboardRowTable:report/lib/visualconstructor/internal/dashboardrow.php:b6a614af0840f58fe01d0aeafc51d9a3 */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_DashboardRow
	 * @see \Bitrix\Report\VisualConstructor\Internal\DashboardRowTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setGid(\string|\Bitrix\Main\DB\SqlExpression $gid)
	 * @method bool hasGid()
	 * @method bool isGidFilled()
	 * @method bool isGidChanged()
	 * @method \string remindActualGid()
	 * @method \string requireGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetGid()
	 * @method \string fillGid()
	 * @method \int getWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setWeight(\int|\Bitrix\Main\DB\SqlExpression $weight)
	 * @method bool hasWeight()
	 * @method bool isWeightFilled()
	 * @method bool isWeightChanged()
	 * @method \int remindActualWeight()
	 * @method \int requireWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetWeight()
	 * @method \int fillWeight()
	 * @method \int getBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setBoardId(\int|\Bitrix\Main\DB\SqlExpression $boardId)
	 * @method bool hasBoardId()
	 * @method bool isBoardIdFilled()
	 * @method bool isBoardIdChanged()
	 * @method \int remindActualBoardId()
	 * @method \int requireBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetBoardId()
	 * @method \int fillBoardId()
	 * @method \string getLayoutMap()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setLayoutMap(\string|\Bitrix\Main\DB\SqlExpression $layoutMap)
	 * @method bool hasLayoutMap()
	 * @method bool isLayoutMapFilled()
	 * @method bool isLayoutMapChanged()
	 * @method \string remindActualLayoutMap()
	 * @method \string requireLayoutMap()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetLayoutMap()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetLayoutMap()
	 * @method \string fillLayoutMap()
	 * @method \Bitrix\Main\Type\Date getCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setCreatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\Date requireCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetCreatedDate()
	 * @method \Bitrix\Main\Type\Date fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date getUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setUpdatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $updatedDate)
	 * @method bool hasUpdatedDate()
	 * @method bool isUpdatedDateFilled()
	 * @method bool isUpdatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualUpdatedDate()
	 * @method \Bitrix\Main\Type\Date requireUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetUpdatedDate()
	 * @method \Bitrix\Main\Type\Date fillUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard getDashboard()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard remindActualDashboard()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard requireDashboard()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow setDashboard(\Bitrix\Report\VisualConstructor\Internal\EO_Dashboard $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow resetDashboard()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unsetDashboard()
	 * @method bool hasDashboard()
	 * @method bool isDashboardFilled()
	 * @method bool isDashboardChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard fillDashboard()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow wakeUp($data)
	 */
	class EO_DashboardRow {
		/* @var \Bitrix\Report\VisualConstructor\Internal\DashboardRowTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\DashboardRowTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_DashboardRow_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getGidList()
	 * @method \string[] fillGid()
	 * @method \int[] getWeightList()
	 * @method \int[] fillWeight()
	 * @method \int[] getBoardIdList()
	 * @method \int[] fillBoardId()
	 * @method \string[] getLayoutMapList()
	 * @method \string[] fillLayoutMap()
	 * @method \Bitrix\Main\Type\Date[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date[] getUpdatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard[] getDashboardList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection getDashboardCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection fillDashboard()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DashboardRow_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\DashboardRowTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\DashboardRowTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DashboardRow_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DashboardRow_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection fetchCollection()
	 */
	class EO_DashboardRow_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection wakeUpCollection($rows)
	 */
	class EO_DashboardRow_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\ReportTable:report/lib/visualconstructor/internal/report.php:6bbe215f5a182b88ab329dfcd4e958fe */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Report
	 * @see \Bitrix\Report\VisualConstructor\Internal\ReportTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setWidgetId(\int|\Bitrix\Main\DB\SqlExpression $widgetId)
	 * @method bool hasWidgetId()
	 * @method bool isWidgetIdFilled()
	 * @method bool isWidgetIdChanged()
	 * @method \int remindActualWidgetId()
	 * @method \int requireWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetWidgetId()
	 * @method \int fillWidgetId()
	 * @method \string getGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setGid(\string|\Bitrix\Main\DB\SqlExpression $gid)
	 * @method bool hasGid()
	 * @method bool isGidFilled()
	 * @method bool isGidChanged()
	 * @method \string remindActualGid()
	 * @method \string requireGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetGid()
	 * @method \string fillGid()
	 * @method \int getWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setWeight(\int|\Bitrix\Main\DB\SqlExpression $weight)
	 * @method bool hasWeight()
	 * @method bool isWeightFilled()
	 * @method bool isWeightChanged()
	 * @method \int remindActualWeight()
	 * @method \int requireWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetWeight()
	 * @method \int fillWeight()
	 * @method \string getReportClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setReportClass(\string|\Bitrix\Main\DB\SqlExpression $reportClass)
	 * @method bool hasReportClass()
	 * @method bool isReportClassFilled()
	 * @method bool isReportClassChanged()
	 * @method \string remindActualReportClass()
	 * @method \string requireReportClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetReportClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetReportClass()
	 * @method \string fillReportClass()
	 * @method \Bitrix\Main\Type\Date getCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setCreatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\Date requireCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetCreatedDate()
	 * @method \Bitrix\Main\Type\Date fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date getUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setUpdatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $updatedDate)
	 * @method bool hasUpdatedDate()
	 * @method bool isUpdatedDateFilled()
	 * @method bool isUpdatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualUpdatedDate()
	 * @method \Bitrix\Main\Type\Date requireUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetUpdatedDate()
	 * @method \Bitrix\Main\Type\Date fillUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget getWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget remindActualWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget requireWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report setWidget(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report resetWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unsetWidget()
	 * @method bool hasWidget()
	 * @method bool isWidgetFilled()
	 * @method bool isWidgetChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget fillWidget()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report wakeUp($data)
	 */
	class EO_Report {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ReportTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ReportTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Report_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getWidgetIdList()
	 * @method \int[] fillWidgetId()
	 * @method \string[] getGidList()
	 * @method \string[] fillGid()
	 * @method \int[] getWeightList()
	 * @method \int[] fillWeight()
	 * @method \string[] getReportClassList()
	 * @method \string[] fillReportClass()
	 * @method \Bitrix\Main\Type\Date[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date[] getUpdatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget[] getWidgetList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection getWidgetCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection fillWidget()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_Report $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_Report $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_Report $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Report_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ReportTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ReportTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Report_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Report_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection fetchCollection()
	 */
	class EO_Report_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection wakeUpCollection($rows)
	 */
	class EO_Report_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable:report/lib/visualconstructor/internal/reportconfiguration.php:ea3c76b456b9ec80e18f5fb1aa41c855 */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_ReportConfiguration
	 * @see \Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getReportId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration setReportId(\int|\Bitrix\Main\DB\SqlExpression $reportId)
	 * @method bool hasReportId()
	 * @method bool isReportIdFilled()
	 * @method bool isReportIdChanged()
	 * @method \int getConfigurationId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration setConfigurationId(\int|\Bitrix\Main\DB\SqlExpression $configurationId)
	 * @method bool hasConfigurationId()
	 * @method bool isConfigurationIdFilled()
	 * @method bool isConfigurationIdChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report getReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report remindActualReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report requireReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration setReport(\Bitrix\Report\VisualConstructor\Internal\EO_Report $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration resetReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration unsetReport()
	 * @method bool hasReport()
	 * @method bool isReportFilled()
	 * @method bool isReportChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report fillReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting getConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting remindActualConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting requireConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration setConfigurationSetting(\Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration resetConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration unsetConfigurationSetting()
	 * @method bool hasConfigurationSetting()
	 * @method bool isConfigurationSettingFilled()
	 * @method bool isConfigurationSettingChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting fillConfigurationSetting()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration wakeUp($data)
	 */
	class EO_ReportConfiguration {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_ReportConfiguration_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getReportIdList()
	 * @method \int[] getConfigurationIdList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report[] getReportList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection getReportCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection fillReport()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting[] getConfigurationSettingList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection getConfigurationSettingCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection fillConfigurationSetting()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ReportConfiguration_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ReportConfiguration_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ReportConfiguration_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection fetchCollection()
	 */
	class EO_ReportConfiguration_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ReportConfiguration_Collection wakeUpCollection($rows)
	 */
	class EO_ReportConfiguration_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\WidgetTable:report/lib/visualconstructor/internal/widget.php:aaa3174182105af80503e387849c41d6 */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Widget
	 * @see \Bitrix\Report\VisualConstructor\Internal\WidgetTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setGid(\string|\Bitrix\Main\DB\SqlExpression $gid)
	 * @method bool hasGid()
	 * @method bool isGidFilled()
	 * @method bool isGidChanged()
	 * @method \string remindActualGid()
	 * @method \string requireGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetGid()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetGid()
	 * @method \string fillGid()
	 * @method \string getBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setBoardId(\string|\Bitrix\Main\DB\SqlExpression $boardId)
	 * @method bool hasBoardId()
	 * @method bool isBoardIdFilled()
	 * @method bool isBoardIdChanged()
	 * @method \string remindActualBoardId()
	 * @method \string requireBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetBoardId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetBoardId()
	 * @method \string fillBoardId()
	 * @method \int getDashboardRowId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setDashboardRowId(\int|\Bitrix\Main\DB\SqlExpression $dashboardRowId)
	 * @method bool hasDashboardRowId()
	 * @method bool isDashboardRowIdFilled()
	 * @method bool isDashboardRowIdChanged()
	 * @method \int remindActualDashboardRowId()
	 * @method \int requireDashboardRowId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetDashboardRowId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetDashboardRowId()
	 * @method \int fillDashboardRowId()
	 * @method \int getParentWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setParentWidgetId(\int|\Bitrix\Main\DB\SqlExpression $parentWidgetId)
	 * @method bool hasParentWidgetId()
	 * @method bool isParentWidgetIdFilled()
	 * @method bool isParentWidgetIdChanged()
	 * @method \int remindActualParentWidgetId()
	 * @method \int requireParentWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetParentWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetParentWidgetId()
	 * @method \int fillParentWidgetId()
	 * @method \string getWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setWeight(\string|\Bitrix\Main\DB\SqlExpression $weight)
	 * @method bool hasWeight()
	 * @method bool isWeightFilled()
	 * @method bool isWeightChanged()
	 * @method \string remindActualWeight()
	 * @method \string requireWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetWeight()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetWeight()
	 * @method \string fillWeight()
	 * @method \string getCategoryKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setCategoryKey(\string|\Bitrix\Main\DB\SqlExpression $categoryKey)
	 * @method bool hasCategoryKey()
	 * @method bool isCategoryKeyFilled()
	 * @method bool isCategoryKeyChanged()
	 * @method \string remindActualCategoryKey()
	 * @method \string requireCategoryKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetCategoryKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetCategoryKey()
	 * @method \string fillCategoryKey()
	 * @method \string getViewKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setViewKey(\string|\Bitrix\Main\DB\SqlExpression $viewKey)
	 * @method bool hasViewKey()
	 * @method bool isViewKeyFilled()
	 * @method bool isViewKeyChanged()
	 * @method \string remindActualViewKey()
	 * @method \string requireViewKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetViewKey()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetViewKey()
	 * @method \string fillViewKey()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetOwnerId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \string getWidgetClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setWidgetClass(\string|\Bitrix\Main\DB\SqlExpression $widgetClass)
	 * @method bool hasWidgetClass()
	 * @method bool isWidgetClassFilled()
	 * @method bool isWidgetClassChanged()
	 * @method \string remindActualWidgetClass()
	 * @method \string requireWidgetClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetWidgetClass()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetWidgetClass()
	 * @method \string fillWidgetClass()
	 * @method \Bitrix\Main\Type\Date getCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setCreatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\Date requireCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetCreatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetCreatedDate()
	 * @method \Bitrix\Main\Type\Date fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date getUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setUpdatedDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $updatedDate)
	 * @method bool hasUpdatedDate()
	 * @method bool isUpdatedDateFilled()
	 * @method bool isUpdatedDateChanged()
	 * @method \Bitrix\Main\Type\Date remindActualUpdatedDate()
	 * @method \Bitrix\Main\Type\Date requireUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetUpdatedDate()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetUpdatedDate()
	 * @method \Bitrix\Main\Type\Date fillUpdatedDate()
	 * @method \boolean getIsPattern()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setIsPattern(\boolean|\Bitrix\Main\DB\SqlExpression $isPattern)
	 * @method bool hasIsPattern()
	 * @method bool isIsPatternFilled()
	 * @method bool isIsPatternChanged()
	 * @method \boolean remindActualIsPattern()
	 * @method \boolean requireIsPattern()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetIsPattern()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetIsPattern()
	 * @method \boolean fillIsPattern()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow getRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow remindActualRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow requireRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setRow(\Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetRow()
	 * @method bool hasRow()
	 * @method bool isRowFilled()
	 * @method bool isRowChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow fillRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget getParentwidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget remindActualParentwidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget requireParentwidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget setParentwidget(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget resetParentwidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unsetParentwidget()
	 * @method bool hasParentwidget()
	 * @method bool isParentwidgetFilled()
	 * @method bool isParentwidgetChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget fillParentwidget()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget wakeUp($data)
	 */
	class EO_Widget {
		/* @var \Bitrix\Report\VisualConstructor\Internal\WidgetTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\WidgetTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_Widget_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getGidList()
	 * @method \string[] fillGid()
	 * @method \string[] getBoardIdList()
	 * @method \string[] fillBoardId()
	 * @method \int[] getDashboardRowIdList()
	 * @method \int[] fillDashboardRowId()
	 * @method \int[] getParentWidgetIdList()
	 * @method \int[] fillParentWidgetId()
	 * @method \string[] getWeightList()
	 * @method \string[] fillWeight()
	 * @method \string[] getCategoryKeyList()
	 * @method \string[] fillCategoryKey()
	 * @method \string[] getViewKeyList()
	 * @method \string[] fillViewKey()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \string[] getWidgetClassList()
	 * @method \string[] fillWidgetClass()
	 * @method \Bitrix\Main\Type\Date[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\Date[] getUpdatedDateList()
	 * @method \Bitrix\Main\Type\Date[] fillUpdatedDate()
	 * @method \boolean[] getIsPatternList()
	 * @method \boolean[] fillIsPattern()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow[] getRowList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection getRowCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection fillRow()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget[] getParentwidgetList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection getParentwidgetCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection fillParentwidget()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Widget_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\WidgetTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\WidgetTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Widget_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Widget_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection fetchCollection()
	 */
	class EO_Widget_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection wakeUpCollection($rows)
	 */
	class EO_Widget_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable:report/lib/visualconstructor/internal/widgetconfiguration.php:daf0c44b7770b0ccb3546e4368471212 */
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_WidgetConfiguration
	 * @see \Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getWidgetId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration setWidgetId(\int|\Bitrix\Main\DB\SqlExpression $widgetId)
	 * @method bool hasWidgetId()
	 * @method bool isWidgetIdFilled()
	 * @method bool isWidgetIdChanged()
	 * @method \int getConfigurationId()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration setConfigurationId(\int|\Bitrix\Main\DB\SqlExpression $configurationId)
	 * @method bool hasConfigurationId()
	 * @method bool isConfigurationIdFilled()
	 * @method bool isConfigurationIdChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget getWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget remindActualWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget requireWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration setWidget(\Bitrix\Report\VisualConstructor\Internal\EO_Widget $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration resetWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration unsetWidget()
	 * @method bool hasWidget()
	 * @method bool isWidgetFilled()
	 * @method bool isWidgetChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget fillWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting getConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting remindActualConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting requireConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration setConfigurationSetting(\Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting $object)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration resetConfigurationSetting()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration unsetConfigurationSetting()
	 * @method bool hasConfigurationSetting()
	 * @method bool isConfigurationSettingFilled()
	 * @method bool isConfigurationSettingChanged()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting fillConfigurationSetting()
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
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration set($fieldName, $value)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration reset($fieldName)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration wakeUp($data)
	 */
	class EO_WidgetConfiguration {
		/* @var \Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * EO_WidgetConfiguration_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getWidgetIdList()
	 * @method \int[] getConfigurationIdList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget[] getWidgetList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection getWidgetCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection fillWidget()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting[] getConfigurationSettingList()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection getConfigurationSettingCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_ConfigurationSetting_Collection fillConfigurationSetting()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration $object)
	 * @method bool has(\Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration getByPrimary($primary)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration[] getAll()
	 * @method bool remove(\Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WidgetConfiguration_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable */
		static public $dataClass = '\Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable';
	}
}
namespace Bitrix\Report\VisualConstructor\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WidgetConfiguration_Result exec()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WidgetConfiguration_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration fetchObject()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection fetchCollection()
	 */
	class EO_WidgetConfiguration_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration createObject($setDefaultValues = true)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection createCollection()
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration wakeUpObject($row)
	 * @method \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection wakeUpCollection($rows)
	 */
	class EO_WidgetConfiguration_Entity extends \Bitrix\Main\ORM\Entity {}
}