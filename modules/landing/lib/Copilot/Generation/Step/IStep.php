<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Metrika;

interface IStep
{
	/**
	 * Set required params
	 * @param Generation $generation
	 * @param int|null $stepId
	 * @return $this
	 */
	public function init(Generation $generation, int $stepId): static;

	/**
	 * Create prompt by some data, send request to AI
	 * @return bool
	 * @throws GenerationException
	 */
	public function execute(): bool;

	/**
	 * Return true if step should't wait results to pass control to next step
	 * @return bool
	 */
	public function isAsync(): bool;

	/**
	 * Check if step was start executing
	 * @return bool
	 */
	public function isStarted(): bool;

	/**
	 * Check if step finish all actions
	 * @return bool
	 */
	public function isFinished(): bool;

	/**
	 * If step change data while executing
	 * @return bool
	 */
	public function isChanged(): bool;

	/**
	 * If step can have errors - clear to start condition
	 * @return void
	 */
	public function clearErrors(): void;

	/**
	 * Returns request limit object
	 *
	 * @param Site $siteData
	 *
	 * @return RequestQuotaDto|null
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto;

	/**
	 * Step may, or not may send analytic event.
	 * The Scenarist decides when to send the event.
	 * @return Metrika\Events|null
	 */
	public function getAnalyticEvent(): ?Metrika\Events;
}