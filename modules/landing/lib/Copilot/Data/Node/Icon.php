<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Node;

use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Assets\PreProcessing;

class Icon extends Node
{
	protected const TYPE = NodeType::Icon;
	protected const DEFAULT_VENDOR = 'fas';
	protected const DEFAULT_CLASS = 'fa-stars';
	protected const ALLOWED_VENDORS = ['fat', 'fal', 'far', 'fas', 'fab'];

	private ?array $classLists = null;
	private ?array $originalClassLists = null;

	public function __construct(string $code, array $data)
	{
		parent::__construct($code, $data);
		foreach ($data['values'] as $classList)
		{
			$classListArray = [];
			if (is_string($classList))
			{
				$classListArray = explode(' ', $classList);
			}
			if (is_array($classList))
			{
				$classListArray = $classList;
			}
			$this->originalClassLists[] = implode(' ', $classListArray);
			$classListArray = array_filter($classListArray, static function($value) {
				return $value !== null && str_starts_with($value, 'fa');
			});
			$classListArrayValues = array_values($classListArray);
			$this->classLists[] = $classListArrayValues;
		}
	}

	public function setData(array $data): void
	{
		$this->setClassLists($data);
	}

	public function getValues(?int $position = null): array
	{
		$nodeDataArray = [];

		$codeSuffix = substr($this->getCode(), 1);

		foreach ($this->classLists as $index => $classList)
		{
			$processedClassList = is_array($classList) ? implode(' ', $classList) : $classList;
			$processedClassList = $codeSuffix . ' ' . $processedClassList;

			$nodeDataArray[$index]['classList'] = $processedClassList;

			if (isset($this->originalClassLists[$index]))
			{
				$classListOriginal = $this->originalClassLists[$index];
				$classListOriginalArray = is_string($classListOriginal)
					? explode(' ', $classListOriginal)
					: $classListOriginal;

				$filteredClasses = array_filter($classListOriginalArray, static function($class) use ($codeSuffix) {
					return $class !== $codeSuffix && (!str_starts_with($class, 'fa') || $class === 'fa');
				});

				if (!empty($filteredClasses))
				{
					$nodeDataArray[$index]['classList'] .= ' ' . implode(' ', $filteredClasses);
				}
			}
		}

		return $nodeDataArray;
	}

	protected function setClassLists(array $classLists): self
	{
		$this->classLists = $this->validateClassLists($classLists);

		return $this;
	}

	protected function validateClassLists(array $classLists): array
	{
		$validatedClassLists = [];
		foreach ($classLists as $classList)
		{
			$validatedClassLists[] = $this->validateClassList($classList);
		}

		return $validatedClassLists;
	}

	/**
	 * Validates a string with a class and returns the correct version.
	 *
	 * @param string $classList
	 *
	 * @return string
	 */
	protected function validateClassList(string $classList): string
	{
		$classListArray = explode(' ', trim($classList));

		if (count($classListArray) !== 2)
		{
			return implode(' ', [self::DEFAULT_VENDOR, self::DEFAULT_CLASS]);
		}

		$vendorName = $classListArray[0];

		if (!in_array($vendorName, self::ALLOWED_VENDORS, true))
		{
			$vendorName = self::DEFAULT_VENDOR;
		}

		$className = $classListArray[1];

		if (!str_starts_with($className, 'fa-'))
		{
			$className = self::DEFAULT_CLASS;
		}

		$iconContent = PreProcessing\Icon::getIconContentByClass($className, $vendorName);
		if ($iconContent === null)
		{
			$iconContentFound = false;
			foreach (self::ALLOWED_VENDORS as $vendor)
			{
				$iconContent = PreProcessing\Icon::getIconContentByClass(
					$className,
					$vendor
				);
				if ($iconContent !== null)
				{
					$vendorName = $vendor;
					$iconContentFound = true;
					break;
				}
			}
			if ($iconContentFound === false)
			{
				$vendorName = self::DEFAULT_VENDOR;
				$className = self::DEFAULT_CLASS;
			}
		}

		return implode(' ', [$vendorName, $className]);
	}
}