import {ajax as Ajax} from 'main.core';
import {Logger} from 'im.v2.lib.logger';

import type {Config} from '../search-config';
import type {SearchItem} from '../search-item';

export class DepartmentSearchService
{
	#searchConfig: Config;

	constructor(searchConfig: Config)
	{
		this.#searchConfig = searchConfig;
	}

	loadUsers(department: SearchItem): Promise
	{
		const parentItem = {
			id: department.getId(),
			entityId: department.getEntityId()
		};

		const config = {
			json: {
				...this.#searchConfig.getDepartmentUsers(),
				parentItem
			}
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.getChildren', config).then(response => {
				Logger.warn('Im.V2.Search: department users response', response);
				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}
}