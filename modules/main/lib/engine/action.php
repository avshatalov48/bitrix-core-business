<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;
use Bitrix\Main\SystemException;

/**
 * Class Action
 * @package Bitrix\Main\Engine
 * @method run
 */
class Action implements Errorable
{
	/** @var Binder */
	protected $binder;
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var Controller */
	protected $controller;
	/** @var array */
	protected $config;
	/** @var string */
	protected $name;

	public function __construct($name, Controller $controller, $config = array())
	{
		$this->errorCollection = new ErrorCollection;
		$this->controller = $controller;
		$this->config = $config;
		$this->name = $name;

		if (isset($config['configure']))
		{
			$this->configure($config['configure']);
		}

		$this->init();
	}

	/**
	 * Configures action by additional params.
	 * The method will be invoked by controller and $params have to set in 'configureActions'
	 * @param $params
	 * @return void
	 */
	public function configure($params)
	{}

	protected function init()
	{}

	/**
	 * Returns list of action arguments.
	 * It is associative array looks like argument name => value.
	 * @return array
	 * @throws SystemException
	 */
	final public function getArguments()
	{
		$binder = $this->buildBinder()->getBinder();

		return $binder->getMethodParams();
	}

	/**
	 * Sets list of action arguments.
	 * It is associative array looks like argument name => value.
	 * Be aware the method reset old values and set new arguments.
	 *
	 * @param array $arguments List of action arguments.
	 *
	 * @return Binder
	 * @throws SystemException
	 */
	final public function setArguments(array $arguments)
	{
		$binder = $this->buildBinder()->getBinder();

		return $binder->setMethodParams($arguments);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			if (!method_exists($this, 'run'))
			{
				throw new SystemException(static::className() . ' must implement run()');
			}

			$this->binder = new Binder($this, 'run', $this->controller->getSourceParametersList());
		}

		return $this;
	}

	public function runWithSourceParametersList()
	{
		$binder = $this->buildBinder()->getBinder();
		if ($this->onBeforeRun())
		{
			/** @see Action::run */
			$result = $binder->invoke();
				
			$this->onAfterRun();
			
			return $result;
		}

		return null;
	}

	/**
	 * @return Binder
	 */
	final public function getBinder()
	{
		return $this->binder;
	}

	/**
	 * @return Controller
	 */
	final public function getController()
	{
		return $this->controller;
	}

	/**
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	final public function getConfig()
	{
		return $this->config;
	}

	protected function onBeforeRun()
	{
		return true;
	}

	protected function onAfterRun()
	{
	}

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	final public static function className()
	{
		return get_called_class();
	}

	/**
	 * Adds error to error collection.
	 * @param Error $error Error.
	 *
	 * @return $this
	 */
	protected function addError(Error $error)
	{
		$this->errorCollection[] = $error;

		return $this;
	}

	/**
	 * Adds list of errors to error collection.
	 * @param Error[] $errors Errors.
	 *
	 * @return $this
	 */
	protected function addErrors(array $errors)
	{
		$this->errorCollection->add($errors);

		return $this;
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}