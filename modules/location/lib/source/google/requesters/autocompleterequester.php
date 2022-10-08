<?php

namespace Bitrix\Location\Source\Google\Requesters;

/**
 * Interface RequesterBase
 * @package Bitrix\Location\Source\Google\Requesters
 */
final class AutocompleteRequester extends BaseRequester
{
	protected $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';
	protected $requiredFields = ['input', 'language', 'key'];
	protected $fieldsToEncode = ['input'];

}
