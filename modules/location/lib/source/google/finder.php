<?php

namespace Bitrix\Location\Source\Google;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Google\Converters\BaseConverter;
use Bitrix\Location\Source\Google\Requesters\BaseRequester;

/**
 * Class Finder
 * @package Bitrix\Location\Source\Google
 * @internal
 */
final class Finder
{
	protected $requester;
	protected $converter;

	/**
	 * Finder constructor.
	 * @param BaseRequester $requester
	 * @param BaseConverter $converter
	 */
	public function __construct(BaseRequester $requester, BaseConverter $converter = null)
	{
		$this->requester = $requester;
		$this->converter = $converter;
	}

	/**
	 * @param array $fields
	 * @return Collection|Location|false|null|array
	 * todo:// process case if status = ZERO_RESULTS description here: https://developers.google.com/places/web-service/details
	 */
	public function find(array $fields)
	{
		$rawData = $this->requester->request($fields);

		if (
			is_array($rawData)
			&& isset($rawData['status']) && $rawData['status'] !== 'OK'
			&& isset($rawData['error_message']) && $rawData['error_message'] <> ''
		)
		{
			throw new RuntimeException($rawData['error_message'], ErrorCodes::FINDER_ERROR);
		}

		return $this->converter !== null ? $this->converter->convert($rawData) : $rawData;
	}
}