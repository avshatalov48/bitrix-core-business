import { RecentList } from 'im.v2.component.list.items.recent-compact';
import { Logger } from 'im.v2.lib.logger';
import { InitManager } from 'im.v2.lib.init';

export const QuickAccess = {
	name: 'QuickAccess',
	components: { RecentList },
	created()
	{
		InitManager.start();
		Logger.warn('Quick access created');
	},
	template: `
		<RecentList />
	`,
};
