<?php

namespace Bitrix\Seo\UI\Provider;

use Bitrix\Seo\Marketing\Configurator;
use Bitrix\Seo\Marketing\Services\AdCampaignFacebook;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class InterestsProvider extends \Bitrix\UI\EntitySelector\BaseProvider
{
	const SEARCH_TYPE = 'adinterest';
	const ENTITY_TYPE = 'facebook_interests';

	public function __construct(array $options = [])
	{
		parent::__construct();
		$this->options['clientId'] = $options['clientId'];
	}

	public function isAvailable()
	: bool
	{
		if($this->getOption('clientId'))
		{
			return true;
		}

		return false;
	}

	public function getItems(array $ids)
	: array
	{
		return [];
	}

	public function getSelectedItems(array $ids)
	: array
	{
		return [];
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$service = Configurator::getService();
		$service->setClientId($this->getOption('clientId'));
		$searchQuery->setCacheable(false);

		$response = Configurator::searchTargetingData(
			AdCampaignFacebook::TYPE_CODE,
			[
				'q' => $searchQuery->getQuery(),
				'type' => static::SEARCH_TYPE
			]
		);

		$items = [];

		foreach($response as $value)
		{
			if(!isset($value['name']))
			{
				continue;
			}

			$topic = htmlspecialcharsbx($value['topic']);
			$title = $value['name']. ($topic ?" ({$topic})" : "");
			$items[] =
				new Item(
					[
						'id'                   => $value['id'],
						'entityId'             => static::ENTITY_TYPE,
						'title'                => $title,
						'customData' => ['audienceSize' => $value['audience_size']],
						'tagOptions' => [
							'bgColor' => "#{$this->stringToColor($title)}",
							'textColor' => "#fff"
						]
					]
				);
		}

		$dialog->addItems(
			$items
		);
	}

	protected function stringToColor($text)
	{
		$hx = dechex(crc32($text));

		return substr($hx, 0, 6);
	}
}