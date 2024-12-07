import {Loc, Tag} from 'main.core';
import './css/style.css';

export class ErrorBox
{
	#wrapper;
	#errorLink;
	#errorBoxWasRendered = false;
	#errorBoxNode = null;
	#mailboxId;

	constructor(config)
	{
		const {
			wrapper = [],
			errorLink = '',
			currentMailboxId = null,
		} = config;

		if (currentMailboxId !== null)
		{
			this.#mailboxId = Number(currentMailboxId);
		}

		this.#wrapper = wrapper;
		this.#errorLink = errorLink;

		top.BX.addCustomEvent("MailClient:syncWasSuccessful", (data) => {
			const {
				mailboxId,
			} = data;

			if (mailboxId !== undefined && this.#mailboxId === Number(mailboxId))
			{
				this.destroy();
			}
		});

		top.BX.addCustomEvent("MailClient:syncFailedWithErrors", (data) => {

			const {
				mailboxId,
			} = data;

			if (mailboxId !== undefined && this.#mailboxId === Number(mailboxId))
			{
				BX.ajax.runComponentAction('bitrix:mail.client.message.list', 'getLastMailboxSyncIsSuccessStatus', {
					mode: 'class',
					data:
						{
							mailboxId,
						}
					}
				).then((response) => {
					const {
						data,
					} = response;

					if (data === false)
					{
						this.build();
					}

					if (data === true)
					{
						this.destroy();
					}
				});
			}
		});
	}

	destroy()
	{
		if (this.#errorBoxWasRendered === true && this.#errorBoxNode !== undefined)
		{
			this.#errorBoxNode.remove();
			this.#errorBoxWasRendered = false;
			this.#errorBoxNode = null;
		}
	}

	build()
	{
		if (this.#errorBoxWasRendered === false)
		{
			let message = Loc.getMessage("MAIL_ERROR_BOX_MAILBOX_CONNECTION_ERROR");
			message = message.replace('[link]', `<a href='${this.#errorLink}' target='_blank'>`);
			message = message.replace('[/link]', '</a>');

			const errorBox = Tag.render`
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger ui-alert-mail-error-box">
				<span class="ui-alert-message">${message}</span>
			</div>`;

			this.#wrapper.prepend(errorBox);
			this.#errorBoxNode = errorBox;

			this.#errorBoxWasRendered = true;
		}
	}
}