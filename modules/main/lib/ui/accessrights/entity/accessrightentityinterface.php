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

	public function __construct(int $id);
	public function getMetaData(): array;
	public function getId(): int;
	public function getType(): string;
	public function getUrl(): string;
	public function getName(): string;
	public function getAvatar(int $width, int $height): ?string;

}