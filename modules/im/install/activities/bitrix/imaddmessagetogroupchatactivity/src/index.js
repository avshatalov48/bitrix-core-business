import { Reflection, Event, Dom, Tag, Text, Type } from 'main.core';
import { Dialog } from 'ui.entity-selector';
import { MessageTemplateSelector } from 'im.robot.message-template-selector';

import 'bp_field_type';

const namespace = Reflection.namespace('BX.Im.Activity');

class ImAddMessageToGroupChatActivity
{
	#form: HTMLFormElement;
	#documentType: Array;
	#isRobot: boolean;
	#currentValues: Object;
	#chatSelector: Dialog;
	#messageTemplateFields: Object;
	#messageTemplateList: Object;
	#messageFieldsElement: ?(HTMLDivElement | HTMLTableElement);
	#messageTypeBtn: ?(HTMLDivElement | HTMLTableElement);

	constructor(parameters: {
		form: HTMLFormElement,
		isRobot: boolean,
		documentType: Array,
		currentValues: Object,
		chatFieldName: string,
		messageTemplateFields: Object,
		messageTemplateList: Object
	})
	{
		this.#form = parameters.form;
		this.#isRobot = parameters.isRobot;
		this.#documentType = parameters.documentType;
		this.#currentValues = parameters.currentValues;
		this.#messageTemplateFields = parameters.messageTemplateFields;
		this.#messageTemplateList = parameters.messageTemplateList;
		this.#messageFieldsElement = document.getElementById('id_message_fields');
		this.#messageTypeBtn = document.querySelector('[data-role="message-type"]');

		if (!Type.isPlainObject(this.#currentValues['message_fields']))
		{
			this.#currentValues['message_fields'] = {};
		}
	}

	init()
	{
		this.#initChatSelector();
		this.#initTemplateSelector();

		Event.bind(this.#chatSelector.getTargetNode(), 'click', () => {this.#chatSelector.show();});

		this.#setTemplate(this.#form['message_template'].value, true);
	}

	#setTemplate(value, forced)
	{
		if (this.#form['message_template'].value === value && !forced)
		{
			return;
		}

		if (this.#messageTypeBtn)
		{
			this.#form['message_template'].value = value;
			this.#messageTypeBtn.textContent = this.#messageTemplateList[value] || '';
		}

		this.showTemplateMessageFields(value);
	}

	showTemplateMessageFields(newMessageTemplate)
	{
		if (!this.#messageFieldsElement)
		{
			return;
		}

		Dom.clean(this.#messageFieldsElement);

		if (this.#messageTemplateFields.hasOwnProperty(newMessageTemplate))
		{
			Object.entries(this.#messageTemplateFields[newMessageTemplate]).forEach(([id, property]) => {
				Dom.append(
					this.#renderProperty(id, property),
					this.#messageFieldsElement,
				);
			})
		}
	}

	#renderProperty(id, property)
	{
		return this.#isRobot ? this.#renderRobotProperty(id, property) : this.#renderDesignerProperty(id, property);
	}

	#renderRobotProperty(id, property)
	{
		return Tag.render`
			<div class="bizproc-automation-popup-settings">
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
					${Text.encode(property.Name)}
				</span>
				${this.#renderValueElement(id, property)}
			</div>
		`;
	}

	#renderDesignerProperty(id, property)
	{
		return Tag.render`
			<tr>
				<td align="right" width="40%">
					${property.Required ? '<span class="adm-required-field">' : ''}
					${Text.encode(property.Name)}:
					${property.Required ? '</span>' : ''}
				</td>
				<td width="60%">
					${this.#renderValueElement(id, property)}
				</td>
			</tr>
		`;
	}

	#renderValueElement(id, property)
	{
		const fieldName = property['FieldName'];
		const fieldValueElement = BX.Bizproc.FieldType.renderControl(
			this.#documentType,
			property,
			fieldName,
			this.#currentValues['message_fields'][id],
			this.#isRobot ? 'public' : 'designer',
		);
		fieldValueElement.onchange = (event) => { this.#currentValues['message_fields'][id] = event.target.value; }

		return fieldValueElement;
	}

	#initChatSelector()
	{
		const chatFieldName = 'chat_id';
		const chatNode = this.#form[chatFieldName];

		this.#chatSelector = new Dialog({
			entities: [
				{
					id: 'im-chat',
					options: {
						searchableChatTypes: ['C'],
					},
				}
			],
			targetNode: chatNode,
			multiple: false,
			enableSearch: true,
			hideOnSelect: true,
			height: 300,
			width: 490,
			autoHide: true,
			compactView: true,
			showAvatars: false,
			dropdownMode: true,
			events: {
				'Item:onBeforeSelect': (event) =>
				{
					event.preventDefault();
					chatNode.value = event.getData().item.getId();
				}
			}
		});
		this.#chatSelector.load();
	}

	#initTemplateSelector()
	{
		if (!this.#isRobot)
		{
			Event.bind(this.#form['message_template'], 'change', (event) => {
				this.#setTemplate(event.target.value, true);
			});

			return;
		}

		const selector = new MessageTemplateSelector();

		Event.bind(this.#messageTypeBtn, 'click', () => {
			selector.show(this.#messageTypeBtn, this.#form['message_template'].value);
		});

		selector.subscribe('select', (event) => {
			this.#setTemplate(event.getData().selected);
		});
	}
}

namespace.ImAddMessageToGroupChatActivity = ImAddMessageToGroupChatActivity;
