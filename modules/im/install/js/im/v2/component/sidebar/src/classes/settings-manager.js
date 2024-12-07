import { Core } from 'im.v2.application.core';
import { Feature, FeatureManager } from 'im.v2.lib.feature';

import type { Store } from 'ui.vue3.vuex';

export class SettingsManager
{
	store: Store;

	constructor()
	{
		this.saveSettings();
	}

	async saveSettings()
	{
		await Core.ready();

		const filesMigrated = FeatureManager.isFeatureAvailable(Feature.sidebarFiles);
		const linksAvailable = FeatureManager.isFeatureAvailable(Feature.sidebarLinks);

		void Core.getStore().dispatch('sidebar/setFilesMigrated', filesMigrated);
		void Core.getStore().dispatch('sidebar/setLinksMigrated', linksAvailable);
	}
}
