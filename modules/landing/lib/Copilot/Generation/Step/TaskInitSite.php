<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

class TaskInitSite extends TaskStep
{
	/**
	 * Executes the main task of the current process.
	 *
	 * @return bool Returns true upon successful completion of the site's creation.
	 */
	public function execute(): bool
	{
		parent::execute();

		$this->siteData->initRandom();

		return true;
	}
}