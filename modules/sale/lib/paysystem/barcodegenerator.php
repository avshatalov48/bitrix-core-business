<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\UI\Barcode\Barcode;
use Bitrix\UI\Barcode\BarcodeDictionary;

class BarcodeGenerator
{
	private Barcode $barcode;

	private const DEFAULT_W = 512;
	private const DEFAULT_H = 512;

	public function __construct()
	{
		if ($this->includeUiModule())
		{
			$this->createBarcode();
		}
	}

	private function createBarcode(): void
	{
		$this->barcode = new Barcode();
		$this->barcode
			->type(BarcodeDictionary::TYPE_QR)
			->format(BarcodeDictionary::FORMAT_PNG)
			->option('w', self::DEFAULT_W)
			->option('h', self::DEFAULT_H)
		;
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
