<?php

namespace Bitrix\Seo\Conversion\Facebook;

/**
 * docs: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/custom-data
 * Class CustomData
 * @package Bitrix\Seo\BusinessSuite\Conversion
 */
final class CustomData
{
	public const CONTENT_CATEGORY_PRODUCT = "product";
	public const CONTENT_CATEGORY_PRODUCT_GROUP = "product_group";

	private $container = [];

	/**
	 * CustomData constructor.
	 *
	 * @param array|null $params
	 */
	public function __construct(?array $params = null)
	{
		if ($params && !empty($params))
		{
			if (array_key_exists('value',$params))
			{
				$this->setValue($params['value']);
			}
			if (array_key_exists('currency',$params) && is_string($params['currency']))
			{
				$this->setCurrency($params['currency']);
			}
			if (array_key_exists('content_name',$params) && is_string($params['content_name']))
			{
				$this->setContentName($params['content_name']);
			}
			if (array_key_exists('content_category',$params) && is_string($params['content_category']))
			{
				$this->setContentCategory($params['content_category']);
			}
			if (array_key_exists('content_ids',$params) && is_array($params['content_ids']))
			{
				$this->setContentIds($params['content_ids']);
			}
			if (array_key_exists('contents',$params) && is_array($params['contents']))
			{
				$this->setContents($params['contents']);
			}
			if (array_key_exists('content_type',$params) && is_string($params['content_type']))
			{
				$this->setContentType($params['content_type']);
			}
			if (array_key_exists('predicted_ltv',$params))
			{
				$this->setPredictedLtv($params['predicted_ltv']);
			}
			if (array_key_exists('num_items',$params) && is_int($params['num_items']))
			{
				$this->setNumItems($params['num_items']);
			}
			if (array_key_exists('status',$params) && is_string($params['status']))
			{
				$this->setStatus($params['status']);
			}
			if (array_key_exists('search_string',$params) && is_string($params['search_string']))
			{
				$this->setSearchString($params['search_string']);
			}
			if (array_key_exists('custom_properties',$params) && is_array($params['custom_properties']))
			{
				$this->setCustomProperties($params['custom_properties']);
			}

		}

	}

	public function setValue($value)
	{
		if(is_float($value) || is_int($value))
		{
			$this->container['value'] = $value;
		}
		return $this;
	}

	public function setCurrency(?string $currency)
	{
		$this->container['currency'] = $currency;
		return $this;
	}

	public function setContentName(?string $contentName)
	{
		$this->container['content_name'] = $contentName;
		return $this;
	}

	public function setContentCategory(?string $contentCategory)
	{
		$this->container['content_category'] = $contentCategory;
		return $this;
	}

	public function setContentIds(?array $contentIds)
	{
		if (!empty($contentIds))
		{
			$this->container['content_ids'] = array_filter($contentIds, function($item){
				return is_string($item) || is_int($item);
			});
		}
		return $this;
	}

	public function setContents(?array $contents)
	{
		if (!empty($contents))
		{
			$this->container['contents'] = array_filter(
				$contents,
				static function($item)
				{
					return is_array($item) && isset($item['product_id'],$item['quantity']);
				}
			);
		}

		return $this;
	}

	public function setContentType(?string $type)
	{
		if (in_array($type,[static::CONTENT_CATEGORY_PRODUCT,static::CONTENT_CATEGORY_PRODUCT_GROUP]))
		{
			$this->container['content_type'] = $type;
		}
		return $this;
	}

	public function setPredictedLtv($predicted)
	{
		if (is_float($predicted) || is_int($predicted))
		{
			$this->container['predicted_ltv'] = $predicted;
		}
		return $this;
	}

	public function setNumItems(?int $items)
	{
		$this->container['num_items'] = $items;
		return $this;
	}

	public function setStatus($status)
	{
		$this->container['status'] = $status;
		return $this;
	}

	public function setSearchString(?string $searchString)
	{
		$this->container['search_string'] = $searchString;
		return $this;
	}

	public function setDeliveryCategory($category)
	{
		$this->container['delivery_category'] = $category;
		return $this;
	}

	public function setCustomProperties(?array $custom_properties)
	{
		$this->container['custom_properties'] = $custom_properties;
		return $this;
	}

	public function getValue()
	{
		return $this->container['value'];
	}

	public function getCurrency()
	{
		return $this->container['currency'];
	}

	public function getContentName()
	{
		return $this->container['content_name'];
	}

	public function getContentCategory()
	{
		return $this->container['content_category'];
	}

	public function getContentIds()
	{
		return $this->container['content_ids'];
	}

	public function getContents()
	{
		return $this->container['contents'];
	}

	public function getContentType()
	{
		return $this->container['content_type'];
	}

	public function getPredictedLtv()
	{
		return $this->container['predicted_ltv'];
	}

	public function getNumItems()
	{
		return $this->container['num_items'];
	}

	public function getStatus()
	{
		return $this->container['status'];
	}

	public function getSearchString()
	{
		return $this->container['search_string'];
	}

	public function getDeliveryCategory()
	{
		return $this->container['delivery_category'];
	}

	public function getCustomProperties($custom_properties)
	{
		return $this->container['custom_properties'];
	}

	public function validate() : bool
	{
		return true;
	}

	public function toArray()
	{
		return $this->container;
	}
}