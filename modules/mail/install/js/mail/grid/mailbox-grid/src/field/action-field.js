import { BaseField } from './base-field';
import { Loc, Tag, Dom } from 'main.core';
import { Button, AirButtonStyle } from 'ui.buttons';

type ActionFieldParams = {
	url: string;
	hasError: ?boolean,
}

export class ActionField extends BaseField
{
	render(params: ActionFieldParams): void
	{
		const actionContainer = Tag.render`
			<div class="mailbox-grid_action-field-container"></div>
		`;

		let button = null;
		if (params.hasError)
		{
			button = new Button({
				size: Button.Size.MEDIUM,
				text: Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_ERROR_ACTION'),
				useAirDesign: true,
				noCaps: true,
				wide: false,
				onclick: () => {
					this.#handleClick(params.url);
				},
				className: 'mailbox-grid_action-button',
				dataset: { id: 'mailbox-grid_action-button-error-action' },
			});

			const buttonNode = button.render();
			Dom.append(buttonNode, actionContainer);
			Dom.append(this.#getErrorMessage(), actionContainer);
		}
		else
		{
			button = new Button({
				size: Button.Size.MEDIUM,
				text: Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_TITLE'),
				useAirDesign: true,
				style: AirButtonStyle.TINTED,
				noCaps: true,
				wide: false,
				onclick: () => {
					this.#handleClick(params.url);
				},
				className: 'mailbox-grid_action-button',
				dataset: { id: 'mailbox-grid_action-button-default-action' },
			});

			const buttonNode = button.render();
			Dom.append(buttonNode, actionContainer);
		}

		this.appendToFieldNode(actionContainer);
	}

	#handleClick(url: string): void
	{
		BX.SidePanel.Instance.open(url);
	}

	#getErrorMessage(): HTMLElement
	{
		return Tag.render`
			<span class="mailbox-grid_action-field-error-container">
				<div class="mailbox-grid_action-field-error-icon ui-icon-set --warning-alarm"></div>
				<span>${Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_ERROR_MESSAGE')}</span>
			</span>
		`;
	}
}
