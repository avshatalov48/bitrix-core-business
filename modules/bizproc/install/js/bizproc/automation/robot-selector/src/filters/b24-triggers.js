import { Loc } from 'main.core';
import { Filter } from './filter';

export class B24Triggers extends Filter
{
	getId(): string
	{
		return 'bitrix24_triggers';
	}

	getText(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_FILTER_BITRIX_24_TRIGGERS');
	}

	getAction(): Function
	{
		return (item) => {
			return item.customData.type === 'trigger' && item.customData.owner === 'bitrix24';
		};
	}
}