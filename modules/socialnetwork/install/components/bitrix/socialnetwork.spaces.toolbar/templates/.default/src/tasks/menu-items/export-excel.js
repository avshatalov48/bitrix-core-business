import { Loc } from 'main.core';
import { TasksExcelManager } from '../tasks-excel-manager';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';

export const ExportExcelItem: MenuItem = (view: TasksSettingsMenu, excelManager: TasksExcelManager) => {
	return {
		dataset: { id: `spaces-tasks-${view.getViewId()}-settings-export-excel` },
		text: Loc.getMessage('SN_SPACES_TASKS_EXPORT_LIST_TO_EXCEL'),
		className: 'menu-popup-item-none',
		items: [
			{
				dataset: { id: `spaces-tasks-${view.getViewId()}-settings-export-excel-grid-fields` },
				text: Loc.getMessage('SN_SPACES_TASKS_EXPORT_GRID_FIELDS'),
				className: 'sn-spaces-tasks-icon-excel',
				href: excelManager.getExportHref(),
			},
			{
				dataset: { id: `spaces-tasks-${view.getViewId()}-settings-export-excel-all-fields` },
				text: Loc.getMessage('SN_SPACES_TASKS_EXPORT_ALL_FIELDS'),
				className: 'sn-spaces-tasks-icon-excel',
				href: excelManager.getExportHref({ isAll: true }),
			},
		],
	};
};
