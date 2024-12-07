import 'ui.forms';

import { Dropdown, Toggle, ToggleSize } from 'im.v2.component.elements';

import { CreateChatSection } from '../section/section';
import { CreateChatHeading } from '../../elements/heading';

import './conference-section.css';

// @vue/component
export const ConferenceSection = {
	components: { CreateChatSection, CreateChatHeading, Dropdown, Toggle },
	props: {
		passwordNeeded: {
			type: Boolean,
			required: true,
		},
		password: {
			type: String,
			required: true,
		},
	},
	emits: ['passwordNeededChange', 'passwordChange'],
	computed:
	{
		ToggleSize: () => ToggleSize,
	},
	methods:
	{
		onPasswordNeededChange(passwordNeeded: boolean)
		{
			this.$emit('passwordNeededChange', passwordNeeded);
		},
		onPasswordChange(event: Event)
		{
			this.$emit('passwordChange', event.target.value);
		},
		onToggleLabelClick()
		{
			this.onPasswordNeededChange(!this.passwordNeeded);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="conference" :title="loc('IM_CREATE_CHAT_CONFERENCE_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<CreateChatHeading :text="loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PRIVACY')" />
				<div @click="onToggleLabelClick" class="bx-im-chat-forms-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="passwordNeeded" />
					<div class="bx-im-chat-forms-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_USE_PASSWORD') }}
					</div>
				</div>
				<div v-if="passwordNeeded" class="bx-im-chat-forms-chat-settings__password-container ui-ctl ui-ctl-textbox">
					<input
						type="text"
						class="bx-im-content-create-chat-conference__password-input ui-ctl-element"
						:value="password"
						:placeholder="loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PASSWORD_PLACEHOLDER')"
						@input="onPasswordChange"
					/>
				</div>
			</div>
		</CreateChatSection>
	`,
};
