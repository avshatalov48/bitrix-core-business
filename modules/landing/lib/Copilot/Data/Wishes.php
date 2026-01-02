<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Exception;

class Wishes
{
	private array $wishes = [];
	private ?string $company = null;

	protected bool $isDemo = false;

	/**
	 * Replace all wishes to passed array. Empty strings will be skipped
	 * @param array $wishes
	 * @return Wishes
	 */
	public function setWishes(array $wishes): self
	{
		$this->wishes = array_filter($this->decodeArray($wishes), static fn($wish) => !empty(trim($wish)));

		return $this;
	}

	/**
	 * Add one more wish to list
	 * @param string $wish
	 * @return Wishes
	 */
	public function addWish(string $wish): self
	{
		$wishDecoded = $this->decodeString($wish);

		if (!empty(trim($wishDecoded)))
		{
			$this->wishes[] = $wishDecoded;
		}

		return $this;
	}

	/**
	 * Set demo data to wishes
	 * @return Wishes
	 */
	public function setDemoWishes(): self
	{
		$this->wishes = [self::getDemoWish()];
		$this->isDemo = true;

		return $this;
	}

	/**
	 * Check is demo wishes used
	 * @return bool
	 */
	public function isDemoWishes(): bool
	{
		return $this->isDemo;
	}

	private static function getDemoWish(): string
	{
		return Loc::getMessage('LANDING_COPILOT_DEMO_WISH_' . (rand(1, 20)));
	}

	/**
	 * Return array ow wishes strings
	 * @return array
	 */
	public function getWishes(): array
	{
		return $this->encodeArray($this->wishes);
	}

	/**
	 * Set company name. Empty string will be skipped
	 * @param string $company
	 * @return Wishes
	 */
	public function setCompany(string $company): self
	{
		$companyDecoded = $this->decodeString($company);

		if (!empty(trim($companyDecoded)))
		{
			$this->company = $companyDecoded;
		}

		return $this;
	}

	/**
	 * Return company name, if set
	 * @return string|null
	 */
	public function getCompany(): ?string
	{
		return $this->company !== null ? $this->encodeString($this->company) : null;
	}

	/**
	 * Transform data object to array
	 * @return array
	 */
	public function toArray(): array
	{
		$data = [];

		if (!empty($this->wishes))
		{
			$data['wishes'] = $this->getWishes();
		}
		if (isset($this->company))
		{
			$data['company'] = $this->getCompany();
		}

		return $data;
	}

	/**
	 * Create data object from array
	 * @param array $data
	 * @return self
	 */
	public static function fromArray(array $data): self
	{
		$wishes = new self();

		if (
			isset($data['wishes'])
			&& is_array($data['wishes'])
		)
		{
			$wishes->setWishes($data['wishes']);
		}

		if (
			isset($data['company'])
			&& is_string($data['company'])
		)
		{
			$wishes->setCompany($data['company']);
		}

		return $wishes;
	}

	/**
	 * Decode an array of strings with emojis
	 * @param array $array
	 * @return array
	 */
	private function decodeArray(array $array): array
	{
		return array_map([$this, 'decodeString'], $array);
	}

	/**
	 * Encode an array of strings with emojis
	 * @param array $array
	 * @return array
	 */
	private function encodeArray(array $array): array
	{
		return array_map([$this, 'encodeString'], $array);
	}

	/**
	 * Decode a string with emojis
	 * @param string $string
	 * @return string
	 */
	private function decodeString(string $string): string
	{
		return Emoji::decode($string);
	}

	/**
	 * Encode a string with emojis
	 * @param string $string
	 * @return string
	 */
	private function encodeString(string $string): string
	{
		return Emoji::encode($string);
	}
}