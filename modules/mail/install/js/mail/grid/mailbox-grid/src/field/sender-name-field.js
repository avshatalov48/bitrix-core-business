import { BaseField } from './base-field';
import { Tag, Text } from 'main.core';

export type SenderNameFieldType = {
	senderName: string,
}

export class SenderNameField extends BaseField
{
	#senderName: string;

	render(params: SenderNameFieldType): void
	{
		this.#senderName = params.senderName;

		if (this.#senderName === '')
		{
			this.#renderEmpty();
		}

		this.#renderSenderName();
	}

	#renderEmpty(): void
	{
		const emptyContainer = Tag.render`
			<div class="mailbox-grid_sender-name --empty">
			</div>
		`;

		this.appendToFieldNode(emptyContainer);
	}

	#renderSenderName(): void
	{
		const senderNameContainer = Tag.render`
			<div class="mailbox-grid_sender-name-container">
				${Text.encode(this.#senderName)}
			</div>
		`;

		this.appendToFieldNode(senderNameContainer);
	}
}
