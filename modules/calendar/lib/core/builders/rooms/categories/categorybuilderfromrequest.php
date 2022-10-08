<?php

namespace Bitrix\Calendar\Core\Builders\Rooms\Categories;

class CategoryBuilderFromRequest extends CategoryBuilder
{
	/** @var \Bitrix\Main\HttpRequest|\Bitrix\Main\Request */
	private $request;

	/**
	 * @param $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	protected function getId()
	{
		return $this->request->getPost('id');
	}

	protected function getName()
	{
		return $this->request->getPost('name');
	}

	protected function getRooms()
	{
		return $this->request->getPost('rooms');
	}
}