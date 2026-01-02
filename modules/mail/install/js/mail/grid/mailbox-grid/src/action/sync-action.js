import { BaseAction, ActionConfig } from './base-action';
import { Loc } from 'main.core';

export class SyncAction extends BaseAction
{
	mailboxId: number;

	static getActionId(): string
	{
		return 'syncAction';
	}

	getActionConfig(): ActionConfig
	{
		return {
			type: 'component',
			component: 'bitrix:mail.client',
			name: 'syncMailbox',
			options: {
				mode: 'ajax',
			},
		};
	}

	getActionData(): Object
	{
		return {
			id: this.mailboxId,
			dir: 'INBOX',
			onlySyncCurrent: true,
		};
	}

	setActionParams(params: Object): void
	{
		this.mailboxId = params.mailboxId;
	}

	onBeforeActionRequest()
	{
		this.grid.tableFade();

		const toastMessage = String(Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_SYNC_START'));

		BX.UI.Notification.Center.notify({
			content: toastMessage,
			position: 'top-right',
			autoHideDelay: 3000,
		});
	}

	onAfterActionRequest(): void
	{
		this.grid.reload(() => {
			this.grid.tableUnfade();
		});
	}
}
