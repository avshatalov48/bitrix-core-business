<?php

namespace Bitrix\Main\Engine;

use Bitrix\Main\Annotations\AnnotationReader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\SystemException;

final class Configurator
{
	const EVENT_ON_BUILD_ACTIONS = 'onBuildActions';

	const MODE_ANNOTATIONS = 0x0001;

	protected $mode = 0;

	/**
	 * Configurator constructor.
	 */
	public function __construct()
	{
	}

	public function getConfigurationByAnnotations(array $annotations)
	{
		$configuration = null;
		if (empty($annotations))
		{
			return $configuration;
		}

		if (isset($annotations['Action']) || array_key_exists('Action', $annotations))
		{
			$configuration = array(
				'prefilters' => array(),
				'postfilters' => array(),
			);

			if (isset($annotations['Action']['method']))
			{
				if (!is_array($annotations['Action']['method']))
				{
					$annotations['Action']['method'] = array($annotations['Action']['method']);
				}

				$configuration['prefilters'][] = new ActionFilter\HttpMethod($annotations['Action']['method']);
			}

			if (isset($annotations['Action']['csrf']) && is_bool($annotations['Action']['csrf']))
			{
				$configuration['prefilters'][] = new ActionFilter\Csrf($annotations['Action']['csrf']);
			}

			if (!$configuration && !isset($configuration['postfilters']))
			{
				$configuration['postfilters'] = array();
			}
		}

		return $configuration;
	}

	public function getConfigurationByController(Controller $controller)
	{
		$newConfiguration = $this->onBuildConfigurationOfActions($controller);
		$configuration = $controller->configureActions() ? : array();

		if ($this->mode & self::MODE_ANNOTATIONS)
		{
			$annotationReader = new AnnotationReader();

			$lengthSuffix = mb_strlen(Controllerable::METHOD_ACTION_SUFFIX);
			$reflectionClass = new \ReflectionClass($controller);
			foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
			{
				if (!$method->isPublic())
				{
					continue;
				}

				$probablySuffix = mb_substr($method->getName(), -$lengthSuffix);
				if ($probablySuffix !== Controllerable::METHOD_ACTION_SUFFIX)
				{
					continue;
				}

				$actionName = mb_substr($method, 0, -$lengthSuffix);
				if ($this->isExists($configuration, $actionName))
				{
					//we have already config and don't have to grab annotations
					continue;
				}

				$annotations = $annotationReader->getMethodAnnotations($method);
				if (!$this->checkReflectionMethodAsAction($method, $annotations))
				{
					continue;
				}

				$configuration[$actionName] = $this->getConfigurationByAnnotations($annotations);
			}
		}

		$configuration = array_merge($newConfiguration, $configuration);
		$configuration = $this->wrapClosure($configuration);

		$this->checkConfigurations($configuration);

		return $configuration;
	}

	private function isExists(array $configuration, $actionName)
	{
		$listOfActions = array_change_key_case($configuration, CASE_LOWER);
		$actionName = mb_strtolower($actionName);

		return isset($listOfActions[$actionName]);
	}

	private function checkReflectionMethodAsAction(\ReflectionMethod $reflectionMethod, array $annotations = null)
	{
		if (!$reflectionMethod->isPublic())
		{
			return false;
		}

		if ($annotations === null)
		{
			$annotationReader = new AnnotationReader();
			$annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
		}

		if (!is_array($annotations) || !array_key_exists('Action', $annotations))
		{
			return false;
		}

		return true;
	}

	private function onBuildConfigurationOfActions(Controller $controller)
	{
		//todo set name of the controller in event name? or use filter?
		$event = new Event(
			'main',
			static::EVENT_ON_BUILD_ACTIONS,
			array(
				'controller' => $controller,
			)
		);
		$event->send($this);

		$newConfiguration = array();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() != EventResult::SUCCESS)
			{
				continue;
			}

			$parameters = $eventResult->getParameters();
			if (isset($parameters['extraActions']) && is_array($parameters['extraActions']))
			{
				//configuration in $this->configureActions() has more priority then configuration which was provided by event
				$newConfiguration = array_merge($newConfiguration, $parameters['extraActions']);
			}
		}

		return $newConfiguration;
	}

	public function wrapFiltersClosure(array $filters)
	{
		foreach ($filters as $i => $filter)
		{
			if (!($filter instanceof \Closure))
			{
				continue;
			}

			$filters[$i] = new ActionFilter\ClosureWrapper($filter);
		}

		return $filters;
	}

	private function wrapClosure(array $configurations)
	{
		foreach ($configurations as $actionName => $configuration)
		{
			if (!empty($configuration['prefilters']))
			{
				$configurations[$actionName]['prefilters'] = $this->wrapFiltersClosure($configuration['prefilters']);
			}

			if (!empty($configuration['+prefilters']))
			{
				$configurations[$actionName]['+prefilters'] = $this->wrapFiltersClosure($configuration['+prefilters']);
			}

			if (!empty($configuration['postfilters']))
			{
				$configurations[$actionName]['postfilters'] = $this->wrapFiltersClosure($configuration['postfilters']);
			}

			if (!empty($configuration['+postfilters']))
			{
				$configurations[$actionName]['+postfilters'] = $this->wrapFiltersClosure($configuration['+postfilters']);
			}
		}

		return $configurations;
	}

	private function checkConfigurations(array $configurations)
	{
		foreach ($configurations as $actionName => $configuration)
		{
			if (!is_string($actionName))
			{
				throw new SystemException('Invalid configuration of actions. Action has to be string');
			}

			if (!is_array($configuration))
			{
				throw new SystemException('Invalid configuration of actions. Configuration has to be array');
			}

			if (!empty($configuration['prefilters']))
			{
				$this->checkFilters($configuration['prefilters']);
			}

			if (!empty($configuration['postfilters']))
			{
				$this->checkFilters($configuration['postfilters']);
			}
		}
	}

	private function checkFilters(array $filters)
	{
		foreach ($filters as $filter)
		{
			if (!($filter instanceof ActionFilter\Base))
			{
				throw new SystemException('Filter has to be subclass of ' . ActionFilter\Base::className());
			}
		}
	}

}