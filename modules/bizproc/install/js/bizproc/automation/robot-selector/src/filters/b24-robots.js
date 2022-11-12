import { Loc } from 'main.core';
import { Filter } from './filter';

export class B24Robots extends Filter
{
	getId(): string
	{
		return 'bitrix24_robots';
	}

	getText(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_FILTER_BITRIX_24_ROBOTS');
	}

	getAction(): Function
	{
		return (item) => {
			return item.customData.type === 'robot' && item.customData.owner === 'bitrix24';
		};
	}
}