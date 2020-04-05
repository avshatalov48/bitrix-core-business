<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting\AuthAdapter;
use Bitrix\Seo\Retargeting\IService;

/**
 * Class Service
 *
 * @package Bitrix\Seo\LeadAds
 */
class Service implements IService
{
	const GROUP = 'leadads';
	const TYPE_FACEBOOK = 'facebook';
	const TYPE_VKONTAKTE = 'vkontakte';

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		static $instance = null;
		if ($instance === null)
		{
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Get engine code by type.
	 *
	 * @param string $type Type
	 * @return string
	 */
	public static function getEngineCode($type)
	{
		return static::GROUP . '.' . $type;
	}

	/**
	 * Get Form by type.
	 *
	 * @param string $type Type
	 * @return Form
	 */
	public static function getForm($type)
	{
		static $form = null;
		if ($form === null)
		{
			$form = Form::create($type)->setService(static::getInstance());
		}

		return $form;
	}

	/**
	 * Get group auth.
	 *
	 * @param string $type Type
	 * @return AuthAdapter
	 */
	public static function getGroupAuth($type)
	{
		static $auth = null;
		if ($auth === null)
		{
			$auth = Form::create($type)->setService(static::getInstance())->getGroupAuthAdapter();
		}

		return $auth;
	}

	/**
	 * Register group.
	 *
	 * @param string $type Type.
	 * @param string $groupId Group ID.
	 * @return bool
	 */
	public static function registerGroup($type, $groupId)
	{
		return Form::create($type)
			->setService(static::getInstance())
			->registerGroup($groupId);
	}

	/**
	 * UnRegister group.
	 *
	 * @param string $type Type.
	 * @param string $groupId Group ID.
	 * @return bool
	 */
	public static function unRegisterGroup($type, $groupId)
	{
		return Form::create($type)
			->setService(static::getInstance())
			->unRegisterGroup($groupId);
	}

	/**
	 * Remove group auth.
	 *
	 * @param string $type Type
	 * @return void
	 */
	public static function removeGroupAuth($type)
	{
		static::getGroupAuth($type)->removeAuth();
	}

	/**
	 * Get Account by type.
	 *
	 * @param string $type Type
	 * @return Account
	 */
	public static function getAccount($type)
	{
		static $account = null;
		if ($account === null)
		{
			$account = Account::create($type)->setService(static::getInstance());
		}

		return $account;
	}

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypes()
	{
		return array(
			static::TYPE_FACEBOOK,
			static::TYPE_VKONTAKTE,
		);
	}

	/**
	 * Get auth adapter.
	 *
	 * @param string $type Type
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type)
	{
		static $adapter = null;
		if ($adapter === null)
		{
			$adapter = AuthAdapter::create($type)->setService(static::getInstance());
		}

		return $adapter;
	}
}