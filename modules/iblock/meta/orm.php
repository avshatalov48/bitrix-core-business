<?php

/* ORMENTITYANNOTATION:Bitrix\Iblock\ElementPropertyTable:iblock/lib/elementpropertytable.php:4ab1bcd8cbe95715e7dcd2f4609c9daa */
namespace Bitrix\Iblock {
	/**
	 * EO_ElementProperty
	 * @see \Bitrix\Iblock\ElementPropertyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_ElementProperty setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getIblockPropertyId()
	 * @method \Bitrix\Iblock\EO_ElementProperty setIblockPropertyId(\int|\Bitrix\Main\DB\SqlExpression $iblockPropertyId)
	 * @method bool hasIblockPropertyId()
	 * @method bool isIblockPropertyIdFilled()
	 * @method bool isIblockPropertyIdChanged()
	 * @method \int remindActualIblockPropertyId()
	 * @method \int requireIblockPropertyId()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetIblockPropertyId()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetIblockPropertyId()
	 * @method \int fillIblockPropertyId()
	 * @method \int getIblockElementId()
	 * @method \Bitrix\Iblock\EO_ElementProperty setIblockElementId(\int|\Bitrix\Main\DB\SqlExpression $iblockElementId)
	 * @method bool hasIblockElementId()
	 * @method bool isIblockElementIdFilled()
	 * @method bool isIblockElementIdChanged()
	 * @method \int remindActualIblockElementId()
	 * @method \int requireIblockElementId()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetIblockElementId()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetIblockElementId()
	 * @method \int fillIblockElementId()
	 * @method \Bitrix\Iblock\EO_Element getElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualElement()
	 * @method \Bitrix\Iblock\EO_Element requireElement()
	 * @method \Bitrix\Iblock\EO_ElementProperty setElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Iblock\EO_ElementProperty resetElement()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetElement()
	 * @method bool hasElement()
	 * @method bool isElementFilled()
	 * @method bool isElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillElement()
	 * @method \string getValue()
	 * @method \Bitrix\Iblock\EO_ElementProperty setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetValue()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetValue()
	 * @method \string fillValue()
	 * @method \string getValueType()
	 * @method \Bitrix\Iblock\EO_ElementProperty setValueType(\string|\Bitrix\Main\DB\SqlExpression $valueType)
	 * @method bool hasValueType()
	 * @method bool isValueTypeFilled()
	 * @method bool isValueTypeChanged()
	 * @method \string remindActualValueType()
	 * @method \string requireValueType()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetValueType()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetValueType()
	 * @method \string fillValueType()
	 * @method \int getValueEnum()
	 * @method \Bitrix\Iblock\EO_ElementProperty setValueEnum(\int|\Bitrix\Main\DB\SqlExpression $valueEnum)
	 * @method bool hasValueEnum()
	 * @method bool isValueEnumFilled()
	 * @method bool isValueEnumChanged()
	 * @method \int remindActualValueEnum()
	 * @method \int requireValueEnum()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetValueEnum()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetValueEnum()
	 * @method \int fillValueEnum()
	 * @method \float getValueNum()
	 * @method \Bitrix\Iblock\EO_ElementProperty setValueNum(\float|\Bitrix\Main\DB\SqlExpression $valueNum)
	 * @method bool hasValueNum()
	 * @method bool isValueNumFilled()
	 * @method bool isValueNumChanged()
	 * @method \float remindActualValueNum()
	 * @method \float requireValueNum()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetValueNum()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetValueNum()
	 * @method \float fillValueNum()
	 * @method \string getDescription()
	 * @method \Bitrix\Iblock\EO_ElementProperty setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Iblock\EO_ElementProperty resetDescription()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetDescription()
	 * @method \string fillDescription()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration getEnum()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration remindActualEnum()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration requireEnum()
	 * @method \Bitrix\Iblock\EO_ElementProperty setEnum(\Bitrix\Iblock\EO_PropertyEnumeration $object)
	 * @method \Bitrix\Iblock\EO_ElementProperty resetEnum()
	 * @method \Bitrix\Iblock\EO_ElementProperty unsetEnum()
	 * @method bool hasEnum()
	 * @method bool isEnumFilled()
	 * @method bool isEnumChanged()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration fillEnum()
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
	 * @method \Bitrix\Iblock\EO_ElementProperty set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_ElementProperty reset($fieldName)
	 * @method \Bitrix\Iblock\EO_ElementProperty unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_ElementProperty wakeUp($data)
	 */
	class EO_ElementProperty {
		/* @var \Bitrix\Iblock\ElementPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\ElementPropertyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_ElementProperty_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getIblockPropertyIdList()
	 * @method \int[] fillIblockPropertyId()
	 * @method \int[] getIblockElementIdList()
	 * @method \int[] fillIblockElementId()
	 * @method \Bitrix\Iblock\EO_Element[] getElementList()
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection getElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillElement()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \string[] getValueTypeList()
	 * @method \string[] fillValueType()
	 * @method \int[] getValueEnumList()
	 * @method \int[] fillValueEnum()
	 * @method \float[] getValueNumList()
	 * @method \float[] fillValueNum()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration[] getEnumList()
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection getEnumCollection()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection fillEnum()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_ElementProperty $object)
	 * @method bool has(\Bitrix\Iblock\EO_ElementProperty $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_ElementProperty getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_ElementProperty[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_ElementProperty $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_ElementProperty_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_ElementProperty current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ElementProperty_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\ElementPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\ElementPropertyTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ElementProperty_Result exec()
	 * @method \Bitrix\Iblock\EO_ElementProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ElementProperty_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_ElementProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection fetchCollection()
	 */
	class EO_ElementProperty_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_ElementProperty createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_ElementProperty wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_ElementProperty_Collection wakeUpCollection($rows)
	 */
	class EO_ElementProperty_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\ElementTable:iblock/lib/elementtable.php:643e85b8c014fa2baa89cc9e22ecd431 */
namespace Bitrix\Iblock {
	/**
	 * EO_Element
	 * @see \Bitrix\Iblock\ElementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_Element setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\EO_Element setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\EO_Element resetTimestampX()
	 * @method \Bitrix\Iblock\EO_Element unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Iblock\EO_Element setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Iblock\EO_Element resetModifiedBy()
	 * @method \Bitrix\Iblock\EO_Element unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Iblock\EO_Element setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Iblock\EO_Element resetDateCreate()
	 * @method \Bitrix\Iblock\EO_Element unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Iblock\EO_Element setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Iblock\EO_Element resetCreatedBy()
	 * @method \Bitrix\Iblock\EO_Element unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_Element setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\EO_Element resetIblockId()
	 * @method \Bitrix\Iblock\EO_Element unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \int getIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Element setIblockSectionId(\int|\Bitrix\Main\DB\SqlExpression $iblockSectionId)
	 * @method bool hasIblockSectionId()
	 * @method bool isIblockSectionIdFilled()
	 * @method bool isIblockSectionIdChanged()
	 * @method \int remindActualIblockSectionId()
	 * @method \int requireIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Element resetIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Element unsetIblockSectionId()
	 * @method \int fillIblockSectionId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\EO_Element setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\EO_Element resetActive()
	 * @method \Bitrix\Iblock\EO_Element unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getActiveFrom()
	 * @method \Bitrix\Iblock\EO_Element setActiveFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeFrom)
	 * @method bool hasActiveFrom()
	 * @method bool isActiveFromFilled()
	 * @method bool isActiveFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime requireActiveFrom()
	 * @method \Bitrix\Iblock\EO_Element resetActiveFrom()
	 * @method \Bitrix\Iblock\EO_Element unsetActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime getActiveTo()
	 * @method \Bitrix\Iblock\EO_Element setActiveTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeTo)
	 * @method bool hasActiveTo()
	 * @method bool isActiveToFilled()
	 * @method bool isActiveToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveTo()
	 * @method \Bitrix\Main\Type\DateTime requireActiveTo()
	 * @method \Bitrix\Iblock\EO_Element resetActiveTo()
	 * @method \Bitrix\Iblock\EO_Element unsetActiveTo()
	 * @method \Bitrix\Main\Type\DateTime fillActiveTo()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\EO_Element setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\EO_Element resetSort()
	 * @method \Bitrix\Iblock\EO_Element unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\EO_Element setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\EO_Element resetName()
	 * @method \Bitrix\Iblock\EO_Element unsetName()
	 * @method \string fillName()
	 * @method \int getPreviewPicture()
	 * @method \Bitrix\Iblock\EO_Element setPreviewPicture(\int|\Bitrix\Main\DB\SqlExpression $previewPicture)
	 * @method bool hasPreviewPicture()
	 * @method bool isPreviewPictureFilled()
	 * @method bool isPreviewPictureChanged()
	 * @method \int remindActualPreviewPicture()
	 * @method \int requirePreviewPicture()
	 * @method \Bitrix\Iblock\EO_Element resetPreviewPicture()
	 * @method \Bitrix\Iblock\EO_Element unsetPreviewPicture()
	 * @method \int fillPreviewPicture()
	 * @method \string getPreviewText()
	 * @method \Bitrix\Iblock\EO_Element setPreviewText(\string|\Bitrix\Main\DB\SqlExpression $previewText)
	 * @method bool hasPreviewText()
	 * @method bool isPreviewTextFilled()
	 * @method bool isPreviewTextChanged()
	 * @method \string remindActualPreviewText()
	 * @method \string requirePreviewText()
	 * @method \Bitrix\Iblock\EO_Element resetPreviewText()
	 * @method \Bitrix\Iblock\EO_Element unsetPreviewText()
	 * @method \string fillPreviewText()
	 * @method \string getPreviewTextType()
	 * @method \Bitrix\Iblock\EO_Element setPreviewTextType(\string|\Bitrix\Main\DB\SqlExpression $previewTextType)
	 * @method bool hasPreviewTextType()
	 * @method bool isPreviewTextTypeFilled()
	 * @method bool isPreviewTextTypeChanged()
	 * @method \string remindActualPreviewTextType()
	 * @method \string requirePreviewTextType()
	 * @method \Bitrix\Iblock\EO_Element resetPreviewTextType()
	 * @method \Bitrix\Iblock\EO_Element unsetPreviewTextType()
	 * @method \string fillPreviewTextType()
	 * @method \int getDetailPicture()
	 * @method \Bitrix\Iblock\EO_Element setDetailPicture(\int|\Bitrix\Main\DB\SqlExpression $detailPicture)
	 * @method bool hasDetailPicture()
	 * @method bool isDetailPictureFilled()
	 * @method bool isDetailPictureChanged()
	 * @method \int remindActualDetailPicture()
	 * @method \int requireDetailPicture()
	 * @method \Bitrix\Iblock\EO_Element resetDetailPicture()
	 * @method \Bitrix\Iblock\EO_Element unsetDetailPicture()
	 * @method \int fillDetailPicture()
	 * @method \string getDetailText()
	 * @method \Bitrix\Iblock\EO_Element setDetailText(\string|\Bitrix\Main\DB\SqlExpression $detailText)
	 * @method bool hasDetailText()
	 * @method bool isDetailTextFilled()
	 * @method bool isDetailTextChanged()
	 * @method \string remindActualDetailText()
	 * @method \string requireDetailText()
	 * @method \Bitrix\Iblock\EO_Element resetDetailText()
	 * @method \Bitrix\Iblock\EO_Element unsetDetailText()
	 * @method \string fillDetailText()
	 * @method \string getDetailTextType()
	 * @method \Bitrix\Iblock\EO_Element setDetailTextType(\string|\Bitrix\Main\DB\SqlExpression $detailTextType)
	 * @method bool hasDetailTextType()
	 * @method bool isDetailTextTypeFilled()
	 * @method bool isDetailTextTypeChanged()
	 * @method \string remindActualDetailTextType()
	 * @method \string requireDetailTextType()
	 * @method \Bitrix\Iblock\EO_Element resetDetailTextType()
	 * @method \Bitrix\Iblock\EO_Element unsetDetailTextType()
	 * @method \string fillDetailTextType()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Iblock\EO_Element setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Iblock\EO_Element resetSearchableContent()
	 * @method \Bitrix\Iblock\EO_Element unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \int getWfStatusId()
	 * @method \Bitrix\Iblock\EO_Element setWfStatusId(\int|\Bitrix\Main\DB\SqlExpression $wfStatusId)
	 * @method bool hasWfStatusId()
	 * @method bool isWfStatusIdFilled()
	 * @method bool isWfStatusIdChanged()
	 * @method \int remindActualWfStatusId()
	 * @method \int requireWfStatusId()
	 * @method \Bitrix\Iblock\EO_Element resetWfStatusId()
	 * @method \Bitrix\Iblock\EO_Element unsetWfStatusId()
	 * @method \int fillWfStatusId()
	 * @method \int getWfParentElementId()
	 * @method \Bitrix\Iblock\EO_Element setWfParentElementId(\int|\Bitrix\Main\DB\SqlExpression $wfParentElementId)
	 * @method bool hasWfParentElementId()
	 * @method bool isWfParentElementIdFilled()
	 * @method bool isWfParentElementIdChanged()
	 * @method \int remindActualWfParentElementId()
	 * @method \int requireWfParentElementId()
	 * @method \Bitrix\Iblock\EO_Element resetWfParentElementId()
	 * @method \Bitrix\Iblock\EO_Element unsetWfParentElementId()
	 * @method \int fillWfParentElementId()
	 * @method \string getWfNew()
	 * @method \Bitrix\Iblock\EO_Element setWfNew(\string|\Bitrix\Main\DB\SqlExpression $wfNew)
	 * @method bool hasWfNew()
	 * @method bool isWfNewFilled()
	 * @method bool isWfNewChanged()
	 * @method \string remindActualWfNew()
	 * @method \string requireWfNew()
	 * @method \Bitrix\Iblock\EO_Element resetWfNew()
	 * @method \Bitrix\Iblock\EO_Element unsetWfNew()
	 * @method \string fillWfNew()
	 * @method \int getWfLockedBy()
	 * @method \Bitrix\Iblock\EO_Element setWfLockedBy(\int|\Bitrix\Main\DB\SqlExpression $wfLockedBy)
	 * @method bool hasWfLockedBy()
	 * @method bool isWfLockedByFilled()
	 * @method bool isWfLockedByChanged()
	 * @method \int remindActualWfLockedBy()
	 * @method \int requireWfLockedBy()
	 * @method \Bitrix\Iblock\EO_Element resetWfLockedBy()
	 * @method \Bitrix\Iblock\EO_Element unsetWfLockedBy()
	 * @method \int fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime getWfDateLock()
	 * @method \Bitrix\Iblock\EO_Element setWfDateLock(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $wfDateLock)
	 * @method bool hasWfDateLock()
	 * @method bool isWfDateLockFilled()
	 * @method bool isWfDateLockChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime requireWfDateLock()
	 * @method \Bitrix\Iblock\EO_Element resetWfDateLock()
	 * @method \Bitrix\Iblock\EO_Element unsetWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime fillWfDateLock()
	 * @method \string getWfComments()
	 * @method \Bitrix\Iblock\EO_Element setWfComments(\string|\Bitrix\Main\DB\SqlExpression $wfComments)
	 * @method bool hasWfComments()
	 * @method bool isWfCommentsFilled()
	 * @method bool isWfCommentsChanged()
	 * @method \string remindActualWfComments()
	 * @method \string requireWfComments()
	 * @method \Bitrix\Iblock\EO_Element resetWfComments()
	 * @method \Bitrix\Iblock\EO_Element unsetWfComments()
	 * @method \string fillWfComments()
	 * @method \boolean getInSections()
	 * @method \Bitrix\Iblock\EO_Element setInSections(\boolean|\Bitrix\Main\DB\SqlExpression $inSections)
	 * @method bool hasInSections()
	 * @method bool isInSectionsFilled()
	 * @method bool isInSectionsChanged()
	 * @method \boolean remindActualInSections()
	 * @method \boolean requireInSections()
	 * @method \Bitrix\Iblock\EO_Element resetInSections()
	 * @method \Bitrix\Iblock\EO_Element unsetInSections()
	 * @method \boolean fillInSections()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\EO_Element setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\EO_Element resetXmlId()
	 * @method \Bitrix\Iblock\EO_Element unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\EO_Element setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\EO_Element resetCode()
	 * @method \Bitrix\Iblock\EO_Element unsetCode()
	 * @method \string fillCode()
	 * @method \string getTags()
	 * @method \Bitrix\Iblock\EO_Element setTags(\string|\Bitrix\Main\DB\SqlExpression $tags)
	 * @method bool hasTags()
	 * @method bool isTagsFilled()
	 * @method bool isTagsChanged()
	 * @method \string remindActualTags()
	 * @method \string requireTags()
	 * @method \Bitrix\Iblock\EO_Element resetTags()
	 * @method \Bitrix\Iblock\EO_Element unsetTags()
	 * @method \string fillTags()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\EO_Element setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\EO_Element resetTmpId()
	 * @method \Bitrix\Iblock\EO_Element unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \int getShowCounter()
	 * @method \Bitrix\Iblock\EO_Element setShowCounter(\int|\Bitrix\Main\DB\SqlExpression $showCounter)
	 * @method bool hasShowCounter()
	 * @method bool isShowCounterFilled()
	 * @method bool isShowCounterChanged()
	 * @method \int remindActualShowCounter()
	 * @method \int requireShowCounter()
	 * @method \Bitrix\Iblock\EO_Element resetShowCounter()
	 * @method \Bitrix\Iblock\EO_Element unsetShowCounter()
	 * @method \int fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime getShowCounterStart()
	 * @method \Bitrix\Iblock\EO_Element setShowCounterStart(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $showCounterStart)
	 * @method bool hasShowCounterStart()
	 * @method bool isShowCounterStartFilled()
	 * @method bool isShowCounterStartChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime requireShowCounterStart()
	 * @method \Bitrix\Iblock\EO_Element resetShowCounterStart()
	 * @method \Bitrix\Iblock\EO_Element unsetShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_Element setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_Element resetIblock()
	 * @method \Bitrix\Iblock\EO_Element unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\EO_Element getWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element requireWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element setWfParentElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Iblock\EO_Element resetWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element unsetWfParentElement()
	 * @method bool hasWfParentElement()
	 * @method bool isWfParentElementFilled()
	 * @method bool isWfParentElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section getIblockSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualIblockSection()
	 * @method \Bitrix\Iblock\EO_Section requireIblockSection()
	 * @method \Bitrix\Iblock\EO_Element setIblockSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\EO_Element resetIblockSection()
	 * @method \Bitrix\Iblock\EO_Element unsetIblockSection()
	 * @method bool hasIblockSection()
	 * @method bool isIblockSectionFilled()
	 * @method bool isIblockSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillIblockSection()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Iblock\EO_Element setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\EO_Element resetModifiedByUser()
	 * @method \Bitrix\Iblock\EO_Element unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Iblock\EO_Element setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\EO_Element resetCreatedByUser()
	 * @method \Bitrix\Iblock\EO_Element unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getWfLockedByUser()
	 * @method \Bitrix\Main\EO_User remindActualWfLockedByUser()
	 * @method \Bitrix\Main\EO_User requireWfLockedByUser()
	 * @method \Bitrix\Iblock\EO_Element setWfLockedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\EO_Element resetWfLockedByUser()
	 * @method \Bitrix\Iblock\EO_Element unsetWfLockedByUser()
	 * @method bool hasWfLockedByUser()
	 * @method bool isWfLockedByUserFilled()
	 * @method bool isWfLockedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillWfLockedByUser()
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
	 * @method \Bitrix\Iblock\EO_Element set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_Element reset($fieldName)
	 * @method \Bitrix\Iblock\EO_Element unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_Element wakeUp($data)
	 */
	class EO_Element {
		/* @var \Bitrix\Iblock\ElementTable */
		static public $dataClass = '\Bitrix\Iblock\ElementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Element_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \int[] getIblockSectionIdList()
	 * @method \int[] fillIblockSectionId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveTo()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getPreviewPictureList()
	 * @method \int[] fillPreviewPicture()
	 * @method \string[] getPreviewTextList()
	 * @method \string[] fillPreviewText()
	 * @method \string[] getPreviewTextTypeList()
	 * @method \string[] fillPreviewTextType()
	 * @method \int[] getDetailPictureList()
	 * @method \int[] fillDetailPicture()
	 * @method \string[] getDetailTextList()
	 * @method \string[] fillDetailText()
	 * @method \string[] getDetailTextTypeList()
	 * @method \string[] fillDetailTextType()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \int[] getWfStatusIdList()
	 * @method \int[] fillWfStatusId()
	 * @method \int[] getWfParentElementIdList()
	 * @method \int[] fillWfParentElementId()
	 * @method \string[] getWfNewList()
	 * @method \string[] fillWfNew()
	 * @method \int[] getWfLockedByList()
	 * @method \int[] fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getWfDateLockList()
	 * @method \Bitrix\Main\Type\DateTime[] fillWfDateLock()
	 * @method \string[] getWfCommentsList()
	 * @method \string[] fillWfComments()
	 * @method \boolean[] getInSectionsList()
	 * @method \boolean[] fillInSections()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTagsList()
	 * @method \string[] fillTags()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \int[] getShowCounterList()
	 * @method \int[] fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime[] getShowCounterStartList()
	 * @method \Bitrix\Main\Type\DateTime[] fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\EO_Element[] getWfParentElementList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getWfParentElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section[] getIblockSectionList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getIblockSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillIblockSection()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getWfLockedByUserList()
	 * @method \Bitrix\Iblock\EO_Element_Collection getWfLockedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillWfLockedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_Element $object)
	 * @method bool has(\Bitrix\Iblock\EO_Element $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Element getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Element[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_Element $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Element_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_Element current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Element_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\ElementTable */
		static public $dataClass = '\Bitrix\Iblock\ElementTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Element_Result exec()
	 * @method \Bitrix\Iblock\EO_Element fetchObject()
	 * @method \Bitrix\Iblock\EO_Element_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Element_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_Element fetchObject()
	 * @method \Bitrix\Iblock\EO_Element_Collection fetchCollection()
	 */
	class EO_Element_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_Element createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Element_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_Element wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Element_Collection wakeUpCollection($rows)
	 */
	class EO_Element_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockTable:iblock/lib/iblocktable.php:09185b290936f8e46030834abf457fd0 */
namespace Bitrix\Iblock {
	/**
	 * Iblock
	 * @see \Bitrix\Iblock\IblockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\Iblock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\Iblock setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\Iblock resetTimestampX()
	 * @method \Bitrix\Iblock\Iblock unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getIblockTypeId()
	 * @method \Bitrix\Iblock\Iblock setIblockTypeId(\string|\Bitrix\Main\DB\SqlExpression $iblockTypeId)
	 * @method bool hasIblockTypeId()
	 * @method bool isIblockTypeIdFilled()
	 * @method bool isIblockTypeIdChanged()
	 * @method \string remindActualIblockTypeId()
	 * @method \string requireIblockTypeId()
	 * @method \Bitrix\Iblock\Iblock resetIblockTypeId()
	 * @method \Bitrix\Iblock\Iblock unsetIblockTypeId()
	 * @method \string fillIblockTypeId()
	 * @method \string getLid()
	 * @method \Bitrix\Iblock\Iblock setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Iblock\Iblock resetLid()
	 * @method \Bitrix\Iblock\Iblock unsetLid()
	 * @method \string fillLid()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\Iblock setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\Iblock resetCode()
	 * @method \Bitrix\Iblock\Iblock unsetCode()
	 * @method \string fillCode()
	 * @method \string getApiCode()
	 * @method \Bitrix\Iblock\Iblock setApiCode(\string|\Bitrix\Main\DB\SqlExpression $apiCode)
	 * @method bool hasApiCode()
	 * @method bool isApiCodeFilled()
	 * @method bool isApiCodeChanged()
	 * @method \string remindActualApiCode()
	 * @method \string requireApiCode()
	 * @method \Bitrix\Iblock\Iblock resetApiCode()
	 * @method \Bitrix\Iblock\Iblock unsetApiCode()
	 * @method \string fillApiCode()
	 * @method \boolean getRestOn()
	 * @method \Bitrix\Iblock\Iblock setRestOn(\boolean|\Bitrix\Main\DB\SqlExpression $restOn)
	 * @method bool hasRestOn()
	 * @method bool isRestOnFilled()
	 * @method bool isRestOnChanged()
	 * @method \boolean remindActualRestOn()
	 * @method \boolean requireRestOn()
	 * @method \Bitrix\Iblock\Iblock resetRestOn()
	 * @method \Bitrix\Iblock\Iblock unsetRestOn()
	 * @method \boolean fillRestOn()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\Iblock setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\Iblock resetName()
	 * @method \Bitrix\Iblock\Iblock unsetName()
	 * @method \string fillName()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\Iblock setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\Iblock resetActive()
	 * @method \Bitrix\Iblock\Iblock unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\Iblock setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\Iblock resetSort()
	 * @method \Bitrix\Iblock\Iblock unsetSort()
	 * @method \int fillSort()
	 * @method \string getListPageUrl()
	 * @method \Bitrix\Iblock\Iblock setListPageUrl(\string|\Bitrix\Main\DB\SqlExpression $listPageUrl)
	 * @method bool hasListPageUrl()
	 * @method bool isListPageUrlFilled()
	 * @method bool isListPageUrlChanged()
	 * @method \string remindActualListPageUrl()
	 * @method \string requireListPageUrl()
	 * @method \Bitrix\Iblock\Iblock resetListPageUrl()
	 * @method \Bitrix\Iblock\Iblock unsetListPageUrl()
	 * @method \string fillListPageUrl()
	 * @method \string getDetailPageUrl()
	 * @method \Bitrix\Iblock\Iblock setDetailPageUrl(\string|\Bitrix\Main\DB\SqlExpression $detailPageUrl)
	 * @method bool hasDetailPageUrl()
	 * @method bool isDetailPageUrlFilled()
	 * @method bool isDetailPageUrlChanged()
	 * @method \string remindActualDetailPageUrl()
	 * @method \string requireDetailPageUrl()
	 * @method \Bitrix\Iblock\Iblock resetDetailPageUrl()
	 * @method \Bitrix\Iblock\Iblock unsetDetailPageUrl()
	 * @method \string fillDetailPageUrl()
	 * @method \string getSectionPageUrl()
	 * @method \Bitrix\Iblock\Iblock setSectionPageUrl(\string|\Bitrix\Main\DB\SqlExpression $sectionPageUrl)
	 * @method bool hasSectionPageUrl()
	 * @method bool isSectionPageUrlFilled()
	 * @method bool isSectionPageUrlChanged()
	 * @method \string remindActualSectionPageUrl()
	 * @method \string requireSectionPageUrl()
	 * @method \Bitrix\Iblock\Iblock resetSectionPageUrl()
	 * @method \Bitrix\Iblock\Iblock unsetSectionPageUrl()
	 * @method \string fillSectionPageUrl()
	 * @method \string getCanonicalPageUrl()
	 * @method \Bitrix\Iblock\Iblock setCanonicalPageUrl(\string|\Bitrix\Main\DB\SqlExpression $canonicalPageUrl)
	 * @method bool hasCanonicalPageUrl()
	 * @method bool isCanonicalPageUrlFilled()
	 * @method bool isCanonicalPageUrlChanged()
	 * @method \string remindActualCanonicalPageUrl()
	 * @method \string requireCanonicalPageUrl()
	 * @method \Bitrix\Iblock\Iblock resetCanonicalPageUrl()
	 * @method \Bitrix\Iblock\Iblock unsetCanonicalPageUrl()
	 * @method \string fillCanonicalPageUrl()
	 * @method \int getPicture()
	 * @method \Bitrix\Iblock\Iblock setPicture(\int|\Bitrix\Main\DB\SqlExpression $picture)
	 * @method bool hasPicture()
	 * @method bool isPictureFilled()
	 * @method bool isPictureChanged()
	 * @method \int remindActualPicture()
	 * @method \int requirePicture()
	 * @method \Bitrix\Iblock\Iblock resetPicture()
	 * @method \Bitrix\Iblock\Iblock unsetPicture()
	 * @method \int fillPicture()
	 * @method \string getDescription()
	 * @method \Bitrix\Iblock\Iblock setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Iblock\Iblock resetDescription()
	 * @method \Bitrix\Iblock\Iblock unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getDescriptionType()
	 * @method \Bitrix\Iblock\Iblock setDescriptionType(\string|\Bitrix\Main\DB\SqlExpression $descriptionType)
	 * @method bool hasDescriptionType()
	 * @method bool isDescriptionTypeFilled()
	 * @method bool isDescriptionTypeChanged()
	 * @method \string remindActualDescriptionType()
	 * @method \string requireDescriptionType()
	 * @method \Bitrix\Iblock\Iblock resetDescriptionType()
	 * @method \Bitrix\Iblock\Iblock unsetDescriptionType()
	 * @method \string fillDescriptionType()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\Iblock setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\Iblock resetXmlId()
	 * @method \Bitrix\Iblock\Iblock unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\Iblock setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\Iblock resetTmpId()
	 * @method \Bitrix\Iblock\Iblock unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \boolean getIndexElement()
	 * @method \Bitrix\Iblock\Iblock setIndexElement(\boolean|\Bitrix\Main\DB\SqlExpression $indexElement)
	 * @method bool hasIndexElement()
	 * @method bool isIndexElementFilled()
	 * @method bool isIndexElementChanged()
	 * @method \boolean remindActualIndexElement()
	 * @method \boolean requireIndexElement()
	 * @method \Bitrix\Iblock\Iblock resetIndexElement()
	 * @method \Bitrix\Iblock\Iblock unsetIndexElement()
	 * @method \boolean fillIndexElement()
	 * @method \boolean getIndexSection()
	 * @method \Bitrix\Iblock\Iblock setIndexSection(\boolean|\Bitrix\Main\DB\SqlExpression $indexSection)
	 * @method bool hasIndexSection()
	 * @method bool isIndexSectionFilled()
	 * @method bool isIndexSectionChanged()
	 * @method \boolean remindActualIndexSection()
	 * @method \boolean requireIndexSection()
	 * @method \Bitrix\Iblock\Iblock resetIndexSection()
	 * @method \Bitrix\Iblock\Iblock unsetIndexSection()
	 * @method \boolean fillIndexSection()
	 * @method \boolean getWorkflow()
	 * @method \Bitrix\Iblock\Iblock setWorkflow(\boolean|\Bitrix\Main\DB\SqlExpression $workflow)
	 * @method bool hasWorkflow()
	 * @method bool isWorkflowFilled()
	 * @method bool isWorkflowChanged()
	 * @method \boolean remindActualWorkflow()
	 * @method \boolean requireWorkflow()
	 * @method \Bitrix\Iblock\Iblock resetWorkflow()
	 * @method \Bitrix\Iblock\Iblock unsetWorkflow()
	 * @method \boolean fillWorkflow()
	 * @method \boolean getBizproc()
	 * @method \Bitrix\Iblock\Iblock setBizproc(\boolean|\Bitrix\Main\DB\SqlExpression $bizproc)
	 * @method bool hasBizproc()
	 * @method bool isBizprocFilled()
	 * @method bool isBizprocChanged()
	 * @method \boolean remindActualBizproc()
	 * @method \boolean requireBizproc()
	 * @method \Bitrix\Iblock\Iblock resetBizproc()
	 * @method \Bitrix\Iblock\Iblock unsetBizproc()
	 * @method \boolean fillBizproc()
	 * @method \string getSectionChooser()
	 * @method \Bitrix\Iblock\Iblock setSectionChooser(\string|\Bitrix\Main\DB\SqlExpression $sectionChooser)
	 * @method bool hasSectionChooser()
	 * @method bool isSectionChooserFilled()
	 * @method bool isSectionChooserChanged()
	 * @method \string remindActualSectionChooser()
	 * @method \string requireSectionChooser()
	 * @method \Bitrix\Iblock\Iblock resetSectionChooser()
	 * @method \Bitrix\Iblock\Iblock unsetSectionChooser()
	 * @method \string fillSectionChooser()
	 * @method \string getListMode()
	 * @method \Bitrix\Iblock\Iblock setListMode(\string|\Bitrix\Main\DB\SqlExpression $listMode)
	 * @method bool hasListMode()
	 * @method bool isListModeFilled()
	 * @method bool isListModeChanged()
	 * @method \string remindActualListMode()
	 * @method \string requireListMode()
	 * @method \Bitrix\Iblock\Iblock resetListMode()
	 * @method \Bitrix\Iblock\Iblock unsetListMode()
	 * @method \string fillListMode()
	 * @method \string getRightsMode()
	 * @method \Bitrix\Iblock\Iblock setRightsMode(\string|\Bitrix\Main\DB\SqlExpression $rightsMode)
	 * @method bool hasRightsMode()
	 * @method bool isRightsModeFilled()
	 * @method bool isRightsModeChanged()
	 * @method \string remindActualRightsMode()
	 * @method \string requireRightsMode()
	 * @method \Bitrix\Iblock\Iblock resetRightsMode()
	 * @method \Bitrix\Iblock\Iblock unsetRightsMode()
	 * @method \string fillRightsMode()
	 * @method \boolean getSectionProperty()
	 * @method \Bitrix\Iblock\Iblock setSectionProperty(\boolean|\Bitrix\Main\DB\SqlExpression $sectionProperty)
	 * @method bool hasSectionProperty()
	 * @method bool isSectionPropertyFilled()
	 * @method bool isSectionPropertyChanged()
	 * @method \boolean remindActualSectionProperty()
	 * @method \boolean requireSectionProperty()
	 * @method \Bitrix\Iblock\Iblock resetSectionProperty()
	 * @method \Bitrix\Iblock\Iblock unsetSectionProperty()
	 * @method \boolean fillSectionProperty()
	 * @method \string getPropertyIndex()
	 * @method \Bitrix\Iblock\Iblock setPropertyIndex(\string|\Bitrix\Main\DB\SqlExpression $propertyIndex)
	 * @method bool hasPropertyIndex()
	 * @method bool isPropertyIndexFilled()
	 * @method bool isPropertyIndexChanged()
	 * @method \string remindActualPropertyIndex()
	 * @method \string requirePropertyIndex()
	 * @method \Bitrix\Iblock\Iblock resetPropertyIndex()
	 * @method \Bitrix\Iblock\Iblock unsetPropertyIndex()
	 * @method \string fillPropertyIndex()
	 * @method \string getVersion()
	 * @method \Bitrix\Iblock\Iblock setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Iblock\Iblock resetVersion()
	 * @method \Bitrix\Iblock\Iblock unsetVersion()
	 * @method \string fillVersion()
	 * @method \int getLastConvElement()
	 * @method \Bitrix\Iblock\Iblock setLastConvElement(\int|\Bitrix\Main\DB\SqlExpression $lastConvElement)
	 * @method bool hasLastConvElement()
	 * @method bool isLastConvElementFilled()
	 * @method bool isLastConvElementChanged()
	 * @method \int remindActualLastConvElement()
	 * @method \int requireLastConvElement()
	 * @method \Bitrix\Iblock\Iblock resetLastConvElement()
	 * @method \Bitrix\Iblock\Iblock unsetLastConvElement()
	 * @method \int fillLastConvElement()
	 * @method \int getSocnetGroupId()
	 * @method \Bitrix\Iblock\Iblock setSocnetGroupId(\int|\Bitrix\Main\DB\SqlExpression $socnetGroupId)
	 * @method bool hasSocnetGroupId()
	 * @method bool isSocnetGroupIdFilled()
	 * @method bool isSocnetGroupIdChanged()
	 * @method \int remindActualSocnetGroupId()
	 * @method \int requireSocnetGroupId()
	 * @method \Bitrix\Iblock\Iblock resetSocnetGroupId()
	 * @method \Bitrix\Iblock\Iblock unsetSocnetGroupId()
	 * @method \int fillSocnetGroupId()
	 * @method \string getEditFileBefore()
	 * @method \Bitrix\Iblock\Iblock setEditFileBefore(\string|\Bitrix\Main\DB\SqlExpression $editFileBefore)
	 * @method bool hasEditFileBefore()
	 * @method bool isEditFileBeforeFilled()
	 * @method bool isEditFileBeforeChanged()
	 * @method \string remindActualEditFileBefore()
	 * @method \string requireEditFileBefore()
	 * @method \Bitrix\Iblock\Iblock resetEditFileBefore()
	 * @method \Bitrix\Iblock\Iblock unsetEditFileBefore()
	 * @method \string fillEditFileBefore()
	 * @method \string getEditFileAfter()
	 * @method \Bitrix\Iblock\Iblock setEditFileAfter(\string|\Bitrix\Main\DB\SqlExpression $editFileAfter)
	 * @method bool hasEditFileAfter()
	 * @method bool isEditFileAfterFilled()
	 * @method bool isEditFileAfterChanged()
	 * @method \string remindActualEditFileAfter()
	 * @method \string requireEditFileAfter()
	 * @method \Bitrix\Iblock\Iblock resetEditFileAfter()
	 * @method \Bitrix\Iblock\Iblock unsetEditFileAfter()
	 * @method \string fillEditFileAfter()
	 * @method \Bitrix\Iblock\EO_Type getType()
	 * @method \Bitrix\Iblock\EO_Type remindActualType()
	 * @method \Bitrix\Iblock\EO_Type requireType()
	 * @method \Bitrix\Iblock\Iblock setType(\Bitrix\Iblock\EO_Type $object)
	 * @method \Bitrix\Iblock\Iblock resetType()
	 * @method \Bitrix\Iblock\Iblock unsetType()
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \Bitrix\Iblock\EO_Type fillType()
	 * @method \Bitrix\Iblock\EO_Property_Collection getProperties()
	 * @method \Bitrix\Iblock\EO_Property_Collection requireProperties()
	 * @method \Bitrix\Iblock\EO_Property_Collection fillProperties()
	 * @method bool hasProperties()
	 * @method bool isPropertiesFilled()
	 * @method bool isPropertiesChanged()
	 * @method void addToProperties(\Bitrix\Iblock\Property $property)
	 * @method void removeFromProperties(\Bitrix\Iblock\Property $property)
	 * @method void removeAllProperties()
	 * @method \Bitrix\Iblock\Iblock resetProperties()
	 * @method \Bitrix\Iblock\Iblock unsetProperties()
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
	 * @method \Bitrix\Iblock\Iblock set($fieldName, $value)
	 * @method \Bitrix\Iblock\Iblock reset($fieldName)
	 * @method \Bitrix\Iblock\Iblock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\Iblock wakeUp($data)
	 */
	class EO_Iblock {
		/* @var \Bitrix\Iblock\IblockTable */
		static public $dataClass = '\Bitrix\Iblock\IblockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Iblock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getIblockTypeIdList()
	 * @method \string[] fillIblockTypeId()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getApiCodeList()
	 * @method \string[] fillApiCode()
	 * @method \boolean[] getRestOnList()
	 * @method \boolean[] fillRestOn()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getListPageUrlList()
	 * @method \string[] fillListPageUrl()
	 * @method \string[] getDetailPageUrlList()
	 * @method \string[] fillDetailPageUrl()
	 * @method \string[] getSectionPageUrlList()
	 * @method \string[] fillSectionPageUrl()
	 * @method \string[] getCanonicalPageUrlList()
	 * @method \string[] fillCanonicalPageUrl()
	 * @method \int[] getPictureList()
	 * @method \int[] fillPicture()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getDescriptionTypeList()
	 * @method \string[] fillDescriptionType()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \boolean[] getIndexElementList()
	 * @method \boolean[] fillIndexElement()
	 * @method \boolean[] getIndexSectionList()
	 * @method \boolean[] fillIndexSection()
	 * @method \boolean[] getWorkflowList()
	 * @method \boolean[] fillWorkflow()
	 * @method \boolean[] getBizprocList()
	 * @method \boolean[] fillBizproc()
	 * @method \string[] getSectionChooserList()
	 * @method \string[] fillSectionChooser()
	 * @method \string[] getListModeList()
	 * @method \string[] fillListMode()
	 * @method \string[] getRightsModeList()
	 * @method \string[] fillRightsMode()
	 * @method \boolean[] getSectionPropertyList()
	 * @method \boolean[] fillSectionProperty()
	 * @method \string[] getPropertyIndexList()
	 * @method \string[] fillPropertyIndex()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \int[] getLastConvElementList()
	 * @method \int[] fillLastConvElement()
	 * @method \int[] getSocnetGroupIdList()
	 * @method \int[] fillSocnetGroupId()
	 * @method \string[] getEditFileBeforeList()
	 * @method \string[] fillEditFileBefore()
	 * @method \string[] getEditFileAfterList()
	 * @method \string[] fillEditFileAfter()
	 * @method \Bitrix\Iblock\EO_Type[] getTypeList()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection getTypeCollection()
	 * @method \Bitrix\Iblock\EO_Type_Collection fillType()
	 * @method \Bitrix\Iblock\EO_Property_Collection[] getPropertiesList()
	 * @method \Bitrix\Iblock\EO_Property_Collection getPropertiesCollection()
	 * @method \Bitrix\Iblock\EO_Property_Collection fillProperties()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\Iblock $object)
	 * @method bool has(\Bitrix\Iblock\Iblock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\Iblock getByPrimary($primary)
	 * @method \Bitrix\Iblock\Iblock[] getAll()
	 * @method bool remove(\Bitrix\Iblock\Iblock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Iblock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\Iblock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Iblock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockTable */
		static public $dataClass = '\Bitrix\Iblock\IblockTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Iblock_Result exec()
	 * @method \Bitrix\Iblock\Iblock fetchObject()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Iblock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\Iblock fetchObject()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fetchCollection()
	 */
	class EO_Iblock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\Iblock createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Iblock_Collection createCollection()
	 * @method \Bitrix\Iblock\Iblock wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Iblock_Collection wakeUpCollection($rows)
	 */
	class EO_Iblock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockFieldTable:iblock/lib/iblockfield.php:454adef96c4ef621b9a766741b7dcb9e */
namespace Bitrix\Iblock {
	/**
	 * EO_IblockField
	 * @see \Bitrix\Iblock\IblockFieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_IblockField setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \string getFieldId()
	 * @method \Bitrix\Iblock\EO_IblockField setFieldId(\string|\Bitrix\Main\DB\SqlExpression $fieldId)
	 * @method bool hasFieldId()
	 * @method bool isFieldIdFilled()
	 * @method bool isFieldIdChanged()
	 * @method \boolean getIsRequired()
	 * @method \Bitrix\Iblock\EO_IblockField setIsRequired(\boolean|\Bitrix\Main\DB\SqlExpression $isRequired)
	 * @method bool hasIsRequired()
	 * @method bool isIsRequiredFilled()
	 * @method bool isIsRequiredChanged()
	 * @method \boolean remindActualIsRequired()
	 * @method \boolean requireIsRequired()
	 * @method \Bitrix\Iblock\EO_IblockField resetIsRequired()
	 * @method \Bitrix\Iblock\EO_IblockField unsetIsRequired()
	 * @method \boolean fillIsRequired()
	 * @method \string getDefaultValue()
	 * @method \Bitrix\Iblock\EO_IblockField setDefaultValue(\string|\Bitrix\Main\DB\SqlExpression $defaultValue)
	 * @method bool hasDefaultValue()
	 * @method bool isDefaultValueFilled()
	 * @method bool isDefaultValueChanged()
	 * @method \string remindActualDefaultValue()
	 * @method \string requireDefaultValue()
	 * @method \Bitrix\Iblock\EO_IblockField resetDefaultValue()
	 * @method \Bitrix\Iblock\EO_IblockField unsetDefaultValue()
	 * @method \string fillDefaultValue()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_IblockField setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_IblockField resetIblock()
	 * @method \Bitrix\Iblock\EO_IblockField unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\EO_IblockField set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_IblockField reset($fieldName)
	 * @method \Bitrix\Iblock\EO_IblockField unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_IblockField wakeUp($data)
	 */
	class EO_IblockField {
		/* @var \Bitrix\Iblock\IblockFieldTable */
		static public $dataClass = '\Bitrix\Iblock\IblockFieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_IblockField_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \string[] getFieldIdList()
	 * @method \boolean[] getIsRequiredList()
	 * @method \boolean[] fillIsRequired()
	 * @method \string[] getDefaultValueList()
	 * @method \string[] fillDefaultValue()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_IblockField_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_IblockField $object)
	 * @method bool has(\Bitrix\Iblock\EO_IblockField $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockField getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockField[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_IblockField $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_IblockField_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_IblockField current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_IblockField_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockFieldTable */
		static public $dataClass = '\Bitrix\Iblock\IblockFieldTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_IblockField_Result exec()
	 * @method \Bitrix\Iblock\EO_IblockField fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockField_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_IblockField_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockField fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockField_Collection fetchCollection()
	 */
	class EO_IblockField_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockField createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_IblockField_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_IblockField wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_IblockField_Collection wakeUpCollection($rows)
	 */
	class EO_IblockField_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockGroupTable:iblock/lib/iblockgroup.php:2918f47edc6784c824ad1232c801ede1 */
namespace Bitrix\Iblock {
	/**
	 * EO_IblockGroup
	 * @see \Bitrix\Iblock\IblockGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_IblockGroup setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int getGroupId()
	 * @method \Bitrix\Iblock\EO_IblockGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \string getPermission()
	 * @method \Bitrix\Iblock\EO_IblockGroup setPermission(\string|\Bitrix\Main\DB\SqlExpression $permission)
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \string remindActualPermission()
	 * @method \string requirePermission()
	 * @method \Bitrix\Iblock\EO_IblockGroup resetPermission()
	 * @method \Bitrix\Iblock\EO_IblockGroup unsetPermission()
	 * @method \string fillPermission()
	 * @method \Bitrix\Main\EO_Group getGroup()
	 * @method \Bitrix\Main\EO_Group remindActualGroup()
	 * @method \Bitrix\Main\EO_Group requireGroup()
	 * @method \Bitrix\Iblock\EO_IblockGroup setGroup(\Bitrix\Main\EO_Group $object)
	 * @method \Bitrix\Iblock\EO_IblockGroup resetGroup()
	 * @method \Bitrix\Iblock\EO_IblockGroup unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Main\EO_Group fillGroup()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_IblockGroup setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_IblockGroup resetIblock()
	 * @method \Bitrix\Iblock\EO_IblockGroup unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\EO_IblockGroup set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_IblockGroup reset($fieldName)
	 * @method \Bitrix\Iblock\EO_IblockGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_IblockGroup wakeUp($data)
	 */
	class EO_IblockGroup {
		/* @var \Bitrix\Iblock\IblockGroupTable */
		static public $dataClass = '\Bitrix\Iblock\IblockGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_IblockGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \int[] getGroupIdList()
	 * @method \string[] getPermissionList()
	 * @method \string[] fillPermission()
	 * @method \Bitrix\Main\EO_Group[] getGroupList()
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection getGroupCollection()
	 * @method \Bitrix\Main\EO_Group_Collection fillGroup()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_IblockGroup $object)
	 * @method bool has(\Bitrix\Iblock\EO_IblockGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockGroup getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockGroup[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_IblockGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_IblockGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_IblockGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_IblockGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockGroupTable */
		static public $dataClass = '\Bitrix\Iblock\IblockGroupTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_IblockGroup_Result exec()
	 * @method \Bitrix\Iblock\EO_IblockGroup fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_IblockGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockGroup fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection fetchCollection()
	 */
	class EO_IblockGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_IblockGroup wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_IblockGroup_Collection wakeUpCollection($rows)
	 */
	class EO_IblockGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockMessageTable:iblock/lib/iblockmessage.php:ee5d0204cf4f5075e80706bb63b11dcf */
namespace Bitrix\Iblock {
	/**
	 * EO_IblockMessage
	 * @see \Bitrix\Iblock\IblockMessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_IblockMessage setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \string getMessageId()
	 * @method \Bitrix\Iblock\EO_IblockMessage setMessageId(\string|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \string getMessageText()
	 * @method \Bitrix\Iblock\EO_IblockMessage setMessageText(\string|\Bitrix\Main\DB\SqlExpression $messageText)
	 * @method bool hasMessageText()
	 * @method bool isMessageTextFilled()
	 * @method bool isMessageTextChanged()
	 * @method \string remindActualMessageText()
	 * @method \string requireMessageText()
	 * @method \Bitrix\Iblock\EO_IblockMessage resetMessageText()
	 * @method \Bitrix\Iblock\EO_IblockMessage unsetMessageText()
	 * @method \string fillMessageText()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_IblockMessage setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_IblockMessage resetIblock()
	 * @method \Bitrix\Iblock\EO_IblockMessage unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\EO_IblockMessage set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_IblockMessage reset($fieldName)
	 * @method \Bitrix\Iblock\EO_IblockMessage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_IblockMessage wakeUp($data)
	 */
	class EO_IblockMessage {
		/* @var \Bitrix\Iblock\IblockMessageTable */
		static public $dataClass = '\Bitrix\Iblock\IblockMessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_IblockMessage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \string[] getMessageIdList()
	 * @method \string[] getMessageTextList()
	 * @method \string[] fillMessageText()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_IblockMessage_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_IblockMessage $object)
	 * @method bool has(\Bitrix\Iblock\EO_IblockMessage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockMessage getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockMessage[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_IblockMessage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_IblockMessage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_IblockMessage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_IblockMessage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockMessageTable */
		static public $dataClass = '\Bitrix\Iblock\IblockMessageTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_IblockMessage_Result exec()
	 * @method \Bitrix\Iblock\EO_IblockMessage fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockMessage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_IblockMessage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockMessage fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockMessage_Collection fetchCollection()
	 */
	class EO_IblockMessage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockMessage createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_IblockMessage_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_IblockMessage wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_IblockMessage_Collection wakeUpCollection($rows)
	 */
	class EO_IblockMessage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockRssTable:iblock/lib/iblockrss.php:8cbee456c516ed874e3c23f414e997b0 */
namespace Bitrix\Iblock {
	/**
	 * EO_IblockRss
	 * @see \Bitrix\Iblock\IblockRssTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_IblockRss setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_IblockRss setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\EO_IblockRss resetIblockId()
	 * @method \Bitrix\Iblock\EO_IblockRss unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \string getNode()
	 * @method \Bitrix\Iblock\EO_IblockRss setNode(\string|\Bitrix\Main\DB\SqlExpression $node)
	 * @method bool hasNode()
	 * @method bool isNodeFilled()
	 * @method bool isNodeChanged()
	 * @method \string remindActualNode()
	 * @method \string requireNode()
	 * @method \Bitrix\Iblock\EO_IblockRss resetNode()
	 * @method \Bitrix\Iblock\EO_IblockRss unsetNode()
	 * @method \string fillNode()
	 * @method \string getNodeValue()
	 * @method \Bitrix\Iblock\EO_IblockRss setNodeValue(\string|\Bitrix\Main\DB\SqlExpression $nodeValue)
	 * @method bool hasNodeValue()
	 * @method bool isNodeValueFilled()
	 * @method bool isNodeValueChanged()
	 * @method \string remindActualNodeValue()
	 * @method \string requireNodeValue()
	 * @method \Bitrix\Iblock\EO_IblockRss resetNodeValue()
	 * @method \Bitrix\Iblock\EO_IblockRss unsetNodeValue()
	 * @method \string fillNodeValue()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_IblockRss setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_IblockRss resetIblock()
	 * @method \Bitrix\Iblock\EO_IblockRss unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\EO_IblockRss set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_IblockRss reset($fieldName)
	 * @method \Bitrix\Iblock\EO_IblockRss unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_IblockRss wakeUp($data)
	 */
	class EO_IblockRss {
		/* @var \Bitrix\Iblock\IblockRssTable */
		static public $dataClass = '\Bitrix\Iblock\IblockRssTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_IblockRss_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \string[] getNodeList()
	 * @method \string[] fillNode()
	 * @method \string[] getNodeValueList()
	 * @method \string[] fillNodeValue()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_IblockRss_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_IblockRss $object)
	 * @method bool has(\Bitrix\Iblock\EO_IblockRss $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockRss getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockRss[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_IblockRss $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_IblockRss_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_IblockRss current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_IblockRss_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockRssTable */
		static public $dataClass = '\Bitrix\Iblock\IblockRssTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_IblockRss_Result exec()
	 * @method \Bitrix\Iblock\EO_IblockRss fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockRss_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_IblockRss_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockRss fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockRss_Collection fetchCollection()
	 */
	class EO_IblockRss_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockRss createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_IblockRss_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_IblockRss wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_IblockRss_Collection wakeUpCollection($rows)
	 */
	class EO_IblockRss_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\IblockSiteTable:iblock/lib/iblocksite.php:43dcc35c6580d899f5089941bbc884a1 */
namespace Bitrix\Iblock {
	/**
	 * EO_IblockSite
	 * @see \Bitrix\Iblock\IblockSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_IblockSite setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Iblock\EO_IblockSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_IblockSite setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_IblockSite resetIblock()
	 * @method \Bitrix\Iblock\EO_IblockSite unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Iblock\EO_IblockSite setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Iblock\EO_IblockSite resetSite()
	 * @method \Bitrix\Iblock\EO_IblockSite unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
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
	 * @method \Bitrix\Iblock\EO_IblockSite set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_IblockSite reset($fieldName)
	 * @method \Bitrix\Iblock\EO_IblockSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_IblockSite wakeUp($data)
	 */
	class EO_IblockSite {
		/* @var \Bitrix\Iblock\IblockSiteTable */
		static public $dataClass = '\Bitrix\Iblock\IblockSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_IblockSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \string[] getSiteIdList()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_IblockSite $object)
	 * @method bool has(\Bitrix\Iblock\EO_IblockSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockSite getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_IblockSite[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_IblockSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_IblockSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_IblockSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_IblockSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\IblockSiteTable */
		static public $dataClass = '\Bitrix\Iblock\IblockSiteTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_IblockSite_Result exec()
	 * @method \Bitrix\Iblock\EO_IblockSite fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_IblockSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockSite fetchObject()
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection fetchCollection()
	 */
	class EO_IblockSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_IblockSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_IblockSite wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_IblockSite_Collection wakeUpCollection($rows)
	 */
	class EO_IblockSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\InheritedPropertyTable:iblock/lib/inheritedproperty.php:ce128f88463af44d89cb4bcac863fb61 */
namespace Bitrix\Iblock {
	/**
	 * EO_InheritedProperty
	 * @see \Bitrix\Iblock\InheritedPropertyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty resetIblockId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\EO_InheritedProperty resetCode()
	 * @method \Bitrix\Iblock\EO_InheritedProperty unsetCode()
	 * @method \string fillCode()
	 * @method \string getEntityType()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Iblock\EO_InheritedProperty resetEntityType()
	 * @method \Bitrix\Iblock\EO_InheritedProperty unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \string getEntityId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string remindActualEntityId()
	 * @method \string requireEntityId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty resetEntityId()
	 * @method \Bitrix\Iblock\EO_InheritedProperty unsetEntityId()
	 * @method \string fillEntityId()
	 * @method \string getTemplate()
	 * @method \Bitrix\Iblock\EO_InheritedProperty setTemplate(\string|\Bitrix\Main\DB\SqlExpression $template)
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \string remindActualTemplate()
	 * @method \string requireTemplate()
	 * @method \Bitrix\Iblock\EO_InheritedProperty resetTemplate()
	 * @method \Bitrix\Iblock\EO_InheritedProperty unsetTemplate()
	 * @method \string fillTemplate()
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
	 * @method \Bitrix\Iblock\EO_InheritedProperty set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_InheritedProperty reset($fieldName)
	 * @method \Bitrix\Iblock\EO_InheritedProperty unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_InheritedProperty wakeUp($data)
	 */
	class EO_InheritedProperty {
		/* @var \Bitrix\Iblock\InheritedPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\InheritedPropertyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_InheritedProperty_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \string[] getEntityIdList()
	 * @method \string[] fillEntityId()
	 * @method \string[] getTemplateList()
	 * @method \string[] fillTemplate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_InheritedProperty $object)
	 * @method bool has(\Bitrix\Iblock\EO_InheritedProperty $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_InheritedProperty getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_InheritedProperty[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_InheritedProperty $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_InheritedProperty_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_InheritedProperty current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_InheritedProperty_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\InheritedPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\InheritedPropertyTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_InheritedProperty_Result exec()
	 * @method \Bitrix\Iblock\EO_InheritedProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_InheritedProperty_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_InheritedProperty_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_InheritedProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_InheritedProperty_Collection fetchCollection()
	 */
	class EO_InheritedProperty_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_InheritedProperty createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_InheritedProperty_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_InheritedProperty wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_InheritedProperty_Collection wakeUpCollection($rows)
	 */
	class EO_InheritedProperty_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\ORM\ElementV1Table:iblock/lib/orm/elementv1table.php:458c0edb3d3cad92c74ac0d4675d3489 */
namespace Bitrix\Iblock\ORM {
	/**
	 * EO_ElementV1
	 * @see \Bitrix\Iblock\ORM\ElementV1Table
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \int getIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setIblockSectionId(\int|\Bitrix\Main\DB\SqlExpression $iblockSectionId)
	 * @method bool hasIblockSectionId()
	 * @method bool isIblockSectionIdFilled()
	 * @method bool isIblockSectionIdChanged()
	 * @method \int remindActualIblockSectionId()
	 * @method \int requireIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetIblockSectionId()
	 * @method \int fillIblockSectionId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setActiveFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeFrom)
	 * @method bool hasActiveFrom()
	 * @method bool isActiveFromFilled()
	 * @method bool isActiveFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime requireActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime getActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setActiveTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeTo)
	 * @method bool hasActiveTo()
	 * @method bool isActiveToFilled()
	 * @method bool isActiveToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveTo()
	 * @method \Bitrix\Main\Type\DateTime requireActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetActiveTo()
	 * @method \Bitrix\Main\Type\DateTime fillActiveTo()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetName()
	 * @method \string fillName()
	 * @method \int getPreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setPreviewPicture(\int|\Bitrix\Main\DB\SqlExpression $previewPicture)
	 * @method bool hasPreviewPicture()
	 * @method bool isPreviewPictureFilled()
	 * @method bool isPreviewPictureChanged()
	 * @method \int remindActualPreviewPicture()
	 * @method \int requirePreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetPreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetPreviewPicture()
	 * @method \int fillPreviewPicture()
	 * @method \string getPreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setPreviewText(\string|\Bitrix\Main\DB\SqlExpression $previewText)
	 * @method bool hasPreviewText()
	 * @method bool isPreviewTextFilled()
	 * @method bool isPreviewTextChanged()
	 * @method \string remindActualPreviewText()
	 * @method \string requirePreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetPreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetPreviewText()
	 * @method \string fillPreviewText()
	 * @method \string getPreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setPreviewTextType(\string|\Bitrix\Main\DB\SqlExpression $previewTextType)
	 * @method bool hasPreviewTextType()
	 * @method bool isPreviewTextTypeFilled()
	 * @method bool isPreviewTextTypeChanged()
	 * @method \string remindActualPreviewTextType()
	 * @method \string requirePreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetPreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetPreviewTextType()
	 * @method \string fillPreviewTextType()
	 * @method \int getDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setDetailPicture(\int|\Bitrix\Main\DB\SqlExpression $detailPicture)
	 * @method bool hasDetailPicture()
	 * @method bool isDetailPictureFilled()
	 * @method bool isDetailPictureChanged()
	 * @method \int remindActualDetailPicture()
	 * @method \int requireDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetDetailPicture()
	 * @method \int fillDetailPicture()
	 * @method \string getDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setDetailText(\string|\Bitrix\Main\DB\SqlExpression $detailText)
	 * @method bool hasDetailText()
	 * @method bool isDetailTextFilled()
	 * @method bool isDetailTextChanged()
	 * @method \string remindActualDetailText()
	 * @method \string requireDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetDetailText()
	 * @method \string fillDetailText()
	 * @method \string getDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setDetailTextType(\string|\Bitrix\Main\DB\SqlExpression $detailTextType)
	 * @method bool hasDetailTextType()
	 * @method bool isDetailTextTypeFilled()
	 * @method bool isDetailTextTypeChanged()
	 * @method \string remindActualDetailTextType()
	 * @method \string requireDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetDetailTextType()
	 * @method \string fillDetailTextType()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \int getWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfStatusId(\int|\Bitrix\Main\DB\SqlExpression $wfStatusId)
	 * @method bool hasWfStatusId()
	 * @method bool isWfStatusIdFilled()
	 * @method bool isWfStatusIdChanged()
	 * @method \int remindActualWfStatusId()
	 * @method \int requireWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfStatusId()
	 * @method \int fillWfStatusId()
	 * @method \int getWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfParentElementId(\int|\Bitrix\Main\DB\SqlExpression $wfParentElementId)
	 * @method bool hasWfParentElementId()
	 * @method bool isWfParentElementIdFilled()
	 * @method bool isWfParentElementIdChanged()
	 * @method \int remindActualWfParentElementId()
	 * @method \int requireWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfParentElementId()
	 * @method \int fillWfParentElementId()
	 * @method \string getWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfNew(\string|\Bitrix\Main\DB\SqlExpression $wfNew)
	 * @method bool hasWfNew()
	 * @method bool isWfNewFilled()
	 * @method bool isWfNewChanged()
	 * @method \string remindActualWfNew()
	 * @method \string requireWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfNew()
	 * @method \string fillWfNew()
	 * @method \int getWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfLockedBy(\int|\Bitrix\Main\DB\SqlExpression $wfLockedBy)
	 * @method bool hasWfLockedBy()
	 * @method bool isWfLockedByFilled()
	 * @method bool isWfLockedByChanged()
	 * @method \int remindActualWfLockedBy()
	 * @method \int requireWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfLockedBy()
	 * @method \int fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime getWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfDateLock(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $wfDateLock)
	 * @method bool hasWfDateLock()
	 * @method bool isWfDateLockFilled()
	 * @method bool isWfDateLockChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime requireWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime fillWfDateLock()
	 * @method \string getWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfComments(\string|\Bitrix\Main\DB\SqlExpression $wfComments)
	 * @method bool hasWfComments()
	 * @method bool isWfCommentsFilled()
	 * @method bool isWfCommentsChanged()
	 * @method \string remindActualWfComments()
	 * @method \string requireWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfComments()
	 * @method \string fillWfComments()
	 * @method \boolean getInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setInSections(\boolean|\Bitrix\Main\DB\SqlExpression $inSections)
	 * @method bool hasInSections()
	 * @method bool isInSectionsFilled()
	 * @method bool isInSectionsChanged()
	 * @method \boolean remindActualInSections()
	 * @method \boolean requireInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetInSections()
	 * @method \boolean fillInSections()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetCode()
	 * @method \string fillCode()
	 * @method \string getTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setTags(\string|\Bitrix\Main\DB\SqlExpression $tags)
	 * @method bool hasTags()
	 * @method bool isTagsFilled()
	 * @method bool isTagsChanged()
	 * @method \string remindActualTags()
	 * @method \string requireTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetTags()
	 * @method \string fillTags()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \int getShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setShowCounter(\int|\Bitrix\Main\DB\SqlExpression $showCounter)
	 * @method bool hasShowCounter()
	 * @method bool isShowCounterFilled()
	 * @method bool isShowCounterChanged()
	 * @method \int remindActualShowCounter()
	 * @method \int requireShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetShowCounter()
	 * @method \int fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime getShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setShowCounterStart(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $showCounterStart)
	 * @method bool hasShowCounterStart()
	 * @method bool isShowCounterStartFilled()
	 * @method bool isShowCounterStartChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime requireShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetIblock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\EO_Element getWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element requireWfParentElement()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfParentElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfParentElement()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfParentElement()
	 * @method bool hasWfParentElement()
	 * @method bool isWfParentElementFilled()
	 * @method bool isWfParentElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section getIblockSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualIblockSection()
	 * @method \Bitrix\Iblock\EO_Section requireIblockSection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setIblockSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetIblockSection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetIblockSection()
	 * @method bool hasIblockSection()
	 * @method bool isIblockSectionFilled()
	 * @method bool isIblockSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillIblockSection()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetModifiedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetCreatedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getWfLockedByUser()
	 * @method \Bitrix\Main\EO_User remindActualWfLockedByUser()
	 * @method \Bitrix\Main\EO_User requireWfLockedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 setWfLockedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 resetWfLockedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unsetWfLockedByUser()
	 * @method bool hasWfLockedByUser()
	 * @method bool isWfLockedByUserFilled()
	 * @method bool isWfLockedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillWfLockedByUser()
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
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 set($fieldName, $value)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 reset($fieldName)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\ORM\EO_ElementV1 wakeUp($data)
	 */
	class EO_ElementV1 {
		/* @var \Bitrix\Iblock\ORM\ElementV1Table */
		static public $dataClass = '\Bitrix\Iblock\ORM\ElementV1Table';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock\ORM {
	/**
	 * EO_ElementV1_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \int[] getIblockSectionIdList()
	 * @method \int[] fillIblockSectionId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveTo()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getPreviewPictureList()
	 * @method \int[] fillPreviewPicture()
	 * @method \string[] getPreviewTextList()
	 * @method \string[] fillPreviewText()
	 * @method \string[] getPreviewTextTypeList()
	 * @method \string[] fillPreviewTextType()
	 * @method \int[] getDetailPictureList()
	 * @method \int[] fillDetailPicture()
	 * @method \string[] getDetailTextList()
	 * @method \string[] fillDetailText()
	 * @method \string[] getDetailTextTypeList()
	 * @method \string[] fillDetailTextType()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \int[] getWfStatusIdList()
	 * @method \int[] fillWfStatusId()
	 * @method \int[] getWfParentElementIdList()
	 * @method \int[] fillWfParentElementId()
	 * @method \string[] getWfNewList()
	 * @method \string[] fillWfNew()
	 * @method \int[] getWfLockedByList()
	 * @method \int[] fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getWfDateLockList()
	 * @method \Bitrix\Main\Type\DateTime[] fillWfDateLock()
	 * @method \string[] getWfCommentsList()
	 * @method \string[] fillWfComments()
	 * @method \boolean[] getInSectionsList()
	 * @method \boolean[] fillInSections()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTagsList()
	 * @method \string[] fillTags()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \int[] getShowCounterList()
	 * @method \int[] fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime[] getShowCounterStartList()
	 * @method \Bitrix\Main\Type\DateTime[] fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\EO_Element[] getWfParentElementList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getWfParentElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section[] getIblockSectionList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getIblockSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillIblockSection()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getWfLockedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection getWfLockedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillWfLockedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\ORM\EO_ElementV1 $object)
	 * @method bool has(\Bitrix\Iblock\ORM\EO_ElementV1 $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 getByPrimary($primary)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1[] getAll()
	 * @method bool remove(\Bitrix\Iblock\ORM\EO_ElementV1 $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\ORM\EO_ElementV1_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ElementV1_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\ORM\ElementV1Table */
		static public $dataClass = '\Bitrix\Iblock\ORM\ElementV1Table';
	}
}
namespace Bitrix\Iblock\ORM {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ElementV1_Result exec()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 fetchObject()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ElementV1_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 fetchObject()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection fetchCollection()
	 */
	class EO_ElementV1_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection createCollection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1 wakeUpObject($row)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV1_Collection wakeUpCollection($rows)
	 */
	class EO_ElementV1_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\ORM\ElementV2Table:iblock/lib/orm/elementv2table.php:c84ed76e7ebef1aff54f2d9c8c2bd2dc */
namespace Bitrix\Iblock\ORM {
	/**
	 * EO_ElementV2
	 * @see \Bitrix\Iblock\ORM\ElementV2Table
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetTimestampX()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetModifiedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetDateCreate()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetCreatedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetIblockId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \int getIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setIblockSectionId(\int|\Bitrix\Main\DB\SqlExpression $iblockSectionId)
	 * @method bool hasIblockSectionId()
	 * @method bool isIblockSectionIdFilled()
	 * @method bool isIblockSectionIdChanged()
	 * @method \int remindActualIblockSectionId()
	 * @method \int requireIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetIblockSectionId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetIblockSectionId()
	 * @method \int fillIblockSectionId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetActive()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setActiveFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeFrom)
	 * @method bool hasActiveFrom()
	 * @method bool isActiveFromFilled()
	 * @method bool isActiveFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime requireActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetActiveFrom()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime getActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setActiveTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeTo)
	 * @method bool hasActiveTo()
	 * @method bool isActiveToFilled()
	 * @method bool isActiveToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveTo()
	 * @method \Bitrix\Main\Type\DateTime requireActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetActiveTo()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetActiveTo()
	 * @method \Bitrix\Main\Type\DateTime fillActiveTo()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetSort()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetName()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetName()
	 * @method \string fillName()
	 * @method \int getPreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setPreviewPicture(\int|\Bitrix\Main\DB\SqlExpression $previewPicture)
	 * @method bool hasPreviewPicture()
	 * @method bool isPreviewPictureFilled()
	 * @method bool isPreviewPictureChanged()
	 * @method \int remindActualPreviewPicture()
	 * @method \int requirePreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetPreviewPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetPreviewPicture()
	 * @method \int fillPreviewPicture()
	 * @method \string getPreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setPreviewText(\string|\Bitrix\Main\DB\SqlExpression $previewText)
	 * @method bool hasPreviewText()
	 * @method bool isPreviewTextFilled()
	 * @method bool isPreviewTextChanged()
	 * @method \string remindActualPreviewText()
	 * @method \string requirePreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetPreviewText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetPreviewText()
	 * @method \string fillPreviewText()
	 * @method \string getPreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setPreviewTextType(\string|\Bitrix\Main\DB\SqlExpression $previewTextType)
	 * @method bool hasPreviewTextType()
	 * @method bool isPreviewTextTypeFilled()
	 * @method bool isPreviewTextTypeChanged()
	 * @method \string remindActualPreviewTextType()
	 * @method \string requirePreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetPreviewTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetPreviewTextType()
	 * @method \string fillPreviewTextType()
	 * @method \int getDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setDetailPicture(\int|\Bitrix\Main\DB\SqlExpression $detailPicture)
	 * @method bool hasDetailPicture()
	 * @method bool isDetailPictureFilled()
	 * @method bool isDetailPictureChanged()
	 * @method \int remindActualDetailPicture()
	 * @method \int requireDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetDetailPicture()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetDetailPicture()
	 * @method \int fillDetailPicture()
	 * @method \string getDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setDetailText(\string|\Bitrix\Main\DB\SqlExpression $detailText)
	 * @method bool hasDetailText()
	 * @method bool isDetailTextFilled()
	 * @method bool isDetailTextChanged()
	 * @method \string remindActualDetailText()
	 * @method \string requireDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetDetailText()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetDetailText()
	 * @method \string fillDetailText()
	 * @method \string getDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setDetailTextType(\string|\Bitrix\Main\DB\SqlExpression $detailTextType)
	 * @method bool hasDetailTextType()
	 * @method bool isDetailTextTypeFilled()
	 * @method bool isDetailTextTypeChanged()
	 * @method \string remindActualDetailTextType()
	 * @method \string requireDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetDetailTextType()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetDetailTextType()
	 * @method \string fillDetailTextType()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetSearchableContent()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \int getWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfStatusId(\int|\Bitrix\Main\DB\SqlExpression $wfStatusId)
	 * @method bool hasWfStatusId()
	 * @method bool isWfStatusIdFilled()
	 * @method bool isWfStatusIdChanged()
	 * @method \int remindActualWfStatusId()
	 * @method \int requireWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfStatusId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfStatusId()
	 * @method \int fillWfStatusId()
	 * @method \int getWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfParentElementId(\int|\Bitrix\Main\DB\SqlExpression $wfParentElementId)
	 * @method bool hasWfParentElementId()
	 * @method bool isWfParentElementIdFilled()
	 * @method bool isWfParentElementIdChanged()
	 * @method \int remindActualWfParentElementId()
	 * @method \int requireWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfParentElementId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfParentElementId()
	 * @method \int fillWfParentElementId()
	 * @method \string getWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfNew(\string|\Bitrix\Main\DB\SqlExpression $wfNew)
	 * @method bool hasWfNew()
	 * @method bool isWfNewFilled()
	 * @method bool isWfNewChanged()
	 * @method \string remindActualWfNew()
	 * @method \string requireWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfNew()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfNew()
	 * @method \string fillWfNew()
	 * @method \int getWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfLockedBy(\int|\Bitrix\Main\DB\SqlExpression $wfLockedBy)
	 * @method bool hasWfLockedBy()
	 * @method bool isWfLockedByFilled()
	 * @method bool isWfLockedByChanged()
	 * @method \int remindActualWfLockedBy()
	 * @method \int requireWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfLockedBy()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfLockedBy()
	 * @method \int fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime getWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfDateLock(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $wfDateLock)
	 * @method bool hasWfDateLock()
	 * @method bool isWfDateLockFilled()
	 * @method bool isWfDateLockChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime requireWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfDateLock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfDateLock()
	 * @method \Bitrix\Main\Type\DateTime fillWfDateLock()
	 * @method \string getWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfComments(\string|\Bitrix\Main\DB\SqlExpression $wfComments)
	 * @method bool hasWfComments()
	 * @method bool isWfCommentsFilled()
	 * @method bool isWfCommentsChanged()
	 * @method \string remindActualWfComments()
	 * @method \string requireWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfComments()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfComments()
	 * @method \string fillWfComments()
	 * @method \boolean getInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setInSections(\boolean|\Bitrix\Main\DB\SqlExpression $inSections)
	 * @method bool hasInSections()
	 * @method bool isInSectionsFilled()
	 * @method bool isInSectionsChanged()
	 * @method \boolean remindActualInSections()
	 * @method \boolean requireInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetInSections()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetInSections()
	 * @method \boolean fillInSections()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetXmlId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetCode()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetCode()
	 * @method \string fillCode()
	 * @method \string getTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setTags(\string|\Bitrix\Main\DB\SqlExpression $tags)
	 * @method bool hasTags()
	 * @method bool isTagsFilled()
	 * @method bool isTagsChanged()
	 * @method \string remindActualTags()
	 * @method \string requireTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetTags()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetTags()
	 * @method \string fillTags()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetTmpId()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \int getShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setShowCounter(\int|\Bitrix\Main\DB\SqlExpression $showCounter)
	 * @method bool hasShowCounter()
	 * @method bool isShowCounterFilled()
	 * @method bool isShowCounterChanged()
	 * @method \int remindActualShowCounter()
	 * @method \int requireShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetShowCounter()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetShowCounter()
	 * @method \int fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime getShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setShowCounterStart(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $showCounterStart)
	 * @method bool hasShowCounterStart()
	 * @method bool isShowCounterStartFilled()
	 * @method bool isShowCounterStartChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime requireShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetShowCounterStart()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetShowCounterStart()
	 * @method \Bitrix\Main\Type\DateTime fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetIblock()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\EO_Element getWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualWfParentElement()
	 * @method \Bitrix\Iblock\EO_Element requireWfParentElement()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfParentElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfParentElement()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfParentElement()
	 * @method bool hasWfParentElement()
	 * @method bool isWfParentElementFilled()
	 * @method bool isWfParentElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section getIblockSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualIblockSection()
	 * @method \Bitrix\Iblock\EO_Section requireIblockSection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setIblockSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetIblockSection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetIblockSection()
	 * @method bool hasIblockSection()
	 * @method bool isIblockSectionFilled()
	 * @method bool isIblockSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillIblockSection()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetModifiedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetCreatedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getWfLockedByUser()
	 * @method \Bitrix\Main\EO_User remindActualWfLockedByUser()
	 * @method \Bitrix\Main\EO_User requireWfLockedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 setWfLockedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 resetWfLockedByUser()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unsetWfLockedByUser()
	 * @method bool hasWfLockedByUser()
	 * @method bool isWfLockedByUserFilled()
	 * @method bool isWfLockedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillWfLockedByUser()
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
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 set($fieldName, $value)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 reset($fieldName)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\ORM\EO_ElementV2 wakeUp($data)
	 */
	class EO_ElementV2 {
		/* @var \Bitrix\Iblock\ORM\ElementV2Table */
		static public $dataClass = '\Bitrix\Iblock\ORM\ElementV2Table';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock\ORM {
	/**
	 * EO_ElementV2_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \int[] getIblockSectionIdList()
	 * @method \int[] fillIblockSectionId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveTo()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getPreviewPictureList()
	 * @method \int[] fillPreviewPicture()
	 * @method \string[] getPreviewTextList()
	 * @method \string[] fillPreviewText()
	 * @method \string[] getPreviewTextTypeList()
	 * @method \string[] fillPreviewTextType()
	 * @method \int[] getDetailPictureList()
	 * @method \int[] fillDetailPicture()
	 * @method \string[] getDetailTextList()
	 * @method \string[] fillDetailText()
	 * @method \string[] getDetailTextTypeList()
	 * @method \string[] fillDetailTextType()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \int[] getWfStatusIdList()
	 * @method \int[] fillWfStatusId()
	 * @method \int[] getWfParentElementIdList()
	 * @method \int[] fillWfParentElementId()
	 * @method \string[] getWfNewList()
	 * @method \string[] fillWfNew()
	 * @method \int[] getWfLockedByList()
	 * @method \int[] fillWfLockedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getWfDateLockList()
	 * @method \Bitrix\Main\Type\DateTime[] fillWfDateLock()
	 * @method \string[] getWfCommentsList()
	 * @method \string[] fillWfComments()
	 * @method \boolean[] getInSectionsList()
	 * @method \boolean[] fillInSections()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTagsList()
	 * @method \string[] fillTags()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \int[] getShowCounterList()
	 * @method \int[] fillShowCounter()
	 * @method \Bitrix\Main\Type\DateTime[] getShowCounterStartList()
	 * @method \Bitrix\Main\Type\DateTime[] fillShowCounterStart()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\EO_Element[] getWfParentElementList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getWfParentElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillWfParentElement()
	 * @method \Bitrix\Iblock\EO_Section[] getIblockSectionList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getIblockSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillIblockSection()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getWfLockedByUserList()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection getWfLockedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillWfLockedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\ORM\EO_ElementV2 $object)
	 * @method bool has(\Bitrix\Iblock\ORM\EO_ElementV2 $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 getByPrimary($primary)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2[] getAll()
	 * @method bool remove(\Bitrix\Iblock\ORM\EO_ElementV2 $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\ORM\EO_ElementV2_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ElementV2_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\ORM\ElementV2Table */
		static public $dataClass = '\Bitrix\Iblock\ORM\ElementV2Table';
	}
}
namespace Bitrix\Iblock\ORM {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ElementV2_Result exec()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 fetchObject()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ElementV2_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 fetchObject()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection fetchCollection()
	 */
	class EO_ElementV2_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection createCollection()
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2 wakeUpObject($row)
	 * @method \Bitrix\Iblock\ORM\EO_ElementV2_Collection wakeUpCollection($rows)
	 */
	class EO_ElementV2_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\PropertyTable:iblock/lib/propertytable.php:f975f0fadc4e4ebf8d2ace90ba0906bf */
namespace Bitrix\Iblock {
	/**
	 * Property
	 * @see \Bitrix\Iblock\PropertyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\Property setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\Property setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\Property resetTimestampX()
	 * @method \Bitrix\Iblock\Property unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\Property setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\Property resetIblockId()
	 * @method \Bitrix\Iblock\Property unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\Property setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\Property resetName()
	 * @method \Bitrix\Iblock\Property unsetName()
	 * @method \string fillName()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\Property setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\Property resetActive()
	 * @method \Bitrix\Iblock\Property unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\Property setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\Property resetSort()
	 * @method \Bitrix\Iblock\Property unsetSort()
	 * @method \int fillSort()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\Property setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\Property resetCode()
	 * @method \Bitrix\Iblock\Property unsetCode()
	 * @method \string fillCode()
	 * @method \string getDefaultValue()
	 * @method \Bitrix\Iblock\Property setDefaultValue(\string|\Bitrix\Main\DB\SqlExpression $defaultValue)
	 * @method bool hasDefaultValue()
	 * @method bool isDefaultValueFilled()
	 * @method bool isDefaultValueChanged()
	 * @method \string remindActualDefaultValue()
	 * @method \string requireDefaultValue()
	 * @method \Bitrix\Iblock\Property resetDefaultValue()
	 * @method \Bitrix\Iblock\Property unsetDefaultValue()
	 * @method \string fillDefaultValue()
	 * @method \string getPropertyType()
	 * @method \Bitrix\Iblock\Property setPropertyType(\string|\Bitrix\Main\DB\SqlExpression $propertyType)
	 * @method bool hasPropertyType()
	 * @method bool isPropertyTypeFilled()
	 * @method bool isPropertyTypeChanged()
	 * @method \string remindActualPropertyType()
	 * @method \string requirePropertyType()
	 * @method \Bitrix\Iblock\Property resetPropertyType()
	 * @method \Bitrix\Iblock\Property unsetPropertyType()
	 * @method \string fillPropertyType()
	 * @method \int getRowCount()
	 * @method \Bitrix\Iblock\Property setRowCount(\int|\Bitrix\Main\DB\SqlExpression $rowCount)
	 * @method bool hasRowCount()
	 * @method bool isRowCountFilled()
	 * @method bool isRowCountChanged()
	 * @method \int remindActualRowCount()
	 * @method \int requireRowCount()
	 * @method \Bitrix\Iblock\Property resetRowCount()
	 * @method \Bitrix\Iblock\Property unsetRowCount()
	 * @method \int fillRowCount()
	 * @method \int getColCount()
	 * @method \Bitrix\Iblock\Property setColCount(\int|\Bitrix\Main\DB\SqlExpression $colCount)
	 * @method bool hasColCount()
	 * @method bool isColCountFilled()
	 * @method bool isColCountChanged()
	 * @method \int remindActualColCount()
	 * @method \int requireColCount()
	 * @method \Bitrix\Iblock\Property resetColCount()
	 * @method \Bitrix\Iblock\Property unsetColCount()
	 * @method \int fillColCount()
	 * @method \string getListType()
	 * @method \Bitrix\Iblock\Property setListType(\string|\Bitrix\Main\DB\SqlExpression $listType)
	 * @method bool hasListType()
	 * @method bool isListTypeFilled()
	 * @method bool isListTypeChanged()
	 * @method \string remindActualListType()
	 * @method \string requireListType()
	 * @method \Bitrix\Iblock\Property resetListType()
	 * @method \Bitrix\Iblock\Property unsetListType()
	 * @method \string fillListType()
	 * @method \boolean getMultiple()
	 * @method \Bitrix\Iblock\Property setMultiple(\boolean|\Bitrix\Main\DB\SqlExpression $multiple)
	 * @method bool hasMultiple()
	 * @method bool isMultipleFilled()
	 * @method bool isMultipleChanged()
	 * @method \boolean remindActualMultiple()
	 * @method \boolean requireMultiple()
	 * @method \Bitrix\Iblock\Property resetMultiple()
	 * @method \Bitrix\Iblock\Property unsetMultiple()
	 * @method \boolean fillMultiple()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\Property setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\Property resetXmlId()
	 * @method \Bitrix\Iblock\Property unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getFileType()
	 * @method \Bitrix\Iblock\Property setFileType(\string|\Bitrix\Main\DB\SqlExpression $fileType)
	 * @method bool hasFileType()
	 * @method bool isFileTypeFilled()
	 * @method bool isFileTypeChanged()
	 * @method \string remindActualFileType()
	 * @method \string requireFileType()
	 * @method \Bitrix\Iblock\Property resetFileType()
	 * @method \Bitrix\Iblock\Property unsetFileType()
	 * @method \string fillFileType()
	 * @method \int getMultipleCnt()
	 * @method \Bitrix\Iblock\Property setMultipleCnt(\int|\Bitrix\Main\DB\SqlExpression $multipleCnt)
	 * @method bool hasMultipleCnt()
	 * @method bool isMultipleCntFilled()
	 * @method bool isMultipleCntChanged()
	 * @method \int remindActualMultipleCnt()
	 * @method \int requireMultipleCnt()
	 * @method \Bitrix\Iblock\Property resetMultipleCnt()
	 * @method \Bitrix\Iblock\Property unsetMultipleCnt()
	 * @method \int fillMultipleCnt()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\Property setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\Property resetTmpId()
	 * @method \Bitrix\Iblock\Property unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \int getLinkIblockId()
	 * @method \Bitrix\Iblock\Property setLinkIblockId(\int|\Bitrix\Main\DB\SqlExpression $linkIblockId)
	 * @method bool hasLinkIblockId()
	 * @method bool isLinkIblockIdFilled()
	 * @method bool isLinkIblockIdChanged()
	 * @method \int remindActualLinkIblockId()
	 * @method \int requireLinkIblockId()
	 * @method \Bitrix\Iblock\Property resetLinkIblockId()
	 * @method \Bitrix\Iblock\Property unsetLinkIblockId()
	 * @method \int fillLinkIblockId()
	 * @method \boolean getWithDescription()
	 * @method \Bitrix\Iblock\Property setWithDescription(\boolean|\Bitrix\Main\DB\SqlExpression $withDescription)
	 * @method bool hasWithDescription()
	 * @method bool isWithDescriptionFilled()
	 * @method bool isWithDescriptionChanged()
	 * @method \boolean remindActualWithDescription()
	 * @method \boolean requireWithDescription()
	 * @method \Bitrix\Iblock\Property resetWithDescription()
	 * @method \Bitrix\Iblock\Property unsetWithDescription()
	 * @method \boolean fillWithDescription()
	 * @method \boolean getSearchable()
	 * @method \Bitrix\Iblock\Property setSearchable(\boolean|\Bitrix\Main\DB\SqlExpression $searchable)
	 * @method bool hasSearchable()
	 * @method bool isSearchableFilled()
	 * @method bool isSearchableChanged()
	 * @method \boolean remindActualSearchable()
	 * @method \boolean requireSearchable()
	 * @method \Bitrix\Iblock\Property resetSearchable()
	 * @method \Bitrix\Iblock\Property unsetSearchable()
	 * @method \boolean fillSearchable()
	 * @method \boolean getFiltrable()
	 * @method \Bitrix\Iblock\Property setFiltrable(\boolean|\Bitrix\Main\DB\SqlExpression $filtrable)
	 * @method bool hasFiltrable()
	 * @method bool isFiltrableFilled()
	 * @method bool isFiltrableChanged()
	 * @method \boolean remindActualFiltrable()
	 * @method \boolean requireFiltrable()
	 * @method \Bitrix\Iblock\Property resetFiltrable()
	 * @method \Bitrix\Iblock\Property unsetFiltrable()
	 * @method \boolean fillFiltrable()
	 * @method \boolean getIsRequired()
	 * @method \Bitrix\Iblock\Property setIsRequired(\boolean|\Bitrix\Main\DB\SqlExpression $isRequired)
	 * @method bool hasIsRequired()
	 * @method bool isIsRequiredFilled()
	 * @method bool isIsRequiredChanged()
	 * @method \boolean remindActualIsRequired()
	 * @method \boolean requireIsRequired()
	 * @method \Bitrix\Iblock\Property resetIsRequired()
	 * @method \Bitrix\Iblock\Property unsetIsRequired()
	 * @method \boolean fillIsRequired()
	 * @method \string getVersion()
	 * @method \Bitrix\Iblock\Property setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Iblock\Property resetVersion()
	 * @method \Bitrix\Iblock\Property unsetVersion()
	 * @method \string fillVersion()
	 * @method \string getUserType()
	 * @method \Bitrix\Iblock\Property setUserType(\string|\Bitrix\Main\DB\SqlExpression $userType)
	 * @method bool hasUserType()
	 * @method bool isUserTypeFilled()
	 * @method bool isUserTypeChanged()
	 * @method \string remindActualUserType()
	 * @method \string requireUserType()
	 * @method \Bitrix\Iblock\Property resetUserType()
	 * @method \Bitrix\Iblock\Property unsetUserType()
	 * @method \string fillUserType()
	 * @method \string getUserTypeSettingsList()
	 * @method \Bitrix\Iblock\Property setUserTypeSettingsList(\string|\Bitrix\Main\DB\SqlExpression $userTypeSettingsList)
	 * @method bool hasUserTypeSettingsList()
	 * @method bool isUserTypeSettingsListFilled()
	 * @method bool isUserTypeSettingsListChanged()
	 * @method \string remindActualUserTypeSettingsList()
	 * @method \string requireUserTypeSettingsList()
	 * @method \Bitrix\Iblock\Property resetUserTypeSettingsList()
	 * @method \Bitrix\Iblock\Property unsetUserTypeSettingsList()
	 * @method \string fillUserTypeSettingsList()
	 * @method \string getUserTypeSettings()
	 * @method \Bitrix\Iblock\Property setUserTypeSettings(\string|\Bitrix\Main\DB\SqlExpression $userTypeSettings)
	 * @method bool hasUserTypeSettings()
	 * @method bool isUserTypeSettingsFilled()
	 * @method bool isUserTypeSettingsChanged()
	 * @method \string remindActualUserTypeSettings()
	 * @method \string requireUserTypeSettings()
	 * @method \Bitrix\Iblock\Property resetUserTypeSettings()
	 * @method \Bitrix\Iblock\Property unsetUserTypeSettings()
	 * @method \string fillUserTypeSettings()
	 * @method \string getHint()
	 * @method \Bitrix\Iblock\Property setHint(\string|\Bitrix\Main\DB\SqlExpression $hint)
	 * @method bool hasHint()
	 * @method bool isHintFilled()
	 * @method bool isHintChanged()
	 * @method \string remindActualHint()
	 * @method \string requireHint()
	 * @method \Bitrix\Iblock\Property resetHint()
	 * @method \Bitrix\Iblock\Property unsetHint()
	 * @method \string fillHint()
	 * @method \Bitrix\Iblock\Iblock getLinkIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualLinkIblock()
	 * @method \Bitrix\Iblock\Iblock requireLinkIblock()
	 * @method \Bitrix\Iblock\Property setLinkIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\Property resetLinkIblock()
	 * @method \Bitrix\Iblock\Property unsetLinkIblock()
	 * @method bool hasLinkIblock()
	 * @method bool isLinkIblockFilled()
	 * @method bool isLinkIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillLinkIblock()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\Property setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\Property resetIblock()
	 * @method \Bitrix\Iblock\Property unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\Property set($fieldName, $value)
	 * @method \Bitrix\Iblock\Property reset($fieldName)
	 * @method \Bitrix\Iblock\Property unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\Property wakeUp($data)
	 */
	class EO_Property {
		/* @var \Bitrix\Iblock\PropertyTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Property_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getDefaultValueList()
	 * @method \string[] fillDefaultValue()
	 * @method \string[] getPropertyTypeList()
	 * @method \string[] fillPropertyType()
	 * @method \int[] getRowCountList()
	 * @method \int[] fillRowCount()
	 * @method \int[] getColCountList()
	 * @method \int[] fillColCount()
	 * @method \string[] getListTypeList()
	 * @method \string[] fillListType()
	 * @method \boolean[] getMultipleList()
	 * @method \boolean[] fillMultiple()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getFileTypeList()
	 * @method \string[] fillFileType()
	 * @method \int[] getMultipleCntList()
	 * @method \int[] fillMultipleCnt()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \int[] getLinkIblockIdList()
	 * @method \int[] fillLinkIblockId()
	 * @method \boolean[] getWithDescriptionList()
	 * @method \boolean[] fillWithDescription()
	 * @method \boolean[] getSearchableList()
	 * @method \boolean[] fillSearchable()
	 * @method \boolean[] getFiltrableList()
	 * @method \boolean[] fillFiltrable()
	 * @method \boolean[] getIsRequiredList()
	 * @method \boolean[] fillIsRequired()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \string[] getUserTypeList()
	 * @method \string[] fillUserType()
	 * @method \string[] getUserTypeSettingsListList()
	 * @method \string[] fillUserTypeSettingsList()
	 * @method \string[] getUserTypeSettingsList()
	 * @method \string[] fillUserTypeSettings()
	 * @method \string[] getHintList()
	 * @method \string[] fillHint()
	 * @method \Bitrix\Iblock\Iblock[] getLinkIblockList()
	 * @method \Bitrix\Iblock\EO_Property_Collection getLinkIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillLinkIblock()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_Property_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\Property $object)
	 * @method bool has(\Bitrix\Iblock\Property $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\Property getByPrimary($primary)
	 * @method \Bitrix\Iblock\Property[] getAll()
	 * @method bool remove(\Bitrix\Iblock\Property $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Property_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\Property current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Property_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\PropertyTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Property_Result exec()
	 * @method \Bitrix\Iblock\Property fetchObject()
	 * @method \Bitrix\Iblock\EO_Property_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Property_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\Property fetchObject()
	 * @method \Bitrix\Iblock\EO_Property_Collection fetchCollection()
	 */
	class EO_Property_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\Property createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Property_Collection createCollection()
	 * @method \Bitrix\Iblock\Property wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Property_Collection wakeUpCollection($rows)
	 */
	class EO_Property_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\PropertyEnumerationTable:iblock/lib/propertyenumeration.php:cb348c0edda3783bf888f702b6ea3852 */
namespace Bitrix\Iblock {
	/**
	 * EO_PropertyEnumeration
	 * @see \Bitrix\Iblock\PropertyEnumerationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPropertyId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setPropertyId(\int|\Bitrix\Main\DB\SqlExpression $propertyId)
	 * @method bool hasPropertyId()
	 * @method bool isPropertyIdFilled()
	 * @method bool isPropertyIdChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetValue()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetValue()
	 * @method \string fillValue()
	 * @method \boolean getDef()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setDef(\boolean|\Bitrix\Main\DB\SqlExpression $def)
	 * @method bool hasDef()
	 * @method bool isDefFilled()
	 * @method bool isDefChanged()
	 * @method \boolean remindActualDef()
	 * @method \boolean requireDef()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetDef()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetDef()
	 * @method \boolean fillDef()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetSort()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetSort()
	 * @method \int fillSort()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetXmlId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetTmpId()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \Bitrix\Iblock\Property getProperty()
	 * @method \Bitrix\Iblock\Property remindActualProperty()
	 * @method \Bitrix\Iblock\Property requireProperty()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration setProperty(\Bitrix\Iblock\Property $object)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration resetProperty()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unsetProperty()
	 * @method bool hasProperty()
	 * @method bool isPropertyFilled()
	 * @method bool isPropertyChanged()
	 * @method \Bitrix\Iblock\Property fillProperty()
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
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration reset($fieldName)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_PropertyEnumeration wakeUp($data)
	 */
	class EO_PropertyEnumeration {
		/* @var \Bitrix\Iblock\PropertyEnumerationTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyEnumerationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_PropertyEnumeration_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPropertyIdList()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \boolean[] getDefList()
	 * @method \boolean[] fillDef()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \Bitrix\Iblock\Property[] getPropertyList()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection getPropertyCollection()
	 * @method \Bitrix\Iblock\EO_Property_Collection fillProperty()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_PropertyEnumeration $object)
	 * @method bool has(\Bitrix\Iblock\EO_PropertyEnumeration $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_PropertyEnumeration $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_PropertyEnumeration_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PropertyEnumeration_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\PropertyEnumerationTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyEnumerationTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PropertyEnumeration_Result exec()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration fetchObject()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PropertyEnumeration_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration fetchObject()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection fetchCollection()
	 */
	class EO_PropertyEnumeration_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_PropertyEnumeration_Collection wakeUpCollection($rows)
	 */
	class EO_PropertyEnumeration_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\PropertyFeatureTable:iblock/lib/propertyfeature.php:e44b169485c9a17af41ccad4ff23dea4 */
namespace Bitrix\Iblock {
	/**
	 * EO_PropertyFeature
	 * @see \Bitrix\Iblock\PropertyFeatureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPropertyId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setPropertyId(\int|\Bitrix\Main\DB\SqlExpression $propertyId)
	 * @method bool hasPropertyId()
	 * @method bool isPropertyIdFilled()
	 * @method bool isPropertyIdChanged()
	 * @method \int remindActualPropertyId()
	 * @method \int requirePropertyId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature resetPropertyId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature unsetPropertyId()
	 * @method \int fillPropertyId()
	 * @method \string getModuleId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature resetModuleId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getFeatureId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setFeatureId(\string|\Bitrix\Main\DB\SqlExpression $featureId)
	 * @method bool hasFeatureId()
	 * @method bool isFeatureIdFilled()
	 * @method bool isFeatureIdChanged()
	 * @method \string remindActualFeatureId()
	 * @method \string requireFeatureId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature resetFeatureId()
	 * @method \Bitrix\Iblock\EO_PropertyFeature unsetFeatureId()
	 * @method \string fillFeatureId()
	 * @method \boolean getIsEnabled()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setIsEnabled(\boolean|\Bitrix\Main\DB\SqlExpression $isEnabled)
	 * @method bool hasIsEnabled()
	 * @method bool isIsEnabledFilled()
	 * @method bool isIsEnabledChanged()
	 * @method \boolean remindActualIsEnabled()
	 * @method \boolean requireIsEnabled()
	 * @method \Bitrix\Iblock\EO_PropertyFeature resetIsEnabled()
	 * @method \Bitrix\Iblock\EO_PropertyFeature unsetIsEnabled()
	 * @method \boolean fillIsEnabled()
	 * @method \Bitrix\Iblock\Property getProperty()
	 * @method \Bitrix\Iblock\Property remindActualProperty()
	 * @method \Bitrix\Iblock\Property requireProperty()
	 * @method \Bitrix\Iblock\EO_PropertyFeature setProperty(\Bitrix\Iblock\Property $object)
	 * @method \Bitrix\Iblock\EO_PropertyFeature resetProperty()
	 * @method \Bitrix\Iblock\EO_PropertyFeature unsetProperty()
	 * @method bool hasProperty()
	 * @method bool isPropertyFilled()
	 * @method bool isPropertyChanged()
	 * @method \Bitrix\Iblock\Property fillProperty()
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
	 * @method \Bitrix\Iblock\EO_PropertyFeature set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_PropertyFeature reset($fieldName)
	 * @method \Bitrix\Iblock\EO_PropertyFeature unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_PropertyFeature wakeUp($data)
	 */
	class EO_PropertyFeature {
		/* @var \Bitrix\Iblock\PropertyFeatureTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyFeatureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_PropertyFeature_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPropertyIdList()
	 * @method \int[] fillPropertyId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getFeatureIdList()
	 * @method \string[] fillFeatureId()
	 * @method \boolean[] getIsEnabledList()
	 * @method \boolean[] fillIsEnabled()
	 * @method \Bitrix\Iblock\Property[] getPropertyList()
	 * @method \Bitrix\Iblock\EO_PropertyFeature_Collection getPropertyCollection()
	 * @method \Bitrix\Iblock\EO_Property_Collection fillProperty()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_PropertyFeature $object)
	 * @method bool has(\Bitrix\Iblock\EO_PropertyFeature $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_PropertyFeature getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_PropertyFeature[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_PropertyFeature $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_PropertyFeature_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_PropertyFeature current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PropertyFeature_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\PropertyFeatureTable */
		static public $dataClass = '\Bitrix\Iblock\PropertyFeatureTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PropertyFeature_Result exec()
	 * @method \Bitrix\Iblock\EO_PropertyFeature fetchObject()
	 * @method \Bitrix\Iblock\EO_PropertyFeature_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PropertyFeature_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_PropertyFeature fetchObject()
	 * @method \Bitrix\Iblock\EO_PropertyFeature_Collection fetchCollection()
	 */
	class EO_PropertyFeature_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_PropertyFeature createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_PropertyFeature_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_PropertyFeature wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_PropertyFeature_Collection wakeUpCollection($rows)
	 */
	class EO_PropertyFeature_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\SectionElementTable:iblock/lib/sectionelementtable.php:7634ac036b400256a7b779a4bae3e467 */
namespace Bitrix\Iblock {
	/**
	 * EO_SectionElement
	 * @see \Bitrix\Iblock\SectionElementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockSectionId()
	 * @method \Bitrix\Iblock\EO_SectionElement setIblockSectionId(\int|\Bitrix\Main\DB\SqlExpression $iblockSectionId)
	 * @method bool hasIblockSectionId()
	 * @method bool isIblockSectionIdFilled()
	 * @method bool isIblockSectionIdChanged()
	 * @method \int getIblockElementId()
	 * @method \Bitrix\Iblock\EO_SectionElement setIblockElementId(\int|\Bitrix\Main\DB\SqlExpression $iblockElementId)
	 * @method bool hasIblockElementId()
	 * @method bool isIblockElementIdFilled()
	 * @method bool isIblockElementIdChanged()
	 * @method \int getAdditionalPropertyId()
	 * @method \Bitrix\Iblock\EO_SectionElement setAdditionalPropertyId(\int|\Bitrix\Main\DB\SqlExpression $additionalPropertyId)
	 * @method bool hasAdditionalPropertyId()
	 * @method bool isAdditionalPropertyIdFilled()
	 * @method bool isAdditionalPropertyIdChanged()
	 * @method \int remindActualAdditionalPropertyId()
	 * @method \int requireAdditionalPropertyId()
	 * @method \Bitrix\Iblock\EO_SectionElement resetAdditionalPropertyId()
	 * @method \Bitrix\Iblock\EO_SectionElement unsetAdditionalPropertyId()
	 * @method \int fillAdditionalPropertyId()
	 * @method \Bitrix\Iblock\EO_Section getIblockSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualIblockSection()
	 * @method \Bitrix\Iblock\EO_Section requireIblockSection()
	 * @method \Bitrix\Iblock\EO_SectionElement setIblockSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\EO_SectionElement resetIblockSection()
	 * @method \Bitrix\Iblock\EO_SectionElement unsetIblockSection()
	 * @method bool hasIblockSection()
	 * @method bool isIblockSectionFilled()
	 * @method bool isIblockSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillIblockSection()
	 * @method \Bitrix\Iblock\EO_Element getIblockElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualIblockElement()
	 * @method \Bitrix\Iblock\EO_Element requireIblockElement()
	 * @method \Bitrix\Iblock\EO_SectionElement setIblockElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Iblock\EO_SectionElement resetIblockElement()
	 * @method \Bitrix\Iblock\EO_SectionElement unsetIblockElement()
	 * @method bool hasIblockElement()
	 * @method bool isIblockElementFilled()
	 * @method bool isIblockElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillIblockElement()
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
	 * @method \Bitrix\Iblock\EO_SectionElement set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_SectionElement reset($fieldName)
	 * @method \Bitrix\Iblock\EO_SectionElement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_SectionElement wakeUp($data)
	 */
	class EO_SectionElement {
		/* @var \Bitrix\Iblock\SectionElementTable */
		static public $dataClass = '\Bitrix\Iblock\SectionElementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_SectionElement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockSectionIdList()
	 * @method \int[] getIblockElementIdList()
	 * @method \int[] getAdditionalPropertyIdList()
	 * @method \int[] fillAdditionalPropertyId()
	 * @method \Bitrix\Iblock\EO_Section[] getIblockSectionList()
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection getIblockSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillIblockSection()
	 * @method \Bitrix\Iblock\EO_Element[] getIblockElementList()
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection getIblockElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillIblockElement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_SectionElement $object)
	 * @method bool has(\Bitrix\Iblock\EO_SectionElement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_SectionElement getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_SectionElement[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_SectionElement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_SectionElement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_SectionElement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SectionElement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\SectionElementTable */
		static public $dataClass = '\Bitrix\Iblock\SectionElementTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SectionElement_Result exec()
	 * @method \Bitrix\Iblock\EO_SectionElement fetchObject()
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SectionElement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_SectionElement fetchObject()
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection fetchCollection()
	 */
	class EO_SectionElement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_SectionElement createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_SectionElement wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_SectionElement_Collection wakeUpCollection($rows)
	 */
	class EO_SectionElement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\SectionPropertyTable:iblock/lib/sectionpropertytable.php:5c334f1830454198e226124611437bda */
namespace Bitrix\Iblock {
	/**
	 * EO_SectionProperty
	 * @see \Bitrix\Iblock\SectionPropertyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_SectionProperty setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int getSectionId()
	 * @method \Bitrix\Iblock\EO_SectionProperty setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int getPropertyId()
	 * @method \Bitrix\Iblock\EO_SectionProperty setPropertyId(\int|\Bitrix\Main\DB\SqlExpression $propertyId)
	 * @method bool hasPropertyId()
	 * @method bool isPropertyIdFilled()
	 * @method bool isPropertyIdChanged()
	 * @method \boolean getSmartFilter()
	 * @method \Bitrix\Iblock\EO_SectionProperty setSmartFilter(\boolean|\Bitrix\Main\DB\SqlExpression $smartFilter)
	 * @method bool hasSmartFilter()
	 * @method bool isSmartFilterFilled()
	 * @method bool isSmartFilterChanged()
	 * @method \boolean remindActualSmartFilter()
	 * @method \boolean requireSmartFilter()
	 * @method \Bitrix\Iblock\EO_SectionProperty resetSmartFilter()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetSmartFilter()
	 * @method \boolean fillSmartFilter()
	 * @method \string getDisplayType()
	 * @method \Bitrix\Iblock\EO_SectionProperty setDisplayType(\string|\Bitrix\Main\DB\SqlExpression $displayType)
	 * @method bool hasDisplayType()
	 * @method bool isDisplayTypeFilled()
	 * @method bool isDisplayTypeChanged()
	 * @method \string remindActualDisplayType()
	 * @method \string requireDisplayType()
	 * @method \Bitrix\Iblock\EO_SectionProperty resetDisplayType()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetDisplayType()
	 * @method \string fillDisplayType()
	 * @method \boolean getDisplayExpanded()
	 * @method \Bitrix\Iblock\EO_SectionProperty setDisplayExpanded(\boolean|\Bitrix\Main\DB\SqlExpression $displayExpanded)
	 * @method bool hasDisplayExpanded()
	 * @method bool isDisplayExpandedFilled()
	 * @method bool isDisplayExpandedChanged()
	 * @method \boolean remindActualDisplayExpanded()
	 * @method \boolean requireDisplayExpanded()
	 * @method \Bitrix\Iblock\EO_SectionProperty resetDisplayExpanded()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetDisplayExpanded()
	 * @method \boolean fillDisplayExpanded()
	 * @method \string getFilterHint()
	 * @method \Bitrix\Iblock\EO_SectionProperty setFilterHint(\string|\Bitrix\Main\DB\SqlExpression $filterHint)
	 * @method bool hasFilterHint()
	 * @method bool isFilterHintFilled()
	 * @method bool isFilterHintChanged()
	 * @method \string remindActualFilterHint()
	 * @method \string requireFilterHint()
	 * @method \Bitrix\Iblock\EO_SectionProperty resetFilterHint()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetFilterHint()
	 * @method \string fillFilterHint()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_SectionProperty setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_SectionProperty resetIblock()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\Property getProperty()
	 * @method \Bitrix\Iblock\Property remindActualProperty()
	 * @method \Bitrix\Iblock\Property requireProperty()
	 * @method \Bitrix\Iblock\EO_SectionProperty setProperty(\Bitrix\Iblock\Property $object)
	 * @method \Bitrix\Iblock\EO_SectionProperty resetProperty()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetProperty()
	 * @method bool hasProperty()
	 * @method bool isPropertyFilled()
	 * @method bool isPropertyChanged()
	 * @method \Bitrix\Iblock\Property fillProperty()
	 * @method \Bitrix\Iblock\EO_Section getSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualSection()
	 * @method \Bitrix\Iblock\EO_Section requireSection()
	 * @method \Bitrix\Iblock\EO_SectionProperty setSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\EO_SectionProperty resetSection()
	 * @method \Bitrix\Iblock\EO_SectionProperty unsetSection()
	 * @method bool hasSection()
	 * @method bool isSectionFilled()
	 * @method bool isSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillSection()
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
	 * @method \Bitrix\Iblock\EO_SectionProperty set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_SectionProperty reset($fieldName)
	 * @method \Bitrix\Iblock\EO_SectionProperty unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_SectionProperty wakeUp($data)
	 */
	class EO_SectionProperty {
		/* @var \Bitrix\Iblock\SectionPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\SectionPropertyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_SectionProperty_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \int[] getSectionIdList()
	 * @method \int[] getPropertyIdList()
	 * @method \boolean[] getSmartFilterList()
	 * @method \boolean[] fillSmartFilter()
	 * @method \string[] getDisplayTypeList()
	 * @method \string[] fillDisplayType()
	 * @method \boolean[] getDisplayExpandedList()
	 * @method \boolean[] fillDisplayExpanded()
	 * @method \string[] getFilterHintList()
	 * @method \string[] fillFilterHint()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\Property[] getPropertyList()
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection getPropertyCollection()
	 * @method \Bitrix\Iblock\EO_Property_Collection fillProperty()
	 * @method \Bitrix\Iblock\EO_Section[] getSectionList()
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection getSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillSection()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_SectionProperty $object)
	 * @method bool has(\Bitrix\Iblock\EO_SectionProperty $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_SectionProperty getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_SectionProperty[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_SectionProperty $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_SectionProperty_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_SectionProperty current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SectionProperty_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\SectionPropertyTable */
		static public $dataClass = '\Bitrix\Iblock\SectionPropertyTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SectionProperty_Result exec()
	 * @method \Bitrix\Iblock\EO_SectionProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SectionProperty_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_SectionProperty fetchObject()
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection fetchCollection()
	 */
	class EO_SectionProperty_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_SectionProperty createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_SectionProperty wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_SectionProperty_Collection wakeUpCollection($rows)
	 */
	class EO_SectionProperty_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\SectionTable:iblock/lib/sectiontable.php:bf9f2b4e51964bc0d060de4c3d7ea218 */
namespace Bitrix\Iblock {
	/**
	 * EO_Section
	 * @see \Bitrix\Iblock\SectionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Iblock\EO_Section setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Iblock\EO_Section setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Iblock\EO_Section resetTimestampX()
	 * @method \Bitrix\Iblock\EO_Section unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Iblock\EO_Section setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Iblock\EO_Section resetModifiedBy()
	 * @method \Bitrix\Iblock\EO_Section unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Iblock\EO_Section setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Iblock\EO_Section resetDateCreate()
	 * @method \Bitrix\Iblock\EO_Section unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Iblock\EO_Section setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Iblock\EO_Section resetCreatedBy()
	 * @method \Bitrix\Iblock\EO_Section unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_Section setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Iblock\EO_Section resetIblockId()
	 * @method \Bitrix\Iblock\EO_Section unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \int getIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Section setIblockSectionId(\int|\Bitrix\Main\DB\SqlExpression $iblockSectionId)
	 * @method bool hasIblockSectionId()
	 * @method bool isIblockSectionIdFilled()
	 * @method bool isIblockSectionIdChanged()
	 * @method \int remindActualIblockSectionId()
	 * @method \int requireIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Section resetIblockSectionId()
	 * @method \Bitrix\Iblock\EO_Section unsetIblockSectionId()
	 * @method \int fillIblockSectionId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Iblock\EO_Section setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Iblock\EO_Section resetActive()
	 * @method \Bitrix\Iblock\EO_Section unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getGlobalActive()
	 * @method \Bitrix\Iblock\EO_Section setGlobalActive(\boolean|\Bitrix\Main\DB\SqlExpression $globalActive)
	 * @method bool hasGlobalActive()
	 * @method bool isGlobalActiveFilled()
	 * @method bool isGlobalActiveChanged()
	 * @method \boolean remindActualGlobalActive()
	 * @method \boolean requireGlobalActive()
	 * @method \Bitrix\Iblock\EO_Section resetGlobalActive()
	 * @method \Bitrix\Iblock\EO_Section unsetGlobalActive()
	 * @method \boolean fillGlobalActive()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\EO_Section setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\EO_Section resetSort()
	 * @method \Bitrix\Iblock\EO_Section unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\EO_Section setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\EO_Section resetName()
	 * @method \Bitrix\Iblock\EO_Section unsetName()
	 * @method \string fillName()
	 * @method \int getPicture()
	 * @method \Bitrix\Iblock\EO_Section setPicture(\int|\Bitrix\Main\DB\SqlExpression $picture)
	 * @method bool hasPicture()
	 * @method bool isPictureFilled()
	 * @method bool isPictureChanged()
	 * @method \int remindActualPicture()
	 * @method \int requirePicture()
	 * @method \Bitrix\Iblock\EO_Section resetPicture()
	 * @method \Bitrix\Iblock\EO_Section unsetPicture()
	 * @method \int fillPicture()
	 * @method \int getLeftMargin()
	 * @method \Bitrix\Iblock\EO_Section setLeftMargin(\int|\Bitrix\Main\DB\SqlExpression $leftMargin)
	 * @method bool hasLeftMargin()
	 * @method bool isLeftMarginFilled()
	 * @method bool isLeftMarginChanged()
	 * @method \int remindActualLeftMargin()
	 * @method \int requireLeftMargin()
	 * @method \Bitrix\Iblock\EO_Section resetLeftMargin()
	 * @method \Bitrix\Iblock\EO_Section unsetLeftMargin()
	 * @method \int fillLeftMargin()
	 * @method \int getRightMargin()
	 * @method \Bitrix\Iblock\EO_Section setRightMargin(\int|\Bitrix\Main\DB\SqlExpression $rightMargin)
	 * @method bool hasRightMargin()
	 * @method bool isRightMarginFilled()
	 * @method bool isRightMarginChanged()
	 * @method \int remindActualRightMargin()
	 * @method \int requireRightMargin()
	 * @method \Bitrix\Iblock\EO_Section resetRightMargin()
	 * @method \Bitrix\Iblock\EO_Section unsetRightMargin()
	 * @method \int fillRightMargin()
	 * @method \int getDepthLevel()
	 * @method \Bitrix\Iblock\EO_Section setDepthLevel(\int|\Bitrix\Main\DB\SqlExpression $depthLevel)
	 * @method bool hasDepthLevel()
	 * @method bool isDepthLevelFilled()
	 * @method bool isDepthLevelChanged()
	 * @method \int remindActualDepthLevel()
	 * @method \int requireDepthLevel()
	 * @method \Bitrix\Iblock\EO_Section resetDepthLevel()
	 * @method \Bitrix\Iblock\EO_Section unsetDepthLevel()
	 * @method \int fillDepthLevel()
	 * @method \string getDescription()
	 * @method \Bitrix\Iblock\EO_Section setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Iblock\EO_Section resetDescription()
	 * @method \Bitrix\Iblock\EO_Section unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getDescriptionType()
	 * @method \Bitrix\Iblock\EO_Section setDescriptionType(\string|\Bitrix\Main\DB\SqlExpression $descriptionType)
	 * @method bool hasDescriptionType()
	 * @method bool isDescriptionTypeFilled()
	 * @method bool isDescriptionTypeChanged()
	 * @method \string remindActualDescriptionType()
	 * @method \string requireDescriptionType()
	 * @method \Bitrix\Iblock\EO_Section resetDescriptionType()
	 * @method \Bitrix\Iblock\EO_Section unsetDescriptionType()
	 * @method \string fillDescriptionType()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Iblock\EO_Section setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Iblock\EO_Section resetSearchableContent()
	 * @method \Bitrix\Iblock\EO_Section unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\EO_Section setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Iblock\EO_Section resetCode()
	 * @method \Bitrix\Iblock\EO_Section unsetCode()
	 * @method \string fillCode()
	 * @method \string getXmlId()
	 * @method \Bitrix\Iblock\EO_Section setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Iblock\EO_Section resetXmlId()
	 * @method \Bitrix\Iblock\EO_Section unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getTmpId()
	 * @method \Bitrix\Iblock\EO_Section setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Iblock\EO_Section resetTmpId()
	 * @method \Bitrix\Iblock\EO_Section unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \int getDetailPicture()
	 * @method \Bitrix\Iblock\EO_Section setDetailPicture(\int|\Bitrix\Main\DB\SqlExpression $detailPicture)
	 * @method bool hasDetailPicture()
	 * @method bool isDetailPictureFilled()
	 * @method bool isDetailPictureChanged()
	 * @method \int remindActualDetailPicture()
	 * @method \int requireDetailPicture()
	 * @method \Bitrix\Iblock\EO_Section resetDetailPicture()
	 * @method \Bitrix\Iblock\EO_Section unsetDetailPicture()
	 * @method \int fillDetailPicture()
	 * @method \int getSocnetGroupId()
	 * @method \Bitrix\Iblock\EO_Section setSocnetGroupId(\int|\Bitrix\Main\DB\SqlExpression $socnetGroupId)
	 * @method bool hasSocnetGroupId()
	 * @method bool isSocnetGroupIdFilled()
	 * @method bool isSocnetGroupIdChanged()
	 * @method \int remindActualSocnetGroupId()
	 * @method \int requireSocnetGroupId()
	 * @method \Bitrix\Iblock\EO_Section resetSocnetGroupId()
	 * @method \Bitrix\Iblock\EO_Section unsetSocnetGroupId()
	 * @method \int fillSocnetGroupId()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_Section setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_Section resetIblock()
	 * @method \Bitrix\Iblock\EO_Section unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\EO_Section getParentSection()
	 * @method \Bitrix\Iblock\EO_Section remindActualParentSection()
	 * @method \Bitrix\Iblock\EO_Section requireParentSection()
	 * @method \Bitrix\Iblock\EO_Section setParentSection(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Iblock\EO_Section resetParentSection()
	 * @method \Bitrix\Iblock\EO_Section unsetParentSection()
	 * @method bool hasParentSection()
	 * @method bool isParentSectionFilled()
	 * @method bool isParentSectionChanged()
	 * @method \Bitrix\Iblock\EO_Section fillParentSection()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Iblock\EO_Section setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\EO_Section resetCreatedByUser()
	 * @method \Bitrix\Iblock\EO_Section unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Iblock\EO_Section setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Iblock\EO_Section resetModifiedByUser()
	 * @method \Bitrix\Iblock\EO_Section unsetModifiedByUser()
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
	 * @method \Bitrix\Iblock\EO_Section set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_Section reset($fieldName)
	 * @method \Bitrix\Iblock\EO_Section unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_Section wakeUp($data)
	 */
	class EO_Section {
		/* @var \Bitrix\Iblock\SectionTable */
		static public $dataClass = '\Bitrix\Iblock\SectionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Section_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \int[] getIblockSectionIdList()
	 * @method \int[] fillIblockSectionId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getGlobalActiveList()
	 * @method \boolean[] fillGlobalActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getPictureList()
	 * @method \int[] fillPicture()
	 * @method \int[] getLeftMarginList()
	 * @method \int[] fillLeftMargin()
	 * @method \int[] getRightMarginList()
	 * @method \int[] fillRightMargin()
	 * @method \int[] getDepthLevelList()
	 * @method \int[] fillDepthLevel()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getDescriptionTypeList()
	 * @method \string[] fillDescriptionType()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \int[] getDetailPictureList()
	 * @method \int[] fillDetailPicture()
	 * @method \int[] getSocnetGroupIdList()
	 * @method \int[] fillSocnetGroupId()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_Section_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\EO_Section[] getParentSectionList()
	 * @method \Bitrix\Iblock\EO_Section_Collection getParentSectionCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillParentSection()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Iblock\EO_Section_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Iblock\EO_Section_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_Section $object)
	 * @method bool has(\Bitrix\Iblock\EO_Section $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Section getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Section[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_Section $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Section_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_Section current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Section_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\SectionTable */
		static public $dataClass = '\Bitrix\Iblock\SectionTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Section_Result exec()
	 * @method \Bitrix\Iblock\EO_Section fetchObject()
	 * @method \Bitrix\Iblock\EO_Section_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Section_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_Section fetchObject()
	 * @method \Bitrix\Iblock\EO_Section_Collection fetchCollection()
	 */
	class EO_Section_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_Section createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Section_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_Section wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Section_Collection wakeUpCollection($rows)
	 */
	class EO_Section_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\SequenceTable:iblock/lib/sequence.php:d58dd0ad5b72b1666498e7953375cadb */
namespace Bitrix\Iblock {
	/**
	 * EO_Sequence
	 * @see \Bitrix\Iblock\SequenceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_Sequence setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Iblock\EO_Sequence setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \int getSeqValue()
	 * @method \Bitrix\Iblock\EO_Sequence setSeqValue(\int|\Bitrix\Main\DB\SqlExpression $seqValue)
	 * @method bool hasSeqValue()
	 * @method bool isSeqValueFilled()
	 * @method bool isSeqValueChanged()
	 * @method \int remindActualSeqValue()
	 * @method \int requireSeqValue()
	 * @method \Bitrix\Iblock\EO_Sequence resetSeqValue()
	 * @method \Bitrix\Iblock\EO_Sequence unsetSeqValue()
	 * @method \int fillSeqValue()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_Sequence setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_Sequence resetIblock()
	 * @method \Bitrix\Iblock\EO_Sequence unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
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
	 * @method \Bitrix\Iblock\EO_Sequence set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_Sequence reset($fieldName)
	 * @method \Bitrix\Iblock\EO_Sequence unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_Sequence wakeUp($data)
	 */
	class EO_Sequence {
		/* @var \Bitrix\Iblock\SequenceTable */
		static public $dataClass = '\Bitrix\Iblock\SequenceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Sequence_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \string[] getCodeList()
	 * @method \int[] getSeqValueList()
	 * @method \int[] fillSeqValue()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_Sequence_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_Sequence $object)
	 * @method bool has(\Bitrix\Iblock\EO_Sequence $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Sequence getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Sequence[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_Sequence $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Sequence_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_Sequence current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Sequence_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\SequenceTable */
		static public $dataClass = '\Bitrix\Iblock\SequenceTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Sequence_Result exec()
	 * @method \Bitrix\Iblock\EO_Sequence fetchObject()
	 * @method \Bitrix\Iblock\EO_Sequence_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Sequence_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_Sequence fetchObject()
	 * @method \Bitrix\Iblock\EO_Sequence_Collection fetchCollection()
	 */
	class EO_Sequence_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_Sequence createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Sequence_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_Sequence wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Sequence_Collection wakeUpCollection($rows)
	 */
	class EO_Sequence_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\SiteTable:iblock/lib/site.php:c5e740890b8a9731aab866f3bb4ae01d */
namespace Bitrix\Iblock {
	/**
	 * EO_Site
	 * @see \Bitrix\Iblock\SiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Iblock\EO_Site setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Iblock\EO_Site setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Iblock\EO_Site setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Iblock\EO_Site resetIblock()
	 * @method \Bitrix\Iblock\EO_Site unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Iblock\EO_Site setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Iblock\EO_Site resetSite()
	 * @method \Bitrix\Iblock\EO_Site unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
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
	 * @method \Bitrix\Iblock\EO_Site set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_Site reset($fieldName)
	 * @method \Bitrix\Iblock\EO_Site unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_Site wakeUp($data)
	 */
	class EO_Site {
		/* @var \Bitrix\Iblock\SiteTable */
		static public $dataClass = '\Bitrix\Iblock\SiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Site_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \string[] getSiteIdList()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Iblock\EO_Site_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Iblock\EO_Site_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_Site $object)
	 * @method bool has(\Bitrix\Iblock\EO_Site $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Site getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Site[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_Site $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Site_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_Site current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Site_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\SiteTable */
		static public $dataClass = '\Bitrix\Iblock\SiteTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Site_Result exec()
	 * @method \Bitrix\Iblock\EO_Site fetchObject()
	 * @method \Bitrix\Iblock\EO_Site_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Site_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_Site fetchObject()
	 * @method \Bitrix\Iblock\EO_Site_Collection fetchCollection()
	 */
	class EO_Site_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_Site createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Site_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_Site wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Site_Collection wakeUpCollection($rows)
	 */
	class EO_Site_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\TypeTable:iblock/lib/type.php:e5097a2449e5645553c7833b1eb10008 */
namespace Bitrix\Iblock {
	/**
	 * EO_Type
	 * @see \Bitrix\Iblock\TypeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Iblock\EO_Type setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getSections()
	 * @method \Bitrix\Iblock\EO_Type setSections(\boolean|\Bitrix\Main\DB\SqlExpression $sections)
	 * @method bool hasSections()
	 * @method bool isSectionsFilled()
	 * @method bool isSectionsChanged()
	 * @method \boolean remindActualSections()
	 * @method \boolean requireSections()
	 * @method \Bitrix\Iblock\EO_Type resetSections()
	 * @method \Bitrix\Iblock\EO_Type unsetSections()
	 * @method \boolean fillSections()
	 * @method \string getEditFileBefore()
	 * @method \Bitrix\Iblock\EO_Type setEditFileBefore(\string|\Bitrix\Main\DB\SqlExpression $editFileBefore)
	 * @method bool hasEditFileBefore()
	 * @method bool isEditFileBeforeFilled()
	 * @method bool isEditFileBeforeChanged()
	 * @method \string remindActualEditFileBefore()
	 * @method \string requireEditFileBefore()
	 * @method \Bitrix\Iblock\EO_Type resetEditFileBefore()
	 * @method \Bitrix\Iblock\EO_Type unsetEditFileBefore()
	 * @method \string fillEditFileBefore()
	 * @method \string getEditFileAfter()
	 * @method \Bitrix\Iblock\EO_Type setEditFileAfter(\string|\Bitrix\Main\DB\SqlExpression $editFileAfter)
	 * @method bool hasEditFileAfter()
	 * @method bool isEditFileAfterFilled()
	 * @method bool isEditFileAfterChanged()
	 * @method \string remindActualEditFileAfter()
	 * @method \string requireEditFileAfter()
	 * @method \Bitrix\Iblock\EO_Type resetEditFileAfter()
	 * @method \Bitrix\Iblock\EO_Type unsetEditFileAfter()
	 * @method \string fillEditFileAfter()
	 * @method \boolean getInRss()
	 * @method \Bitrix\Iblock\EO_Type setInRss(\boolean|\Bitrix\Main\DB\SqlExpression $inRss)
	 * @method bool hasInRss()
	 * @method bool isInRssFilled()
	 * @method bool isInRssChanged()
	 * @method \boolean remindActualInRss()
	 * @method \boolean requireInRss()
	 * @method \Bitrix\Iblock\EO_Type resetInRss()
	 * @method \Bitrix\Iblock\EO_Type unsetInRss()
	 * @method \boolean fillInRss()
	 * @method \int getSort()
	 * @method \Bitrix\Iblock\EO_Type setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Iblock\EO_Type resetSort()
	 * @method \Bitrix\Iblock\EO_Type unsetSort()
	 * @method \int fillSort()
	 * @method \Bitrix\Iblock\EO_TypeLanguage getLangMessage()
	 * @method \Bitrix\Iblock\EO_TypeLanguage remindActualLangMessage()
	 * @method \Bitrix\Iblock\EO_TypeLanguage requireLangMessage()
	 * @method \Bitrix\Iblock\EO_Type setLangMessage(\Bitrix\Iblock\EO_TypeLanguage $object)
	 * @method \Bitrix\Iblock\EO_Type resetLangMessage()
	 * @method \Bitrix\Iblock\EO_Type unsetLangMessage()
	 * @method bool hasLangMessage()
	 * @method bool isLangMessageFilled()
	 * @method bool isLangMessageChanged()
	 * @method \Bitrix\Iblock\EO_TypeLanguage fillLangMessage()
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
	 * @method \Bitrix\Iblock\EO_Type set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_Type reset($fieldName)
	 * @method \Bitrix\Iblock\EO_Type unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_Type wakeUp($data)
	 */
	class EO_Type {
		/* @var \Bitrix\Iblock\TypeTable */
		static public $dataClass = '\Bitrix\Iblock\TypeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_Type_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \boolean[] getSectionsList()
	 * @method \boolean[] fillSections()
	 * @method \string[] getEditFileBeforeList()
	 * @method \string[] fillEditFileBefore()
	 * @method \string[] getEditFileAfterList()
	 * @method \string[] fillEditFileAfter()
	 * @method \boolean[] getInRssList()
	 * @method \boolean[] fillInRss()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \Bitrix\Iblock\EO_TypeLanguage[] getLangMessageList()
	 * @method \Bitrix\Iblock\EO_Type_Collection getLangMessageCollection()
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection fillLangMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_Type $object)
	 * @method bool has(\Bitrix\Iblock\EO_Type $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Type getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_Type[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_Type $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_Type_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_Type current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Type_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\TypeTable */
		static public $dataClass = '\Bitrix\Iblock\TypeTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Type_Result exec()
	 * @method \Bitrix\Iblock\EO_Type fetchObject()
	 * @method \Bitrix\Iblock\EO_Type_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Type_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_Type fetchObject()
	 * @method \Bitrix\Iblock\EO_Type_Collection fetchCollection()
	 */
	class EO_Type_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_Type createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_Type_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_Type wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_Type_Collection wakeUpCollection($rows)
	 */
	class EO_Type_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Iblock\TypeLanguageTable:iblock/lib/typelanguage.php:419def59859fefccd51523bf7bb868ca */
namespace Bitrix\Iblock {
	/**
	 * EO_TypeLanguage
	 * @see \Bitrix\Iblock\TypeLanguageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getIblockTypeId()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setIblockTypeId(\string|\Bitrix\Main\DB\SqlExpression $iblockTypeId)
	 * @method bool hasIblockTypeId()
	 * @method bool isIblockTypeIdFilled()
	 * @method bool isIblockTypeIdChanged()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage resetName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage unsetName()
	 * @method \string fillName()
	 * @method \string getSectionsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setSectionsName(\string|\Bitrix\Main\DB\SqlExpression $sectionsName)
	 * @method bool hasSectionsName()
	 * @method bool isSectionsNameFilled()
	 * @method bool isSectionsNameChanged()
	 * @method \string remindActualSectionsName()
	 * @method \string requireSectionsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage resetSectionsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage unsetSectionsName()
	 * @method \string fillSectionsName()
	 * @method \string getElementsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setElementsName(\string|\Bitrix\Main\DB\SqlExpression $elementsName)
	 * @method bool hasElementsName()
	 * @method bool isElementsNameFilled()
	 * @method bool isElementsNameChanged()
	 * @method \string remindActualElementsName()
	 * @method \string requireElementsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage resetElementsName()
	 * @method \Bitrix\Iblock\EO_TypeLanguage unsetElementsName()
	 * @method \string fillElementsName()
	 * @method \Bitrix\Main\Localization\EO_Language getLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language remindActualLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language requireLanguage()
	 * @method \Bitrix\Iblock\EO_TypeLanguage setLanguage(\Bitrix\Main\Localization\EO_Language $object)
	 * @method \Bitrix\Iblock\EO_TypeLanguage resetLanguage()
	 * @method \Bitrix\Iblock\EO_TypeLanguage unsetLanguage()
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
	 * @method \Bitrix\Iblock\EO_TypeLanguage set($fieldName, $value)
	 * @method \Bitrix\Iblock\EO_TypeLanguage reset($fieldName)
	 * @method \Bitrix\Iblock\EO_TypeLanguage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Iblock\EO_TypeLanguage wakeUp($data)
	 */
	class EO_TypeLanguage {
		/* @var \Bitrix\Iblock\TypeLanguageTable */
		static public $dataClass = '\Bitrix\Iblock\TypeLanguageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Iblock {
	/**
	 * EO_TypeLanguage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIblockTypeIdList()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getSectionsNameList()
	 * @method \string[] fillSectionsName()
	 * @method \string[] getElementsNameList()
	 * @method \string[] fillElementsName()
	 * @method \Bitrix\Main\Localization\EO_Language[] getLanguageList()
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection getLanguageCollection()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fillLanguage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Iblock\EO_TypeLanguage $object)
	 * @method bool has(\Bitrix\Iblock\EO_TypeLanguage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_TypeLanguage getByPrimary($primary)
	 * @method \Bitrix\Iblock\EO_TypeLanguage[] getAll()
	 * @method bool remove(\Bitrix\Iblock\EO_TypeLanguage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Iblock\EO_TypeLanguage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Iblock\EO_TypeLanguage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TypeLanguage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Iblock\TypeLanguageTable */
		static public $dataClass = '\Bitrix\Iblock\TypeLanguageTable';
	}
}
namespace Bitrix\Iblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TypeLanguage_Result exec()
	 * @method \Bitrix\Iblock\EO_TypeLanguage fetchObject()
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TypeLanguage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Iblock\EO_TypeLanguage fetchObject()
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection fetchCollection()
	 */
	class EO_TypeLanguage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Iblock\EO_TypeLanguage createObject($setDefaultValues = true)
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection createCollection()
	 * @method \Bitrix\Iblock\EO_TypeLanguage wakeUpObject($row)
	 * @method \Bitrix\Iblock\EO_TypeLanguage_Collection wakeUpCollection($rows)
	 */
	class EO_TypeLanguage_Entity extends \Bitrix\Main\ORM\Entity {}
}