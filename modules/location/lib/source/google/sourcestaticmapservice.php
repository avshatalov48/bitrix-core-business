<?php

namespace Bitrix\Location\Source\Google;

use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\StaticMap;
use Bitrix\Location\StaticMap\StaticMapResult;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;

final class SourceStaticMapService extends StaticMap\SourceStaticMapService
{
	private HttpClient $httpClient;
	private ?string $backendKey;

	public function __construct(GoogleSource $source)
	{
		parent::__construct($source);

		$this->httpClient = new HttpClient([
			'version' => HttpClient::HTTP_1_1,
			'socketTimeout' => 5,
			'streamTimeout' => 5,
			'redirect' => true,
			'redirectMax' => 5,
		]);
	}

	public function getStaticMap(Point $point, int $zoom, int $width, int $height): StaticMapResult
	{
		$result = new StaticMapResult();

		if (is_null($this->backendKey))
		{
			$result->addError(new Error('API key is not specified'));

			return $result;
		}

		$response = $this->httpClient->get(
			'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
				'center' => implode(
					',',
					[
						$point->getLat(),
						$point->getLng(),
					]
				),
				'zoom' => $zoom,
				'size' => implode(
					'x',
					[
						$width,
						$height,
					]
				),
				'key' => $this->backendKey,
			])
		);
		$status = $this->httpClient->getStatus();

		if (!$response || $status !== 200)
		{
			$result->addError(new Error('Service request error'));

			return $result;
		}

		return $result
			->setContent($response)
			->setMimeType('image/png')
		;
	}

	public function setBackendKey(?string $backendKey): SourceStaticMapService
	{
		$this->backendKey = $backendKey;

		return $this;
	}
}
