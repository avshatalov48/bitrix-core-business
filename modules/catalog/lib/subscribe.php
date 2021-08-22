<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Entity,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Application,
	Bitrix\Main\Event,
	Bitrix\Main\EventManager,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Loader,
	Bitrix\Landing\Connector\Iblock as IblockConnector;

Loc::loadMessages(__FILE__);

/**
 * Class SubscribeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATE_FROM datetime mandatory
 * <li> DATE_TO datetime optional
 * <li> USER_CONTACT string mandatory
 * <li> CONTACT_TYPE int mandatory
 * <li> USER_ID int optional
 * <li> USER reference to {@link \Bitrix\Main\UserTable}
 * <li> ITEM_ID int mandatory
 * <li> PRODUCT reference to {@link ProductTable}
 * <li> IBLOCK_ELEMENT reference to {@link \Bitrix\Iblock\ElementTable}
 * <li> NEED_SENDING bool default=N
 * <li> SITE_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Subscribe_Query query()
 * @method static EO_Subscribe_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Subscribe_Result getById($id)
 * @method static EO_Subscribe_Result getList(array $parameters = array())
 * @method static EO_Subscribe_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Subscribe createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Subscribe_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Subscribe wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Subscribe_Collection wakeUpCollection($rows)
 */
class SubscribeTable extends Entity\DataManager
{
	const EVENT_ADD_CONTACT_TYPE = 'onAddContactType';

	const CONTACT_TYPE_EMAIL = 1;
	const LIMIT_SEND = 50;
	const AGENT_TIME_OUT = 10;
	const AGENT_INTERVAL = 10;

	private static $oldProductAvailable = array();
	private static $agentNoticeCreated = false;
	private static $agentRepeatedNoticeCreated = false;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_subscribe';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_FROM' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'DATE_TO' => array(
				'data_type' => 'datetime',
			),
			'USER_CONTACT' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CONTACT_TYPE' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'ITEM_ID' => array(
				'data_type' => 'integer',
			),
			'PRODUCT' => array(
				'data_type' => 'Bitrix\Catalog\ProductTable',
				'reference' => array('=this.ITEM_ID' => 'ref.ID'),
			),
			'IBLOCK_ELEMENT' => array(
				'data_type' => 'Bitrix\Iblock\ElementTable',
				'reference' => array('=this.ITEM_ID' => 'ref.ID'),
			),
			'NEED_SENDING' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'validation' => array(__CLASS__, 'validateNeedSending'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSiteId'),
			),
			'LANDING_SITE_ID' => array(
				'data_type' => 'integer',
			),
		);
	}

	/**
	 * Returns validators for NEED_SENDING field.
	 *
	 * @return array
	 */
	public static function validateNeedSending()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}

	/**
	 * Handler onUserDelete for change subscription data when removing a user.
	 *
	 * @param integer $userId Id user.
	 * @return bool
	 */
	public static function onUserDelete($userId)
	{
		$userId = (int)$userId;
		if ($userId <= 0)
			return false;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('update '.$helper->quote(static::getTableName()).' set '
			.$helper->quote('DATE_TO').' = '.$helper->getCurrentDateTimeFunction().', '
			.$helper->quote('USER_ID').' = \'NULL\' where '.$helper->quote('USER_ID').' = '.$userId
		);

		return true;
	}

	/**
	 * Handler onIblockElementDelete for delete the data on the subscription in case of removal of product.
	 *
	 * @param integer $productId Id product.
	 * @return bool
	 */
	public static function onIblockElementDelete($productId)
	{
		$productId = (int)$productId;
		if ($productId <= 0)
			return true;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('delete from '.$helper->quote(static::getTableName()).' where '
			.$helper->quote('ITEM_ID').' = '.$productId
		);

		return true;
	}

	/**
	 * Handler OnSaleOrderSaved to unsubscribe when ordering.
	 *
	 * @param Event $event Object event.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onSaleOrderSaved(Event $event)
	{
		if(!$event->getParameter('IS_NEW'))
			return;

		$order = $event->getParameter('ENTITY');

		if($order instanceof Sale\Order)
		{
			$userId = $order->getUserId();
			$siteId = $order->getSiteId();
			$basketObject = $order->getBasket();
			$listProductId = array();
			/** @var \Bitrix\Sale\BasketItem $item */
			foreach ($basketObject->getBasketItems() as $item)
				$listProductId[] = $item->getProductId();

			if(!$userId || empty($listProductId))
				return;

			$user = \CUser::getList('ID', 'ASC',
				array('ID' => $userId) , array('FIELDS' => array('EMAIL'))
			)->fetch();
			if($user['EMAIL'])
			{
				$listSubscribe = static::getList(array(
					'select' => array('ID'),
					'filter' => array(
						'=USER_CONTACT' => $user['EMAIL'],
						'=SITE_ID' => $siteId,
						'ITEM_ID' => $listProductId,
					),
				))->fetchAll();
				$listSubscribeId = array();
				foreach($listSubscribe as $subscribe)
					$listSubscribeId[] = $subscribe['ID'];

				static::unSubscribe($listSubscribeId);
			}
		}
	}

	/**
	 * The method returns an array of available types and allows you to add a type using an event.
	 * When you add a new type of need to add a 'RULE' to validate the field 'USER_CONTACT'.
	 * And also a function that will send messages to the specified type.
	 *
	 * @return array array(typeId=>array(ID => typeId, NAME => typeName, RULE => validateRule, HANDLER => function())).
	 */
	public static function getContactTypes()
	{
		$contactTypes = array();

		$event = new Event('catalog', static::EVENT_ADD_CONTACT_TYPE, array(&$contactTypes));
		$event->send();

		if(!is_array($contactTypes))
			return array();

		$availableFields = array('ID', 'NAME', 'RULE', 'HANDLER');
		foreach($contactTypes as $typeId => $typeData)
		{
			$currentFields = array_keys($typeData);
			$divergenceFields = array_diff($availableFields, $currentFields);
			if(!empty($divergenceFields))
			{
				unset($contactTypes[$typeId]);
				continue;
			}
			if(!is_string($typeData['NAME']) || !is_string($typeData['RULE']) || !is_callable($typeData['HANDLER']))
			{
				unset($contactTypes[$typeId]);
			}
		}

		return $contactTypes;
	}

	/**
	 * Handler onAddContactType. Adding a new contact type.
	 *
	 * @param array &$contactTypes Contact type.
	 * @return void
	 */
	public static function onAddContactType(&$contactTypes)
	{
		$contactTypes[static::CONTACT_TYPE_EMAIL] = array(
			'ID' => static::CONTACT_TYPE_EMAIL,
			'NAME' => Loc::getMessage('CONTACT_TYPE_EMAIL_NAME'),
			'RULE' => '/@/i',
			'HANDLER' => function(Event $event)
			{
				$eventData = $event->getParameters();
				$eventObject = new \CEvent;
				foreach($eventData as $userContact => $dataList)
				{
					foreach($dataList as $data)
					{
						$eventObject->send($data['EVENT_NAME'], $data['SITE_ID'], $data);
					}
				}
				return true;
			}
		);
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * Method for send a notification to subscribers about positive change available.
	 *
	 * @param integer $productId Id product.
	 * @param array $fields An array of event data.
	 * @return bool
	 */
	public static function onProductUpdate($productId, $fields)
	{
		return static::checkOldProductAvailable($productId, $fields);
	}

	/**
	 * Method OnProductSetAvailableUpdate for send a notification to subscribers about positive change available.
	 *
	 * @param integer $productId Id product.
	 * @param array $fields An array of event data.
	 * @return bool
	 */
	public static function onProductSetAvailableUpdate($productId, $fields)
	{
		return static::checkOldProductAvailable($productId, $fields);
	}

	/**
	 * The method runs the agent to send notifications to subscribers.
	 *
	 * @param integer $productId Id product.
	 * @return bool
	 */
	public static function runAgentToSendNotice($productId)
	{
		$productId = (int)$productId;
		if ($productId <= 0)
			return false;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('update '.$helper->quote(static::getTableName()).' set '
			.$helper->quote('NEED_SENDING').' = \'Y\' where '.$helper->quote('ITEM_ID').' = '.$productId
			.' and ('.$helper->quote('DATE_TO').' is null or '.$helper->quote('DATE_TO').' > '
			.$helper->getCurrentDateTimeFunction().')'
		);

		if (!static::$agentNoticeCreated)
		{
			$t = DateTime::createFromTimestamp(time() + static::AGENT_TIME_OUT);
			static::$agentNoticeCreated = true;
			\CAgent::AddAgent(
				'\Bitrix\Catalog\SubscribeTable::sendNotice();',
				'catalog',
				'N',
				static::AGENT_INTERVAL,
				'',
				'Y',
				$t->toString(),
				100,
				false,
				false
			);
		}

		return true;
	}

	/**
	 * The method runs the agent to send repeated notifications to subscribers.
	 *
	 * @param integer $productId Id product.
	 * @return bool
	 */
	public static function runAgentToSendRepeatedNotice($productId)
	{
		$productId = (int)$productId;
		if ($productId <= 0 || (string)Option::get('catalog', 'subscribe_repeated_notify') != 'Y')
			return false;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('update '.$helper->quote(static::getTableName()).' set '
			.$helper->quote('NEED_SENDING').' = \'Y\' where '.$helper->quote('ITEM_ID').' = '.$productId
			.' and ('.$helper->quote('DATE_TO').' is null or '.$helper->quote('DATE_TO').' > '
			.$helper->getCurrentDateTimeFunction().')'
		);

		if (!static::$agentRepeatedNoticeCreated)
		{
			$t = DateTime::createFromTimestamp(time() + static::AGENT_TIME_OUT);
			static::$agentRepeatedNoticeCreated = true;
			\CAgent::AddAgent(
				'Bitrix\Catalog\SubscribeTable::sendRepeatedNotice();',
				'catalog',
				'N',
				static::AGENT_INTERVAL,
				'',
				'Y',
				$t->toString(),
				100,
				false,
				false
			);
		}

		return true;
	}

	/**
	 * The method checks permission the subscription for the product.
	 *
	 * @param string $subscribe The field value SUBSCRIBE of the product.
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function checkPermissionSubscribe($subscribe)
	{
		return $subscribe == 'Y' || ($subscribe == 'D' && (string)Option::get('catalog', 'default_subscribe') == 'Y');
	}

	/**
	 * The method stores the value of availability of product before updating.
	 *
	 * @param integer $productId Product id.
	 * @param string $available The field value AVAILABLE of the product.
	 * @return bool
	 */
	public static function setOldProductAvailable($productId, $available)
	{
		if(!$productId || !$available)
		{
			return false;
		}

		static::$oldProductAvailable[$productId]['AVAILABLE'] = $available;

		return true;
	}

	/**
	 * Agent function. Get the necessary data and send notifications to users.
	 *
	 * @return string
	 */
	public static function sendNotice()
	{
		if(static::checkLastUpdate())
			return '\Bitrix\Catalog\SubscribeTable::sendNotice();';

		list($listSubscribe, $totalCount) = static::getSubscriptionsData();

		if(empty($listSubscribe))
		{
			static::$agentNoticeCreated = false;
			return '';
		}

		$anotherStep = (int)$totalCount['CNT'] > static::LIMIT_SEND;

		list($dataSendToNotice, $listNotifiedSubscribeId) =
			static::prepareDataForNotice($listSubscribe, 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY');

		static::startEventNotification($dataSendToNotice);

		if($listNotifiedSubscribeId)
			static::setNeedSending($listNotifiedSubscribeId);

		if($anotherStep)
		{
			return '\Bitrix\Catalog\SubscribeTable::sendNotice();';
		}
		else
		{
			static::$agentNoticeCreated = false;
			return '';
		}
	}

	/**
	 * Agent function. Get the necessary data and send notifications to users.
	 *
	 * @return string
	 */
	public static function sendRepeatedNotice()
	{
		if(static::checkLastUpdate())
			return 'Bitrix\Catalog\SubscribeTable::sendRepeatedNotice();';

		list($listSubscribe, $totalCount) = static::getSubscriptionsData();

		if(empty($listSubscribe))
		{
			static::$agentRepeatedNoticeCreated = false;
			return '';
		}

		$anotherStep = (int)$totalCount['CNT'] > static::LIMIT_SEND;

		list($dataSendToNotice, $listNotifiedSubscribeId) =
			static::prepareDataForNotice($listSubscribe, 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED');

		static::startEventNotification($dataSendToNotice);

		if($listNotifiedSubscribeId)
			static::setNeedSending($listNotifiedSubscribeId);

		if($anotherStep)
		{
			return 'Bitrix\Catalog\SubscribeTable::sendRepeatedNotice();';
		}
		else
		{
			static::$agentRepeatedNoticeCreated = false;
			return '';
		}
	}

	/**
	 * The method checks the old and new product availability.
	 *
	 * @param integer $productId Id product.
	 * @param array $fields An array of event data.
	 * @return bool
	 */
	protected static function checkOldProductAvailable($productId, $fields)
	{
		$productId = (int)$productId;
		if ($productId <= 0 || (empty(static::$oldProductAvailable[$productId]))
			|| !static::checkPermissionSubscribe($fields['SUBSCRIBE']))
		{
			return false;
		}

		if(static::$oldProductAvailable[$productId]['AVAILABLE'] == ProductTable::STATUS_NO
			&& $fields['AVAILABLE'] == ProductTable::STATUS_YES)
		{
			static::runAgentToSendNotice($productId);
		}
		elseif(static::$oldProductAvailable[$productId]['AVAILABLE'] == ProductTable::STATUS_YES
			&& $fields['AVAILABLE'] == ProductTable::STATUS_NO)
		{
			static::runAgentToSendRepeatedNotice($productId);
		}

		unset(static::$oldProductAvailable[$productId]);

		return true;
	}

	/**
	 * Return true, if the last update products was completed less than self::AGENT_TIME_OUT seconds ago.
	 *
	 * @return bool
	 */
	protected static function checkLastUpdate()
	{
		$lastUpdate = ProductTable::getList(
			array(
				'select' => array('TIMESTAMP_X'),
				'order' => array('TIMESTAMP_X' => 'DESC'),
				'limit' => 1
			)
		)->fetch();
		if (empty($lastUpdate) || !($lastUpdate['TIMESTAMP_X'] instanceof DateTime))
			return true;

		return (time() - $lastUpdate['TIMESTAMP_X']->getTimestamp() < static::AGENT_TIME_OUT);
	}

	protected static function getSubscriptionsData()
	{
		global $DB;

		$filter = array(
			'=NEED_SENDING' => 'Y',
			'!=PRODUCT.SUBSCRIBE' => 'N',
			array(
				'LOGIC' => 'OR',
				array('=DATE_TO' => false),
				array('>DATE_TO' => date($DB->dateFormatToPHP(\CLang::getDateFormat('FULL')), time()))
			)
		);

		/* Compatibility with the sale subscribe option */
		$notifyOption = Option::get('sale', 'subscribe_prod');
		$notify = array();
		if($notifyOption <> '')
			$notify = unserialize($notifyOption, ['allowed_classes' => false]);
		if(is_array($notify))
		{
			$listSiteId = array();
			foreach($notify as $siteId => $data)
			{
				if($data['use'] != 'Y')
					$listSiteId[] = $siteId;
			}
			if($listSiteId)
				$filter['!=SITE_ID'] = $listSiteId;
		}

		$listSubscribe = static::getList(array(
			'select'=>array(
				'ID',
				'USER_CONTACT',
				'CONTACT_TYPE',
				'DATE_TO',
				'PRODUCT_NAME' => 'IBLOCK_ELEMENT.NAME',
				'DETAIL_PAGE_URL' => 'IBLOCK_ELEMENT.IBLOCK.DETAIL_PAGE_URL',
				'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
				'TYPE' => 'PRODUCT.TYPE',
				'ITEM_ID',
				'SITE_ID',
				'LANDING_SITE_ID',
				'USER_NAME' => 'USER.NAME',
				'USER_LAST_NAME' => 'USER.LAST_NAME',
			),
			'filter' => $filter,
			'limit' => static::LIMIT_SEND,
		))->fetchAll();

		$totalCount = static::getList(array(
			'select' => array('CNT'),
			'filter' => $filter,
			'runtime' => array(new Entity\ExpressionField('CNT', 'COUNT(*)'))
		))->fetch();

		return array($listSubscribe, $totalCount);
	}

	protected static function prepareDataForNotice(array $listSubscribe, $eventName)
	{
		/* Preparation of data for the mail template */
		$itemIdGroupByIblock = array();
		foreach($listSubscribe as $key => $subscribeData)
			$itemIdGroupByIblock[$subscribeData['IBLOCK_ID']][$subscribeData['ITEM_ID']] = $subscribeData['ITEM_ID'];

		$detailPageUrlGroupByItemId = array();
		if(!empty($itemIdGroupByIblock))
		{
			foreach($itemIdGroupByIblock as $iblockId => $listItemId)
			{
				$queryObject = \CIBlockElement::getList(array('ID'=>'ASC'),
					array('IBLOCK_ID' => $iblockId, 'ID' => $listItemId), false, false, array('DETAIL_PAGE_URL'));
				while($result = $queryObject->getNext())
					$detailPageUrlGroupByItemId[$result['ID']] = $result['DETAIL_PAGE_URL'];
			}
		}

		$dataSendToNotice = array();
		$listNotifiedSubscribeId = array();
		foreach($listSubscribe as $key => $subscribeData)
		{
			$pageUrl = self::getPageUrl($subscribeData, $detailPageUrlGroupByItemId);
			if ($pageUrl == '')
			{
				continue;
			}

			$listNotifiedSubscribeId[] = $subscribeData['ID'];

			$subscribeData['EVENT_NAME'] = $eventName;
			$subscribeData['USER_NAME'] = $subscribeData['USER_NAME'] ?
				$subscribeData['USER_NAME'] : Loc::getMessage('EMAIL_TEMPLATE_USER_NAME');
			$subscribeData['EMAIL_TO'] = $subscribeData['USER_CONTACT'];
			$subscribeData['NAME'] = $subscribeData['PRODUCT_NAME'];
			$subscribeData['PAGE_URL'] = $pageUrl;
			$subscribeData['PRODUCT_ID'] = $subscribeData['ITEM_ID'];
			$subscribeData['CHECKOUT_URL'] = \CHTTP::urlAddParams($pageUrl, array(
				'action' => 'BUY', 'id' => $subscribeData['PRODUCT_ID']));
			$subscribeData['CHECKOUT_URL_PARAMETERS'] = \CHTTP::urlAddParams('', array(
				'action' => 'BUY', 'id' => $subscribeData['PRODUCT_ID']));
			$subscribeData['UNSUBSCRIBE_URL'] = \CHTTP::urlAddParams(
				self::getUnsubscribeUrl($subscribeData),
				array('unSubscribe' => 'Y', 'subscribeId' => $subscribeData['ID'],
					'userContact' => $subscribeData['USER_CONTACT'], 'productId' => $subscribeData['PRODUCT_ID']));
			$subscribeData['UNSUBSCRIBE_URL_PARAMETERS'] = \CHTTP::urlAddParams('',
				array('unSubscribe' => 'Y', 'subscribeId' => $subscribeData['ID'],
					'userContact' => $subscribeData['USER_CONTACT'], 'productId' => $subscribeData['PRODUCT_ID']));

			$dataSendToNotice[$subscribeData['CONTACT_TYPE']][$subscribeData['USER_CONTACT']][$key] = $subscribeData;
		}

		return array($dataSendToNotice, $listNotifiedSubscribeId);
	}

	private static function getPageUrl(array $subscribeData, array $detailPageUrlGroupByItemId)
	{
		$pageUrl = "";

		if (!empty($subscribeData['LANDING_SITE_ID']))
		{
			$pageUrl = Loader::includeModule('landing') ?
				IblockConnector::getElementUrl($subscribeData['LANDING_SITE_ID'], $subscribeData['ITEM_ID']) : "";
		}
		elseif (!empty($detailPageUrlGroupByItemId[$subscribeData['ITEM_ID']]))
		{
			$pageUrl = self::getProtocol().self::getServerName($subscribeData['SITE_ID']).
				$detailPageUrlGroupByItemId[$subscribeData['ITEM_ID']];
		}

		return $pageUrl;
	}

	private static function getUnsubscribeUrl(array $subscribeData)
	{
		if (!empty($subscribeData['LANDING_SITE_ID']))
		{
			$unsubscribeUrl = '';
			if (Loader::includeModule('landing'))
			{
				$unsubscribeUrl = \Bitrix\Landing\Syspage::getSpecialPage(
					$subscribeData['LANDING_SITE_ID'],
					'personal',
					['SECTION' => 'subscribe']
				);
			}
		}
		else
		{
			$unsubscribeUrl = self::getProtocol().self::getServerName(
				$subscribeData['SITE_ID']).'/personal/subscribe/';
		}

		return $unsubscribeUrl;
	}

	private static function getProtocol()
	{
		$currentApplication = Application::getInstance();
		$context = $currentApplication->getContext();

		if ($protocol = Option::get('main', 'mail_link_protocol'))
		{
			if (mb_strrpos($protocol, '://') === false)
				$protocol .= '://';
		}
		else
		{
			if ($context->getServer()->getServerName())
			{
				$protocol = ($context->getRequest()->isHttps() ? 'https://' : 'http://');
			}
			else
			{
				$protocol = 'https://';
			}
		}

		unset($currentApplication);
		unset($context);

		return $protocol;
	}

	private static function getServerName($siteId)
	{
		$serverName = '';
		$iterator = \CSite::GetByID($siteId);
		$site = $iterator->fetch();
		unset($iterator);
		if (!empty($site))
			$serverName = (string)$site['SERVER_NAME'];
		unset($site);
		if ($serverName == '')
		{
			$serverName = (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '' ?
				SITE_SERVER_NAME : (string)Option::get('main', 'server_name', '', $siteId)
			);
			if ($serverName == '')
			{
				$currentApplication = Application::getInstance();
				$context = $currentApplication->getContext();
				$serverName = $context->getServer()->getServerName();
				unset($currentApplication);
				unset($context);
			}
		}

		return $serverName;
	}

	protected static function startEventNotification(array $dataSendToNotice)
	{
		$contactTypes = static::getContactTypes();
		foreach($contactTypes as $typeId => $typeData)
		{
			if (empty($dataSendToNotice[$typeId]))
			{
				continue;
			}

			$eventKey = EventManager::getInstance()
				->addEventHandler('catalog', 'OnSubscribeSubmit', $typeData['HANDLER']);

			$event = new Event('catalog', 'OnSubscribeSubmit', $dataSendToNotice[$typeId]);
			$event->send();

			EventManager::getInstance()->removeEventHandler('catalog', 'OnSubscribeSubmit', $eventKey);
		}
	}

	private static function setNeedSending(array $listSubscribeId, $needSending = 'N')
	{
		if(empty($listSubscribeId))
			return;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('update '.$helper->quote(static::getTableName()).' set '
			.$helper->quote('NEED_SENDING').' = \''.$needSending.'\' where '
			.$helper->quote('ID').' in ('.implode(',', $listSubscribeId).')'
		);
	}

	private static function unSubscribe(array $listSubscribeId)
	{
		if(empty($listSubscribeId))
			return;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('update '.$helper->quote(static::getTableName()).' set '
			.$helper->quote('NEED_SENDING').' = \'N\', '.$helper->quote('DATE_TO').' ='
			.$helper->getCurrentDateTimeFunction().' where '
			.$helper->quote('ID').' in ('.implode(',', $listSubscribeId).')'
		);
	}
}