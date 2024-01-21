import { AppearanceSection } from './sections/appearance';
import { NotificationSection } from './sections/notification';
import { HotkeySection } from './sections/hotkey';
import { RecentSection } from './sections/recent';
import { DesktopSection } from './sections/desktop';

import { SectionMetaData } from '../sections';

import '../css/section-content.css';

// @vue/component
export const SectionContent = {
	name: 'SectionContent',
	components: { AppearanceSection, NotificationSection, HotkeySection, RecentSection, DesktopSection },
	props:
	{
		activeSection: {
			type: String,
			required: true,
		},
	},
	data(): Object<string, any>
	{
		return {};
	},
	computed:
	{
		sectionComponentName(): string
		{
			const uppercaseSection = this.activeSection[0].toUpperCase() + this.activeSection.slice(1);
			const COMPONENT_POSTFIX = 'Section';

			return `${uppercaseSection}${COMPONENT_POSTFIX}`;
		},
		sectionName(): string
		{
			return SectionMetaData[this.activeSection].name;
		},
		sectionIconClass(): string
		{
			return SectionMetaData[this.activeSection].icon;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-settings-section-content__container">
			<div class="bx-im-settings-section-content__header">
				<div class="bx-im-settings-section-content__header_icon">
					<i :class="sectionIconClass"></i>
				</div>
				<div class="bx-im-settings-section-content__header_title">{{ sectionName }}</div>
			</div>
			<div class="bx-im-settings-section-content__background">
				<component :is="sectionComponentName" />
			</div>
		</div>
	`,
};
