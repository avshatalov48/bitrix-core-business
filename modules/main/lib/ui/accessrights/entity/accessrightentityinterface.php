<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;

interface AccessRightEntityInterface
{
	/**
	 * @param int $id entity id
	 */
	public function __construct(int $id);

	/**
	 * Meta data for entity.
	 *
	 * @return array in format:
	 * ```php
	 * [
	 * 		'type' => ...
	 * 		'id' => ...
	 * 		'name' => ...
	 * 		'url' => ...
	 * 		'avatar' => ...
	 * ]
	 * ```
	 */
	public function getMetaData(): array;

	/**
	 * Entity id.
	 *
	 * @return int
	 */
	public function getId(): int;

	/**
	 * Entity type.
	 *
	 * Returns access code.
	 *
	 * @see Bitrix\Main\Access\AccessCode `TYPE_*` constants.
	 *
	 * @return string
	 */
	public function getType(): string;

	/**
	 * Url to entity detail page.
	 *
	 * @return string
	 */
	public function getUrl(): string;

	/**
	 * Entity name.
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * URL to avatar image.
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @return string|null
	 */
	public function getAvatar(int $width, int $height): ?string;
}
