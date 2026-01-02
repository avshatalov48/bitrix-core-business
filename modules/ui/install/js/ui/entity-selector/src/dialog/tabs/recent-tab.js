import { Loc } from 'main.core';
import Tab from './tab';
import type { TabOptions } from './tab-options';
import type Dialog from '../dialog';
import { Outline } from 'ui.icon-set.api.core';

export default class RecentTab extends Tab
{
	constructor(dialog: Dialog, tabOptions: TabOptions)
	{
		const defaults = {
			title: Loc.getMessage('UI_SELECTOR_RECENT_TAB_TITLE'),
			itemOrder: { sort: 'asc' },
			visible: !dialog.isDropdownMode(),
			stub: !dialog.isDropdownMode(),
			icon: Outline.SEARCH,
		};

		const options: TabOptions = Object.assign({}, defaults, tabOptions);
		options.id = 'recents';

		super(dialog, options);
	}
}
