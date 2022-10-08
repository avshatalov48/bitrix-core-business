<?php

namespace Bitrix\Location\Entity\Format;

use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Main\ArgumentTypeException;

final class TemplateCollection extends Collection
{
	/** @var Template[] */
	protected $items = [];

	/**
	 * Add Format field to collection
	 * @param Template $template
	 * @return int
	 * @throws ArgumentTypeException
	 */
	public function addItem($template): int
	{
		if(!($template instanceof Template))
		{
			throw new ArgumentTypeException('field must be the instance of Template');
		}

		$this->removeTemplateByType($template->getType());
		return parent::addItem($template);
	}

	/**
	 * @param string $type
	 */
	private function removeTemplateByType(string $type): void
	{
		foreach ($this->items as $idx => $template)
		{
			if($template->getType() === $type)
			{
				unset($this->items[$idx]);
				break;
			}
		}
	}

	/**
	 * @param string $type See TemplateType
	 * @return Template|null
	 */
	public function getTemplate(string $type): ?Template
	{
		foreach ($this->items as $template)
		{
			if($template->getType() === $type)
			{
				return $template;
			}
		}

		return null;
	}
}
