<?php

/* ORMENTITYANNOTATION:Bitrix\MessageService\Internal\Entity\MessageTable:messageservice/lib/internal/entity/message.php:852ef731f8665374793118681043804c */
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_Message
	 * @see \Bitrix\MessageService\Internal\Entity\MessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetType()
	 * @method \string fillType()
	 * @method \string getSenderId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setSenderId(\string|\Bitrix\Main\DB\SqlExpression $senderId)
	 * @method bool hasSenderId()
	 * @method bool isSenderIdFilled()
	 * @method bool isSenderIdChanged()
	 * @method \string remindActualSenderId()
	 * @method \string requireSenderId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetSenderId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetSenderId()
	 * @method \string fillSenderId()
	 * @method \int getAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \Bitrix\Main\EO_User getAuthor()
	 * @method \Bitrix\Main\EO_User remindActualAuthor()
	 * @method \Bitrix\Main\EO_User requireAuthor()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setAuthor(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetAuthor()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetAuthor()
	 * @method bool hasAuthor()
	 * @method bool isAuthorFilled()
	 * @method bool isAuthorChanged()
	 * @method \Bitrix\Main\EO_User fillAuthor()
	 * @method \string getMessageFrom()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setMessageFrom(\string|\Bitrix\Main\DB\SqlExpression $messageFrom)
	 * @method bool hasMessageFrom()
	 * @method bool isMessageFromFilled()
	 * @method bool isMessageFromChanged()
	 * @method \string remindActualMessageFrom()
	 * @method \string requireMessageFrom()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetMessageFrom()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetMessageFrom()
	 * @method \string fillMessageFrom()
	 * @method \string getMessageTo()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setMessageTo(\string|\Bitrix\Main\DB\SqlExpression $messageTo)
	 * @method bool hasMessageTo()
	 * @method bool isMessageToFilled()
	 * @method bool isMessageToChanged()
	 * @method \string remindActualMessageTo()
	 * @method \string requireMessageTo()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetMessageTo()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetMessageTo()
	 * @method \string fillMessageTo()
	 * @method \string getMessageHeaders()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setMessageHeaders(\string|\Bitrix\Main\DB\SqlExpression $messageHeaders)
	 * @method bool hasMessageHeaders()
	 * @method bool isMessageHeadersFilled()
	 * @method bool isMessageHeadersChanged()
	 * @method \string remindActualMessageHeaders()
	 * @method \string requireMessageHeaders()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetMessageHeaders()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetMessageHeaders()
	 * @method \string fillMessageHeaders()
	 * @method \string getMessageBody()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setMessageBody(\string|\Bitrix\Main\DB\SqlExpression $messageBody)
	 * @method bool hasMessageBody()
	 * @method bool isMessageBodyFilled()
	 * @method bool isMessageBodyChanged()
	 * @method \string remindActualMessageBody()
	 * @method \string requireMessageBody()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetMessageBody()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetMessageBody()
	 * @method \string fillMessageBody()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetDateInsert()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setDateExec(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateExec)
	 * @method bool hasDateExec()
	 * @method bool isDateExecFilled()
	 * @method bool isDateExecChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateExec()
	 * @method \Bitrix\Main\Type\DateTime requireDateExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetDateExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetDateExec()
	 * @method \Bitrix\Main\Type\DateTime fillDateExec()
	 * @method \Bitrix\Main\Type\DateTime getNextExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setNextExec(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $nextExec)
	 * @method bool hasNextExec()
	 * @method bool isNextExecFilled()
	 * @method bool isNextExecChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualNextExec()
	 * @method \Bitrix\Main\Type\DateTime requireNextExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetNextExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetNextExec()
	 * @method \Bitrix\Main\Type\DateTime fillNextExec()
	 * @method \string getSuccessExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setSuccessExec(\string|\Bitrix\Main\DB\SqlExpression $successExec)
	 * @method bool hasSuccessExec()
	 * @method bool isSuccessExecFilled()
	 * @method bool isSuccessExecChanged()
	 * @method \string remindActualSuccessExec()
	 * @method \string requireSuccessExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetSuccessExec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetSuccessExec()
	 * @method \string fillSuccessExec()
	 * @method \string getExecError()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setExecError(\string|\Bitrix\Main\DB\SqlExpression $execError)
	 * @method bool hasExecError()
	 * @method bool isExecErrorFilled()
	 * @method bool isExecErrorChanged()
	 * @method \string remindActualExecError()
	 * @method \string requireExecError()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetExecError()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetExecError()
	 * @method \string fillExecError()
	 * @method \int getStatusId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setStatusId(\int|\Bitrix\Main\DB\SqlExpression $statusId)
	 * @method bool hasStatusId()
	 * @method bool isStatusIdFilled()
	 * @method bool isStatusIdChanged()
	 * @method \int remindActualStatusId()
	 * @method \int requireStatusId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetStatusId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetStatusId()
	 * @method \int fillStatusId()
	 * @method \string getExternalId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message resetExternalId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unsetExternalId()
	 * @method \string fillExternalId()
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
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message set($fieldName, $value)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message reset($fieldName)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message wakeUp($data)
	 */
	class EO_Message {
		/* @var \Bitrix\MessageService\Internal\Entity\MessageTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\MessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_Message_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getSenderIdList()
	 * @method \string[] fillSenderId()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \Bitrix\Main\EO_User[] getAuthorList()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message_Collection getAuthorCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillAuthor()
	 * @method \string[] getMessageFromList()
	 * @method \string[] fillMessageFrom()
	 * @method \string[] getMessageToList()
	 * @method \string[] fillMessageTo()
	 * @method \string[] getMessageHeadersList()
	 * @method \string[] fillMessageHeaders()
	 * @method \string[] getMessageBodyList()
	 * @method \string[] fillMessageBody()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateExecList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateExec()
	 * @method \Bitrix\Main\Type\DateTime[] getNextExecList()
	 * @method \Bitrix\Main\Type\DateTime[] fillNextExec()
	 * @method \string[] getSuccessExecList()
	 * @method \string[] fillSuccessExec()
	 * @method \string[] getExecErrorList()
	 * @method \string[] fillExecError()
	 * @method \int[] getStatusIdList()
	 * @method \int[] fillStatusId()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MessageService\Internal\Entity\EO_Message $object)
	 * @method bool has(\Bitrix\MessageService\Internal\Entity\EO_Message $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message getByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message[] getAll()
	 * @method bool remove(\Bitrix\MessageService\Internal\Entity\EO_Message $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Message_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MessageService\Internal\Entity\MessageTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\MessageTable';
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Message_Result exec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Message_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message_Collection fetchCollection()
	 */
	class EO_Message_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message createObject($setDefaultValues = true)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message_Collection createCollection()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message wakeUpObject($row)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_Message_Collection wakeUpCollection($rows)
	 */
	class EO_Message_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\MessageService\Internal\Entity\RestAppTable:messageservice/lib/internal/entity/restapp.php:a762677c5bf0ab63625013bd94259055 */
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_RestApp
	 * @see \Bitrix\MessageService\Internal\Entity\RestAppTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setAppId(\string|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \string remindActualAppId()
	 * @method \string requireAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetAppId()
	 * @method \string fillAppId()
	 * @method \string getCode()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetCode()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetCode()
	 * @method \string fillCode()
	 * @method \string getType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetType()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetType()
	 * @method \string fillType()
	 * @method \string getHandler()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setHandler(\string|\Bitrix\Main\DB\SqlExpression $handler)
	 * @method bool hasHandler()
	 * @method bool isHandlerFilled()
	 * @method bool isHandlerChanged()
	 * @method \string remindActualHandler()
	 * @method \string requireHandler()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetHandler()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetHandler()
	 * @method \string fillHandler()
	 * @method \Bitrix\Main\Type\DateTime getDateAdd()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setDateAdd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateAdd)
	 * @method bool hasDateAdd()
	 * @method bool isDateAddFilled()
	 * @method bool isDateAddChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateAdd()
	 * @method \Bitrix\Main\Type\DateTime requireDateAdd()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetDateAdd()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetDateAdd()
	 * @method \Bitrix\Main\Type\DateTime fillDateAdd()
	 * @method \int getAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetAuthorId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \Bitrix\Main\EO_User getAuthor()
	 * @method \Bitrix\Main\EO_User remindActualAuthor()
	 * @method \Bitrix\Main\EO_User requireAuthor()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp setAuthor(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp resetAuthor()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unsetAuthor()
	 * @method bool hasAuthor()
	 * @method bool isAuthorFilled()
	 * @method bool isAuthorChanged()
	 * @method \Bitrix\Main\EO_User fillAuthor()
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
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp set($fieldName, $value)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp reset($fieldName)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp wakeUp($data)
	 */
	class EO_RestApp {
		/* @var \Bitrix\MessageService\Internal\Entity\RestAppTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\RestAppTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_RestApp_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAppIdList()
	 * @method \string[] fillAppId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getHandlerList()
	 * @method \string[] fillHandler()
	 * @method \Bitrix\Main\Type\DateTime[] getDateAddList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateAdd()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \Bitrix\Main\EO_User[] getAuthorList()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection getAuthorCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillAuthor()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MessageService\Internal\Entity\EO_RestApp $object)
	 * @method bool has(\Bitrix\MessageService\Internal\Entity\EO_RestApp $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp getByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp[] getAll()
	 * @method bool remove(\Bitrix\MessageService\Internal\Entity\EO_RestApp $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_RestApp_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MessageService\Internal\Entity\RestAppTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\RestAppTable';
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RestApp_Result exec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RestApp_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection fetchCollection()
	 */
	class EO_RestApp_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp createObject($setDefaultValues = true)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection createCollection()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp wakeUpObject($row)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection wakeUpCollection($rows)
	 */
	class EO_RestApp_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\MessageService\Internal\Entity\RestAppLangTable:messageservice/lib/internal/entity/restapplang.php:7d7f0f72a619b6fa71ecb54b44a48386 */
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_RestAppLang
	 * @see \Bitrix\MessageService\Internal\Entity\RestAppLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang resetAppId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getLanguageId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang resetLanguageId()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang resetName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unsetName()
	 * @method \string fillName()
	 * @method \string getAppName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setAppName(\string|\Bitrix\Main\DB\SqlExpression $appName)
	 * @method bool hasAppName()
	 * @method bool isAppNameFilled()
	 * @method bool isAppNameChanged()
	 * @method \string remindActualAppName()
	 * @method \string requireAppName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang resetAppName()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unsetAppName()
	 * @method \string fillAppName()
	 * @method \string getDescription()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang resetDescription()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unsetDescription()
	 * @method \string fillDescription()
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
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang set($fieldName, $value)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang reset($fieldName)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestAppLang wakeUp($data)
	 */
	class EO_RestAppLang {
		/* @var \Bitrix\MessageService\Internal\Entity\RestAppLangTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\RestAppLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * EO_RestAppLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getAppNameList()
	 * @method \string[] fillAppName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MessageService\Internal\Entity\EO_RestAppLang $object)
	 * @method bool has(\Bitrix\MessageService\Internal\Entity\EO_RestAppLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang getByPrimary($primary)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang[] getAll()
	 * @method bool remove(\Bitrix\MessageService\Internal\Entity\EO_RestAppLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestAppLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_RestAppLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MessageService\Internal\Entity\RestAppLangTable */
		static public $dataClass = '\Bitrix\MessageService\Internal\Entity\RestAppLangTable';
	}
}
namespace Bitrix\MessageService\Internal\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RestAppLang_Result exec()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RestAppLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang fetchObject()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang_Collection fetchCollection()
	 */
	class EO_RestAppLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang createObject($setDefaultValues = true)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang_Collection createCollection()
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang wakeUpObject($row)
	 * @method \Bitrix\MessageService\Internal\Entity\EO_RestAppLang_Collection wakeUpCollection($rows)
	 */
	class EO_RestAppLang_Entity extends \Bitrix\Main\ORM\Entity {}
}