import { Tag, Text, Type, Dom } from 'main.core';
import { Counter, CounterColor, CounterStyle } from 'ui.cnt';
import { BaseField } from './base-field';

export type EmailWithCounterFieldType = {
	email: string,
	serviceName: string,
	count: number,
	isOverLimit: boolean,
	counterHintText?: string,
}

export class EmailWithCounterField extends BaseField
{
	render(params: EmailWithCounterFieldType): void
	{
		const counterNode = this.renderCounter(params.count, params.isOverLimit, params.counterHintText);
		const iconNode = this.#renderProviderIcon(params.serviceName);

		const emailContainer = Tag.render`
			<div class="mailbox-grid_email-container">
				${iconNode}
				<span class="mailbox-grid_email-text">${Text.encode(params.email)}</span>
				${counterNode}
			</div>
		`;

		this.appendToFieldNode(emailContainer);
		BX.UI.Hint.init(this.getFieldNode());
	}

	renderCounter(count: number, isOverLimit: boolean, hintText: ?string): ?HTMLElement
	{
		if (!(Type.isNumber(count) && count > 0))
		{
			return null;
		}

		const maxValue = count;
		const value = isOverLimit ? count + 1 : count;

		const counter = new Counter({
			value,
			maxValue,
			useAirDesign: true,
			style: CounterStyle.FILLED_NO_ACCENT,
		});

		const counterNode = Tag.render`
			<div class="mailbox-grid_counter-container">
				${counter.getContainer()}
			</div>
		`;

		if (Type.isStringFilled(hintText))
		{
			Dom.attr(counterNode, {
				'data-hint': hintText,
				'data-hint-no-icon': 'true',
			});
		}

		return counterNode;
	}

	#renderProviderIcon(serviceName: string): ?HTMLElement
	{
		if (!Type.isStringFilled(serviceName))
		{
			return null;
		}

		const iconKey = this.#getProviderKey(serviceName);
		const iconClass = this.#getProviderImgSrcClass(iconKey);

		return Tag.render`
			<div class="mail-provider-img-container --grid-view">
				<div class="mailbox-grid_email-icon">
					<div class="mail-provider-img ${iconClass}"></div>
				</div>
			</div>
		`;
	}

	#getProviderKey(name: string): string
	{
		switch (name)
		{
			case 'aol':
				return 'aol';
			case 'gmail':
				return 'gmail';
			case 'yahoo':
				return 'yahoo';
			case 'mail.ru':
			case 'mailru':
				return 'mailru';
			case 'icloud':
				return 'icloud';
			case 'outlook.com':
			case 'outlook':
				return 'outlook';
			case 'office365':
				return 'office365';
			case 'exchangeOnline':
			case 'exchange':
				return 'exchange';
			case 'yandex':
				return 'yandex';
			case 'ukr.net':
				return 'ukrnet';
			case 'other':
			case 'imap':
				return 'other';
			default:
				return '';
		}
	}

	#getProviderImgSrcClass(name: string): string
	{
		return `mail-provider-${name}-img`;
	}
}
