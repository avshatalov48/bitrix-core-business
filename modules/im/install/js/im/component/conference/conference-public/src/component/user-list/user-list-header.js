import { Vuex } from "ui.vue.vuex";
import { MenuManager } from 'main.popup';
import { Clipboard } from "im.lib.clipboard";
import { MessageBox, MessageBoxButtons } from "ui.dialogs.messagebox";

const UserListHeader = {
	computed:
	{
		userId()
		{
			return this.application.common.userId;
		},
		isCurrentUserOwner()
		{
			if (!this.dialog)
			{
				return false;
			}

			return this.dialog.ownerId === this.userId;
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
		onCloseUsers()
		{
			this.getApplication().toggleUserList();
		},
		openMenu()
		{
			if (this.menuPopup)
			{
				this.closeMenu();
				return false;
			}

			this.menuPopup = MenuManager.create({
				id: 'bx-im-component-call-user-list-header-popup',
				bindElement: this.$refs['user-list-header-menu'],
				items: this.getMenuItems(),
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
		getMenuItems()
		{
			const items = [{
				text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_HEADER_MENU_COPY_LINK'),
				onclick: () => {
					this.closeMenu();
					this.onMenuCopyLink();
				}
			}];
			if (this.isCurrentUserOwner)
			{
				items.push({
					text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_HEADER_MENU_CHANGE_LINK'),
					onclick: () => {
						this.closeMenu();
						this.onMenuChangeLink();
					}
				});
			}

			return items;
		},
		onMenuCopyLink()
		{
			const publicLink = this.dialog.public.link;
			Clipboard.copy(publicLink);

			const notificationText = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_LINK_COPIED');
			BX.UI.Notification.Center.notify({
				content: notificationText,
				autoHideDelay: 4000
			})
		},
		onMenuChangeLink()
		{
			const confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_CHANGE_LINK_CONFIRM_TEXT');
			this.changeLinkConfirm = MessageBox.create({
				message: confirmMessage,
				modal: true,
				buttons: MessageBoxButtons.OK_CANCEL,
				onOk: () => {
					this.changeLink();
					this.changeLinkConfirm.getPopupWindow().destroy();
				},
				onCancel: () => {
					this.changeLinkConfirm.getPopupWindow().destroy();
				}
			});
			this.changeLinkConfirm.show();
		},
		changeLink()
		{
			this.getApplication().changeLink().then(() => {
				const notificationText = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_LINK_CHANGED');
				BX.UI.Notification.Center.notify({
					content: notificationText,
					autoHideDelay: 4000
				})
			}).catch(error => {
				console.error('Conference: change link error', error);
			});
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		}
	},
	template: `
		<div class="bx-im-component-call-right-header">
			<div class="bx-im-component-call-right-header-left">
				<div @click="onCloseUsers" class="bx-im-component-call-right-header-close" :title="$Bitrix.Loc.getMessage['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']"></div>
			<div class="bx-im-component-call-right-header-title">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USERS_LIST_TITLE') }}</div>
			</div>
			<div class="bx-im-component-call-right-header-right">
				<div @click="openMenu" class="bx-im-component-call-user-list-header-more" ref="user-list-header-menu"></div>	
			</div>
		</div>
	`
};

export {UserListHeader};