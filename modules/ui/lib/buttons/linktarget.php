<?php

namespace Bitrix\UI\Buttons;

abstract class LinkTarget
{
	public const LINK_TARGET_BLANK = '_blank';
	public const LINK_TARGET_SELF = '_self';
	public const LINK_TARGET_PARENT = '_parent';
	public const LINK_TARGET_TOP = '_top';

	public const LINK_TARGETS = [
		self::LINK_TARGET_BLANK,
		self::LINK_TARGET_SELF,
		self::LINK_TARGET_PARENT,
		self::LINK_TARGET_TOP,
	];
}