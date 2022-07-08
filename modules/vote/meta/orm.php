<?php

/* ORMENTITYANNOTATION:Bitrix\Vote\AnswerTable:vote/lib/answer.php:e00fdade8b968cf689d19eb2a4002d0b */
namespace Bitrix\Vote {
	/**
	 * EO_Answer
	 * @see \Bitrix\Vote\AnswerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Answer setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getActive()
	 * @method \Bitrix\Vote\EO_Answer setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Vote\EO_Answer resetActive()
	 * @method \Bitrix\Vote\EO_Answer unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Vote\EO_Answer setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Vote\EO_Answer resetTimestampX()
	 * @method \Bitrix\Vote\EO_Answer unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getQuestionId()
	 * @method \Bitrix\Vote\EO_Answer setQuestionId(\int|\Bitrix\Main\DB\SqlExpression $questionId)
	 * @method bool hasQuestionId()
	 * @method bool isQuestionIdFilled()
	 * @method bool isQuestionIdChanged()
	 * @method \int remindActualQuestionId()
	 * @method \int requireQuestionId()
	 * @method \Bitrix\Vote\EO_Answer resetQuestionId()
	 * @method \Bitrix\Vote\EO_Answer unsetQuestionId()
	 * @method \int fillQuestionId()
	 * @method \int getCSort()
	 * @method \Bitrix\Vote\EO_Answer setCSort(\int|\Bitrix\Main\DB\SqlExpression $cSort)
	 * @method bool hasCSort()
	 * @method bool isCSortFilled()
	 * @method bool isCSortChanged()
	 * @method \int remindActualCSort()
	 * @method \int requireCSort()
	 * @method \Bitrix\Vote\EO_Answer resetCSort()
	 * @method \Bitrix\Vote\EO_Answer unsetCSort()
	 * @method \int fillCSort()
	 * @method \int getImageId()
	 * @method \Bitrix\Vote\EO_Answer setImageId(\int|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \int remindActualImageId()
	 * @method \int requireImageId()
	 * @method \Bitrix\Vote\EO_Answer resetImageId()
	 * @method \Bitrix\Vote\EO_Answer unsetImageId()
	 * @method \int fillImageId()
	 * @method \Bitrix\Main\EO_File getImage()
	 * @method \Bitrix\Main\EO_File remindActualImage()
	 * @method \Bitrix\Main\EO_File requireImage()
	 * @method \Bitrix\Vote\EO_Answer setImage(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Vote\EO_Answer resetImage()
	 * @method \Bitrix\Vote\EO_Answer unsetImage()
	 * @method bool hasImage()
	 * @method bool isImageFilled()
	 * @method bool isImageChanged()
	 * @method \Bitrix\Main\EO_File fillImage()
	 * @method \string getMessage()
	 * @method \Bitrix\Vote\EO_Answer setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Vote\EO_Answer resetMessage()
	 * @method \Bitrix\Vote\EO_Answer unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getMessageType()
	 * @method \Bitrix\Vote\EO_Answer setMessageType(\string|\Bitrix\Main\DB\SqlExpression $messageType)
	 * @method bool hasMessageType()
	 * @method bool isMessageTypeFilled()
	 * @method bool isMessageTypeChanged()
	 * @method \string remindActualMessageType()
	 * @method \string requireMessageType()
	 * @method \Bitrix\Vote\EO_Answer resetMessageType()
	 * @method \Bitrix\Vote\EO_Answer unsetMessageType()
	 * @method \string fillMessageType()
	 * @method \int getCounter()
	 * @method \Bitrix\Vote\EO_Answer setCounter(\int|\Bitrix\Main\DB\SqlExpression $counter)
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \int remindActualCounter()
	 * @method \int requireCounter()
	 * @method \Bitrix\Vote\EO_Answer resetCounter()
	 * @method \Bitrix\Vote\EO_Answer unsetCounter()
	 * @method \int fillCounter()
	 * @method \int getFieldType()
	 * @method \Bitrix\Vote\EO_Answer setFieldType(\int|\Bitrix\Main\DB\SqlExpression $fieldType)
	 * @method bool hasFieldType()
	 * @method bool isFieldTypeFilled()
	 * @method bool isFieldTypeChanged()
	 * @method \int remindActualFieldType()
	 * @method \int requireFieldType()
	 * @method \Bitrix\Vote\EO_Answer resetFieldType()
	 * @method \Bitrix\Vote\EO_Answer unsetFieldType()
	 * @method \int fillFieldType()
	 * @method \int getFieldWidth()
	 * @method \Bitrix\Vote\EO_Answer setFieldWidth(\int|\Bitrix\Main\DB\SqlExpression $fieldWidth)
	 * @method bool hasFieldWidth()
	 * @method bool isFieldWidthFilled()
	 * @method bool isFieldWidthChanged()
	 * @method \int remindActualFieldWidth()
	 * @method \int requireFieldWidth()
	 * @method \Bitrix\Vote\EO_Answer resetFieldWidth()
	 * @method \Bitrix\Vote\EO_Answer unsetFieldWidth()
	 * @method \int fillFieldWidth()
	 * @method \int getFieldHeight()
	 * @method \Bitrix\Vote\EO_Answer setFieldHeight(\int|\Bitrix\Main\DB\SqlExpression $fieldHeight)
	 * @method bool hasFieldHeight()
	 * @method bool isFieldHeightFilled()
	 * @method bool isFieldHeightChanged()
	 * @method \int remindActualFieldHeight()
	 * @method \int requireFieldHeight()
	 * @method \Bitrix\Vote\EO_Answer resetFieldHeight()
	 * @method \Bitrix\Vote\EO_Answer unsetFieldHeight()
	 * @method \int fillFieldHeight()
	 * @method \string getFieldParam()
	 * @method \Bitrix\Vote\EO_Answer setFieldParam(\string|\Bitrix\Main\DB\SqlExpression $fieldParam)
	 * @method bool hasFieldParam()
	 * @method bool isFieldParamFilled()
	 * @method bool isFieldParamChanged()
	 * @method \string remindActualFieldParam()
	 * @method \string requireFieldParam()
	 * @method \Bitrix\Vote\EO_Answer resetFieldParam()
	 * @method \Bitrix\Vote\EO_Answer unsetFieldParam()
	 * @method \string fillFieldParam()
	 * @method \string getColor()
	 * @method \Bitrix\Vote\EO_Answer setColor(\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method \string remindActualColor()
	 * @method \string requireColor()
	 * @method \Bitrix\Vote\EO_Answer resetColor()
	 * @method \Bitrix\Vote\EO_Answer unsetColor()
	 * @method \string fillColor()
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
	 * @method \Bitrix\Vote\EO_Answer set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Answer reset($fieldName)
	 * @method \Bitrix\Vote\EO_Answer unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Answer wakeUp($data)
	 */
	class EO_Answer {
		/* @var \Bitrix\Vote\AnswerTable */
		static public $dataClass = '\Bitrix\Vote\AnswerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Answer_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getQuestionIdList()
	 * @method \int[] fillQuestionId()
	 * @method \int[] getCSortList()
	 * @method \int[] fillCSort()
	 * @method \int[] getImageIdList()
	 * @method \int[] fillImageId()
	 * @method \Bitrix\Main\EO_File[] getImageList()
	 * @method \Bitrix\Vote\EO_Answer_Collection getImageCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillImage()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getMessageTypeList()
	 * @method \string[] fillMessageType()
	 * @method \int[] getCounterList()
	 * @method \int[] fillCounter()
	 * @method \int[] getFieldTypeList()
	 * @method \int[] fillFieldType()
	 * @method \int[] getFieldWidthList()
	 * @method \int[] fillFieldWidth()
	 * @method \int[] getFieldHeightList()
	 * @method \int[] fillFieldHeight()
	 * @method \string[] getFieldParamList()
	 * @method \string[] fillFieldParam()
	 * @method \string[] getColorList()
	 * @method \string[] fillColor()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Answer $object)
	 * @method bool has(\Bitrix\Vote\EO_Answer $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Answer getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Answer[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Answer $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Answer_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Answer current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Answer_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\AnswerTable */
		static public $dataClass = '\Bitrix\Vote\AnswerTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Answer_Result exec()
	 * @method \Bitrix\Vote\EO_Answer fetchObject()
	 * @method \Bitrix\Vote\EO_Answer_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Answer_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Answer fetchObject()
	 * @method \Bitrix\Vote\EO_Answer_Collection fetchCollection()
	 */
	class EO_Answer_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Answer createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Answer_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Answer wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Answer_Collection wakeUpCollection($rows)
	 */
	class EO_Answer_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\AttachTable:vote/lib/attach.php:0c74c1cf8d95d0cb974d2e43c72e78b5 */
namespace Bitrix\Vote {
	/**
	 * EO_Attach
	 * @see \Bitrix\Vote\AttachTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Attach setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getObjectId()
	 * @method \Bitrix\Vote\EO_Attach setObjectId(\int|\Bitrix\Main\DB\SqlExpression $objectId)
	 * @method bool hasObjectId()
	 * @method bool isObjectIdFilled()
	 * @method bool isObjectIdChanged()
	 * @method \int remindActualObjectId()
	 * @method \int requireObjectId()
	 * @method \Bitrix\Vote\EO_Attach resetObjectId()
	 * @method \Bitrix\Vote\EO_Attach unsetObjectId()
	 * @method \int fillObjectId()
	 * @method \string getModuleId()
	 * @method \Bitrix\Vote\EO_Attach setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Vote\EO_Attach resetModuleId()
	 * @method \Bitrix\Vote\EO_Attach unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Vote\EO_Attach setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Vote\EO_Attach resetEntityType()
	 * @method \Bitrix\Vote\EO_Attach unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Vote\EO_Attach setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Vote\EO_Attach resetEntityId()
	 * @method \Bitrix\Vote\EO_Attach unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \Bitrix\Main\Type\DateTime getCreateTime()
	 * @method \Bitrix\Vote\EO_Attach setCreateTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createTime)
	 * @method bool hasCreateTime()
	 * @method bool isCreateTimeFilled()
	 * @method bool isCreateTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreateTime()
	 * @method \Bitrix\Main\Type\DateTime requireCreateTime()
	 * @method \Bitrix\Vote\EO_Attach resetCreateTime()
	 * @method \Bitrix\Vote\EO_Attach unsetCreateTime()
	 * @method \Bitrix\Main\Type\DateTime fillCreateTime()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Vote\EO_Attach setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Vote\EO_Attach resetCreatedBy()
	 * @method \Bitrix\Vote\EO_Attach unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Vote\EO_Vote getVote()
	 * @method \Bitrix\Vote\EO_Vote remindActualVote()
	 * @method \Bitrix\Vote\EO_Vote requireVote()
	 * @method \Bitrix\Vote\EO_Attach setVote(\Bitrix\Vote\EO_Vote $object)
	 * @method \Bitrix\Vote\EO_Attach resetVote()
	 * @method \Bitrix\Vote\EO_Attach unsetVote()
	 * @method bool hasVote()
	 * @method bool isVoteFilled()
	 * @method bool isVoteChanged()
	 * @method \Bitrix\Vote\EO_Vote fillVote()
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
	 * @method \Bitrix\Vote\EO_Attach set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Attach reset($fieldName)
	 * @method \Bitrix\Vote\EO_Attach unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Attach wakeUp($data)
	 */
	class EO_Attach {
		/* @var \Bitrix\Vote\AttachTable */
		static public $dataClass = '\Bitrix\Vote\AttachTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Attach_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getObjectIdList()
	 * @method \int[] fillObjectId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \Bitrix\Main\Type\DateTime[] getCreateTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreateTime()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Vote\EO_Vote[] getVoteList()
	 * @method \Bitrix\Vote\EO_Attach_Collection getVoteCollection()
	 * @method \Bitrix\Vote\EO_Vote_Collection fillVote()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Attach $object)
	 * @method bool has(\Bitrix\Vote\EO_Attach $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Attach getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Attach[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Attach $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Attach_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Attach current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Attach_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\AttachTable */
		static public $dataClass = '\Bitrix\Vote\AttachTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Attach_Result exec()
	 * @method \Bitrix\Vote\EO_Attach fetchObject()
	 * @method \Bitrix\Vote\EO_Attach_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Attach_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Attach fetchObject()
	 * @method \Bitrix\Vote\EO_Attach_Collection fetchCollection()
	 */
	class EO_Attach_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Attach createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Attach_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Attach wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Attach_Collection wakeUpCollection($rows)
	 */
	class EO_Attach_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\ChannelTable:vote/lib/channel.php:c9f0fe4672533048fc7f5a9029cde104 */
namespace Bitrix\Vote {
	/**
	 * EO_Channel
	 * @see \Bitrix\Vote\ChannelTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Channel setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSymbolicName()
	 * @method \Bitrix\Vote\EO_Channel setSymbolicName(\string|\Bitrix\Main\DB\SqlExpression $symbolicName)
	 * @method bool hasSymbolicName()
	 * @method bool isSymbolicNameFilled()
	 * @method bool isSymbolicNameChanged()
	 * @method \string remindActualSymbolicName()
	 * @method \string requireSymbolicName()
	 * @method \Bitrix\Vote\EO_Channel resetSymbolicName()
	 * @method \Bitrix\Vote\EO_Channel unsetSymbolicName()
	 * @method \string fillSymbolicName()
	 * @method \string getTitle()
	 * @method \Bitrix\Vote\EO_Channel setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Vote\EO_Channel resetTitle()
	 * @method \Bitrix\Vote\EO_Channel unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getCSort()
	 * @method \Bitrix\Vote\EO_Channel setCSort(\int|\Bitrix\Main\DB\SqlExpression $cSort)
	 * @method bool hasCSort()
	 * @method bool isCSortFilled()
	 * @method bool isCSortChanged()
	 * @method \int remindActualCSort()
	 * @method \int requireCSort()
	 * @method \Bitrix\Vote\EO_Channel resetCSort()
	 * @method \Bitrix\Vote\EO_Channel unsetCSort()
	 * @method \int fillCSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Vote\EO_Channel setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Vote\EO_Channel resetActive()
	 * @method \Bitrix\Vote\EO_Channel unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getHidden()
	 * @method \Bitrix\Vote\EO_Channel setHidden(\boolean|\Bitrix\Main\DB\SqlExpression $hidden)
	 * @method bool hasHidden()
	 * @method bool isHiddenFilled()
	 * @method bool isHiddenChanged()
	 * @method \boolean remindActualHidden()
	 * @method \boolean requireHidden()
	 * @method \Bitrix\Vote\EO_Channel resetHidden()
	 * @method \Bitrix\Vote\EO_Channel unsetHidden()
	 * @method \boolean fillHidden()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Vote\EO_Channel setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Vote\EO_Channel resetTimestampX()
	 * @method \Bitrix\Vote\EO_Channel unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \boolean getVoteSingle()
	 * @method \Bitrix\Vote\EO_Channel setVoteSingle(\boolean|\Bitrix\Main\DB\SqlExpression $voteSingle)
	 * @method bool hasVoteSingle()
	 * @method bool isVoteSingleFilled()
	 * @method bool isVoteSingleChanged()
	 * @method \boolean remindActualVoteSingle()
	 * @method \boolean requireVoteSingle()
	 * @method \Bitrix\Vote\EO_Channel resetVoteSingle()
	 * @method \Bitrix\Vote\EO_Channel unsetVoteSingle()
	 * @method \boolean fillVoteSingle()
	 * @method \boolean getUseCaptcha()
	 * @method \Bitrix\Vote\EO_Channel setUseCaptcha(\boolean|\Bitrix\Main\DB\SqlExpression $useCaptcha)
	 * @method bool hasUseCaptcha()
	 * @method bool isUseCaptchaFilled()
	 * @method bool isUseCaptchaChanged()
	 * @method \boolean remindActualUseCaptcha()
	 * @method \boolean requireUseCaptcha()
	 * @method \Bitrix\Vote\EO_Channel resetUseCaptcha()
	 * @method \Bitrix\Vote\EO_Channel unsetUseCaptcha()
	 * @method \boolean fillUseCaptcha()
	 * @method \Bitrix\Vote\EO_ChannelGroup getPermission()
	 * @method \Bitrix\Vote\EO_ChannelGroup remindActualPermission()
	 * @method \Bitrix\Vote\EO_ChannelGroup requirePermission()
	 * @method \Bitrix\Vote\EO_Channel setPermission(\Bitrix\Vote\EO_ChannelGroup $object)
	 * @method \Bitrix\Vote\EO_Channel resetPermission()
	 * @method \Bitrix\Vote\EO_Channel unsetPermission()
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \Bitrix\Vote\EO_ChannelGroup fillPermission()
	 * @method \Bitrix\Vote\EO_ChannelSite getSite()
	 * @method \Bitrix\Vote\EO_ChannelSite remindActualSite()
	 * @method \Bitrix\Vote\EO_ChannelSite requireSite()
	 * @method \Bitrix\Vote\EO_Channel setSite(\Bitrix\Vote\EO_ChannelSite $object)
	 * @method \Bitrix\Vote\EO_Channel resetSite()
	 * @method \Bitrix\Vote\EO_Channel unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Vote\EO_ChannelSite fillSite()
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
	 * @method \Bitrix\Vote\EO_Channel set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Channel reset($fieldName)
	 * @method \Bitrix\Vote\EO_Channel unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Channel wakeUp($data)
	 */
	class EO_Channel {
		/* @var \Bitrix\Vote\ChannelTable */
		static public $dataClass = '\Bitrix\Vote\ChannelTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Channel_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSymbolicNameList()
	 * @method \string[] fillSymbolicName()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getCSortList()
	 * @method \int[] fillCSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getHiddenList()
	 * @method \boolean[] fillHidden()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \boolean[] getVoteSingleList()
	 * @method \boolean[] fillVoteSingle()
	 * @method \boolean[] getUseCaptchaList()
	 * @method \boolean[] fillUseCaptcha()
	 * @method \Bitrix\Vote\EO_ChannelGroup[] getPermissionList()
	 * @method \Bitrix\Vote\EO_Channel_Collection getPermissionCollection()
	 * @method \Bitrix\Vote\EO_ChannelGroup_Collection fillPermission()
	 * @method \Bitrix\Vote\EO_ChannelSite[] getSiteList()
	 * @method \Bitrix\Vote\EO_Channel_Collection getSiteCollection()
	 * @method \Bitrix\Vote\EO_ChannelSite_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Channel $object)
	 * @method bool has(\Bitrix\Vote\EO_Channel $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Channel getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Channel[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Channel $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Channel_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Channel current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Channel_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\ChannelTable */
		static public $dataClass = '\Bitrix\Vote\ChannelTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Channel_Result exec()
	 * @method \Bitrix\Vote\EO_Channel fetchObject()
	 * @method \Bitrix\Vote\EO_Channel_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Channel_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Channel fetchObject()
	 * @method \Bitrix\Vote\EO_Channel_Collection fetchCollection()
	 */
	class EO_Channel_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Channel createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Channel_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Channel wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Channel_Collection wakeUpCollection($rows)
	 */
	class EO_Channel_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\ChannelGroupTable:vote/lib/channel.php:c9f0fe4672533048fc7f5a9029cde104 */
namespace Bitrix\Vote {
	/**
	 * EO_ChannelGroup
	 * @see \Bitrix\Vote\ChannelGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_ChannelGroup setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getChannelId()
	 * @method \Bitrix\Vote\EO_ChannelGroup setChannelId(\int|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \int remindActualChannelId()
	 * @method \int requireChannelId()
	 * @method \Bitrix\Vote\EO_ChannelGroup resetChannelId()
	 * @method \Bitrix\Vote\EO_ChannelGroup unsetChannelId()
	 * @method \int fillChannelId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Vote\EO_ChannelGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Vote\EO_ChannelGroup resetGroupId()
	 * @method \Bitrix\Vote\EO_ChannelGroup unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \string getPermission()
	 * @method \Bitrix\Vote\EO_ChannelGroup setPermission(\string|\Bitrix\Main\DB\SqlExpression $permission)
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \string remindActualPermission()
	 * @method \string requirePermission()
	 * @method \Bitrix\Vote\EO_ChannelGroup resetPermission()
	 * @method \Bitrix\Vote\EO_ChannelGroup unsetPermission()
	 * @method \string fillPermission()
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
	 * @method \Bitrix\Vote\EO_ChannelGroup set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_ChannelGroup reset($fieldName)
	 * @method \Bitrix\Vote\EO_ChannelGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_ChannelGroup wakeUp($data)
	 */
	class EO_ChannelGroup {
		/* @var \Bitrix\Vote\ChannelGroupTable */
		static public $dataClass = '\Bitrix\Vote\ChannelGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_ChannelGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getChannelIdList()
	 * @method \int[] fillChannelId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \string[] getPermissionList()
	 * @method \string[] fillPermission()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_ChannelGroup $object)
	 * @method bool has(\Bitrix\Vote\EO_ChannelGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_ChannelGroup getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_ChannelGroup[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_ChannelGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_ChannelGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_ChannelGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ChannelGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\ChannelGroupTable */
		static public $dataClass = '\Bitrix\Vote\ChannelGroupTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ChannelGroup_Result exec()
	 * @method \Bitrix\Vote\EO_ChannelGroup fetchObject()
	 * @method \Bitrix\Vote\EO_ChannelGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ChannelGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_ChannelGroup fetchObject()
	 * @method \Bitrix\Vote\EO_ChannelGroup_Collection fetchCollection()
	 */
	class EO_ChannelGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_ChannelGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_ChannelGroup_Collection createCollection()
	 * @method \Bitrix\Vote\EO_ChannelGroup wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_ChannelGroup_Collection wakeUpCollection($rows)
	 */
	class EO_ChannelGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\ChannelSiteTable:vote/lib/channel.php:c9f0fe4672533048fc7f5a9029cde104 */
namespace Bitrix\Vote {
	/**
	 * EO_ChannelSite
	 * @see \Bitrix\Vote\ChannelSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getChannelId()
	 * @method \Bitrix\Vote\EO_ChannelSite setChannelId(\int|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Vote\EO_ChannelSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
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
	 * @method \Bitrix\Vote\EO_ChannelSite set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_ChannelSite reset($fieldName)
	 * @method \Bitrix\Vote\EO_ChannelSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_ChannelSite wakeUp($data)
	 */
	class EO_ChannelSite {
		/* @var \Bitrix\Vote\ChannelSiteTable */
		static public $dataClass = '\Bitrix\Vote\ChannelSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_ChannelSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getChannelIdList()
	 * @method \string[] getSiteIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_ChannelSite $object)
	 * @method bool has(\Bitrix\Vote\EO_ChannelSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_ChannelSite getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_ChannelSite[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_ChannelSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_ChannelSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_ChannelSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ChannelSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\ChannelSiteTable */
		static public $dataClass = '\Bitrix\Vote\ChannelSiteTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ChannelSite_Result exec()
	 * @method \Bitrix\Vote\EO_ChannelSite fetchObject()
	 * @method \Bitrix\Vote\EO_ChannelSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ChannelSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_ChannelSite fetchObject()
	 * @method \Bitrix\Vote\EO_ChannelSite_Collection fetchCollection()
	 */
	class EO_ChannelSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_ChannelSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_ChannelSite_Collection createCollection()
	 * @method \Bitrix\Vote\EO_ChannelSite wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_ChannelSite_Collection wakeUpCollection($rows)
	 */
	class EO_ChannelSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\EventTable:vote/lib/event.php:a44ba1574dd3801cdc8b7e6c585fe3d8 */
namespace Bitrix\Vote {
	/**
	 * EO_Event
	 * @see \Bitrix\Vote\EventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Event setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getVoteId()
	 * @method \Bitrix\Vote\EO_Event setVoteId(\int|\Bitrix\Main\DB\SqlExpression $voteId)
	 * @method bool hasVoteId()
	 * @method bool isVoteIdFilled()
	 * @method bool isVoteIdChanged()
	 * @method \int remindActualVoteId()
	 * @method \int requireVoteId()
	 * @method \Bitrix\Vote\EO_Event resetVoteId()
	 * @method \Bitrix\Vote\EO_Event unsetVoteId()
	 * @method \int fillVoteId()
	 * @method \int getVoteUserId()
	 * @method \Bitrix\Vote\EO_Event setVoteUserId(\int|\Bitrix\Main\DB\SqlExpression $voteUserId)
	 * @method bool hasVoteUserId()
	 * @method bool isVoteUserIdFilled()
	 * @method bool isVoteUserIdChanged()
	 * @method \int remindActualVoteUserId()
	 * @method \int requireVoteUserId()
	 * @method \Bitrix\Vote\EO_Event resetVoteUserId()
	 * @method \Bitrix\Vote\EO_Event unsetVoteUserId()
	 * @method \int fillVoteUserId()
	 * @method \Bitrix\Main\Type\DateTime getDateVote()
	 * @method \Bitrix\Vote\EO_Event setDateVote(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateVote)
	 * @method bool hasDateVote()
	 * @method bool isDateVoteFilled()
	 * @method bool isDateVoteChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateVote()
	 * @method \Bitrix\Main\Type\DateTime requireDateVote()
	 * @method \Bitrix\Vote\EO_Event resetDateVote()
	 * @method \Bitrix\Vote\EO_Event unsetDateVote()
	 * @method \Bitrix\Main\Type\DateTime fillDateVote()
	 * @method \int getStatSessionId()
	 * @method \Bitrix\Vote\EO_Event setStatSessionId(\int|\Bitrix\Main\DB\SqlExpression $statSessionId)
	 * @method bool hasStatSessionId()
	 * @method bool isStatSessionIdFilled()
	 * @method bool isStatSessionIdChanged()
	 * @method \int remindActualStatSessionId()
	 * @method \int requireStatSessionId()
	 * @method \Bitrix\Vote\EO_Event resetStatSessionId()
	 * @method \Bitrix\Vote\EO_Event unsetStatSessionId()
	 * @method \int fillStatSessionId()
	 * @method \string getIp()
	 * @method \Bitrix\Vote\EO_Event setIp(\string|\Bitrix\Main\DB\SqlExpression $ip)
	 * @method bool hasIp()
	 * @method bool isIpFilled()
	 * @method bool isIpChanged()
	 * @method \string remindActualIp()
	 * @method \string requireIp()
	 * @method \Bitrix\Vote\EO_Event resetIp()
	 * @method \Bitrix\Vote\EO_Event unsetIp()
	 * @method \string fillIp()
	 * @method \boolean getValid()
	 * @method \Bitrix\Vote\EO_Event setValid(\boolean|\Bitrix\Main\DB\SqlExpression $valid)
	 * @method bool hasValid()
	 * @method bool isValidFilled()
	 * @method bool isValidChanged()
	 * @method \boolean remindActualValid()
	 * @method \boolean requireValid()
	 * @method \Bitrix\Vote\EO_Event resetValid()
	 * @method \Bitrix\Vote\EO_Event unsetValid()
	 * @method \boolean fillValid()
	 * @method \boolean getVisible()
	 * @method \Bitrix\Vote\EO_Event setVisible(\boolean|\Bitrix\Main\DB\SqlExpression $visible)
	 * @method bool hasVisible()
	 * @method bool isVisibleFilled()
	 * @method bool isVisibleChanged()
	 * @method \boolean remindActualVisible()
	 * @method \boolean requireVisible()
	 * @method \Bitrix\Vote\EO_Event resetVisible()
	 * @method \Bitrix\Vote\EO_Event unsetVisible()
	 * @method \boolean fillVisible()
	 * @method \Bitrix\Vote\EO_EventQuestion getQuestion()
	 * @method \Bitrix\Vote\EO_EventQuestion remindActualQuestion()
	 * @method \Bitrix\Vote\EO_EventQuestion requireQuestion()
	 * @method \Bitrix\Vote\EO_Event setQuestion(\Bitrix\Vote\EO_EventQuestion $object)
	 * @method \Bitrix\Vote\EO_Event resetQuestion()
	 * @method \Bitrix\Vote\EO_Event unsetQuestion()
	 * @method bool hasQuestion()
	 * @method bool isQuestionFilled()
	 * @method bool isQuestionChanged()
	 * @method \Bitrix\Vote\EO_EventQuestion fillQuestion()
	 * @method \Bitrix\Vote\EO_User getUser()
	 * @method \Bitrix\Vote\EO_User remindActualUser()
	 * @method \Bitrix\Vote\EO_User requireUser()
	 * @method \Bitrix\Vote\EO_Event setUser(\Bitrix\Vote\EO_User $object)
	 * @method \Bitrix\Vote\EO_Event resetUser()
	 * @method \Bitrix\Vote\EO_Event unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Vote\EO_User fillUser()
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
	 * @method \Bitrix\Vote\EO_Event set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Event reset($fieldName)
	 * @method \Bitrix\Vote\EO_Event unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Event wakeUp($data)
	 */
	class EO_Event {
		/* @var \Bitrix\Vote\EventTable */
		static public $dataClass = '\Bitrix\Vote\EventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Event_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getVoteIdList()
	 * @method \int[] fillVoteId()
	 * @method \int[] getVoteUserIdList()
	 * @method \int[] fillVoteUserId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateVoteList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateVote()
	 * @method \int[] getStatSessionIdList()
	 * @method \int[] fillStatSessionId()
	 * @method \string[] getIpList()
	 * @method \string[] fillIp()
	 * @method \boolean[] getValidList()
	 * @method \boolean[] fillValid()
	 * @method \boolean[] getVisibleList()
	 * @method \boolean[] fillVisible()
	 * @method \Bitrix\Vote\EO_EventQuestion[] getQuestionList()
	 * @method \Bitrix\Vote\EO_Event_Collection getQuestionCollection()
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection fillQuestion()
	 * @method \Bitrix\Vote\EO_User[] getUserList()
	 * @method \Bitrix\Vote\EO_Event_Collection getUserCollection()
	 * @method \Bitrix\Vote\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Event $object)
	 * @method bool has(\Bitrix\Vote\EO_Event $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Event getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Event[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Event $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Event_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Event current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Event_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\EventTable */
		static public $dataClass = '\Bitrix\Vote\EventTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Event_Result exec()
	 * @method \Bitrix\Vote\EO_Event fetchObject()
	 * @method \Bitrix\Vote\EO_Event_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Event_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Event fetchObject()
	 * @method \Bitrix\Vote\EO_Event_Collection fetchCollection()
	 */
	class EO_Event_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Event createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Event_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Event wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EO_Event_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\EventQuestionTable:vote/lib/event.php:a44ba1574dd3801cdc8b7e6c585fe3d8 */
namespace Bitrix\Vote {
	/**
	 * EO_EventQuestion
	 * @see \Bitrix\Vote\EventQuestionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_EventQuestion setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventId()
	 * @method \Bitrix\Vote\EO_EventQuestion setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Vote\EO_EventQuestion resetEventId()
	 * @method \Bitrix\Vote\EO_EventQuestion unsetEventId()
	 * @method \int fillEventId()
	 * @method \int getQuestionId()
	 * @method \Bitrix\Vote\EO_EventQuestion setQuestionId(\int|\Bitrix\Main\DB\SqlExpression $questionId)
	 * @method bool hasQuestionId()
	 * @method bool isQuestionIdFilled()
	 * @method bool isQuestionIdChanged()
	 * @method \int remindActualQuestionId()
	 * @method \int requireQuestionId()
	 * @method \Bitrix\Vote\EO_EventQuestion resetQuestionId()
	 * @method \Bitrix\Vote\EO_EventQuestion unsetQuestionId()
	 * @method \int fillQuestionId()
	 * @method \Bitrix\Vote\EO_Event getVote()
	 * @method \Bitrix\Vote\EO_Event remindActualVote()
	 * @method \Bitrix\Vote\EO_Event requireVote()
	 * @method \Bitrix\Vote\EO_EventQuestion setVote(\Bitrix\Vote\EO_Event $object)
	 * @method \Bitrix\Vote\EO_EventQuestion resetVote()
	 * @method \Bitrix\Vote\EO_EventQuestion unsetVote()
	 * @method bool hasVote()
	 * @method bool isVoteFilled()
	 * @method bool isVoteChanged()
	 * @method \Bitrix\Vote\EO_Event fillVote()
	 * @method \Bitrix\Vote\EO_EventAnswer getAnswer()
	 * @method \Bitrix\Vote\EO_EventAnswer remindActualAnswer()
	 * @method \Bitrix\Vote\EO_EventAnswer requireAnswer()
	 * @method \Bitrix\Vote\EO_EventQuestion setAnswer(\Bitrix\Vote\EO_EventAnswer $object)
	 * @method \Bitrix\Vote\EO_EventQuestion resetAnswer()
	 * @method \Bitrix\Vote\EO_EventQuestion unsetAnswer()
	 * @method bool hasAnswer()
	 * @method bool isAnswerFilled()
	 * @method bool isAnswerChanged()
	 * @method \Bitrix\Vote\EO_EventAnswer fillAnswer()
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
	 * @method \Bitrix\Vote\EO_EventQuestion set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_EventQuestion reset($fieldName)
	 * @method \Bitrix\Vote\EO_EventQuestion unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_EventQuestion wakeUp($data)
	 */
	class EO_EventQuestion {
		/* @var \Bitrix\Vote\EventQuestionTable */
		static public $dataClass = '\Bitrix\Vote\EventQuestionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_EventQuestion_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \int[] getQuestionIdList()
	 * @method \int[] fillQuestionId()
	 * @method \Bitrix\Vote\EO_Event[] getVoteList()
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection getVoteCollection()
	 * @method \Bitrix\Vote\EO_Event_Collection fillVote()
	 * @method \Bitrix\Vote\EO_EventAnswer[] getAnswerList()
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection getAnswerCollection()
	 * @method \Bitrix\Vote\EO_EventAnswer_Collection fillAnswer()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_EventQuestion $object)
	 * @method bool has(\Bitrix\Vote\EO_EventQuestion $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_EventQuestion getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_EventQuestion[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_EventQuestion $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_EventQuestion_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_EventQuestion current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventQuestion_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\EventQuestionTable */
		static public $dataClass = '\Bitrix\Vote\EventQuestionTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventQuestion_Result exec()
	 * @method \Bitrix\Vote\EO_EventQuestion fetchObject()
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventQuestion_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_EventQuestion fetchObject()
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection fetchCollection()
	 */
	class EO_EventQuestion_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_EventQuestion createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection createCollection()
	 * @method \Bitrix\Vote\EO_EventQuestion wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_EventQuestion_Collection wakeUpCollection($rows)
	 */
	class EO_EventQuestion_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\EventAnswerTable:vote/lib/event.php:a44ba1574dd3801cdc8b7e6c585fe3d8 */
namespace Bitrix\Vote {
	/**
	 * EO_EventAnswer
	 * @see \Bitrix\Vote\EventAnswerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_EventAnswer setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventQuestionId()
	 * @method \Bitrix\Vote\EO_EventAnswer setEventQuestionId(\int|\Bitrix\Main\DB\SqlExpression $eventQuestionId)
	 * @method bool hasEventQuestionId()
	 * @method bool isEventQuestionIdFilled()
	 * @method bool isEventQuestionIdChanged()
	 * @method \int remindActualEventQuestionId()
	 * @method \int requireEventQuestionId()
	 * @method \Bitrix\Vote\EO_EventAnswer resetEventQuestionId()
	 * @method \Bitrix\Vote\EO_EventAnswer unsetEventQuestionId()
	 * @method \int fillEventQuestionId()
	 * @method \int getAnswerId()
	 * @method \Bitrix\Vote\EO_EventAnswer setAnswerId(\int|\Bitrix\Main\DB\SqlExpression $answerId)
	 * @method bool hasAnswerId()
	 * @method bool isAnswerIdFilled()
	 * @method bool isAnswerIdChanged()
	 * @method \int remindActualAnswerId()
	 * @method \int requireAnswerId()
	 * @method \Bitrix\Vote\EO_EventAnswer resetAnswerId()
	 * @method \Bitrix\Vote\EO_EventAnswer unsetAnswerId()
	 * @method \int fillAnswerId()
	 * @method \string getMessage()
	 * @method \Bitrix\Vote\EO_EventAnswer setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Vote\EO_EventAnswer resetMessage()
	 * @method \Bitrix\Vote\EO_EventAnswer unsetMessage()
	 * @method \string fillMessage()
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
	 * @method \Bitrix\Vote\EO_EventAnswer set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_EventAnswer reset($fieldName)
	 * @method \Bitrix\Vote\EO_EventAnswer unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_EventAnswer wakeUp($data)
	 */
	class EO_EventAnswer {
		/* @var \Bitrix\Vote\EventAnswerTable */
		static public $dataClass = '\Bitrix\Vote\EventAnswerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_EventAnswer_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventQuestionIdList()
	 * @method \int[] fillEventQuestionId()
	 * @method \int[] getAnswerIdList()
	 * @method \int[] fillAnswerId()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_EventAnswer $object)
	 * @method bool has(\Bitrix\Vote\EO_EventAnswer $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_EventAnswer getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_EventAnswer[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_EventAnswer $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_EventAnswer_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_EventAnswer current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventAnswer_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\EventAnswerTable */
		static public $dataClass = '\Bitrix\Vote\EventAnswerTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventAnswer_Result exec()
	 * @method \Bitrix\Vote\EO_EventAnswer fetchObject()
	 * @method \Bitrix\Vote\EO_EventAnswer_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventAnswer_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_EventAnswer fetchObject()
	 * @method \Bitrix\Vote\EO_EventAnswer_Collection fetchCollection()
	 */
	class EO_EventAnswer_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_EventAnswer createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_EventAnswer_Collection createCollection()
	 * @method \Bitrix\Vote\EO_EventAnswer wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_EventAnswer_Collection wakeUpCollection($rows)
	 */
	class EO_EventAnswer_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\QuestionTable:vote/lib/question.php:3c1ea72b512a9b27ba0218299cdeb3d1 */
namespace Bitrix\Vote {
	/**
	 * EO_Question
	 * @see \Bitrix\Vote\QuestionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Question setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getActive()
	 * @method \Bitrix\Vote\EO_Question setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Vote\EO_Question resetActive()
	 * @method \Bitrix\Vote\EO_Question unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Vote\EO_Question setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Vote\EO_Question resetTimestampX()
	 * @method \Bitrix\Vote\EO_Question unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getVoteId()
	 * @method \Bitrix\Vote\EO_Question setVoteId(\int|\Bitrix\Main\DB\SqlExpression $voteId)
	 * @method bool hasVoteId()
	 * @method bool isVoteIdFilled()
	 * @method bool isVoteIdChanged()
	 * @method \int remindActualVoteId()
	 * @method \int requireVoteId()
	 * @method \Bitrix\Vote\EO_Question resetVoteId()
	 * @method \Bitrix\Vote\EO_Question unsetVoteId()
	 * @method \int fillVoteId()
	 * @method \int getCSort()
	 * @method \Bitrix\Vote\EO_Question setCSort(\int|\Bitrix\Main\DB\SqlExpression $cSort)
	 * @method bool hasCSort()
	 * @method bool isCSortFilled()
	 * @method bool isCSortChanged()
	 * @method \int remindActualCSort()
	 * @method \int requireCSort()
	 * @method \Bitrix\Vote\EO_Question resetCSort()
	 * @method \Bitrix\Vote\EO_Question unsetCSort()
	 * @method \int fillCSort()
	 * @method \int getCounter()
	 * @method \Bitrix\Vote\EO_Question setCounter(\int|\Bitrix\Main\DB\SqlExpression $counter)
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \int remindActualCounter()
	 * @method \int requireCounter()
	 * @method \Bitrix\Vote\EO_Question resetCounter()
	 * @method \Bitrix\Vote\EO_Question unsetCounter()
	 * @method \int fillCounter()
	 * @method \string getQuestion()
	 * @method \Bitrix\Vote\EO_Question setQuestion(\string|\Bitrix\Main\DB\SqlExpression $question)
	 * @method bool hasQuestion()
	 * @method bool isQuestionFilled()
	 * @method bool isQuestionChanged()
	 * @method \string remindActualQuestion()
	 * @method \string requireQuestion()
	 * @method \Bitrix\Vote\EO_Question resetQuestion()
	 * @method \Bitrix\Vote\EO_Question unsetQuestion()
	 * @method \string fillQuestion()
	 * @method \string getQuestionType()
	 * @method \Bitrix\Vote\EO_Question setQuestionType(\string|\Bitrix\Main\DB\SqlExpression $questionType)
	 * @method bool hasQuestionType()
	 * @method bool isQuestionTypeFilled()
	 * @method bool isQuestionTypeChanged()
	 * @method \string remindActualQuestionType()
	 * @method \string requireQuestionType()
	 * @method \Bitrix\Vote\EO_Question resetQuestionType()
	 * @method \Bitrix\Vote\EO_Question unsetQuestionType()
	 * @method \string fillQuestionType()
	 * @method \int getImageId()
	 * @method \Bitrix\Vote\EO_Question setImageId(\int|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \int remindActualImageId()
	 * @method \int requireImageId()
	 * @method \Bitrix\Vote\EO_Question resetImageId()
	 * @method \Bitrix\Vote\EO_Question unsetImageId()
	 * @method \int fillImageId()
	 * @method \Bitrix\Main\EO_File getImage()
	 * @method \Bitrix\Main\EO_File remindActualImage()
	 * @method \Bitrix\Main\EO_File requireImage()
	 * @method \Bitrix\Vote\EO_Question setImage(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Vote\EO_Question resetImage()
	 * @method \Bitrix\Vote\EO_Question unsetImage()
	 * @method bool hasImage()
	 * @method bool isImageFilled()
	 * @method bool isImageChanged()
	 * @method \Bitrix\Main\EO_File fillImage()
	 * @method \boolean getDiagram()
	 * @method \Bitrix\Vote\EO_Question setDiagram(\boolean|\Bitrix\Main\DB\SqlExpression $diagram)
	 * @method bool hasDiagram()
	 * @method bool isDiagramFilled()
	 * @method bool isDiagramChanged()
	 * @method \boolean remindActualDiagram()
	 * @method \boolean requireDiagram()
	 * @method \Bitrix\Vote\EO_Question resetDiagram()
	 * @method \Bitrix\Vote\EO_Question unsetDiagram()
	 * @method \boolean fillDiagram()
	 * @method \string getDiagramType()
	 * @method \Bitrix\Vote\EO_Question setDiagramType(\string|\Bitrix\Main\DB\SqlExpression $diagramType)
	 * @method bool hasDiagramType()
	 * @method bool isDiagramTypeFilled()
	 * @method bool isDiagramTypeChanged()
	 * @method \string remindActualDiagramType()
	 * @method \string requireDiagramType()
	 * @method \Bitrix\Vote\EO_Question resetDiagramType()
	 * @method \Bitrix\Vote\EO_Question unsetDiagramType()
	 * @method \string fillDiagramType()
	 * @method \boolean getRequired()
	 * @method \Bitrix\Vote\EO_Question setRequired(\boolean|\Bitrix\Main\DB\SqlExpression $required)
	 * @method bool hasRequired()
	 * @method bool isRequiredFilled()
	 * @method bool isRequiredChanged()
	 * @method \boolean remindActualRequired()
	 * @method \boolean requireRequired()
	 * @method \Bitrix\Vote\EO_Question resetRequired()
	 * @method \Bitrix\Vote\EO_Question unsetRequired()
	 * @method \boolean fillRequired()
	 * @method \string getFieldType()
	 * @method \Bitrix\Vote\EO_Question setFieldType(\string|\Bitrix\Main\DB\SqlExpression $fieldType)
	 * @method bool hasFieldType()
	 * @method bool isFieldTypeFilled()
	 * @method bool isFieldTypeChanged()
	 * @method \string remindActualFieldType()
	 * @method \string requireFieldType()
	 * @method \Bitrix\Vote\EO_Question resetFieldType()
	 * @method \Bitrix\Vote\EO_Question unsetFieldType()
	 * @method \string fillFieldType()
	 * @method \Bitrix\Vote\EO_Vote getVote()
	 * @method \Bitrix\Vote\EO_Vote remindActualVote()
	 * @method \Bitrix\Vote\EO_Vote requireVote()
	 * @method \Bitrix\Vote\EO_Question setVote(\Bitrix\Vote\EO_Vote $object)
	 * @method \Bitrix\Vote\EO_Question resetVote()
	 * @method \Bitrix\Vote\EO_Question unsetVote()
	 * @method bool hasVote()
	 * @method bool isVoteFilled()
	 * @method bool isVoteChanged()
	 * @method \Bitrix\Vote\EO_Vote fillVote()
	 * @method \Bitrix\Vote\EO_Answer getAnswer()
	 * @method \Bitrix\Vote\EO_Answer remindActualAnswer()
	 * @method \Bitrix\Vote\EO_Answer requireAnswer()
	 * @method \Bitrix\Vote\EO_Question setAnswer(\Bitrix\Vote\EO_Answer $object)
	 * @method \Bitrix\Vote\EO_Question resetAnswer()
	 * @method \Bitrix\Vote\EO_Question unsetAnswer()
	 * @method bool hasAnswer()
	 * @method bool isAnswerFilled()
	 * @method bool isAnswerChanged()
	 * @method \Bitrix\Vote\EO_Answer fillAnswer()
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
	 * @method \Bitrix\Vote\EO_Question set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Question reset($fieldName)
	 * @method \Bitrix\Vote\EO_Question unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Question wakeUp($data)
	 */
	class EO_Question {
		/* @var \Bitrix\Vote\QuestionTable */
		static public $dataClass = '\Bitrix\Vote\QuestionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Question_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getVoteIdList()
	 * @method \int[] fillVoteId()
	 * @method \int[] getCSortList()
	 * @method \int[] fillCSort()
	 * @method \int[] getCounterList()
	 * @method \int[] fillCounter()
	 * @method \string[] getQuestionList()
	 * @method \string[] fillQuestion()
	 * @method \string[] getQuestionTypeList()
	 * @method \string[] fillQuestionType()
	 * @method \int[] getImageIdList()
	 * @method \int[] fillImageId()
	 * @method \Bitrix\Main\EO_File[] getImageList()
	 * @method \Bitrix\Vote\EO_Question_Collection getImageCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillImage()
	 * @method \boolean[] getDiagramList()
	 * @method \boolean[] fillDiagram()
	 * @method \string[] getDiagramTypeList()
	 * @method \string[] fillDiagramType()
	 * @method \boolean[] getRequiredList()
	 * @method \boolean[] fillRequired()
	 * @method \string[] getFieldTypeList()
	 * @method \string[] fillFieldType()
	 * @method \Bitrix\Vote\EO_Vote[] getVoteList()
	 * @method \Bitrix\Vote\EO_Question_Collection getVoteCollection()
	 * @method \Bitrix\Vote\EO_Vote_Collection fillVote()
	 * @method \Bitrix\Vote\EO_Answer[] getAnswerList()
	 * @method \Bitrix\Vote\EO_Question_Collection getAnswerCollection()
	 * @method \Bitrix\Vote\EO_Answer_Collection fillAnswer()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Question $object)
	 * @method bool has(\Bitrix\Vote\EO_Question $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Question getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Question[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Question $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Question_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Question current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Question_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\QuestionTable */
		static public $dataClass = '\Bitrix\Vote\QuestionTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Question_Result exec()
	 * @method \Bitrix\Vote\EO_Question fetchObject()
	 * @method \Bitrix\Vote\EO_Question_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Question_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Question fetchObject()
	 * @method \Bitrix\Vote\EO_Question_Collection fetchCollection()
	 */
	class EO_Question_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Question createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Question_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Question wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Question_Collection wakeUpCollection($rows)
	 */
	class EO_Question_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\UserTable:vote/lib/user.php:b8b421cc2fa4cca9a39f715c0baf71e3 */
namespace Bitrix\Vote {
	/**
	 * EO_User
	 * @see \Bitrix\Vote\UserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_User setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCookieId()
	 * @method \Bitrix\Vote\EO_User setCookieId(\int|\Bitrix\Main\DB\SqlExpression $cookieId)
	 * @method bool hasCookieId()
	 * @method bool isCookieIdFilled()
	 * @method bool isCookieIdChanged()
	 * @method \int remindActualCookieId()
	 * @method \int requireCookieId()
	 * @method \Bitrix\Vote\EO_User resetCookieId()
	 * @method \Bitrix\Vote\EO_User unsetCookieId()
	 * @method \int fillCookieId()
	 * @method \int getAuthUserId()
	 * @method \Bitrix\Vote\EO_User setAuthUserId(\int|\Bitrix\Main\DB\SqlExpression $authUserId)
	 * @method bool hasAuthUserId()
	 * @method bool isAuthUserIdFilled()
	 * @method bool isAuthUserIdChanged()
	 * @method \int remindActualAuthUserId()
	 * @method \int requireAuthUserId()
	 * @method \Bitrix\Vote\EO_User resetAuthUserId()
	 * @method \Bitrix\Vote\EO_User unsetAuthUserId()
	 * @method \int fillAuthUserId()
	 * @method \int getCounter()
	 * @method \Bitrix\Vote\EO_User setCounter(\int|\Bitrix\Main\DB\SqlExpression $counter)
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \int remindActualCounter()
	 * @method \int requireCounter()
	 * @method \Bitrix\Vote\EO_User resetCounter()
	 * @method \Bitrix\Vote\EO_User unsetCounter()
	 * @method \int fillCounter()
	 * @method \Bitrix\Main\Type\DateTime getDateFirst()
	 * @method \Bitrix\Vote\EO_User setDateFirst(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFirst)
	 * @method bool hasDateFirst()
	 * @method bool isDateFirstFilled()
	 * @method bool isDateFirstChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFirst()
	 * @method \Bitrix\Main\Type\DateTime requireDateFirst()
	 * @method \Bitrix\Vote\EO_User resetDateFirst()
	 * @method \Bitrix\Vote\EO_User unsetDateFirst()
	 * @method \Bitrix\Main\Type\DateTime fillDateFirst()
	 * @method \Bitrix\Main\Type\DateTime getDateLast()
	 * @method \Bitrix\Vote\EO_User setDateLast(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateLast)
	 * @method bool hasDateLast()
	 * @method bool isDateLastFilled()
	 * @method bool isDateLastChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateLast()
	 * @method \Bitrix\Main\Type\DateTime requireDateLast()
	 * @method \Bitrix\Vote\EO_User resetDateLast()
	 * @method \Bitrix\Vote\EO_User unsetDateLast()
	 * @method \Bitrix\Main\Type\DateTime fillDateLast()
	 * @method \string getLastIp()
	 * @method \Bitrix\Vote\EO_User setLastIp(\string|\Bitrix\Main\DB\SqlExpression $lastIp)
	 * @method bool hasLastIp()
	 * @method bool isLastIpFilled()
	 * @method bool isLastIpChanged()
	 * @method \string remindActualLastIp()
	 * @method \string requireLastIp()
	 * @method \Bitrix\Vote\EO_User resetLastIp()
	 * @method \Bitrix\Vote\EO_User unsetLastIp()
	 * @method \string fillLastIp()
	 * @method \int getStatGuestId()
	 * @method \Bitrix\Vote\EO_User setStatGuestId(\int|\Bitrix\Main\DB\SqlExpression $statGuestId)
	 * @method bool hasStatGuestId()
	 * @method bool isStatGuestIdFilled()
	 * @method bool isStatGuestIdChanged()
	 * @method \int remindActualStatGuestId()
	 * @method \int requireStatGuestId()
	 * @method \Bitrix\Vote\EO_User resetStatGuestId()
	 * @method \Bitrix\Vote\EO_User unsetStatGuestId()
	 * @method \int fillStatGuestId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Vote\EO_User setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Vote\EO_User resetUser()
	 * @method \Bitrix\Vote\EO_User unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
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
	 * @method \Bitrix\Vote\EO_User set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_User reset($fieldName)
	 * @method \Bitrix\Vote\EO_User unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_User wakeUp($data)
	 */
	class EO_User {
		/* @var \Bitrix\Vote\UserTable */
		static public $dataClass = '\Bitrix\Vote\UserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_User_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCookieIdList()
	 * @method \int[] fillCookieId()
	 * @method \int[] getAuthUserIdList()
	 * @method \int[] fillAuthUserId()
	 * @method \int[] getCounterList()
	 * @method \int[] fillCounter()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFirstList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFirst()
	 * @method \Bitrix\Main\Type\DateTime[] getDateLastList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateLast()
	 * @method \string[] getLastIpList()
	 * @method \string[] fillLastIp()
	 * @method \int[] getStatGuestIdList()
	 * @method \int[] fillStatGuestId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Vote\EO_User_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_User $object)
	 * @method bool has(\Bitrix\Vote\EO_User $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_User getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_User[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_User $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_User_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_User current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_User_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\UserTable */
		static public $dataClass = '\Bitrix\Vote\UserTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_User_Result exec()
	 * @method \Bitrix\Vote\EO_User fetchObject()
	 * @method \Bitrix\Vote\EO_User_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_User_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_User fetchObject()
	 * @method \Bitrix\Vote\EO_User_Collection fetchCollection()
	 */
	class EO_User_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_User createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_User_Collection createCollection()
	 * @method \Bitrix\Vote\EO_User wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_User_Collection wakeUpCollection($rows)
	 */
	class EO_User_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Vote\VoteTable:vote/lib/vote.php:080364331253a74dd0c97de9e550871f */
namespace Bitrix\Vote {
	/**
	 * EO_Vote
	 * @see \Bitrix\Vote\VoteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Vote\EO_Vote setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getChannelId()
	 * @method \Bitrix\Vote\EO_Vote setChannelId(\int|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \int remindActualChannelId()
	 * @method \int requireChannelId()
	 * @method \Bitrix\Vote\EO_Vote resetChannelId()
	 * @method \Bitrix\Vote\EO_Vote unsetChannelId()
	 * @method \int fillChannelId()
	 * @method \int getCSort()
	 * @method \Bitrix\Vote\EO_Vote setCSort(\int|\Bitrix\Main\DB\SqlExpression $cSort)
	 * @method bool hasCSort()
	 * @method bool isCSortFilled()
	 * @method bool isCSortChanged()
	 * @method \int remindActualCSort()
	 * @method \int requireCSort()
	 * @method \Bitrix\Vote\EO_Vote resetCSort()
	 * @method \Bitrix\Vote\EO_Vote unsetCSort()
	 * @method \int fillCSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Vote\EO_Vote setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Vote\EO_Vote resetActive()
	 * @method \Bitrix\Vote\EO_Vote unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getAnonymity()
	 * @method \Bitrix\Vote\EO_Vote setAnonymity(\int|\Bitrix\Main\DB\SqlExpression $anonymity)
	 * @method bool hasAnonymity()
	 * @method bool isAnonymityFilled()
	 * @method bool isAnonymityChanged()
	 * @method \int remindActualAnonymity()
	 * @method \int requireAnonymity()
	 * @method \Bitrix\Vote\EO_Vote resetAnonymity()
	 * @method \Bitrix\Vote\EO_Vote unsetAnonymity()
	 * @method \int fillAnonymity()
	 * @method \string getNotify()
	 * @method \Bitrix\Vote\EO_Vote setNotify(\string|\Bitrix\Main\DB\SqlExpression $notify)
	 * @method bool hasNotify()
	 * @method bool isNotifyFilled()
	 * @method bool isNotifyChanged()
	 * @method \string remindActualNotify()
	 * @method \string requireNotify()
	 * @method \Bitrix\Vote\EO_Vote resetNotify()
	 * @method \Bitrix\Vote\EO_Vote unsetNotify()
	 * @method \string fillNotify()
	 * @method \int getAuthorId()
	 * @method \Bitrix\Vote\EO_Vote setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\Vote\EO_Vote resetAuthorId()
	 * @method \Bitrix\Vote\EO_Vote unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \Bitrix\Main\EO_User getAuthor()
	 * @method \Bitrix\Main\EO_User remindActualAuthor()
	 * @method \Bitrix\Main\EO_User requireAuthor()
	 * @method \Bitrix\Vote\EO_Vote setAuthor(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Vote\EO_Vote resetAuthor()
	 * @method \Bitrix\Vote\EO_Vote unsetAuthor()
	 * @method bool hasAuthor()
	 * @method bool isAuthorFilled()
	 * @method bool isAuthorChanged()
	 * @method \Bitrix\Main\EO_User fillAuthor()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Vote\EO_Vote setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Vote\EO_Vote resetTimestampX()
	 * @method \Bitrix\Vote\EO_Vote unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime getDateStart()
	 * @method \Bitrix\Vote\EO_Vote setDateStart(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateStart)
	 * @method bool hasDateStart()
	 * @method bool isDateStartFilled()
	 * @method bool isDateStartChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateStart()
	 * @method \Bitrix\Main\Type\DateTime requireDateStart()
	 * @method \Bitrix\Vote\EO_Vote resetDateStart()
	 * @method \Bitrix\Vote\EO_Vote unsetDateStart()
	 * @method \Bitrix\Main\Type\DateTime fillDateStart()
	 * @method \Bitrix\Main\Type\DateTime getDateEnd()
	 * @method \Bitrix\Vote\EO_Vote setDateEnd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateEnd)
	 * @method bool hasDateEnd()
	 * @method bool isDateEndFilled()
	 * @method bool isDateEndChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateEnd()
	 * @method \Bitrix\Main\Type\DateTime requireDateEnd()
	 * @method \Bitrix\Vote\EO_Vote resetDateEnd()
	 * @method \Bitrix\Vote\EO_Vote unsetDateEnd()
	 * @method \Bitrix\Main\Type\DateTime fillDateEnd()
	 * @method \string getUrl()
	 * @method \Bitrix\Vote\EO_Vote setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Vote\EO_Vote resetUrl()
	 * @method \Bitrix\Vote\EO_Vote unsetUrl()
	 * @method \string fillUrl()
	 * @method \int getCounter()
	 * @method \Bitrix\Vote\EO_Vote setCounter(\int|\Bitrix\Main\DB\SqlExpression $counter)
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \int remindActualCounter()
	 * @method \int requireCounter()
	 * @method \Bitrix\Vote\EO_Vote resetCounter()
	 * @method \Bitrix\Vote\EO_Vote unsetCounter()
	 * @method \int fillCounter()
	 * @method \string getTitle()
	 * @method \Bitrix\Vote\EO_Vote setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Vote\EO_Vote resetTitle()
	 * @method \Bitrix\Vote\EO_Vote unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDescription()
	 * @method \Bitrix\Vote\EO_Vote setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Vote\EO_Vote resetDescription()
	 * @method \Bitrix\Vote\EO_Vote unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getDescriptionType()
	 * @method \Bitrix\Vote\EO_Vote setDescriptionType(\string|\Bitrix\Main\DB\SqlExpression $descriptionType)
	 * @method bool hasDescriptionType()
	 * @method bool isDescriptionTypeFilled()
	 * @method bool isDescriptionTypeChanged()
	 * @method \string remindActualDescriptionType()
	 * @method \string requireDescriptionType()
	 * @method \Bitrix\Vote\EO_Vote resetDescriptionType()
	 * @method \Bitrix\Vote\EO_Vote unsetDescriptionType()
	 * @method \string fillDescriptionType()
	 * @method \int getImageId()
	 * @method \Bitrix\Vote\EO_Vote setImageId(\int|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \int remindActualImageId()
	 * @method \int requireImageId()
	 * @method \Bitrix\Vote\EO_Vote resetImageId()
	 * @method \Bitrix\Vote\EO_Vote unsetImageId()
	 * @method \int fillImageId()
	 * @method \Bitrix\Main\EO_File getImage()
	 * @method \Bitrix\Main\EO_File remindActualImage()
	 * @method \Bitrix\Main\EO_File requireImage()
	 * @method \Bitrix\Vote\EO_Vote setImage(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Vote\EO_Vote resetImage()
	 * @method \Bitrix\Vote\EO_Vote unsetImage()
	 * @method bool hasImage()
	 * @method bool isImageFilled()
	 * @method bool isImageChanged()
	 * @method \Bitrix\Main\EO_File fillImage()
	 * @method \string getEvent1()
	 * @method \Bitrix\Vote\EO_Vote setEvent1(\string|\Bitrix\Main\DB\SqlExpression $event1)
	 * @method bool hasEvent1()
	 * @method bool isEvent1Filled()
	 * @method bool isEvent1Changed()
	 * @method \string remindActualEvent1()
	 * @method \string requireEvent1()
	 * @method \Bitrix\Vote\EO_Vote resetEvent1()
	 * @method \Bitrix\Vote\EO_Vote unsetEvent1()
	 * @method \string fillEvent1()
	 * @method \string getEvent2()
	 * @method \Bitrix\Vote\EO_Vote setEvent2(\string|\Bitrix\Main\DB\SqlExpression $event2)
	 * @method bool hasEvent2()
	 * @method bool isEvent2Filled()
	 * @method bool isEvent2Changed()
	 * @method \string remindActualEvent2()
	 * @method \string requireEvent2()
	 * @method \Bitrix\Vote\EO_Vote resetEvent2()
	 * @method \Bitrix\Vote\EO_Vote unsetEvent2()
	 * @method \string fillEvent2()
	 * @method \string getEvent3()
	 * @method \Bitrix\Vote\EO_Vote setEvent3(\string|\Bitrix\Main\DB\SqlExpression $event3)
	 * @method bool hasEvent3()
	 * @method bool isEvent3Filled()
	 * @method bool isEvent3Changed()
	 * @method \string remindActualEvent3()
	 * @method \string requireEvent3()
	 * @method \Bitrix\Vote\EO_Vote resetEvent3()
	 * @method \Bitrix\Vote\EO_Vote unsetEvent3()
	 * @method \string fillEvent3()
	 * @method \int getUniqueType()
	 * @method \Bitrix\Vote\EO_Vote setUniqueType(\int|\Bitrix\Main\DB\SqlExpression $uniqueType)
	 * @method bool hasUniqueType()
	 * @method bool isUniqueTypeFilled()
	 * @method bool isUniqueTypeChanged()
	 * @method \int remindActualUniqueType()
	 * @method \int requireUniqueType()
	 * @method \Bitrix\Vote\EO_Vote resetUniqueType()
	 * @method \Bitrix\Vote\EO_Vote unsetUniqueType()
	 * @method \int fillUniqueType()
	 * @method \int getKeepIpSec()
	 * @method \Bitrix\Vote\EO_Vote setKeepIpSec(\int|\Bitrix\Main\DB\SqlExpression $keepIpSec)
	 * @method bool hasKeepIpSec()
	 * @method bool isKeepIpSecFilled()
	 * @method bool isKeepIpSecChanged()
	 * @method \int remindActualKeepIpSec()
	 * @method \int requireKeepIpSec()
	 * @method \Bitrix\Vote\EO_Vote resetKeepIpSec()
	 * @method \Bitrix\Vote\EO_Vote unsetKeepIpSec()
	 * @method \int fillKeepIpSec()
	 * @method \int getOptions()
	 * @method \Bitrix\Vote\EO_Vote setOptions(\int|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \int remindActualOptions()
	 * @method \int requireOptions()
	 * @method \Bitrix\Vote\EO_Vote resetOptions()
	 * @method \Bitrix\Vote\EO_Vote unsetOptions()
	 * @method \int fillOptions()
	 * @method \string getLamp()
	 * @method \string remindActualLamp()
	 * @method \string requireLamp()
	 * @method bool hasLamp()
	 * @method bool isLampFilled()
	 * @method \Bitrix\Vote\EO_Vote unsetLamp()
	 * @method \string fillLamp()
	 * @method \Bitrix\Vote\EO_Channel getChannel()
	 * @method \Bitrix\Vote\EO_Channel remindActualChannel()
	 * @method \Bitrix\Vote\EO_Channel requireChannel()
	 * @method \Bitrix\Vote\EO_Vote setChannel(\Bitrix\Vote\EO_Channel $object)
	 * @method \Bitrix\Vote\EO_Vote resetChannel()
	 * @method \Bitrix\Vote\EO_Vote unsetChannel()
	 * @method bool hasChannel()
	 * @method bool isChannelFilled()
	 * @method bool isChannelChanged()
	 * @method \Bitrix\Vote\EO_Channel fillChannel()
	 * @method \Bitrix\Vote\EO_Question getQuestion()
	 * @method \Bitrix\Vote\EO_Question remindActualQuestion()
	 * @method \Bitrix\Vote\EO_Question requireQuestion()
	 * @method \Bitrix\Vote\EO_Vote setQuestion(\Bitrix\Vote\EO_Question $object)
	 * @method \Bitrix\Vote\EO_Vote resetQuestion()
	 * @method \Bitrix\Vote\EO_Vote unsetQuestion()
	 * @method bool hasQuestion()
	 * @method bool isQuestionFilled()
	 * @method bool isQuestionChanged()
	 * @method \Bitrix\Vote\EO_Question fillQuestion()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Vote\EO_Vote setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Vote\EO_Vote resetUser()
	 * @method \Bitrix\Vote\EO_Vote unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
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
	 * @method \Bitrix\Vote\EO_Vote set($fieldName, $value)
	 * @method \Bitrix\Vote\EO_Vote reset($fieldName)
	 * @method \Bitrix\Vote\EO_Vote unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Vote\EO_Vote wakeUp($data)
	 */
	class EO_Vote {
		/* @var \Bitrix\Vote\VoteTable */
		static public $dataClass = '\Bitrix\Vote\VoteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Vote {
	/**
	 * EO_Vote_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getChannelIdList()
	 * @method \int[] fillChannelId()
	 * @method \int[] getCSortList()
	 * @method \int[] fillCSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getAnonymityList()
	 * @method \int[] fillAnonymity()
	 * @method \string[] getNotifyList()
	 * @method \string[] fillNotify()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \Bitrix\Main\EO_User[] getAuthorList()
	 * @method \Bitrix\Vote\EO_Vote_Collection getAuthorCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillAuthor()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime[] getDateStartList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateStart()
	 * @method \Bitrix\Main\Type\DateTime[] getDateEndList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateEnd()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \int[] getCounterList()
	 * @method \int[] fillCounter()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getDescriptionTypeList()
	 * @method \string[] fillDescriptionType()
	 * @method \int[] getImageIdList()
	 * @method \int[] fillImageId()
	 * @method \Bitrix\Main\EO_File[] getImageList()
	 * @method \Bitrix\Vote\EO_Vote_Collection getImageCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillImage()
	 * @method \string[] getEvent1List()
	 * @method \string[] fillEvent1()
	 * @method \string[] getEvent2List()
	 * @method \string[] fillEvent2()
	 * @method \string[] getEvent3List()
	 * @method \string[] fillEvent3()
	 * @method \int[] getUniqueTypeList()
	 * @method \int[] fillUniqueType()
	 * @method \int[] getKeepIpSecList()
	 * @method \int[] fillKeepIpSec()
	 * @method \int[] getOptionsList()
	 * @method \int[] fillOptions()
	 * @method \string[] getLampList()
	 * @method \string[] fillLamp()
	 * @method \Bitrix\Vote\EO_Channel[] getChannelList()
	 * @method \Bitrix\Vote\EO_Vote_Collection getChannelCollection()
	 * @method \Bitrix\Vote\EO_Channel_Collection fillChannel()
	 * @method \Bitrix\Vote\EO_Question[] getQuestionList()
	 * @method \Bitrix\Vote\EO_Vote_Collection getQuestionCollection()
	 * @method \Bitrix\Vote\EO_Question_Collection fillQuestion()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Vote\EO_Vote_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Vote\EO_Vote $object)
	 * @method bool has(\Bitrix\Vote\EO_Vote $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Vote getByPrimary($primary)
	 * @method \Bitrix\Vote\EO_Vote[] getAll()
	 * @method bool remove(\Bitrix\Vote\EO_Vote $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Vote\EO_Vote_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Vote\EO_Vote current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Vote_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Vote\VoteTable */
		static public $dataClass = '\Bitrix\Vote\VoteTable';
	}
}
namespace Bitrix\Vote {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Vote_Result exec()
	 * @method \Bitrix\Vote\EO_Vote fetchObject()
	 * @method \Bitrix\Vote\EO_Vote_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Vote_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Vote\EO_Vote fetchObject()
	 * @method \Bitrix\Vote\EO_Vote_Collection fetchCollection()
	 */
	class EO_Vote_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Vote\EO_Vote createObject($setDefaultValues = true)
	 * @method \Bitrix\Vote\EO_Vote_Collection createCollection()
	 * @method \Bitrix\Vote\EO_Vote wakeUpObject($row)
	 * @method \Bitrix\Vote\EO_Vote_Collection wakeUpCollection($rows)
	 */
	class EO_Vote_Entity extends \Bitrix\Main\ORM\Entity {}
}