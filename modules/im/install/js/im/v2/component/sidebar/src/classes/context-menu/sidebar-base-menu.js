import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {BaseMenu} from 'im.v2.lib.menu';
import {EventType} from 'im.v2.const';
import type {MenuItem} from 'im.v2.lib.menu';

export class SidebarMenu extends BaseMenu
{
	constructor()
	{
		super();
		this.id = 'im-sidebar-context-menu';
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
		};
	}

	getOpenContextMessageItem(): ?MenuItem
	{
		if (!this.context.messageId || this.context.messageId === 0)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_GO_TO_CONTEXT_MESSAGE'),
			onclick: () => {
				EventEmitter.emit(EventType.dialog.goToMessageContext, {
					messageId: this.context.messageId,
					dialogId: this.context.dialogId,
				});

				this.menuInstance.close();
			}
		};
	}

	getCopyLinkItem(title: string): ?MenuItem
	{
		if (!BX.clipboard.isCopySupported())
		{
			return null;
		}

		return {
			text: title,
			onclick: () => {
				if (BX.clipboard.copy(this.context.source))
				{
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS')
					});
				}
				this.menuInstance.close();
			}
		};
	}
}