<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Asset;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\ViewSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\ViewSpecSettings;

class ViewSpecAssembler implements Assembler
{

	/**
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request)
	{
		$viewSpec = new ViewSpec();
		$viewSpecSettings = new ViewSpecSettings();

		$viewSpec->setMarkup($request->get('markup'));
		$viewSpec->setScript($request->get('script'));
		$viewSpec->setStyles($request->get('styles'));
		$viewSpec->setAssets(new Asset());
		$viewSpec->setSettings($viewSpecSettings);

		return $viewSpec;
	}
}