<?php

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Seo\BusinessSuite\Utils\QueueRemoveEventHandler;
use Bitrix\Seo\Service;
use Bitrix\Seo\Service as SeoService;

class AuthAdapter
{
	/** @var  IService $service */
	protected $service;

	/**@var string $type*/
	protected $type;

	/* @var \CSocServOAuthTransport|\CFacebookInterface */
	protected $transport;

	protected $requestCodeParamName;
	protected $data;

	/** @var array $parameters Parameters. */
	protected $parameters = ['URL_PARAMETERS' => []];

	public function __construct($type)
	{
		$this->type = $type;

		if($type === \Bitrix\Seo\Retargeting\Service::TYPE_YANDEX)
		{
			$this->parameters['URL_PARAMETERS']['force_confirm'] = 'yes';
		}
	}

	/**
	 * @throws \Bitrix\Main\LoaderException
	 * @throws SystemException
	 */
	public static function create($type, IService $service = null)
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new SystemException('Module "socialservices" not installed.');
		}
		$instance = new static($type);
		if ($service)
		{
			$instance->setService($service);
		}

		return $instance;
	}

	/**
	 * @param IService $service
	 * @return $this
	 */
	public function setService(IService $service)
	{
		$this->service = $service;

		return $this;
	}

	/**
	 * @param array $parameters
	 * @return $this
	 */
	public function setParameters(array $parameters = [])
	{
		$this->parameters = $parameters + $this->parameters;

		return $this;
	}

	public function getAuthUrl()
	{
		if (!SeoService::isRegistered())
		{
			SeoService::register();
		}

		$authorizeData = SeoService::getAuthorizeData(
			$this->getEngineCode(),
			$this->canUseMultipleClients() ? Service::CLIENT_TYPE_MULTIPLE : Service::CLIENT_TYPE_SINGLE
		);

		if (!empty($this->parameters['URL_PARAMETERS']))
		{
			$authorizeData['urlParameters'] = $this->parameters['URL_PARAMETERS'];
		}

		$uri = new Uri(SeoService::getAuthorizeLink());

		return $uri->addParams($authorizeData)->getLocator();
	}

	protected function getAuthData($isUseCache = true)
	{
		return ($this->canUseMultipleClients()
			? $this->getAuthDataMultiple()
			: $this->getAuthDataSingle($isUseCache))
		;
	}

	protected function getAuthDataMultiple()
	{
		return $this->getClientById($this->getClientId());
	}

	protected function getAuthDataSingle($isUseCache = true)
	{
		if (!$isUseCache || !$this->data || count($this->data) == 0)
		{
			$this->data = SeoService::getAuth($this->getEngineCode());
		}

		return $this->data;
	}

	/**
	 * @throws SystemException
	 * @return void
	 */
	public function removeAuth()
	{
		$this->data = array();

		if ($existedAuthData = $this->getAuthData(false))
		{
			QueueRemoveEventHandler::handleEvent(
				$existedAuthData['proxy_client_id'],
				$existedAuthData['engine_code']
			);
			$this->canUseMultipleClients()
				? SeoService::clearAuthForClient($existedAuthData)
				: SeoService::clearAuth($this->getEngineCode());

		}
	}

	/**
	 * @return string
	 */
	protected function getEngineCode()
	{
		if ($this->service)
		{
			return $this->service::getEngineCode($this->type);
		}

		return Service::getEngineCode($this->type);
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	public function getToken()
	{
		return is_array($data = $this->getAuthData(false)) ? $data['access_token'] : null;
	}

	/**
	 * @return bool
	 */
	public function hasAuth()
	{
		return $this->canUseMultipleClients()
			? count($this->getAuthorizedClientsList()) > 0
			: $this->getToken() <> "";
	}

	/**
	 * @return bool
	 */
	public function canUseMultipleClients()
	{
		if (!$this->service)
		{
			return Service::canUseMultipleClients();
		}

		if ($this->service instanceof IMultiClientService)
		{
			return $this->service::canUseMultipleClients();
		}

		return false;
	}

	/**
	 * @return array|null
	 * @throws SystemException
	 */
	public function getClientList()
	{
		return $this->canUseMultipleClients() ? SeoService::getClientList($this->getEngineCode()) : [];
	}

	public function getClientById($clientId)
	{
		$clients = $this->getClientList();
		foreach ($clients as $client)
		{
			if ($client['proxy_client_id'] == $clientId)
			{
				return $client;
			}
		}
		return null;
	}

	/**
	 * @return array|null
	 * @throws SystemException
	 */
	public function getAuthorizedClientsList()
	{
		return array_filter(
			$this->getClientList(),
			static function ($item) : bool {
				return $item['access_token'] <> '';
			}
		);
	}

	public function getClientId()
	{
		if (!$this->canUseMultipleClients())
		{
			return null;
		}
		$clientId = $this->service->getClientId();
		if ($clientId)
		{
			$client = $this->getClientById($clientId);
			if ($client['engine_code'] == $this->getEngineCode())
			{
				return $clientId;
			}
			return null;
		}

		// try to guess account id from accounts list:
		$clients = $this->getClientList();
		foreach ($clients as $client)
		{
			if ($client['proxy_client_type'] == SeoService::CLIENT_TYPE_COMPATIBLE)
			{
				return $client['proxy_client_id'];
			}
		}
		return null;
	}
}
