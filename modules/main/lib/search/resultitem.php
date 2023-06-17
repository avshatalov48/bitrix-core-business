<?php

namespace Bitrix\Main\Search;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Web\Uri;

final class ResultItem implements \JsonSerializable, \ArrayAccess
{
	/** @var mixed */
	private $id;
	/** @var string */
	private $type;
	/** @var string */
	private $title;
	/** @var Uri */
	private $showLink;
	/** @var string */
	private $module;
	/** @var string */
	private $subTitle;
	/** @var array */
	private $actions = [];
	/** @var Uri[] */
	private $links = [];
	/** @var array */
	private $attributes = [];

	/**
	 * ResultItem constructor.
	 *
	 * @param $title
	 * @param Uri|string $showLink
	 * @param null $id
	 */
	public function __construct($title, $showLink, $id = null)
	{
		$this
			->setTitle($title)
			->setShowLink($showLink)
			->setId($id)
		;
	}

	/**
	 * Returns id.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets id.
	 * @param mixed $id
	 *
	 * @return ResultItem
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Returns type of element. For example: lead, deal, file, folder, etc.
	 * Type is unnecessary.
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets type of element. For example: lead, deal, file, folder, etc.
	 * Type is unnecessary.
	 * @param string $type
	 *
	 * @return ResultItem
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Returns title of searched item.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets title of searched item.
	 * @param string $title Title.
	 *
	 * @return ResultItem
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Returns link to show searched item.
	 *
	 * @return Uri
	 */
	public function getShowLink()
	{
		return $this->showLink;
	}

	/**
	 * Sets link to show searched item.
	 * @param Uri $showLink Show link.
	 *
	 * @return ResultItem
	 */
	public function setShowLink($showLink)
	{
		$this->showLink = $this->adjustLink($showLink);
		$this->addLink('show', $this->showLink);

		return $this;
	}

	/**
	 * Adjusts link.
	 * @param Uri|string $link Link.
	 *
	 * @return Uri
	 */
	protected function adjustLink($link)
	{
		if ($link instanceof Uri)
		{
			return $link;
		}

		return new Uri($link);
	}

	/**
	 * Returns module which provides searched item.
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Sets module which provides searched item.
	 * Module is unnecessary and will generate automatically.
	 * @param string $module Module id.
	 *
	 * @return ResultItem
	 */
	public function setModule($module)
	{
		$this->module = $module;

		return $this;
	}

	/**
	 * Returns subtitle of searched item.
	 * Subtitle is is unnecessary.
	 *
	 * @return string
	 */
	public function getSubTitle()
	{
		return $this->subTitle;
	}

	/**
	 * Sets subtitle of searched item.
	 * Subtitle is is unnecessary.
	 * @param string $subTitle Subtitle.
	 *
	 * @return ResultItem
	 */
	public function setSubTitle($subTitle)
	{
		$this->subTitle = $subTitle;

		return $this;
	}

	/**
	 * Returns actions.
	 * It's reserved field and unused.
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * Returns links on searched item.
	 *
	 * @return Uri[]
	 */
	public function getLinks()
	{
		return $this->links;
	}

	/**
	 * Sets links on searched item.
	 * Should be associative array.
	 * @param Uri[]|string[] $links Links.
	 *
	 * @return ResultItem
	 */
	public function setLinks(array $links)
	{
		$adjustedLinks = [];
		foreach ($links as $key => $link)
		{
			$adjustedLinks[$key] = $this->adjustLink($link);
		}

		$this->links = $adjustedLinks;

		return $this;
	}

	/**
	 * Adds link on searched item.
	 * @param string $name Name of link.
	 * @param Uri|string $link Link.
	 *
	 * @return $this
	 */
	public function addLink($name, $link)
	{
		$this->links[$name] = $this->adjustLink($link);

		return $this;
	}

	/**
	 * Returns attributes.
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Sets attributes.
	 * @param array $attributes
	 *
	 * @return ResultItem
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * Sets value for attribute with name.
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Deletes attribute by name.
	 * @param string $name
	 *
	 * @return $this
	 */
	public function unsetAttribute($name)
	{
		unset($this->attributes[$name]);

		return $this;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'type' => $this->getType(),
			'title' => $this->getTitle(),
			'module' => $this->getModule(),
			'subTitle' => $this->getSubTitle(),
			'actions' => $this->getActions(),
			'links' => $this->getLinks(),
			'attributes' => $this->getAttributes(),
		];
	}

	/**
	 * Whether a offset exists
	 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset): bool
	{
		$data = $this->jsonSerialize();

		return isset($data[$offset]) || array_key_exists($offset, $data);
	}

	/**
	 * Offset to retrieve
	 * @link https://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		$data = $this->jsonSerialize();

		if (isset($data[$offset]) || array_key_exists($offset, $data))
		{
			return $data[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 * @link https://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value): void
	{
		throw new NotSupportedException('ResultItem provides ArrayAccess only for reading');
	}

	/**
	 * Offset to unset
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset): void
	{
		throw new NotSupportedException('ResultItem provides ArrayAccess only for reading');
	}
}