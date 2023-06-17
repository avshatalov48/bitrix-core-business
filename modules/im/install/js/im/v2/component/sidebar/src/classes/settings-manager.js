import {Extension} from 'main.core';

import {Core} from 'im.v2.application.core';

export class SettingsManager
{
	store: Object = null;
	settings: Object = null;

	constructor()
	{
		this.store = Core.getStore();
		this.settings = Extension.getSettings('im.v2.component.sidebar');
		this.saveSettings();
	}

	saveSettings()
	{
		this.store.dispatch('sidebar/setFilesMigrated', this.settings.get('filesMigrated', false));
		this.store.dispatch('sidebar/setLinksMigrated', this.settings.get('linksAvailable', false));
	}

	getBlocks(): string[]
	{
		return this.settings.get('blocks', []);
	}

	isLinksMigrationFinished(): boolean
	{
		return this.store.state.sidebar.isLinksMigrated;
	}

	canShowBriefs(): boolean
	{
		return this.settings.get('canShowBriefs', false);
	}

	isFileMigrationFinished(): boolean
	{
		return this.store.state.sidebar.isFilesMigrated;
	}
}