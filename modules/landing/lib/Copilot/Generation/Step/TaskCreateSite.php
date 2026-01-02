<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site;
use CUtil;

class TaskCreateSite extends TaskStep
{
	private const DEFAULT_PAGE_CODE = 'create-by-copilot';

	/**
	 * Executes the main task of the current process.
	 *
	 * @return bool Returns true upon successful completion of the site's creation.
	 */
	public function execute(): bool
	{
		parent::execute();

		$this->createSite();

		return true;
	}

	/**
	 * Creates a site and its landing page.
	 */
	private function createSite(): void
	{
		$siteId = $this->addSite();
		if ($siteId > 0)
		{
			$this->siteData->setSiteId($siteId);
			$landingId = $this->addLanding($siteId);
			if ($landingId > 0)
			{
				$this->siteData->setLandingId($landingId);
				$this->addAdditionalFieldsToLanding();
			}

			$previewSrc = $this->siteData->getPreviewImageSrc();
			if ($previewSrc === null || $previewSrc === '')
			{
				$defaultPreview = Manager::getUrlFromFile('/bitrix/images/landing/copilot-preview.jpg');
				$this->siteData->setPreviewImageSrc($defaultPreview);
				$previewSrc = $defaultPreview;
			}

			$additionalFields = [
				'METAOG_IMAGE' => $previewSrc,
			];
			Rights::setGlobalOff();
			Landing::saveAdditionalFields($this->siteData->getLandingId(), $additionalFields);
			Rights::setGlobalOn();

			if ($this->generation->getChatId())
			{
				(new Connector\Chat\Chat())
					->setUserId($this->generation->getAuthorId())
					->setChatForSite(
						$siteId,
						$this->generation->getChatId()
					)
				;
			}
		}
	}

	/**
	 * Adds a new site using the site data.
	 *
	 * @return int The ID of the newly created site, or 0 on failure.
	 */
	protected function addSite(): int
	{
		$colors = $this->siteData->getColors();
		$additionalFields = [];

		$this->processColor($colors->theme, 'THEME_COLOR', $additionalFields);
		$this->processColor($colors->background, 'BACKGROUND_COLOR', $additionalFields);
		if (isset($additionalFields['BACKGROUND_COLOR']))
		{
			$additionalFields['BACKGROUND_USE'] = 'Y';
		}
		$this->processColor($colors->headersBg, 'THEMEFONTS_COLOR_H', $additionalFields);
		$this->processColor($colors->textsBg, 'THEMEFONTS_COLOR', $additionalFields);

		$fonts = $this->siteData->getFonts();
		$additionalFields['THEMEFONTS_CODE_H'] = $fonts->headers;
		$additionalFields['THEMEFONTS_CODE'] = $fonts->texts;

		$siteTitle = $this->siteData->getSiteTitle();
		$siteFields = [
			'TITLE' => $siteTitle,
			'CODE' => self::getTranslitedCode($siteTitle),
			'TYPE' => 'PAGE',
			'ADDITIONAL_FIELDS' => $additionalFields,
		];
		Rights::setGlobalOff();
		$siteAddResult = Site::add($siteFields);
		Rights::setGlobalOn();

		return $siteAddResult->isSuccess() ? $siteAddResult->getId() : 0;
	}

	/**
	 * Adds a new landing page to a site.
	 *
	 * @param int $siteId The ID of the site to which the landing page will be added.
	 *
	 * @return int The ID of the newly created landing page, or 0 on failure.
	 */
	protected function addLanding(int $siteId): int
	{
		$pageTitle = $this->siteData->getPageTitle();
		$landingFields = [
			'SITE_ID' => $siteId,
			'TITLE' => $pageTitle,
			'CODE' => self::getTranslitedCode($pageTitle),
		];

		Rights::setGlobalOff();
		$landingAddResult = Landing::add($landingFields);
		Rights::setGlobalOn();

		if (!$landingAddResult->isSuccess())
		{
			return 0;
		}

		return $landingAddResult->getId();
	}

	protected function addAdditionalFieldsToLanding(): void
	{
		$pageTitle = $this->siteData->getPageTitle();
		$pageDescription = $this->siteData->getPageDescription();
		$additionalFields = [
			'METAOG_DESCRIPTION' => $pageDescription,
			'METAMAIN_USE' => 'Y',
			'METAMAIN_TITLE' => $pageTitle,
			'METAMAIN_DESCRIPTION' => $pageDescription,
			'METAMAIN_KEYWORDS' => $this->siteData->getKeywords() ?? '',
		];

		$landingId = $this->siteData->getLandingId();

		Rights::setGlobalOff();
		Landing::saveAdditionalFields($landingId, $additionalFields);
		Rights::setGlobalOn();
	}

	/**
	 * Processes a color and adds it to additional fields if valid.
	 *
	 * @param string $color The color object containing a hex property.
	 * @param string $fieldKey The key for the additional field.
	 * @param array &$additionalFields The array of additional fields to update.
	 *
	 * @return void
	 */
	private function processColor(string $color, string $fieldKey, array &$additionalFields): void
	{
		$code = $this->convertToLowercase($color);
		if ($this->isValidHexCode($code))
		{
			$additionalFields[$fieldKey] = $code;
		}
	}

	/**
	 * Converts a string to lowercase.
	 *
	 * @param string $string The string to convert.
	 *
	 * @return string The converted lowercase string.
	 */
	private function convertToLowercase(string $string): string
	{
		return strtr($string, 'ABCDEF', 'abcdef');
	}

	/**
	 * Validates if a string is a valid hex color code.
	 *
	 * @param string $string The string to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private function isValidHexCode(string $string): bool
	{
		return preg_match('/^#[0-9a-f]{6}$/', $string) === 1;
	}

	/**
	 * Transliterates a given code into a safe URL-friendly format.
	 *
	 * @param string $code The original code that needs to be transliterated.
	 *
	 * @return string|null The transliterated and URL-safe version of the code or
	 * a default page code if transliteration fails.
	 */
	protected static function getTranslitedCode(string $code): ?string
	{
		$params = [
			"max_len" => 30,
			"change_case" => "L",
			"replace_space" => "-",
			"replace_other" => "",
			"delete_repeat_replace" => true,
		];
		$translitedCode = CUtil::translit(
			$code,
			LANGUAGE_ID,
			$params
		);
		$pattern = '/[^-]/';
		if (LANGUAGE_ID !== 'en' && !preg_match($pattern, $translitedCode))
		{
			$translitedCode = CUtil::translit(
				$translitedCode,
				'en',
				$params
			);
		}
		if (!preg_match($pattern, $translitedCode))
		{
			$translitedCode = self::DEFAULT_PAGE_CODE;
		}

		return $translitedCode;
	}

	/**
	 * Determines if the process is finished based on site id and landing id
	 *
	 * @return bool True if site id and landing id have non-zero IDs, false otherwise.
	 */
	public function isFinished(): bool
	{
		$siteId = $this->siteData->getSiteId();
		$landingId = $this->siteData->getLandingId();

		if (is_int($siteId) && is_int($landingId) && $siteId > 0 && $landingId > 0)
		{
			return true;
		}

		if (is_int($siteId) && is_int($landingId))
		{
			$exceptionMessage = "Landing id not correct.";
		}
		else
		{
			$exceptionMessage = "Site id not correct.";
		}

		throw new GenerationException(
			GenerationErrors::dataValidation,
			$exceptionMessage,
		);
	}
}