import 'ui.forms';

import { Dropdown, Toggle, ToggleSize } from 'im.v2.component.elements';

import { CreateChatSection } from '../section';

import type { JsonObject } from 'main.core';

// @vue/component
export const ConferenceSection = {
	components: { CreateChatSection, Dropdown, Toggle },
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
	data(): JsonObject
	{
		return {};
	},
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
			this.$refs.toggle.toggle();
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="conference" :title="loc('IM_CREATE_CHAT_CONFERENCE_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.M" :isEnabled="passwordNeeded" @change="onPasswordNeededChange" ref="toggle" />
					<div @click="onToggleLabelClick" class="bx-im-content-create-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_CONFERENCE_SECTION_USE_PASSWORD') }}
					</div>
				</div>
				<div v-if="passwordNeeded" class="bx-im-content-create-chat-conference__password-container ui-ctl ui-ctl-textbox">
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
