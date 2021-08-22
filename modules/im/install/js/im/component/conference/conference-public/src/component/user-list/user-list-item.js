import { Vuex } from "ui.vue.vuex";
import { Utils } from "im.lib.utils";
import { MenuManager } from "main.popup";
import { ConferenceUserState, ConferenceStateType, EventType } from 'im.const';
import { EventEmitter } from "main.core.events";

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
			menuId: 'bx-messenger-context-popup-external-data',
			onlineStates: [ConferenceUserState.Ready, ConferenceUserState.Connected]
		}
	},
	computed:
	{
		user()
		{
			return this.$store.getters['users/get'](this.userId, true);
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
		isDesktop()
		{
			return Utils.platform.isBitrixDesktop();
		},
		isGuestWithDefaultName()
		{
			const guestDefaultName = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME');

			return this.user.id === this.currentUser && this.user.extranet && this.user.name === guestDefaultName;
		},
		userCallStatus()
		{
			return this.$store.getters['call/getUser'](this.user.id);
		},
		isUserInCall()
		{
			return this.onlineStates.includes(this.userCallStatus.state);
		},
		userInCallCount()
		{
			const usersInCall = Object.values(this.call.users).filter(user => {
				return this.onlineStates.includes(user.state);
			});

			return usersInCall.length;
		},
		isBroadcast()
		{
			return this.conference.common.isBroadcast;
		},
		presentersList()
		{
			return this.conference.common.presenters;
		},
		isUserPresenter()
		{
			return this.presentersList.includes(this.user.id);
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
			// for self
			if (this.user.id === this.currentUser)
			{
				// self-rename
				if (this.isCurrentUserExternal)
				{
					items.push({
						text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME_SELF'),
						onclick: () => {
							this.closeMenu();
							this.onRenameStart();
						}
					});
				}
				// change background
				if (this.isDesktop)
				{
					items.push({
						text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_CHANGE_BACKGROUND'),
						onclick: () => {
							this.closeMenu();
							this.$emit('userChangeBackground');
						}
					});
				}
			}
			// for other users
			else
			{
				// force-rename
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
				// kick
				if (this.isCurrentUserOwner && !this.isUserPresenter)
				{
					items.push({
						text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_KICK'),
						onclick: () => {
							this.closeMenu();
							this.$emit('userKick', {user: this.user});
						}
					});
				}
				if (this.isUserInCall && this.userCallStatus.cameraState && this.userInCallCount > 2)
				{
					// pin
					if (!this.userCallStatus.pinned)
					{
						items.push({
							text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_PIN'),
							onclick: () => {
								this.closeMenu();
								this.$emit('userPin', {user: this.user});
							}
						});
					}
					// unpin
					else
					{
						items.push({
							text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_UNPIN'),
							onclick: () => {
								this.closeMenu();
								this.$emit('userUnpin');
							}
						});
					}

				}
				// open 1-1 chat and profile
				if (this.isDesktop && !this.user.extranet)
				{
					items.push({
						text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_OPEN_CHAT'),
						onclick: () => {
							this.closeMenu();
							this.$emit('userOpenChat', {user: this.user});
						}
					});
					items.push({
						text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_OPEN_PROFILE'),
						onclick: () => {
							this.closeMenu();
							this.$emit('userOpenProfile', {user: this.user});
						}
					});
				}
				// insert name
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
		avatarWrapClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-avatar-wrap'];

			if (this.userCallStatus.talking)
			{
				classes.push('bx-im-component-call-user-list-item-avatar-wrap-talking');
			}

			return classes;
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
		isCallStatusPanelNeeded()
		{
			if (this.isBroadcast)
			{
				return this.conference.common.state === ConferenceStateType.call && this.isUserInCall && this.isUserPresenter;
			}
			else
			{
				return this.conference.common.state === ConferenceStateType.call && this.isUserInCall;
			}
		},
		callLeftIconClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-left'];

			if (this.userCallStatus.floorRequestState)
			{
				classes.push('bx-im-component-call-user-list-item-icons-floor-request');
			}
			else if (this.userCallStatus.screenState)
			{
				classes.push('bx-im-component-call-user-list-item-icons-screen');
			}

			return classes;
		},
		callCenterIconClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-center'];

			if (this.userCallStatus.microphoneState)
			{
				classes.push('bx-im-component-call-user-list-item-icons-mic-on');
			}
			else
			{
				classes.push('bx-im-component-call-user-list-item-icons-mic-off');
			}

			return classes;
		},
		callRightIconClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-right'];

			if (this.userCallStatus.cameraState)
			{
				classes.push('bx-im-component-call-user-list-item-icons-camera-on');
			}
			else
			{
				classes.push('bx-im-component-call-user-list-item-icons-camera-off');
			}

			return classes;
		},
		bodyClasses()
		{
			const classes = ['bx-im-component-call-user-list-item-body'];

			if (!this.isUserInCall)
			{
				classes.push('bx-im-component-call-user-list-item-body-offline');
			}

			return classes;
		},
		...Vuex.mapState({
			application: state => state.application,
			conference: state => state.conference,
			call: state => state.call,
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
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
		},
		onFocus(event)
		{
			EventEmitter.emit(EventType.conference.userRenameFocus, event);
		},
		onBlur(event)
		{
			EventEmitter.emit(EventType.conference.userRenameBlur, event);
		},
	},
	//language=Vue
	template: `
		<div class="bx-im-component-call-user-list-item">
			<!-- Avatar -->
			<div :class="avatarWrapClasses">
				<div :class="avatarClasses" :style="avatarStyle"></div>
			</div>
			<!-- Body -->
			<div :class="bodyClasses">
				<!-- Introduce yourself blinking mode -->
				<template v-if="!renameMode && isGuestWithDefaultName">
					<div class="bx-im-component-call-user-list-item-body-left">
						<div @click="onRenameStart" class="bx-im-component-call-user-list-introduce-yourself">
							<div class="bx-im-component-call-user-list-introduce-yourself-text">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_INTRODUCE_YOURSELF') }}</div>
						</div>
					</div>
				</template>
				<!-- Rename mode -->
				<template v-else-if="renameMode">
					<div class="bx-im-component-call-user-list-item-body-left">
						<div class="bx-im-component-call-user-list-change-name-container">
							<div @click="renameMode = false" class="bx-im-component-call-user-list-change-name-cancel"></div>
							<input @keydown="onRenameKeyDown" @focus="onFocus" @blur="onBlur" v-model="newName" :ref="'rename-input'" type="text" class="bx-im-component-call-user-list-change-name-input">
							<div v-if="!renameRequested" @click="changeName" class="bx-im-component-call-user-list-change-name-confirm"></div>
							<div v-else class="bx-im-component-call-user-list-change-name-loader">
								<div class="bx-im-component-call-user-list-change-name-loader-icon"></div>
							</div>
						</div>
					</div>
				</template>
				<template v-if="!renameMode && !isGuestWithDefaultName">
					<div class="bx-im-component-call-user-list-item-body-left">
						<div class="bx-im-component-call-user-list-item-name-wrap">
							<!-- Name -->
							<div class="bx-im-component-call-user-list-item-name">{{ user.name }}</div>
							<!-- Status subtitle -->
							<div v-if="formattedSubtitle !== ''" class="bx-im-component-call-user-list-item-name-subtitle">{{ formattedSubtitle }}</div>
						</div>
						<!-- Context menu icon -->
						<div v-if="menuItems.length > 0 && !isMobile" @click="openMenu" ref="user-menu" class="bx-im-component-call-user-list-item-menu"></div>
					</div>
				</template>
				<template v-if="isCallStatusPanelNeeded">
					<div class="bx-im-component-call-user-list-item-icons">
						<div :class="callLeftIconClasses"></div>
						<div :class="callCenterIconClasses"></div>
						<div :class="callRightIconClasses"></div>
					</div>
				</template>
			</div>
		</div>
	`
};

export {UserListItem};