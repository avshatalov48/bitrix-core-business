<?php

namespace Bitrix\Seo\LeadAds;


use JsonSerializable;

class LeadAdsForm implements JsonSerializable
{
	/** @var null|int|string $id*/
	protected $id;

	/**@var null|string  $name*/
	protected $name;

	/**@var null|string $description*/
	protected $description;

	/**@var null|string $title*/
	protected $title;

	/** @var Field[] */
	protected $fields;

	/** @var null|string $successMessage*/
	protected $successMessage;

	/** @var null|string $link*/
	protected $link;

	/**@var bool*/
	protected $active = true;

	/**
	 * LeadAdsForm constructor.
	 *
	 * @param array{
	 * 		id?: int|string|null,
	 * 		name?: string|null,
	 * 		description?: string|null,
	 * 		title?: string|null,
	 * 		fields?: Field[],
	 * 		link?: string|null,
	 * 		message?: string|null
	 * 	} $parameters
	 */
	public function __construct(array $parameters = [])
	{
		if (array_key_exists('id',$parameters))
		{
			$this->id = $parameters['id'];
		}
		if (array_key_exists('name',$parameters) && is_string($parameters['name']))
		{
			$this->name = $parameters['name'];
		}
		if (array_key_exists('description',$parameters) && is_string($parameters['description']))
		{
			$this->description = $parameters['description'];
		}
		if (array_key_exists('title',$parameters) && is_string($parameters['title']))
		{
			$this->title = $parameters['title'];
		}
		if (array_key_exists('fields',$parameters) && is_array($parameters['fields']))
		{
			$this->fields = array_filter(
				$parameters['fields'],
				static function($object)
				{
					return $object instanceof Field && $object->getKey();
				}
			);
		}
		if (array_key_exists('message',$parameters) && is_string($parameters['message']))
		{
			$this->successMessage = $parameters['message'];
		}
		if (array_key_exists('link',$parameters) && is_string($parameters['link']))
		{
			$this->link = $parameters['link'];
		}
		if (array_key_exists('active', $parameters) && is_scalar($parameters['active']))
		{
			$this->active = (bool) $parameters['active'];
		}
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return null|string
	 */
	public function getName() : ?string
	{
		return $this->name;
	}

	/**
	 * @return null|string
	 */
	public function getDescription() : ?string
	{
		return $this->description;
	}

	/**
	 * @return null|string
	 */
	public function getTitle() : ?string
	{
		return $this->title;
	}

	/**
	 * @return Field[]|null
	 */
	public function getFields() : ?array
	{
		return $this->fields;
	}

	/**
	 * @return null|string
	 */
	public function getSuccessMessage() : ?string
	{
		return $this->successMessage;
	}

	/**
	 * @return null|string
	 */
	public function getLink() : ?string
	{
		return $this->link;
	}

	/**
	 * @return bool
	 */
	public function isActive() : bool
	{
		return $this->active;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'id' => $this->id,
			'name' => $this->name,
			'title' => $this->title,
			'description' => $this->description,
			'message' => $this->successMessage,
			'link' => $this->link,
			'active' => $this->active,
			'fields' => array_map(
				static function(Field $element) : array
				{
					return $element->toArray();
				},
				$this->fields
			)
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}