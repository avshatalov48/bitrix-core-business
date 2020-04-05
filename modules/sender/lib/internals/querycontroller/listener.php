<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

class Listener extends Base
{
	const REQUEST_METHOD_POST = 'POST';
	const REQUEST_METHOD_GET = 'GET';

	/** @var ErrorCollection $errors Errors. */
	protected $errors;

	/** @var Action $action Action. */
	protected $action;

	/** @var string $actionName Action name. */
	protected $actionName;

	/** @var Action[] $actions */
	protected $actions = array();

	/** @var HttpRequest $request */
	protected $request;

	/** @var Response $response */
	protected $response;

	/**
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		$this->errors = new ErrorCollection;
	}

	/**
	 * Set actions.
	 * @param Action[] $actions Actions.
	 * @return $this
	 */
	public function setActions(array $actions)
	{
		foreach ($actions as $action)
		{
			$this->addAction($action);
		}

		return $this;
	}

	/**
	 * Add action.
	 * @param Action $action Action.
	 * @return $this
	 */
	public function addAction(Action $action)
	{
		$this->actions[] = $action;
		return $this;
	}

	protected function giveResponse()
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		$this->response->getContent()->getErrorCollection()->add($this->errors->toArray());
		$this->response->flushContent();

		\CMain::finalActions();
		exit;
	}

	/**
	 * Get error collection.
	 *
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errors;
	}

	protected function check()
	{
		if(!$this->action)
		{
			$messageText = 'Action';
			if ($this->actionName)
			{
				$messageText .= ' "' . htmlspecialcharsbx($this->actionName) . '"';
			}
			$messageText .= ' not found.';

			$this->errors->setError(new Error($messageText));
			return;
		}

		if(!check_bitrix_sessid() || ($this->action->getRequestMethod() != $this->request->getRequestMethod()))
		{
			$this->errors->setError(new Error('Security error.'));
			return;
		}
	}

	protected function findAction()
	{
		$this->action = null;
		foreach ($this->actions as $action)
		{
			if ($action->getName() != $this->actionName)
			{
				continue;
			}

			$this->action = $action;
			break;
		}

		return $this->action;
	}

	protected function process()
	{
		// check
		$this->check();
		if (!$this->errors->isEmpty())
		{
			return;
		}

		// checkers
		$checkResult = new Result();
		$checkers = array_merge($this->getCheckers(), $this->action->getCheckers());
		static::callList($checkers, array($checkResult, $this->request));
		$this->errors->add($checkResult->getErrors());
		if (!$this->errors->isEmpty())
		{
			return;
		}

		// run action
		$this->action->run($this->request, $this->response);

		// modify response
		$modifiers = array_merge($this->getResponseModifiers(), $this->action->getResponseModifiers());
		static::callList($modifiers, array($this->response, $this->request));
	}

	/**
	 * Run.
	 */
	public function run()
	{
		$this->request = $this->request ?: Context::getCurrent()->getRequest();
		$this->response = $this->response ?: new Response(Context::getCurrent());
		$this->actionName = $this->request->get('action');

		// find action by name
		$this->findAction();

		// process action
		$this->process();

		// give response
		$this->giveResponse();
	}



	/**
	 * Call list.
	 *
	 * @param callable[] $list Callee list.
	 * @param array $parameters Parameters.
	 * @return void
	 */
	protected function callList(array $list, array $parameters = array())
	{
		foreach ($list as $callee)
		{
			$result = static::call($callee, $parameters);
			if ($result instanceof Error)
			{
				$this->errors->setError($result);
				return;
			}
		}
	}
}