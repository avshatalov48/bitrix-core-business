<?

namespace Bitrix\Seo\Retargeting;

class Service implements IService
{
	const GROUP = 'retargeting';

	const TYPE_FACEBOOK = 'facebook';
	const TYPE_VKONTAKTE = 'vkontakte';
	const TYPE_MYCOM = 'mycom';
	const TYPE_YANDEX = 'yandex';
	const TYPE_GOOGLE = 'google';

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
	 * @param string $type
	 * @return string
	 */
	public static function getEngineCode($type)
	{
		return static::GROUP . '.' . $type;
	}

	/**
	 * @param string $type
	 * @return Audience
	 */
	public static function getAudience($type)
	{
		return Audience::create($type)->setService(static::getInstance());
	}

	/**
	 * @param string $type
	 * @return Account
	 */
	public static function getAccount($type)
	{
		return Account::create($type)->setService(static::getInstance());
	}

	/**
	 * @return array
	 */
	public static function getTypes()
	{
		return array(
			static::TYPE_FACEBOOK,
			static::TYPE_VKONTAKTE,
			static::TYPE_GOOGLE,
			static::TYPE_YANDEX
		);
	}

	/**
	 * @param string $type
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type)
	{
		return AuthAdapter::create($type)->setService(static::getInstance());
	}
}