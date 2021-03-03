<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Config\Option;
use Bitrix\Seo\BusinessSuite\IInternalService;

class Service implements IService, IMultiClientService, IInternalService
{
	const GROUP = 'retargeting';

	const TYPE_FACEBOOK = 'facebook';
	const TYPE_VKONTAKTE = 'vkontakte';
	const TYPE_MYCOM = 'mycom';
	const TYPE_YANDEX = 'yandex';
	const TYPE_GOOGLE = 'google';

	protected $clientId;

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
	 * @param string $type Engine type.
	 * @return string
	 */
	public static function getEngineCode($type)
	{
		return static::GROUP . '.' . $type;
	}

	/**
	 * @param string $type Engine type.
	 * @return Audience
	 */
	public static function getAudience($type)
	{
		return Audience::create($type)->setService(static::getInstance());
	}

	/**
	 * @param string $type Engine type.
	 * @return Account
	 */
	public static function getAccount($type)
	{
		return Account::create($type)->setService(static::getInstance());
	}

	/**
	 * Can use multiple clients
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function canUseMultipleClients()
	{
		return Option::get('seo', 'use_multiple_clients', true);
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
	 * Get auth adapter.
	 *
	 * @param string $type Type.
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type)
	{
		return AuthAdapter::create($type)->setService(static::getInstance());
	}
	/**
	 * Get client id
	 * @return string
	 */
	public function getClientId()
	{
		return $this->clientId;
	}
	/**
	 * Set client id.
	 * @param string $clientId Client id.
	 * @return void
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypeByEngine(string $engineCode): ?string
	{
		foreach (static::getTypes() as $type)
		{
			if($engineCode === static::getEngineCode($type))
			{
				return $type;
			}
		}
		return null;
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
		return 'retargeting';
	}
}