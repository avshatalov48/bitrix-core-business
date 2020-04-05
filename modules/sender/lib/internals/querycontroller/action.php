<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

class Action extends Base
{
	/** @var string $name Name. */
	protected $name;

	/** @var string $contentType Content type. */
	protected $contentType;

	/** @var callable $handler Handler. */
	protected $handler;

	/** @var string $postMethod Post method. */
	protected $requestMethod = Listener::REQUEST_METHOD_POST;

	/**
	 * Create instance.
	 * @param string $name Name.
	 * @return static
	 */
	public static function create($name)
	{
		return new static($name);
	}

	/**
	 * Action constructor.
	 * @param string $name Name.
	 * @param callable|null $handler Handler.
	 */
	public function __construct($name, $handler = null)
	{
		$this->name = $name;
		if ($handler)
		{
			$this->setHandler($handler);
		}
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set handler.
	 *
	 * @param callable $handler Handler.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setHandler($handler)
	{
		if (!is_callable($handler))
		{
			throw new ArgumentException("Argument `handler` should be callabe.");
		}

		$this->handler = $handler;
		return $this;
	}

	/**
	 * Get request method.
	 *
	 * @return string
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	/**
	 * Set request method GET.
	 *
	 * @return $this
	 */
	public function setRequestMethodGet()
	{
		$this->requestMethod = Listener::REQUEST_METHOD_GET;
		return $this;
	}

	/**
	 * Run.
	 *
	 * @param HttpRequest $request
	 * @param Response $response
	 * @throws \Bitrix\Main\Error;
	 */
	public function run(HttpRequest $request, Response $response)
	{
		if (!$this->handler)
		{
			return;
		}

		static::call($this->handler, array($request, $response));
	}
}