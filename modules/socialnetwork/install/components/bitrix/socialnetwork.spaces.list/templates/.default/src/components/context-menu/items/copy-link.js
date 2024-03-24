import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { LinkManager } from '../../../util/link-manager';
import { ContextItem } from './context-item';
import { UI } from 'ui.notification';

export class CopyLink extends ContextItem
{
	create(): Object
	{
		return {
			text: this.message,
			onclick: (event, menuItem: MenuItem) => {
				BX.clipboard.copy(location.origin + LinkManager.getSpaceLink(this.spaceId));
				menuItem.getMenuWindow().close();
				UI.Notification.Center.notify({
					content: Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LINK_NOTIFY'),
				});
			},
		};
	}
}
