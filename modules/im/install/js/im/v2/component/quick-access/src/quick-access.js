import {RecentList} from 'im.v2.component.list.element-list.recent';
import {Logger} from 'im.v2.lib.logger';
import {InitManager} from 'im.v2.lib.init';

export const QuickAccess = {
	name: 'QuickAccess',
	props: {
		compactMode: {
			type: Boolean,
			default: false
		}
	},
	components: {RecentList},
	created()
	{
		InitManager.start();
		Logger.warn('Quick access created');
	},
	template: `
		<RecentList :compactMode="compactMode" />
	`
};