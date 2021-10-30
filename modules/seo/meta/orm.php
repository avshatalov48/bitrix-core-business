<?php

/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\AutologTable:seo/lib/adv/autolog.php:d1ebd1e1b002b942fcf571c94b5bb27e */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Autolog
	 * @see \Bitrix\Seo\Adv\AutologTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \string getCampaignXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setCampaignXmlId(\string|\Bitrix\Main\DB\SqlExpression $campaignXmlId)
	 * @method bool hasCampaignXmlId()
	 * @method bool isCampaignXmlIdFilled()
	 * @method bool isCampaignXmlIdChanged()
	 * @method \string remindActualCampaignXmlId()
	 * @method \string requireCampaignXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetCampaignXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetCampaignXmlId()
	 * @method \string fillCampaignXmlId()
	 * @method \int getBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setBannerId(\int|\Bitrix\Main\DB\SqlExpression $bannerId)
	 * @method bool hasBannerId()
	 * @method bool isBannerIdFilled()
	 * @method bool isBannerIdChanged()
	 * @method \int remindActualBannerId()
	 * @method \int requireBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetBannerId()
	 * @method \int fillBannerId()
	 * @method \string getBannerXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setBannerXmlId(\string|\Bitrix\Main\DB\SqlExpression $bannerXmlId)
	 * @method bool hasBannerXmlId()
	 * @method bool isBannerXmlIdFilled()
	 * @method bool isBannerXmlIdChanged()
	 * @method \string remindActualBannerXmlId()
	 * @method \string requireBannerXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetBannerXmlId()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetBannerXmlId()
	 * @method \string fillBannerXmlId()
	 * @method \int getCauseCode()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setCauseCode(\int|\Bitrix\Main\DB\SqlExpression $causeCode)
	 * @method bool hasCauseCode()
	 * @method bool isCauseCodeFilled()
	 * @method bool isCauseCodeChanged()
	 * @method \int remindActualCauseCode()
	 * @method \int requireCauseCode()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetCauseCode()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetCauseCode()
	 * @method \int fillCauseCode()
	 * @method \boolean getSuccess()
	 * @method \Bitrix\Seo\Adv\EO_Autolog setSuccess(\boolean|\Bitrix\Main\DB\SqlExpression $success)
	 * @method bool hasSuccess()
	 * @method bool isSuccessFilled()
	 * @method bool isSuccessChanged()
	 * @method \boolean remindActualSuccess()
	 * @method \boolean requireSuccess()
	 * @method \Bitrix\Seo\Adv\EO_Autolog resetSuccess()
	 * @method \Bitrix\Seo\Adv\EO_Autolog unsetSuccess()
	 * @method \boolean fillSuccess()
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
	 * @method \Bitrix\Seo\Adv\EO_Autolog set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_Autolog reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_Autolog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_Autolog wakeUp($data)
	 */
	class EO_Autolog {
		/* @var \Bitrix\Seo\Adv\AutologTable */
		static public $dataClass = '\Bitrix\Seo\Adv\AutologTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Autolog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \string[] getCampaignXmlIdList()
	 * @method \string[] fillCampaignXmlId()
	 * @method \int[] getBannerIdList()
	 * @method \int[] fillBannerId()
	 * @method \string[] getBannerXmlIdList()
	 * @method \string[] fillBannerXmlId()
	 * @method \int[] getCauseCodeList()
	 * @method \int[] fillCauseCode()
	 * @method \boolean[] getSuccessList()
	 * @method \boolean[] fillSuccess()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_Autolog $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_Autolog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Autolog getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Autolog[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_Autolog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_Autolog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_Autolog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Autolog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\AutologTable */
		static public $dataClass = '\Bitrix\Seo\Adv\AutologTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Autolog_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_Autolog fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Autolog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Autolog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Autolog fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Autolog_Collection fetchCollection()
	 */
	class EO_Autolog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Autolog createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_Autolog_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_Autolog wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_Autolog_Collection wakeUpCollection($rows)
	 */
	class EO_Autolog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\LinkTable:seo/lib/adv/link.php:dd018a1414c7d7e1d936491eadc093d7 */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Link
	 * @see \Bitrix\Seo\Adv\LinkTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getLinkType()
	 * @method \Bitrix\Seo\Adv\EO_Link setLinkType(\string|\Bitrix\Main\DB\SqlExpression $linkType)
	 * @method bool hasLinkType()
	 * @method bool isLinkTypeFilled()
	 * @method bool isLinkTypeChanged()
	 * @method \int getLinkId()
	 * @method \Bitrix\Seo\Adv\EO_Link setLinkId(\int|\Bitrix\Main\DB\SqlExpression $linkId)
	 * @method bool hasLinkId()
	 * @method bool isLinkIdFilled()
	 * @method bool isLinkIdChanged()
	 * @method \int getBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Link setBannerId(\int|\Bitrix\Main\DB\SqlExpression $bannerId)
	 * @method bool hasBannerId()
	 * @method bool isBannerIdFilled()
	 * @method bool isBannerIdChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner getBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner remindActualBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner requireBanner()
	 * @method \Bitrix\Seo\Adv\EO_Link setBanner(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method \Bitrix\Seo\Adv\EO_Link resetBanner()
	 * @method \Bitrix\Seo\Adv\EO_Link unsetBanner()
	 * @method bool hasBanner()
	 * @method bool isBannerFilled()
	 * @method bool isBannerChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner fillBanner()
	 * @method \Bitrix\Iblock\EO_Element getIblockElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualIblockElement()
	 * @method \Bitrix\Iblock\EO_Element requireIblockElement()
	 * @method \Bitrix\Seo\Adv\EO_Link setIblockElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Seo\Adv\EO_Link resetIblockElement()
	 * @method \Bitrix\Seo\Adv\EO_Link unsetIblockElement()
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
	 * @method \Bitrix\Seo\Adv\EO_Link set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_Link reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_Link unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_Link wakeUp($data)
	 */
	class EO_Link {
		/* @var \Bitrix\Seo\Adv\LinkTable */
		static public $dataClass = '\Bitrix\Seo\Adv\LinkTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Link_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getLinkTypeList()
	 * @method \int[] getLinkIdList()
	 * @method \int[] getBannerIdList()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner[] getBannerList()
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection getBannerCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection fillBanner()
	 * @method \Bitrix\Iblock\EO_Element[] getIblockElementList()
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection getIblockElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillIblockElement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_Link $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_Link $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Link getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Link[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_Link $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_Link_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_Link current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Link_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\LinkTable */
		static public $dataClass = '\Bitrix\Seo\Adv\LinkTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Link_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_Link fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Link_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Link fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection fetchCollection()
	 */
	class EO_Link_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Link createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_Link wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_Link_Collection wakeUpCollection($rows)
	 */
	class EO_Link_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\LogTable:seo/lib/adv/log.php:b41eae0f8d1393318fb646c4912d772e */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Log
	 * @see \Bitrix\Seo\Adv\LogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_Log setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Log setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Log resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Log setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Log resetTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getRequestUri()
	 * @method \Bitrix\Seo\Adv\EO_Log setRequestUri(\string|\Bitrix\Main\DB\SqlExpression $requestUri)
	 * @method bool hasRequestUri()
	 * @method bool isRequestUriFilled()
	 * @method bool isRequestUriChanged()
	 * @method \string remindActualRequestUri()
	 * @method \string requireRequestUri()
	 * @method \Bitrix\Seo\Adv\EO_Log resetRequestUri()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetRequestUri()
	 * @method \string fillRequestUri()
	 * @method \string getRequestData()
	 * @method \Bitrix\Seo\Adv\EO_Log setRequestData(\string|\Bitrix\Main\DB\SqlExpression $requestData)
	 * @method bool hasRequestData()
	 * @method bool isRequestDataFilled()
	 * @method bool isRequestDataChanged()
	 * @method \string remindActualRequestData()
	 * @method \string requireRequestData()
	 * @method \Bitrix\Seo\Adv\EO_Log resetRequestData()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetRequestData()
	 * @method \string fillRequestData()
	 * @method \float getResponseTime()
	 * @method \Bitrix\Seo\Adv\EO_Log setResponseTime(\float|\Bitrix\Main\DB\SqlExpression $responseTime)
	 * @method bool hasResponseTime()
	 * @method bool isResponseTimeFilled()
	 * @method bool isResponseTimeChanged()
	 * @method \float remindActualResponseTime()
	 * @method \float requireResponseTime()
	 * @method \Bitrix\Seo\Adv\EO_Log resetResponseTime()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetResponseTime()
	 * @method \float fillResponseTime()
	 * @method \int getResponseStatus()
	 * @method \Bitrix\Seo\Adv\EO_Log setResponseStatus(\int|\Bitrix\Main\DB\SqlExpression $responseStatus)
	 * @method bool hasResponseStatus()
	 * @method bool isResponseStatusFilled()
	 * @method bool isResponseStatusChanged()
	 * @method \int remindActualResponseStatus()
	 * @method \int requireResponseStatus()
	 * @method \Bitrix\Seo\Adv\EO_Log resetResponseStatus()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetResponseStatus()
	 * @method \int fillResponseStatus()
	 * @method \string getResponseData()
	 * @method \Bitrix\Seo\Adv\EO_Log setResponseData(\string|\Bitrix\Main\DB\SqlExpression $responseData)
	 * @method bool hasResponseData()
	 * @method bool isResponseDataFilled()
	 * @method bool isResponseDataChanged()
	 * @method \string remindActualResponseData()
	 * @method \string requireResponseData()
	 * @method \Bitrix\Seo\Adv\EO_Log resetResponseData()
	 * @method \Bitrix\Seo\Adv\EO_Log unsetResponseData()
	 * @method \string fillResponseData()
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
	 * @method \Bitrix\Seo\Adv\EO_Log set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_Log reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_Log unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_Log wakeUp($data)
	 */
	class EO_Log {
		/* @var \Bitrix\Seo\Adv\LogTable */
		static public $dataClass = '\Bitrix\Seo\Adv\LogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Log_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getRequestUriList()
	 * @method \string[] fillRequestUri()
	 * @method \string[] getRequestDataList()
	 * @method \string[] fillRequestData()
	 * @method \float[] getResponseTimeList()
	 * @method \float[] fillResponseTime()
	 * @method \int[] getResponseStatusList()
	 * @method \int[] fillResponseStatus()
	 * @method \string[] getResponseDataList()
	 * @method \string[] fillResponseData()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_Log $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_Log $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Log getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Log[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_Log $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_Log_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_Log current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Log_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\LogTable */
		static public $dataClass = '\Bitrix\Seo\Adv\LogTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Log_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_Log fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Log_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Log_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Log fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Log_Collection fetchCollection()
	 */
	class EO_Log_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Log createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_Log_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_Log wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_Log_Collection wakeUpCollection($rows)
	 */
	class EO_Log_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\OrderTable:seo/lib/adv/order.php:bdf9c595f319a060db76c573b9514452 */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Order
	 * @see \Bitrix\Seo\Adv\OrderTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_Order setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Order setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Order resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Order setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Order resetTimestampX()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Order setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Order resetCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \int getBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Order setBannerId(\int|\Bitrix\Main\DB\SqlExpression $bannerId)
	 * @method bool hasBannerId()
	 * @method bool isBannerIdFilled()
	 * @method bool isBannerIdChanged()
	 * @method \int remindActualBannerId()
	 * @method \int requireBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Order resetBannerId()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetBannerId()
	 * @method \int fillBannerId()
	 * @method \int getOrderId()
	 * @method \Bitrix\Seo\Adv\EO_Order setOrderId(\int|\Bitrix\Main\DB\SqlExpression $orderId)
	 * @method bool hasOrderId()
	 * @method bool isOrderIdFilled()
	 * @method bool isOrderIdChanged()
	 * @method \int remindActualOrderId()
	 * @method \int requireOrderId()
	 * @method \Bitrix\Seo\Adv\EO_Order resetOrderId()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetOrderId()
	 * @method \int fillOrderId()
	 * @method \float getSum()
	 * @method \Bitrix\Seo\Adv\EO_Order setSum(\float|\Bitrix\Main\DB\SqlExpression $sum)
	 * @method bool hasSum()
	 * @method bool isSumFilled()
	 * @method bool isSumChanged()
	 * @method \float remindActualSum()
	 * @method \float requireSum()
	 * @method \Bitrix\Seo\Adv\EO_Order resetSum()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetSum()
	 * @method \float fillSum()
	 * @method \boolean getProcessed()
	 * @method \Bitrix\Seo\Adv\EO_Order setProcessed(\boolean|\Bitrix\Main\DB\SqlExpression $processed)
	 * @method bool hasProcessed()
	 * @method bool isProcessedFilled()
	 * @method bool isProcessedChanged()
	 * @method \boolean remindActualProcessed()
	 * @method \boolean requireProcessed()
	 * @method \Bitrix\Seo\Adv\EO_Order resetProcessed()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetProcessed()
	 * @method \boolean fillProcessed()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign getCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign remindActualCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign requireCampaign()
	 * @method \Bitrix\Seo\Adv\EO_Order setCampaign(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method \Bitrix\Seo\Adv\EO_Order resetCampaign()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetCampaign()
	 * @method bool hasCampaign()
	 * @method bool isCampaignFilled()
	 * @method bool isCampaignChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner getBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner remindActualBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner requireBanner()
	 * @method \Bitrix\Seo\Adv\EO_Order setBanner(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method \Bitrix\Seo\Adv\EO_Order resetBanner()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetBanner()
	 * @method bool hasBanner()
	 * @method bool isBannerFilled()
	 * @method bool isBannerChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner fillBanner()
	 * @method \Bitrix\Sale\Internals\EO_Order getOrder()
	 * @method \Bitrix\Sale\Internals\EO_Order remindActualOrder()
	 * @method \Bitrix\Sale\Internals\EO_Order requireOrder()
	 * @method \Bitrix\Seo\Adv\EO_Order setOrder(\Bitrix\Sale\Internals\EO_Order $object)
	 * @method \Bitrix\Seo\Adv\EO_Order resetOrder()
	 * @method \Bitrix\Seo\Adv\EO_Order unsetOrder()
	 * @method bool hasOrder()
	 * @method bool isOrderFilled()
	 * @method bool isOrderChanged()
	 * @method \Bitrix\Sale\Internals\EO_Order fillOrder()
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
	 * @method \Bitrix\Seo\Adv\EO_Order set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_Order reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_Order unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_Order wakeUp($data)
	 */
	class EO_Order {
		/* @var \Bitrix\Seo\Adv\OrderTable */
		static public $dataClass = '\Bitrix\Seo\Adv\OrderTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_Order_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \int[] getBannerIdList()
	 * @method \int[] fillBannerId()
	 * @method \int[] getOrderIdList()
	 * @method \int[] fillOrderId()
	 * @method \float[] getSumList()
	 * @method \float[] fillSum()
	 * @method \boolean[] getProcessedList()
	 * @method \boolean[] fillProcessed()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign[] getCampaignList()
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection getCampaignCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner[] getBannerList()
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection getBannerCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection fillBanner()
	 * @method \Bitrix\Sale\Internals\EO_Order[] getOrderList()
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection getOrderCollection()
	 * @method \Bitrix\Sale\Internals\EO_Order_Collection fillOrder()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_Order $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_Order $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Order getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_Order[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_Order $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_Order_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_Order current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Order_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\OrderTable */
		static public $dataClass = '\Bitrix\Seo\Adv\OrderTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Order_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_Order fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Order_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Order fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection fetchCollection()
	 */
	class EO_Order_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_Order createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_Order wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_Order_Collection wakeUpCollection($rows)
	 */
	class EO_Order_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\YandexBannerTable:seo/lib/adv/yandexbanner.php:bb6a41360dc6eb3a7e9355af6cbed5b3 */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexBanner
	 * @see \Bitrix\Seo\Adv\YandexBannerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \string getOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setOwnerName(\string|\Bitrix\Main\DB\SqlExpression $ownerName)
	 * @method bool hasOwnerName()
	 * @method bool isOwnerNameFilled()
	 * @method bool isOwnerNameChanged()
	 * @method \string remindActualOwnerName()
	 * @method \string requireOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetOwnerName()
	 * @method \string fillOwnerName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetName()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setLastUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUpdate)
	 * @method bool hasLastUpdate()
	 * @method bool isLastUpdateFilled()
	 * @method bool isLastUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUpdate()
	 * @method array getSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setSettings(array|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method array remindActualSettings()
	 * @method array requireSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetSettings()
	 * @method array fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine getEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine remindActualEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine requireEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setEngine(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetEngine()
	 * @method bool hasEngine()
	 * @method bool isEngineFilled()
	 * @method bool isEngineChanged()
	 * @method \Bitrix\Seo\EO_SearchEngine fillEngine()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetGroupId()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \string getAutoQuantityOff()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setAutoQuantityOff(\string|\Bitrix\Main\DB\SqlExpression $autoQuantityOff)
	 * @method bool hasAutoQuantityOff()
	 * @method bool isAutoQuantityOffFilled()
	 * @method bool isAutoQuantityOffChanged()
	 * @method \string remindActualAutoQuantityOff()
	 * @method \string requireAutoQuantityOff()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetAutoQuantityOff()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetAutoQuantityOff()
	 * @method \string fillAutoQuantityOff()
	 * @method \string getAutoQuantityOn()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setAutoQuantityOn(\string|\Bitrix\Main\DB\SqlExpression $autoQuantityOn)
	 * @method bool hasAutoQuantityOn()
	 * @method bool isAutoQuantityOnFilled()
	 * @method bool isAutoQuantityOnChanged()
	 * @method \string remindActualAutoQuantityOn()
	 * @method \string requireAutoQuantityOn()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetAutoQuantityOn()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetAutoQuantityOn()
	 * @method \string fillAutoQuantityOn()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign getCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign remindActualCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign requireCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setCampaign(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetCampaign()
	 * @method bool hasCampaign()
	 * @method bool isCampaignFilled()
	 * @method bool isCampaignChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup getGroup()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup remindActualGroup()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup requireGroup()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner setGroup(\Bitrix\Seo\Adv\EO_YandexGroup $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner resetGroup()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup fillGroup()
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
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_YandexBanner wakeUp($data)
	 */
	class EO_YandexBanner {
		/* @var \Bitrix\Seo\Adv\YandexBannerTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexBannerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexBanner_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \string[] getOwnerNameList()
	 * @method \string[] fillOwnerName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUpdate()
	 * @method array[] getSettingsList()
	 * @method array[] fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine[] getEngineList()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection getEngineCollection()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fillEngine()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \string[] getAutoQuantityOffList()
	 * @method \string[] fillAutoQuantityOff()
	 * @method \string[] getAutoQuantityOnList()
	 * @method \string[] fillAutoQuantityOn()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign[] getCampaignList()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection getCampaignCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup[] getGroupList()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection getGroupCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_YandexBanner_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_YandexBanner_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\YandexBannerTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexBannerTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_YandexBanner_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_YandexBanner_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection fetchCollection()
	 */
	class EO_YandexBanner_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection wakeUpCollection($rows)
	 */
	class EO_YandexBanner_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\YandexCampaignTable:seo/lib/adv/yandexcampaign.php:8cc3d726a1bea2ef4d6ba769227f546f */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexCampaign
	 * @see \Bitrix\Seo\Adv\YandexCampaignTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \string getOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setOwnerName(\string|\Bitrix\Main\DB\SqlExpression $ownerName)
	 * @method bool hasOwnerName()
	 * @method bool isOwnerNameFilled()
	 * @method bool isOwnerNameChanged()
	 * @method \string remindActualOwnerName()
	 * @method \string requireOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetOwnerName()
	 * @method \string fillOwnerName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetName()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setLastUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUpdate)
	 * @method bool hasLastUpdate()
	 * @method bool isLastUpdateFilled()
	 * @method bool isLastUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUpdate()
	 * @method array getSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setSettings(array|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method array remindActualSettings()
	 * @method array requireSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetSettings()
	 * @method array fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine getEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine remindActualEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine requireEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign setEngine(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign resetEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unsetEngine()
	 * @method bool hasEngine()
	 * @method bool isEngineFilled()
	 * @method bool isEngineChanged()
	 * @method \Bitrix\Seo\EO_SearchEngine fillEngine()
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
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_YandexCampaign wakeUp($data)
	 */
	class EO_YandexCampaign {
		/* @var \Bitrix\Seo\Adv\YandexCampaignTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexCampaignTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexCampaign_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \string[] getOwnerNameList()
	 * @method \string[] fillOwnerName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUpdate()
	 * @method array[] getSettingsList()
	 * @method array[] fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine[] getEngineList()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection getEngineCollection()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fillEngine()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_YandexCampaign_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_YandexCampaign_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\YandexCampaignTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexCampaignTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_YandexCampaign_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_YandexCampaign_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fetchCollection()
	 */
	class EO_YandexCampaign_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection wakeUpCollection($rows)
	 */
	class EO_YandexCampaign_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\YandexGroupTable:seo/lib/adv/yandexgroup.php:4f011d56a599d60f97d8b2645e032823 */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexGroup
	 * @see \Bitrix\Seo\Adv\YandexGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \string getOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setOwnerName(\string|\Bitrix\Main\DB\SqlExpression $ownerName)
	 * @method bool hasOwnerName()
	 * @method bool isOwnerNameFilled()
	 * @method bool isOwnerNameChanged()
	 * @method \string remindActualOwnerName()
	 * @method \string requireOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetOwnerName()
	 * @method \string fillOwnerName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetName()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setLastUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUpdate)
	 * @method bool hasLastUpdate()
	 * @method bool isLastUpdateFilled()
	 * @method bool isLastUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUpdate()
	 * @method array getSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setSettings(array|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method array remindActualSettings()
	 * @method array requireSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetSettings()
	 * @method array fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine getEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine remindActualEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine requireEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setEngine(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetEngine()
	 * @method bool hasEngine()
	 * @method bool isEngineFilled()
	 * @method bool isEngineChanged()
	 * @method \Bitrix\Seo\EO_SearchEngine fillEngine()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign getCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign remindActualCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign requireCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup setCampaign(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup resetCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unsetCampaign()
	 * @method bool hasCampaign()
	 * @method bool isCampaignFilled()
	 * @method bool isCampaignChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fillCampaign()
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
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_YandexGroup wakeUp($data)
	 */
	class EO_YandexGroup {
		/* @var \Bitrix\Seo\Adv\YandexGroupTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \string[] getOwnerNameList()
	 * @method \string[] fillOwnerName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUpdate()
	 * @method array[] getSettingsList()
	 * @method array[] fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine[] getEngineList()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection getEngineCollection()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fillEngine()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign[] getCampaignList()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection getCampaignCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fillCampaign()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_YandexGroup $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_YandexGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_YandexGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_YandexGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_YandexGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\YandexGroupTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexGroupTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_YandexGroup_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_YandexGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection fetchCollection()
	 */
	class EO_YandexGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_YandexGroup_Collection wakeUpCollection($rows)
	 */
	class EO_YandexGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\YandexRegionTable:seo/lib/adv/yandexregion.php:86f239d300685fc1a082671fce7cf13b */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexRegion
	 * @see \Bitrix\Seo\Adv\YandexRegionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setEngineId(\int|\Bitrix\Main\DB\SqlExpression $engineId)
	 * @method bool hasEngineId()
	 * @method bool isEngineIdFilled()
	 * @method bool isEngineIdChanged()
	 * @method \int remindActualEngineId()
	 * @method \int requireEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetEngineId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetEngineId()
	 * @method \int fillEngineId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetActive()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetOwnerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \string getOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setOwnerName(\string|\Bitrix\Main\DB\SqlExpression $ownerName)
	 * @method bool hasOwnerName()
	 * @method bool isOwnerNameFilled()
	 * @method bool isOwnerNameChanged()
	 * @method \string remindActualOwnerName()
	 * @method \string requireOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetOwnerName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetOwnerName()
	 * @method \string fillOwnerName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetXmlId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetName()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setLastUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUpdate)
	 * @method bool hasLastUpdate()
	 * @method bool isLastUpdateFilled()
	 * @method bool isLastUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetLastUpdate()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetLastUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUpdate()
	 * @method array getSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setSettings(array|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method array remindActualSettings()
	 * @method array requireSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetSettings()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetSettings()
	 * @method array fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine getEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine remindActualEngine()
	 * @method \Bitrix\Seo\EO_SearchEngine requireEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setEngine(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetEngine()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetEngine()
	 * @method bool hasEngine()
	 * @method bool isEngineFilled()
	 * @method bool isEngineChanged()
	 * @method \Bitrix\Seo\EO_SearchEngine fillEngine()
	 * @method \int getParentId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetParentId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetParentId()
	 * @method \int fillParentId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion getParent()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion remindActualParent()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion requireParent()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion setParent(\Bitrix\Seo\Adv\EO_YandexRegion $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion resetParent()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unsetParent()
	 * @method bool hasParent()
	 * @method bool isParentFilled()
	 * @method bool isParentChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion fillParent()
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
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_YandexRegion wakeUp($data)
	 */
	class EO_YandexRegion {
		/* @var \Bitrix\Seo\Adv\YandexRegionTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexRegionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexRegion_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEngineIdList()
	 * @method \int[] fillEngineId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \string[] getOwnerNameList()
	 * @method \string[] fillOwnerName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUpdate()
	 * @method array[] getSettingsList()
	 * @method array[] fillSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine[] getEngineList()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection getEngineCollection()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fillEngine()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion[] getParentList()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection getParentCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection fillParent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_YandexRegion $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_YandexRegion $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_YandexRegion $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_YandexRegion_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_YandexRegion_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\YandexRegionTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexRegionTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_YandexRegion_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_YandexRegion_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection fetchCollection()
	 */
	class EO_YandexRegion_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_YandexRegion_Collection wakeUpCollection($rows)
	 */
	class EO_YandexRegion_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Adv\YandexStatTable:seo/lib/adv/yandexstat.php:16aed0e7a4d23ee406dec156b7c2ab4d */
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexStat
	 * @see \Bitrix\Seo\Adv\YandexStatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetCampaignId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \int getBannerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setBannerId(\int|\Bitrix\Main\DB\SqlExpression $bannerId)
	 * @method bool hasBannerId()
	 * @method bool isBannerIdFilled()
	 * @method bool isBannerIdChanged()
	 * @method \int remindActualBannerId()
	 * @method \int requireBannerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetBannerId()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetBannerId()
	 * @method \int fillBannerId()
	 * @method \Bitrix\Main\Type\Date getDateDay()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setDateDay(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $dateDay)
	 * @method bool hasDateDay()
	 * @method bool isDateDayFilled()
	 * @method bool isDateDayChanged()
	 * @method \Bitrix\Main\Type\Date remindActualDateDay()
	 * @method \Bitrix\Main\Type\Date requireDateDay()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetDateDay()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetDateDay()
	 * @method \Bitrix\Main\Type\Date fillDateDay()
	 * @method \string getCurrency()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string remindActualCurrency()
	 * @method \string requireCurrency()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetCurrency()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetCurrency()
	 * @method \string fillCurrency()
	 * @method \float getSum()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setSum(\float|\Bitrix\Main\DB\SqlExpression $sum)
	 * @method bool hasSum()
	 * @method bool isSumFilled()
	 * @method bool isSumChanged()
	 * @method \float remindActualSum()
	 * @method \float requireSum()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetSum()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetSum()
	 * @method \float fillSum()
	 * @method \float getSumSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setSumSearch(\float|\Bitrix\Main\DB\SqlExpression $sumSearch)
	 * @method bool hasSumSearch()
	 * @method bool isSumSearchFilled()
	 * @method bool isSumSearchChanged()
	 * @method \float remindActualSumSearch()
	 * @method \float requireSumSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetSumSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetSumSearch()
	 * @method \float fillSumSearch()
	 * @method \float getSumContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setSumContext(\float|\Bitrix\Main\DB\SqlExpression $sumContext)
	 * @method bool hasSumContext()
	 * @method bool isSumContextFilled()
	 * @method bool isSumContextChanged()
	 * @method \float remindActualSumContext()
	 * @method \float requireSumContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetSumContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetSumContext()
	 * @method \float fillSumContext()
	 * @method \int getClicks()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setClicks(\int|\Bitrix\Main\DB\SqlExpression $clicks)
	 * @method bool hasClicks()
	 * @method bool isClicksFilled()
	 * @method bool isClicksChanged()
	 * @method \int remindActualClicks()
	 * @method \int requireClicks()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetClicks()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetClicks()
	 * @method \int fillClicks()
	 * @method \int getClicksSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setClicksSearch(\int|\Bitrix\Main\DB\SqlExpression $clicksSearch)
	 * @method bool hasClicksSearch()
	 * @method bool isClicksSearchFilled()
	 * @method bool isClicksSearchChanged()
	 * @method \int remindActualClicksSearch()
	 * @method \int requireClicksSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetClicksSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetClicksSearch()
	 * @method \int fillClicksSearch()
	 * @method \int getClicksContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setClicksContext(\int|\Bitrix\Main\DB\SqlExpression $clicksContext)
	 * @method bool hasClicksContext()
	 * @method bool isClicksContextFilled()
	 * @method bool isClicksContextChanged()
	 * @method \int remindActualClicksContext()
	 * @method \int requireClicksContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetClicksContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetClicksContext()
	 * @method \int fillClicksContext()
	 * @method \int getShows()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setShows(\int|\Bitrix\Main\DB\SqlExpression $shows)
	 * @method bool hasShows()
	 * @method bool isShowsFilled()
	 * @method bool isShowsChanged()
	 * @method \int remindActualShows()
	 * @method \int requireShows()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetShows()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetShows()
	 * @method \int fillShows()
	 * @method \int getShowsSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setShowsSearch(\int|\Bitrix\Main\DB\SqlExpression $showsSearch)
	 * @method bool hasShowsSearch()
	 * @method bool isShowsSearchFilled()
	 * @method bool isShowsSearchChanged()
	 * @method \int remindActualShowsSearch()
	 * @method \int requireShowsSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetShowsSearch()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetShowsSearch()
	 * @method \int fillShowsSearch()
	 * @method \int getShowsContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setShowsContext(\int|\Bitrix\Main\DB\SqlExpression $showsContext)
	 * @method bool hasShowsContext()
	 * @method bool isShowsContextFilled()
	 * @method bool isShowsContextChanged()
	 * @method \int remindActualShowsContext()
	 * @method \int requireShowsContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetShowsContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetShowsContext()
	 * @method \int fillShowsContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign getCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign remindActualCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign requireCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setCampaign(\Bitrix\Seo\Adv\EO_YandexCampaign $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetCampaign()
	 * @method bool hasCampaign()
	 * @method bool isCampaignFilled()
	 * @method bool isCampaignChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner getBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner remindActualBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner requireBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat setBanner(\Bitrix\Seo\Adv\EO_YandexBanner $object)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat resetBanner()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unsetBanner()
	 * @method bool hasBanner()
	 * @method bool isBannerFilled()
	 * @method bool isBannerChanged()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner fillBanner()
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
	 * @method \Bitrix\Seo\Adv\EO_YandexStat set($fieldName, $value)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat reset($fieldName)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Adv\EO_YandexStat wakeUp($data)
	 */
	class EO_YandexStat {
		/* @var \Bitrix\Seo\Adv\YandexStatTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexStatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * EO_YandexStat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \int[] getBannerIdList()
	 * @method \int[] fillBannerId()
	 * @method \Bitrix\Main\Type\Date[] getDateDayList()
	 * @method \Bitrix\Main\Type\Date[] fillDateDay()
	 * @method \string[] getCurrencyList()
	 * @method \string[] fillCurrency()
	 * @method \float[] getSumList()
	 * @method \float[] fillSum()
	 * @method \float[] getSumSearchList()
	 * @method \float[] fillSumSearch()
	 * @method \float[] getSumContextList()
	 * @method \float[] fillSumContext()
	 * @method \int[] getClicksList()
	 * @method \int[] fillClicks()
	 * @method \int[] getClicksSearchList()
	 * @method \int[] fillClicksSearch()
	 * @method \int[] getClicksContextList()
	 * @method \int[] fillClicksContext()
	 * @method \int[] getShowsList()
	 * @method \int[] fillShows()
	 * @method \int[] getShowsSearchList()
	 * @method \int[] fillShowsSearch()
	 * @method \int[] getShowsContextList()
	 * @method \int[] fillShowsContext()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign[] getCampaignList()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection getCampaignCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexCampaign_Collection fillCampaign()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner[] getBannerList()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection getBannerCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexBanner_Collection fillBanner()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Adv\EO_YandexStat $object)
	 * @method bool has(\Bitrix\Seo\Adv\EO_YandexStat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat getByPrimary($primary)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat[] getAll()
	 * @method bool remove(\Bitrix\Seo\Adv\EO_YandexStat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Adv\EO_YandexStat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Adv\EO_YandexStat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_YandexStat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Adv\YandexStatTable */
		static public $dataClass = '\Bitrix\Seo\Adv\YandexStatTable';
	}
}
namespace Bitrix\Seo\Adv {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_YandexStat_Result exec()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_YandexStat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexStat fetchObject()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection fetchCollection()
	 */
	class EO_YandexStat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Adv\EO_YandexStat createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection createCollection()
	 * @method \Bitrix\Seo\Adv\EO_YandexStat wakeUpObject($row)
	 * @method \Bitrix\Seo\Adv\EO_YandexStat_Collection wakeUpCollection($rows)
	 */
	class EO_YandexStat_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable:seo/lib/businesssuite/internals/servicequeue.php:54a4c783f6bdac440058659c838b64a9 */
namespace Bitrix\Seo\BusinessSuite\Internals {
	/**
	 * EO_ServiceQueue
	 * @see \Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue resetType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue unsetType()
	 * @method \string fillType()
	 * @method \string getServiceType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue setServiceType(\string|\Bitrix\Main\DB\SqlExpression $serviceType)
	 * @method bool hasServiceType()
	 * @method bool isServiceTypeFilled()
	 * @method bool isServiceTypeChanged()
	 * @method \string remindActualServiceType()
	 * @method \string requireServiceType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue resetServiceType()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue unsetServiceType()
	 * @method \string fillServiceType()
	 * @method \int getClientId()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue setClientId(\int|\Bitrix\Main\DB\SqlExpression $clientId)
	 * @method bool hasClientId()
	 * @method bool isClientIdFilled()
	 * @method bool isClientIdChanged()
	 * @method \int remindActualClientId()
	 * @method \int requireClientId()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue resetClientId()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue unsetClientId()
	 * @method \int fillClientId()
	 * @method \int getSort()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue resetSort()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue unsetSort()
	 * @method \int fillSort()
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
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue set($fieldName, $value)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue reset($fieldName)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue wakeUp($data)
	 */
	class EO_ServiceQueue {
		/* @var \Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable */
		static public $dataClass = '\Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\BusinessSuite\Internals {
	/**
	 * EO_ServiceQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getServiceTypeList()
	 * @method \string[] fillServiceType()
	 * @method \int[] getClientIdList()
	 * @method \int[] fillClientId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue $object)
	 * @method bool has(\Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue getByPrimary($primary)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue[] getAll()
	 * @method bool remove(\Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ServiceQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable */
		static public $dataClass = '\Bitrix\Seo\BusinessSuite\Internals\ServiceQueueTable';
	}
}
namespace Bitrix\Seo\BusinessSuite\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ServiceQueue_Result exec()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue fetchObject()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ServiceQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue fetchObject()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection fetchCollection()
	 */
	class EO_ServiceQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection createCollection()
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue wakeUpObject($row)
	 * @method \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection wakeUpCollection($rows)
	 */
	class EO_ServiceQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable:seo/lib/leadads/internals/callbacksubscription.php:413be8e9e60cae9d1e017c6328e0e4a8 */
namespace Bitrix\Seo\LeadAds\Internals {
	/**
	 * EO_CallbackSubscription
	 * @see \Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription resetDateInsert()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getType()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription resetType()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unsetType()
	 * @method \string fillType()
	 * @method \string getGroupId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setGroupId(\string|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \string remindActualGroupId()
	 * @method \string requireGroupId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription resetGroupId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unsetGroupId()
	 * @method \string fillGroupId()
	 * @method \string getCallbackServerId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setCallbackServerId(\string|\Bitrix\Main\DB\SqlExpression $callbackServerId)
	 * @method bool hasCallbackServerId()
	 * @method bool isCallbackServerIdFilled()
	 * @method bool isCallbackServerIdChanged()
	 * @method \string remindActualCallbackServerId()
	 * @method \string requireCallbackServerId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription resetCallbackServerId()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unsetCallbackServerId()
	 * @method \string fillCallbackServerId()
	 * @method \boolean getHasAuth()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription setHasAuth(\boolean|\Bitrix\Main\DB\SqlExpression $hasAuth)
	 * @method bool hasHasAuth()
	 * @method bool isHasAuthFilled()
	 * @method bool isHasAuthChanged()
	 * @method \boolean remindActualHasAuth()
	 * @method \boolean requireHasAuth()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription resetHasAuth()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unsetHasAuth()
	 * @method \boolean fillHasAuth()
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
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription set($fieldName, $value)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription reset($fieldName)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription wakeUp($data)
	 */
	class EO_CallbackSubscription {
		/* @var \Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable */
		static public $dataClass = '\Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\LeadAds\Internals {
	/**
	 * EO_CallbackSubscription_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getGroupIdList()
	 * @method \string[] fillGroupId()
	 * @method \string[] getCallbackServerIdList()
	 * @method \string[] fillCallbackServerId()
	 * @method \boolean[] getHasAuthList()
	 * @method \boolean[] fillHasAuth()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription $object)
	 * @method bool has(\Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription getByPrimary($primary)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription[] getAll()
	 * @method bool remove(\Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CallbackSubscription_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable */
		static public $dataClass = '\Bitrix\Seo\LeadAds\Internals\CallbackSubscriptionTable';
	}
}
namespace Bitrix\Seo\LeadAds\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CallbackSubscription_Result exec()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription fetchObject()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CallbackSubscription_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription fetchObject()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription_Collection fetchCollection()
	 */
	class EO_CallbackSubscription_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription_Collection createCollection()
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription wakeUpObject($row)
	 * @method \Bitrix\Seo\LeadAds\Internals\EO_CallbackSubscription_Collection wakeUpCollection($rows)
	 */
	class EO_CallbackSubscription_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Retargeting\Internals\QueueTable:seo/lib/retargeting/internals/queue.php:d1667239615e0970481516bb33a0b1c0 */
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * EO_Queue
	 * @see \Bitrix\Seo\Retargeting\Internals\QueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetType()
	 * @method \string fillType()
	 * @method \string getClientId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setClientId(\string|\Bitrix\Main\DB\SqlExpression $clientId)
	 * @method bool hasClientId()
	 * @method bool isClientIdFilled()
	 * @method bool isClientIdChanged()
	 * @method \string remindActualClientId()
	 * @method \string requireClientId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetClientId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetClientId()
	 * @method \string fillClientId()
	 * @method \string getAccountId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setAccountId(\string|\Bitrix\Main\DB\SqlExpression $accountId)
	 * @method bool hasAccountId()
	 * @method bool isAccountIdFilled()
	 * @method bool isAccountIdChanged()
	 * @method \string remindActualAccountId()
	 * @method \string requireAccountId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetAccountId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetAccountId()
	 * @method \string fillAccountId()
	 * @method \string getAudienceId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setAudienceId(\string|\Bitrix\Main\DB\SqlExpression $audienceId)
	 * @method bool hasAudienceId()
	 * @method bool isAudienceIdFilled()
	 * @method bool isAudienceIdChanged()
	 * @method \string remindActualAudienceId()
	 * @method \string requireAudienceId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetAudienceId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetAudienceId()
	 * @method \string fillAudienceId()
	 * @method \string getParentId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setParentId(\string|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \string remindActualParentId()
	 * @method \string requireParentId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetParentId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetParentId()
	 * @method \string fillParentId()
	 * @method \string getContactType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setContactType(\string|\Bitrix\Main\DB\SqlExpression $contactType)
	 * @method bool hasContactType()
	 * @method bool isContactTypeFilled()
	 * @method bool isContactTypeChanged()
	 * @method \string remindActualContactType()
	 * @method \string requireContactType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetContactType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetContactType()
	 * @method \string fillContactType()
	 * @method \string getValue()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetValue()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetValue()
	 * @method \string fillValue()
	 * @method \string getAction()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setAction(\string|\Bitrix\Main\DB\SqlExpression $action)
	 * @method bool hasAction()
	 * @method bool isActionFilled()
	 * @method bool isActionChanged()
	 * @method \string remindActualAction()
	 * @method \string requireAction()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetAction()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetAction()
	 * @method \string fillAction()
	 * @method \Bitrix\Main\Type\DateTime getDateAutoRemove()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue setDateAutoRemove(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateAutoRemove)
	 * @method bool hasDateAutoRemove()
	 * @method bool isDateAutoRemoveFilled()
	 * @method bool isDateAutoRemoveChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateAutoRemove()
	 * @method \Bitrix\Main\Type\DateTime requireDateAutoRemove()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue resetDateAutoRemove()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unsetDateAutoRemove()
	 * @method \Bitrix\Main\Type\DateTime fillDateAutoRemove()
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
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue set($fieldName, $value)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue reset($fieldName)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Retargeting\Internals\EO_Queue wakeUp($data)
	 */
	class EO_Queue {
		/* @var \Bitrix\Seo\Retargeting\Internals\QueueTable */
		static public $dataClass = '\Bitrix\Seo\Retargeting\Internals\QueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * EO_Queue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getClientIdList()
	 * @method \string[] fillClientId()
	 * @method \string[] getAccountIdList()
	 * @method \string[] fillAccountId()
	 * @method \string[] getAudienceIdList()
	 * @method \string[] fillAudienceId()
	 * @method \string[] getParentIdList()
	 * @method \string[] fillParentId()
	 * @method \string[] getContactTypeList()
	 * @method \string[] fillContactType()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \string[] getActionList()
	 * @method \string[] fillAction()
	 * @method \Bitrix\Main\Type\DateTime[] getDateAutoRemoveList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateAutoRemove()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Retargeting\Internals\EO_Queue $object)
	 * @method bool has(\Bitrix\Seo\Retargeting\Internals\EO_Queue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue getByPrimary($primary)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue[] getAll()
	 * @method bool remove(\Bitrix\Seo\Retargeting\Internals\EO_Queue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Retargeting\Internals\EO_Queue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Queue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Retargeting\Internals\QueueTable */
		static public $dataClass = '\Bitrix\Seo\Retargeting\Internals\QueueTable';
	}
}
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Queue_Result exec()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue fetchObject()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Queue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue fetchObject()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue_Collection fetchCollection()
	 */
	class EO_Queue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue_Collection createCollection()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue wakeUpObject($row)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_Queue_Collection wakeUpCollection($rows)
	 */
	class EO_Queue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\Retargeting\Internals\ServiceLogTable:seo/lib/retargeting/internals/servicelog.php:8a7d80a8ba10a81741b7826112434f4a */
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * EO_ServiceLog
	 * @see \Bitrix\Seo\Retargeting\Internals\ServiceLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog resetDateInsert()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getGroupId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setGroupId(\string|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \string remindActualGroupId()
	 * @method \string requireGroupId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog resetGroupId()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unsetGroupId()
	 * @method \string fillGroupId()
	 * @method \string getType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog resetType()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unsetType()
	 * @method \string fillType()
	 * @method \string getCode()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog resetCode()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unsetCode()
	 * @method \string fillCode()
	 * @method \string getMessage()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog resetMessage()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unsetMessage()
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
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog set($fieldName, $value)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog reset($fieldName)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog wakeUp($data)
	 */
	class EO_ServiceLog {
		/* @var \Bitrix\Seo\Retargeting\Internals\ServiceLogTable */
		static public $dataClass = '\Bitrix\Seo\Retargeting\Internals\ServiceLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * EO_ServiceLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getGroupIdList()
	 * @method \string[] fillGroupId()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\Retargeting\Internals\EO_ServiceLog $object)
	 * @method bool has(\Bitrix\Seo\Retargeting\Internals\EO_ServiceLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog getByPrimary($primary)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog[] getAll()
	 * @method bool remove(\Bitrix\Seo\Retargeting\Internals\EO_ServiceLog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ServiceLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\Retargeting\Internals\ServiceLogTable */
		static public $dataClass = '\Bitrix\Seo\Retargeting\Internals\ServiceLogTable';
	}
}
namespace Bitrix\Seo\Retargeting\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ServiceLog_Result exec()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog fetchObject()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ServiceLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog fetchObject()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection fetchCollection()
	 */
	class EO_ServiceLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection createCollection()
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog wakeUpObject($row)
	 * @method \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection wakeUpCollection($rows)
	 */
	class EO_ServiceLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SearchEngineTable:seo/lib/searchengine.php:1235d382696fdefe0e9f89ba6cae59ee */
namespace Bitrix\Seo {
	/**
	 * EO_SearchEngine
	 * @see \Bitrix\Seo\SearchEngineTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_SearchEngine setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Seo\EO_SearchEngine setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Seo\EO_SearchEngine resetCode()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetCode()
	 * @method \string fillCode()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\EO_SearchEngine setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\EO_SearchEngine resetActive()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Seo\EO_SearchEngine setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Seo\EO_SearchEngine resetSort()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Seo\EO_SearchEngine setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\EO_SearchEngine resetName()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetName()
	 * @method \string fillName()
	 * @method \string getClientId()
	 * @method \Bitrix\Seo\EO_SearchEngine setClientId(\string|\Bitrix\Main\DB\SqlExpression $clientId)
	 * @method bool hasClientId()
	 * @method bool isClientIdFilled()
	 * @method bool isClientIdChanged()
	 * @method \string remindActualClientId()
	 * @method \string requireClientId()
	 * @method \Bitrix\Seo\EO_SearchEngine resetClientId()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetClientId()
	 * @method \string fillClientId()
	 * @method \string getClientSecret()
	 * @method \Bitrix\Seo\EO_SearchEngine setClientSecret(\string|\Bitrix\Main\DB\SqlExpression $clientSecret)
	 * @method bool hasClientSecret()
	 * @method bool isClientSecretFilled()
	 * @method bool isClientSecretChanged()
	 * @method \string remindActualClientSecret()
	 * @method \string requireClientSecret()
	 * @method \Bitrix\Seo\EO_SearchEngine resetClientSecret()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetClientSecret()
	 * @method \string fillClientSecret()
	 * @method \string getRedirectUri()
	 * @method \Bitrix\Seo\EO_SearchEngine setRedirectUri(\string|\Bitrix\Main\DB\SqlExpression $redirectUri)
	 * @method bool hasRedirectUri()
	 * @method bool isRedirectUriFilled()
	 * @method bool isRedirectUriChanged()
	 * @method \string remindActualRedirectUri()
	 * @method \string requireRedirectUri()
	 * @method \Bitrix\Seo\EO_SearchEngine resetRedirectUri()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetRedirectUri()
	 * @method \string fillRedirectUri()
	 * @method \string getSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine resetSettings()
	 * @method \Bitrix\Seo\EO_SearchEngine unsetSettings()
	 * @method \string fillSettings()
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
	 * @method \Bitrix\Seo\EO_SearchEngine set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_SearchEngine reset($fieldName)
	 * @method \Bitrix\Seo\EO_SearchEngine unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_SearchEngine wakeUp($data)
	 */
	class EO_SearchEngine {
		/* @var \Bitrix\Seo\SearchEngineTable */
		static public $dataClass = '\Bitrix\Seo\SearchEngineTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_SearchEngine_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getClientIdList()
	 * @method \string[] fillClientId()
	 * @method \string[] getClientSecretList()
	 * @method \string[] fillClientSecret()
	 * @method \string[] getRedirectUriList()
	 * @method \string[] fillRedirectUri()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method bool has(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SearchEngine getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SearchEngine[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_SearchEngine $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_SearchEngine_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_SearchEngine current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SearchEngine_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SearchEngineTable */
		static public $dataClass = '\Bitrix\Seo\SearchEngineTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SearchEngine_Result exec()
	 * @method \Bitrix\Seo\EO_SearchEngine fetchObject()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SearchEngine_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_SearchEngine fetchObject()
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection fetchCollection()
	 */
	class EO_SearchEngine_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_SearchEngine createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection createCollection()
	 * @method \Bitrix\Seo\EO_SearchEngine wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_SearchEngine_Collection wakeUpCollection($rows)
	 */
	class EO_SearchEngine_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SitemapTable:seo/lib/sitemap.php:a0556c1d3fc980102cb5bdc089ebf511 */
namespace Bitrix\Seo {
	/**
	 * EO_Sitemap
	 * @see \Bitrix\Seo\SitemapTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_Sitemap setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Seo\EO_Sitemap setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Seo\EO_Sitemap resetTimestampX()
	 * @method \Bitrix\Seo\EO_Sitemap unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getSiteId()
	 * @method \Bitrix\Seo\EO_Sitemap setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Seo\EO_Sitemap resetSiteId()
	 * @method \Bitrix\Seo\EO_Sitemap unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\EO_Sitemap setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\EO_Sitemap resetActive()
	 * @method \Bitrix\Seo\EO_Sitemap unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Seo\EO_Sitemap setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Seo\EO_Sitemap resetName()
	 * @method \Bitrix\Seo\EO_Sitemap unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getDateRun()
	 * @method \Bitrix\Seo\EO_Sitemap setDateRun(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateRun)
	 * @method bool hasDateRun()
	 * @method bool isDateRunFilled()
	 * @method bool isDateRunChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateRun()
	 * @method \Bitrix\Main\Type\DateTime requireDateRun()
	 * @method \Bitrix\Seo\EO_Sitemap resetDateRun()
	 * @method \Bitrix\Seo\EO_Sitemap unsetDateRun()
	 * @method \Bitrix\Main\Type\DateTime fillDateRun()
	 * @method \string getSettings()
	 * @method \Bitrix\Seo\EO_Sitemap setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Seo\EO_Sitemap resetSettings()
	 * @method \Bitrix\Seo\EO_Sitemap unsetSettings()
	 * @method \string fillSettings()
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
	 * @method \Bitrix\Seo\EO_Sitemap set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_Sitemap reset($fieldName)
	 * @method \Bitrix\Seo\EO_Sitemap unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_Sitemap wakeUp($data)
	 */
	class EO_Sitemap {
		/* @var \Bitrix\Seo\SitemapTable */
		static public $dataClass = '\Bitrix\Seo\SitemapTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_Sitemap_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRunList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateRun()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_Sitemap $object)
	 * @method bool has(\Bitrix\Seo\EO_Sitemap $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_Sitemap getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_Sitemap[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_Sitemap $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_Sitemap_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_Sitemap current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Sitemap_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SitemapTable */
		static public $dataClass = '\Bitrix\Seo\SitemapTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Sitemap_Result exec()
	 * @method \Bitrix\Seo\EO_Sitemap fetchObject()
	 * @method \Bitrix\Seo\EO_Sitemap_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Sitemap_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_Sitemap fetchObject()
	 * @method \Bitrix\Seo\EO_Sitemap_Collection fetchCollection()
	 */
	class EO_Sitemap_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_Sitemap createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_Sitemap_Collection createCollection()
	 * @method \Bitrix\Seo\EO_Sitemap wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_Sitemap_Collection wakeUpCollection($rows)
	 */
	class EO_Sitemap_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SitemapEntityTable:seo/lib/sitemapentity.php:11a99be45557c93ccfea3c5c26429d30 */
namespace Bitrix\Seo {
	/**
	 * EO_SitemapEntity
	 * @see \Bitrix\Seo\SitemapEntityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_SitemapEntity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Seo\EO_SitemapEntity setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Seo\EO_SitemapEntity resetEntityType()
	 * @method \Bitrix\Seo\EO_SitemapEntity unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Seo\EO_SitemapEntity setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Seo\EO_SitemapEntity resetEntityId()
	 * @method \Bitrix\Seo\EO_SitemapEntity unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \int getSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapEntity setSitemapId(\int|\Bitrix\Main\DB\SqlExpression $sitemapId)
	 * @method bool hasSitemapId()
	 * @method bool isSitemapIdFilled()
	 * @method bool isSitemapIdChanged()
	 * @method \int remindActualSitemapId()
	 * @method \int requireSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapEntity resetSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapEntity unsetSitemapId()
	 * @method \int fillSitemapId()
	 * @method \Bitrix\Seo\EO_Sitemap getSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap remindActualSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap requireSitemap()
	 * @method \Bitrix\Seo\EO_SitemapEntity setSitemap(\Bitrix\Seo\EO_Sitemap $object)
	 * @method \Bitrix\Seo\EO_SitemapEntity resetSitemap()
	 * @method \Bitrix\Seo\EO_SitemapEntity unsetSitemap()
	 * @method bool hasSitemap()
	 * @method bool isSitemapFilled()
	 * @method bool isSitemapChanged()
	 * @method \Bitrix\Seo\EO_Sitemap fillSitemap()
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
	 * @method \Bitrix\Seo\EO_SitemapEntity set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_SitemapEntity reset($fieldName)
	 * @method \Bitrix\Seo\EO_SitemapEntity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_SitemapEntity wakeUp($data)
	 */
	class EO_SitemapEntity {
		/* @var \Bitrix\Seo\SitemapEntityTable */
		static public $dataClass = '\Bitrix\Seo\SitemapEntityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_SitemapEntity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \int[] getSitemapIdList()
	 * @method \int[] fillSitemapId()
	 * @method \Bitrix\Seo\EO_Sitemap[] getSitemapList()
	 * @method \Bitrix\Seo\EO_SitemapEntity_Collection getSitemapCollection()
	 * @method \Bitrix\Seo\EO_Sitemap_Collection fillSitemap()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_SitemapEntity $object)
	 * @method bool has(\Bitrix\Seo\EO_SitemapEntity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapEntity getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapEntity[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_SitemapEntity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_SitemapEntity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_SitemapEntity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SitemapEntity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SitemapEntityTable */
		static public $dataClass = '\Bitrix\Seo\SitemapEntityTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SitemapEntity_Result exec()
	 * @method \Bitrix\Seo\EO_SitemapEntity fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapEntity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SitemapEntity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapEntity fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapEntity_Collection fetchCollection()
	 */
	class EO_SitemapEntity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapEntity createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_SitemapEntity_Collection createCollection()
	 * @method \Bitrix\Seo\EO_SitemapEntity wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_SitemapEntity_Collection wakeUpCollection($rows)
	 */
	class EO_SitemapEntity_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SitemapForumTable:seo/lib/sitemapforum.php:3ee266f67de6ec9fbd7f9b628b882728 */
namespace Bitrix\Seo {
	/**
	 * EO_SitemapForum
	 * @see \Bitrix\Seo\SitemapForumTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_SitemapForum setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Seo\EO_SitemapForum setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Seo\EO_SitemapForum resetEntityType()
	 * @method \Bitrix\Seo\EO_SitemapForum unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Seo\EO_SitemapForum setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Seo\EO_SitemapForum resetEntityId()
	 * @method \Bitrix\Seo\EO_SitemapForum unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \int getSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapForum setSitemapId(\int|\Bitrix\Main\DB\SqlExpression $sitemapId)
	 * @method bool hasSitemapId()
	 * @method bool isSitemapIdFilled()
	 * @method bool isSitemapIdChanged()
	 * @method \int remindActualSitemapId()
	 * @method \int requireSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapForum resetSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapForum unsetSitemapId()
	 * @method \int fillSitemapId()
	 * @method \Bitrix\Seo\EO_Sitemap getSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap remindActualSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap requireSitemap()
	 * @method \Bitrix\Seo\EO_SitemapForum setSitemap(\Bitrix\Seo\EO_Sitemap $object)
	 * @method \Bitrix\Seo\EO_SitemapForum resetSitemap()
	 * @method \Bitrix\Seo\EO_SitemapForum unsetSitemap()
	 * @method bool hasSitemap()
	 * @method bool isSitemapFilled()
	 * @method bool isSitemapChanged()
	 * @method \Bitrix\Seo\EO_Sitemap fillSitemap()
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
	 * @method \Bitrix\Seo\EO_SitemapForum set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_SitemapForum reset($fieldName)
	 * @method \Bitrix\Seo\EO_SitemapForum unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_SitemapForum wakeUp($data)
	 */
	class EO_SitemapForum {
		/* @var \Bitrix\Seo\SitemapForumTable */
		static public $dataClass = '\Bitrix\Seo\SitemapForumTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_SitemapForum_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \int[] getSitemapIdList()
	 * @method \int[] fillSitemapId()
	 * @method \Bitrix\Seo\EO_Sitemap[] getSitemapList()
	 * @method \Bitrix\Seo\EO_SitemapForum_Collection getSitemapCollection()
	 * @method \Bitrix\Seo\EO_Sitemap_Collection fillSitemap()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_SitemapForum $object)
	 * @method bool has(\Bitrix\Seo\EO_SitemapForum $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapForum getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapForum[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_SitemapForum $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_SitemapForum_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_SitemapForum current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SitemapForum_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SitemapForumTable */
		static public $dataClass = '\Bitrix\Seo\SitemapForumTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SitemapForum_Result exec()
	 * @method \Bitrix\Seo\EO_SitemapForum fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapForum_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SitemapForum_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapForum fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapForum_Collection fetchCollection()
	 */
	class EO_SitemapForum_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapForum createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_SitemapForum_Collection createCollection()
	 * @method \Bitrix\Seo\EO_SitemapForum wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_SitemapForum_Collection wakeUpCollection($rows)
	 */
	class EO_SitemapForum_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SitemapIblockTable:seo/lib/sitemapiblock.php:9b87798aa2fa8a08d389a0c0fd0775b4 */
namespace Bitrix\Seo {
	/**
	 * EO_SitemapIblock
	 * @see \Bitrix\Seo\SitemapIblockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_SitemapIblock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapIblock setSitemapId(\int|\Bitrix\Main\DB\SqlExpression $sitemapId)
	 * @method bool hasSitemapId()
	 * @method bool isSitemapIdFilled()
	 * @method bool isSitemapIdChanged()
	 * @method \int remindActualSitemapId()
	 * @method \int requireSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapIblock resetSitemapId()
	 * @method \Bitrix\Seo\EO_SitemapIblock unsetSitemapId()
	 * @method \int fillSitemapId()
	 * @method \int getIblockId()
	 * @method \Bitrix\Seo\EO_SitemapIblock setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \int remindActualIblockId()
	 * @method \int requireIblockId()
	 * @method \Bitrix\Seo\EO_SitemapIblock resetIblockId()
	 * @method \Bitrix\Seo\EO_SitemapIblock unsetIblockId()
	 * @method \int fillIblockId()
	 * @method \Bitrix\Seo\EO_Sitemap getSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap remindActualSitemap()
	 * @method \Bitrix\Seo\EO_Sitemap requireSitemap()
	 * @method \Bitrix\Seo\EO_SitemapIblock setSitemap(\Bitrix\Seo\EO_Sitemap $object)
	 * @method \Bitrix\Seo\EO_SitemapIblock resetSitemap()
	 * @method \Bitrix\Seo\EO_SitemapIblock unsetSitemap()
	 * @method bool hasSitemap()
	 * @method bool isSitemapFilled()
	 * @method bool isSitemapChanged()
	 * @method \Bitrix\Seo\EO_Sitemap fillSitemap()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Seo\EO_SitemapIblock setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Seo\EO_SitemapIblock resetIblock()
	 * @method \Bitrix\Seo\EO_SitemapIblock unsetIblock()
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
	 * @method \Bitrix\Seo\EO_SitemapIblock set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_SitemapIblock reset($fieldName)
	 * @method \Bitrix\Seo\EO_SitemapIblock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_SitemapIblock wakeUp($data)
	 */
	class EO_SitemapIblock {
		/* @var \Bitrix\Seo\SitemapIblockTable */
		static public $dataClass = '\Bitrix\Seo\SitemapIblockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_SitemapIblock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSitemapIdList()
	 * @method \int[] fillSitemapId()
	 * @method \int[] getIblockIdList()
	 * @method \int[] fillIblockId()
	 * @method \Bitrix\Seo\EO_Sitemap[] getSitemapList()
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection getSitemapCollection()
	 * @method \Bitrix\Seo\EO_Sitemap_Collection fillSitemap()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_SitemapIblock $object)
	 * @method bool has(\Bitrix\Seo\EO_SitemapIblock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapIblock getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapIblock[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_SitemapIblock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_SitemapIblock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_SitemapIblock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SitemapIblock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SitemapIblockTable */
		static public $dataClass = '\Bitrix\Seo\SitemapIblockTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SitemapIblock_Result exec()
	 * @method \Bitrix\Seo\EO_SitemapIblock fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SitemapIblock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapIblock fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection fetchCollection()
	 */
	class EO_SitemapIblock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapIblock createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection createCollection()
	 * @method \Bitrix\Seo\EO_SitemapIblock wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_SitemapIblock_Collection wakeUpCollection($rows)
	 */
	class EO_SitemapIblock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\SitemapRuntimeTable:seo/lib/sitemapruntime.php:9402ab4105fb9526908e661c8297ea3d */
namespace Bitrix\Seo {
	/**
	 * EO_SitemapRuntime
	 * @see \Bitrix\Seo\SitemapRuntimeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPid()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setPid(\int|\Bitrix\Main\DB\SqlExpression $pid)
	 * @method bool hasPid()
	 * @method bool isPidFilled()
	 * @method bool isPidChanged()
	 * @method \int remindActualPid()
	 * @method \int requirePid()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetPid()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetPid()
	 * @method \int fillPid()
	 * @method \boolean getProcessed()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setProcessed(\boolean|\Bitrix\Main\DB\SqlExpression $processed)
	 * @method bool hasProcessed()
	 * @method bool isProcessedFilled()
	 * @method bool isProcessedChanged()
	 * @method \boolean remindActualProcessed()
	 * @method \boolean requireProcessed()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetProcessed()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetProcessed()
	 * @method \boolean fillProcessed()
	 * @method \string getItemPath()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setItemPath(\string|\Bitrix\Main\DB\SqlExpression $itemPath)
	 * @method bool hasItemPath()
	 * @method bool isItemPathFilled()
	 * @method bool isItemPathChanged()
	 * @method \string remindActualItemPath()
	 * @method \string requireItemPath()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetItemPath()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetItemPath()
	 * @method \string fillItemPath()
	 * @method \int getItemId()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setItemId(\int|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \int remindActualItemId()
	 * @method \int requireItemId()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetItemId()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetItemId()
	 * @method \int fillItemId()
	 * @method \string getItemType()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setItemType(\string|\Bitrix\Main\DB\SqlExpression $itemType)
	 * @method bool hasItemType()
	 * @method bool isItemTypeFilled()
	 * @method bool isItemTypeChanged()
	 * @method \string remindActualItemType()
	 * @method \string requireItemType()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetItemType()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetItemType()
	 * @method \string fillItemType()
	 * @method \boolean getActive()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetActive()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getActiveElement()
	 * @method \Bitrix\Seo\EO_SitemapRuntime setActiveElement(\boolean|\Bitrix\Main\DB\SqlExpression $activeElement)
	 * @method bool hasActiveElement()
	 * @method bool isActiveElementFilled()
	 * @method bool isActiveElementChanged()
	 * @method \boolean remindActualActiveElement()
	 * @method \boolean requireActiveElement()
	 * @method \Bitrix\Seo\EO_SitemapRuntime resetActiveElement()
	 * @method \Bitrix\Seo\EO_SitemapRuntime unsetActiveElement()
	 * @method \boolean fillActiveElement()
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
	 * @method \Bitrix\Seo\EO_SitemapRuntime set($fieldName, $value)
	 * @method \Bitrix\Seo\EO_SitemapRuntime reset($fieldName)
	 * @method \Bitrix\Seo\EO_SitemapRuntime unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\EO_SitemapRuntime wakeUp($data)
	 */
	class EO_SitemapRuntime {
		/* @var \Bitrix\Seo\SitemapRuntimeTable */
		static public $dataClass = '\Bitrix\Seo\SitemapRuntimeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo {
	/**
	 * EO_SitemapRuntime_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPidList()
	 * @method \int[] fillPid()
	 * @method \boolean[] getProcessedList()
	 * @method \boolean[] fillProcessed()
	 * @method \string[] getItemPathList()
	 * @method \string[] fillItemPath()
	 * @method \int[] getItemIdList()
	 * @method \int[] fillItemId()
	 * @method \string[] getItemTypeList()
	 * @method \string[] fillItemType()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getActiveElementList()
	 * @method \boolean[] fillActiveElement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\EO_SitemapRuntime $object)
	 * @method bool has(\Bitrix\Seo\EO_SitemapRuntime $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapRuntime getByPrimary($primary)
	 * @method \Bitrix\Seo\EO_SitemapRuntime[] getAll()
	 * @method bool remove(\Bitrix\Seo\EO_SitemapRuntime $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\EO_SitemapRuntime_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\EO_SitemapRuntime current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SitemapRuntime_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\SitemapRuntimeTable */
		static public $dataClass = '\Bitrix\Seo\SitemapRuntimeTable';
	}
}
namespace Bitrix\Seo {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SitemapRuntime_Result exec()
	 * @method \Bitrix\Seo\EO_SitemapRuntime fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapRuntime_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SitemapRuntime_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapRuntime fetchObject()
	 * @method \Bitrix\Seo\EO_SitemapRuntime_Collection fetchCollection()
	 */
	class EO_SitemapRuntime_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\EO_SitemapRuntime createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\EO_SitemapRuntime_Collection createCollection()
	 * @method \Bitrix\Seo\EO_SitemapRuntime wakeUpObject($row)
	 * @method \Bitrix\Seo\EO_SitemapRuntime_Collection wakeUpCollection($rows)
	 */
	class EO_SitemapRuntime_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Seo\WebHook\Internals\WebHookTable:seo/lib/webhook/internals/webhook.php:6925cc60aa1c3492d1aad3ed280b169b */
namespace Bitrix\Seo\WebHook\Internals {
	/**
	 * EO_WebHook
	 * @see \Bitrix\Seo\WebHook\Internals\WebHookTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook resetDateInsert()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getType()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook resetType()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook unsetType()
	 * @method \string fillType()
	 * @method \string getExternalId()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook resetExternalId()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \string getSecurityCode()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook setSecurityCode(\string|\Bitrix\Main\DB\SqlExpression $securityCode)
	 * @method bool hasSecurityCode()
	 * @method bool isSecurityCodeFilled()
	 * @method bool isSecurityCodeChanged()
	 * @method \string remindActualSecurityCode()
	 * @method \string requireSecurityCode()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook resetSecurityCode()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook unsetSecurityCode()
	 * @method \string fillSecurityCode()
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
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook set($fieldName, $value)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook reset($fieldName)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook wakeUp($data)
	 */
	class EO_WebHook {
		/* @var \Bitrix\Seo\WebHook\Internals\WebHookTable */
		static public $dataClass = '\Bitrix\Seo\WebHook\Internals\WebHookTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Seo\WebHook\Internals {
	/**
	 * EO_WebHook_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \string[] getSecurityCodeList()
	 * @method \string[] fillSecurityCode()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Seo\WebHook\Internals\EO_WebHook $object)
	 * @method bool has(\Bitrix\Seo\WebHook\Internals\EO_WebHook $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook getByPrimary($primary)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook[] getAll()
	 * @method bool remove(\Bitrix\Seo\WebHook\Internals\EO_WebHook $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WebHook_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Seo\WebHook\Internals\WebHookTable */
		static public $dataClass = '\Bitrix\Seo\WebHook\Internals\WebHookTable';
	}
}
namespace Bitrix\Seo\WebHook\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WebHook_Result exec()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook fetchObject()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WebHook_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook fetchObject()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection fetchCollection()
	 */
	class EO_WebHook_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook createObject($setDefaultValues = true)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection createCollection()
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook wakeUpObject($row)
	 * @method \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection wakeUpCollection($rows)
	 */
	class EO_WebHook_Entity extends \Bitrix\Main\ORM\Entity {}
}