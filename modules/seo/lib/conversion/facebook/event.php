<?php

namespace Bitrix\Seo\Conversion\Facebook;


use Bitrix\Seo\Conversion\ConversionEventInterface;

class Event implements ConversionEventInterface
{
	private $container = [];

	// action source values : https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#action-source
	public const ACTION_SOURCE_EMAIL = 'email';
	public const ACTION_SOURCE_WEBSITE = 'website';
	public const ACTION_SOURCE_APP = 'app';
	public const ACTION_SOURCE_PHONE_CALL = 'phone_call';
	public const ACTION_SOURCE_CHAT = 'chat';
	public const ACTION_SOURCE_PHYSICAL_STORE = 'physical_store';
	public const ACTION_SOURCE_SYSTEM_GENERATED = 'system_generated';
	public const ACTION_SOURCE_OTHER = 'other';

	// events description: https://developers.facebook.com/docs/facebook-pixel/reference#standard-events
	public const EVENT_ADD_PAYMENT = 'AddPaymentInfo';
	public const EVENT_ADD_TO_CART = 'AddToCart';
	public const EVENT_ADD_TO_WISH_LIST = 'AddToWishlist';
	public const EVENT_COMPLETE_REGISTRATION = 'CompleteRegistration';
	public const EVENT_CONTACT = 'Contact';
	public const EVENT_DONATE = 'CustomizeProduct';
	public const EVENT_FIND_LOCATION = 'FindLocation';
	public const EVENT_INITIATE_CHECKOUT = 'InitiateCheckout';
	public const EVENT_LEAD = 'Lead';
	public const EVENT_PAGE_VIEW = 'PageView';
	public const EVENT_PURCHASE = 'Purchase';
	public const EVENT_SEARCH = 'Search';
	public const EVENT_START_TRIAL = 'StartTrial';
	public const EVENT_SUBMIT_APPLICATION = 'SubmitApplication';
	public const EVENT_SUBSCRIBE = 'Subscribe';
	public const EVENT_VIEW_CONTENT = 'ViewContent';

	private function setParameter(string $key,$value)
	{
		$this->container[$key] = $value;
	}

	private function getParameter(string $key)
	{
		return array_key_exists($key,$this->container)? $this->container[$key] : null;
	}

	public static function getEventTypeList()
	{
		return [
			static::EVENT_ADD_PAYMENT,
			static::EVENT_ADD_TO_CART,
			static::EVENT_ADD_TO_WISH_LIST,
			static::EVENT_COMPLETE_REGISTRATION,
			static::EVENT_CONTACT,
			static::EVENT_DONATE,
			static::EVENT_FIND_LOCATION,
			static::EVENT_INITIATE_CHECKOUT,
			static::EVENT_LEAD,
			static::EVENT_PAGE_VIEW,
			static::EVENT_SEARCH,
			static::EVENT_START_TRIAL,
			static::EVENT_SUBMIT_APPLICATION,
			static::EVENT_SUBSCRIBE,
			static::EVENT_VIEW_CONTENT,
			static::EVENT_PURCHASE
		];
	}
	/**
	 *
	 * @return array
	 */
	public static function getActionSourceList()
	{
		return [
			static::ACTION_SOURCE_EMAIL,
			static::ACTION_SOURCE_WEBSITE,
			static::ACTION_SOURCE_APP,
			static::ACTION_SOURCE_PHONE_CALL,
			static::ACTION_SOURCE_CHAT,
			static::ACTION_SOURCE_PHYSICAL_STORE,
			static::ACTION_SOURCE_SYSTEM_GENERATED,
			static::ACTION_SOURCE_OTHER
		];
	}

	/**
	 * Event constructor.
	 *
	 * @param array|null $params
	 */
	public function __construct(?array $params = null)
	{
		if ($params && !empty($params))
		{
			if (array_key_exists('action_source',$params) && is_string($params['action_source']))
			{
				$this->setActionSource($params['action_source']);
			}
			if (array_key_exists('event_time',$params) && is_int($params['event_time']))
			{
				$this->setTime($params['event_time']);
			}
			if (array_key_exists('opt_out',$params) && is_bool($params['opt_out']))
			{
				$this->setDynamicAdsOption($params['opt_out']);
			}
			if (array_key_exists('event_name',$params) && is_string($params['event_name']))
			{
				$this->setEventType($params['event_name']);
			}
			if (array_key_exists('event_source_url',$params) && is_string($params['event_source_url']))
			{
				$this->setSource($params['event_source_url']);
			}
			if (array_key_exists('user_data',$params))
			{
				if ($params['user_data'] instanceof UserData)
				{
					$this->setUserData($params['user_data']);

				} elseif (is_array($params['user_data']))
				{
					$this->setUserData(new UserData($params['user_data']));
				}
			}
			if (array_key_exists('custom_data',$params))
			{
				if ($params['custom_data'] instanceof CustomData)
				{
					$this->setCustomData($params['custom_data']);

				} elseif (is_array($params['custom_data']))
				{
					$this->setCustomData(new CustomData($params['custom_data']));
				}
			}
		}

		if (!$this->getParameter('event_time'))
		{
			$this->setParameter('event_time',time());
		}

	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#action-source
	 * @param $action
	 *
	 * @return $this
	 */
	public function setActionSource(?string $action = self::ACTION_SOURCE_WEBSITE)
	{
		if(in_array($action,static::getActionSourceList()))
		{
			$this->setParameter('action_source',$action);
		}
		return $this;
	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#event-time
	 * @param int|null $timeStamp
	 *
	 * @return $this
	 */
	public function setTime(?int $timeStamp)
	{

		if(is_int($timeStamp))
		{
			$this->setParameter('event_time',$timeStamp);
		}
		return $this;
	}
	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#opt-out
	 * @param bool $option
	 *
	 * @return $this
	 */
	public function setDynamicAdsOption(?bool $option = false)
	{
		$this->setParameter('opt_out',$option);
		return $this;
	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#event-name
	 * @param string $type
	 *
	 * @return Event
	 */
	public function setEventType(string $type)
	{
		//if(in_array($type,static::getEventTypeList()))
		{
			$this->setParameter('event_name',$type);
		}
		return $this;
	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#event-source-url
	 * @param string|null $source
	 *
	 * @return $this
	 */
	public function setSource(string $source)
	{
		if(preg_match('%^((https://)|(www\.)|(http://))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i',$source))
		{
			$this->setParameter('event_source_url',$source);
		}
		return $this;
	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#user-data
	 * @param UserData $userData
	 *
	 * @return $this
	 */
	public function setUserData(UserData $userData)
	{
		$this->setParameter('user_data',$userData);
		return $this;
	}

	/**
	 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#custom-data
	 * @param CustomData $data
	 *
	 * @return $this
	 */
	public function setCustomData(CustomData $data)
	{
		$this->setParameter('custom_data',$data);
		return $this;
	}

	/**
	 * @return bool
	 */
	public function validate() : bool
	{
		[$userData, $customData] = [$this->getParameter('user_data'), $this->getParameter('custom_data')];
		if (
			$this->getParameter('event_name')
			&& $userData instanceof UserData
			&& $userData->validate()
			&& $customData instanceof CustomData
			&& $customData->validate()
			&& $this->getParameter('event_time')
			&& $this->getParameter('event_time') + 604800 > time()
		)
		{
			$result = true;
			if ($this->getParameter('event_name') === static::EVENT_PURCHASE)
			{
				$result = $result && is_set($customData->getValue()) && is_set($customData->getCurrency());
			}
			if ($this->getParameter('action_source') === static::ACTION_SOURCE_WEBSITE)
			{
				$result = is_set($this->getParameter('event_source_url'));
			}
			return $result;
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function prepareData() : array
	{
		[$userData, $customData] = [$this->getParameter('user_data'), $this->getParameter('custom_data')];
		return array_filter([
			'action_source' => $this->getParameter('action_source'),
			'custom_data' => $customData->toArray(),
			'user_data' => $userData->toArray(),
			'event_source_url' => $this->getParameter('event_source_url'),
			'event_name' => $this->getParameter('event_name'),
			'opt_out' => $this->getParameter('opt_out'),
			'event_time' => $this->getParameter('event_time')
		]);
	}
}
