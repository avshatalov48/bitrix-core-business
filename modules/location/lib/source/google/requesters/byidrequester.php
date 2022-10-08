<?php

namespace Bitrix\Location\Source\Google\Requesters;

/**
 * Interface RequesterBase
 * @package Bitrix\Location\Source\Google\Requesters
 */
final class ByIdRequester extends BaseRequester
{
	protected $url = 'https://maps.googleapis.com/maps/api/place/details/json';
	protected $defaultFields = 'formatted_address,name,place_id,types,address_components,geometry';
	protected $requiredFields = ['placeid', 'language', 'key'];
	protected $fieldsToEncode = ['placeid'];

	/**
	 * @param array $data
	 * @return mixed|string
	 */
	protected function makeUrl(array $data): string
	{
		if(empty($data['fields']))
		{
			$data['fields'] = $this->defaultFields;
		}

		return parent::makeUrl($data);
	}
}
