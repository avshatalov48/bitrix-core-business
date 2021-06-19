<?

namespace Bitrix\Seo\Marketing;

use Bitrix\Main\Config\Option;
use Bitrix\Seo\BusinessSuite\IInternalService;
use Bitrix\Seo\Retargeting\AuthAdapter;
use Bitrix\Seo\Retargeting\IMultiClientService;
use Bitrix\Seo\Retargeting\IService;

/**
 * Class Service
 *
 * @package Bitrix\Seo\Marketing
 */
class Service implements IService, IMultiClientService, IInternalService
{
	const GROUP         = 'marketing';
	const TYPE_FACEBOOK = 'facebook';
	const TYPE_INSTAGRAM = 'instagram';
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
	 * Get engine code by type.
	 *
	 * @param string $type Type
	 *
	 * @return string
	 */
	public static function getEngineCode($type)
	{
		return static::GROUP.'.'.$type;
	}

	/**
	 * Get Account by type.
	 *
	 * @param string $type Type
	 *
	 * @return Account
	 */
	public static function getAccount($type)
	{
		static $account = null;
		if ($account === null)
		{
			$account = Account::create($type)
				->setService(static::getInstance());
		}

		return $account;
	}

	/**
	 * @param $type
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function createCampaign($type, array $data)
	{
		return AdCampaign::create($type)
			->setService(static::getInstance())
			->createCampaign($data);
	}

	/**
	 * @param $type
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function createAudience($type, array $data)
	{
		return Audience::create($type)
			->setService(static::getInstance())
			->add($data);
	}

	/**
	 * @param $type
	 * @param $accountId
	 *
	 * @return array|null
	 */
	public static function getPostList($type, $params)
	{
		return PostList::create($type)
			->setService(static::getInstance())
			->getList($params);
	}

	/**
	 * @param $type
	 *
	 * @return Audience|null
	 */
	public static function getAudience($type)
	{
		return Audience::create($type)
			->setService(static::getInstance());
	}

	/**
	 * @param $type
	 * @param $accountId
	 *
	 * @return array|null
	 */
	public static function getAudienceList($type, $accountId)
	{
		return Audience::create($type)
			->setService(static::getInstance())
			->getList($accountId);
	}

	/**
	 * @param $type
	 * @param $accountId
	 *
	 * @return array|null
	 */
	public static function getAdSetList($type, $accountId)
	{
		return AdCampaign::create($type)
			->setService(static::getInstance())
			->getAdSetList($accountId);
	}

	/**
	 * @param $type
	 * @param $adsId
	 *
	 * @return mixed
	 */
	public static function getAds($type, $adsId)
	{
		return AdCampaign::create($type)
			->setService(static::getInstance())
			->getAds($adsId);
	}

	/**
	 * @param $type
	 * @param $adsId
	 *
	 * @return mixed
	 */
	public static function searchTargetingData($type, $params)
	{
		return AdCampaign::create($type)
			->setService(static::getInstance())
			->searchTargetingData($params);
	}

	/**
	 * @param $type
	 * @param $accountId
	 *
	 * @return array|null
	 */
	public static function getCampaignList($type, $accountId)
	{
		return AdCampaign::create($type)
			->setService(static::getInstance())
			->getCampaignList($accountId);
	}

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypes()
	{
		return [
			static::TYPE_FACEBOOK,
			static::TYPE_INSTAGRAM,
		];
	}

	/**
	 * Get auth adapter.
	 *
	 * @param string $type Type.
	 *
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type)
	{
		return AuthAdapter::create($type)
			->setService(static::getInstance());
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
	 *
	 * @param string $clientId Client id.
	 *
	 * @return void
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
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
		return 'marketing';
	}
}