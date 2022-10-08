<?php

namespace Bitrix\Calendar\Sync\Internals;

trait HasContextTrait
{
	/** @var ContextInterface */
	protected ContextInterface $context;

	/**
	 * @return ContextInterface
	 */
	public function getContext(): ContextInterface
	{
		return $this->context;
	}
}
