<?php
namespace Bitrix\Landing\Chat;

use \Bitrix\Landing\Block\Cache;

class Binding extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Block binding type.
	 */
	const BINDING_TYPE_BLOCK = 'B';

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'ChatBindingTable';

	/**
	 * Clears all cache by chat id.
	 * @param int $chatId Internal chat id.
	 * @return void
	 */
	public static function clearCache(int $chatId): void
	{
		$res = self::getList([
			'select' => [
				'ENTITY_ID'
			],
			'filter' => [
				'INTERNAL_CHAT_ID' => $chatId,
				'=ENTITY_TYPE' => self::BINDING_TYPE_BLOCK
			]
		]);
		while ($row = $res->fetch())
		{
			Cache::clear($row['ENTITY_ID']);
		}
	}

	/**
	 * Binds block with chat.
	 * @param int $chatId Internal chat id.
	 * @param int $blockId Block id.
	 * @return void
	 */
	public static function bindingBlock(int $chatId, int $blockId): void
	{
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'INTERNAL_CHAT_ID' => $chatId,
				'ENTITY_ID' => $blockId,
				'=ENTITY_TYPE' => self::BINDING_TYPE_BLOCK
			]
		]);
		if (!$res->fetch())
		{
			self::add([
				'INTERNAL_CHAT_ID' => $chatId,
				'ENTITY_ID' => $blockId,
				'ENTITY_TYPE' => self::BINDING_TYPE_BLOCK
			]);
		}
	}

	/**
	 * Unbinds block from chat.
	 * @param int $blockId Block id.
	 * @return void
	 */
	public static function unbindingBlock(int $blockId): void
	{
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ENTITY_ID' => $blockId,
				'=ENTITY_TYPE' => self::BINDING_TYPE_BLOCK
			]
		]);
		while ($row = $res->fetch())
		{
			self::delete($row['ID']);
		}
	}
}