import { Loc } from 'main.core';
import { TasksExcelManager } from '../tasks-excel-manager';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';

export const ExportItem: MenuItem = (view: TasksSettingsMenu, excelManager: TasksExcelManager) => {
	return {
		dataset: { id: `spaces-tasks-${view.getViewId()}-settings-export-list` },
		text: Loc.getMessage('SN_SPACES_TASKS_EXPORT_LIST'),
		className: 'menu-popup-item-none',
		items: [
			{
				dataset: { id: `spaces-tasks-${view.getViewId()}-settings-to-excel` },
				text: Loc.getMessage('SN_SPACES_TASKS_EXPORT_TO_EXCEL'),
				className: 'sn-spaces-tasks-icon-excel',
				href: excelManager.getExportHref(),
			},
		],
	};
};
