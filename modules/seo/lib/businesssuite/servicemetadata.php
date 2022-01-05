<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\Retargeting\IService;

class ServiceMetaData
{
	/**@var int|string|null $clientId*/
	protected $clientId;

	/**@var string|null $type*/
	protected $type;

	/**@var string|null $engineCode*/
	protected $engineCode;

	/**@var IInternalService $service*/
	protected $service;

	/**
	 * create instance of ServiceMetaData
	 * @return static
	 */
	public static function create(): ServiceMetaData
	{
		return new static();
	}

	/**
	 * Type getter
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * Engine code getter
	 * @return string|null
	 */
	public function getEngineCode(): ?string
	{
		return $this->engineCode;
	}

	/**
	 * Client id getter
	 * @return int|string|null
	 */
	public function getClientId()
	{
		return $this->clientId;
	}

	/**
	 * Type setter
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type): ServiceMetaData
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Client id setter
	 * @param int $clientId
	 *
	 * @return $this
	 */
	public function setClientId(int $clientId): ServiceMetaData
	{
		$this->clientId = $clientId;

		return $this;
	}

	/**
	 * Engine code setter
	 * @param string $code
	 *
	 * @return $this
	 */
	public function setEngineCode(string $code): ServiceMetaData
	{
		$this->engineCode = $code;

		return $this;
	}

	/**
	 * Service setter
	 * @param IInternalService|null $service
	 *
	 * @return $this
	 */
	public function setService(?IInternalService $service): ServiceMetaData
	{
		$this->service = $service;

		return $this;
	}

	/**
	 * Service getter
	 * @return IInternalService|null
	 */
	public function getService() : ?IInternalService
	{
		return $this->service;
	}
}