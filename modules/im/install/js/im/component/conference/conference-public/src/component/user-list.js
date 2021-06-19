import { Vuex } from "ui.vue.vuex";
import { Logger } from "im.lib.logger";
import { MenuManager } from 'main.popup';
import { Utils } from "im.lib.utils";
import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";
import { ConferenceRightPanelMode as RightPanelMode } from 'im.const';

const UserList = {
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
		chatOwner()
		{
			if (!this.dialog)
			{
				return 0;
			}

			return this.dialog.ownerId;
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
		usersInfo()
		{
			return this.$store.getters['users/getList'](this.usersList);
		},
		presentersList()
		{
			return [...this.conference.common.presenters].sort(this.userSortFunction);
		},
		presentersInfo()
		{
			return this.$store.getters['users/getList'](this.presentersList);
		},
		isCurrentUserPresenter()
		{
			return this.presentersList.includes(this.userId);
		},
		isCurrentUserExternal()
		{
			return !!this.conference.user.hash;
		},
		isCurrentUserOwner()
		{
			return this.chatOwner === this.userId;
		},
		isMobile()
		{
			return Utils.device.isMobile();
		},
		rightPanelMode()
		{
			return this.conference.common.rightPanelMode;
		},
		...Vuex.mapState({
			user: state => state.users.collection[state.application.common.userId],
			application: state => state.application,
			conference: state => state.conference,
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
		openMenu(user)
		{
			if (this.menuPopup)
			{
				this.closeMenu();
				return false;
			}

			this.menuPopup = MenuManager.create({
				id: 'bx-messenger-context-popup-external-data',
				bindElement: this.$refs[`user-menu-${user.id}`][0],
				items: this.getMenuForUser(user),
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
		isMenuNeeded(user)
		{
			return this.getMenuForUser(user).length > 0;
		},
		getMenuForUser(user)
		{
			const items = [];
			if (this.isCurrentUserExternal && user.id === this.userId)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME_SELF'),
					onclick: () => {
						this.closeMenu();
						this.onMenuRename(user);
					}
				});
			}
			if (this.isCurrentUserOwner && user.externalAuthId === 'call')
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME'),
					onclick: () => {
						this.closeMenu();
						this.onMenuRename(user);
					}
				});
			}
			if (this.isCurrentUserOwner && user.id !== this.userId)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_KICK'),
					onclick: () => {
						this.closeMenu();
						this.onMenuKick(user);
					}
				});
			}
			if (user.id !== this.userId)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_INSERT_NAME'),
					onclick: () => {
						this.closeMenu();
						this.onMenuInsertName(user);
					}
				});
			}

			return items;
		},
		onMenuRename(user)
		{
			this.rename.newName = user.name;
			this.rename.user = user.id;
			this.$nextTick(() => {
				this.$refs[`rename-user-${user.id}`][0].focus();
				this.$refs[`rename-user-${user.id}`][0].select();
			});
		},
		onMenuKick(user)
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
		onMenuInsertName(user)
		{
			if (this.rightPanelMode === RightPanelMode.hidden || this.rightPanelMode === RightPanelMode.users)
			{
				this.getApplication().toggleChat();
			}
			this.$nextTick(() => {
				EventEmitter.emit(EventType.textarea.insertText, {text: `${user.name}, `, focus: true});
			});
		},
		onChangeNameInputKeyDown(user, event)
		{
			//enter
			if (event.keyCode === 13)
			{
				this.changeName(user);
			}
			//escape
			else if (event.keyCode === 27)
			{
				this.rename.user = 0;
			}
		},
		changeName(user)
		{
			if (user.name === this.rename.newName.trim() || this.rename.newName === '')
			{
				this.rename.user = 0;

				return false;
			}

			this.rename.renameRequested = true;
			const method = user.id === this.userId ? 'im.call.user.update' : 'im.call.user.force.rename';

			const oldName = user.name;
			this.$store.dispatch('users/update', {
				id: user.userId,
				fields: {name: this.rename.newName, lastActivityDate: new Date()}
			});
			this.$Bitrix.RestClient.get().callMethod(method, {
				name: this.rename.newName,
				chat_id: this.application.dialog.chatId,
				user_id: user.id
			}).then(() => {
				this.rename.user = 0;
				this.rename.renameRequested = false;
			}).catch((error) => {
				Logger.error('Conference: renaming error', error);
				this.$store.dispatch('users/update', {
					id: user.userId,
					fields: {name: oldName, lastActivityDate: new Date()}
				});
				this.rename.user = 0;
				this.rename.renameRequested = false;
			});
		},
		isSubtitleNeeded(user)
		{
			return this.getSubtitleForUser(user) !== '';
		},
		getSubtitleForUser(user)
		{
			const subtitles = [];

			if (this.chatOwner === user.id)
			{
				subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OWNER'));
			}

			if (user.id === this.userId)
			{
				subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_CURRENT_USER'));
			}

			// if (!user.extranet && !user.isOnline)
			// {
			// 	subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OFFLINE'));
			// }

			return subtitles.join(', ');
		},
		getAvatarClasses(user)
		{
			const classes = ['bx-im-component-call-user-list-item-avatar'];

			if (!user.avatar && user.extranet)
			{
				classes.push('bx-im-component-call-user-list-item-avatar-extranet');
			}
			else if (!user.avatar && !user.extranet)
			{
				classes.push('bx-im-component-call-user-list-item-avatar-default');
			}

			return classes;
		},
		getAvatarStyle(user)
		{
			const style = {};

			if (user.avatar)
			{
				style.backgroundImage = `url('${user.avatar}')`;
			}
			else if (!user.avatar && !user.extranet)
			{
				style.backgroundColor = user.color;
			}

			return style;
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
		userSortFunction(user)
		{
			if (user === this.userId)
			{
				return -1;
			}
			else
			{
				return 0;
			}
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
					vnode.context.loaderObserver.unobserve(element);

					return true;
				}
			},
	},
	template: `
		<div class="bx-im-component-call-user-list">
			<!-- Loading first page -->
			<div v-if="!firstPageLoaded">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING') }}</div>
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
						<div v-for="presenter in presentersInfo" :key="presenter.id" class="bx-im-component-call-user-list-item">
							<!-- Avatar -->
							<div :class="getAvatarClasses(presenter)" :style="getAvatarStyle(presenter)"></div>
							<!-- Body -->
							<div class="bx-im-component-call-user-list-item-body">
								<div class="bx-im-component-call-user-list-item-name-wrap">
									<!-- Name -->
									<div class="bx-im-component-call-user-list-item-name">{{ presenter.name }}</div>
									<!-- Status subtitle -->
									<div v-if="isSubtitleNeeded(presenter)" class="bx-im-component-call-user-list-item-name-subtitle">{{ getSubtitleForUser(presenter) }}</div>
								</div>
								<!-- Context menu icon -->
								<div v-if="isMenuNeeded(presenter) && !isMobile" @click="openMenu(presenter)" :ref="'user-menu-' + presenter.id" class="bx-im-component-call-user-list-item-menu"></div>
								<div class="bx-im-component-call-user-list-item-icons"></div>
							</div>
						</div>
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
						<div v-for="user in usersInfo" :key="user.id" class="bx-im-component-call-user-list-item">
							<!-- Avatar -->
							<div :class="getAvatarClasses(user)" :style="getAvatarStyle(user)"></div>
							<!-- Rename mode -->
							<template v-if="rename.user === user.id">
								<div class="bx-im-component-call-user-list-change-name-container">
									<div @click="rename.user = 0" class="bx-im-component-call-user-list-change-name-cancel"></div>
									<input @keydown="onChangeNameInputKeyDown(user, $event)" v-model="rename.newName" :ref="'rename-user-' + user.id" type="text" class="bx-im-component-call-user-list-change-name-input">
									<div v-if="!rename.renameRequested" @click="changeName(user)" class="bx-im-component-call-user-list-change-name-confirm"></div>
									<div v-else class="bx-im-component-call-user-list-change-name-loader">
										<div class="bx-im-component-call-user-list-change-name-loader-icon"></div>
									</div>
								</div>
							</template>
							<!-- Normal display mode -->
							<template v-else>
							<!-- Body -->
								<div class="bx-im-component-call-user-list-item-body">
									<div class="bx-im-component-call-user-list-item-name-wrap">
										<!-- Name -->
										<div class="bx-im-component-call-user-list-item-name">{{ user.name }}</div>
										<!-- Status subtitle -->
										<div v-if="isSubtitleNeeded(user)" class="bx-im-component-call-user-list-item-name-subtitle">{{ getSubtitleForUser(user) }}</div>
									</div>
									<!-- Context menu icon -->
									<div v-if="isMenuNeeded(user) && !isMobile" @click="openMenu(user)" :ref="'user-menu-' + user.id" class="bx-im-component-call-user-list-item-menu"></div>
									<div class="bx-im-component-call-user-list-item-icons"></div>
								</div>
							</template>
						</div>
					</div>
				</template>
				<!-- Next page loader -->
				<div v-if="hasMoreToLoad" v-bx-im-directive-user-list-observer class="bx-im-component-call-user-list-next-page-loader">
					{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING') }}
				</div>
			</template>	
		</div>
	`
};

export {UserList};