<?php

/* ORMENTITYANNOTATION:Bitrix\Mail\MailLogTable:mail/lib/maillog.php */
namespace Bitrix\Mail {
	/**
	 * EO_MailLog
	 * @see \Bitrix\Mail\MailLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\EO_MailLog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\EO_MailLog setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\EO_MailLog resetMailboxId()
	 * @method \Bitrix\Mail\EO_MailLog unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getFilterId()
	 * @method \Bitrix\Mail\EO_MailLog setFilterId(\int|\Bitrix\Main\DB\SqlExpression $filterId)
	 * @method bool hasFilterId()
	 * @method bool isFilterIdFilled()
	 * @method bool isFilterIdChanged()
	 * @method \int remindActualFilterId()
	 * @method \int requireFilterId()
	 * @method \Bitrix\Mail\EO_MailLog resetFilterId()
	 * @method \Bitrix\Mail\EO_MailLog unsetFilterId()
	 * @method \int fillFilterId()
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\EO_MailLog setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Mail\EO_MailLog resetMessageId()
	 * @method \Bitrix\Mail\EO_MailLog unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \string getLogType()
	 * @method \Bitrix\Mail\EO_MailLog setLogType(\string|\Bitrix\Main\DB\SqlExpression $logType)
	 * @method bool hasLogType()
	 * @method bool isLogTypeFilled()
	 * @method bool isLogTypeChanged()
	 * @method \string remindActualLogType()
	 * @method \string requireLogType()
	 * @method \Bitrix\Mail\EO_MailLog resetLogType()
	 * @method \Bitrix\Mail\EO_MailLog unsetLogType()
	 * @method \string fillLogType()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Mail\EO_MailLog setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Mail\EO_MailLog resetDateInsert()
	 * @method \Bitrix\Mail\EO_MailLog unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \boolean getStatusGood()
	 * @method \Bitrix\Mail\EO_MailLog setStatusGood(\boolean|\Bitrix\Main\DB\SqlExpression $statusGood)
	 * @method bool hasStatusGood()
	 * @method bool isStatusGoodFilled()
	 * @method bool isStatusGoodChanged()
	 * @method \boolean remindActualStatusGood()
	 * @method \boolean requireStatusGood()
	 * @method \Bitrix\Mail\EO_MailLog resetStatusGood()
	 * @method \Bitrix\Mail\EO_MailLog unsetStatusGood()
	 * @method \boolean fillStatusGood()
	 * @method \string getMessage()
	 * @method \Bitrix\Mail\EO_MailLog setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Mail\EO_MailLog resetMessage()
	 * @method \Bitrix\Mail\EO_MailLog unsetMessage()
	 * @method \string fillMessage()
	 * @method \Bitrix\Mail\EO_Mailbox getMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox remindActualMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox requireMailbox()
	 * @method \Bitrix\Mail\EO_MailLog setMailbox(\Bitrix\Mail\EO_Mailbox $object)
	 * @method \Bitrix\Mail\EO_MailLog resetMailbox()
	 * @method \Bitrix\Mail\EO_MailLog unsetMailbox()
	 * @method bool hasMailbox()
	 * @method bool isMailboxFilled()
	 * @method bool isMailboxChanged()
	 * @method \Bitrix\Mail\EO_Mailbox fillMailbox()
	 * @method \Bitrix\Mail\EO_MailFilter getFilter()
	 * @method \Bitrix\Mail\EO_MailFilter remindActualFilter()
	 * @method \Bitrix\Mail\EO_MailFilter requireFilter()
	 * @method \Bitrix\Mail\EO_MailLog setFilter(\Bitrix\Mail\EO_MailFilter $object)
	 * @method \Bitrix\Mail\EO_MailLog resetFilter()
	 * @method \Bitrix\Mail\EO_MailLog unsetFilter()
	 * @method bool hasFilter()
	 * @method bool isFilterFilled()
	 * @method bool isFilterChanged()
	 * @method \Bitrix\Mail\EO_MailFilter fillFilter()
	 * @method \Bitrix\Mail\EO_MailMessage getMailMessage()
	 * @method \Bitrix\Mail\EO_MailMessage remindActualMailMessage()
	 * @method \Bitrix\Mail\EO_MailMessage requireMailMessage()
	 * @method \Bitrix\Mail\EO_MailLog setMailMessage(\Bitrix\Mail\EO_MailMessage $object)
	 * @method \Bitrix\Mail\EO_MailLog resetMailMessage()
	 * @method \Bitrix\Mail\EO_MailLog unsetMailMessage()
	 * @method bool hasMailMessage()
	 * @method bool isMailMessageFilled()
	 * @method bool isMailMessageChanged()
	 * @method \Bitrix\Mail\EO_MailMessage fillMailMessage()
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
	 * @method \Bitrix\Mail\EO_MailLog set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_MailLog reset($fieldName)
	 * @method \Bitrix\Mail\EO_MailLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_MailLog wakeUp($data)
	 */
	class EO_MailLog {
		/* @var \Bitrix\Mail\MailLogTable */
		static public $dataClass = '\Bitrix\Mail\MailLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_MailLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getFilterIdList()
	 * @method \int[] fillFilterId()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \string[] getLogTypeList()
	 * @method \string[] fillLogType()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \boolean[] getStatusGoodList()
	 * @method \boolean[] fillStatusGood()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \Bitrix\Mail\EO_Mailbox[] getMailboxList()
	 * @method \Bitrix\Mail\EO_MailLog_Collection getMailboxCollection()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection fillMailbox()
	 * @method \Bitrix\Mail\EO_MailFilter[] getFilterList()
	 * @method \Bitrix\Mail\EO_MailLog_Collection getFilterCollection()
	 * @method \Bitrix\Mail\EO_MailFilter_Collection fillFilter()
	 * @method \Bitrix\Mail\EO_MailMessage[] getMailMessageList()
	 * @method \Bitrix\Mail\EO_MailLog_Collection getMailMessageCollection()
	 * @method \Bitrix\Mail\EO_MailMessage_Collection fillMailMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_MailLog $object)
	 * @method bool has(\Bitrix\Mail\EO_MailLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailLog getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailLog[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_MailLog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_MailLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_MailLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailLog_Collection merge(?EO_MailLog_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailLogTable */
		static public $dataClass = '\Bitrix\Mail\MailLogTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailLog_Result exec()
	 * @method \Bitrix\Mail\EO_MailLog fetchObject()
	 * @method \Bitrix\Mail\EO_MailLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_MailLog fetchObject()
	 * @method \Bitrix\Mail\EO_MailLog_Collection fetchCollection()
	 */
	class EO_MailLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_MailLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_MailLog_Collection createCollection()
	 * @method \Bitrix\Mail\EO_MailLog wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_MailLog_Collection wakeUpCollection($rows)
	 */
	class EO_MailLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\MailServicesTable:mail/lib/mailservices.php */
namespace Bitrix\Mail {
	/**
	 * EO_MailServices
	 * @see \Bitrix\Mail\MailServicesTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\EO_MailServices setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Mail\EO_MailServices setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Mail\EO_MailServices resetSiteId()
	 * @method \Bitrix\Mail\EO_MailServices unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Mail\EO_MailServices setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Mail\EO_MailServices resetActive()
	 * @method \Bitrix\Mail\EO_MailServices unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Mail\EO_MailServices setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Mail\EO_MailServices resetSort()
	 * @method \Bitrix\Mail\EO_MailServices unsetSort()
	 * @method \int fillSort()
	 * @method \string getServiceType()
	 * @method \Bitrix\Mail\EO_MailServices setServiceType(\string|\Bitrix\Main\DB\SqlExpression $serviceType)
	 * @method bool hasServiceType()
	 * @method bool isServiceTypeFilled()
	 * @method bool isServiceTypeChanged()
	 * @method \string remindActualServiceType()
	 * @method \string requireServiceType()
	 * @method \Bitrix\Mail\EO_MailServices resetServiceType()
	 * @method \Bitrix\Mail\EO_MailServices unsetServiceType()
	 * @method \string fillServiceType()
	 * @method \string getName()
	 * @method \Bitrix\Mail\EO_MailServices setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Mail\EO_MailServices resetName()
	 * @method \Bitrix\Mail\EO_MailServices unsetName()
	 * @method \string fillName()
	 * @method \string getServer()
	 * @method \Bitrix\Mail\EO_MailServices setServer(\string|\Bitrix\Main\DB\SqlExpression $server)
	 * @method bool hasServer()
	 * @method bool isServerFilled()
	 * @method bool isServerChanged()
	 * @method \string remindActualServer()
	 * @method \string requireServer()
	 * @method \Bitrix\Mail\EO_MailServices resetServer()
	 * @method \Bitrix\Mail\EO_MailServices unsetServer()
	 * @method \string fillServer()
	 * @method \int getPort()
	 * @method \Bitrix\Mail\EO_MailServices setPort(\int|\Bitrix\Main\DB\SqlExpression $port)
	 * @method bool hasPort()
	 * @method bool isPortFilled()
	 * @method bool isPortChanged()
	 * @method \int remindActualPort()
	 * @method \int requirePort()
	 * @method \Bitrix\Mail\EO_MailServices resetPort()
	 * @method \Bitrix\Mail\EO_MailServices unsetPort()
	 * @method \int fillPort()
	 * @method \boolean getEncryption()
	 * @method \Bitrix\Mail\EO_MailServices setEncryption(\boolean|\Bitrix\Main\DB\SqlExpression $encryption)
	 * @method bool hasEncryption()
	 * @method bool isEncryptionFilled()
	 * @method bool isEncryptionChanged()
	 * @method \boolean remindActualEncryption()
	 * @method \boolean requireEncryption()
	 * @method \Bitrix\Mail\EO_MailServices resetEncryption()
	 * @method \Bitrix\Mail\EO_MailServices unsetEncryption()
	 * @method \boolean fillEncryption()
	 * @method \string getLink()
	 * @method \Bitrix\Mail\EO_MailServices setLink(\string|\Bitrix\Main\DB\SqlExpression $link)
	 * @method bool hasLink()
	 * @method bool isLinkFilled()
	 * @method bool isLinkChanged()
	 * @method \string remindActualLink()
	 * @method \string requireLink()
	 * @method \Bitrix\Mail\EO_MailServices resetLink()
	 * @method \Bitrix\Mail\EO_MailServices unsetLink()
	 * @method \string fillLink()
	 * @method \int getIcon()
	 * @method \Bitrix\Mail\EO_MailServices setIcon(\int|\Bitrix\Main\DB\SqlExpression $icon)
	 * @method bool hasIcon()
	 * @method bool isIconFilled()
	 * @method bool isIconChanged()
	 * @method \int remindActualIcon()
	 * @method \int requireIcon()
	 * @method \Bitrix\Mail\EO_MailServices resetIcon()
	 * @method \Bitrix\Mail\EO_MailServices unsetIcon()
	 * @method \int fillIcon()
	 * @method \string getToken()
	 * @method \Bitrix\Mail\EO_MailServices setToken(\string|\Bitrix\Main\DB\SqlExpression $token)
	 * @method bool hasToken()
	 * @method bool isTokenFilled()
	 * @method bool isTokenChanged()
	 * @method \string remindActualToken()
	 * @method \string requireToken()
	 * @method \Bitrix\Mail\EO_MailServices resetToken()
	 * @method \Bitrix\Mail\EO_MailServices unsetToken()
	 * @method \string fillToken()
	 * @method \int getFlags()
	 * @method \Bitrix\Mail\EO_MailServices setFlags(\int|\Bitrix\Main\DB\SqlExpression $flags)
	 * @method bool hasFlags()
	 * @method bool isFlagsFilled()
	 * @method bool isFlagsChanged()
	 * @method \int remindActualFlags()
	 * @method \int requireFlags()
	 * @method \Bitrix\Mail\EO_MailServices resetFlags()
	 * @method \Bitrix\Mail\EO_MailServices unsetFlags()
	 * @method \int fillFlags()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Mail\EO_MailServices setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Mail\EO_MailServices resetSite()
	 * @method \Bitrix\Mail\EO_MailServices unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
	 * @method \string getSmtpServer()
	 * @method \Bitrix\Mail\EO_MailServices setSmtpServer(\string|\Bitrix\Main\DB\SqlExpression $smtpServer)
	 * @method bool hasSmtpServer()
	 * @method bool isSmtpServerFilled()
	 * @method bool isSmtpServerChanged()
	 * @method \string remindActualSmtpServer()
	 * @method \string requireSmtpServer()
	 * @method \Bitrix\Mail\EO_MailServices resetSmtpServer()
	 * @method \Bitrix\Mail\EO_MailServices unsetSmtpServer()
	 * @method \string fillSmtpServer()
	 * @method \int getSmtpPort()
	 * @method \Bitrix\Mail\EO_MailServices setSmtpPort(\int|\Bitrix\Main\DB\SqlExpression $smtpPort)
	 * @method bool hasSmtpPort()
	 * @method bool isSmtpPortFilled()
	 * @method bool isSmtpPortChanged()
	 * @method \int remindActualSmtpPort()
	 * @method \int requireSmtpPort()
	 * @method \Bitrix\Mail\EO_MailServices resetSmtpPort()
	 * @method \Bitrix\Mail\EO_MailServices unsetSmtpPort()
	 * @method \int fillSmtpPort()
	 * @method \boolean getSmtpLoginAsImap()
	 * @method \Bitrix\Mail\EO_MailServices setSmtpLoginAsImap(\boolean|\Bitrix\Main\DB\SqlExpression $smtpLoginAsImap)
	 * @method bool hasSmtpLoginAsImap()
	 * @method bool isSmtpLoginAsImapFilled()
	 * @method bool isSmtpLoginAsImapChanged()
	 * @method \boolean remindActualSmtpLoginAsImap()
	 * @method \boolean requireSmtpLoginAsImap()
	 * @method \Bitrix\Mail\EO_MailServices resetSmtpLoginAsImap()
	 * @method \Bitrix\Mail\EO_MailServices unsetSmtpLoginAsImap()
	 * @method \boolean fillSmtpLoginAsImap()
	 * @method \boolean getSmtpPasswordAsImap()
	 * @method \Bitrix\Mail\EO_MailServices setSmtpPasswordAsImap(\boolean|\Bitrix\Main\DB\SqlExpression $smtpPasswordAsImap)
	 * @method bool hasSmtpPasswordAsImap()
	 * @method bool isSmtpPasswordAsImapFilled()
	 * @method bool isSmtpPasswordAsImapChanged()
	 * @method \boolean remindActualSmtpPasswordAsImap()
	 * @method \boolean requireSmtpPasswordAsImap()
	 * @method \Bitrix\Mail\EO_MailServices resetSmtpPasswordAsImap()
	 * @method \Bitrix\Mail\EO_MailServices unsetSmtpPasswordAsImap()
	 * @method \boolean fillSmtpPasswordAsImap()
	 * @method \boolean getSmtpEncryption()
	 * @method \Bitrix\Mail\EO_MailServices setSmtpEncryption(\boolean|\Bitrix\Main\DB\SqlExpression $smtpEncryption)
	 * @method bool hasSmtpEncryption()
	 * @method bool isSmtpEncryptionFilled()
	 * @method bool isSmtpEncryptionChanged()
	 * @method \boolean remindActualSmtpEncryption()
	 * @method \boolean requireSmtpEncryption()
	 * @method \Bitrix\Mail\EO_MailServices resetSmtpEncryption()
	 * @method \Bitrix\Mail\EO_MailServices unsetSmtpEncryption()
	 * @method \boolean fillSmtpEncryption()
	 * @method \boolean getUploadOutgoing()
	 * @method \Bitrix\Mail\EO_MailServices setUploadOutgoing(\boolean|\Bitrix\Main\DB\SqlExpression $uploadOutgoing)
	 * @method bool hasUploadOutgoing()
	 * @method bool isUploadOutgoingFilled()
	 * @method bool isUploadOutgoingChanged()
	 * @method \boolean remindActualUploadOutgoing()
	 * @method \boolean requireUploadOutgoing()
	 * @method \Bitrix\Mail\EO_MailServices resetUploadOutgoing()
	 * @method \Bitrix\Mail\EO_MailServices unsetUploadOutgoing()
	 * @method \boolean fillUploadOutgoing()
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
	 * @method \Bitrix\Mail\EO_MailServices set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_MailServices reset($fieldName)
	 * @method \Bitrix\Mail\EO_MailServices unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_MailServices wakeUp($data)
	 */
	class EO_MailServices {
		/* @var \Bitrix\Mail\MailServicesTable */
		static public $dataClass = '\Bitrix\Mail\MailServicesTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_MailServices_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getServiceTypeList()
	 * @method \string[] fillServiceType()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getServerList()
	 * @method \string[] fillServer()
	 * @method \int[] getPortList()
	 * @method \int[] fillPort()
	 * @method \boolean[] getEncryptionList()
	 * @method \boolean[] fillEncryption()
	 * @method \string[] getLinkList()
	 * @method \string[] fillLink()
	 * @method \int[] getIconList()
	 * @method \int[] fillIcon()
	 * @method \string[] getTokenList()
	 * @method \string[] fillToken()
	 * @method \int[] getFlagsList()
	 * @method \int[] fillFlags()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Mail\EO_MailServices_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 * @method \string[] getSmtpServerList()
	 * @method \string[] fillSmtpServer()
	 * @method \int[] getSmtpPortList()
	 * @method \int[] fillSmtpPort()
	 * @method \boolean[] getSmtpLoginAsImapList()
	 * @method \boolean[] fillSmtpLoginAsImap()
	 * @method \boolean[] getSmtpPasswordAsImapList()
	 * @method \boolean[] fillSmtpPasswordAsImap()
	 * @method \boolean[] getSmtpEncryptionList()
	 * @method \boolean[] fillSmtpEncryption()
	 * @method \boolean[] getUploadOutgoingList()
	 * @method \boolean[] fillUploadOutgoing()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_MailServices $object)
	 * @method bool has(\Bitrix\Mail\EO_MailServices $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailServices getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailServices[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_MailServices $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_MailServices_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_MailServices current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailServices_Collection merge(?EO_MailServices_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailServices_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailServicesTable */
		static public $dataClass = '\Bitrix\Mail\MailServicesTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailServices_Result exec()
	 * @method \Bitrix\Mail\EO_MailServices fetchObject()
	 * @method \Bitrix\Mail\EO_MailServices_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailServices_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_MailServices fetchObject()
	 * @method \Bitrix\Mail\EO_MailServices_Collection fetchCollection()
	 */
	class EO_MailServices_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_MailServices createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_MailServices_Collection createCollection()
	 * @method \Bitrix\Mail\EO_MailServices wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_MailServices_Collection wakeUpCollection($rows)
	 */
	class EO_MailServices_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailCounterTable:mail/lib/internals/mailcounter.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailCounter
	 * @see \Bitrix\Mail\Internals\MailCounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int getValue()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter resetValue()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter unsetValue()
	 * @method \int fillValue()
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
	 * @method \Bitrix\Mail\Internals\EO_MailCounter set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MailCounter wakeUp($data)
	 */
	class EO_MailCounter {
		/* @var \Bitrix\Mail\Internals\MailCounterTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailCounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailCounter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMailboxIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] getEntityIdList()
	 * @method \int[] getValueList()
	 * @method \int[] fillValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MailCounter $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MailCounter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MailCounter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailCounter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MailCounter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailCounter_Collection merge(?EO_MailCounter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailCounter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailCounterTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailCounterTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailCounter_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailCounter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailCounter fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter_Collection fetchCollection()
	 */
	class EO_MailCounter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailCounter createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MailCounter wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailCounter_Collection wakeUpCollection($rows)
	 */
	class EO_MailCounter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MessageUploadQueueTable:mail/lib/internals/messageuploadqueue.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageUploadQueue
	 * @see \Bitrix\Mail\Internals\MessageUploadQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int getSyncStage()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue setSyncStage(\int|\Bitrix\Main\DB\SqlExpression $syncStage)
	 * @method bool hasSyncStage()
	 * @method bool isSyncStageFilled()
	 * @method bool isSyncStageChanged()
	 * @method \int remindActualSyncStage()
	 * @method \int requireSyncStage()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue resetSyncStage()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue unsetSyncStage()
	 * @method \int fillSyncStage()
	 * @method \int getSyncLock()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue setSyncLock(\int|\Bitrix\Main\DB\SqlExpression $syncLock)
	 * @method bool hasSyncLock()
	 * @method bool isSyncLockFilled()
	 * @method bool isSyncLockChanged()
	 * @method \int remindActualSyncLock()
	 * @method \int requireSyncLock()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue resetSyncLock()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue unsetSyncLock()
	 * @method \int fillSyncLock()
	 * @method \int getAttempts()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue setAttempts(\int|\Bitrix\Main\DB\SqlExpression $attempts)
	 * @method bool hasAttempts()
	 * @method bool isAttemptsFilled()
	 * @method bool isAttemptsChanged()
	 * @method \int remindActualAttempts()
	 * @method \int requireAttempts()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue resetAttempts()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue unsetAttempts()
	 * @method \int fillAttempts()
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
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue wakeUp($data)
	 */
	class EO_MessageUploadQueue {
		/* @var \Bitrix\Mail\Internals\MessageUploadQueueTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageUploadQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageUploadQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] getSyncStageList()
	 * @method \int[] fillSyncStage()
	 * @method \int[] getSyncLockList()
	 * @method \int[] fillSyncLock()
	 * @method \int[] getAttemptsList()
	 * @method \int[] fillAttempts()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MessageUploadQueue $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MessageUploadQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MessageUploadQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MessageUploadQueue_Collection merge(?EO_MessageUploadQueue_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MessageUploadQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MessageUploadQueueTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageUploadQueueTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageUploadQueue_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageUploadQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection fetchCollection()
	 */
	class EO_MessageUploadQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection wakeUpCollection($rows)
	 */
	class EO_MessageUploadQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MessageDeleteQueueTable:mail/lib/internals/messagedeletequeue.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageDeleteQueue
	 * @see \Bitrix\Mail\Internals\MessageDeleteQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue resetMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue resetMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue unsetMessageId()
	 * @method \int fillMessageId()
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
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue wakeUp($data)
	 */
	class EO_MessageDeleteQueue {
		/* @var \Bitrix\Mail\Internals\MessageDeleteQueueTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageDeleteQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageDeleteQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MessageDeleteQueue $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MessageDeleteQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MessageDeleteQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MessageDeleteQueue_Collection merge(?EO_MessageDeleteQueue_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MessageDeleteQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MessageDeleteQueueTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageDeleteQueueTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageDeleteQueue_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageDeleteQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection fetchCollection()
	 */
	class EO_MessageDeleteQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection wakeUpCollection($rows)
	 */
	class EO_MessageDeleteQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\BlacklistTable:mail/lib/blacklist.php */
namespace Bitrix\Mail {
	/**
	 * BlacklistEmail
	 * @see \Bitrix\Mail\BlacklistTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail resetSiteId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail resetMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail resetUserId()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getItemType()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setItemType(\int|\Bitrix\Main\DB\SqlExpression $itemType)
	 * @method bool hasItemType()
	 * @method bool isItemTypeFilled()
	 * @method bool isItemTypeChanged()
	 * @method \int remindActualItemType()
	 * @method \int requireItemType()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail resetItemType()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unsetItemType()
	 * @method \int fillItemType()
	 * @method \string getItemValue()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail setItemValue(\string|\Bitrix\Main\DB\SqlExpression $itemValue)
	 * @method bool hasItemValue()
	 * @method bool isItemValueFilled()
	 * @method bool isItemValueChanged()
	 * @method \string remindActualItemValue()
	 * @method \string requireItemValue()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail resetItemValue()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unsetItemValue()
	 * @method \string fillItemValue()
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
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail reset($fieldName)
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\Entity\BlacklistEmail wakeUp($data)
	 */
	class EO_Blacklist {
		/* @var \Bitrix\Mail\BlacklistTable */
		static public $dataClass = '\Bitrix\Mail\BlacklistTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_Blacklist_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getItemTypeList()
	 * @method \int[] fillItemType()
	 * @method \string[] getItemValueList()
	 * @method \string[] fillItemValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\Entity\BlacklistEmail $object)
	 * @method bool has(\Bitrix\Mail\Internals\Entity\BlacklistEmail $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\Entity\BlacklistEmail $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_Blacklist_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Blacklist_Collection merge(?EO_Blacklist_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Blacklist_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\BlacklistTable */
		static public $dataClass = '\Bitrix\Mail\BlacklistTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Blacklist_Result exec()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail fetchObject()
	 * @method \Bitrix\Mail\EO_Blacklist_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Blacklist_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail fetchObject()
	 * @method \Bitrix\Mail\EO_Blacklist_Collection fetchCollection()
	 */
	class EO_Blacklist_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_Blacklist_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\Entity\BlacklistEmail wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_Blacklist_Collection wakeUpCollection($rows)
	 */
	class EO_Blacklist_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\UserSignatureTable:mail/lib/internals/usersignature.php */
namespace Bitrix\Mail\Internals {
	/**
	 * UserSignature
	 * @see \Bitrix\Mail\Internals\UserSignatureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature resetUserId()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getSignature()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature setSignature(\string|\Bitrix\Main\DB\SqlExpression $signature)
	 * @method bool hasSignature()
	 * @method bool isSignatureFilled()
	 * @method bool isSignatureChanged()
	 * @method \string remindActualSignature()
	 * @method \string requireSignature()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature resetSignature()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature unsetSignature()
	 * @method \string fillSignature()
	 * @method \string getSender()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature setSender(\string|\Bitrix\Main\DB\SqlExpression $sender)
	 * @method bool hasSender()
	 * @method bool isSenderFilled()
	 * @method bool isSenderChanged()
	 * @method \string remindActualSender()
	 * @method \string requireSender()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature resetSender()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature unsetSender()
	 * @method \string fillSender()
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
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature reset($fieldName)
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\Entity\UserSignature wakeUp($data)
	 */
	class EO_UserSignature {
		/* @var \Bitrix\Mail\Internals\UserSignatureTable */
		static public $dataClass = '\Bitrix\Mail\Internals\UserSignatureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_UserSignature_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getSignatureList()
	 * @method \string[] fillSignature()
	 * @method \string[] getSenderList()
	 * @method \string[] fillSender()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\Entity\UserSignature $object)
	 * @method bool has(\Bitrix\Mail\Internals\Entity\UserSignature $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\Entity\UserSignature $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_UserSignature_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_UserSignature_Collection merge(?EO_UserSignature_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_UserSignature_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\UserSignatureTable */
		static public $dataClass = '\Bitrix\Mail\Internals\UserSignatureTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserSignature_Result exec()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_UserSignature_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserSignature_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_UserSignature_Collection fetchCollection()
	 */
	class EO_UserSignature_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_UserSignature_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\Entity\UserSignature wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_UserSignature_Collection wakeUpCollection($rows)
	 */
	class EO_UserSignature_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailboxDirectoryTable:mail/lib/internals/mailboxdirectory.php */
namespace Bitrix\Mail\Internals {
	/**
	 * MailboxDirectory
	 * @see \Bitrix\Mail\Internals\MailboxDirectoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetMailboxId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \string getName()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetName()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetName()
	 * @method \string fillName()
	 * @method \string getPath()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setPath(\string|\Bitrix\Main\DB\SqlExpression $path)
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \string remindActualPath()
	 * @method \string requirePath()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetPath()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetPath()
	 * @method \string fillPath()
	 * @method \string getFlags()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setFlags(\string|\Bitrix\Main\DB\SqlExpression $flags)
	 * @method bool hasFlags()
	 * @method bool isFlagsFilled()
	 * @method bool isFlagsChanged()
	 * @method \string remindActualFlags()
	 * @method \string requireFlags()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetFlags()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetFlags()
	 * @method \string fillFlags()
	 * @method \string getDelimiter()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setDelimiter(\string|\Bitrix\Main\DB\SqlExpression $delimiter)
	 * @method bool hasDelimiter()
	 * @method bool isDelimiterFilled()
	 * @method bool isDelimiterChanged()
	 * @method \string remindActualDelimiter()
	 * @method \string requireDelimiter()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetDelimiter()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetDelimiter()
	 * @method \string fillDelimiter()
	 * @method \string getDirMd5()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setDirMd5(\string|\Bitrix\Main\DB\SqlExpression $dirMd5)
	 * @method bool hasDirMd5()
	 * @method bool isDirMd5Filled()
	 * @method bool isDirMd5Changed()
	 * @method \string remindActualDirMd5()
	 * @method \string requireDirMd5()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetDirMd5()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetDirMd5()
	 * @method \string fillDirMd5()
	 * @method \int getLevel()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setLevel(\int|\Bitrix\Main\DB\SqlExpression $level)
	 * @method bool hasLevel()
	 * @method bool isLevelFilled()
	 * @method bool isLevelChanged()
	 * @method \int remindActualLevel()
	 * @method \int requireLevel()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetLevel()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetLevel()
	 * @method \int fillLevel()
	 * @method \int getMessageCount()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setMessageCount(\int|\Bitrix\Main\DB\SqlExpression $messageCount)
	 * @method bool hasMessageCount()
	 * @method bool isMessageCountFilled()
	 * @method bool isMessageCountChanged()
	 * @method \int remindActualMessageCount()
	 * @method \int requireMessageCount()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetMessageCount()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetMessageCount()
	 * @method \int fillMessageCount()
	 * @method \int getParentId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetParentId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getRootId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setRootId(\int|\Bitrix\Main\DB\SqlExpression $rootId)
	 * @method bool hasRootId()
	 * @method bool isRootIdFilled()
	 * @method bool isRootIdChanged()
	 * @method \int remindActualRootId()
	 * @method \int requireRootId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetRootId()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetRootId()
	 * @method \int fillRootId()
	 * @method \int getIsSync()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsSync(\int|\Bitrix\Main\DB\SqlExpression $isSync)
	 * @method bool hasIsSync()
	 * @method bool isIsSyncFilled()
	 * @method bool isIsSyncChanged()
	 * @method \int remindActualIsSync()
	 * @method \int requireIsSync()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsSync()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsSync()
	 * @method \int fillIsSync()
	 * @method \int getIsDisabled()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsDisabled(\int|\Bitrix\Main\DB\SqlExpression $isDisabled)
	 * @method bool hasIsDisabled()
	 * @method bool isIsDisabledFilled()
	 * @method bool isIsDisabledChanged()
	 * @method \int remindActualIsDisabled()
	 * @method \int requireIsDisabled()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsDisabled()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsDisabled()
	 * @method \int fillIsDisabled()
	 * @method \int getIsIncome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsIncome(\int|\Bitrix\Main\DB\SqlExpression $isIncome)
	 * @method bool hasIsIncome()
	 * @method bool isIsIncomeFilled()
	 * @method bool isIsIncomeChanged()
	 * @method \int remindActualIsIncome()
	 * @method \int requireIsIncome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsIncome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsIncome()
	 * @method \int fillIsIncome()
	 * @method \int getIsOutcome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsOutcome(\int|\Bitrix\Main\DB\SqlExpression $isOutcome)
	 * @method bool hasIsOutcome()
	 * @method bool isIsOutcomeFilled()
	 * @method bool isIsOutcomeChanged()
	 * @method \int remindActualIsOutcome()
	 * @method \int requireIsOutcome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsOutcome()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsOutcome()
	 * @method \int fillIsOutcome()
	 * @method \int getIsDraft()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsDraft(\int|\Bitrix\Main\DB\SqlExpression $isDraft)
	 * @method bool hasIsDraft()
	 * @method bool isIsDraftFilled()
	 * @method bool isIsDraftChanged()
	 * @method \int remindActualIsDraft()
	 * @method \int requireIsDraft()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsDraft()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsDraft()
	 * @method \int fillIsDraft()
	 * @method \int getIsTrash()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsTrash(\int|\Bitrix\Main\DB\SqlExpression $isTrash)
	 * @method bool hasIsTrash()
	 * @method bool isIsTrashFilled()
	 * @method bool isIsTrashChanged()
	 * @method \int remindActualIsTrash()
	 * @method \int requireIsTrash()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsTrash()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsTrash()
	 * @method \int fillIsTrash()
	 * @method \int getIsSpam()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setIsSpam(\int|\Bitrix\Main\DB\SqlExpression $isSpam)
	 * @method bool hasIsSpam()
	 * @method bool isIsSpamFilled()
	 * @method bool isIsSpamChanged()
	 * @method \int remindActualIsSpam()
	 * @method \int requireIsSpam()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetIsSpam()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetIsSpam()
	 * @method \int fillIsSpam()
	 * @method \int getSyncTime()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setSyncTime(\int|\Bitrix\Main\DB\SqlExpression $syncTime)
	 * @method bool hasSyncTime()
	 * @method bool isSyncTimeFilled()
	 * @method bool isSyncTimeChanged()
	 * @method \int remindActualSyncTime()
	 * @method \int requireSyncTime()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetSyncTime()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetSyncTime()
	 * @method \int fillSyncTime()
	 * @method \int getSyncLock()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory setSyncLock(\int|\Bitrix\Main\DB\SqlExpression $syncLock)
	 * @method bool hasSyncLock()
	 * @method bool isSyncLockFilled()
	 * @method bool isSyncLockChanged()
	 * @method \int remindActualSyncLock()
	 * @method \int requireSyncLock()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory resetSyncLock()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unsetSyncLock()
	 * @method \int fillSyncLock()
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
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory reset($fieldName)
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\Entity\MailboxDirectory wakeUp($data)
	 */
	class EO_MailboxDirectory {
		/* @var \Bitrix\Mail\Internals\MailboxDirectoryTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailboxDirectoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailboxDirectory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getPathList()
	 * @method \string[] fillPath()
	 * @method \string[] getFlagsList()
	 * @method \string[] fillFlags()
	 * @method \string[] getDelimiterList()
	 * @method \string[] fillDelimiter()
	 * @method \string[] getDirMd5List()
	 * @method \string[] fillDirMd5()
	 * @method \int[] getLevelList()
	 * @method \int[] fillLevel()
	 * @method \int[] getMessageCountList()
	 * @method \int[] fillMessageCount()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getRootIdList()
	 * @method \int[] fillRootId()
	 * @method \int[] getIsSyncList()
	 * @method \int[] fillIsSync()
	 * @method \int[] getIsDisabledList()
	 * @method \int[] fillIsDisabled()
	 * @method \int[] getIsIncomeList()
	 * @method \int[] fillIsIncome()
	 * @method \int[] getIsOutcomeList()
	 * @method \int[] fillIsOutcome()
	 * @method \int[] getIsDraftList()
	 * @method \int[] fillIsDraft()
	 * @method \int[] getIsTrashList()
	 * @method \int[] fillIsTrash()
	 * @method \int[] getIsSpamList()
	 * @method \int[] fillIsSpam()
	 * @method \int[] getSyncTimeList()
	 * @method \int[] fillSyncTime()
	 * @method \int[] getSyncLockList()
	 * @method \int[] fillSyncLock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\Entity\MailboxDirectory $object)
	 * @method bool has(\Bitrix\Mail\Internals\Entity\MailboxDirectory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\Entity\MailboxDirectory $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailboxDirectory_Collection merge(?EO_MailboxDirectory_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailboxDirectory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailboxDirectoryTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailboxDirectoryTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailboxDirectory_Result exec()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailboxDirectory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection fetchCollection()
	 */
	class EO_MailboxDirectory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\Entity\MailboxDirectory wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailboxDirectory_Collection wakeUpCollection($rows)
	 */
	class EO_MailboxDirectory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\OAuthTable:mail/lib/internals/oauth.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_OAuth
	 * @see \Bitrix\Mail\Internals\OAuthTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getUid()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setUid(\string|\Bitrix\Main\DB\SqlExpression $uid)
	 * @method bool hasUid()
	 * @method bool isUidFilled()
	 * @method bool isUidChanged()
	 * @method \string remindActualUid()
	 * @method \string requireUid()
	 * @method \Bitrix\Mail\Internals\EO_OAuth resetUid()
	 * @method \Bitrix\Mail\Internals\EO_OAuth unsetUid()
	 * @method \string fillUid()
	 * @method \string getToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setToken(\string|\Bitrix\Main\DB\SqlExpression $token)
	 * @method bool hasToken()
	 * @method bool isTokenFilled()
	 * @method bool isTokenChanged()
	 * @method \string remindActualToken()
	 * @method \string requireToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth resetToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth unsetToken()
	 * @method \string fillToken()
	 * @method \string getRefreshToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setRefreshToken(\string|\Bitrix\Main\DB\SqlExpression $refreshToken)
	 * @method bool hasRefreshToken()
	 * @method bool isRefreshTokenFilled()
	 * @method bool isRefreshTokenChanged()
	 * @method \string remindActualRefreshToken()
	 * @method \string requireRefreshToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth resetRefreshToken()
	 * @method \Bitrix\Mail\Internals\EO_OAuth unsetRefreshToken()
	 * @method \string fillRefreshToken()
	 * @method \int getTokenExpires()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setTokenExpires(\int|\Bitrix\Main\DB\SqlExpression $tokenExpires)
	 * @method bool hasTokenExpires()
	 * @method bool isTokenExpiresFilled()
	 * @method bool isTokenExpiresChanged()
	 * @method \int remindActualTokenExpires()
	 * @method \int requireTokenExpires()
	 * @method \Bitrix\Mail\Internals\EO_OAuth resetTokenExpires()
	 * @method \Bitrix\Mail\Internals\EO_OAuth unsetTokenExpires()
	 * @method \int fillTokenExpires()
	 * @method \string getSecret()
	 * @method \Bitrix\Mail\Internals\EO_OAuth setSecret(\string|\Bitrix\Main\DB\SqlExpression $secret)
	 * @method bool hasSecret()
	 * @method bool isSecretFilled()
	 * @method bool isSecretChanged()
	 * @method \string remindActualSecret()
	 * @method \string requireSecret()
	 * @method \Bitrix\Mail\Internals\EO_OAuth resetSecret()
	 * @method \Bitrix\Mail\Internals\EO_OAuth unsetSecret()
	 * @method \string fillSecret()
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
	 * @method \Bitrix\Mail\Internals\EO_OAuth set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_OAuth reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_OAuth unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_OAuth wakeUp($data)
	 */
	class EO_OAuth {
		/* @var \Bitrix\Mail\Internals\OAuthTable */
		static public $dataClass = '\Bitrix\Mail\Internals\OAuthTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_OAuth_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getUidList()
	 * @method \string[] fillUid()
	 * @method \string[] getTokenList()
	 * @method \string[] fillToken()
	 * @method \string[] getRefreshTokenList()
	 * @method \string[] fillRefreshToken()
	 * @method \int[] getTokenExpiresList()
	 * @method \int[] fillTokenExpires()
	 * @method \string[] getSecretList()
	 * @method \string[] fillSecret()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_OAuth $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_OAuth $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_OAuth getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_OAuth[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_OAuth $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_OAuth_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_OAuth current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_OAuth_Collection merge(?EO_OAuth_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OAuth_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\OAuthTable */
		static public $dataClass = '\Bitrix\Mail\Internals\OAuthTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OAuth_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_OAuth fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_OAuth_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OAuth_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_OAuth fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_OAuth_Collection fetchCollection()
	 */
	class EO_OAuth_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_OAuth createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_OAuth_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_OAuth wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_OAuth_Collection wakeUpCollection($rows)
	 */
	class EO_OAuth_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MessageClosureTable:mail/lib/internals/messageclosure.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageClosure
	 * @see \Bitrix\Mail\Internals\MessageClosureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
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
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MessageClosure wakeUp($data)
	 */
	class EO_MessageClosure {
		/* @var \Bitrix\Mail\Internals\MessageClosureTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageClosureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageClosure_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMessageIdList()
	 * @method \int[] getParentIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MessageClosure $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MessageClosure $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MessageClosure $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MessageClosure_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MessageClosure_Collection merge(?EO_MessageClosure_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MessageClosure_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MessageClosureTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageClosureTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageClosure_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageClosure_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure_Collection fetchCollection()
	 */
	class EO_MessageClosure_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MessageClosure_Collection wakeUpCollection($rows)
	 */
	class EO_MessageClosure_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailMessageAttachmentTable:mail/lib/internals/mailmessageattachment.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailMessageAttachment
	 * @see \Bitrix\Mail\Internals\MailMessageAttachmentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \int getFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetFileId()
	 * @method \int fillFileId()
	 * @method \string getFileName()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setFileName(\string|\Bitrix\Main\DB\SqlExpression $fileName)
	 * @method bool hasFileName()
	 * @method bool isFileNameFilled()
	 * @method bool isFileNameChanged()
	 * @method \string remindActualFileName()
	 * @method \string requireFileName()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetFileName()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetFileName()
	 * @method \string fillFileName()
	 * @method \int getFileSize()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetFileSize()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getFileData()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setFileData(\string|\Bitrix\Main\DB\SqlExpression $fileData)
	 * @method bool hasFileData()
	 * @method bool isFileDataFilled()
	 * @method bool isFileDataChanged()
	 * @method \string remindActualFileData()
	 * @method \string requireFileData()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetFileData()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetFileData()
	 * @method \string fillFileData()
	 * @method \string getContentType()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setContentType(\string|\Bitrix\Main\DB\SqlExpression $contentType)
	 * @method bool hasContentType()
	 * @method bool isContentTypeFilled()
	 * @method bool isContentTypeChanged()
	 * @method \string remindActualContentType()
	 * @method \string requireContentType()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetContentType()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetContentType()
	 * @method \string fillContentType()
	 * @method \int getImageWidth()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setImageWidth(\int|\Bitrix\Main\DB\SqlExpression $imageWidth)
	 * @method bool hasImageWidth()
	 * @method bool isImageWidthFilled()
	 * @method bool isImageWidthChanged()
	 * @method \int remindActualImageWidth()
	 * @method \int requireImageWidth()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetImageWidth()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetImageWidth()
	 * @method \int fillImageWidth()
	 * @method \int getImageHeight()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment setImageHeight(\int|\Bitrix\Main\DB\SqlExpression $imageHeight)
	 * @method bool hasImageHeight()
	 * @method bool isImageHeightFilled()
	 * @method bool isImageHeightChanged()
	 * @method \int remindActualImageHeight()
	 * @method \int requireImageHeight()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment resetImageHeight()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unsetImageHeight()
	 * @method \int fillImageHeight()
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
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment wakeUp($data)
	 */
	class EO_MailMessageAttachment {
		/* @var \Bitrix\Mail\Internals\MailMessageAttachmentTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailMessageAttachmentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailMessageAttachment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \string[] getFileNameList()
	 * @method \string[] fillFileName()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getFileDataList()
	 * @method \string[] fillFileData()
	 * @method \string[] getContentTypeList()
	 * @method \string[] fillContentType()
	 * @method \int[] getImageWidthList()
	 * @method \int[] fillImageWidth()
	 * @method \int[] getImageHeightList()
	 * @method \int[] fillImageHeight()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MailMessageAttachment $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MailMessageAttachment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MailMessageAttachment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailMessageAttachment_Collection merge(?EO_MailMessageAttachment_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailMessageAttachment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailMessageAttachmentTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailMessageAttachmentTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailMessageAttachment_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailMessageAttachment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection fetchCollection()
	 */
	class EO_MailMessageAttachment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection wakeUpCollection($rows)
	 */
	class EO_MailMessageAttachment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailContactTable:mail/lib/internals/mailcontact.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailContact
	 * @see \Bitrix\Mail\Internals\MailContactTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEmail()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetEmail()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getName()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetName()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetName()
	 * @method \string fillName()
	 * @method \string getIcon()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setIcon(\string|\Bitrix\Main\DB\SqlExpression $icon)
	 * @method bool hasIcon()
	 * @method bool isIconFilled()
	 * @method bool isIconChanged()
	 * @method \string remindActualIcon()
	 * @method \string requireIcon()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetIcon()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetIcon()
	 * @method \string fillIcon()
	 * @method \int getFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetFileId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetFileId()
	 * @method \int fillFileId()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetUserId()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getAddedFrom()
	 * @method \Bitrix\Mail\Internals\EO_MailContact setAddedFrom(\string|\Bitrix\Main\DB\SqlExpression $addedFrom)
	 * @method bool hasAddedFrom()
	 * @method bool isAddedFromFilled()
	 * @method bool isAddedFromChanged()
	 * @method \string remindActualAddedFrom()
	 * @method \string requireAddedFrom()
	 * @method \Bitrix\Mail\Internals\EO_MailContact resetAddedFrom()
	 * @method \Bitrix\Mail\Internals\EO_MailContact unsetAddedFrom()
	 * @method \string fillAddedFrom()
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
	 * @method \Bitrix\Mail\Internals\EO_MailContact set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MailContact reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MailContact unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MailContact wakeUp($data)
	 */
	class EO_MailContact {
		/* @var \Bitrix\Mail\Internals\MailContactTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailContactTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailContact_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getIconList()
	 * @method \string[] fillIcon()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getAddedFromList()
	 * @method \string[] fillAddedFrom()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MailContact $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MailContact $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailContact getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailContact[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MailContact $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailContact_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MailContact current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailContact_Collection merge(?EO_MailContact_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailContact_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailContactTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailContactTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailContact_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MailContact fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailContact_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailContact_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailContact fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailContact_Collection fetchCollection()
	 */
	class EO_MailContact_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailContact createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailContact_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MailContact wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailContact_Collection wakeUpCollection($rows)
	 */
	class EO_MailContact_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailboxAccessTable:mail/lib/internals/mailboxaccess.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailboxAccess
	 * @see \Bitrix\Mail\Internals\MailboxAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess resetMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getTaskId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \int remindActualTaskId()
	 * @method \int requireTaskId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess resetTaskId()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess unsetTaskId()
	 * @method \int fillTaskId()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess resetAccessCode()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess unsetAccessCode()
	 * @method \string fillAccessCode()
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
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess wakeUp($data)
	 */
	class EO_MailboxAccess {
		/* @var \Bitrix\Mail\Internals\MailboxAccessTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailboxAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailboxAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getTaskIdList()
	 * @method \int[] fillTaskId()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MailboxAccess $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MailboxAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MailboxAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailboxAccess_Collection merge(?EO_MailboxAccess_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailboxAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailboxAccessTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailboxAccessTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailboxAccess_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailboxAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess_Collection fetchCollection()
	 */
	class EO_MailboxAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailboxAccess_Collection wakeUpCollection($rows)
	 */
	class EO_MailboxAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MailEntityOptionsTable:mail/lib/internals/mailentityoptions.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailEntityOptions
	 * @see \Bitrix\Mail\Internals\MailEntityOptionsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getPropertyName()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setPropertyName(\string|\Bitrix\Main\DB\SqlExpression $propertyName)
	 * @method bool hasPropertyName()
	 * @method bool isPropertyNameFilled()
	 * @method bool isPropertyNameChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions resetValue()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions unsetValue()
	 * @method \string fillValue()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions resetDateInsert()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
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
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions wakeUp($data)
	 */
	class EO_MailEntityOptions {
		/* @var \Bitrix\Mail\Internals\MailEntityOptionsTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailEntityOptionsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MailEntityOptions_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMailboxIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] getPropertyNameList()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MailEntityOptions $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MailEntityOptions $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MailEntityOptions $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailEntityOptions_Collection merge(?EO_MailEntityOptions_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailEntityOptions_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MailEntityOptionsTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MailEntityOptionsTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailEntityOptions_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailEntityOptions_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection fetchCollection()
	 */
	class EO_MailEntityOptions_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection wakeUpCollection($rows)
	 */
	class EO_MailEntityOptions_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\MessageAccessTable:mail/lib/internals/messageaccess.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageAccess
	 * @see \Bitrix\Mail\Internals\MessageAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getToken()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setToken(\string|\Bitrix\Main\DB\SqlExpression $token)
	 * @method bool hasToken()
	 * @method bool isTokenFilled()
	 * @method bool isTokenChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetMailboxId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetMessageId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \int getEntityUfId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setEntityUfId(\int|\Bitrix\Main\DB\SqlExpression $entityUfId)
	 * @method bool hasEntityUfId()
	 * @method bool isEntityUfIdFilled()
	 * @method bool isEntityUfIdChanged()
	 * @method \int remindActualEntityUfId()
	 * @method \int requireEntityUfId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetEntityUfId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetEntityUfId()
	 * @method \int fillEntityUfId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetEntityType()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetEntityId()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getSecret()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setSecret(\string|\Bitrix\Main\DB\SqlExpression $secret)
	 * @method bool hasSecret()
	 * @method bool isSecretFilled()
	 * @method bool isSecretChanged()
	 * @method \string remindActualSecret()
	 * @method \string requireSecret()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetSecret()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetSecret()
	 * @method \string fillSecret()
	 * @method \string getOptions()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setOptions(\string|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \string remindActualOptions()
	 * @method \string requireOptions()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetOptions()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetOptions()
	 * @method \string fillOptions()
	 * @method \Bitrix\Crm\EO_Activity getCrmActivity()
	 * @method \Bitrix\Crm\EO_Activity remindActualCrmActivity()
	 * @method \Bitrix\Crm\EO_Activity requireCrmActivity()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess setCrmActivity(\Bitrix\Crm\EO_Activity $object)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess resetCrmActivity()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unsetCrmActivity()
	 * @method bool hasCrmActivity()
	 * @method bool isCrmActivityFilled()
	 * @method bool isCrmActivityChanged()
	 * @method \Bitrix\Crm\EO_Activity fillCrmActivity()
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
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_MessageAccess wakeUp($data)
	 */
	class EO_MessageAccess {
		/* @var \Bitrix\Mail\Internals\MessageAccessTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_MessageAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getTokenList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \int[] getEntityUfIdList()
	 * @method \int[] fillEntityUfId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getSecretList()
	 * @method \string[] fillSecret()
	 * @method \string[] getOptionsList()
	 * @method \string[] fillOptions()
	 * @method \Bitrix\Crm\EO_Activity[] getCrmActivityList()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess_Collection getCrmActivityCollection()
	 * @method \Bitrix\Crm\EO_Activity_Collection fillCrmActivity()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_MessageAccess $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_MessageAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_MessageAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_MessageAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MessageAccess_Collection merge(?EO_MessageAccess_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MessageAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\MessageAccessTable */
		static public $dataClass = '\Bitrix\Mail\Internals\MessageAccessTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageAccess_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess_Collection fetchCollection()
	 */
	class EO_MessageAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_MessageAccess_Collection wakeUpCollection($rows)
	 */
	class EO_MessageAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\Internals\DomainEmailTable:mail/lib/internals/domainemail.php */
namespace Bitrix\Mail\Internals {
	/**
	 * EO_DomainEmail
	 * @see \Bitrix\Mail\Internals\DomainEmailTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getDomain()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string getLogin()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail setLogin(\string|\Bitrix\Main\DB\SqlExpression $login)
	 * @method bool hasLogin()
	 * @method bool isLoginFilled()
	 * @method bool isLoginChanged()
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
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail set($fieldName, $value)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail reset($fieldName)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\Internals\EO_DomainEmail wakeUp($data)
	 */
	class EO_DomainEmail {
		/* @var \Bitrix\Mail\Internals\DomainEmailTable */
		static public $dataClass = '\Bitrix\Mail\Internals\DomainEmailTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * EO_DomainEmail_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getDomainList()
	 * @method \string[] getLoginList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\Internals\EO_DomainEmail $object)
	 * @method bool has(\Bitrix\Mail\Internals\EO_DomainEmail $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail getByPrimary($primary)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail[] getAll()
	 * @method bool remove(\Bitrix\Mail\Internals\EO_DomainEmail $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\Internals\EO_DomainEmail_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_DomainEmail_Collection merge(?EO_DomainEmail_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_DomainEmail_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\Internals\DomainEmailTable */
		static public $dataClass = '\Bitrix\Mail\Internals\DomainEmailTable';
	}
}
namespace Bitrix\Mail\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DomainEmail_Result exec()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DomainEmail_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail fetchObject()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail_Collection fetchCollection()
	 */
	class EO_DomainEmail_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail_Collection createCollection()
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail wakeUpObject($row)
	 * @method \Bitrix\Mail\Internals\EO_DomainEmail_Collection wakeUpCollection($rows)
	 */
	class EO_DomainEmail_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\MailFilterTable:mail/lib/mailfilter.php */
namespace Bitrix\Mail {
	/**
	 * EO_MailFilter
	 * @see \Bitrix\Mail\MailFilterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\EO_MailFilter setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Mail\EO_MailFilter setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Mail\EO_MailFilter resetTimestampX()
	 * @method \Bitrix\Mail\EO_MailFilter unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\EO_MailFilter setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\EO_MailFilter resetMailboxId()
	 * @method \Bitrix\Mail\EO_MailFilter unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \int getParentFilterId()
	 * @method \Bitrix\Mail\EO_MailFilter setParentFilterId(\int|\Bitrix\Main\DB\SqlExpression $parentFilterId)
	 * @method bool hasParentFilterId()
	 * @method bool isParentFilterIdFilled()
	 * @method bool isParentFilterIdChanged()
	 * @method \int remindActualParentFilterId()
	 * @method \int requireParentFilterId()
	 * @method \Bitrix\Mail\EO_MailFilter resetParentFilterId()
	 * @method \Bitrix\Mail\EO_MailFilter unsetParentFilterId()
	 * @method \int fillParentFilterId()
	 * @method \string getName()
	 * @method \Bitrix\Mail\EO_MailFilter setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Mail\EO_MailFilter resetName()
	 * @method \Bitrix\Mail\EO_MailFilter unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Mail\EO_MailFilter setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Mail\EO_MailFilter resetDescription()
	 * @method \Bitrix\Mail\EO_MailFilter unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getSort()
	 * @method \Bitrix\Mail\EO_MailFilter setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Mail\EO_MailFilter resetSort()
	 * @method \Bitrix\Mail\EO_MailFilter unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Mail\EO_MailFilter setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Mail\EO_MailFilter resetActive()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getPhpCondition()
	 * @method \Bitrix\Mail\EO_MailFilter setPhpCondition(\string|\Bitrix\Main\DB\SqlExpression $phpCondition)
	 * @method bool hasPhpCondition()
	 * @method bool isPhpConditionFilled()
	 * @method bool isPhpConditionChanged()
	 * @method \string remindActualPhpCondition()
	 * @method \string requirePhpCondition()
	 * @method \Bitrix\Mail\EO_MailFilter resetPhpCondition()
	 * @method \Bitrix\Mail\EO_MailFilter unsetPhpCondition()
	 * @method \string fillPhpCondition()
	 * @method \boolean getWhenMailReceived()
	 * @method \Bitrix\Mail\EO_MailFilter setWhenMailReceived(\boolean|\Bitrix\Main\DB\SqlExpression $whenMailReceived)
	 * @method bool hasWhenMailReceived()
	 * @method bool isWhenMailReceivedFilled()
	 * @method bool isWhenMailReceivedChanged()
	 * @method \boolean remindActualWhenMailReceived()
	 * @method \boolean requireWhenMailReceived()
	 * @method \Bitrix\Mail\EO_MailFilter resetWhenMailReceived()
	 * @method \Bitrix\Mail\EO_MailFilter unsetWhenMailReceived()
	 * @method \boolean fillWhenMailReceived()
	 * @method \boolean getWhenManuallyRun()
	 * @method \Bitrix\Mail\EO_MailFilter setWhenManuallyRun(\boolean|\Bitrix\Main\DB\SqlExpression $whenManuallyRun)
	 * @method bool hasWhenManuallyRun()
	 * @method bool isWhenManuallyRunFilled()
	 * @method bool isWhenManuallyRunChanged()
	 * @method \boolean remindActualWhenManuallyRun()
	 * @method \boolean requireWhenManuallyRun()
	 * @method \Bitrix\Mail\EO_MailFilter resetWhenManuallyRun()
	 * @method \Bitrix\Mail\EO_MailFilter unsetWhenManuallyRun()
	 * @method \boolean fillWhenManuallyRun()
	 * @method \float getSpamRating()
	 * @method \Bitrix\Mail\EO_MailFilter setSpamRating(\float|\Bitrix\Main\DB\SqlExpression $spamRating)
	 * @method bool hasSpamRating()
	 * @method bool isSpamRatingFilled()
	 * @method bool isSpamRatingChanged()
	 * @method \float remindActualSpamRating()
	 * @method \float requireSpamRating()
	 * @method \Bitrix\Mail\EO_MailFilter resetSpamRating()
	 * @method \Bitrix\Mail\EO_MailFilter unsetSpamRating()
	 * @method \float fillSpamRating()
	 * @method \string getSpamRatingType()
	 * @method \Bitrix\Mail\EO_MailFilter setSpamRatingType(\string|\Bitrix\Main\DB\SqlExpression $spamRatingType)
	 * @method bool hasSpamRatingType()
	 * @method bool isSpamRatingTypeFilled()
	 * @method bool isSpamRatingTypeChanged()
	 * @method \string remindActualSpamRatingType()
	 * @method \string requireSpamRatingType()
	 * @method \Bitrix\Mail\EO_MailFilter resetSpamRatingType()
	 * @method \Bitrix\Mail\EO_MailFilter unsetSpamRatingType()
	 * @method \string fillSpamRatingType()
	 * @method \int getMessageSize()
	 * @method \Bitrix\Mail\EO_MailFilter setMessageSize(\int|\Bitrix\Main\DB\SqlExpression $messageSize)
	 * @method bool hasMessageSize()
	 * @method bool isMessageSizeFilled()
	 * @method bool isMessageSizeChanged()
	 * @method \int remindActualMessageSize()
	 * @method \int requireMessageSize()
	 * @method \Bitrix\Mail\EO_MailFilter resetMessageSize()
	 * @method \Bitrix\Mail\EO_MailFilter unsetMessageSize()
	 * @method \int fillMessageSize()
	 * @method \string getMessageSizeType()
	 * @method \Bitrix\Mail\EO_MailFilter setMessageSizeType(\string|\Bitrix\Main\DB\SqlExpression $messageSizeType)
	 * @method bool hasMessageSizeType()
	 * @method bool isMessageSizeTypeFilled()
	 * @method bool isMessageSizeTypeChanged()
	 * @method \string remindActualMessageSizeType()
	 * @method \string requireMessageSizeType()
	 * @method \Bitrix\Mail\EO_MailFilter resetMessageSizeType()
	 * @method \Bitrix\Mail\EO_MailFilter unsetMessageSizeType()
	 * @method \string fillMessageSizeType()
	 * @method \string getMessageSizeUnit()
	 * @method \Bitrix\Mail\EO_MailFilter setMessageSizeUnit(\string|\Bitrix\Main\DB\SqlExpression $messageSizeUnit)
	 * @method bool hasMessageSizeUnit()
	 * @method bool isMessageSizeUnitFilled()
	 * @method bool isMessageSizeUnitChanged()
	 * @method \string remindActualMessageSizeUnit()
	 * @method \string requireMessageSizeUnit()
	 * @method \Bitrix\Mail\EO_MailFilter resetMessageSizeUnit()
	 * @method \Bitrix\Mail\EO_MailFilter unsetMessageSizeUnit()
	 * @method \string fillMessageSizeUnit()
	 * @method \boolean getActionStopExec()
	 * @method \Bitrix\Mail\EO_MailFilter setActionStopExec(\boolean|\Bitrix\Main\DB\SqlExpression $actionStopExec)
	 * @method bool hasActionStopExec()
	 * @method bool isActionStopExecFilled()
	 * @method bool isActionStopExecChanged()
	 * @method \boolean remindActualActionStopExec()
	 * @method \boolean requireActionStopExec()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionStopExec()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionStopExec()
	 * @method \boolean fillActionStopExec()
	 * @method \boolean getActionDeleteMessage()
	 * @method \Bitrix\Mail\EO_MailFilter setActionDeleteMessage(\boolean|\Bitrix\Main\DB\SqlExpression $actionDeleteMessage)
	 * @method bool hasActionDeleteMessage()
	 * @method bool isActionDeleteMessageFilled()
	 * @method bool isActionDeleteMessageChanged()
	 * @method \boolean remindActualActionDeleteMessage()
	 * @method \boolean requireActionDeleteMessage()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionDeleteMessage()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionDeleteMessage()
	 * @method \boolean fillActionDeleteMessage()
	 * @method \string getActionRead()
	 * @method \Bitrix\Mail\EO_MailFilter setActionRead(\string|\Bitrix\Main\DB\SqlExpression $actionRead)
	 * @method bool hasActionRead()
	 * @method bool isActionReadFilled()
	 * @method bool isActionReadChanged()
	 * @method \string remindActualActionRead()
	 * @method \string requireActionRead()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionRead()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionRead()
	 * @method \string fillActionRead()
	 * @method \string getActionPhp()
	 * @method \Bitrix\Mail\EO_MailFilter setActionPhp(\string|\Bitrix\Main\DB\SqlExpression $actionPhp)
	 * @method bool hasActionPhp()
	 * @method bool isActionPhpFilled()
	 * @method bool isActionPhpChanged()
	 * @method \string remindActualActionPhp()
	 * @method \string requireActionPhp()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionPhp()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionPhp()
	 * @method \string fillActionPhp()
	 * @method \string getActionType()
	 * @method \Bitrix\Mail\EO_MailFilter setActionType(\string|\Bitrix\Main\DB\SqlExpression $actionType)
	 * @method bool hasActionType()
	 * @method bool isActionTypeFilled()
	 * @method bool isActionTypeChanged()
	 * @method \string remindActualActionType()
	 * @method \string requireActionType()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionType()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionType()
	 * @method \string fillActionType()
	 * @method \string getActionVars()
	 * @method \Bitrix\Mail\EO_MailFilter setActionVars(\string|\Bitrix\Main\DB\SqlExpression $actionVars)
	 * @method bool hasActionVars()
	 * @method bool isActionVarsFilled()
	 * @method bool isActionVarsChanged()
	 * @method \string remindActualActionVars()
	 * @method \string requireActionVars()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionVars()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionVars()
	 * @method \string fillActionVars()
	 * @method \string getActionSpam()
	 * @method \Bitrix\Mail\EO_MailFilter setActionSpam(\string|\Bitrix\Main\DB\SqlExpression $actionSpam)
	 * @method bool hasActionSpam()
	 * @method bool isActionSpamFilled()
	 * @method bool isActionSpamChanged()
	 * @method \string remindActualActionSpam()
	 * @method \string requireActionSpam()
	 * @method \Bitrix\Mail\EO_MailFilter resetActionSpam()
	 * @method \Bitrix\Mail\EO_MailFilter unsetActionSpam()
	 * @method \string fillActionSpam()
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
	 * @method \Bitrix\Mail\EO_MailFilter set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_MailFilter reset($fieldName)
	 * @method \Bitrix\Mail\EO_MailFilter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_MailFilter wakeUp($data)
	 */
	class EO_MailFilter {
		/* @var \Bitrix\Mail\MailFilterTable */
		static public $dataClass = '\Bitrix\Mail\MailFilterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_MailFilter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \int[] getParentFilterIdList()
	 * @method \int[] fillParentFilterId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getPhpConditionList()
	 * @method \string[] fillPhpCondition()
	 * @method \boolean[] getWhenMailReceivedList()
	 * @method \boolean[] fillWhenMailReceived()
	 * @method \boolean[] getWhenManuallyRunList()
	 * @method \boolean[] fillWhenManuallyRun()
	 * @method \float[] getSpamRatingList()
	 * @method \float[] fillSpamRating()
	 * @method \string[] getSpamRatingTypeList()
	 * @method \string[] fillSpamRatingType()
	 * @method \int[] getMessageSizeList()
	 * @method \int[] fillMessageSize()
	 * @method \string[] getMessageSizeTypeList()
	 * @method \string[] fillMessageSizeType()
	 * @method \string[] getMessageSizeUnitList()
	 * @method \string[] fillMessageSizeUnit()
	 * @method \boolean[] getActionStopExecList()
	 * @method \boolean[] fillActionStopExec()
	 * @method \boolean[] getActionDeleteMessageList()
	 * @method \boolean[] fillActionDeleteMessage()
	 * @method \string[] getActionReadList()
	 * @method \string[] fillActionRead()
	 * @method \string[] getActionPhpList()
	 * @method \string[] fillActionPhp()
	 * @method \string[] getActionTypeList()
	 * @method \string[] fillActionType()
	 * @method \string[] getActionVarsList()
	 * @method \string[] fillActionVars()
	 * @method \string[] getActionSpamList()
	 * @method \string[] fillActionSpam()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_MailFilter $object)
	 * @method bool has(\Bitrix\Mail\EO_MailFilter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailFilter getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailFilter[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_MailFilter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_MailFilter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_MailFilter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailFilter_Collection merge(?EO_MailFilter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailFilter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailFilterTable */
		static public $dataClass = '\Bitrix\Mail\MailFilterTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailFilter_Result exec()
	 * @method \Bitrix\Mail\EO_MailFilter fetchObject()
	 * @method \Bitrix\Mail\EO_MailFilter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailFilter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_MailFilter fetchObject()
	 * @method \Bitrix\Mail\EO_MailFilter_Collection fetchCollection()
	 */
	class EO_MailFilter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_MailFilter createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_MailFilter_Collection createCollection()
	 * @method \Bitrix\Mail\EO_MailFilter wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_MailFilter_Collection wakeUpCollection($rows)
	 */
	class EO_MailFilter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\MailboxTable:mail/lib/mailbox.php */
namespace Bitrix\Mail {
	/**
	 * EO_Mailbox
	 * @see \Bitrix\Mail\MailboxTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\EO_Mailbox setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Mail\EO_Mailbox setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Mail\EO_Mailbox resetTimestampX()
	 * @method \Bitrix\Mail\EO_Mailbox unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getLid()
	 * @method \Bitrix\Mail\EO_Mailbox setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Mail\EO_Mailbox resetLid()
	 * @method \Bitrix\Mail\EO_Mailbox unsetLid()
	 * @method \string fillLid()
	 * @method \boolean getActive()
	 * @method \Bitrix\Mail\EO_Mailbox setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Mail\EO_Mailbox resetActive()
	 * @method \Bitrix\Mail\EO_Mailbox unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getServiceId()
	 * @method \Bitrix\Mail\EO_Mailbox setServiceId(\int|\Bitrix\Main\DB\SqlExpression $serviceId)
	 * @method bool hasServiceId()
	 * @method bool isServiceIdFilled()
	 * @method bool isServiceIdChanged()
	 * @method \int remindActualServiceId()
	 * @method \int requireServiceId()
	 * @method \Bitrix\Mail\EO_Mailbox resetServiceId()
	 * @method \Bitrix\Mail\EO_Mailbox unsetServiceId()
	 * @method \int fillServiceId()
	 * @method \string getEmail()
	 * @method \Bitrix\Mail\EO_Mailbox setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Mail\EO_Mailbox resetEmail()
	 * @method \Bitrix\Mail\EO_Mailbox unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getUsername()
	 * @method \Bitrix\Mail\EO_Mailbox setUsername(\string|\Bitrix\Main\DB\SqlExpression $username)
	 * @method bool hasUsername()
	 * @method bool isUsernameFilled()
	 * @method bool isUsernameChanged()
	 * @method \string remindActualUsername()
	 * @method \string requireUsername()
	 * @method \Bitrix\Mail\EO_Mailbox resetUsername()
	 * @method \Bitrix\Mail\EO_Mailbox unsetUsername()
	 * @method \string fillUsername()
	 * @method \string getName()
	 * @method \Bitrix\Mail\EO_Mailbox setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Mail\EO_Mailbox resetName()
	 * @method \Bitrix\Mail\EO_Mailbox unsetName()
	 * @method \string fillName()
	 * @method \string getServer()
	 * @method \Bitrix\Mail\EO_Mailbox setServer(\string|\Bitrix\Main\DB\SqlExpression $server)
	 * @method bool hasServer()
	 * @method bool isServerFilled()
	 * @method bool isServerChanged()
	 * @method \string remindActualServer()
	 * @method \string requireServer()
	 * @method \Bitrix\Mail\EO_Mailbox resetServer()
	 * @method \Bitrix\Mail\EO_Mailbox unsetServer()
	 * @method \string fillServer()
	 * @method \int getPort()
	 * @method \Bitrix\Mail\EO_Mailbox setPort(\int|\Bitrix\Main\DB\SqlExpression $port)
	 * @method bool hasPort()
	 * @method bool isPortFilled()
	 * @method bool isPortChanged()
	 * @method \int remindActualPort()
	 * @method \int requirePort()
	 * @method \Bitrix\Mail\EO_Mailbox resetPort()
	 * @method \Bitrix\Mail\EO_Mailbox unsetPort()
	 * @method \int fillPort()
	 * @method \string getLink()
	 * @method \Bitrix\Mail\EO_Mailbox setLink(\string|\Bitrix\Main\DB\SqlExpression $link)
	 * @method bool hasLink()
	 * @method bool isLinkFilled()
	 * @method bool isLinkChanged()
	 * @method \string remindActualLink()
	 * @method \string requireLink()
	 * @method \Bitrix\Mail\EO_Mailbox resetLink()
	 * @method \Bitrix\Mail\EO_Mailbox unsetLink()
	 * @method \string fillLink()
	 * @method \string getLogin()
	 * @method \Bitrix\Mail\EO_Mailbox setLogin(\string|\Bitrix\Main\DB\SqlExpression $login)
	 * @method bool hasLogin()
	 * @method bool isLoginFilled()
	 * @method bool isLoginChanged()
	 * @method \string remindActualLogin()
	 * @method \string requireLogin()
	 * @method \Bitrix\Mail\EO_Mailbox resetLogin()
	 * @method \Bitrix\Mail\EO_Mailbox unsetLogin()
	 * @method \string fillLogin()
	 * @method \string getCharset()
	 * @method \Bitrix\Mail\EO_Mailbox setCharset(\string|\Bitrix\Main\DB\SqlExpression $charset)
	 * @method bool hasCharset()
	 * @method bool isCharsetFilled()
	 * @method bool isCharsetChanged()
	 * @method \string remindActualCharset()
	 * @method \string requireCharset()
	 * @method \Bitrix\Mail\EO_Mailbox resetCharset()
	 * @method \Bitrix\Mail\EO_Mailbox unsetCharset()
	 * @method \string fillCharset()
	 * @method \string getPassword()
	 * @method \Bitrix\Mail\EO_Mailbox setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Mail\EO_Mailbox resetPassword()
	 * @method \Bitrix\Mail\EO_Mailbox unsetPassword()
	 * @method \string fillPassword()
	 * @method \string getDescription()
	 * @method \Bitrix\Mail\EO_Mailbox setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Mail\EO_Mailbox resetDescription()
	 * @method \Bitrix\Mail\EO_Mailbox unsetDescription()
	 * @method \string fillDescription()
	 * @method \boolean getUseMd5()
	 * @method \Bitrix\Mail\EO_Mailbox setUseMd5(\boolean|\Bitrix\Main\DB\SqlExpression $useMd5)
	 * @method bool hasUseMd5()
	 * @method bool isUseMd5Filled()
	 * @method bool isUseMd5Changed()
	 * @method \boolean remindActualUseMd5()
	 * @method \boolean requireUseMd5()
	 * @method \Bitrix\Mail\EO_Mailbox resetUseMd5()
	 * @method \Bitrix\Mail\EO_Mailbox unsetUseMd5()
	 * @method \boolean fillUseMd5()
	 * @method \boolean getDeleteMessages()
	 * @method \Bitrix\Mail\EO_Mailbox setDeleteMessages(\boolean|\Bitrix\Main\DB\SqlExpression $deleteMessages)
	 * @method bool hasDeleteMessages()
	 * @method bool isDeleteMessagesFilled()
	 * @method bool isDeleteMessagesChanged()
	 * @method \boolean remindActualDeleteMessages()
	 * @method \boolean requireDeleteMessages()
	 * @method \Bitrix\Mail\EO_Mailbox resetDeleteMessages()
	 * @method \Bitrix\Mail\EO_Mailbox unsetDeleteMessages()
	 * @method \boolean fillDeleteMessages()
	 * @method \int getPeriodCheck()
	 * @method \Bitrix\Mail\EO_Mailbox setPeriodCheck(\int|\Bitrix\Main\DB\SqlExpression $periodCheck)
	 * @method bool hasPeriodCheck()
	 * @method bool isPeriodCheckFilled()
	 * @method bool isPeriodCheckChanged()
	 * @method \int remindActualPeriodCheck()
	 * @method \int requirePeriodCheck()
	 * @method \Bitrix\Mail\EO_Mailbox resetPeriodCheck()
	 * @method \Bitrix\Mail\EO_Mailbox unsetPeriodCheck()
	 * @method \int fillPeriodCheck()
	 * @method \int getMaxMsgCount()
	 * @method \Bitrix\Mail\EO_Mailbox setMaxMsgCount(\int|\Bitrix\Main\DB\SqlExpression $maxMsgCount)
	 * @method bool hasMaxMsgCount()
	 * @method bool isMaxMsgCountFilled()
	 * @method bool isMaxMsgCountChanged()
	 * @method \int remindActualMaxMsgCount()
	 * @method \int requireMaxMsgCount()
	 * @method \Bitrix\Mail\EO_Mailbox resetMaxMsgCount()
	 * @method \Bitrix\Mail\EO_Mailbox unsetMaxMsgCount()
	 * @method \int fillMaxMsgCount()
	 * @method \int getMaxMsgSize()
	 * @method \Bitrix\Mail\EO_Mailbox setMaxMsgSize(\int|\Bitrix\Main\DB\SqlExpression $maxMsgSize)
	 * @method bool hasMaxMsgSize()
	 * @method bool isMaxMsgSizeFilled()
	 * @method bool isMaxMsgSizeChanged()
	 * @method \int remindActualMaxMsgSize()
	 * @method \int requireMaxMsgSize()
	 * @method \Bitrix\Mail\EO_Mailbox resetMaxMsgSize()
	 * @method \Bitrix\Mail\EO_Mailbox unsetMaxMsgSize()
	 * @method \int fillMaxMsgSize()
	 * @method \int getMaxKeepDays()
	 * @method \Bitrix\Mail\EO_Mailbox setMaxKeepDays(\int|\Bitrix\Main\DB\SqlExpression $maxKeepDays)
	 * @method bool hasMaxKeepDays()
	 * @method bool isMaxKeepDaysFilled()
	 * @method bool isMaxKeepDaysChanged()
	 * @method \int remindActualMaxKeepDays()
	 * @method \int requireMaxKeepDays()
	 * @method \Bitrix\Mail\EO_Mailbox resetMaxKeepDays()
	 * @method \Bitrix\Mail\EO_Mailbox unsetMaxKeepDays()
	 * @method \int fillMaxKeepDays()
	 * @method \string getUseTls()
	 * @method \Bitrix\Mail\EO_Mailbox setUseTls(\string|\Bitrix\Main\DB\SqlExpression $useTls)
	 * @method bool hasUseTls()
	 * @method bool isUseTlsFilled()
	 * @method bool isUseTlsChanged()
	 * @method \string remindActualUseTls()
	 * @method \string requireUseTls()
	 * @method \Bitrix\Mail\EO_Mailbox resetUseTls()
	 * @method \Bitrix\Mail\EO_Mailbox unsetUseTls()
	 * @method \string fillUseTls()
	 * @method \string getServerType()
	 * @method \Bitrix\Mail\EO_Mailbox setServerType(\string|\Bitrix\Main\DB\SqlExpression $serverType)
	 * @method bool hasServerType()
	 * @method bool isServerTypeFilled()
	 * @method bool isServerTypeChanged()
	 * @method \string remindActualServerType()
	 * @method \string requireServerType()
	 * @method \Bitrix\Mail\EO_Mailbox resetServerType()
	 * @method \Bitrix\Mail\EO_Mailbox unsetServerType()
	 * @method \string fillServerType()
	 * @method \string getDomains()
	 * @method \Bitrix\Mail\EO_Mailbox setDomains(\string|\Bitrix\Main\DB\SqlExpression $domains)
	 * @method bool hasDomains()
	 * @method bool isDomainsFilled()
	 * @method bool isDomainsChanged()
	 * @method \string remindActualDomains()
	 * @method \string requireDomains()
	 * @method \Bitrix\Mail\EO_Mailbox resetDomains()
	 * @method \Bitrix\Mail\EO_Mailbox unsetDomains()
	 * @method \string fillDomains()
	 * @method \boolean getRelay()
	 * @method \Bitrix\Mail\EO_Mailbox setRelay(\boolean|\Bitrix\Main\DB\SqlExpression $relay)
	 * @method bool hasRelay()
	 * @method bool isRelayFilled()
	 * @method bool isRelayChanged()
	 * @method \boolean remindActualRelay()
	 * @method \boolean requireRelay()
	 * @method \Bitrix\Mail\EO_Mailbox resetRelay()
	 * @method \Bitrix\Mail\EO_Mailbox unsetRelay()
	 * @method \boolean fillRelay()
	 * @method \boolean getAuthRelay()
	 * @method \Bitrix\Mail\EO_Mailbox setAuthRelay(\boolean|\Bitrix\Main\DB\SqlExpression $authRelay)
	 * @method bool hasAuthRelay()
	 * @method bool isAuthRelayFilled()
	 * @method bool isAuthRelayChanged()
	 * @method \boolean remindActualAuthRelay()
	 * @method \boolean requireAuthRelay()
	 * @method \Bitrix\Mail\EO_Mailbox resetAuthRelay()
	 * @method \Bitrix\Mail\EO_Mailbox unsetAuthRelay()
	 * @method \boolean fillAuthRelay()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\EO_Mailbox setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\EO_Mailbox resetUserId()
	 * @method \Bitrix\Mail\EO_Mailbox unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getSyncLock()
	 * @method \Bitrix\Mail\EO_Mailbox setSyncLock(\int|\Bitrix\Main\DB\SqlExpression $syncLock)
	 * @method bool hasSyncLock()
	 * @method bool isSyncLockFilled()
	 * @method bool isSyncLockChanged()
	 * @method \int remindActualSyncLock()
	 * @method \int requireSyncLock()
	 * @method \Bitrix\Mail\EO_Mailbox resetSyncLock()
	 * @method \Bitrix\Mail\EO_Mailbox unsetSyncLock()
	 * @method \int fillSyncLock()
	 * @method \string getOptions()
	 * @method \Bitrix\Mail\EO_Mailbox setOptions(\string|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \string remindActualOptions()
	 * @method \string requireOptions()
	 * @method \Bitrix\Mail\EO_Mailbox resetOptions()
	 * @method \Bitrix\Mail\EO_Mailbox unsetOptions()
	 * @method \string fillOptions()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Mail\EO_Mailbox setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Mail\EO_Mailbox resetSite()
	 * @method \Bitrix\Mail\EO_Mailbox unsetSite()
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
	 * @method \Bitrix\Mail\EO_Mailbox set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_Mailbox reset($fieldName)
	 * @method \Bitrix\Mail\EO_Mailbox unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_Mailbox wakeUp($data)
	 */
	class EO_Mailbox {
		/* @var \Bitrix\Mail\MailboxTable */
		static public $dataClass = '\Bitrix\Mail\MailboxTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_Mailbox_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getServiceIdList()
	 * @method \int[] fillServiceId()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getUsernameList()
	 * @method \string[] fillUsername()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getServerList()
	 * @method \string[] fillServer()
	 * @method \int[] getPortList()
	 * @method \int[] fillPort()
	 * @method \string[] getLinkList()
	 * @method \string[] fillLink()
	 * @method \string[] getLoginList()
	 * @method \string[] fillLogin()
	 * @method \string[] getCharsetList()
	 * @method \string[] fillCharset()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \boolean[] getUseMd5List()
	 * @method \boolean[] fillUseMd5()
	 * @method \boolean[] getDeleteMessagesList()
	 * @method \boolean[] fillDeleteMessages()
	 * @method \int[] getPeriodCheckList()
	 * @method \int[] fillPeriodCheck()
	 * @method \int[] getMaxMsgCountList()
	 * @method \int[] fillMaxMsgCount()
	 * @method \int[] getMaxMsgSizeList()
	 * @method \int[] fillMaxMsgSize()
	 * @method \int[] getMaxKeepDaysList()
	 * @method \int[] fillMaxKeepDays()
	 * @method \string[] getUseTlsList()
	 * @method \string[] fillUseTls()
	 * @method \string[] getServerTypeList()
	 * @method \string[] fillServerType()
	 * @method \string[] getDomainsList()
	 * @method \string[] fillDomains()
	 * @method \boolean[] getRelayList()
	 * @method \boolean[] fillRelay()
	 * @method \boolean[] getAuthRelayList()
	 * @method \boolean[] fillAuthRelay()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getSyncLockList()
	 * @method \int[] fillSyncLock()
	 * @method \string[] getOptionsList()
	 * @method \string[] fillOptions()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_Mailbox $object)
	 * @method bool has(\Bitrix\Mail\EO_Mailbox $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_Mailbox getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_Mailbox[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_Mailbox $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_Mailbox_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_Mailbox current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Mailbox_Collection merge(?EO_Mailbox_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Mailbox_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailboxTable */
		static public $dataClass = '\Bitrix\Mail\MailboxTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Mailbox_Result exec()
	 * @method \Bitrix\Mail\EO_Mailbox fetchObject()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Mailbox_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_Mailbox fetchObject()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection fetchCollection()
	 */
	class EO_Mailbox_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_Mailbox createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_Mailbox_Collection createCollection()
	 * @method \Bitrix\Mail\EO_Mailbox wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_Mailbox_Collection wakeUpCollection($rows)
	 */
	class EO_Mailbox_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\MailMessageUidTable:mail/lib/mailmessageuid.php */
namespace Bitrix\Mail {
	/**
	 * EO_MailMessageUid
	 * @see \Bitrix\Mail\MailMessageUidTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Mail\EO_MailMessageUid setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\EO_MailMessageUid setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \string getDirMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid setDirMd5(\string|\Bitrix\Main\DB\SqlExpression $dirMd5)
	 * @method bool hasDirMd5()
	 * @method bool isDirMd5Filled()
	 * @method bool isDirMd5Changed()
	 * @method \string remindActualDirMd5()
	 * @method \string requireDirMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetDirMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetDirMd5()
	 * @method \string fillDirMd5()
	 * @method \int getDirUidv()
	 * @method \Bitrix\Mail\EO_MailMessageUid setDirUidv(\int|\Bitrix\Main\DB\SqlExpression $dirUidv)
	 * @method bool hasDirUidv()
	 * @method bool isDirUidvFilled()
	 * @method bool isDirUidvChanged()
	 * @method \int remindActualDirUidv()
	 * @method \int requireDirUidv()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetDirUidv()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetDirUidv()
	 * @method \int fillDirUidv()
	 * @method \int getMsgUid()
	 * @method \Bitrix\Mail\EO_MailMessageUid setMsgUid(\int|\Bitrix\Main\DB\SqlExpression $msgUid)
	 * @method bool hasMsgUid()
	 * @method bool isMsgUidFilled()
	 * @method bool isMsgUidChanged()
	 * @method \int remindActualMsgUid()
	 * @method \int requireMsgUid()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetMsgUid()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetMsgUid()
	 * @method \int fillMsgUid()
	 * @method \Bitrix\Main\Type\DateTime getInternaldate()
	 * @method \Bitrix\Mail\EO_MailMessageUid setInternaldate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $internaldate)
	 * @method bool hasInternaldate()
	 * @method bool isInternaldateFilled()
	 * @method bool isInternaldateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualInternaldate()
	 * @method \Bitrix\Main\Type\DateTime requireInternaldate()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetInternaldate()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetInternaldate()
	 * @method \Bitrix\Main\Type\DateTime fillInternaldate()
	 * @method \string getHeaderMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid setHeaderMd5(\string|\Bitrix\Main\DB\SqlExpression $headerMd5)
	 * @method bool hasHeaderMd5()
	 * @method bool isHeaderMd5Filled()
	 * @method bool isHeaderMd5Changed()
	 * @method \string remindActualHeaderMd5()
	 * @method \string requireHeaderMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetHeaderMd5()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetHeaderMd5()
	 * @method \string fillHeaderMd5()
	 * @method \string getIsSeen()
	 * @method \Bitrix\Mail\EO_MailMessageUid setIsSeen(\string|\Bitrix\Main\DB\SqlExpression $isSeen)
	 * @method bool hasIsSeen()
	 * @method bool isIsSeenFilled()
	 * @method bool isIsSeenChanged()
	 * @method \string remindActualIsSeen()
	 * @method \string requireIsSeen()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetIsSeen()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetIsSeen()
	 * @method \string fillIsSeen()
	 * @method \string getIsOld()
	 * @method \Bitrix\Mail\EO_MailMessageUid setIsOld(\string|\Bitrix\Main\DB\SqlExpression $isOld)
	 * @method bool hasIsOld()
	 * @method bool isIsOldFilled()
	 * @method bool isIsOldChanged()
	 * @method \string remindActualIsOld()
	 * @method \string requireIsOld()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetIsOld()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetIsOld()
	 * @method \string fillIsOld()
	 * @method \string getSessionId()
	 * @method \Bitrix\Mail\EO_MailMessageUid setSessionId(\string|\Bitrix\Main\DB\SqlExpression $sessionId)
	 * @method bool hasSessionId()
	 * @method bool isSessionIdFilled()
	 * @method bool isSessionIdChanged()
	 * @method \string remindActualSessionId()
	 * @method \string requireSessionId()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetSessionId()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetSessionId()
	 * @method \string fillSessionId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Mail\EO_MailMessageUid setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetTimestampX()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessageUid setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \int getMessageId()
	 * @method \Bitrix\Mail\EO_MailMessageUid setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetMessageId()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \Bitrix\Mail\EO_Mailbox getMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox remindActualMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox requireMailbox()
	 * @method \Bitrix\Mail\EO_MailMessageUid setMailbox(\Bitrix\Mail\EO_Mailbox $object)
	 * @method \Bitrix\Mail\EO_MailMessageUid resetMailbox()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetMailbox()
	 * @method bool hasMailbox()
	 * @method bool isMailboxFilled()
	 * @method bool isMailboxChanged()
	 * @method \Bitrix\Mail\EO_Mailbox fillMailbox()
	 * @method \Bitrix\Mail\EO_MailMessage getMessage()
	 * @method \Bitrix\Mail\EO_MailMessage remindActualMessage()
	 * @method \Bitrix\Mail\EO_MailMessage requireMessage()
	 * @method \Bitrix\Mail\EO_MailMessageUid setMessage(\Bitrix\Mail\EO_MailMessage $object)
	 * @method \Bitrix\Mail\EO_MailMessageUid resetMessage()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetMessage()
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \Bitrix\Mail\EO_MailMessage fillMessage()
	 * @method \int getDeleteTime()
	 * @method \Bitrix\Mail\EO_MailMessageUid setDeleteTime(\int|\Bitrix\Main\DB\SqlExpression $deleteTime)
	 * @method bool hasDeleteTime()
	 * @method bool isDeleteTimeFilled()
	 * @method bool isDeleteTimeChanged()
	 * @method \int remindActualDeleteTime()
	 * @method \int requireDeleteTime()
	 * @method \Bitrix\Mail\EO_MailMessageUid resetDeleteTime()
	 * @method \Bitrix\Mail\EO_MailMessageUid unsetDeleteTime()
	 * @method \int fillDeleteTime()
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
	 * @method \Bitrix\Mail\EO_MailMessageUid set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_MailMessageUid reset($fieldName)
	 * @method \Bitrix\Mail\EO_MailMessageUid unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_MailMessageUid wakeUp($data)
	 */
	class EO_MailMessageUid {
		/* @var \Bitrix\Mail\MailMessageUidTable */
		static public $dataClass = '\Bitrix\Mail\MailMessageUidTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_MailMessageUid_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \string[] getDirMd5List()
	 * @method \string[] fillDirMd5()
	 * @method \int[] getDirUidvList()
	 * @method \int[] fillDirUidv()
	 * @method \int[] getMsgUidList()
	 * @method \int[] fillMsgUid()
	 * @method \Bitrix\Main\Type\DateTime[] getInternaldateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillInternaldate()
	 * @method \string[] getHeaderMd5List()
	 * @method \string[] fillHeaderMd5()
	 * @method \string[] getIsSeenList()
	 * @method \string[] fillIsSeen()
	 * @method \string[] getIsOldList()
	 * @method \string[] fillIsOld()
	 * @method \string[] getSessionIdList()
	 * @method \string[] fillSessionId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \Bitrix\Mail\EO_Mailbox[] getMailboxList()
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection getMailboxCollection()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection fillMailbox()
	 * @method \Bitrix\Mail\EO_MailMessage[] getMessageList()
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection getMessageCollection()
	 * @method \Bitrix\Mail\EO_MailMessage_Collection fillMessage()
	 * @method \int[] getDeleteTimeList()
	 * @method \int[] fillDeleteTime()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_MailMessageUid $object)
	 * @method bool has(\Bitrix\Mail\EO_MailMessageUid $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailMessageUid getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailMessageUid[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_MailMessageUid $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_MailMessageUid_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_MailMessageUid current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailMessageUid_Collection merge(?EO_MailMessageUid_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailMessageUid_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailMessageUidTable */
		static public $dataClass = '\Bitrix\Mail\MailMessageUidTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailMessageUid_Result exec()
	 * @method \Bitrix\Mail\EO_MailMessageUid fetchObject()
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailMessageUid_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_MailMessageUid fetchObject()
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection fetchCollection()
	 */
	class EO_MailMessageUid_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_MailMessageUid createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection createCollection()
	 * @method \Bitrix\Mail\EO_MailMessageUid wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_MailMessageUid_Collection wakeUpCollection($rows)
	 */
	class EO_MailMessageUid_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\User\MessageTable:mail/lib/user/message.php */
namespace Bitrix\Mail\User {
	/**
	 * EO_Message
	 * @see \Bitrix\Mail\User\MessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\User\EO_Message setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getType()
	 * @method \Bitrix\Mail\User\EO_Message setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Mail\User\EO_Message resetType()
	 * @method \Bitrix\Mail\User\EO_Message unsetType()
	 * @method \string fillType()
	 * @method \string getSiteId()
	 * @method \Bitrix\Mail\User\EO_Message setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Mail\User\EO_Message resetSiteId()
	 * @method \Bitrix\Mail\User\EO_Message unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Mail\User\EO_Message setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Mail\User\EO_Message resetEntityType()
	 * @method \Bitrix\Mail\User\EO_Message unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Mail\User\EO_Message setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Mail\User\EO_Message resetEntityId()
	 * @method \Bitrix\Mail\User\EO_Message unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\User\EO_Message setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\User\EO_Message resetUserId()
	 * @method \Bitrix\Mail\User\EO_Message unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getSubject()
	 * @method \Bitrix\Mail\User\EO_Message setSubject(\string|\Bitrix\Main\DB\SqlExpression $subject)
	 * @method bool hasSubject()
	 * @method bool isSubjectFilled()
	 * @method bool isSubjectChanged()
	 * @method \string remindActualSubject()
	 * @method \string requireSubject()
	 * @method \Bitrix\Mail\User\EO_Message resetSubject()
	 * @method \Bitrix\Mail\User\EO_Message unsetSubject()
	 * @method \string fillSubject()
	 * @method \string getContent()
	 * @method \Bitrix\Mail\User\EO_Message setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Mail\User\EO_Message resetContent()
	 * @method \Bitrix\Mail\User\EO_Message unsetContent()
	 * @method \string fillContent()
	 * @method \string getAttachments()
	 * @method \Bitrix\Mail\User\EO_Message setAttachments(\string|\Bitrix\Main\DB\SqlExpression $attachments)
	 * @method bool hasAttachments()
	 * @method bool isAttachmentsFilled()
	 * @method bool isAttachmentsChanged()
	 * @method \string remindActualAttachments()
	 * @method \string requireAttachments()
	 * @method \Bitrix\Mail\User\EO_Message resetAttachments()
	 * @method \Bitrix\Mail\User\EO_Message unsetAttachments()
	 * @method \string fillAttachments()
	 * @method \string getHeaders()
	 * @method \Bitrix\Mail\User\EO_Message setHeaders(\string|\Bitrix\Main\DB\SqlExpression $headers)
	 * @method bool hasHeaders()
	 * @method bool isHeadersFilled()
	 * @method bool isHeadersChanged()
	 * @method \string remindActualHeaders()
	 * @method \string requireHeaders()
	 * @method \Bitrix\Mail\User\EO_Message resetHeaders()
	 * @method \Bitrix\Mail\User\EO_Message unsetHeaders()
	 * @method \string fillHeaders()
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
	 * @method \Bitrix\Mail\User\EO_Message set($fieldName, $value)
	 * @method \Bitrix\Mail\User\EO_Message reset($fieldName)
	 * @method \Bitrix\Mail\User\EO_Message unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\User\EO_Message wakeUp($data)
	 */
	class EO_Message {
		/* @var \Bitrix\Mail\User\MessageTable */
		static public $dataClass = '\Bitrix\Mail\User\MessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail\User {
	/**
	 * EO_Message_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getSubjectList()
	 * @method \string[] fillSubject()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \string[] getAttachmentsList()
	 * @method \string[] fillAttachments()
	 * @method \string[] getHeadersList()
	 * @method \string[] fillHeaders()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\User\EO_Message $object)
	 * @method bool has(\Bitrix\Mail\User\EO_Message $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\User\EO_Message getByPrimary($primary)
	 * @method \Bitrix\Mail\User\EO_Message[] getAll()
	 * @method bool remove(\Bitrix\Mail\User\EO_Message $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\User\EO_Message_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\User\EO_Message current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Message_Collection merge(?EO_Message_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Message_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\User\MessageTable */
		static public $dataClass = '\Bitrix\Mail\User\MessageTable';
	}
}
namespace Bitrix\Mail\User {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Message_Result exec()
	 * @method \Bitrix\Mail\User\EO_Message fetchObject()
	 * @method \Bitrix\Mail\User\EO_Message_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Message_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\User\EO_Message fetchObject()
	 * @method \Bitrix\Mail\User\EO_Message_Collection fetchCollection()
	 */
	class EO_Message_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\User\EO_Message createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\User\EO_Message_Collection createCollection()
	 * @method \Bitrix\Mail\User\EO_Message wakeUpObject($row)
	 * @method \Bitrix\Mail\User\EO_Message_Collection wakeUpCollection($rows)
	 */
	class EO_Message_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\UserRelationsTable:mail/lib/userrelations.php */
namespace Bitrix\Mail {
	/**
	 * EO_UserRelations
	 * @see \Bitrix\Mail\UserRelationsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getToken()
	 * @method \Bitrix\Mail\EO_UserRelations setToken(\string|\Bitrix\Main\DB\SqlExpression $token)
	 * @method bool hasToken()
	 * @method bool isTokenFilled()
	 * @method bool isTokenChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Mail\EO_UserRelations setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Mail\EO_UserRelations resetSiteId()
	 * @method \Bitrix\Mail\EO_UserRelations unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getUserId()
	 * @method \Bitrix\Mail\EO_UserRelations setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Mail\EO_UserRelations resetUserId()
	 * @method \Bitrix\Mail\EO_UserRelations unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getEntityType()
	 * @method \Bitrix\Mail\EO_UserRelations setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Mail\EO_UserRelations resetEntityType()
	 * @method \Bitrix\Mail\EO_UserRelations unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Mail\EO_UserRelations setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Mail\EO_UserRelations resetEntityId()
	 * @method \Bitrix\Mail\EO_UserRelations unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityLink()
	 * @method \Bitrix\Mail\EO_UserRelations setEntityLink(\string|\Bitrix\Main\DB\SqlExpression $entityLink)
	 * @method bool hasEntityLink()
	 * @method bool isEntityLinkFilled()
	 * @method bool isEntityLinkChanged()
	 * @method \string remindActualEntityLink()
	 * @method \string requireEntityLink()
	 * @method \Bitrix\Mail\EO_UserRelations resetEntityLink()
	 * @method \Bitrix\Mail\EO_UserRelations unsetEntityLink()
	 * @method \string fillEntityLink()
	 * @method \string getBackurl()
	 * @method \Bitrix\Mail\EO_UserRelations setBackurl(\string|\Bitrix\Main\DB\SqlExpression $backurl)
	 * @method bool hasBackurl()
	 * @method bool isBackurlFilled()
	 * @method bool isBackurlChanged()
	 * @method \string remindActualBackurl()
	 * @method \string requireBackurl()
	 * @method \Bitrix\Mail\EO_UserRelations resetBackurl()
	 * @method \Bitrix\Mail\EO_UserRelations unsetBackurl()
	 * @method \string fillBackurl()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Mail\EO_UserRelations setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Mail\EO_UserRelations resetUser()
	 * @method \Bitrix\Mail\EO_UserRelations unsetUser()
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
	 * @method \Bitrix\Mail\EO_UserRelations set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_UserRelations reset($fieldName)
	 * @method \Bitrix\Mail\EO_UserRelations unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_UserRelations wakeUp($data)
	 */
	class EO_UserRelations {
		/* @var \Bitrix\Mail\UserRelationsTable */
		static public $dataClass = '\Bitrix\Mail\UserRelationsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_UserRelations_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getTokenList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityLinkList()
	 * @method \string[] fillEntityLink()
	 * @method \string[] getBackurlList()
	 * @method \string[] fillBackurl()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Mail\EO_UserRelations_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_UserRelations $object)
	 * @method bool has(\Bitrix\Mail\EO_UserRelations $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_UserRelations getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_UserRelations[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_UserRelations $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_UserRelations_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_UserRelations current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_UserRelations_Collection merge(?EO_UserRelations_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_UserRelations_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\UserRelationsTable */
		static public $dataClass = '\Bitrix\Mail\UserRelationsTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserRelations_Result exec()
	 * @method \Bitrix\Mail\EO_UserRelations fetchObject()
	 * @method \Bitrix\Mail\EO_UserRelations_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserRelations_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_UserRelations fetchObject()
	 * @method \Bitrix\Mail\EO_UserRelations_Collection fetchCollection()
	 */
	class EO_UserRelations_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_UserRelations createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_UserRelations_Collection createCollection()
	 * @method \Bitrix\Mail\EO_UserRelations wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_UserRelations_Collection wakeUpCollection($rows)
	 */
	class EO_UserRelations_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Mail\MailMessageTable:mail/lib/mailmessage.php */
namespace Bitrix\Mail {
	/**
	 * EO_MailMessage
	 * @see \Bitrix\Mail\MailMessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Mail\EO_MailMessage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailboxId()
	 * @method \Bitrix\Mail\EO_MailMessage setMailboxId(\int|\Bitrix\Main\DB\SqlExpression $mailboxId)
	 * @method bool hasMailboxId()
	 * @method bool isMailboxIdFilled()
	 * @method bool isMailboxIdChanged()
	 * @method \int remindActualMailboxId()
	 * @method \int requireMailboxId()
	 * @method \Bitrix\Mail\EO_MailMessage resetMailboxId()
	 * @method \Bitrix\Mail\EO_MailMessage unsetMailboxId()
	 * @method \int fillMailboxId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessage setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessage resetDateInsert()
	 * @method \Bitrix\Mail\EO_MailMessage unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getFullText()
	 * @method \Bitrix\Mail\EO_MailMessage setFullText(\string|\Bitrix\Main\DB\SqlExpression $fullText)
	 * @method bool hasFullText()
	 * @method bool isFullTextFilled()
	 * @method bool isFullTextChanged()
	 * @method \string remindActualFullText()
	 * @method \string requireFullText()
	 * @method \Bitrix\Mail\EO_MailMessage resetFullText()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFullText()
	 * @method \string fillFullText()
	 * @method \int getMessageSize()
	 * @method \Bitrix\Mail\EO_MailMessage setMessageSize(\int|\Bitrix\Main\DB\SqlExpression $messageSize)
	 * @method bool hasMessageSize()
	 * @method bool isMessageSizeFilled()
	 * @method bool isMessageSizeChanged()
	 * @method \int remindActualMessageSize()
	 * @method \int requireMessageSize()
	 * @method \Bitrix\Mail\EO_MailMessage resetMessageSize()
	 * @method \Bitrix\Mail\EO_MailMessage unsetMessageSize()
	 * @method \int fillMessageSize()
	 * @method \string getHeader()
	 * @method \Bitrix\Mail\EO_MailMessage setHeader(\string|\Bitrix\Main\DB\SqlExpression $header)
	 * @method bool hasHeader()
	 * @method bool isHeaderFilled()
	 * @method bool isHeaderChanged()
	 * @method \string remindActualHeader()
	 * @method \string requireHeader()
	 * @method \Bitrix\Mail\EO_MailMessage resetHeader()
	 * @method \Bitrix\Mail\EO_MailMessage unsetHeader()
	 * @method \string fillHeader()
	 * @method \Bitrix\Main\Type\DateTime getFieldDate()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $fieldDate)
	 * @method bool hasFieldDate()
	 * @method bool isFieldDateFilled()
	 * @method bool isFieldDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFieldDate()
	 * @method \Bitrix\Main\Type\DateTime requireFieldDate()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldDate()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldDate()
	 * @method \Bitrix\Main\Type\DateTime fillFieldDate()
	 * @method \string getFieldFrom()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldFrom(\string|\Bitrix\Main\DB\SqlExpression $fieldFrom)
	 * @method bool hasFieldFrom()
	 * @method bool isFieldFromFilled()
	 * @method bool isFieldFromChanged()
	 * @method \string remindActualFieldFrom()
	 * @method \string requireFieldFrom()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldFrom()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldFrom()
	 * @method \string fillFieldFrom()
	 * @method \string getFieldReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldReplyTo(\string|\Bitrix\Main\DB\SqlExpression $fieldReplyTo)
	 * @method bool hasFieldReplyTo()
	 * @method bool isFieldReplyToFilled()
	 * @method bool isFieldReplyToChanged()
	 * @method \string remindActualFieldReplyTo()
	 * @method \string requireFieldReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldReplyTo()
	 * @method \string fillFieldReplyTo()
	 * @method \string getFieldTo()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldTo(\string|\Bitrix\Main\DB\SqlExpression $fieldTo)
	 * @method bool hasFieldTo()
	 * @method bool isFieldToFilled()
	 * @method bool isFieldToChanged()
	 * @method \string remindActualFieldTo()
	 * @method \string requireFieldTo()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldTo()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldTo()
	 * @method \string fillFieldTo()
	 * @method \string getFieldCc()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldCc(\string|\Bitrix\Main\DB\SqlExpression $fieldCc)
	 * @method bool hasFieldCc()
	 * @method bool isFieldCcFilled()
	 * @method bool isFieldCcChanged()
	 * @method \string remindActualFieldCc()
	 * @method \string requireFieldCc()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldCc()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldCc()
	 * @method \string fillFieldCc()
	 * @method \string getFieldBcc()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldBcc(\string|\Bitrix\Main\DB\SqlExpression $fieldBcc)
	 * @method bool hasFieldBcc()
	 * @method bool isFieldBccFilled()
	 * @method bool isFieldBccChanged()
	 * @method \string remindActualFieldBcc()
	 * @method \string requireFieldBcc()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldBcc()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldBcc()
	 * @method \string fillFieldBcc()
	 * @method \int getFieldPriority()
	 * @method \Bitrix\Mail\EO_MailMessage setFieldPriority(\int|\Bitrix\Main\DB\SqlExpression $fieldPriority)
	 * @method bool hasFieldPriority()
	 * @method bool isFieldPriorityFilled()
	 * @method bool isFieldPriorityChanged()
	 * @method \int remindActualFieldPriority()
	 * @method \int requireFieldPriority()
	 * @method \Bitrix\Mail\EO_MailMessage resetFieldPriority()
	 * @method \Bitrix\Mail\EO_MailMessage unsetFieldPriority()
	 * @method \int fillFieldPriority()
	 * @method \string getSubject()
	 * @method \Bitrix\Mail\EO_MailMessage setSubject(\string|\Bitrix\Main\DB\SqlExpression $subject)
	 * @method bool hasSubject()
	 * @method bool isSubjectFilled()
	 * @method bool isSubjectChanged()
	 * @method \string remindActualSubject()
	 * @method \string requireSubject()
	 * @method \Bitrix\Mail\EO_MailMessage resetSubject()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSubject()
	 * @method \string fillSubject()
	 * @method \string getBody()
	 * @method \Bitrix\Mail\EO_MailMessage setBody(\string|\Bitrix\Main\DB\SqlExpression $body)
	 * @method bool hasBody()
	 * @method bool isBodyFilled()
	 * @method bool isBodyChanged()
	 * @method \string remindActualBody()
	 * @method \string requireBody()
	 * @method \Bitrix\Mail\EO_MailMessage resetBody()
	 * @method \Bitrix\Mail\EO_MailMessage unsetBody()
	 * @method \string fillBody()
	 * @method \string getBodyHtml()
	 * @method \Bitrix\Mail\EO_MailMessage setBodyHtml(\string|\Bitrix\Main\DB\SqlExpression $bodyHtml)
	 * @method bool hasBodyHtml()
	 * @method bool isBodyHtmlFilled()
	 * @method bool isBodyHtmlChanged()
	 * @method \string remindActualBodyHtml()
	 * @method \string requireBodyHtml()
	 * @method \Bitrix\Mail\EO_MailMessage resetBodyHtml()
	 * @method \Bitrix\Mail\EO_MailMessage unsetBodyHtml()
	 * @method \string fillBodyHtml()
	 * @method \int getAttachments()
	 * @method \Bitrix\Mail\EO_MailMessage setAttachments(\int|\Bitrix\Main\DB\SqlExpression $attachments)
	 * @method bool hasAttachments()
	 * @method bool isAttachmentsFilled()
	 * @method bool isAttachmentsChanged()
	 * @method \int remindActualAttachments()
	 * @method \int requireAttachments()
	 * @method \Bitrix\Mail\EO_MailMessage resetAttachments()
	 * @method \Bitrix\Mail\EO_MailMessage unsetAttachments()
	 * @method \int fillAttachments()
	 * @method \boolean getNewMessage()
	 * @method \Bitrix\Mail\EO_MailMessage setNewMessage(\boolean|\Bitrix\Main\DB\SqlExpression $newMessage)
	 * @method bool hasNewMessage()
	 * @method bool isNewMessageFilled()
	 * @method bool isNewMessageChanged()
	 * @method \boolean remindActualNewMessage()
	 * @method \boolean requireNewMessage()
	 * @method \Bitrix\Mail\EO_MailMessage resetNewMessage()
	 * @method \Bitrix\Mail\EO_MailMessage unsetNewMessage()
	 * @method \boolean fillNewMessage()
	 * @method \string getSpam()
	 * @method \Bitrix\Mail\EO_MailMessage setSpam(\string|\Bitrix\Main\DB\SqlExpression $spam)
	 * @method bool hasSpam()
	 * @method bool isSpamFilled()
	 * @method bool isSpamChanged()
	 * @method \string remindActualSpam()
	 * @method \string requireSpam()
	 * @method \Bitrix\Mail\EO_MailMessage resetSpam()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSpam()
	 * @method \string fillSpam()
	 * @method \float getSpamRating()
	 * @method \Bitrix\Mail\EO_MailMessage setSpamRating(\float|\Bitrix\Main\DB\SqlExpression $spamRating)
	 * @method bool hasSpamRating()
	 * @method bool isSpamRatingFilled()
	 * @method bool isSpamRatingChanged()
	 * @method \float remindActualSpamRating()
	 * @method \float requireSpamRating()
	 * @method \Bitrix\Mail\EO_MailMessage resetSpamRating()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSpamRating()
	 * @method \float fillSpamRating()
	 * @method \string getSpamWords()
	 * @method \Bitrix\Mail\EO_MailMessage setSpamWords(\string|\Bitrix\Main\DB\SqlExpression $spamWords)
	 * @method bool hasSpamWords()
	 * @method bool isSpamWordsFilled()
	 * @method bool isSpamWordsChanged()
	 * @method \string remindActualSpamWords()
	 * @method \string requireSpamWords()
	 * @method \Bitrix\Mail\EO_MailMessage resetSpamWords()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSpamWords()
	 * @method \string fillSpamWords()
	 * @method \boolean getSpamLastResult()
	 * @method \Bitrix\Mail\EO_MailMessage setSpamLastResult(\boolean|\Bitrix\Main\DB\SqlExpression $spamLastResult)
	 * @method bool hasSpamLastResult()
	 * @method bool isSpamLastResultFilled()
	 * @method bool isSpamLastResultChanged()
	 * @method \boolean remindActualSpamLastResult()
	 * @method \boolean requireSpamLastResult()
	 * @method \Bitrix\Mail\EO_MailMessage resetSpamLastResult()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSpamLastResult()
	 * @method \boolean fillSpamLastResult()
	 * @method \string getExternalId()
	 * @method \Bitrix\Mail\EO_MailMessage setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Mail\EO_MailMessage resetExternalId()
	 * @method \Bitrix\Mail\EO_MailMessage unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \string getMsgId()
	 * @method \Bitrix\Mail\EO_MailMessage setMsgId(\string|\Bitrix\Main\DB\SqlExpression $msgId)
	 * @method bool hasMsgId()
	 * @method bool isMsgIdFilled()
	 * @method bool isMsgIdChanged()
	 * @method \string remindActualMsgId()
	 * @method \string requireMsgId()
	 * @method \Bitrix\Mail\EO_MailMessage resetMsgId()
	 * @method \Bitrix\Mail\EO_MailMessage unsetMsgId()
	 * @method \string fillMsgId()
	 * @method \string getInReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage setInReplyTo(\string|\Bitrix\Main\DB\SqlExpression $inReplyTo)
	 * @method bool hasInReplyTo()
	 * @method bool isInReplyToFilled()
	 * @method bool isInReplyToChanged()
	 * @method \string remindActualInReplyTo()
	 * @method \string requireInReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage resetInReplyTo()
	 * @method \Bitrix\Mail\EO_MailMessage unsetInReplyTo()
	 * @method \string fillInReplyTo()
	 * @method \int getLeftMargin()
	 * @method \Bitrix\Mail\EO_MailMessage setLeftMargin(\int|\Bitrix\Main\DB\SqlExpression $leftMargin)
	 * @method bool hasLeftMargin()
	 * @method bool isLeftMarginFilled()
	 * @method bool isLeftMarginChanged()
	 * @method \int remindActualLeftMargin()
	 * @method \int requireLeftMargin()
	 * @method \Bitrix\Mail\EO_MailMessage resetLeftMargin()
	 * @method \Bitrix\Mail\EO_MailMessage unsetLeftMargin()
	 * @method \int fillLeftMargin()
	 * @method \int getRightMargin()
	 * @method \Bitrix\Mail\EO_MailMessage setRightMargin(\int|\Bitrix\Main\DB\SqlExpression $rightMargin)
	 * @method bool hasRightMargin()
	 * @method bool isRightMarginFilled()
	 * @method bool isRightMarginChanged()
	 * @method \int remindActualRightMargin()
	 * @method \int requireRightMargin()
	 * @method \Bitrix\Mail\EO_MailMessage resetRightMargin()
	 * @method \Bitrix\Mail\EO_MailMessage unsetRightMargin()
	 * @method \int fillRightMargin()
	 * @method \string getSearchContent()
	 * @method \Bitrix\Mail\EO_MailMessage setSearchContent(\string|\Bitrix\Main\DB\SqlExpression $searchContent)
	 * @method bool hasSearchContent()
	 * @method bool isSearchContentFilled()
	 * @method bool isSearchContentChanged()
	 * @method \string remindActualSearchContent()
	 * @method \string requireSearchContent()
	 * @method \Bitrix\Mail\EO_MailMessage resetSearchContent()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSearchContent()
	 * @method \string fillSearchContent()
	 * @method \int getIndexVersion()
	 * @method \Bitrix\Mail\EO_MailMessage setIndexVersion(\int|\Bitrix\Main\DB\SqlExpression $indexVersion)
	 * @method bool hasIndexVersion()
	 * @method bool isIndexVersionFilled()
	 * @method bool isIndexVersionChanged()
	 * @method \int remindActualIndexVersion()
	 * @method \int requireIndexVersion()
	 * @method \Bitrix\Mail\EO_MailMessage resetIndexVersion()
	 * @method \Bitrix\Mail\EO_MailMessage unsetIndexVersion()
	 * @method \int fillIndexVersion()
	 * @method \Bitrix\Main\Type\DateTime getReadConfirmed()
	 * @method \Bitrix\Mail\EO_MailMessage setReadConfirmed(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $readConfirmed)
	 * @method bool hasReadConfirmed()
	 * @method bool isReadConfirmedFilled()
	 * @method bool isReadConfirmedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualReadConfirmed()
	 * @method \Bitrix\Main\Type\DateTime requireReadConfirmed()
	 * @method \Bitrix\Mail\EO_MailMessage resetReadConfirmed()
	 * @method \Bitrix\Mail\EO_MailMessage unsetReadConfirmed()
	 * @method \Bitrix\Main\Type\DateTime fillReadConfirmed()
	 * @method \string getOptions()
	 * @method \Bitrix\Mail\EO_MailMessage setOptions(\string|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \string remindActualOptions()
	 * @method \string requireOptions()
	 * @method \Bitrix\Mail\EO_MailMessage resetOptions()
	 * @method \Bitrix\Mail\EO_MailMessage unsetOptions()
	 * @method \string fillOptions()
	 * @method \Bitrix\Mail\EO_Mailbox getMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox remindActualMailbox()
	 * @method \Bitrix\Mail\EO_Mailbox requireMailbox()
	 * @method \Bitrix\Mail\EO_MailMessage setMailbox(\Bitrix\Mail\EO_Mailbox $object)
	 * @method \Bitrix\Mail\EO_MailMessage resetMailbox()
	 * @method \Bitrix\Mail\EO_MailMessage unsetMailbox()
	 * @method bool hasMailbox()
	 * @method bool isMailboxFilled()
	 * @method bool isMailboxChanged()
	 * @method \Bitrix\Mail\EO_Mailbox fillMailbox()
	 * @method \boolean getSanitizeOnView()
	 * @method \Bitrix\Mail\EO_MailMessage setSanitizeOnView(\boolean|\Bitrix\Main\DB\SqlExpression $sanitizeOnView)
	 * @method bool hasSanitizeOnView()
	 * @method bool isSanitizeOnViewFilled()
	 * @method bool isSanitizeOnViewChanged()
	 * @method \boolean remindActualSanitizeOnView()
	 * @method \boolean requireSanitizeOnView()
	 * @method \Bitrix\Mail\EO_MailMessage resetSanitizeOnView()
	 * @method \Bitrix\Mail\EO_MailMessage unsetSanitizeOnView()
	 * @method \boolean fillSanitizeOnView()
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
	 * @method \Bitrix\Mail\EO_MailMessage set($fieldName, $value)
	 * @method \Bitrix\Mail\EO_MailMessage reset($fieldName)
	 * @method \Bitrix\Mail\EO_MailMessage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Mail\EO_MailMessage wakeUp($data)
	 */
	class EO_MailMessage {
		/* @var \Bitrix\Mail\MailMessageTable */
		static public $dataClass = '\Bitrix\Mail\MailMessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Mail {
	/**
	 * EO_MailMessage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailboxIdList()
	 * @method \int[] fillMailboxId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getFullTextList()
	 * @method \string[] fillFullText()
	 * @method \int[] getMessageSizeList()
	 * @method \int[] fillMessageSize()
	 * @method \string[] getHeaderList()
	 * @method \string[] fillHeader()
	 * @method \Bitrix\Main\Type\DateTime[] getFieldDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFieldDate()
	 * @method \string[] getFieldFromList()
	 * @method \string[] fillFieldFrom()
	 * @method \string[] getFieldReplyToList()
	 * @method \string[] fillFieldReplyTo()
	 * @method \string[] getFieldToList()
	 * @method \string[] fillFieldTo()
	 * @method \string[] getFieldCcList()
	 * @method \string[] fillFieldCc()
	 * @method \string[] getFieldBccList()
	 * @method \string[] fillFieldBcc()
	 * @method \int[] getFieldPriorityList()
	 * @method \int[] fillFieldPriority()
	 * @method \string[] getSubjectList()
	 * @method \string[] fillSubject()
	 * @method \string[] getBodyList()
	 * @method \string[] fillBody()
	 * @method \string[] getBodyHtmlList()
	 * @method \string[] fillBodyHtml()
	 * @method \int[] getAttachmentsList()
	 * @method \int[] fillAttachments()
	 * @method \boolean[] getNewMessageList()
	 * @method \boolean[] fillNewMessage()
	 * @method \string[] getSpamList()
	 * @method \string[] fillSpam()
	 * @method \float[] getSpamRatingList()
	 * @method \float[] fillSpamRating()
	 * @method \string[] getSpamWordsList()
	 * @method \string[] fillSpamWords()
	 * @method \boolean[] getSpamLastResultList()
	 * @method \boolean[] fillSpamLastResult()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \string[] getMsgIdList()
	 * @method \string[] fillMsgId()
	 * @method \string[] getInReplyToList()
	 * @method \string[] fillInReplyTo()
	 * @method \int[] getLeftMarginList()
	 * @method \int[] fillLeftMargin()
	 * @method \int[] getRightMarginList()
	 * @method \int[] fillRightMargin()
	 * @method \string[] getSearchContentList()
	 * @method \string[] fillSearchContent()
	 * @method \int[] getIndexVersionList()
	 * @method \int[] fillIndexVersion()
	 * @method \Bitrix\Main\Type\DateTime[] getReadConfirmedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillReadConfirmed()
	 * @method \string[] getOptionsList()
	 * @method \string[] fillOptions()
	 * @method \Bitrix\Mail\EO_Mailbox[] getMailboxList()
	 * @method \Bitrix\Mail\EO_MailMessage_Collection getMailboxCollection()
	 * @method \Bitrix\Mail\EO_Mailbox_Collection fillMailbox()
	 * @method \boolean[] getSanitizeOnViewList()
	 * @method \boolean[] fillSanitizeOnView()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Mail\EO_MailMessage $object)
	 * @method bool has(\Bitrix\Mail\EO_MailMessage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailMessage getByPrimary($primary)
	 * @method \Bitrix\Mail\EO_MailMessage[] getAll()
	 * @method bool remove(\Bitrix\Mail\EO_MailMessage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Mail\EO_MailMessage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Mail\EO_MailMessage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_MailMessage_Collection merge(?EO_MailMessage_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_MailMessage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Mail\MailMessageTable */
		static public $dataClass = '\Bitrix\Mail\MailMessageTable';
	}
}
namespace Bitrix\Mail {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailMessage_Result exec()
	 * @method \Bitrix\Mail\EO_MailMessage fetchObject()
	 * @method \Bitrix\Mail\EO_MailMessage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailMessage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Mail\EO_MailMessage fetchObject()
	 * @method \Bitrix\Mail\EO_MailMessage_Collection fetchCollection()
	 */
	class EO_MailMessage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Mail\EO_MailMessage createObject($setDefaultValues = true)
	 * @method \Bitrix\Mail\EO_MailMessage_Collection createCollection()
	 * @method \Bitrix\Mail\EO_MailMessage wakeUpObject($row)
	 * @method \Bitrix\Mail\EO_MailMessage_Collection wakeUpCollection($rows)
	 */
	class EO_MailMessage_Entity extends \Bitrix\Main\ORM\Entity {}
}