<?php

namespace Bitrix\Seo\Analytics\Internals;

class Page
{
	protected $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->prepareData($data);
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->data['id'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data['name'];
	}

	/**
	 * @return string
	 */
	public function getAbout()
	{
		return $this->data['about'];
	}

	/**
	 * @return string
	 */
	public function getImage()
	{
		return $this->data['image'];
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->data['phone'];
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->data['email'];
	}

	/**
	 * @param array $data
	 */
	protected function prepareData(array $data)
	{
		$this->data = [
			'id' => 0,
			'name' => null,
			'about' => null,
			'image' => null,
			'phone' => null,
			'email' => null,
		];

		$this->data = array_merge($this->data, $data);
	}
}