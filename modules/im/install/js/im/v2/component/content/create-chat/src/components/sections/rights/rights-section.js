import { Dropdown } from 'im.v2.component.elements';
import { UserRole, PopupType } from 'im.v2.const';

import { CreateChatSection } from '../section';
import { OwnerSelector } from './owner';
import { ManagersSelector } from './managers';
import { rightsDropdownItems } from './dropdown-items';

import type { DropdownItem } from 'im.v2.component.elements';

type UserRoleItem = $Keys<typeof UserRole>;

// @vue/component
export const RightsSection = {
	components: { CreateChatSection, Dropdown, OwnerSelector, ManagersSelector },
	props: {
		ownerId: {
			type: Number,
			required: true,
		},
		managerIds: {
			type: Array,
			required: true,
		},
		manageUsersAdd: {
			type: String,
			required: true,
		},
		manageUsersDelete: {
			type: String,
			required: true,
		},
		manageSettings: {
			type: String,
			required: true,
		},
		manageUi: {
			type: String,
			required: true,
		},
		canPost: {
			type: String,
			required: true,
		},
	},
	emits: ['ownerChange', 'managersChange', 'manageUsersAddChange', 'manageUsersDeleteChange', 'manageUiChange', 'canPostChange'],
	data()
	{
		return {};
	},
	computed:
	{
		PopupType: () => PopupType,
		manageUsersAddItems(): DropdownItem[]
		{
			return rightsDropdownItems.map((item) => {
				if (item.value === this.manageUsersAdd)
				{
					return {
						...item,
						default: true,
					};
				}

				return { ...item };
			});
		},
		manageUsersDeleteItems(): DropdownItem[]
		{
			return rightsDropdownItems.map((item) => {
				if (item.value === this.manageUsersDelete)
				{
					return {
						...item,
						default: true,
					};
				}

				return { ...item };
			});
		},
		manageUiItems(): DropdownItem[]
		{
			return rightsDropdownItems.map((item) => {
				if (item.value === this.manageUi)
				{
					return {
						...item,
						default: true,
					};
				}

				return { ...item };
			});
		},
		canPostItems(): DropdownItem[]
		{
			return rightsDropdownItems.map((item) => {
				if (item.value === this.canPost)
				{
					return {
						...item,
						default: true,
					};
				}

				return { ...item };
			});
		},
	},
	methods:
	{
		onOwnerChange(ownerId: number)
		{
			this.$emit('ownerChange', ownerId);
		},
		onManagersChange(managerIds: number[])
		{
			this.$emit('managersChange', managerIds);
		},
		onManageUsersAddChange(newValue: UserRoleItem)
		{
			this.$emit('manageUsersAddChange', newValue);
		},
		onManageUsersDeleteChange(newValue: UserRoleItem)
		{
			this.$emit('manageUsersDeleteChange', newValue);
		},
		onManageUiChange(newValue: UserRoleItem)
		{
			this.$emit('manageUiChange', newValue);
		},
		onCanPostChange(newValue: UserRoleItem)
		{
			this.$emit('canPostChange', newValue);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="rights" :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER') }}
				</div>
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGERS') }}
				</div>
				<ManagersSelector :managerIds="managerIds" @managersChange="onManagersChange" />
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_ADD') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="manageUsersAddItems" :id="PopupType.createChatManageUsersAddMenu" @itemChange="onManageUsersAddChange" />
				</div>
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_DELETE') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="manageUsersDeleteItems" :id="PopupType.createChatManageUsersDeleteMenu" @itemChange="onManageUsersDeleteChange" />
				</div>
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_UI_MSGVER_2') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="manageUiItems" :id="PopupType.createChatManageUiMenu" @itemChange="onManageUiChange" />
				</div>
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_SENDING_MSGVER_1') }}
				</div>
				<div class="bx-im-content-create-chat-settings__manage-select">
					<Dropdown :items="canPostItems" :id="PopupType.createChatCanPostMenu" @itemChange="onCanPostChange" />
				</div>
			</div>
		</CreateChatSection>
	`,
};