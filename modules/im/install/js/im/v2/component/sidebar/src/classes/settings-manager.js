import { Extension } from 'main.core';

import { Core } from 'im.v2.application.core';

import type { Store } from 'ui.vue3.vuex';

export class SettingsManager
{
	store: Store;
	settings: Object;

	constructor()
	{
		this.settings = Extension.getSettings('im.v2.component.sidebar');
		this.saveSettings();
	}

	async saveSettings()
	{
		await Core.ready();
		void Core.getStore().dispatch('sidebar/setFilesMigrated', this.settings.get('filesMigrated', false));
		void Core.getStore().dispatch('sidebar/setLinksMigrated', this.settings.get('linksAvailable', false));
	}

	canShowBriefs(): boolean
	{
		return this.settings.get('canShowBriefs', false);
	}

	isLinksMigrationFinished(): boolean
	{
		return Core.getStore().state.sidebar.isLinksMigrated;
	}

	isFileMigrationFinished(): boolean
	{
		return Core.getStore().state.sidebar.isFilesMigrated;
	}
}
