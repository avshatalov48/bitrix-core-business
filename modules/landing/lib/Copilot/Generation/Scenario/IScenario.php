<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Scenario;

use Bitrix\Landing\Copilot\Connector\Chat\ICopilotChatBot;
use Bitrix\Landing\Copilot\Generation\Step\IStep;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Metrika;

interface IScenario
{
	/**
	 * Array id => IStep of current scenario steps
	 * @return IStep[]
	 */
	public function getMap(): array;

	/**
	 * Get Chatbot for chat side of scenario
	 * @return ICopilotChatBot|null
	 */
	public function getChatbot(): ?ICopilotChatBot;

	public function getAnalyticCategory(): Metrika\Categories;

	/**
	 * If some steps must be run only after async step - need set relations
	 * f.e. [asyncStepId => [dependent_step_ids]]
	 * @return array|null
	 */
	public function getAsyncRelations(): ?array;

	/**
	 * Get ID of first step in scenario
	 * @return int|null - if null - has no steps in map
	 */
	public function getFirstStepId(): ?int;

	/**
	 * Get ID of next step in scenario
	 * @param int $stepId
	 * @return int|null - if null - current step is last
	 */
	public function getNextStepId(int $stepId): ?int;

	/**
	 * Check if stepId in current scenario
	 * @param int|null $stepId
	 * @return bool
	 */
	public function checkStep(?int $stepId): bool;

	/**
	 * Call method when scenario is finished
	 * @param Generation $generation
	 * @return void
	 */
	public function onFinish(Generation $generation): void;
}