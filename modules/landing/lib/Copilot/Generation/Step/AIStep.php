<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI\IConnector;

abstract class AIStep extends BaseStep
{
	protected IConnector $connector;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Return class name of AI connector
	 * Should be overwritten in child classes.
	 * @return string
	 */
	abstract public static function getConnectorClass(): string;

	public function execute(): bool
	{
		return parent::execute() && isset($this->connector);
	}
}