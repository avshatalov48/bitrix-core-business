<?php
namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook;


use Bitrix\Main\Application;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\BusinessSuite\ServiceAdapter;
use Bitrix\Seo\BusinessSuite\Exception;
use Bitrix\Seo\BusinessSuite\Configuration\IConfig;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config as ConfigField;


final class Config implements IConfig
{
	// public constants
	const IG_CTA = 'ig_cta';
	const MESSENGER_CHAT = 'messenger_chat';
	const MESSENGER_MENU = 'messenger_menu';
	const BUSINESS_NAME = 'business';
	const PAGE_CARD = 'page_card';
	const PAGE_CTA = 'page_cta';
	const PAGE_POST = 'page_post';
	const THREAD_INTENT = 'thread_intent';

	// Non public constants
	private const FIELDS_MAP = [
		self::PAGE_CTA => ConfigField\PageCta::class,
		self::BUSINESS_NAME => ConfigField\Name::class,
		self::PAGE_CARD => ConfigField\PageCard::class,
		self::PAGE_POST => ConfigField\PagePost::class,
		self::IG_CTA => ConfigField\InstagramCta::class,
		self::THREAD_INTENT => ConfigField\ThreadIntent::class,
		self::MESSENGER_MENU => ConfigField\MessengerMenu::class,
		self::MESSENGER_CHAT => ConfigField\MessengerChat::class
	];

	private const FIELDS_DEPENDENCIES = [
		self::MESSENGER_MENU => [self::MESSENGER_CHAT],
		self::THREAD_INTENT => [self::MESSENGER_CHAT],
	];

	public const FACEBOOK_BUSINESS_CONFIG_TLL = 86400;
	private const FACEBOOK_BUSINESS_CONFIG_CACHE_ID = 'facebook|business|config';

	/**@var self $current*/
	private static $current;

	/**@var array $value*/
	private $value = [];

	/**
	 *
	 * @param string $name
	 *
	 * @return ConfigField\IConfigField
	 * @throws Exception\UnknownFieldException
	 */
	private static function getField(string $name) : ConfigField\IConfigField
	{
		if($field = self::FIELDS_MAP[$name])
		{
			return new $field;
		}
		throw new Exception\UnknownFieldException("$name");
	}
	private static function getFieldCodes() : array
	{
		static $codes;
		return $codes = $codes ?? array_keys(self::FIELDS_MAP);
	}

	public static function create(): IConfig
	{
		return new self();
	}

	/**
	 * @return array
	 * @throws Exception\UnresolvedDependencyException
	 */
	public function toArray(): array
	{
		$result = array_reduce(
			static::getFieldCodes(),
			(function (array $result, string $code) : array
			{
				$field = $this::getField($code);
				if($field::available())
				{
					if ($value = $this->get($code))
					{
						$result[$code] = $value;
					}
					elseif ($field::required())
					{
						throw new Exception\RequiredFieldNotFoundException("$code");
					}
				}
				return $result;

			})->bindTo($this,$this),
			array()
		);
		$keys = array_keys($result);
		foreach ($this::FIELDS_DEPENDENCIES as $code => $dependencies)
		{
			if($result[$code] && count($undefined = array_diff($dependencies,$keys)) > 0)
			{
				throw new Exception\UnresolvedDependencyException("$code:".implode(',',$undefined));
			}
		}
		return $result;
	}

	public function save() : bool
	{
		try
		{
			if($adapter = ServiceAdapter::loadFacebookService())
			{
				return $adapter->getConfig()->set($this)->isSuccess();
			}
			return false;
		}
		catch (\Throwable $exception)
		{
			return false;
		}
	}

	public function set(string $name, $value) : IConfig
	{
		$field = static::getField($name);
		if($field::available())
		{
			if($field::checkValue($value = $field::prepareValue($value)))
			{
				$this->value[$name] = $value;
				return $this;
			}
			throw new Exception\InvalidFieldValue("$name");
		}
		return $this;
	}

	public function get(string $name)
	{
		return $this->value[$name] ?? null;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return array_reduce(
			static::getFieldCodes(),
			function(array $result, string $code) : array
			{
				$field = self::getField($code);
				$result[$code] = ['value' => $this->value[$code] ?? $field::getDefaultValue()];
				if($field instanceof Fields\IAvailableFieldList)
				{
					$result[$code]['set'] = $field::getAvailableValues();
				}
				return $result;

			},
			array()
		);
	}

	/**
	 * @return IConfig
	 * @throws Exception\ConfigLoadException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function load(): IConfig
	{
		if(!self::$current)
		{
			$cache = Application::getInstance()->getManagedCache();
			if ($cache->read(self::FACEBOOK_BUSINESS_CONFIG_TLL,self::FACEBOOK_BUSINESS_CONFIG_CACHE_ID))
			{
				self::$current = self::create();
				self::$current->value = $cache->get(self::FACEBOOK_BUSINESS_CONFIG_CACHE_ID);
			}
			elseif (
				($adapter = ServiceAdapter::loadFacebookService())
				&& ($response = $adapter->getConfig()->get())
				&& $response->isSuccess()
				&& $data = $response->fetch()
			)
			{
				self::$current = array_reduce(
					static::getFieldCodes(),
					function(IConfig $instance, string $code) use ($data)
					{
						if($value = $data[mb_strtoupper($code)])
						{
							$instance->set($code,$value);
						}
						return $instance;
					},
					self::create()
				);
				$cache->set(self::FACEBOOK_BUSINESS_CONFIG_CACHE_ID,self::$current->value);
			}
			else
			{
				throw new Exception\ConfigLoadException("can't load current fbe configuration");
			}
		}
		return self::$current;
	}

	/**
	 * @return IConfig
	 * @throws Exception\UnknownFieldException
	 */
	public static function default(): IConfig
	{
		$instance = self::create();
		foreach (static::getFieldCodes()as $code)
		{
			$instance = $instance->set($code,self::getField($code)::getDefaultValue());
		}
		return $instance;

	}

	/**
	 * @param array $array
	 *
	 * @return IConfig
	 */
	public static function loadFromArray(array $array) : IConfig
	{
		return array_reduce(
			static::getFieldCodes(),
			function(IConfig $instance, string $code) use ($array)
			{
				return (array_key_exists($code,$array)? $instance->set($code,$array[$code]) : $instance);
			},
			self::create()
		);
	}

	public static function clearCache()
	{
		Application::getInstance()->getManagedCache()->clean(self::FACEBOOK_BUSINESS_CONFIG_CACHE_ID);
		self::$current = null;
	}
}