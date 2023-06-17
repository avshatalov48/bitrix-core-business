import 'ui.forms';

import {Dropdown, Toggle, ToggleSize} from 'im.v2.component.elements';

import {Section} from '../section';
import {OwnerSelector} from './owner';

const MANAGE_TYPES = [
	{
		value: 'ALL',
		text: 'All members',
		default: true
	},
	{
		value: 'OWNERS',
		text: 'Owners only'
	},
	{
		value: 'ADMINS_AND_OWNERS',
		text: 'Admins and owners'
	},
];

// @vue/component
export const SettingsSection = {
	components: {Section, Dropdown, Toggle, OwnerSelector},
	props: {
		isAvailableInSearch: {
			type: Boolean,
			required: true
		},
		ownerId: {
			type: Number,
			required: true
		},
		description: {
			type: String,
			required: true
		}
	},
	emits: ['ownerChange', 'manageTypeChange', 'chatTypeChange', 'descriptionChange'],
	data()
	{
		return {
			manageType: ''
		};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		MANAGE_TYPES: () => MANAGE_TYPES,
		descriptionPlaceholderText(): string
		{
			return this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION_PLACEHOLDER', {
				'#BR#': '\n',
				'#QUOTE_START#': '"',
				'#QUOTE_END#': '"'
			});
		}
	},
	methods:
	{
		onManageTypeChange(value: string)
		{
			this.$emit('manageTypeChange', value);
		},
		onOwnerChange(ownerId: number)
		{
			this.$emit('ownerChange', ownerId);
		},
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
			this.$refs['toggle'].toggle();
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template: `
		<Section name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER') }}
				</div>
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
				<!--<div class="bx-im-content-create-chat__heading">Who can manage the participants</div>-->
				<!--<div class="bx-im-content-create-chat-settings__manage-select">-->
				<!--<Dropdown :items="MANAGE_TYPES" id="im-content-create-chat-manage-menu" @itemChange="onManageTypeChange" />-->
				<!--</div>-->
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.S" :isEnabled="isAvailableInSearch" @change="onTypeChange" ref="toggle" />
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
							placeholder="1&#10;2&#10;3&#10;4&#10;5"
							class="bx-im-content-create-chat-settings__description ui-ctl-element"
						></textarea>
					</div>
				</div>
			</div>
		</Section>
	`
};