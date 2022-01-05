<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\Mail;

class Context
{
	const CAT_EXTERNAL = 1;
	const PRIORITY_HIGH = 1;
	const PRIORITY_NORMAL = 2;
	const PRIORITY_LOW = 3;

	protected $category;
	protected $priority;

	/** @var  Smtp\Config|null $smtp */
	protected $smtp;

	/** @var  Callback\Config $callback */
	protected $callback;

	protected $keepAlive;

	public function __construct(array $params = null)
	{
		if (!empty($params) && is_array($params))
		{
			foreach ($params as $name => $value)
			{
				$setter = sprintf('set%s', $name);
				if (is_callable(array($this, $setter)))
					$this->$setter($value);
			}
		}
	}

	/**
	 * @param int $category See Context CAT_* constants.
	 * @return $this
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param Smtp\Config $config Smtp config.
	 * @return $this
	 */
	public function setSmtp(Smtp\Config $config)
	{
		$this->smtp = $config;
		return $this;
	}

	/**
	 * @return Smtp\Config|null
	 */
	public function getSmtp()
	{
		return $this->smtp;
	}

	/**
	 * @param int $priority See Context PRIORITY_* constants.
	 * @return $this
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * Get callback config instance.
	 *
	 * @param Callback\Config $config Callback config instance.
	 * @return $this
	 */
	public function setCallback(Callback\Config $config)
	{
		$this->callback = $config;
		return $this;
	}

	/**
	 * Get callback config instance.
	 *
	 * @return Callback\Config|null
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * @return string | null
	 */
	public function getKeepAlive(): ?string
	{
		return $this->keepAlive;
	}

	/**
	 * @param string | null $keepAlive
	 */
	public function setKeepAlive(?string $keepAlive)
	{
		$this->keepAlive = $keepAlive;
	}
}
