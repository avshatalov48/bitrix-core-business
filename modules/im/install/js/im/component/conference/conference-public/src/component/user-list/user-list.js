import { Vuex } from "ui.vue.vuex";
import { Logger } from "im.lib.logger";
import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";
import { ConferenceRightPanelMode as RightPanelMode, ConferenceUserState } from 'im.const';
import { MessageBox, MessageBoxButtons } from "ui.dialogs.messagebox";

import {UserListItem} from './user-list-item';

const UserList = {
	components: {UserListItem},
	data()
	{
		return {
			usersPerPage: 50,
			firstPageLoaded: false,
			pagesLoaded: 0,
			hasMoreToLoad: true,
			rename: {
				user: 0,
				newName: '',
				renameRequested: false
			}
		}
	},
	created()
	{
		Logger.warn('Conference: user list created');
		this.requestUsers({firstPage: true});
	},
	beforeDestroy()
	{
		this.loaderObserver = null;
	},
	computed:
	{
		userId()
		{
			return this.application.common.userId;
		},
		isBroadcast()
		{
			return this.conference.common.isBroadcast;
		},
		usersList()
		{
			const users = this.conference.common.users.filter(user => {
				return !this.presentersList.includes(user);
			});

			return [...users].sort(this.userSortFunction);
		},
		presentersList()
		{
			return [...this.conference.common.presenters].sort(this.userSortFunction);
		},
		rightPanelMode()
		{
			return this.conference.common.rightPanelMode;
		},
		...Vuex.mapState({
			user: state => state.users.collection[state.application.common.userId],
			application: state => state.application,
			conference: state => state.conference,
			call: state => state.call,
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
	},
	methods:
	{
		requestUsers({firstPage = false} = {})
		{
			this.$Bitrix.RestClient.get().callMethod('im.dialog.users.list', {
				'DIALOG_ID': this.application.dialog.dialogId,
				'LIMIT': this.usersPerPage,
				'OFFSET': firstPage? 0 : (this.pagesLoaded * this.usersPerPage)
			}).then(result => {
				Logger.warn('Conference: getting next user list result', result.data());
				const users = result.data();
				this.pagesLoaded++;
				if (users.length < this.usersPerPage)
				{
					this.hasMoreToLoad = false;
				}

				this.$store.dispatch('users/set', users);
				const usersIds = users.map(user => user.id);

				return this.$store.dispatch('conference/setUsers', { users: usersIds });
			}).then(() => {
				if (firstPage)
				{
					this.firstPageLoaded = true;
				}
			}).catch(result => {
				Logger.warn('Conference: error getting users list', result.error().ex);
			});
		},
		onUserMenuKick({user})
		{
			this.showUserKickConfirm(user);
		},
		showUserKickConfirm(user)
		{
			if (this.userKickConfirm)
			{
				this.userKickConfirm.close();
			}

			let confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_KICK_INTRANET_USER_CONFIRM_TEXT');
			if (user.extranet)
			{
				confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_KICK_GUEST_USER_CONFIRM_TEXT');
			}
			this.userKickConfirm = MessageBox.create({
				message: confirmMessage,
				modal: true,
				buttons: MessageBoxButtons.OK_CANCEL,
				onOk: () => {
					this.kickUser(user);
					this.userKickConfirm.close();
				},
				onCancel: () => {
					this.userKickConfirm.close();
				}
			});
			this.userKickConfirm.show();
		},
		kickUser(user)
		{
			this.$store.dispatch('conference/removeUsers', { users: [user.id] });
			this.$Bitrix.RestClient.get().callMethod('im.chat.user.delete', {
				user_id: user.id,
				chat_id: this.application.dialog.chatId
			}).catch((error) => {
				Logger.error('Conference: removing user from chat error', error);
				this.$store.dispatch('conference/setUsers', { users: [user.id] });
			});
		},
		onUserMenuInsertName({user})
		{
			if (this.rightPanelMode === RightPanelMode.hidden || this.rightPanelMode === RightPanelMode.users)
			{
				this.getApplication().toggleChat();
			}
			this.$nextTick(() => {
				EventEmitter.emit(EventType.textarea.insertText, {text: `${user.name}, `, focus: true});
			});
		},
		onUserChangeName({user, newName})
		{
			const method = user.id === this.userId ? 'im.call.user.update' : 'im.call.user.force.rename';

			const oldName = user.name;
			this.$store.dispatch('users/update', {
				id: user.id,
				fields: {name: newName, lastActivityDate: new Date()}
			});
			this.$Bitrix.RestClient.get().callMethod(method, {
				name: newName,
				chat_id: this.application.dialog.chatId,
				user_id: user.id
			}).then(() => {
				Logger.warn('Conference: rename completed', user.id, newName);
				if (oldName === this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'))
				{
					this.getApplication().setUserWasRenamed();
				}
			}).catch((error) => {
				Logger.error('Conference: renaming error', error);
				this.$store.dispatch('users/update', {
					id: user.id,
					fields: {name: oldName, lastActivityDate: new Date()}
				});
			});
		},
		onUserMenuPin({user})
		{
			this.getApplication().pinUser(user);
		},
		onUserMenuUnpin()
		{
			this.getApplication().unpinUser();
		},
		onUserMenuChangeBackground()
		{
			this.getApplication().changeBackground();
		},
		onUserMenuOpenChat({user})
		{
			this.getApplication().openChat(user);
		},
		onUserMenuOpenProfile({user})
		{
			this.getApplication().openProfile(user);
		},
		// Helpers
		getLoaderObserver()
		{
			const options = {
				root: document.querySelector('.bx-im-component-call-right-users'),
				threshold: 0.01
			};

			const callback = (entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting && entry.intersectionRatio > 0.01)
					{
						Logger.warn('Conference: UserList: I see loader! Load next page!');
						this.requestUsers();
					}
				})
			};

			return new IntersectionObserver(callback, options);
		},
		userSortFunction(userA, userB)
		{
			if (userA === this.userId)
			{
				return -1;
			}
			if (userB === this.userId)
			{
				return 1;
			}

			if (this.call.users[userA] && (this.call.users[userA].floorRequestState || this.call.users[userA].screenState))
			{
				return -1;
			}
			if (this.call.users[userB] && (this.call.users[userB].floorRequestState || this.call.users[userB].screenState))
			{
				return 1;
			}

			if (this.call.users[userA] && [ConferenceUserState.Ready, ConferenceUserState.Connected].includes(this.call.users[userA].state))
			{
				return -1;
			}
			if (this.call.users[userB] && [ConferenceUserState.Ready, ConferenceUserState.Connected].includes(this.call.users[userB].state))
			{
				return 1;
			}

			return 0;
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		}
	},
	directives:
	{
		'bx-im-directive-user-list-observer':
			{
				inserted(element, bindings, vnode)
				{
					vnode.context.loaderObserver = vnode.context.getLoaderObserver();
					vnode.context.loaderObserver.observe(element);

					return true;
				},
				unbind(element, bindings, vnode)
				{
					if (vnode.context.loaderObserver)
					{
						vnode.context.loaderObserver.unobserve(element);
					}

					return true;
				}
			},
	},
	template: `
		<div class="bx-im-component-call-user-list">
			<!-- Loading first page -->
			<div v-if="!firstPageLoaded" class="bx-im-component-call-user-list-loader">
				<div class="bx-im-component-call-user-list-loader-icon"></div>
				<div class="bx-im-component-call-user-list-loader-text">
					{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING_USERS') }}
				</div>
			</div>
			<!-- Loading completed -->
			<template v-else>
				<!-- Speakers list section (if broadcast) -->
				<template v-if="isBroadcast">
					<!-- Speakers category title -->
					<div class="bx-im-component-call-user-list-category">
						<div class="bx-im-component-call-user-list-category-text">
							{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_CATEGORY_PRESENTERS') }}
						</div>
						<div class="bx-im-component-call-user-list-category-counter">
							{{ presentersList.length }}
						</div>
					</div>
					<!-- Speakers list -->
					<div class="bx-im-component-call-user-list-items">
						<template v-for="presenter in presentersList">
							<UserListItem
								@userChangeName="onUserChangeName"
								@userKick="onUserMenuKick"
								@userInsertName="onUserMenuInsertName"
								@userPin="onUserMenuPin"
								@userUnpin="onUserMenuUnpin"
								@userChangeBackground="onUserMenuChangeBackground"
								@userOpenChat="onUserMenuOpenChat"
								@userOpenProfile="onUserMenuOpenProfile"
								:userId="presenter"
								:key="presenter"
							/>
						</template>
					</div>
				</template>
				<!-- Participants list section (if there are any users) -->
				<template v-if="usersList.length > 0">
					<!-- Show participants category title if broadcast -->
					<div v-if="isBroadcast" class="bx-im-component-call-user-list-category bx-im-component-call-user-list-category-participants">
						<div class="bx-im-component-call-user-list-category-text">
							{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_CATEGORY_PARTICIPANTS') }}
						</div>
						<div class="bx-im-component-call-user-list-category-counter">
							{{ usersList.length }}
						</div>
					</div>
					<!-- Participants list -->
					<div class="bx-im-component-call-user-list-items">
						<template v-for="user in usersList">
							<UserListItem
								@userChangeName="onUserChangeName"
								@userKick="onUserMenuKick"
								@userInsertName="onUserMenuInsertName" 
								@userPin="onUserMenuPin"
								@userUnpin="onUserMenuUnpin"
								@userChangeBackground="onUserMenuChangeBackground"
								@userOpenChat="onUserMenuOpenChat"
								@userOpenProfile="onUserMenuOpenProfile"
								:userId="user"
								:key="user" />
						</template>
					</div>
				</template>
				<!-- Next page loader -->
				<div v-if="hasMoreToLoad" v-bx-im-directive-user-list-observer class="bx-im-component-call-user-list-loader">
					<div class="bx-im-component-call-user-list-loader-icon"></div>
					<div class="bx-im-component-call-user-list-loader-text">
						{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING_USERS') }}
					</div>
				</div>
			</template>	
		</div>
	`
};

export {UserList};