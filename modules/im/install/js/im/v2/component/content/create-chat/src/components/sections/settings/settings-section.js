import 'ui.forms';

import { Dropdown, Toggle, ToggleSize } from 'im.v2.component.elements';

import { CreateChatSection } from '../section';

// @vue/component
export const SettingsSection = {
	components: { CreateChatSection, Dropdown, Toggle },
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
	data()
	{
		return {};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		descriptionPlaceholderText(): string
		{
			return this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION_PLACEHOLDER', {
				'#BR#': '\n',
				'#QUOTE_START#': '"',
				'#QUOTE_END#': '"',
			});
		},
	},
	methods:
	{
		onTypeChange(isAvailableInSearch: boolean)
		{
			this.$emit('chatTypeChange', isAvailableInSearch);
		},
		onDescriptionChange(event: Event)
		{
			this.$emit('descriptionChange', event.target.value);
		},
		onToggleLabelClick()
		{
			this.$refs.toggle.toggle();
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div v-if="withSearchOption" class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="isAvailableInSearch" @change="onTypeChange" ref="toggle" />
					<div @click="onToggleLabelClick" class="bx-im-content-create-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_AVAILABLE_FOR_SEARCH') }}
					</div>
				</div>	
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION') }}</div>
				<div class="bx-im-content-create-chat-settings__description_container">
					<div class="ui-ctl ui-ctl-textarea ui-ctl-w100 ui-ctl-no-resize">
						<textarea
							@input="onDescriptionChange"
							:value="description"
							:placeholder="descriptionPlaceholderText"
							class="bx-im-content-create-chat-settings__description ui-ctl-element"
						></textarea>
					</div>
				</div>
			</div>
		</CreateChatSection>
	`,
};
