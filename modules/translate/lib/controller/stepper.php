<?php
namespace Bitrix\Translate\Controller;

use Bitrix\Main;
use Bitrix\Translate;

/**
 * Stepper
 *
 * @implements Translate\Controller\ITimeLimit
 */
trait Stepper
{
	/** @var string */
	protected $processToken;

	/** @var boolean */
	protected $isNewProcess = true;

	/** @var boolean */
	protected $isProcessCompleted = false;

	/** @var int */
	protected $processedItems = 0;

	/** @var int */
	protected $totalItems = 0;

	/** @var Translate\Controller\Timer */
	protected $timer;


	/**
	 * Initializes controller stepper and checks necessary parameters.
	 * @see \Bitrix\Main\Engine\Action::onBeforeRun
	 *
	 * @return bool
	 */
	protected function onBeforeRun()
	{
		if ($this instanceof IProcessParameters)
		{
			$this->keepField(array(
				'processedItems',
				'totalItems',
			));

			/** @var Main\Engine\Action $this */
			$this->processToken = $this->getController()->getRequest()->get('PROCESS_TOKEN');

			$progressData = $this->getProgressParameters();
			if (count($progressData) > 0)
			{
				$this->isNewProcess = (empty($progressData['processToken']) || $progressData['processToken'] !== $this->processToken);
				if (!$this->isNewProcess)
				{
					// restore state
					$this->restoreProgressParameters();
				}
			}

			$this->keepField('processToken');
		}

		if($this->processToken === '')
		{
			$this->addError(new Main\Error('Process token is not specified.'));
		}

		return count($this->getErrors()) === 0;
	}

	/**
	 * Performs action.
	 *
	 * @param \Closure|callable|string $action Action to be executed.
	 * @param array $params Parameters.
	 *
	 * @return array|Main\Engine\Response\AjaxJson
	 */
	protected function performStep($action, array $params = array())
	{
		if ($this->isNewProcess)
		{
			$this->processedItems = 0;
			$this->totalItems = 0;

			if ($this instanceof IProcessParameters)
			{
				$this->saveProgressParameters();
			}
		}

		$this->startTimer();

		if ($action instanceof \Closure)
		{
			$result = $action->call($this, $params);
		}
		elseif (is_callable($action))
		{
			$result = call_user_func($action, $params);
		}
		elseif (is_string($action) && is_callable(array($this, $action)))
		{
			$result = call_user_func(array($this, $action), $params);
		}
		else
		{
			$this->addError(new Main\Error('Wrong action parameter!'));
		}

		if ($this->hasErrors())
		{
			$result['STATUS'] = Translate\Controller\STATUS_COMPLETED;
		}
		elseif ($this->hasProcessCompleted())
		{
			$result['STATUS'] = Translate\Controller\STATUS_COMPLETED;
		}
		else
		{
			$result['STATUS'] = Translate\Controller\STATUS_PROGRESS;
		}

		if ($this instanceof IProcessParameters)
		{
			// Save progress
			$this->saveProgressParameters();
		}

		return $result;
	}

	/**
	 * Switch accomplishment flag of the process.
	 *
	 * @param boolean $flag Accomplishment flag value.
	 *
	 * @return void
	 */
	public function declareAccomplishment($flag = true)
	{
		$this->isProcessCompleted = $flag;
	}

	/**
	 * Tells true if process has completed.
	 *
	 * @return boolean
	 */
	public function hasProcessCompleted()
	{
		return $this->isProcessCompleted;
	}


	/**
	 * Getting array of errors.
	 * @return boolean
	 */
	public function hasErrors()
	{
		/** @property \Bitrix\Main\ErrorCollection $errorCollection */
		if ($this->errorCollection instanceof Main\ErrorCollection)
		{
			return $this->errorCollection->isEmpty() !== true;
		}

		return false;
	}

	/**
	 * Gets timer.
	 *
	 * @return Translate\Controller\Timer
	 */
	public function instanceTimer()
	{
		if (!($this->timer instanceof Translate\Controller\Timer))
		{
			$this->timer = new Translate\Controller\Timer();
		}

		return $this->timer;
	}

	/**
	 * Sets start up time.
	 *
	 * @see Translate\Controller\ITimeLimit
	 *
	 * @return void
	 */
	public function startTimer()
	{
		$this->instanceTimer()->startTimer((int)START_EXEC_TIME);
	}

	/**
	 * Tells true if time limit reached.
	 *
	 * @see Translate\Controller\ITimeLimit
	 *
	 * @return boolean
	 */
	public function hasTimeLimitReached()
	{
		return $this->instanceTimer()->hasTimeLimitReached();
	}
}
