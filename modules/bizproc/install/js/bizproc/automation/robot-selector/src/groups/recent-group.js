import { Group } from './group';
import type { GroupData } from 'ui.entity-catalog';

export class RecentGroup extends Group
{
	getId(): string
	{
		return 'recent';
	}

	getName(): string
	{
		return '';
	}

	getData(): GroupData
	{
		return {
			selected: this.getSelected(),
			compare: this.getCompare(),
		};
	}
}