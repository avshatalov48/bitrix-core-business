<?php

/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\BindingTable:landing/lib/internals/binding.php:1cb462769aebd6b061b9a9b55d37ba03 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Binding
	 * @see \Bitrix\Landing\Internals\BindingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Binding setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Binding setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Binding resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Binding unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Binding setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Binding resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Binding unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \string getBindingId()
	 * @method \Bitrix\Landing\Internals\EO_Binding setBindingId(\string|\Bitrix\Main\DB\SqlExpression $bindingId)
	 * @method bool hasBindingId()
	 * @method bool isBindingIdFilled()
	 * @method bool isBindingIdChanged()
	 * @method \string remindActualBindingId()
	 * @method \string requireBindingId()
	 * @method \Bitrix\Landing\Internals\EO_Binding resetBindingId()
	 * @method \Bitrix\Landing\Internals\EO_Binding unsetBindingId()
	 * @method \string fillBindingId()
	 * @method \string getBindingType()
	 * @method \Bitrix\Landing\Internals\EO_Binding setBindingType(\string|\Bitrix\Main\DB\SqlExpression $bindingType)
	 * @method bool hasBindingType()
	 * @method bool isBindingTypeFilled()
	 * @method bool isBindingTypeChanged()
	 * @method \string remindActualBindingType()
	 * @method \string requireBindingType()
	 * @method \Bitrix\Landing\Internals\EO_Binding resetBindingType()
	 * @method \Bitrix\Landing\Internals\EO_Binding unsetBindingType()
	 * @method \string fillBindingType()
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
	 * @method \Bitrix\Landing\Internals\EO_Binding set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Binding reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Binding unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Binding wakeUp($data)
	 */
	class EO_Binding {
		/* @var \Bitrix\Landing\Internals\BindingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\BindingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Binding_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \string[] getBindingIdList()
	 * @method \string[] fillBindingId()
	 * @method \string[] getBindingTypeList()
	 * @method \string[] fillBindingType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Binding $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Binding $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Binding getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Binding[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Binding $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Binding_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Binding current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Binding_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\BindingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\BindingTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Binding_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Binding fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Binding_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Binding_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Binding fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Binding_Collection fetchCollection()
	 */
	class EO_Binding_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Binding createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Binding_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Binding wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Binding_Collection wakeUpCollection($rows)
	 */
	class EO_Binding_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\BlockTable:landing/lib/internals/block.php:752f04cfc48ed43205c88cfc9734b807 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Block
	 * @see \Bitrix\Landing\Internals\BlockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Block setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Landing\Internals\EO_Block setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Landing\Internals\EO_Block resetParentId()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getLid()
	 * @method \Bitrix\Landing\Internals\EO_Block setLid(\int|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \int remindActualLid()
	 * @method \int requireLid()
	 * @method \Bitrix\Landing\Internals\EO_Block resetLid()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetLid()
	 * @method \int fillLid()
	 * @method \Bitrix\Landing\Internals\EO_Landing getLanding()
	 * @method \Bitrix\Landing\Internals\EO_Landing remindActualLanding()
	 * @method \Bitrix\Landing\Internals\EO_Landing requireLanding()
	 * @method \Bitrix\Landing\Internals\EO_Block setLanding(\Bitrix\Landing\Internals\EO_Landing $object)
	 * @method \Bitrix\Landing\Internals\EO_Block resetLanding()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetLanding()
	 * @method bool hasLanding()
	 * @method bool isLandingFilled()
	 * @method bool isLandingChanged()
	 * @method \Bitrix\Landing\Internals\EO_Landing fillLanding()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_Block setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_Block resetCode()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetCode()
	 * @method \string fillCode()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Block setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Block resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Block setInitiatorAppCode(\string|\Bitrix\Main\DB\SqlExpression $initiatorAppCode)
	 * @method bool hasInitiatorAppCode()
	 * @method bool isInitiatorAppCodeFilled()
	 * @method bool isInitiatorAppCodeChanged()
	 * @method \string remindActualInitiatorAppCode()
	 * @method \string requireInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Block resetInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetInitiatorAppCode()
	 * @method \string fillInitiatorAppCode()
	 * @method \string getAnchor()
	 * @method \Bitrix\Landing\Internals\EO_Block setAnchor(\string|\Bitrix\Main\DB\SqlExpression $anchor)
	 * @method bool hasAnchor()
	 * @method bool isAnchorFilled()
	 * @method bool isAnchorChanged()
	 * @method \string remindActualAnchor()
	 * @method \string requireAnchor()
	 * @method \Bitrix\Landing\Internals\EO_Block resetAnchor()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetAnchor()
	 * @method \string fillAnchor()
	 * @method \int getSort()
	 * @method \Bitrix\Landing\Internals\EO_Block setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Landing\Internals\EO_Block resetSort()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetSort()
	 * @method \int fillSort()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Block setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Block resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetActive()
	 * @method \string fillActive()
	 * @method \string getPublic()
	 * @method \Bitrix\Landing\Internals\EO_Block setPublic(\string|\Bitrix\Main\DB\SqlExpression $public)
	 * @method bool hasPublic()
	 * @method bool isPublicFilled()
	 * @method bool isPublicChanged()
	 * @method \string remindActualPublic()
	 * @method \string requirePublic()
	 * @method \Bitrix\Landing\Internals\EO_Block resetPublic()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetPublic()
	 * @method \string fillPublic()
	 * @method \string getDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Block setDeleted(\string|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \string remindActualDeleted()
	 * @method \string requireDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Block resetDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetDeleted()
	 * @method \string fillDeleted()
	 * @method \string getDesigned()
	 * @method \Bitrix\Landing\Internals\EO_Block setDesigned(\string|\Bitrix\Main\DB\SqlExpression $designed)
	 * @method bool hasDesigned()
	 * @method bool isDesignedFilled()
	 * @method bool isDesignedChanged()
	 * @method \string remindActualDesigned()
	 * @method \string requireDesigned()
	 * @method \Bitrix\Landing\Internals\EO_Block resetDesigned()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetDesigned()
	 * @method \string fillDesigned()
	 * @method \string getAccess()
	 * @method \Bitrix\Landing\Internals\EO_Block setAccess(\string|\Bitrix\Main\DB\SqlExpression $access)
	 * @method bool hasAccess()
	 * @method bool isAccessFilled()
	 * @method bool isAccessChanged()
	 * @method \string remindActualAccess()
	 * @method \string requireAccess()
	 * @method \Bitrix\Landing\Internals\EO_Block resetAccess()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetAccess()
	 * @method \string fillAccess()
	 * @method array getSourceParams()
	 * @method \Bitrix\Landing\Internals\EO_Block setSourceParams(array|\Bitrix\Main\DB\SqlExpression $sourceParams)
	 * @method bool hasSourceParams()
	 * @method bool isSourceParamsFilled()
	 * @method bool isSourceParamsChanged()
	 * @method array remindActualSourceParams()
	 * @method array requireSourceParams()
	 * @method \Bitrix\Landing\Internals\EO_Block resetSourceParams()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetSourceParams()
	 * @method array fillSourceParams()
	 * @method \string getContent()
	 * @method \Bitrix\Landing\Internals\EO_Block setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Landing\Internals\EO_Block resetContent()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetContent()
	 * @method \string fillContent()
	 * @method \string getSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Block setSearchContent(\string|\Bitrix\Main\DB\SqlExpression $searchContent)
	 * @method bool hasSearchContent()
	 * @method bool isSearchContentFilled()
	 * @method bool isSearchContentChanged()
	 * @method \string remindActualSearchContent()
	 * @method \string requireSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Block resetSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetSearchContent()
	 * @method \string fillSearchContent()
	 * @method array getAssets()
	 * @method \Bitrix\Landing\Internals\EO_Block setAssets(array|\Bitrix\Main\DB\SqlExpression $assets)
	 * @method bool hasAssets()
	 * @method bool isAssetsFilled()
	 * @method bool isAssetsChanged()
	 * @method array remindActualAssets()
	 * @method array requireAssets()
	 * @method \Bitrix\Landing\Internals\EO_Block resetAssets()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetAssets()
	 * @method array fillAssets()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Block setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Block resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Block setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Block resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Block setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Block resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Block setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Block resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Block unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Block set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Block reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Block unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Block wakeUp($data)
	 */
	class EO_Block {
		/* @var \Bitrix\Landing\Internals\BlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\BlockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Block_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getLidList()
	 * @method \int[] fillLid()
	 * @method \Bitrix\Landing\Internals\EO_Landing[] getLandingList()
	 * @method \Bitrix\Landing\Internals\EO_Block_Collection getLandingCollection()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection fillLanding()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getInitiatorAppCodeList()
	 * @method \string[] fillInitiatorAppCode()
	 * @method \string[] getAnchorList()
	 * @method \string[] fillAnchor()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getPublicList()
	 * @method \string[] fillPublic()
	 * @method \string[] getDeletedList()
	 * @method \string[] fillDeleted()
	 * @method \string[] getDesignedList()
	 * @method \string[] fillDesigned()
	 * @method \string[] getAccessList()
	 * @method \string[] fillAccess()
	 * @method array[] getSourceParamsList()
	 * @method array[] fillSourceParams()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \string[] getSearchContentList()
	 * @method \string[] fillSearchContent()
	 * @method array[] getAssetsList()
	 * @method array[] fillAssets()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Block $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Block $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Block getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Block[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Block $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Block_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Block current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Block_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\BlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\BlockTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Block_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Block fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Block_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Block_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Block fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Block_Collection fetchCollection()
	 */
	class EO_Block_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Block createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Block_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Block wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Block_Collection wakeUpCollection($rows)
	 */
	class EO_Block_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\ChatTable:landing/lib/internals/chat.php:8be0fd1528ea85b7f3ca2d7c7614cc76 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Chat
	 * @see \Bitrix\Landing\Internals\ChatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Chat setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getChatId()
	 * @method \Bitrix\Landing\Internals\EO_Chat setChatId(\int|\Bitrix\Main\DB\SqlExpression $chatId)
	 * @method bool hasChatId()
	 * @method bool isChatIdFilled()
	 * @method bool isChatIdChanged()
	 * @method \int remindActualChatId()
	 * @method \int requireChatId()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetChatId()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetChatId()
	 * @method \int fillChatId()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Chat setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getAvatar()
	 * @method \Bitrix\Landing\Internals\EO_Chat setAvatar(\int|\Bitrix\Main\DB\SqlExpression $avatar)
	 * @method bool hasAvatar()
	 * @method bool isAvatarFilled()
	 * @method bool isAvatarChanged()
	 * @method \int remindActualAvatar()
	 * @method \int requireAvatar()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetAvatar()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetAvatar()
	 * @method \int fillAvatar()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Chat setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Chat resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Chat setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Chat resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Chat setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Chat setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Chat resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Chat unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Chat set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Chat reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Chat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Chat wakeUp($data)
	 */
	class EO_Chat {
		/* @var \Bitrix\Landing\Internals\ChatTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ChatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Chat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getChatIdList()
	 * @method \int[] fillChatId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getAvatarList()
	 * @method \int[] fillAvatar()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Chat $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Chat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Chat getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Chat[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Chat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Chat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Chat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Chat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\ChatTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ChatTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Chat_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Chat fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Chat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Chat fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection fetchCollection()
	 */
	class EO_Chat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Chat createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Chat wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Chat_Collection wakeUpCollection($rows)
	 */
	class EO_Chat_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\ChatBindingTable:landing/lib/internals/chatbinding.php:0fa6699edec8d480693372a318c5ae6a */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_ChatBinding
	 * @see \Bitrix\Landing\Internals\ChatBindingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getInternalChatId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding setInternalChatId(\int|\Bitrix\Main\DB\SqlExpression $internalChatId)
	 * @method bool hasInternalChatId()
	 * @method bool isInternalChatIdFilled()
	 * @method bool isInternalChatIdChanged()
	 * @method \int remindActualInternalChatId()
	 * @method \int requireInternalChatId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding resetInternalChatId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding unsetInternalChatId()
	 * @method \int fillInternalChatId()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding unsetEntityType()
	 * @method \string fillEntityType()
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
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_ChatBinding wakeUp($data)
	 */
	class EO_ChatBinding {
		/* @var \Bitrix\Landing\Internals\ChatBindingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ChatBindingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_ChatBinding_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getInternalChatIdList()
	 * @method \int[] fillInternalChatId()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_ChatBinding $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_ChatBinding $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_ChatBinding $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_ChatBinding_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ChatBinding_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\ChatBindingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ChatBindingTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ChatBinding_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ChatBinding_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding_Collection fetchCollection()
	 */
	class EO_ChatBinding_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_ChatBinding_Collection wakeUpCollection($rows)
	 */
	class EO_ChatBinding_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\CookiesAgreementTable:landing/lib/internals/cookiesagreement.php:59770097752803348a9deafc49ede860 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_CookiesAgreement
	 * @see \Bitrix\Landing\Internals\CookiesAgreementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetActive()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetActive()
	 * @method \string fillActive()
	 * @method \int getSiteId()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setSiteId(\int|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \int remindActualSiteId()
	 * @method \int requireSiteId()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetSiteId()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetSiteId()
	 * @method \int fillSiteId()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetCode()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetCode()
	 * @method \string fillCode()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getContent()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetContent()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetContent()
	 * @method \string fillContent()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_CookiesAgreement wakeUp($data)
	 */
	class EO_CookiesAgreement {
		/* @var \Bitrix\Landing\Internals\CookiesAgreementTable */
		static public $dataClass = '\Bitrix\Landing\Internals\CookiesAgreementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_CookiesAgreement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \int[] getSiteIdList()
	 * @method \int[] fillSiteId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_CookiesAgreement $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_CookiesAgreement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_CookiesAgreement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_CookiesAgreement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CookiesAgreement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\CookiesAgreementTable */
		static public $dataClass = '\Bitrix\Landing\Internals\CookiesAgreementTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CookiesAgreement_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CookiesAgreement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement_Collection fetchCollection()
	 */
	class EO_CookiesAgreement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_CookiesAgreement_Collection wakeUpCollection($rows)
	 */
	class EO_CookiesAgreement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\DemosTable:landing/lib/internals/demos.php:5c34aa81fb80288f773835301a29838f */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Demos
	 * @see \Bitrix\Landing\Internals\DemosTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Demos setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Demos setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Demos setAppCode(\string|\Bitrix\Main\DB\SqlExpression $appCode)
	 * @method bool hasAppCode()
	 * @method bool isAppCodeFilled()
	 * @method bool isAppCodeChanged()
	 * @method \string remindActualAppCode()
	 * @method \string requireAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetAppCode()
	 * @method \string fillAppCode()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Demos setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetActive()
	 * @method \string fillActive()
	 * @method \string getType()
	 * @method \Bitrix\Landing\Internals\EO_Demos setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetType()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetType()
	 * @method \string fillType()
	 * @method \string getTplType()
	 * @method \Bitrix\Landing\Internals\EO_Demos setTplType(\string|\Bitrix\Main\DB\SqlExpression $tplType)
	 * @method bool hasTplType()
	 * @method bool isTplTypeFilled()
	 * @method bool isTplTypeChanged()
	 * @method \string remindActualTplType()
	 * @method \string requireTplType()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetTplType()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetTplType()
	 * @method \string fillTplType()
	 * @method \string getShowInList()
	 * @method \Bitrix\Landing\Internals\EO_Demos setShowInList(\string|\Bitrix\Main\DB\SqlExpression $showInList)
	 * @method bool hasShowInList()
	 * @method bool isShowInListFilled()
	 * @method bool isShowInListChanged()
	 * @method \string remindActualShowInList()
	 * @method \string requireShowInList()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetShowInList()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetShowInList()
	 * @method \string fillShowInList()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Demos setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDescription()
	 * @method \Bitrix\Landing\Internals\EO_Demos setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetDescription()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getPreviewUrl()
	 * @method \Bitrix\Landing\Internals\EO_Demos setPreviewUrl(\string|\Bitrix\Main\DB\SqlExpression $previewUrl)
	 * @method bool hasPreviewUrl()
	 * @method bool isPreviewUrlFilled()
	 * @method bool isPreviewUrlChanged()
	 * @method \string remindActualPreviewUrl()
	 * @method \string requirePreviewUrl()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetPreviewUrl()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetPreviewUrl()
	 * @method \string fillPreviewUrl()
	 * @method \string getPreview()
	 * @method \Bitrix\Landing\Internals\EO_Demos setPreview(\string|\Bitrix\Main\DB\SqlExpression $preview)
	 * @method bool hasPreview()
	 * @method bool isPreviewFilled()
	 * @method bool isPreviewChanged()
	 * @method \string remindActualPreview()
	 * @method \string requirePreview()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetPreview()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetPreview()
	 * @method \string fillPreview()
	 * @method \string getPreview2x()
	 * @method \Bitrix\Landing\Internals\EO_Demos setPreview2x(\string|\Bitrix\Main\DB\SqlExpression $preview2x)
	 * @method bool hasPreview2x()
	 * @method bool isPreview2xFilled()
	 * @method bool isPreview2xChanged()
	 * @method \string remindActualPreview2x()
	 * @method \string requirePreview2x()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetPreview2x()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetPreview2x()
	 * @method \string fillPreview2x()
	 * @method \string getPreview3x()
	 * @method \Bitrix\Landing\Internals\EO_Demos setPreview3x(\string|\Bitrix\Main\DB\SqlExpression $preview3x)
	 * @method bool hasPreview3x()
	 * @method bool isPreview3xFilled()
	 * @method bool isPreview3xChanged()
	 * @method \string remindActualPreview3x()
	 * @method \string requirePreview3x()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetPreview3x()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetPreview3x()
	 * @method \string fillPreview3x()
	 * @method \string getManifest()
	 * @method \Bitrix\Landing\Internals\EO_Demos setManifest(\string|\Bitrix\Main\DB\SqlExpression $manifest)
	 * @method bool hasManifest()
	 * @method bool isManifestFilled()
	 * @method bool isManifestChanged()
	 * @method \string remindActualManifest()
	 * @method \string requireManifest()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetManifest()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetManifest()
	 * @method \string fillManifest()
	 * @method \string getLang()
	 * @method \Bitrix\Landing\Internals\EO_Demos setLang(\string|\Bitrix\Main\DB\SqlExpression $lang)
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \string remindActualLang()
	 * @method \string requireLang()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetLang()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetLang()
	 * @method \string fillLang()
	 * @method \string getSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Demos setSiteTemplateId(\string|\Bitrix\Main\DB\SqlExpression $siteTemplateId)
	 * @method bool hasSiteTemplateId()
	 * @method bool isSiteTemplateIdFilled()
	 * @method bool isSiteTemplateIdChanged()
	 * @method \string remindActualSiteTemplateId()
	 * @method \string requireSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetSiteTemplateId()
	 * @method \string fillSiteTemplateId()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Demos setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Demos setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Demos resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Demos unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Demos set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Demos reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Demos unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Demos wakeUp($data)
	 */
	class EO_Demos {
		/* @var \Bitrix\Landing\Internals\DemosTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DemosTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Demos_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getAppCodeList()
	 * @method \string[] fillAppCode()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getTplTypeList()
	 * @method \string[] fillTplType()
	 * @method \string[] getShowInListList()
	 * @method \string[] fillShowInList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getPreviewUrlList()
	 * @method \string[] fillPreviewUrl()
	 * @method \string[] getPreviewList()
	 * @method \string[] fillPreview()
	 * @method \string[] getPreview2xList()
	 * @method \string[] fillPreview2x()
	 * @method \string[] getPreview3xList()
	 * @method \string[] fillPreview3x()
	 * @method \string[] getManifestList()
	 * @method \string[] fillManifest()
	 * @method \string[] getLangList()
	 * @method \string[] fillLang()
	 * @method \string[] getSiteTemplateIdList()
	 * @method \string[] fillSiteTemplateId()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Demos $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Demos $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Demos getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Demos[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Demos $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Demos_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Demos current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Demos_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\DemosTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DemosTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Demos_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Demos fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Demos_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Demos_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Demos fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Demos_Collection fetchCollection()
	 */
	class EO_Demos_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Demos createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Demos_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Demos wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Demos_Collection wakeUpCollection($rows)
	 */
	class EO_Demos_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\DesignerRepoTable:landing/lib/internals/designerrepo.php:45fe5a9a94343906536f8028bcb0107a */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_DesignerRepo
	 * @see \Bitrix\Landing\Internals\DesignerRepoTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getSort()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetSort()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetSort()
	 * @method \int fillSort()
	 * @method \string getHtml()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setHtml(\string|\Bitrix\Main\DB\SqlExpression $html)
	 * @method bool hasHtml()
	 * @method bool isHtmlFilled()
	 * @method bool isHtmlChanged()
	 * @method \string remindActualHtml()
	 * @method \string requireHtml()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetHtml()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetHtml()
	 * @method \string fillHtml()
	 * @method array getManifest()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setManifest(array|\Bitrix\Main\DB\SqlExpression $manifest)
	 * @method bool hasManifest()
	 * @method bool isManifestFilled()
	 * @method bool isManifestChanged()
	 * @method array remindActualManifest()
	 * @method array requireManifest()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetManifest()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetManifest()
	 * @method array fillManifest()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo wakeUp($data)
	 */
	class EO_DesignerRepo {
		/* @var \Bitrix\Landing\Internals\DesignerRepoTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DesignerRepoTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_DesignerRepo_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getHtmlList()
	 * @method \string[] fillHtml()
	 * @method array[] getManifestList()
	 * @method array[] fillManifest()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_DesignerRepo $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_DesignerRepo $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_DesignerRepo $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DesignerRepo_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\DesignerRepoTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DesignerRepoTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DesignerRepo_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DesignerRepo_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo_Collection fetchCollection()
	 */
	class EO_DesignerRepo_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_DesignerRepo_Collection wakeUpCollection($rows)
	 */
	class EO_DesignerRepo_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\DomainTable:landing/lib/internals/domain.php:3760372ea6e056d2f0fc760e01d378a8 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Domain
	 * @see \Bitrix\Landing\Internals\DomainTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Domain setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Domain setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetActive()
	 * @method \string fillActive()
	 * @method \string getDomain()
	 * @method \Bitrix\Landing\Internals\EO_Domain setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetDomain()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Domain setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getProtocol()
	 * @method \Bitrix\Landing\Internals\EO_Domain setProtocol(\string|\Bitrix\Main\DB\SqlExpression $protocol)
	 * @method bool hasProtocol()
	 * @method bool isProtocolFilled()
	 * @method bool isProtocolChanged()
	 * @method \string remindActualProtocol()
	 * @method \string requireProtocol()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetProtocol()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetProtocol()
	 * @method \string fillProtocol()
	 * @method \string getProvider()
	 * @method \Bitrix\Landing\Internals\EO_Domain setProvider(\string|\Bitrix\Main\DB\SqlExpression $provider)
	 * @method bool hasProvider()
	 * @method bool isProviderFilled()
	 * @method bool isProviderChanged()
	 * @method \string remindActualProvider()
	 * @method \string requireProvider()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetProvider()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetProvider()
	 * @method \string fillProvider()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Domain setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Domain resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Domain setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Domain resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Domain setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Domain setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Domain resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Domain unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Domain set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Domain reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Domain unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Domain wakeUp($data)
	 */
	class EO_Domain {
		/* @var \Bitrix\Landing\Internals\DomainTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DomainTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Domain_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getProtocolList()
	 * @method \string[] fillProtocol()
	 * @method \string[] getProviderList()
	 * @method \string[] fillProvider()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Domain $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Domain $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Domain getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Domain[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Domain $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Domain_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Domain current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Domain_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\DomainTable */
		static public $dataClass = '\Bitrix\Landing\Internals\DomainTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Domain_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Domain fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Domain_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Domain fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection fetchCollection()
	 */
	class EO_Domain_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Domain createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Domain wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection wakeUpCollection($rows)
	 */
	class EO_Domain_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\FileTable:landing/lib/internals/file.php:ec9c8ec33670e7c7c0a9497e080f7d7a */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_File
	 * @see \Bitrix\Landing\Internals\FileTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_File setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_File setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_File resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_File unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_File setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_File resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_File unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getFileId()
	 * @method \Bitrix\Landing\Internals\EO_File setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Landing\Internals\EO_File resetFileId()
	 * @method \Bitrix\Landing\Internals\EO_File unsetFileId()
	 * @method \int fillFileId()
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
	 * @method \Bitrix\Landing\Internals\EO_File set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_File reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_File unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_File wakeUp($data)
	 */
	class EO_File {
		/* @var \Bitrix\Landing\Internals\FileTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FileTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_File_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_File $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_File $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_File getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_File[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_File $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_File_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_File current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_File_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\FileTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FileTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_File_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_File fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_File_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_File_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_File fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_File_Collection fetchCollection()
	 */
	class EO_File_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_File createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_File_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_File wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_File_Collection wakeUpCollection($rows)
	 */
	class EO_File_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\FilterBlockTable:landing/lib/internals/filterblock.php:b4dd76ae731c883b07640719c2c99dac */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_FilterBlock
	 * @see \Bitrix\Landing\Internals\FilterBlockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFilterId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock setFilterId(\int|\Bitrix\Main\DB\SqlExpression $filterId)
	 * @method bool hasFilterId()
	 * @method bool isFilterIdFilled()
	 * @method bool isFilterIdChanged()
	 * @method \int remindActualFilterId()
	 * @method \int requireFilterId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock resetFilterId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock unsetFilterId()
	 * @method \int fillFilterId()
	 * @method \int getBlockId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock setBlockId(\int|\Bitrix\Main\DB\SqlExpression $blockId)
	 * @method bool hasBlockId()
	 * @method bool isBlockIdFilled()
	 * @method bool isBlockIdChanged()
	 * @method \int remindActualBlockId()
	 * @method \int requireBlockId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock resetBlockId()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock unsetBlockId()
	 * @method \int fillBlockId()
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
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_FilterBlock wakeUp($data)
	 */
	class EO_FilterBlock {
		/* @var \Bitrix\Landing\Internals\FilterBlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FilterBlockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_FilterBlock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFilterIdList()
	 * @method \int[] fillFilterId()
	 * @method \int[] getBlockIdList()
	 * @method \int[] fillBlockId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_FilterBlock $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_FilterBlock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_FilterBlock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_FilterBlock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FilterBlock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\FilterBlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FilterBlockTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FilterBlock_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FilterBlock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock_Collection fetchCollection()
	 */
	class EO_FilterBlock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_FilterBlock_Collection wakeUpCollection($rows)
	 */
	class EO_FilterBlock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\FilterEntityTable:landing/lib/internals/filterentity.php:85f92e01a633ba7e91eeac88f4ba9c69 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_FilterEntity
	 * @see \Bitrix\Landing\Internals\FilterEntityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSourceId()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setSourceId(\string|\Bitrix\Main\DB\SqlExpression $sourceId)
	 * @method bool hasSourceId()
	 * @method bool isSourceIdFilled()
	 * @method bool isSourceIdChanged()
	 * @method \string remindActualSourceId()
	 * @method \string requireSourceId()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetSourceId()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetSourceId()
	 * @method \string fillSourceId()
	 * @method \string getFilterHash()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setFilterHash(\string|\Bitrix\Main\DB\SqlExpression $filterHash)
	 * @method bool hasFilterHash()
	 * @method bool isFilterHashFilled()
	 * @method bool isFilterHashChanged()
	 * @method \string remindActualFilterHash()
	 * @method \string requireFilterHash()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetFilterHash()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetFilterHash()
	 * @method \string fillFilterHash()
	 * @method \string getFilter()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setFilter(\string|\Bitrix\Main\DB\SqlExpression $filter)
	 * @method bool hasFilter()
	 * @method bool isFilterFilled()
	 * @method bool isFilterChanged()
	 * @method \string remindActualFilter()
	 * @method \string requireFilter()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetFilter()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetFilter()
	 * @method \string fillFilter()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_FilterEntity wakeUp($data)
	 */
	class EO_FilterEntity {
		/* @var \Bitrix\Landing\Internals\FilterEntityTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FilterEntityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_FilterEntity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSourceIdList()
	 * @method \string[] fillSourceId()
	 * @method \string[] getFilterHashList()
	 * @method \string[] fillFilterHash()
	 * @method \string[] getFilterList()
	 * @method \string[] fillFilter()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_FilterEntity $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_FilterEntity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_FilterEntity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_FilterEntity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FilterEntity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\FilterEntityTable */
		static public $dataClass = '\Bitrix\Landing\Internals\FilterEntityTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FilterEntity_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FilterEntity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity_Collection fetchCollection()
	 */
	class EO_FilterEntity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_FilterEntity_Collection wakeUpCollection($rows)
	 */
	class EO_FilterEntity_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\HookDataTable:landing/lib/internals/hookdata.php:80d45f53f41af83fb8a78e3fb62d0204 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_HookData
	 * @see \Bitrix\Landing\Internals\HookDataTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_HookData setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_HookData setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_HookData setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \string getHook()
	 * @method \Bitrix\Landing\Internals\EO_HookData setHook(\string|\Bitrix\Main\DB\SqlExpression $hook)
	 * @method bool hasHook()
	 * @method bool isHookFilled()
	 * @method bool isHookChanged()
	 * @method \string remindActualHook()
	 * @method \string requireHook()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetHook()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetHook()
	 * @method \string fillHook()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_HookData setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetCode()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetCode()
	 * @method \string fillCode()
	 * @method \string getValue()
	 * @method \Bitrix\Landing\Internals\EO_HookData setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetValue()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetValue()
	 * @method \string fillValue()
	 * @method \string getPublic()
	 * @method \Bitrix\Landing\Internals\EO_HookData setPublic(\string|\Bitrix\Main\DB\SqlExpression $public)
	 * @method bool hasPublic()
	 * @method bool isPublicFilled()
	 * @method bool isPublicChanged()
	 * @method \string remindActualPublic()
	 * @method \string requirePublic()
	 * @method \Bitrix\Landing\Internals\EO_HookData resetPublic()
	 * @method \Bitrix\Landing\Internals\EO_HookData unsetPublic()
	 * @method \string fillPublic()
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
	 * @method \Bitrix\Landing\Internals\EO_HookData set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_HookData reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_HookData unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_HookData wakeUp($data)
	 */
	class EO_HookData {
		/* @var \Bitrix\Landing\Internals\HookDataTable */
		static public $dataClass = '\Bitrix\Landing\Internals\HookDataTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_HookData_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \string[] getHookList()
	 * @method \string[] fillHook()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \string[] getPublicList()
	 * @method \string[] fillPublic()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_HookData $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_HookData $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_HookData getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_HookData[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_HookData $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_HookData_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_HookData current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_HookData_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\HookDataTable */
		static public $dataClass = '\Bitrix\Landing\Internals\HookDataTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_HookData_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_HookData fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_HookData_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_HookData_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_HookData fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_HookData_Collection fetchCollection()
	 */
	class EO_HookData_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_HookData createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_HookData_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_HookData wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_HookData_Collection wakeUpCollection($rows)
	 */
	class EO_HookData_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\LandingTable:landing/lib/internals/landing.php:33312bd05c60324439a26ae9561497ee */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Landing
	 * @see \Bitrix\Landing\Internals\LandingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Landing setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetCode()
	 * @method \string fillCode()
	 * @method \string getInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing setInitiatorAppCode(\string|\Bitrix\Main\DB\SqlExpression $initiatorAppCode)
	 * @method bool hasInitiatorAppCode()
	 * @method bool isInitiatorAppCodeFilled()
	 * @method bool isInitiatorAppCodeChanged()
	 * @method \string remindActualInitiatorAppCode()
	 * @method \string requireInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetInitiatorAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetInitiatorAppCode()
	 * @method \string fillInitiatorAppCode()
	 * @method \string getRule()
	 * @method \Bitrix\Landing\Internals\EO_Landing setRule(\string|\Bitrix\Main\DB\SqlExpression $rule)
	 * @method bool hasRule()
	 * @method bool isRuleFilled()
	 * @method bool isRuleChanged()
	 * @method \string remindActualRule()
	 * @method \string requireRule()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetRule()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetRule()
	 * @method \string fillRule()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Landing setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetActive()
	 * @method \string fillActive()
	 * @method \string getDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Landing setDeleted(\string|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \string remindActualDeleted()
	 * @method \string requireDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetDeleted()
	 * @method \string fillDeleted()
	 * @method \string getPublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing setPublic(\string|\Bitrix\Main\DB\SqlExpression $public)
	 * @method bool hasPublic()
	 * @method bool isPublicFilled()
	 * @method bool isPublicChanged()
	 * @method \string remindActualPublic()
	 * @method \string requirePublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetPublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetPublic()
	 * @method \string fillPublic()
	 * @method \string getSys()
	 * @method \Bitrix\Landing\Internals\EO_Landing setSys(\string|\Bitrix\Main\DB\SqlExpression $sys)
	 * @method bool hasSys()
	 * @method bool isSysFilled()
	 * @method bool isSysChanged()
	 * @method \string remindActualSys()
	 * @method \string requireSys()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetSys()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetSys()
	 * @method \string fillSys()
	 * @method \int getViews()
	 * @method \Bitrix\Landing\Internals\EO_Landing setViews(\int|\Bitrix\Main\DB\SqlExpression $views)
	 * @method bool hasViews()
	 * @method bool isViewsFilled()
	 * @method bool isViewsChanged()
	 * @method \int remindActualViews()
	 * @method \int requireViews()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetViews()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetViews()
	 * @method \int fillViews()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Landing setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Landing setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getDescription()
	 * @method \Bitrix\Landing\Internals\EO_Landing setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetDescription()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getTplId()
	 * @method \Bitrix\Landing\Internals\EO_Landing setTplId(\int|\Bitrix\Main\DB\SqlExpression $tplId)
	 * @method bool hasTplId()
	 * @method bool isTplIdFilled()
	 * @method bool isTplIdChanged()
	 * @method \int remindActualTplId()
	 * @method \int requireTplId()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetTplId()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetTplId()
	 * @method \int fillTplId()
	 * @method \string getTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing setTplCode(\string|\Bitrix\Main\DB\SqlExpression $tplCode)
	 * @method bool hasTplCode()
	 * @method bool isTplCodeFilled()
	 * @method bool isTplCodeChanged()
	 * @method \string remindActualTplCode()
	 * @method \string requireTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetTplCode()
	 * @method \string fillTplCode()
	 * @method \int getSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Landing setSiteId(\int|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \int remindActualSiteId()
	 * @method \int requireSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetSiteId()
	 * @method \int fillSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Site getSite()
	 * @method \Bitrix\Landing\Internals\EO_Site remindActualSite()
	 * @method \Bitrix\Landing\Internals\EO_Site requireSite()
	 * @method \Bitrix\Landing\Internals\EO_Landing setSite(\Bitrix\Landing\Internals\EO_Site $object)
	 * @method \Bitrix\Landing\Internals\EO_Landing resetSite()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Landing\Internals\EO_Site fillSite()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef getAreas()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef remindActualAreas()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef requireAreas()
	 * @method \Bitrix\Landing\Internals\EO_Landing setAreas(\Bitrix\Landing\Internals\EO_TemplateRef $object)
	 * @method \Bitrix\Landing\Internals\EO_Landing resetAreas()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetAreas()
	 * @method bool hasAreas()
	 * @method bool isAreasFilled()
	 * @method bool isAreasChanged()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef fillAreas()
	 * @method \string getSitemap()
	 * @method \Bitrix\Landing\Internals\EO_Landing setSitemap(\string|\Bitrix\Main\DB\SqlExpression $sitemap)
	 * @method bool hasSitemap()
	 * @method bool isSitemapFilled()
	 * @method bool isSitemapChanged()
	 * @method \string remindActualSitemap()
	 * @method \string requireSitemap()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetSitemap()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetSitemap()
	 * @method \string fillSitemap()
	 * @method \string getFolder()
	 * @method \Bitrix\Landing\Internals\EO_Landing setFolder(\string|\Bitrix\Main\DB\SqlExpression $folder)
	 * @method bool hasFolder()
	 * @method bool isFolderFilled()
	 * @method bool isFolderChanged()
	 * @method \string remindActualFolder()
	 * @method \string requireFolder()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetFolder()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetFolder()
	 * @method \string fillFolder()
	 * @method \int getFolderId()
	 * @method \Bitrix\Landing\Internals\EO_Landing setFolderId(\int|\Bitrix\Main\DB\SqlExpression $folderId)
	 * @method bool hasFolderId()
	 * @method bool isFolderIdFilled()
	 * @method bool isFolderIdChanged()
	 * @method \int remindActualFolderId()
	 * @method \int requireFolderId()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetFolderId()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetFolderId()
	 * @method \int fillFolderId()
	 * @method \string getSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Landing setSearchContent(\string|\Bitrix\Main\DB\SqlExpression $searchContent)
	 * @method bool hasSearchContent()
	 * @method bool isSearchContentFilled()
	 * @method bool isSearchContentChanged()
	 * @method \string remindActualSearchContent()
	 * @method \string requireSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetSearchContent()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetSearchContent()
	 * @method \string fillSearchContent()
	 * @method \int getVersion()
	 * @method \Bitrix\Landing\Internals\EO_Landing setVersion(\int|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \int remindActualVersion()
	 * @method \int requireVersion()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetVersion()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetVersion()
	 * @method \int fillVersion()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Landing setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Landing resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Landing setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Landing resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Landing setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Landing setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime getDatePublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing setDatePublic(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $datePublic)
	 * @method bool hasDatePublic()
	 * @method bool isDatePublicFilled()
	 * @method bool isDatePublicChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDatePublic()
	 * @method \Bitrix\Main\Type\DateTime requireDatePublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing resetDatePublic()
	 * @method \Bitrix\Landing\Internals\EO_Landing unsetDatePublic()
	 * @method \Bitrix\Main\Type\DateTime fillDatePublic()
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
	 * @method \Bitrix\Landing\Internals\EO_Landing set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Landing reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Landing unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Landing wakeUp($data)
	 */
	class EO_Landing {
		/* @var \Bitrix\Landing\Internals\LandingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\LandingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Landing_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getInitiatorAppCodeList()
	 * @method \string[] fillInitiatorAppCode()
	 * @method \string[] getRuleList()
	 * @method \string[] fillRule()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getDeletedList()
	 * @method \string[] fillDeleted()
	 * @method \string[] getPublicList()
	 * @method \string[] fillPublic()
	 * @method \string[] getSysList()
	 * @method \string[] fillSys()
	 * @method \int[] getViewsList()
	 * @method \int[] fillViews()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getTplIdList()
	 * @method \int[] fillTplId()
	 * @method \string[] getTplCodeList()
	 * @method \string[] fillTplCode()
	 * @method \int[] getSiteIdList()
	 * @method \int[] fillSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Site[] getSiteList()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection getSiteCollection()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection fillSite()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef[] getAreasList()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection getAreasCollection()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef_Collection fillAreas()
	 * @method \string[] getSitemapList()
	 * @method \string[] fillSitemap()
	 * @method \string[] getFolderList()
	 * @method \string[] fillFolder()
	 * @method \int[] getFolderIdList()
	 * @method \int[] fillFolderId()
	 * @method \string[] getSearchContentList()
	 * @method \string[] fillSearchContent()
	 * @method \int[] getVersionList()
	 * @method \int[] fillVersion()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime[] getDatePublicList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDatePublic()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Landing $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Landing $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Landing getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Landing[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Landing $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Landing_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Landing current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Landing_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\LandingTable */
		static public $dataClass = '\Bitrix\Landing\Internals\LandingTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Landing_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Landing fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Landing_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Landing fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection fetchCollection()
	 */
	class EO_Landing_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Landing createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Landing wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Landing_Collection wakeUpCollection($rows)
	 */
	class EO_Landing_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\LockTable:landing/lib/internals/lock.php:30dfecdbc88a6ff5d83ae6bc5f0cfbf8 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Lock
	 * @see \Bitrix\Landing\Internals\LockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Lock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Lock setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Lock resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Lock unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Lock setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Lock resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Lock unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \string getLockType()
	 * @method \Bitrix\Landing\Internals\EO_Lock setLockType(\string|\Bitrix\Main\DB\SqlExpression $lockType)
	 * @method bool hasLockType()
	 * @method bool isLockTypeFilled()
	 * @method bool isLockTypeChanged()
	 * @method \string remindActualLockType()
	 * @method \string requireLockType()
	 * @method \Bitrix\Landing\Internals\EO_Lock resetLockType()
	 * @method \Bitrix\Landing\Internals\EO_Lock unsetLockType()
	 * @method \string fillLockType()
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
	 * @method \Bitrix\Landing\Internals\EO_Lock set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Lock reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Lock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Lock wakeUp($data)
	 */
	class EO_Lock {
		/* @var \Bitrix\Landing\Internals\LockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\LockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Lock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \string[] getLockTypeList()
	 * @method \string[] fillLockType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Lock $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Lock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Lock getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Lock[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Lock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Lock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Lock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Lock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\LockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\LockTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Lock_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Lock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Lock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Lock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Lock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Lock_Collection fetchCollection()
	 */
	class EO_Lock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Lock createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Lock_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Lock wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Lock_Collection wakeUpCollection($rows)
	 */
	class EO_Lock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\PlacementTable:landing/lib/internals/placement.php:82b18ea46ec0deafca89902d74df0307 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Placement
	 * @see \Bitrix\Landing\Internals\PlacementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Placement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\Landing\Internals\EO_Placement setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetAppId()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getPlacement()
	 * @method \Bitrix\Landing\Internals\EO_Placement setPlacement(\string|\Bitrix\Main\DB\SqlExpression $placement)
	 * @method bool hasPlacement()
	 * @method bool isPlacementFilled()
	 * @method bool isPlacementChanged()
	 * @method \string remindActualPlacement()
	 * @method \string requirePlacement()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetPlacement()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetPlacement()
	 * @method \string fillPlacement()
	 * @method \string getPlacementHandler()
	 * @method \Bitrix\Landing\Internals\EO_Placement setPlacementHandler(\string|\Bitrix\Main\DB\SqlExpression $placementHandler)
	 * @method bool hasPlacementHandler()
	 * @method bool isPlacementHandlerFilled()
	 * @method bool isPlacementHandlerChanged()
	 * @method \string remindActualPlacementHandler()
	 * @method \string requirePlacementHandler()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetPlacementHandler()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetPlacementHandler()
	 * @method \string fillPlacementHandler()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Placement setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Placement setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Placement setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Placement resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Placement unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Placement set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Placement reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Placement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Placement wakeUp($data)
	 */
	class EO_Placement {
		/* @var \Bitrix\Landing\Internals\PlacementTable */
		static public $dataClass = '\Bitrix\Landing\Internals\PlacementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Placement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getPlacementList()
	 * @method \string[] fillPlacement()
	 * @method \string[] getPlacementHandlerList()
	 * @method \string[] fillPlacementHandler()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Placement $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Placement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Placement getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Placement[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Placement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Placement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Placement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Placement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\PlacementTable */
		static public $dataClass = '\Bitrix\Landing\Internals\PlacementTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Placement_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Placement fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Placement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Placement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Placement fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Placement_Collection fetchCollection()
	 */
	class EO_Placement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Placement createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Placement_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Placement wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Placement_Collection wakeUpCollection($rows)
	 */
	class EO_Placement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\RepoTable:landing/lib/internals/repo.php:b2695b9df05ee71082d22ca4a2bb1ee8 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Repo
	 * @see \Bitrix\Landing\Internals\RepoTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Repo setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Repo setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Repo setAppCode(\string|\Bitrix\Main\DB\SqlExpression $appCode)
	 * @method bool hasAppCode()
	 * @method bool isAppCodeFilled()
	 * @method bool isAppCodeChanged()
	 * @method \string remindActualAppCode()
	 * @method \string requireAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetAppCode()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetAppCode()
	 * @method \string fillAppCode()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Repo setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetActive()
	 * @method \string fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Landing\Internals\EO_Repo setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetName()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Landing\Internals\EO_Repo setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetDescription()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getSections()
	 * @method \Bitrix\Landing\Internals\EO_Repo setSections(\string|\Bitrix\Main\DB\SqlExpression $sections)
	 * @method bool hasSections()
	 * @method bool isSectionsFilled()
	 * @method bool isSectionsChanged()
	 * @method \string remindActualSections()
	 * @method \string requireSections()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetSections()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetSections()
	 * @method \string fillSections()
	 * @method \string getSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Repo setSiteTemplateId(\string|\Bitrix\Main\DB\SqlExpression $siteTemplateId)
	 * @method bool hasSiteTemplateId()
	 * @method bool isSiteTemplateIdFilled()
	 * @method bool isSiteTemplateIdChanged()
	 * @method \string remindActualSiteTemplateId()
	 * @method \string requireSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetSiteTemplateId()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetSiteTemplateId()
	 * @method \string fillSiteTemplateId()
	 * @method \string getPreview()
	 * @method \Bitrix\Landing\Internals\EO_Repo setPreview(\string|\Bitrix\Main\DB\SqlExpression $preview)
	 * @method bool hasPreview()
	 * @method bool isPreviewFilled()
	 * @method bool isPreviewChanged()
	 * @method \string remindActualPreview()
	 * @method \string requirePreview()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetPreview()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetPreview()
	 * @method \string fillPreview()
	 * @method \string getManifest()
	 * @method \Bitrix\Landing\Internals\EO_Repo setManifest(\string|\Bitrix\Main\DB\SqlExpression $manifest)
	 * @method bool hasManifest()
	 * @method bool isManifestFilled()
	 * @method bool isManifestChanged()
	 * @method \string remindActualManifest()
	 * @method \string requireManifest()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetManifest()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetManifest()
	 * @method \string fillManifest()
	 * @method \string getContent()
	 * @method \Bitrix\Landing\Internals\EO_Repo setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetContent()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetContent()
	 * @method \string fillContent()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Repo setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Repo setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Repo resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Repo unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Repo set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Repo reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Repo unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Repo wakeUp($data)
	 */
	class EO_Repo {
		/* @var \Bitrix\Landing\Internals\RepoTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RepoTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Repo_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getAppCodeList()
	 * @method \string[] fillAppCode()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getSectionsList()
	 * @method \string[] fillSections()
	 * @method \string[] getSiteTemplateIdList()
	 * @method \string[] fillSiteTemplateId()
	 * @method \string[] getPreviewList()
	 * @method \string[] fillPreview()
	 * @method \string[] getManifestList()
	 * @method \string[] fillManifest()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Repo $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Repo $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Repo getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Repo[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Repo $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Repo_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Repo current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Repo_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\RepoTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RepoTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Repo_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Repo fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Repo_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Repo_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Repo fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Repo_Collection fetchCollection()
	 */
	class EO_Repo_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Repo createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Repo_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Repo wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Repo_Collection wakeUpCollection($rows)
	 */
	class EO_Repo_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\RightsTable:landing/lib/internals/rights.php:fa4efd8a4808eb8ce4849d5b61e5934e */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Rights
	 * @see \Bitrix\Landing\Internals\RightsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Rights setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Rights setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Rights resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Rights setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Rights resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getTaskId()
	 * @method \Bitrix\Landing\Internals\EO_Rights setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \int remindActualTaskId()
	 * @method \int requireTaskId()
	 * @method \Bitrix\Landing\Internals\EO_Rights resetTaskId()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetTaskId()
	 * @method \int fillTaskId()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Landing\Internals\EO_Rights setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Landing\Internals\EO_Rights resetAccessCode()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \int getRoleId()
	 * @method \Bitrix\Landing\Internals\EO_Rights setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Landing\Internals\EO_Rights resetRoleId()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \Bitrix\Landing\Internals\EO_Role getRole()
	 * @method \Bitrix\Landing\Internals\EO_Role remindActualRole()
	 * @method \Bitrix\Landing\Internals\EO_Role requireRole()
	 * @method \Bitrix\Landing\Internals\EO_Rights setRole(\Bitrix\Landing\Internals\EO_Role $object)
	 * @method \Bitrix\Landing\Internals\EO_Rights resetRole()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetRole()
	 * @method bool hasRole()
	 * @method bool isRoleFilled()
	 * @method bool isRoleChanged()
	 * @method \Bitrix\Landing\Internals\EO_Role fillRole()
	 * @method \Bitrix\Main\EO_UserAccess getUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess remindActualUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess requireUserAccess()
	 * @method \Bitrix\Landing\Internals\EO_Rights setUserAccess(\Bitrix\Main\EO_UserAccess $object)
	 * @method \Bitrix\Landing\Internals\EO_Rights resetUserAccess()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetUserAccess()
	 * @method bool hasUserAccess()
	 * @method bool isUserAccessFilled()
	 * @method bool isUserAccessChanged()
	 * @method \Bitrix\Main\EO_UserAccess fillUserAccess()
	 * @method \Bitrix\Main\EO_TaskOperation getTaskOperation()
	 * @method \Bitrix\Main\EO_TaskOperation remindActualTaskOperation()
	 * @method \Bitrix\Main\EO_TaskOperation requireTaskOperation()
	 * @method \Bitrix\Landing\Internals\EO_Rights setTaskOperation(\Bitrix\Main\EO_TaskOperation $object)
	 * @method \Bitrix\Landing\Internals\EO_Rights resetTaskOperation()
	 * @method \Bitrix\Landing\Internals\EO_Rights unsetTaskOperation()
	 * @method bool hasTaskOperation()
	 * @method bool isTaskOperationFilled()
	 * @method bool isTaskOperationChanged()
	 * @method \Bitrix\Main\EO_TaskOperation fillTaskOperation()
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
	 * @method \Bitrix\Landing\Internals\EO_Rights set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Rights reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Rights unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Rights wakeUp($data)
	 */
	class EO_Rights {
		/* @var \Bitrix\Landing\Internals\RightsTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RightsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Rights_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getTaskIdList()
	 * @method \int[] fillTaskId()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \int[] getRoleIdList()
	 * @method \int[] fillRoleId()
	 * @method \Bitrix\Landing\Internals\EO_Role[] getRoleList()
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection getRoleCollection()
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection fillRole()
	 * @method \Bitrix\Main\EO_UserAccess[] getUserAccessList()
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection getUserAccessCollection()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fillUserAccess()
	 * @method \Bitrix\Main\EO_TaskOperation[] getTaskOperationList()
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection getTaskOperationCollection()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection fillTaskOperation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Rights $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Rights $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Rights getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Rights[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Rights $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Rights_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Rights current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Rights_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\RightsTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RightsTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Rights_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Rights fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Rights_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Rights fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection fetchCollection()
	 */
	class EO_Rights_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Rights createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Rights wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Rights_Collection wakeUpCollection($rows)
	 */
	class EO_Rights_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\RoleTable:landing/lib/internals/role.php:bae2faecb7126bd3f0905cafabe5009e */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Role
	 * @see \Bitrix\Landing\Internals\RoleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Role setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Role setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Role resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Role setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Role resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getType()
	 * @method \Bitrix\Landing\Internals\EO_Role setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Landing\Internals\EO_Role resetType()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetType()
	 * @method \string fillType()
	 * @method \string getAccessCodes()
	 * @method \Bitrix\Landing\Internals\EO_Role setAccessCodes(\string|\Bitrix\Main\DB\SqlExpression $accessCodes)
	 * @method bool hasAccessCodes()
	 * @method bool isAccessCodesFilled()
	 * @method bool isAccessCodesChanged()
	 * @method \string remindActualAccessCodes()
	 * @method \string requireAccessCodes()
	 * @method \Bitrix\Landing\Internals\EO_Role resetAccessCodes()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetAccessCodes()
	 * @method \string fillAccessCodes()
	 * @method \string getAdditionalRights()
	 * @method \Bitrix\Landing\Internals\EO_Role setAdditionalRights(\string|\Bitrix\Main\DB\SqlExpression $additionalRights)
	 * @method bool hasAdditionalRights()
	 * @method bool isAdditionalRightsFilled()
	 * @method bool isAdditionalRightsChanged()
	 * @method \string remindActualAdditionalRights()
	 * @method \string requireAdditionalRights()
	 * @method \Bitrix\Landing\Internals\EO_Role resetAdditionalRights()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetAdditionalRights()
	 * @method \string fillAdditionalRights()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Role setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Role resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Role setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Role resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Role setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Role resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Role setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Role resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Role setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Role resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Role setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Role resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Role unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Role set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Role reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Role unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Role wakeUp($data)
	 */
	class EO_Role {
		/* @var \Bitrix\Landing\Internals\RoleTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RoleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Role_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getAccessCodesList()
	 * @method \string[] fillAccessCodes()
	 * @method \string[] getAdditionalRightsList()
	 * @method \string[] fillAdditionalRights()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Role $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Role $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Role getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Role[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Role $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Role_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Role current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Role_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\RoleTable */
		static public $dataClass = '\Bitrix\Landing\Internals\RoleTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Role_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Role fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Role_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Role fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection fetchCollection()
	 */
	class EO_Role_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Role createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Role wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Role_Collection wakeUpCollection($rows)
	 */
	class EO_Role_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\SiteTable:landing/lib/internals/site.php:0cb7f0c42249c5deac1bfac6a9e3ed6c */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Site
	 * @see \Bitrix\Landing\Internals\SiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Site setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_Site setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_Site resetCode()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetCode()
	 * @method \string fillCode()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Site setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Site resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetActive()
	 * @method \string fillActive()
	 * @method \string getDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Site setDeleted(\string|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \string remindActualDeleted()
	 * @method \string requireDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Site resetDeleted()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDeleted()
	 * @method \string fillDeleted()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Site setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Site resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Site setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Site resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getDescription()
	 * @method \Bitrix\Landing\Internals\EO_Site setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Landing\Internals\EO_Site resetDescription()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getType()
	 * @method \Bitrix\Landing\Internals\EO_Site setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Landing\Internals\EO_Site resetType()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetType()
	 * @method \string fillType()
	 * @method \int getTplId()
	 * @method \Bitrix\Landing\Internals\EO_Site setTplId(\int|\Bitrix\Main\DB\SqlExpression $tplId)
	 * @method bool hasTplId()
	 * @method bool isTplIdFilled()
	 * @method bool isTplIdChanged()
	 * @method \int remindActualTplId()
	 * @method \int requireTplId()
	 * @method \Bitrix\Landing\Internals\EO_Site resetTplId()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetTplId()
	 * @method \int fillTplId()
	 * @method \string getTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Site setTplCode(\string|\Bitrix\Main\DB\SqlExpression $tplCode)
	 * @method bool hasTplCode()
	 * @method bool isTplCodeFilled()
	 * @method bool isTplCodeChanged()
	 * @method \string remindActualTplCode()
	 * @method \string requireTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Site resetTplCode()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetTplCode()
	 * @method \string fillTplCode()
	 * @method \int getDomainId()
	 * @method \Bitrix\Landing\Internals\EO_Site setDomainId(\int|\Bitrix\Main\DB\SqlExpression $domainId)
	 * @method bool hasDomainId()
	 * @method bool isDomainIdFilled()
	 * @method bool isDomainIdChanged()
	 * @method \int remindActualDomainId()
	 * @method \int requireDomainId()
	 * @method \Bitrix\Landing\Internals\EO_Site resetDomainId()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDomainId()
	 * @method \int fillDomainId()
	 * @method \Bitrix\Landing\Internals\EO_Domain getDomain()
	 * @method \Bitrix\Landing\Internals\EO_Domain remindActualDomain()
	 * @method \Bitrix\Landing\Internals\EO_Domain requireDomain()
	 * @method \Bitrix\Landing\Internals\EO_Site setDomain(\Bitrix\Landing\Internals\EO_Domain $object)
	 * @method \Bitrix\Landing\Internals\EO_Site resetDomain()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDomain()
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \Bitrix\Landing\Internals\EO_Domain fillDomain()
	 * @method \string getSmnSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Site setSmnSiteId(\string|\Bitrix\Main\DB\SqlExpression $smnSiteId)
	 * @method bool hasSmnSiteId()
	 * @method bool isSmnSiteIdFilled()
	 * @method bool isSmnSiteIdChanged()
	 * @method \string remindActualSmnSiteId()
	 * @method \string requireSmnSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Site resetSmnSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetSmnSiteId()
	 * @method \string fillSmnSiteId()
	 * @method \int getLandingIdIndex()
	 * @method \Bitrix\Landing\Internals\EO_Site setLandingIdIndex(\int|\Bitrix\Main\DB\SqlExpression $landingIdIndex)
	 * @method bool hasLandingIdIndex()
	 * @method bool isLandingIdIndexFilled()
	 * @method bool isLandingIdIndexChanged()
	 * @method \int remindActualLandingIdIndex()
	 * @method \int requireLandingIdIndex()
	 * @method \Bitrix\Landing\Internals\EO_Site resetLandingIdIndex()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetLandingIdIndex()
	 * @method \int fillLandingIdIndex()
	 * @method \int getLandingId404()
	 * @method \Bitrix\Landing\Internals\EO_Site setLandingId404(\int|\Bitrix\Main\DB\SqlExpression $landingId404)
	 * @method bool hasLandingId404()
	 * @method bool isLandingId404Filled()
	 * @method bool isLandingId404Changed()
	 * @method \int remindActualLandingId404()
	 * @method \int requireLandingId404()
	 * @method \Bitrix\Landing\Internals\EO_Site resetLandingId404()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetLandingId404()
	 * @method \int fillLandingId404()
	 * @method \int getLandingId503()
	 * @method \Bitrix\Landing\Internals\EO_Site setLandingId503(\int|\Bitrix\Main\DB\SqlExpression $landingId503)
	 * @method bool hasLandingId503()
	 * @method bool isLandingId503Filled()
	 * @method bool isLandingId503Changed()
	 * @method \int remindActualLandingId503()
	 * @method \int requireLandingId503()
	 * @method \Bitrix\Landing\Internals\EO_Site resetLandingId503()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetLandingId503()
	 * @method \int fillLandingId503()
	 * @method \int getLang()
	 * @method \Bitrix\Landing\Internals\EO_Site setLang(\int|\Bitrix\Main\DB\SqlExpression $lang)
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \int remindActualLang()
	 * @method \int requireLang()
	 * @method \Bitrix\Landing\Internals\EO_Site resetLang()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetLang()
	 * @method \int fillLang()
	 * @method \string getSpecial()
	 * @method \Bitrix\Landing\Internals\EO_Site setSpecial(\string|\Bitrix\Main\DB\SqlExpression $special)
	 * @method bool hasSpecial()
	 * @method bool isSpecialFilled()
	 * @method bool isSpecialChanged()
	 * @method \string remindActualSpecial()
	 * @method \string requireSpecial()
	 * @method \Bitrix\Landing\Internals\EO_Site resetSpecial()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetSpecial()
	 * @method \string fillSpecial()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Site setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Site resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Site setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Site resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Site setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Site resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Site setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Site resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Site setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Site resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Site setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Site resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Site unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Site set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Site reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Site unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Site wakeUp($data)
	 */
	class EO_Site {
		/* @var \Bitrix\Landing\Internals\SiteTable */
		static public $dataClass = '\Bitrix\Landing\Internals\SiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Site_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getDeletedList()
	 * @method \string[] fillDeleted()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \int[] getTplIdList()
	 * @method \int[] fillTplId()
	 * @method \string[] getTplCodeList()
	 * @method \string[] fillTplCode()
	 * @method \int[] getDomainIdList()
	 * @method \int[] fillDomainId()
	 * @method \Bitrix\Landing\Internals\EO_Domain[] getDomainList()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection getDomainCollection()
	 * @method \Bitrix\Landing\Internals\EO_Domain_Collection fillDomain()
	 * @method \string[] getSmnSiteIdList()
	 * @method \string[] fillSmnSiteId()
	 * @method \int[] getLandingIdIndexList()
	 * @method \int[] fillLandingIdIndex()
	 * @method \int[] getLandingId404List()
	 * @method \int[] fillLandingId404()
	 * @method \int[] getLandingId503List()
	 * @method \int[] fillLandingId503()
	 * @method \int[] getLangList()
	 * @method \int[] fillLang()
	 * @method \string[] getSpecialList()
	 * @method \string[] fillSpecial()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Site $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Site $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Site getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Site[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Site $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Site_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Site current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Site_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\SiteTable */
		static public $dataClass = '\Bitrix\Landing\Internals\SiteTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Site_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Site fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Site_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Site fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection fetchCollection()
	 */
	class EO_Site_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Site createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Site wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Site_Collection wakeUpCollection($rows)
	 */
	class EO_Site_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\SyspageTable:landing/lib/internals/syspage.php:abe44034f447cd34c4ca78d2b940476a */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Syspage
	 * @see \Bitrix\Landing\Internals\SyspageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage setSiteId(\int|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \int remindActualSiteId()
	 * @method \int requireSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage resetSiteId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage unsetSiteId()
	 * @method \int fillSiteId()
	 * @method \string getType()
	 * @method \Bitrix\Landing\Internals\EO_Syspage setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Landing\Internals\EO_Syspage resetType()
	 * @method \Bitrix\Landing\Internals\EO_Syspage unsetType()
	 * @method \string fillType()
	 * @method \int getLandingId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage setLandingId(\int|\Bitrix\Main\DB\SqlExpression $landingId)
	 * @method bool hasLandingId()
	 * @method bool isLandingIdFilled()
	 * @method bool isLandingIdChanged()
	 * @method \int remindActualLandingId()
	 * @method \int requireLandingId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage resetLandingId()
	 * @method \Bitrix\Landing\Internals\EO_Syspage unsetLandingId()
	 * @method \int fillLandingId()
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
	 * @method \Bitrix\Landing\Internals\EO_Syspage set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Syspage reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Syspage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Syspage wakeUp($data)
	 */
	class EO_Syspage {
		/* @var \Bitrix\Landing\Internals\SyspageTable */
		static public $dataClass = '\Bitrix\Landing\Internals\SyspageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Syspage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSiteIdList()
	 * @method \int[] fillSiteId()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \int[] getLandingIdList()
	 * @method \int[] fillLandingId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Syspage $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Syspage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Syspage getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Syspage[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Syspage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Syspage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Syspage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Syspage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\SyspageTable */
		static public $dataClass = '\Bitrix\Landing\Internals\SyspageTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Syspage_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Syspage fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Syspage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Syspage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Syspage fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Syspage_Collection fetchCollection()
	 */
	class EO_Syspage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Syspage createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Syspage_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Syspage wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Syspage_Collection wakeUpCollection($rows)
	 */
	class EO_Syspage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\TemplateTable:landing/lib/internals/template.php:32db944a03ef0dfed0f6bddb86dca3e1 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Template
	 * @see \Bitrix\Landing\Internals\TemplateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_Template setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getActive()
	 * @method \Bitrix\Landing\Internals\EO_Template setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Landing\Internals\EO_Template resetActive()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetActive()
	 * @method \string fillActive()
	 * @method \string getTitle()
	 * @method \Bitrix\Landing\Internals\EO_Template setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Landing\Internals\EO_Template resetTitle()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getSort()
	 * @method \Bitrix\Landing\Internals\EO_Template setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Landing\Internals\EO_Template resetSort()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetSort()
	 * @method \int fillSort()
	 * @method \string getXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Template setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Template resetXmlId()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getContent()
	 * @method \Bitrix\Landing\Internals\EO_Template setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Landing\Internals\EO_Template resetContent()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetContent()
	 * @method \string fillContent()
	 * @method \int getAreaCount()
	 * @method \Bitrix\Landing\Internals\EO_Template setAreaCount(\int|\Bitrix\Main\DB\SqlExpression $areaCount)
	 * @method bool hasAreaCount()
	 * @method bool isAreaCountFilled()
	 * @method bool isAreaCountChanged()
	 * @method \int remindActualAreaCount()
	 * @method \int requireAreaCount()
	 * @method \Bitrix\Landing\Internals\EO_Template resetAreaCount()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetAreaCount()
	 * @method \int fillAreaCount()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Template setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Template resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \Bitrix\Main\EO_User getCreatedBy()
	 * @method \Bitrix\Main\EO_User remindActualCreatedBy()
	 * @method \Bitrix\Main\EO_User requireCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Template setCreatedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Template resetCreatedBy()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetCreatedBy()
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedBy()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Template setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Template resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\EO_User getModifiedBy()
	 * @method \Bitrix\Main\EO_User remindActualModifiedBy()
	 * @method \Bitrix\Main\EO_User requireModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Template setModifiedBy(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Landing\Internals\EO_Template resetModifiedBy()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetModifiedBy()
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Template setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Template resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Template setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Template resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_Template unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_Template set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_Template reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_Template unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_Template wakeUp($data)
	 */
	class EO_Template {
		/* @var \Bitrix\Landing\Internals\TemplateTable */
		static public $dataClass = '\Bitrix\Landing\Internals\TemplateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_Template_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \int[] getAreaCountList()
	 * @method \int[] fillAreaCount()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \Bitrix\Main\EO_User[] getCreatedByList()
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection getCreatedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedBy()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\EO_User[] getModifiedByList()
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection getModifiedByCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_Template $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_Template $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Template getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_Template[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_Template $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_Template_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_Template current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Template_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\TemplateTable */
		static public $dataClass = '\Bitrix\Landing\Internals\TemplateTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Template_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_Template fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Template_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Template fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection fetchCollection()
	 */
	class EO_Template_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_Template createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_Template wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_Template_Collection wakeUpCollection($rows)
	 */
	class EO_Template_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\TemplateRefTable:landing/lib/internals/templateref.php:3acc79413d6833cf18ba6d5ea705a56d */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_TemplateRef
	 * @see \Bitrix\Landing\Internals\TemplateRefTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef resetEntityId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef resetEntityType()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getArea()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef setArea(\int|\Bitrix\Main\DB\SqlExpression $area)
	 * @method bool hasArea()
	 * @method bool isAreaFilled()
	 * @method bool isAreaChanged()
	 * @method \int remindActualArea()
	 * @method \int requireArea()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef resetArea()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef unsetArea()
	 * @method \int fillArea()
	 * @method \int getLandingId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef setLandingId(\int|\Bitrix\Main\DB\SqlExpression $landingId)
	 * @method bool hasLandingId()
	 * @method bool isLandingIdFilled()
	 * @method bool isLandingIdChanged()
	 * @method \int remindActualLandingId()
	 * @method \int requireLandingId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef resetLandingId()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef unsetLandingId()
	 * @method \int fillLandingId()
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
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_TemplateRef wakeUp($data)
	 */
	class EO_TemplateRef {
		/* @var \Bitrix\Landing\Internals\TemplateRefTable */
		static public $dataClass = '\Bitrix\Landing\Internals\TemplateRefTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_TemplateRef_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getAreaList()
	 * @method \int[] fillArea()
	 * @method \int[] getLandingIdList()
	 * @method \int[] fillLandingId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_TemplateRef $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_TemplateRef $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_TemplateRef $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_TemplateRef_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TemplateRef_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\TemplateRefTable */
		static public $dataClass = '\Bitrix\Landing\Internals\TemplateRefTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TemplateRef_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TemplateRef_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef_Collection fetchCollection()
	 */
	class EO_TemplateRef_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_TemplateRef_Collection wakeUpCollection($rows)
	 */
	class EO_TemplateRef_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\UpdateBlockTable:landing/lib/internals/updateblock.php:eb51fbdc445bbc6a5927e6e215bfebf5 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_UpdateBlock
	 * @see \Bitrix\Landing\Internals\UpdateBlockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetCode()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetCode()
	 * @method \string fillCode()
	 * @method \int getLastBlockId()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setLastBlockId(\int|\Bitrix\Main\DB\SqlExpression $lastBlockId)
	 * @method bool hasLastBlockId()
	 * @method bool isLastBlockIdFilled()
	 * @method bool isLastBlockIdChanged()
	 * @method \int remindActualLastBlockId()
	 * @method \int requireLastBlockId()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetLastBlockId()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetLastBlockId()
	 * @method \int fillLastBlockId()
	 * @method \string getParams()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setParams(\string|\Bitrix\Main\DB\SqlExpression $params)
	 * @method bool hasParams()
	 * @method bool isParamsFilled()
	 * @method bool isParamsChanged()
	 * @method \string remindActualParams()
	 * @method \string requireParams()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetParams()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetParams()
	 * @method \string fillParams()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_UpdateBlock wakeUp($data)
	 */
	class EO_UpdateBlock {
		/* @var \Bitrix\Landing\Internals\UpdateBlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\UpdateBlockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_UpdateBlock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \int[] getLastBlockIdList()
	 * @method \int[] fillLastBlockId()
	 * @method \string[] getParamsList()
	 * @method \string[] fillParams()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_UpdateBlock $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_UpdateBlock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_UpdateBlock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_UpdateBlock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UpdateBlock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\UpdateBlockTable */
		static public $dataClass = '\Bitrix\Landing\Internals\UpdateBlockTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UpdateBlock_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UpdateBlock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock_Collection fetchCollection()
	 */
	class EO_UpdateBlock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_UpdateBlock_Collection wakeUpCollection($rows)
	 */
	class EO_UpdateBlock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\UrlRewriteTable:landing/lib/internals/urlrewrite.php:c7cc364226e749bb69c6b102fcc8c321 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_UrlRewrite
	 * @see \Bitrix\Landing\Internals\UrlRewriteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSiteId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setSiteId(\int|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \int remindActualSiteId()
	 * @method \int requireSiteId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetSiteId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetSiteId()
	 * @method \int fillSiteId()
	 * @method \string getRule()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setRule(\string|\Bitrix\Main\DB\SqlExpression $rule)
	 * @method bool hasRule()
	 * @method bool isRuleFilled()
	 * @method bool isRuleChanged()
	 * @method \string remindActualRule()
	 * @method \string requireRule()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetRule()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetRule()
	 * @method \string fillRule()
	 * @method \int getLandingId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setLandingId(\int|\Bitrix\Main\DB\SqlExpression $landingId)
	 * @method bool hasLandingId()
	 * @method bool isLandingIdFilled()
	 * @method bool isLandingIdChanged()
	 * @method \int remindActualLandingId()
	 * @method \int requireLandingId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetLandingId()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetLandingId()
	 * @method \int fillLandingId()
	 * @method \int getCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setCreatedById(\int|\Bitrix\Main\DB\SqlExpression $createdById)
	 * @method bool hasCreatedById()
	 * @method bool isCreatedByIdFilled()
	 * @method bool isCreatedByIdChanged()
	 * @method \int remindActualCreatedById()
	 * @method \int requireCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetCreatedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetCreatedById()
	 * @method \int fillCreatedById()
	 * @method \int getModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setModifiedById(\int|\Bitrix\Main\DB\SqlExpression $modifiedById)
	 * @method bool hasModifiedById()
	 * @method bool isModifiedByIdFilled()
	 * @method bool isModifiedByIdChanged()
	 * @method \int remindActualModifiedById()
	 * @method \int requireModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetModifiedById()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetModifiedById()
	 * @method \int fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetDateCreate()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite resetDateModify()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
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
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_UrlRewrite wakeUp($data)
	 */
	class EO_UrlRewrite {
		/* @var \Bitrix\Landing\Internals\UrlRewriteTable */
		static public $dataClass = '\Bitrix\Landing\Internals\UrlRewriteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_UrlRewrite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSiteIdList()
	 * @method \int[] fillSiteId()
	 * @method \string[] getRuleList()
	 * @method \string[] fillRule()
	 * @method \int[] getLandingIdList()
	 * @method \int[] fillLandingId()
	 * @method \int[] getCreatedByIdList()
	 * @method \int[] fillCreatedById()
	 * @method \int[] getModifiedByIdList()
	 * @method \int[] fillModifiedById()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_UrlRewrite $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_UrlRewrite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_UrlRewrite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_UrlRewrite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UrlRewrite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\UrlRewriteTable */
		static public $dataClass = '\Bitrix\Landing\Internals\UrlRewriteTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UrlRewrite_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UrlRewrite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite_Collection fetchCollection()
	 */
	class EO_UrlRewrite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_UrlRewrite_Collection wakeUpCollection($rows)
	 */
	class EO_UrlRewrite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Landing\Internals\ViewTable:landing/lib/internals/view.php:4c358005e76dadb9102d6df8892e0c90 */
namespace Bitrix\Landing\Internals {
	/**
	 * EO_View
	 * @see \Bitrix\Landing\Internals\ViewTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Landing\Internals\EO_View setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLid()
	 * @method \Bitrix\Landing\Internals\EO_View setLid(\int|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \int remindActualLid()
	 * @method \int requireLid()
	 * @method \Bitrix\Landing\Internals\EO_View resetLid()
	 * @method \Bitrix\Landing\Internals\EO_View unsetLid()
	 * @method \int fillLid()
	 * @method \int getUserId()
	 * @method \Bitrix\Landing\Internals\EO_View setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Landing\Internals\EO_View resetUserId()
	 * @method \Bitrix\Landing\Internals\EO_View unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getViews()
	 * @method \Bitrix\Landing\Internals\EO_View setViews(\int|\Bitrix\Main\DB\SqlExpression $views)
	 * @method bool hasViews()
	 * @method bool isViewsFilled()
	 * @method bool isViewsChanged()
	 * @method \int remindActualViews()
	 * @method \int requireViews()
	 * @method \Bitrix\Landing\Internals\EO_View resetViews()
	 * @method \Bitrix\Landing\Internals\EO_View unsetViews()
	 * @method \int fillViews()
	 * @method \Bitrix\Main\Type\DateTime getFirstView()
	 * @method \Bitrix\Landing\Internals\EO_View setFirstView(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $firstView)
	 * @method bool hasFirstView()
	 * @method bool isFirstViewFilled()
	 * @method bool isFirstViewChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFirstView()
	 * @method \Bitrix\Main\Type\DateTime requireFirstView()
	 * @method \Bitrix\Landing\Internals\EO_View resetFirstView()
	 * @method \Bitrix\Landing\Internals\EO_View unsetFirstView()
	 * @method \Bitrix\Main\Type\DateTime fillFirstView()
	 * @method \Bitrix\Main\Type\DateTime getLastView()
	 * @method \Bitrix\Landing\Internals\EO_View setLastView(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastView)
	 * @method bool hasLastView()
	 * @method bool isLastViewFilled()
	 * @method bool isLastViewChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastView()
	 * @method \Bitrix\Main\Type\DateTime requireLastView()
	 * @method \Bitrix\Landing\Internals\EO_View resetLastView()
	 * @method \Bitrix\Landing\Internals\EO_View unsetLastView()
	 * @method \Bitrix\Main\Type\DateTime fillLastView()
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
	 * @method \Bitrix\Landing\Internals\EO_View set($fieldName, $value)
	 * @method \Bitrix\Landing\Internals\EO_View reset($fieldName)
	 * @method \Bitrix\Landing\Internals\EO_View unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Landing\Internals\EO_View wakeUp($data)
	 */
	class EO_View {
		/* @var \Bitrix\Landing\Internals\ViewTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ViewTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * EO_View_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLidList()
	 * @method \int[] fillLid()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getViewsList()
	 * @method \int[] fillViews()
	 * @method \Bitrix\Main\Type\DateTime[] getFirstViewList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFirstView()
	 * @method \Bitrix\Main\Type\DateTime[] getLastViewList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastView()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Landing\Internals\EO_View $object)
	 * @method bool has(\Bitrix\Landing\Internals\EO_View $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_View getByPrimary($primary)
	 * @method \Bitrix\Landing\Internals\EO_View[] getAll()
	 * @method bool remove(\Bitrix\Landing\Internals\EO_View $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Landing\Internals\EO_View_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Landing\Internals\EO_View current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_View_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Landing\Internals\ViewTable */
		static public $dataClass = '\Bitrix\Landing\Internals\ViewTable';
	}
}
namespace Bitrix\Landing\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_View_Result exec()
	 * @method \Bitrix\Landing\Internals\EO_View fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_View_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_View_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_View fetchObject()
	 * @method \Bitrix\Landing\Internals\EO_View_Collection fetchCollection()
	 */
	class EO_View_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Landing\Internals\EO_View createObject($setDefaultValues = true)
	 * @method \Bitrix\Landing\Internals\EO_View_Collection createCollection()
	 * @method \Bitrix\Landing\Internals\EO_View wakeUpObject($row)
	 * @method \Bitrix\Landing\Internals\EO_View_Collection wakeUpCollection($rows)
	 */
	class EO_View_Entity extends \Bitrix\Main\ORM\Entity {}
}