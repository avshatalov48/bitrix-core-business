import { SettingsSection } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { SectionMetaData } from '../sections';

import 'ui.feedback.form';
import '../css/section-list.css';

const AdditionalSections = {
	help: 'help',
	feedback: 'feedback',
};

// @vue/component
export const SectionList = {
	name: 'SectionList',
	props:
	{
		activeSection: {
			type: String,
			required: true,
		},
	},
	emits: ['sectionClick'],
	data(): {}
	{
		return {};
	},
	computed:
	{
		AdditionalSections: () => AdditionalSections,
		disabledSections(): Set<string>
		{
			const disabledSections = new Set([SettingsSection.message]);
			if (!DesktopApi.isDesktop())
			{
				disabledSections.add(SettingsSection.desktop);
			}

			return disabledSections;
		},
		sections(): string[]
		{
			return Object.keys(SettingsSection).filter((section) => {
				return !this.disabledSections.has(section);
			});
		},
	},
	methods:
	{
		getSectionName(section: string): string
		{
			return SectionMetaData[section].name;
		},
		getSectionIconClass(section: string): string
		{
			return SectionMetaData[section].icon;
		},
		onHelpClick()
		{
			const ARTICLE_CODE = '17373696';
			BX.Helper?.show(`redirect=detail&code=${ARTICLE_CODE}`);
		},
		onFeedbackClick()
		{
			BX.UI.Feedback.Form.open({
				id: 'im-v2-feedback',
				forms: [
					{ zones: ['ru'], id: 550, sec: '50my2x', lang: 'ru' },
					{ zones: ['en'], id: 560, sec: '621lbr', lang: 'ru' },
				],
				presets: {
					sender_page: 'profile',
				},
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-settings-section-list__container">
			<div class="bx-im-settings-section-list__title">
				{{ loc('IM_CONTENT_SETTINGS_SECTION_LIST_TITLE') }}
			</div>
			<div
				v-for="section in sections"
				:key="section"
				class="bx-im-settings-section-list__item"
				:class="{'--active': section === activeSection}"
				@click="$emit('sectionClick', section)"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(section)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(section) }}</div>
			</div>
			<!-- Help -->
			<div
				class="bx-im-settings-section-list__item"
				@click="onHelpClick"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(AdditionalSections.help)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(AdditionalSections.help) }}</div>
			</div>
			<!-- Feedback -->
			<div
				class="bx-im-settings-section-list__item"
				@click="onFeedbackClick"
			>
				<div class="bx-im-settings-section-list__item_icon">
					<i :class="getSectionIconClass(AdditionalSections.feedback)"></i>
				</div>
				<div class="bx-im-settings-section-list__item_title">{{ getSectionName(AdditionalSections.feedback) }}</div>
			</div>
		</div>
	`,
};
