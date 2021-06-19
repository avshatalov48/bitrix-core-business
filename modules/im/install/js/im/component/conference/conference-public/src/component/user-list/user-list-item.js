import { Vuex } from "ui.vue.vuex";
import { Utils } from "im.lib.utils";
import { MenuManager } from "main.popup";

const UserListItem = {
	props: {
		userId: {
			type: Number,
			required: true
		}
	},
	data: function() {
		return {
			renameMode: false,
			newName: '',
			renameRequested: false,
			menuId: 'bx-messenger-context-popup-external-data'
		}
	},
	computed:
	{
		user()
		{
			return this.$store.getters['users/get'](this.userId);
		},
		// statuses
		currentUser()
		{
			return this.application.common.userId;
		},
		chatOwner()
		{
			if (!this.dialog)
			{
				return 0;
			}

			return this.dialog.ownerId;
		},
		isCurrentUserOwner()
		{
			return this.chatOwner === this.currentUser;
		},
		isCurrentUserExternal()
		{
			return !!this.conference.user.hash;
		},
		isMobile()
		{
			return Utils.device.isMobile();
		},
		isGuestWithDefaultName()
		{
			const guestDefaultName = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME');

			return this.user.id === this.currentUser && this.user.extranet && this.user.name === guestDefaultName;
		},
		// end statuses
		formattedSubtitle()
		{
			const subtitles = [];

			if (this.user.id === this.chatOwner)
			{
				subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OWNER'));
			}

			if (this.user.id === this.currentUser)
			{
				subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_CURRENT_USER'));
			}

			// if (!this.user.extranet && !this.user.isOnline)
			// {
			// 	subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OFFLINE'));
			// }

			return subtitles.join(', ');
		},
		isMenuNeeded()
		{
			return this.getMenuItems.length > 0;
		},
		menuItems()
		{
			const items = [];
			if (this.isCurrentUserExternal && this.user.id === this.currentUser)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME_SELF'),
					onclick: () => {
						this.closeMenu();
						this.onRenameStart();
					}
				});
			}
			if (this.isCurrentUserOwner && this.user.externalAuthId === 'call')
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME'),
					onclick: () => {
						this.closeMenu();
						this.onRenameStart();
					}
				});
			}
			if (this.isCurrentUserOwner && this.user.id !== this.currentUser)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_KICK'),
					onclick: () => {
						this.closeMenu();
						this.$emit('userKick', {user: this.user});
					}
				});
			}
			if (this.user.id !== this.currentUser)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_INSERT_NAME'),
					onclick: () => {
						this.closeMenu();
						this.$emit('userInsertName', {user: this.user});
					}
				});
			}

			return items;
		},
		avatarClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-avatar'];

			if (!this.user.avatar && this.user.extranet)
			{
				classes.push('bx-im-component-call-user-list-item-avatar-extranet');
			}
			else if (!this.user.avatar && !this.user.extranet)
			{
				classes.push('bx-im-component-call-user-list-item-avatar-default');
			}

			return classes;
		},
		avatarStyle()
		{
			const style = {};

			if (this.user.avatar)
			{
				style.backgroundImage = `url('${this.user.avatar}')`;
			}
			else if (!this.user.avatar && !this.user.extranet)
			{
				style.backgroundColor = this.user.color;
			}

			return style;
		},
		...Vuex.mapState({
			application: state => state.application,
			conference: state => state.conference,
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
	},
	watch:
	{

	},
	methods:
	{
		openMenu()
		{
			if (this.menuPopup)
			{
				this.closeMenu();
				return false;
			}

			//menu for other items
			const existingMenu = MenuManager.getMenuById(this.menuId);
			if (existingMenu)
			{
				existingMenu.destroy();
			}

			this.menuPopup = MenuManager.create({
				id: this.menuId,
				bindElement: this.$refs['user-menu'],
				items: this.menuItems,
				events: {
					onPopupClose: () => this.menuPopup.destroy(),
					onPopupDestroy: () => this.menuPopup = null
				},
			});

			this.menuPopup.show();
		},
		closeMenu()
		{
			this.menuPopup.destroy();
			this.menuPopup = null;
		},
		onRenameStart()
		{
			this.newName = this.user.name;
			this.renameMode = true;
			this.$nextTick(() => {
				this.$refs['rename-input'].focus();
				this.$refs['rename-input'].select();
			});
		},
		onRenameKeyDown(event)
		{
			//enter
			if (event.keyCode === 13)
			{
				this.changeName();
			}
			//escape
			else if (event.keyCode === 27)
			{
				this.renameMode = false;
			}
		},
		changeName()
		{
			if (this.user.name === this.newName.trim() || this.newName === '')
			{
				this.renameMode = false;

				return false;
			}

			this.$emit('userChangeName', {user: this.user, newName: this.newName});
			this.$nextTick(() => {
				this.renameMode = false;
			});
		}
	},
	template: `
		<div class="bx-im-component-call-user-list-item">
			<!-- Avatar -->
			<div :class="avatarClasses" :style="avatarStyle"></div>
			<!-- Introduce yourself blinking mode -->
			<template v-if="!renameMode && isGuestWithDefaultName">
				<div @click="onRenameStart" class="bx-im-component-call-user-list-introduce-yourself">
					<div class="bx-im-component-call-user-list-introduce-yourself-text">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_INTRODUCE_YOURSELF') }}</div>
				</div>
			</template>
			<!-- Rename mode -->
			<template v-else-if="renameMode">
				<div class="bx-im-component-call-user-list-change-name-container">
					<div @click="renameMode = false" class="bx-im-component-call-user-list-change-name-cancel"></div>
					<input @keydown="onRenameKeyDown" v-model="newName" :ref="'rename-input'" type="text" class="bx-im-component-call-user-list-change-name-input">
					<div v-if="!renameRequested" @click="changeName" class="bx-im-component-call-user-list-change-name-confirm"></div>
					<div v-else class="bx-im-component-call-user-list-change-name-loader">
						<div class="bx-im-component-call-user-list-change-name-loader-icon"></div>
					</div>
				</div>
			</template>
			<!-- Body -->
			<template v-else>
				<div class="bx-im-component-call-user-list-item-body">
					<div class="bx-im-component-call-user-list-item-name-wrap">
						<!-- Name -->
						<div class="bx-im-component-call-user-list-item-name">{{ user.name }}</div>
						<!-- Status subtitle -->
						<div v-if="formattedSubtitle !== ''" class="bx-im-component-call-user-list-item-name-subtitle">{{ formattedSubtitle }}</div>
					</div>
					<!-- Context menu icon -->
					<div v-if="menuItems.length > 0 && !isMobile" @click="openMenu" ref="user-menu" class="bx-im-component-call-user-list-item-menu"></div>
					<div class="bx-im-component-call-user-list-item-icons"></div>
				</div>
			</template>
		</div>
	`
};

export {UserListItem};