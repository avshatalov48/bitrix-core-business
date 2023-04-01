<?php
namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\ContextInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Office365\Converter\Converter;
use Bitrix\Calendar\Sync\Util\RequestLogger;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Web\HttpClient;
use COffice365OAuthInterface;
use CSocServOffice365OAuth;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Factory for Sync Manager classes
 */
class Office365Context implements ContextInterface
{
	/** @var array */
	private static array $instances = [];

	/** @var EventManager */
	private EventManager $eventManager;
	/** @var Helper */
	private $helper;
	/**@var VendorSyncService */
	private VendorSyncService $syncService;
	/** @var Role */
	private Role $owner;
	/** @var ApiClient */
	private ApiClient $apiClient;
	/** @var ApiService */
	private ApiService $apiService;
	/** @var Connection  */
	private Connection $connection;

	/** @var IncomingSectionManagerInterface */
	private IncomingSectionManagerInterface $incomingManager;
	/** @var OutgoingEventManagerInterface  */
	private OutgoingEventManagerInterface $outgoingEventManager;
	private Converter $converter;
	private PushManager $pushManager;

	/**
	 * @param Connection $connection
	 *
	 * @return Office365Context
	 *
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 */
	public static function getConnectionContext(Connection $connection): Office365Context
	{
		if (!array_key_exists($connection->getId(), self::$instances))
		{
			self::$instances[$connection->getId()] = new self($connection);
		}
		return self::$instances[$connection->getId()];
	}

	/**
	 * @param Connection $connection
	 *
	 * @throws ObjectNotFoundException
	 * @throws NotFoundExceptionInterface
	 */
	protected function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->owner = $connection->getOwner();
		$this->helper = ServiceLocator::getInstance()->get('calendar.service.office365.helper');
	}

	/**
	 * @return EventManager
	 */
	public function getEventManager(): EventManager
	{
		if (empty($this->eventManager))
		{
			$this->eventManager = new EventManager($this);
		}

		return $this->eventManager;
	}

	/**
	 * @return VendorSyncService
	 *
	 * @throws AuthException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws RemoteAccountException
	 */
	public function getVendorSyncService(): VendorSyncService
	{
		if (empty($this->syncService))
		{
			$this->syncService = new VendorSyncService($this);
		}

		return $this->syncService;
	}

	/**
	 * @return ApiService
	 *
	 * @throws AuthException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws RemoteAccountException
	 */
	public function getApiService(): ApiService
	{
		if(empty($this->apiService))
		{
			$this->apiService = new ApiService($this);
		}

		return $this->apiService;
	}

	/**
	 * @return ApiClient
	 *
	 * @throws BaseException
	 * @throws RemoteAccountException
	 * @throws AuthException
	 * @throws LoaderException
	 *
	 */
	public function getApiClient(): ApiClient
	{
		if(empty($this->apiClient))
		{
			$httpClient = $this->prepareHttpClient();

			$this->apiClient = new ApiClient($httpClient, $this);
		}

		return $this->apiClient;
	}


	protected function getMaxPageSize(): ?int
	{
		return 100;
	}

	/**
	 * @return Helper
	 */
	public function getHelper(): Helper
	{
		return $this->helper;
	}

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * @return PushManager
	 */
	public function getPushManager(): PushManager
	{
		if (empty($this->pushManager))
		{
			$this->pushManager = new PushManager($this);
		}
		return $this->pushManager;
	}

	/**
	 * @return Converter
	 */
	public function getConverter(): Converter
	{
		if (empty($this->converter))
		{
			$this->converter = new Converter($this);
		}
		return $this->converter;
	}

	/**
	 * @return HttpClient
	 *
	 * @throws RemoteAccountException
	 * @throws AuthException
	 * @throws LoaderException
	 * @throws BaseException
	 */
	private function prepareHttpClient(): HttpClient
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new LoaderException('Module socialservices is required.');
		}
		$httpClient = new HttpClient();

		$oAuthEntity = $this->prepareAuthEntity($this->owner->getId());
		if ($oAuthEntity->GetAccessToken())
		{
			$httpClient->setHeader('Authorization', 'Bearer ' . $oAuthEntity->getToken());
			$httpClient->setHeader('Content-Type', 'application/json');
			$httpClient->setHeader('Prefer', 'odata.maxpagesize=' . $this->getMaxPageSize());
			$httpClient->setRedirect(false);
		}
		elseif ($checkUser = $oAuthEntity->GetCurrentUser())
		{
			if (!empty($checkUser['access_token']))
			{
				$httpClient->setHeader('Authorization', 'Bearer ' . $checkUser['access_token']);
				$httpClient->setHeader('Content-Type', 'application/json');
				$httpClient->setHeader('Prefer', 'odata.maxpagesize=' . $this->getMaxPageSize());
				$httpClient->setRedirect(false);
			}
			else
			{
				throw new AuthException('Access token not recived', 401);
			}
		}
		else
		{
			// TODO: maybe move it to the exception handler.
			// Now it's impossible, because there are many points of call this class
			(new \Bitrix\Calendar\Core\Mappers\Connection())->update(
				$this->getConnection()->setDeleted(true)
			);
			throw new RemoteAccountException('Office365 account not found', 403);
		}
		return $httpClient;
	}

	/**
	 * @param $userId
	 *
	 * @return COffice365OAuthInterface
	 */
	public function prepareAuthEntity($userId): COffice365OAuthInterface
	{
		$oauth = new CSocServOffice365OAuth($userId);
		$oAuthEntity = $oauth->getEntityOAuth();
		$oAuthEntity->addScope($this->helper::NEED_SCOPE);
		$oAuthEntity->setUser($this->owner->getId());
		if (!$oAuthEntity->checkAccessToken())
		{
			$oAuthEntity->getNewAccessToken(
				$oAuthEntity->getRefreshToken(),
				$this->owner->getId(),
				true
			);
		}

		return $oAuthEntity;
	}

	public function getIncomingManager()
	{
		if (empty($this->incomingManager))
		{
			$this->incomingManager = new IncomingManager($this);
		}

		return $this->incomingManager;
	}

	public function getOutgoingEventManager(): OutgoingEventManagerInterface
	{
		if (empty($this->outgoingEventManager))
		{
			$this->outgoingEventManager = new OutgoingEventManager($this);
		}
		return $this->outgoingEventManager;
	}

	/**
	 * @return Role
	 */
	public function getOwner(): Role
	{
		return $this->owner;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		if (RequestLogger::isEnabled())
		{
			$logger = new RequestLogger($this->getOwner()->getId(), Helper::ACCOUNT_TYPE);
		}
		else
		{
			$logger = new NullLogger();
		}

		return $logger;
	}
}
