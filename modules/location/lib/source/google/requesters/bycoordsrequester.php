<?php

namespace Bitrix\Location\Source\Google\Requesters;

/**
 * Interface PlaceById
 * @package Bitrix\Location\Source\Google\Requesters
 * todo: restricts results by types?
 */
final class ByCoordsRequester extends BaseRequester
{
	protected $url = 'https://maps.googleapis.com/maps/api/geocode/json';
	protected $requiredFields = ['latlng', 'language', 'key'];

	protected function makeUrl(array $data): string
	{
		return parent::makeUrl($data) . '&location_type=ROOFTOP';
	}
}
