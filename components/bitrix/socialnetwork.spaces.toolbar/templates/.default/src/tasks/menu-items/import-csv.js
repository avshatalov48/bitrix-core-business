import { Loc } from 'main.core';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';
import { TasksExcelManager } from '../tasks-excel-manager';

export const ImportCsvItem: MenuItem = (view: TasksSettingsMenu, excelManager: TasksExcelManager) => {
	return {
		dataset: { id: `spaces-tasks-${view.getViewId()}-settings-import` },
		text: Loc.getMessage('SN_SPACES_TASKS_IMPORT_LIST'),
		className: 'menu-popup-item-none',
		items: [
			{
				dataset: { id: `spaces-tasks-${view.getViewId()}-settings-import-csv` },
				text: Loc.getMessage('SN_SPACES_TASKS_IMPORT_LIST_CSV'),
				className: 'sn-spaces-tasks-icon-excel',
				href: excelManager.getImportHref(),
				onclick: () => view.close(),
			},
		],
	};
};
