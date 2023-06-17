import 'main.date';
import {PopupManager} from 'main.popup';

import {Core} from 'im.v2.application.core';
import {DialogType, Settings, Layout} from 'im.v2.const';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import {MessengerSlider} from 'im.v2.lib.slider';
import {Messenger} from 'im.public';

import {NewUserPopup} from './new-user-popup';
import {MessageText} from './message-text';
import {MessageStatus} from './message-status';
import {DateFormatter, DateTemplate} from 'im.v2.lib.date-formatter';

import '../css/recent-item.css';

import type {ImModelRecentItem, ImModelDialog, ImModelUser} from 'im.v2.model';

const NEW_USER_POPUP_ID = 'im-new-user-popup';

// @vue/component
export const RecentItem = {
	name: 'RecentItem',
	components: {Avatar, ChatTitle, NewUserPopup, MessageText, MessageStatus},
	props: {
		item: {
			type: Object,
			required: true
		},
		compactMode: {
			type: Boolean,
			default: false
		},
		isVisibleOnScreen: {
			type: Boolean,
			required: true
		}
	},
	data()
	{
		return {
			showNewUserPopup: false
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		formattedDate(): string
		{
			if (this.needsBirthdayPlaceholder)
			{
				return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_BIRTHDAY_DATE');
			}

			return this.formatDate(this.recentItem.message.date);
		},
		formattedCounter(): string
		{
			return this.dialog.counter > 99 ? '99+' : this.dialog.counter.toString();
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.recentItem.dialogId, true);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.recentItem.dialogId, true);
		},
		layout(): {name: string, entityId: string}
		{
			return this.$store.getters['application/getLayout'];
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isSelfChat(): boolean
		{
			return this.isUser && this.user.id === Core.getUserId();
		},
		isChatSelected(): boolean
		{
			if (this.layout.name !== Layout.chat.name)
			{
				return false;
			}

			return this.layout.entityId === this.recentItem.dialogId;
		},
		isChatMuted(): boolean
		{
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find(element => {
				return element === Core.getUserId();
			});

			return !!isMuted;
		},
		isSomeoneTyping(): boolean
		{
			return this.dialog.writingList.length > 0;
		},
		needsBirthdayPlaceholder(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		},
		showBirthdays(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showBirthday);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		showCounterContainer(): boolean
		{
			return !this.needsBirthdayPlaceholder && !this.invitation.isActive;
		},
		showPinnedIcon(): boolean
		{
			return this.recentItem.pinned && this.dialog.counter === 0 && !this.recentItem.unread;
		},
		showUnreadWithoutCounter(): boolean
		{
			return this.recentItem.unread && this.dialog.counter === 0;
		},
		showUnreadWithCounter(): boolean
		{
			return this.recentItem.unread && this.dialog.counter > 0;
		},
		showCounter(): boolean
		{
			return !this.recentItem.unread && this.dialog.counter > 0 && !this.isSelfChat;
		},
		invitation(): Object
		{
			return this.recentItem.invitation;
		},
		newUserPopupContainer(): string
		{
			return `#popup-window-content-${NEW_USER_POPUP_ID}-${this.recentItem.dialogId}`;
		},
		wrapClasses(): Object
		{
			return {
				'--pinned': this.recentItem.pinned,
				'--selected': !this.compactMode && this.isChatSelected
			};
		},
		itemClasses(): Object
		{
			return {
				'--no-text': !this.showLastMessage,
			};
		},
		compactItemClasses(): Object
		{
			return {
				'--no-counter': this.dialog.counter === 0
			};
		}
	},
	watch:
	{
		invitation(newValue, oldValue)
		{
			if (!this.compactMode)
			{
				return false;
			}

			// invitation accepted, user logged in
			if (oldValue.isActive === true && newValue.isActive === false)
			{
				this.openNewUserPopup();
			}
		}
	},
	methods:
	{
		openNewUserPopup()
		{
			if (!this.isVisibleOnScreen || MessengerSlider.getInstance().isOpened())
			{
				return false;
			}

			this.newUserPopup = this.getNewUserPopup();
			this.newUserPopup.show();
			this.showNewUserPopup = true;
			this.$nextTick(() => {
				this.newUserPopup.setOffset({
					offsetTop: -this.newUserPopup.popupContainer.offsetHeight + 1,
					offsetLeft: -this.newUserPopup.popupContainer.offsetWidth + 13
				});
				this.newUserPopup.adjustPosition();
			});
		},
		getNewUserPopup()
		{
			return PopupManager.create({
				id: `${NEW_USER_POPUP_ID}-${this.recentItem.dialogId}`,
				bindElement: this.$refs.container,
				bindOptions: {forceBindPosition: true},
				className: `bx-${NEW_USER_POPUP_ID}`,
				cacheable: false,
				animation: {
					showClassName: 'bx-im-new-user-popup__animation_show',
					closeClassName: 'bx-im-new-user-popup__animation_hide',
					closeAnimationType: 'animation'
				}
			});
		},
		onNewUserPopupClick()
		{
			Messenger.openChat(this.recentItem.dialogId);
		},
		onNewUserPopupClose()
		{
			this.newUserPopup.close();
			this.newUserPopup = null;
			this.showNewUserPopup = false;
		},
		formatDate(date): string
		{
			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	// language=Vue
	template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-recent-item__wrap">
			<div v-if="!compactMode" :class="itemClasses" class="bx-im-list-recent-item__container">
				<div class="bx-im-list-recent-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-item__avatar_invitation"></div>
					<div v-else class="bx-im-list-recent-item__avatar_content">
						<Avatar :dialogId="recentItem.dialogId" :size="AvatarSize.XL" :withStatus="!isSomeoneTyping" :withSpecialTypes="!isSomeoneTyping" />
						<div v-if="isSomeoneTyping" class="bx-im-list-recent-item__avatar_typing"></div>
					</div>
				</div>
				<div class="bx-im-list-recent-item__content_container">
					<div class="bx-im-list-recent-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" :withMute="true" />
						<div class="bx-im-list-recent-item__date">
							<MessageStatus :item="item" />
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-recent-item__content_bottom">
						<MessageText :item="recentItem" />
						<div v-if="showCounterContainer" :class="{'--extended': dialog.counter > 99, '--withUnread': recentItem.unread}" class="bx-im-list-recent-item__counter_wrap">
							<div class="bx-im-list-recent-item__counter_container">
								<div v-if="showPinnedIcon" class="bx-im-list-recent-item__pinned-icon"></div>
								<div v-else-if="showUnreadWithoutCounter" :class="{'--muted': isChatMuted}"  class="bx-im-list-recent-item__counter_number --no-counter"></div>
								<div v-else-if="showUnreadWithCounter" :class="{'--muted': isChatMuted}"  class="bx-im-list-recent-item__counter_number --with-counter">
									{{ formattedCounter }}
								</div>
								<div v-else-if="showCounter" :class="{'--muted': isChatMuted}" class="bx-im-list-recent-item__counter_number">
									{{ formattedCounter }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div v-if="compactMode" :class="compactItemClasses" class="bx-im-list-recent-item__container" ref="container">
				<div class="bx-im-list-recent-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-item__avatar_invitation"></div>
					<Avatar v-else :dialogId="recentItem.dialogId" :size="AvatarSize.M" :withStatus="false" :withSpecialTypes="false" />
					<div v-if="dialog.counter > 0" :class="{'--muted': isChatMuted}" class="bx-im-list-recent-item__avatar_counter">
						{{ formattedCounter }}
					</div>
				</div>
				<Teleport v-if="showNewUserPopup" :to="newUserPopupContainer">
					<NewUserPopup
						:title="dialog.name"
						:text="loc('IM_LIST_RECENT_NEW_USER_POPUP_TEXT')"
						@click="onNewUserPopupClick"
						@close="onNewUserPopupClose"
					/>
				</Teleport>
			</div>
		</div>
	`
};