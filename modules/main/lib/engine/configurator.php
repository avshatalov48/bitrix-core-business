<?php

namespace Bitrix\Main\Engine;

use Bitrix\Main\Engine\ActionFilter\Attribute\AttributeReader;
use Bitrix\Main\Engine\ActionFilter\FilterType;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Exception\ActionConfigurationException;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Closure;
use ReflectionClass;
use ReflectionMethod;

final class Configurator
{
	public const EVENT_ON_BUILD_ACTIONS = 'onBuildActions';

	public function getConfigurationByController(Controller $controller): array
	{
		$newConfiguration = $this->onBuildConfigurationOfActions($controller);
		$configuration = $controller->configureActions() ? : [];

		$lengthSuffix = mb_strlen(Controllerable::METHOD_ACTION_SUFFIX);
		$reflectionClass = new ReflectionClass($controller);

		$attributeReader = new AttributeReader();

		foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$probablySuffix = mb_substr($method->getName(), -$lengthSuffix);
			if ($probablySuffix !== Controllerable::METHOD_ACTION_SUFFIX)
			{
				continue;
			}

			$actionName = mb_substr($method->name, 0, -$lengthSuffix);
			if (!$attributeReader->hasMethodFilterAttributes($method))
			{
				continue;
			}

			if ($this->isExists($configuration, $actionName))
			{
				throw new ActionConfigurationException('Invalid configuration of actions. Configuration is multiple');
			}

			$configuration[$actionName] = $attributeReader->buildConfig($method);
		}

		$configuration = array_merge($newConfiguration, $configuration);
		$configuration = $this->wrapClosure($configuration);

		$this->checkConfigurations($configuration);

		return $configuration;
	}

	private function isExists(array $configuration, $actionName): bool
	{
		$listOfActions = array_change_key_case($configuration);
		$actionName = mb_strtolower($actionName);

		return isset($listOfActions[$actionName]);
	}

	private function onBuildConfigurationOfActions(Controller $controller): array
	{
		$event = new Event(
			'main',
			self::EVENT_ON_BUILD_ACTIONS,
			[
				'controller' => $controller,
			]
		);
		$event->send($this);

		$newConfiguration = [];
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

	public function wrapFiltersClosure(array $filters): array
	{
		foreach ($filters as $i => $filter)
		{
			if (!($filter instanceof Closure))
			{
				continue;
			}

			$filters[$i] = new ActionFilter\ClosureWrapper($filter);
		}

		return $filters;
	}

	private function wrapClosure(array $configurations): array
	{
		foreach ($configurations as $actionName => $configuration)
		{
			if (!empty($configuration[FilterType::Prefilter->value]))
			{
				$configurations[$actionName][FilterType::Prefilter->value] =
					$this->wrapFiltersClosure($configuration[FilterType::Prefilter->value]);
			}

			if (!empty($configuration[FilterType::EnablePrefilter->value]))
			{
				$configurations[$actionName][FilterType::EnablePrefilter->value] =
					$this->wrapFiltersClosure($configuration[FilterType::EnablePrefilter->value]);
			}

			if (!empty($configuration[FilterType::Postfilter->value]))
			{
				$configurations[$actionName][FilterType::Postfilter->value] =
					$this->wrapFiltersClosure($configuration[FilterType::Postfilter->value]);
			}

			if (!empty($configuration[FilterType::EnablePostfilter->value]))
			{
				$configurations[$actionName][FilterType::EnablePostfilter->value]
					= $this->wrapFiltersClosure($configuration[FilterType::EnablePostfilter->value]);
			}
		}

		return $configurations;
	}

	private function checkConfigurations(array $configurations): void
	{
		foreach ($configurations as $actionName => $configuration)
		{
			if (!is_string($actionName))
			{
				throw new ActionConfigurationException('Invalid configuration of actions. Action has to be string');
			}

			if (!is_array($configuration))
			{
				throw new ActionConfigurationException('Invalid configuration of actions. Configuration has to be array');
			}

			if (!empty($configuration[FilterType::Prefilter->value]))
			{
				$this->checkFilters($configuration[FilterType::Prefilter->value]);
			}

			if (!empty($configuration[FilterType::Postfilter->value]))
			{
				$this->checkFilters($configuration[FilterType::Postfilter->value]);
			}
		}
	}

	private function checkFilters(array $filters): void
	{
		foreach ($filters as $filter)
		{
			if (!($filter instanceof ActionFilter\Base))
			{
				throw new ActionConfigurationException('Filter has to be subclass of ' . ActionFilter\Base::className());
			}
		}
	}
}
