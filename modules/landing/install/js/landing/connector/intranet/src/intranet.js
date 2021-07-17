import {Loc} from 'landing.loc';
import {MessageBox} from 'ui.dialogs.messagebox';

export class Intranet
{
	static unbindMenuItem(bindCode, entityId, title)
	{
		MessageBox.confirm(
			Loc.getMessage('LANDING_CONNECTOR_INTRANET_HIDE_ALERT_MESSAGE'),
			Loc.getMessage('LANDING_CONNECTOR_INTRANET_HIDE_ALERT_TITLE').replaceAll('#title#', title),
			() => {
				BX.ajax({
					url: BX.message('SITE_DIR') + 'kb/binding/menu/',
					method: 'POST',
					data: {
						action: 'unbind',
						param: entityId,
						menuId: bindCode,
						sessid: BX.message('bitrix_sessid'),
						actionType: 'json'
					},
					dataType: 'json',
					onsuccess: data => {
						if (data)
						{
							top.window.location.reload();
						}
					}
				});
			},
			Loc.getMessage('LANDING_CONNECTOR_INTRANET_HIDE_ALERT_BUTTON')
		);
	}
}