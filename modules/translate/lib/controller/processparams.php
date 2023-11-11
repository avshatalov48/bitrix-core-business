<?php
namespace Bitrix\Translate\Controller;

use Bitrix\Translate;

/**
 * Manages process session parameters.
 *
 * @implements Translate\Controller\IProcessParameters
 */
trait ProcessParams
{
	/** @var string[] */
	protected array $fieldToStoreInProcess = [];

	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	public function getProgressParameterOptionName(): string
	{
		$settingId = 'translate';
		if ($this instanceof Translate\Controller\Action)
		{
			$controller = $this->getController();
			$settingId = $controller::SETTING_ID;
		}
		elseif ($this instanceof Translate\Controller\Controller)
		{
			$settingId = static::SETTING_ID;
		}

		$classId = \str_replace(['_', '\\'], '', static::class);

		$id = "{$settingId}/{$classId}";

		if (!empty($this->tabId))
		{
			$id .= '/'. $this->tabId;
		}

		return $id;
	}

	/**
	 * Tells if needed to keep field state as progress parameters.
	 *
	 * @param string|string[] $fieldName Name of instance field to keep in progress parameters.
	 *
	 * @return self
	 */
	public function keepField($fieldName): self
	{
		if (\is_array($fieldName))
		{
			$this->fieldToStoreInProcess = \array_merge($this->fieldToStoreInProcess, $fieldName);
		}
		else
		{
			$this->fieldToStoreInProcess[] = $fieldName;
		}

		return $this;
	}

	/**
	 * Restore progress state of the instance.
	 *
	 * @return self
	 */
	public function restoreProgressParameters(): self
	{
		if (\count($this->fieldToStoreInProcess) > 0)
		{
			$progressData = $this->getProgressParameters();
			if (\count($progressData) > 0)
			{
				foreach ($this->fieldToStoreInProcess as $fieldName)
				{
					if (isset($progressData[$fieldName]))
					{
						$this->{$fieldName} = $progressData[$fieldName];
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Save progress parameters.
	 *
	 * @return self
	 */
	public function saveProgressParameters(): self
	{
		// store state
		$progressData = [];
		foreach ($this->fieldToStoreInProcess as $fieldName)
		{
			$progressData[$fieldName] = $this->{$fieldName};
		}

		$optName = \explode('/', $this->getProgressParameterOptionName());
		$storage =& $_SESSION;
		for ($d = 0, $depth = \count($optName); $d < $depth; $d++)
		{
			if (!isset($storage[$optName[$d]]))
			{
				$storage[$optName[$d]] = [];
			}
			$storage =& $storage[$optName[$d]];
		}

		$storage = $progressData;

		return $this;
	}

	/**
	 * Load progress parameters.
	 *
	 * @return array
	 */
	public function getProgressParameters(): array
	{
		$optName = \explode('/', $this->getProgressParameterOptionName());
		$storage =& $_SESSION;
		for ($d = 0, $depth = \count($optName); $d < $depth; $d++)
		{
			if (!isset($storage[$optName[$d]]))
			{
				return [];
			}
			$storage =& $storage[$optName[$d]];
		}

		return $storage;
	}

	/**
	 * Removes progress parameters.
	 *
	 * @return self
	 */
	public function clearProgressParameters(): self
	{
		$optName = \explode('/', $this->getProgressParameterOptionName());
		$storage =& $_SESSION;
		for ($d = 0, $depth = \count($optName); $d < $depth; $d++)
		{
			if (!isset($storage[$optName[$d]]))
			{
				return $this;
			}
			$storage =& $storage[$optName[$d]];
		}

		unset($storage);

		return $this;
	}
}
