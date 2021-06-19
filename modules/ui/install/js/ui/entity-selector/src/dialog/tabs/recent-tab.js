import { Loc } from 'main.core';
import Tab from './tab';
import type { TabOptions } from './tab-options';
import type Dialog from '../dialog';

export default class RecentTab extends Tab
{
	constructor(dialog: Dialog, tabOptions: TabOptions)
	{
		const icon =
			'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20fill%3D%' +
			'22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M14.432%2013.985a.96.' +
			'96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM14.432%2011.' +
			'009a.96.96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM14.' +
			'432%208.033a.96.96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.' +
			'96z%22%20fill%3D%22%23ABB1B8%22/%3E%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd' +
			'%22%20d%3D%22M10.988%2019.52c1.8%200%203.469-.558%204.844-1.51l2.205%202.204a1.525%201.525%200%20' +
			'102.157-2.157l-2.205-2.205a8.512%208.512%200%2010-7%203.668zm0-2.403a6.108%206.108%200%20100-12.2' +
			'16%206.108%206.108%200%20000%2012.216z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E'
		;

		const defaults = {
			title: Loc.getMessage('UI_SELECTOR_RECENT_TAB_TITLE'),
			itemOrder: { sort: 'asc' },
			visible: !dialog.isDropdownMode(),
			stub: !dialog.isDropdownMode(),
			icon: {
				//default: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-icon.svg',
				//selected: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-icon-selected.svg'
				default: icon,
				selected: icon.replace(/ABB1B8/g, 'fff'),
			}
		};

		const options: TabOptions = Object.assign({}, defaults, tabOptions);
		options.id = 'recents';

		super(dialog, options);
	}
}