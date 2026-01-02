<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Type;

enum NodeType: string
{
	case Text = 'text';
	case Link = 'link';
	case Img = 'img';
	case Icon = 'icon';
}
