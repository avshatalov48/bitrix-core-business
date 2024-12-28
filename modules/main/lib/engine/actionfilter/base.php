<?php


namespace Bitrix\Main\Engine\ActionFilter;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;
use Bitrix\Main\Event;

abstract class Base implements Errorable
{
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var Action */
	protected $action;

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	final public static function className()
	{
		return get_called_class();
	}

	/**
	 * Constructor Controller.
	 */
	public function __construct()
	{
		$this->errorCollection = new ErrorCollection;
	}

	final public function bindAction(Action $action)
	{
		$this->action = $action;

		return $this;
	}

	/**
	 * @return Action
	 */
	final public function getAction()
	{
		return $this->action;
	}

	/**
	 * List allowed values of scopes where the filter should work.
	 * @return array
	 */
	public function listAllowedScopes()
	{
		return array(
			Controller::SCOPE_REST,
			Controller::SCOPE_AJAX,
			Controller::SCOPE_CLI,
		);
	}

	public function onBeforeAction(Event $event)
	{
	}

	public function onAfterAction(Event $event)
	{
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
	protected function addErrors(array $errors): static
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