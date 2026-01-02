<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter;

enum FilterType: string
{
	case Prefilter = 'prefilters';
	case Postfilter = 'postfilters';

	case EnablePrefilter = '+prefilters';
	case DisablePrefilter = '-prefilters';

	case EnablePostfilter = '+postfilters';
	case DisablePostfilter = '-postfilters';

	public function isNegative(): bool
	{
		return in_array($this, [self::DisablePrefilter, self::DisablePostfilter], true);
	}
}