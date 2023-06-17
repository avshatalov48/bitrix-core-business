<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\UI\Barcode\Barcode;
use Bitrix\UI\Barcode\BarcodeDictionary;

class BarcodeGenerator
{
	private Barcode $barcode;

	private const DEFAULT_W = 380;
	private const DEFAULT_H = 380;
	private const DEFAULT_P = 0;
	private const DEFAULT_WQ = 0;

	public function __construct(?array $options = null)
	{
		if ($this->includeUiModule())
		{
			$this->createBarcode($options);
		}
	}

	private function createBarcode(?array $options = null): void
	{
		$options =
			is_null($options)
				? [
					'w' => self::DEFAULT_W,
					'h' => self::DEFAULT_H,
					'p' => self::DEFAULT_P,
					'wq' => self::DEFAULT_WQ,
				]
				: array_intersect_key($options, array_flip(self::getAllowedOptions()))
		;

		$this->barcode = new Barcode();
		$this->barcode
			->type(BarcodeDictionary::TYPE_QR)
			->format(BarcodeDictionary::FORMAT_PNG)
			->options($options)
		;
	}

	private static function getAllowedOptions(): array
	{
		return [
			'w',
			'h',
			'p',
			'wq',
		];
	}

	public function generate(string $data): ?string
	{
		$renderData = null;

		if ($this->includeUiModule())
		{
			$renderData = $this->barcode->render($data);
		}

		return $renderData ?: null;
	}

	private function includeUiModule(): bool
	{
		return Loader::includeModule('ui');
	}
}
