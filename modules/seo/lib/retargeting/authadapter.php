<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\SystemException;
use Bitrix\Seo\Service as SeoService;

class AuthAdapter
{
	/** @var  IService $service */
	protected $service;
	protected $type;
	/* @var \CSocServOAuthTransport|\CFacebookInterface */
	protected $transport;
	protected $requestCodeParamName;
	protected $data;

	public function __construct($type)
	{
		$this->type = $type;
	}

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

	public function setService(IService $service)
	{
		$this->service = $service;
		return $this;
	}

	public function getAuthUrl()
	{
		if (!SeoService::isRegistered())
		{
			SeoService::register();
		}

		$authorizeUrl = SeoService::getAuthorizeLink();
		$authorizeData = SeoService::getAuthorizeData($this->getEngineCode());
		$uri = new Uri($authorizeUrl);
		$uri->addParams($authorizeData);
		return $uri->getLocator();
	}

	protected function getAuthData($isUseCache = true)
	{
		if (!$isUseCache || !$this->data || count($this->data) == 0)
		{
			$this->data = SeoService::getAuth($this->getEngineCode());
		}

		return $this->data;
	}

	public function removeAuth()
	{
		$this->data = array();

		if ($existedAuthData = $this->getAuthData(false))
		{
			SeoService::clearAuth($this->getEngineCode());
		}
	}

	protected function getEngineCode()
	{
		if ($this->service)
		{
			return $this->service->getEngineCode($this->type);
		}
		else
		{
			return Service::getEngineCode($this->type);
		}
	}

	public function getType()
	{
		return $this->type;
	}

	public function getToken()
	{
		$data = $this->getAuthData();
		return $data ? $data['access_token'] : null;
	}

	public function hasAuth()
	{
		return strlen($this->getToken()) > 0;
	}
}