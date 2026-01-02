<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Generation\Error;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Markers;
use Bitrix\Landing\Copilot\Generation\Type\Errors;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Metrika;

class RequestSiteData extends RequestSingle
{
	public function __construct()
	{
		parent::__construct();
		if (class_exists(self::getConnectorClass()))
		{
			$this->connector = new (self::getConnectorClass())();
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function getConnectorClass(): string
	{
		return AI\Text::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto
	{
		return new RequestQuotaDto(self::getConnectorClass(), 1);
	}

	public function getAnalyticEvent(): ?Metrika\Events
	{
		return Metrika\Events::dataGeneration;
	}

	protected function getPrompt(): Prompt
	{
		$prompt = new Prompt('landing_ai_data');
		$prompt->setMarkers(Markers::getSiteDataPromptMarkers($this->siteData));

		return $prompt;
	}

	protected function applyResponse(): bool
	{
		$result = $this->request->getResult();

		if ($result)
		{
			Site::initSite($this->siteData, $result);
		}

		return true;
	}

	/**
	 * Verifies the completeness of the response data from a request.
	 *
	 * This function checks the result of a request to ensure it exists and contains
	 * the necessary data. If the result is missing or does not include the required
	 * 'colors' or 'titles' under 'siteData', an GenerationException is thrown.
	 *
	 * @return void
	 */
	public function verifyResponse(): void
	{
		$result = $this->request->getResult();

		if (!$result)
		{
			throw new GenerationException(GenerationErrors::notExistResponse);
		}

		if (
			!isset($result['siteData']['isAllowedRequest'])
			|| $result['siteData']['isAllowedRequest'] !== "yes"
		)
		{
			$this->request->saveError(Error::createError(Errors::requestNotAllowed));

			throw new GenerationException(GenerationErrors::restrictedRequest);
		}

		if (
			!isset($result['siteData']['colors'])
			&& !isset($result['siteData']['titles'])
		)
		{
			throw new GenerationException(
				GenerationErrors::notFullyResponse,
				"Missing required 'colors' or 'titles' in 'siteData'.",
			);
		}
	}
}