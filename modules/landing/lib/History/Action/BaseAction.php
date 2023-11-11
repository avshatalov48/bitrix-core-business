<?php
namespace Bitrix\Landing\History\Action;

abstract class BaseAction
{
	protected const JS_COMMAND = '';
	protected array $params = [];

	/**
	 * @param array $params
	 * @param bool $prepared - If true - no need prepare before set. Default - need prepare
	 * @return void
	 */
	public function setParams(array $params, bool $prepared = false): void
	{
		if (!$prepared)
		{
			$params = static::enrichParams($params);
		}
		$this->params = $params;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	abstract public function execute(bool $undo = true): bool;
	abstract public static function enrichParams(array $params): array;

	/**
	 * If need - do preliminary operations before del from table
	 * @return bool
	 */
	public function delete(): bool
	{
		return true;
	}

	/**
	 * Check correctly params before push
	 * @return bool
	 */
	public function isNeedPush(): bool
	{
		// todo: compare valuebefore||valueafter (see examples in some actions)
		return !empty($this->params);
	}

	/**
	 * @param bool $undo - if false - redo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		return [
			'command' => static::JS_COMMAND,
			'params' => $this->params,
		];
	}

	/**
	 * Get name of JS action command
	 * @return string
	 */
	public static function getJsCommandName(): string
	{
		return static::JS_COMMAND;
	}

	/**
	 * Check if params duplicated with previously step
	 * @param array $oldParams
	 * @param array $newParams
	 * @return bool
	 */
	public static function compareParams(array $oldParams, array $newParams): bool
	{
		return $oldParams === $newParams;
	}
}