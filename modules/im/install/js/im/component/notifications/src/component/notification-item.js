import 'ui.design-tokens';

import 'im.view.element.attach';
import 'im.view.element.keyboard';

import "./notification-item.css";
import "./notification-item.dark.css";
import { Utils } from "im.lib.utils";
import { Vue } from "ui.vue";
import { NotificationQuickAnswer } from './notification-quick-answer';
import { NotificationItemHeader } from './notification-item-header';
import { NotificationPlaceholder } from './notification-placeholder';
import { PopupManager } from "main.popup";
import { Dom } from 'main.core';
import { NotificationTypesCodes } from 'im.const';

export const NotificationItem = {
	components:
	{
		NotificationQuickAnswer,
		NotificationItemHeader,
		NotificationPlaceholder
	},
	props: ['rawListItem', 'searchMode'],
	data()
	{
		return {
			menuId: 'popup-window-content-bx-messenger-popup-notify'
		};
	},
	computed:
	{
		NotificationTypesCodes: () => NotificationTypesCodes,
		listItem()
		{
			return {
				id: this.rawListItem.id,
				type: this.rawListItem.type,
				sectionCode: this.rawListItem.sectionCode,
				authorId: this.rawListItem.authorId,
				systemType: this.rawListItem.type === 4 || (this.rawListItem.authorId === 0 && this.avatar === ''),
				title: {
					value: this.userTitle,
				},
				subtitle: {
					value: this.rawListItem.textConverted,
				},
				avatar: {
					url: this.avatar,
					color: this.defaultAvatarColor
				},
				params: this.rawListItem.params || {},
				notifyButtons: this.rawListItem.notifyButtons || undefined,
				unread: this.rawListItem.unread,
				settingName: this.rawListItem.settingName,
				date: {
					value: Utils.date.format(this.rawListItem.date, null, this.$Bitrix.Loc.getMessages())
				},
			}
		},
		isRealItem()
		{
			return this.rawListItem.sectionCode !== NotificationTypesCodes.placeholder;
		},
		isNeedQuickAnswer()
		{
			return this.listItem.params.CAN_ANSWER && this.listItem.params.CAN_ANSWER === 'Y'
		},

		userTitle()
		{
			if (this.isRealItem && this.rawListItem.authorId > 0)
			{
				return this.userData.name;
			}

			const {title} = this.rawListItem;

			return title.length > 0 ? title : this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_ITEM_SYSTEM');
		},
		avatar()
		{
			let avatar = '';

			if (this.isRealItem && this.rawListItem.authorId > 0)
			{
				avatar = this.userData.avatar;
			}
			else if (this.isRealItem && this.rawListItem.authorId === 0)
			{
				//system notification
				return '';
			}

			return avatar;
		},
		defaultAvatarColor()
		{
			if (this.rawListItem.authorId <= 0)
			{
				return '';
			}

			return this.userData.color;
		},
		userData()
		{
			return this.$store.getters['users/get'](this.rawListItem.authorId, true);
		},
		isExtranet(): boolean
		{
			return this.userData.extranet;
		},
		avatarStyles()
		{
			return {
				backgroundImage: 'url("' + this.listItem.avatar.url +'")',
			}
		}
	},
	methods:
	{
		//events
		onDoubleClick(event)
		{
			if (!this.searchMode)
			{
				this.$emit('dblclick', event);
			}
		},
		onButtonsClick(event)
		{
			if (event.action === 'COMMAND')
			{
				this.$emit('buttonsClick', event);
			}
		},
		onDeleteClick(event)
		{
			this.$emit('deleteClick', event);
		},
		onMoreUsersClick(event)
		{
			this.$emit('contentClick', event);
		},
		onContentClick(event)
		{
			if (Vue.testNode(event.target, {className: 'bx-im-mention'}))
			{
				this.$emit('contentClick', {
					event,
					content: {
						type: event.target.dataset.type,
						value: event.target.dataset.value
					}
				});
			}
		},
		onRightClick(event)
		{
			if (
				Utils.platform.isBitrixDesktop()
				&& event.target.tagName === 'A'
				&& (
					!event.target.href.startsWith('/desktop_app/')
					|| event.target.href.startsWith('/desktop_app/show.file.php')
				)
			)
			{
				const hrefToCopy = event.target.href;
				if (!hrefToCopy)
				{
					return;
				}

				if (this.menuPopup)
				{
					this.menuPopup.destroy();
					this.menuPopup = null;
				}

				//menu for other items
				const existingMenu = PopupManager.getPopupById(this.menuId);
				if (existingMenu)
				{
					existingMenu.destroy();
				}

				const menuItem = Dom.create('span', {
					attrs: {
						className: 'bx-messenger-popup-menu-item-text bx-messenger-popup-menu-item',
					},
					events: {
						click: (event) => {
							BX.desktop.clipboardCopy(hrefToCopy);
							this.menuPopup.destroy();
							this.menuPopup = null;
						},
					},
					text: this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_CONTEXT_COPY_LINK'),
				});

				this.menuPopup = PopupManager.create({
					id: this.menuId,
					targetContainer: document.body,
					className: BX.MessengerTheme.isDark() ? 'bx-im-notifications-popup-window-dark' : '',
					darkMode: BX.MessengerTheme.isDark(),
					bindElement: event,
					offsetLeft: 13,
					autoHide: true,
					closeByEsc: true,
					events: {
						onPopupClose: () => this.menuPopup.destroy(),
						onPopupDestroy: () => this.menuPopup = null,
					},
					content: menuItem
				});
				if (!BX.MessengerTheme.isDark())
				{
					this.menuPopup.setAngle({});
				}

				this.menuPopup.show();
			}
		},
	},
	//language=Vue
	template: `
		<div 
			class="bx-im-notifications-item"
			:class="[listItem.unread && !searchMode ? 'bx-im-notifications-item-unread' : '']"
			@dblclick="onDoubleClick({item: listItem, event: $event})"
			@contextmenu="onRightClick"
		>
			<template v-if="listItem.sectionCode !== NotificationTypesCodes.placeholder">
				<div v-if="listItem.avatar" class="bx-im-notifications-item-image-wrap">
					<div 
						v-if="listItem.avatar.url" 
						class="bx-im-notifications-item-image"
						:style="avatarStyles"
					></div>
					<div v-else-if="listItem.systemType" class="bx-im-notifications-item-image bx-im-notifications-image-system"></div>
					<div 
						v-else-if="!listItem.avatar.url" 
						class="bx-im-notifications-item-image bx-im-notifications-item-image-default"
						:style="{backgroundColor: listItem.avatar.color}"
						>
					</div>
				</div>
				<div class="bx-im-notifications-item-content" @click="onContentClick">
					<NotificationItemHeader 
						:listItem="listItem"
						:isExtranet="isExtranet"
						@deleteClick="onDeleteClick"
						@moreUsersClick="onMoreUsersClick"
					/>
					<div v-if="listItem.subtitle.value.length > 0" class="bx-im-notifications-item-content-bottom">
						<div class="bx-im-notifications-item-bottom-subtitle">
							<span
								:class="[!listItem.title.value ? 'bx-im-notifications-item-bottom-subtitle-text' : 'bx-im-notifications-item-bottom-no-subtitle-text']"
								v-html="listItem.subtitle.value"
							>
							</span>
						</div>
					</div>
					<NotificationQuickAnswer v-if="isNeedQuickAnswer" :listItem="listItem"/>
					<div v-if="listItem.params['ATTACH']" class="bx-im-notifications-item-content-additional">
						<div v-for="attach in listItem.params['ATTACH']">
							<bx-im-view-element-attach :config="attach"/>
						</div>
					</div>
					<div v-if="listItem.notifyButtons">
						<bx-im-view-element-keyboard @click="onButtonsClick" :buttons="listItem.notifyButtons"/>
					</div>
				</div>
			</template>
			<NotificationPlaceholder v-else-if="listItem.sectionCode === NotificationTypesCodes.placeholder"/>
		</div>
	`
};
