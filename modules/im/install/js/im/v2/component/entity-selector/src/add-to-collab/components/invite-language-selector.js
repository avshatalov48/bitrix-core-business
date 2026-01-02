import { Languages } from 'intranet.languages';
import { Text } from 'main.core';
import { type Menu, MenuItem, PopupMenu } from 'main.popup';
import { hint } from 'ui.vue3.directives.hint';

import type { PopupOptions } from 'main.popup';
import type { JsonObject } from 'main.core';

const ITEM_CLASS = 'bx-im-add-to-collab__language-selector_item';
const ACCEPTED_ITEM_CLASS = 'menu-popup-item-accept';

// @vue/component
export const InviteLanguageSelector = {
	name: 'InviteLanguageSelector',
	directives: { hint },
	props:
	{
		defaultLanguageCode: {
			type: String,
			required: true,
		},
	},
	emits: ['selectLanguage', 'openLanguageSelector', 'closeLanguageSelector'],
	data(): JsonObject
	{
		return {
			selectedLanguageCode: this.defaultLanguageCode,
			isSelectorShown: false,
		};
	},
	computed:
	{
		selectedLanguageName(): string
		{
			const lang = this.availableLanguages[this.selectedLanguageCode];

			return lang ? lang.NAME : '';
		},
	},
	created(): void
	{
		this.availableLanguages = new Languages().getLanguages();
	},
	methods:
	{
		languageSelectorHint(): { text: string, popupOptions: PopupOptions }
		{
			return {
				text: this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LANGUAGE_SELECTOR_HINT'),
				popupOptions: {
					className: 'im-add-to-collab__invite-language-section_hint',
					width: 270,
					bindOptions: {
						position: 'top',
						forceBindPosition: true,
					},
					angle: {
						offset: 37,
						position: 'bottom',
					},
					offsetTop: -8,
				},
			};
		},
		onLanguageSelected(langCode: string): void
		{
			this.toggleSelector();

			this.selectedLanguageCode = langCode;
			this.$emit('selectLanguage', this.selectedLanguageCode);
		},
		toggleSelector(): void
		{
			if (this.isSelectorShown)
			{
				this.closeSelector();
			}
			else
			{
				this.showSelector();
			}
		},
		showSelector(): void
		{
			this.selector = this.createSelector();
			this.selector.show();

			this.isSelectorShown = true;

			this.$emit('openLanguageSelector');
		},
		closeSelector(): void
		{
			this.selector.close();
			this.isSelectorShown = false;

			this.$emit('closeLanguageSelector');
		},
		createSelector(): Menu
		{
			return PopupMenu.create({
				id: Text.getRandom().toLowerCase(),
				bindElement: this.$refs.inviteLanguageSection,
				className: 'bx-im-messenger__scope bx-im-add-to-collab__invite-language-section_language-selector',
				items: this.getMenuItems(),
				angle: false,
				autoHide: true,
				closeByEsc: true,
				maxHeight: 207,
				contentPadding: 10,
				padding: 10,
				bindOptions: {
					forceBindPosition: true,
				},
				events: {
					onPopupClose: () => {
						this.isSelectorShown = false;
						this.$emit('closeLanguageSelector');
					},
				},
			});
		},
		getMenuItems(): MenuItem[]
		{
			return Object.keys(this.availableLanguages).map((langCode: string): MenuItem => {
				return new MenuItem({
					langCode,
					className: this.getMenuItemClass(langCode),
					text: this.getMenuItemText(langCode),
					onclick: () => this.onLanguageSelected(langCode),
				});
			});
		},
		getMenuItemClass(langCode: string): string
		{
			return (langCode === this.selectedLanguageCode)
				? `${ITEM_CLASS} ${ACCEPTED_ITEM_CLASS}`
				: ITEM_CLASS
			;
		},
		getMenuItemText(langCode: string): string
		{
			const lang = this.availableLanguages[langCode];
			const langName = lang ? lang.NAME : '';

			if (langCode === this.defaultLanguageCode)
			{
				return this.loc(
					'IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DEFAULT_LANGUAGE_TITLE',
					{ '#LANG_NAME#': langName },
				);
			}

			return langName;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<section
			class="bx-im-add-to-collab__invite-language-section"
			v-hint="languageSelectorHint"
			ref="inviteLanguageSection"
		>
			<span class="bx-im-add-to-collab__invite-language-section_title">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_LANGUAGE_TITLE') }}
			</span>
			<div
				class="bx-im-add-to-collab__invite-language-section_selected-language-block"
				@click="toggleSelector"
			>
				<span class="bx-im-add-to-collab__selected-language-block_title">
					{{ selectedLanguageName }}
				</span>
				<span
					class="bx-im-add-to-collab__selected-language-block_arrow"
					:class="{ '--open': isSelectorShown }"
				></span>
			</div>
		</section>
	`,
};
