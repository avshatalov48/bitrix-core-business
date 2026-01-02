import { BaseField } from './base-field';
import { Dom, Tag, Text, Event, Loc } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { GridManager } from '../grid-manager';

export type LastSyncFieldType = {
	lastSync: ?number,
	mailboxId: ?number,
	hasError: ?boolean,
}

export class LastSyncField extends BaseField
{
	render(params: LastSyncFieldType): void
	{
		const lastSyncContainer = Tag.render`
			<div class="mailbox-grid_last-sync-container"></div>
		`;

		if (params.hasError)
		{
			Dom.append(this.#getErrorMessage(), lastSyncContainer);
		}
		else
		{
			if (params.lastSync)
			{
				Dom.append(this.#getLastSyncContainer(params.lastSync), lastSyncContainer);
			}

			if (params.mailboxId)
			{
				Dom.append(this.#getLastSyncButton(params.mailboxId), lastSyncContainer);
			}
		}

		this.appendToFieldNode(lastSyncContainer);
	}

	#getLastSyncContainer(lastSync: string): HTMLElement
	{
		let formattedTime = lastSync;
		if (/^\d+$/.test(lastSync))
		{
			const timestamp = parseInt(lastSync, 10);
			formattedTime = DateTimeFormat.formatLastActivityDate(timestamp);
		}

		return Tag.render`
			<span class="mailbox-grid_last-sync-text">${Text.encode(formattedTime)}</span>
		`;
	}

	#getLastSyncButton(mailboxId: number): HTMLElement
	{
		const button = Tag.render`
			<div class="mailbox-grid_last-sync-button ui-icon-set --o-refresh" data-test-id="mailbox-grid_refresh-button"></div>
		`;

		Event.bind(button, 'click', () => {
			GridManager.getInstance(this.getGrid().containerId).runAction({
				actionId: 'syncAction',
				options: {},
				params: { mailboxId },
			});
		});

		return button;
	}

	#getErrorMessage(): HTMLElement
	{
		return Tag.render`
			<span class="mailbox-grid_last-sync-error-message">
				${Loc.getMessage('MAIL_MAILBOX_LIST_LAST_SYNC_ERROR_MESSAGE')}
			</span>
		`;
	}
}
