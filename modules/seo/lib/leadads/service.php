<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting\AuthAdapter;
use Bitrix\Seo\Retargeting\IService;

class Service implements IService
{
	const GROUP = 'leadads';
	const TYPE_FACEBOOK = 'facebook';

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