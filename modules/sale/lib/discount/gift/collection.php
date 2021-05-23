<?php

namespace Bitrix\Sale\Discount\Gift;


use Bitrix\Main\Type\Dictionary;

class Collection extends Dictionary
{
	public const TYPE_GRANT_ALL = 'all';
	public const TYPE_GRANT_ONE = 'one';

	protected $type = self::TYPE_GRANT_ONE;

	/**
	 * GiftCollection constructor.
	 * @param array  $gifts
	 * @param string $type
	 */
	public function __construct(array $gifts, $type)
	{
		$this->type = $type;

		parent::__construct($gifts);
	}

	protected function setGift(Gift $gift, $offset = null)
	{
		parent::offsetSet($offset, $gift);
	}

	public function offsetSet($offset, $value)
	{
		$this->setGift($value, $offset);
	}

	public function getGrantType(): string
	{
		return $this->type;
	}
}