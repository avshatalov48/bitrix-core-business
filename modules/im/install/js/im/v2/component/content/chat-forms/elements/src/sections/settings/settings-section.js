import 'ui.forms';

import { Dropdown } from 'im.v2.component.elements';

import { CreateChatSection } from '../section/section';
import { TextareaInput } from '../../elements/textarea';
import { RadioOption, type RadioOptionItem } from '../../elements/radio';
import { CreateChatHeading } from '../../elements/heading';

import './settings-section.css';

// @vue/component
export const SettingsSection = {
	components: { CreateChatSection, CreateChatHeading, Dropdown, TextareaInput, RadioOption },
	props: {
		description: {
			type: String,
			required: true,
		},
		withSearchOption: {
			type: Boolean,
			default: true,
		},
		isAvailableInSearch: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['chatTypeChange', 'descriptionChange'],
	computed:
	{
		privacyOptions(): RadioOptionItem[]
		{
			return [
				{
					value: false,
					text: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVATE_TITLE'),
					subtext: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVATE_SUBTITLE'),
					selected: !this.isAvailableInSearch,
				},
				{
					value: true,
					text: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_OPEN_TITLE'),
					subtext: this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_OPEN_SUBTITLE'),
					selected: this.isAvailableInSearch,
				},
			];
		},
	},
	methods:
	{
		onTypeChange(isAvailableInSearch: boolean)
		{
			this.$emit('chatTypeChange', isAvailableInSearch);
		},
		onDescriptionChange(description: string)
		{
			this.$emit('descriptionChange', description);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div v-if="withSearchOption" class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY_MSGVER_1')" />
				<RadioOption :items="privacyOptions" @change="onTypeChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION')" />
				<div class="bx-im-chat-forms-settings__description_container">
					<TextareaInput
						:value="description"
						:placeholder="loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION_PLACEHOLDER_MSGVER_1')"
						:rounded="false"
						@input="onDescriptionChange"
					/>
				</div>
			</div>
		</CreateChatSection>
	`,
};
