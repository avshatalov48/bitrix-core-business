<?php

namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

/**
 * Abstract common controller class
 * @package Bitrix\Report\VisualConstructor\Controller
 */
abstract class Base extends Controller
{
	/**
	 * If debug mode on return last 3 lines of trace in error collection.
	 *
	 * @param \Exception $e Exception.
	 * @return void
	 */
	protected function runProcessingException(\Exception $e)
	{
		parent::runProcessingException($e);
		$exceptionHandling = Configuration::getValue('exception_handling');
		if (!empty($exceptionHandling['debug']))
		{
			$trace = $e->getTrace();

			$traceLength = count($trace);

			$this->addError(new Error($e->getFile() . ':' . $e->getLine()));


			for ($i = 0; $i < $traceLength && $i < 3; $i++)
			{
				$this->addError(new Error( '#' . $i . ' ' . $trace[$i]['file'] . ':' . $trace[$i]['line'] . '  ' .  $trace[$i]['function']));
			}

		}
	}
}