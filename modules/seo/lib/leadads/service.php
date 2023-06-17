<?php

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\BusinessSuite\IInternalService;
use Bitrix\Seo\Retargeting\AuthAdapter;
use Bitrix\Seo\Retargeting\IService;

/**
 * Class Service
 *
 * @package Bitrix\Seo\LeadAds
 */
class Service implements IService, IInternalService
{
	public const GROUP = 'leadads';

	public const TYPE_FACEBOOK = 'facebook';

	public const TYPE_VKONTAKTE = 'vkontakte';

	/**@var array<string,Account> $accounts */
	protected $accounts = [];

	/**@var array<string,Form> $accounts */
	protected $forms = [];

	/**
	 * Register group.
	 *
	 * @param string $type Type.
	 * @param string $groupId Group ID.
	 *
	 * @return bool
	 */
	public static function registerGroup(string $type, string $groupId): bool
	{
		return Form::create($type)
			->setService(static::getInstance())
			->registerGroup($groupId);
	}

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function getInstance(): Service
	{
		static $instance;

		return $instance = $instance ?? new static();
	}

	/**
	 * UnRegister group.
	 *
	 * @param string $type Type.
	 * @param string $groupId Group ID.
	 *
	 * @return bool
	 */
	public static function unRegisterGroup(string $type, string $groupId): bool
	{
		$result = Form::create($type)
			->setService(static::getInstance())
			->unRegisterGroup($groupId);

		static::getInstance()->getGroupAuth($type)->removeAuth();

		return $result;
	}

	/**
	 * Get auth adapter.
	 *
	 * @param string $type Type
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type): AuthAdapter
	{
		/**@var array<string,AuthAdapter> */
		static $adapters;

		$adapters = $adapters ?? [];
		if (!array_key_exists($type, $adapters))
		{
			$adapters[$type] = AuthAdapter::create($type)->setService(static::getInstance());
		}

		return $adapters[$type];
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypeByEngine(string $engineCode): ?string
	{
		foreach (static::getTypes() as $type)
		{
			if ($engineCode === static::getEngineCode($type))
			{
				return $type;
			}
		}

		return null;
	}

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [
			static::TYPE_FACEBOOK,
			static::TYPE_VKONTAKTE,
		];
	}

	/**
	 * Get engine code by type.
	 *
	 * @param string $type Type
	 * @return string
	 */
	public static function getEngineCode($type): string
	{
		return static::GROUP . '.' . $type;
	}

	/**
	 * @inheritDoc
	 */
	public static function canUseAsInternal(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		return 'leadads';
	}

	/**
	 * Get group auth object.
	 *
	 * @param string $type Type
	 *
	 * @return AuthAdapter
	 */
	public function getGroupAuth(string $type): ?AuthAdapter
	{
		return $this->getForm($type)->getGroupAuthAdapter();
	}

	/**
	 * Get Form by type.
	 *
	 * @param string $type Type
	 *
	 * @return Form
	 */
	public function getForm(string $type): Form
	{
		if (!array_key_exists($type, $this->forms))
		{
			$this->forms[$type] = Form::create($type)->setService($this);
		}

		return $this->forms[$type];
	}

	/**
	 * Get Account by type.
	 *
	 * @param string $type Type
	 *
	 * @return Account
	 */
	public function getAccount(string $type): ?Account
	{
		if (!array_key_exists($type, $this->accounts))
		{
			$this->accounts[$type] = Account::create($type)->setService($this);
		}

		return $this->accounts[$type];
	}

	public function getAuthUrl($type): string
	{
		$authManager = static::getAuthAdapter($type);
		return $authManager->getAuthUrl();
	}
}

