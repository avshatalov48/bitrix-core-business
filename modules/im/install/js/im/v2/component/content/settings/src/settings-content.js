import { Logger } from 'im.v2.lib.logger';
import { Settings } from 'im.v2.const';

import { SectionList } from './components/section-list';
import { SectionContent } from './components/section-content';

import './css/settings-content.css';

// @vue/component
export const SettingsContent = {
	name: 'SettingsContent',
	components: { SectionList, SectionContent },
	data()
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
		setInitialSection()
		{
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
