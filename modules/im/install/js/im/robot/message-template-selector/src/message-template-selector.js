import { Dom, Tag, Text, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button } from 'ui.buttons';

import './style.css';

type MessageTemplate = {
	id: string,
	name: string,
	description: string,
};

type TemplateRow = {
	template: HTMLDivElement,
	radioButton: HTMLInputElement,
};

export class MessageTemplateSelector extends EventEmitter
{
	#rows: Array<TemplateRow> = [];

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.IM.Robot.MessageTemplateSelector');
	}

	show(bindElement: ?(HTMLDivElement | HTMLTableElement), selected: string)
	{
		const popup = new Popup({
			bindElement: bindElement,
			width: 431,
			padding: 20,
			content: this.#createControlNode(selected),
			closeByEsc: true,
			events: {
				onClose: () => {
					this.#rows = [];
				},
			},
			buttons: [
				new Button({
					text: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_BUTTON_OK'),
					color: Button.Color.PRIMARY,
					events: {
						click: () => {

							const form = popup.getContentContainer().querySelector('form');
							const value = (new FormData(form)).get('select-type-message');

							this.emit('select', { selected: value });

							popup.close();
						},
					},
				}),
				new Button({
					text: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_BUTTON_CANCEL'),
					color: Button.Color.LINK,
					events: {
						click: () => {
							popup.close();
						},
					},
				}),
			],
			autoHide: true,
			closeIcon: false,
			titleBar: false,
			angle: true,
		});

		popup.setCacheable(false);
		popup.show();
	}

	#createControlNode(selected: string)
	{
		const templates = this.#getTemplates();

		templates.forEach((template) => {

			const isSelected = template.id === selected;

			const { root: templateRow, templateRadio } = Tag.render`
				<div class="bizproc-automation-popup-settings__select-type_row ${isSelected ? '--active' : ''}">
						<label class="bizproc-automation-popup-settings__select-type_info">
							<div class="bizproc-automation-popup-settings__select-type_info-name ui-ctl ui-ctl-radio ui-ctl-wa">
								<input ref="templateRadio" type="radio" ${isSelected ? 'checked' : ''} onclick="${this.#onTemplateSelect.bind(this)}" name="select-type-message" value="${Text.encode(template.id)}" class="ui-ctl-element bizproc-automation-popup-settings__select-type_info-input">
								${Text.encode(template.name)}
							</div>
							<div class="bizproc-automation-popup-settings__select-type_info-description">
								${Text.encode(template.description)}
							</div>
						</label>
						<div class="bizproc-automation-popup-settings__select-type_images">
							<img src="/bitrix/js/im/robot/message-template-selector/images/template-${Text.encode(template.id)}.svg" alt="${Text.encode(template.name)}">
						</div>
					</div>
			`;

			this.#rows.push({
				template: templateRow,
				radioButton: templateRadio,
			});
		});

		return Tag.render`
			<form class="bizproc-automation-popup-settings__select-type">
				${this.#rows.map((row) => row.template)}
			</form>
		`;
	}

	#onTemplateSelect()
	{
		for (const row of this.#rows)
		{
			if (row.radioButton.checked)
			{
				Dom.addClass(row.template, '--active');
			}
			else
			{
				Dom.removeClass(row.template, '--active');
			}
		}
	}

	#getTemplates(): Array<MessageTemplate>
	{
		return [
			{
				id: 'plain',
				name: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_PLAIN_NAME'),
				description: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_PLAIN_DESC'),
			},
			{
				id: 'news',
				name: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NEWS_NAME'),
				description: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NEWS_DESC'),
			},
			{
				id: 'notify',
				name: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NOTIFY_NAME'),
				description: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NOTIFY_DESC'),
			},
			{
				id: 'important',
				name: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_IMPORTANT_NAME'),
				description: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_IMPORTANT_DESC'),
			},
			{
				id: 'alert',
				name: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_ALERT_NAME'),
				description: Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_ALERT_DESC'),
			},
		];
	}
}
