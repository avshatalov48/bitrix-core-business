import { UserRole, PopupType, ChatType, ChatActionGroup } from 'im.v2.const';

import { CreateChatSection } from '../section/section';
import { RoleSelector } from './components/role-selector';
import { UserSelector } from './components/user-selector/user-selector';
import { OwnerSelector } from './components/user-selector/owner';
import { ManagersSelector } from './components/user-selector/managers';
import { rightsDropdownItems } from './const/dropdown-items';
import {
	BlocksByChatType,
	CanAddUsersCaptionByChatType,
	CanSendMessageCaptionByChatType,
	CanKickUsersCaptionByChatType,
	OwnerHintByChatType,
	ManagerHintByChatType,
	AddUsersHintByChatType,
	DeleteUsersHintByChatType,
	ManageUiHintByChatType,
	SendMessagesHintByChatType,
} from './const/config';

import type { DropdownItem } from 'im.v2.component.elements';

type UserRoleItem = $Keys<typeof UserRole>;

// @vue/component
export const RightsSection = {
	name: 'RightsSection',
	components: { CreateChatSection, RoleSelector, UserSelector, OwnerSelector, ManagersSelector },
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
		manageUi: {
			type: String,
			required: true,
		},
		manageMessages: {
			type: String,
			required: true,
		},
		chatType: {
			type: String,
			default: ChatType.chat,
		},
	},
	emits: ['ownerChange', 'managersChange', 'manageUsersAddChange', 'manageUsersDeleteChange', 'manageUiChange', 'manageMessagesChange'],
	computed:
	{
		PopupType: () => PopupType,
		manageUsersAddItems(): DropdownItem[]
		{
			return this.prepareDropdownItems(this.manageUsersAdd);
		},
		manageUsersDeleteItems(): DropdownItem[]
		{
			return this.prepareDropdownItems(this.manageUsersDelete);
		},
		manageUiItems(): DropdownItem[]
		{
			return this.prepareDropdownItems(this.manageUi);
		},
		manageMessagesItems(): DropdownItem[]
		{
			return this.prepareDropdownItems(this.manageMessages);
		},
		showManageUiBlock(): boolean
		{
			const blocksByType = BlocksByChatType[this.chatType] ?? BlocksByChatType.default;

			return blocksByType.has(ChatActionGroup.manageUi);
		},
		canAddUsersCaption(): string
		{
			return CanAddUsersCaptionByChatType[this.chatType] ?? CanAddUsersCaptionByChatType.default;
		},
		canKickUsersCaption(): string
		{
			return CanKickUsersCaptionByChatType[this.chatType] ?? CanKickUsersCaptionByChatType.default;
		},
		canSendCaption(): string
		{
			return CanSendMessageCaptionByChatType[this.chatType] ?? CanSendMessageCaptionByChatType.default;
		},
		ownerHint(): string
		{
			return OwnerHintByChatType[this.chatType] ?? OwnerHintByChatType.default;
		},
		managerHint(): string
		{
			return ManagerHintByChatType[this.chatType] ?? ManagerHintByChatType.default;
		},
		addUsersHint(): string
		{
			return AddUsersHintByChatType[this.chatType] ?? AddUsersHintByChatType.default;
		},
		deleteUsersHint(): string
		{
			return DeleteUsersHintByChatType[this.chatType] ?? DeleteUsersHintByChatType.default;
		},
		manageUiHint(): string
		{
			return ManageUiHintByChatType[this.chatType] ?? ManageUiHintByChatType.default;
		},
		sendMessagesHint(): string
		{
			return SendMessagesHintByChatType[this.chatType] ?? SendMessagesHintByChatType.default;
		},
	},
	methods:
	{
		prepareDropdownItems(defaultValue: UserRoleItem): DropdownItem[]
		{
			return rightsDropdownItems.map((item) => {
				return {
					...item,
					default: item.value === defaultValue,
				};
			});
		},
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
		onManageMessagesChange(newValue: UserRoleItem)
		{
			this.$emit('manageMessagesChange', newValue);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection name="rights" :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')">
			<UserSelector :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER')" :hintText="ownerHint">
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
			</UserSelector>
			<UserSelector :title="loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGERS')" :hintText="managerHint">
				<ManagersSelector :managerIds="managerIds" @managersChange="onManagersChange" />
			</UserSelector>
			<RoleSelector
				:title="canAddUsersCaption"
				:hintText="addUsersHint"
				:dropdownId="PopupType.createChatManageUsersAddMenu"
				:dropdownItems="manageUsersAddItems"
				@itemChange="onManageUsersAddChange"
			/>
			<RoleSelector
				:title="canKickUsersCaption"
				:hintText="deleteUsersHint"
				:dropdownId="PopupType.createChatManageUsersDeleteMenu"
				:dropdownItems="manageUsersDeleteItems"
				@itemChange="onManageUsersDeleteChange"
			/>
			<RoleSelector
				v-if="showManageUiBlock"
				:title="loc('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_UI_MSGVER_2')"
				:hintText="manageUiHint"
				:dropdownId="PopupType.createChatManageUiMenu"
				:dropdownItems="manageUiItems"
				@itemChange="onManageUiChange"
			/>
			<RoleSelector
				:title="canSendCaption"
				:hintText="sendMessagesHint"
				:dropdownId="PopupType.createChatManageMessagesMenu"
				:dropdownItems="manageMessagesItems"
				@itemChange="onManageMessagesChange"
			/>
		</CreateChatSection>
	`,
};
