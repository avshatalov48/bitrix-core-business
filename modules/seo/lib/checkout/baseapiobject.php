<?php

namespace Bitrix\Seo\Checkout;

/**
 * Class BaseApiObject
 * @package Bitrix\Seo\Checkout
 */
class BaseApiObject
{
	const TYPE_CODE = '';

	/** @var Request $request */
	protected $request;

	/** @var Service $service */
	protected $service;

	/**
	 * BaseApiObject constructor.
	 */
	public function __construct()
	{
		$this->request = Request::create(static::TYPE_CODE);
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param Request $request
	 * @return $this
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
		return $this;
	}

	/**
	 * @param $type
	 * @param null $parameters
	 * @param IService|null $service
	 * @return static
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function create($type, $parameters = null, IService $service = null)
	{
		$instance = Factory::create(get_called_class(), $type, $parameters);
		if ($service)
		{
			$instance->setService($service);
		}

		return $instance;
	}

	/**
	 * @param IService $service
	 * @return $this
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setService(IService $service)
	{
		$this->service = $service;
		$this->request->setAuthAdapter($this->service->getAuthAdapter(static::TYPE_CODE));

		return $this;
	}
}