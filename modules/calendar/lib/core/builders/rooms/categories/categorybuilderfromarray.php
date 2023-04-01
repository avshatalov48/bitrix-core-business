<?php

namespace Bitrix\Calendar\Core\Builders\Rooms\Categories;

class CategoryBuilderFromArray extends CategoryBuilder
{
	/**
	 * @var array
	 */
	private array $params;

	/**
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$params = [
			'id' => $params['id'] ?? null,
			'name' => $params['name'] ?? null,
			'rooms' => $params['rooms'] ?? null,
		];
		$this->params = $params;
	}

	protected function getId()
	{
		return $this->params['id'];
	}

	protected function getName()
	{
		return $this->params['name'];
	}

	protected function getRooms()
	{
		return $this->params['rooms'];
	}
}