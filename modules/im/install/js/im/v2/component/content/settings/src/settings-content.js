import { Logger } from 'im.v2.lib.logger';
import { Settings, SettingsSection } from 'im.v2.const';

import { SectionList } from './components/section-list';
import { SectionContent } from './components/section-content';

import './css/settings-content.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const SettingsContent = {
	name: 'SettingsContent',
	components: { SectionList, SectionContent },
	props:
	{
		entityId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			activeSection: '',
		};
	},
	computed:
	{
		sections(): string[]
		{
			return Object.keys(Settings);
		},
	},
	created()
	{
		Logger.warn('Content: Openlines created');
		this.setInitialSection();
	},
	methods:
	{
		setInitialSection(): void
		{
			if (this.entityId && SettingsSection[this.entityId])
			{
				this.activeSection = this.entityId;

				return;
			}

			this.activeSection = this.sections[0];
		},
		onSectionClick(sectionId: string)
		{
			this.activeSection = sectionId;
		},
	},
	template: `
		<div class="bx-im-content-settings__container">
			<SectionList :activeSection="activeSection" @sectionClick="onSectionClick" />
			<SectionContent :activeSection="activeSection" />
		</div>
	`,
};
