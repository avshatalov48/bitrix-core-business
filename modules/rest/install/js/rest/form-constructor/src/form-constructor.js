import { Dom, Tag, ajax, Type, Text, Loc } from 'main.core';
import { StepByStep } from 'ui.stepbystep';
import { DropdownList } from './fields/dropdown-list';
import { Input } from './fields/input';
import { BaseField } from './fields/base-field';
import type { SettingsEInvoiceOptions, StepEInvoiceSettingsConfig } from './types';
import { BaseEvent, EventEmitter } from 'main.core.events';

export class FormConstructor extends EventEmitter
{
	#options: SettingsEInvoiceOptions;
	#fields: Array<BaseField>;
	#stepByStep: StepByStep;

	constructor(options: SettingsEInvoiceOptions)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoice');
		if (!Type.isArray(options.steps))
		{
			throw new Error('Unexpected property type  "steps", expected type array');
		}
		this.#options = options;
		this.#fields = [];

		this.#stepByStep = new StepByStep({
			content: this.#getContentConfig(),
		});
	}

	getFields()
	{
		return this.#fields;
	}

	render(): HTMLElement
	{
		return this.#stepByStep.getContentWrapper();
	}

	renderTo(target: HTMLElement)
	{
		this.#stepByStep.target = target;
		this.#stepByStep.init();
	}

	#getContentConfig(): Object
	{
		const contentConfig = [];

		this.#options.steps.forEach((item) => {
			const stepConfig = {
				html: [{
					backgroundColor: '#ffffff',
				}],
			};

			if (item.title)
			{
				stepConfig.html[0].header = {
					title: item.title,
				};
			}

			stepConfig.html[0].node = this.#getStepContent(item);
			contentConfig.push(stepConfig);
		});

		return contentConfig;
	}

	#getStepContent(stepConfig: StepEInvoiceSettingsConfig): HTMLElement
	{
		const wrapper = Tag.render`
			<div class="bitrix-einvoice-settings-step__wrapper"></div>
		`;

		if (stepConfig.description)
		{
			const description = Tag.render`
				<div class="bitrix-einvoice-settings-step__description">${stepConfig.description}</div>
			`;
			Dom.append(description, wrapper);
		}

		if (stepConfig.fields)
		{
			stepConfig.fields.forEach((fieldConfig, index) => {
				let field;

				switch (fieldConfig.type)
				{
					case 'input':
						field = new Input(fieldConfig);
						break;
					case 'dropdown-list':
						field = new DropdownList(fieldConfig);
						break;
					default:
						throw new Error('Incorrect field type');
				}

				if (field instanceof BaseField)
				{
					field.subscribe('onReadySave', () => {
						this.emit('onReadySave');
					});
					field.subscribe('onUnreadySave', () => {
						this.emit('onUnreadySave');
					});
					field.subscribe('onFieldChange', (event) => {
						this.emit('onFieldChange', event);
					});
					this.#fields.push(field);
					const fieldContent = field.getContent();
					Dom.append(fieldContent, wrapper);

					if (index > 0)
					{
						Dom.style(fieldContent, 'margin-top', '12px');
					}
				}
			});
		}

		if (stepConfig.link && stepConfig.link.url.startsWith('https://'))
		{
			const linkArticle = Tag.render`
				<div class="bitrix-einvoice-settings-step-wrapper-link">
					<a href="${stepConfig.link.url}" class="bitrix-einvoice-settings-step__link">${Text.encode(stepConfig.link.name)}</a>
				</div>
			`;
			Dom.append(linkArticle, wrapper);
		}

		return wrapper;
	}

	getFormData(): Object
	{
		let result = {};

		this.#fields.forEach((field) => {
			if (field.isReadySave())
			{
				result[field.getName()] = field.getValue();
			}
		});

		return result
	}

	/*
	errors = {
		fieldName: ['error message']
	}
	 */
	showFieldErrors(errors: Object): void
	{
		for (const [fieldName, messages] of Object.entries(errors)) {
			this.#fields.forEach((field) => {
				if (field.getName() === fieldName)
				{
					field.emit('error', new BaseEvent({
						data: {
							messages: messages,
						},
					}));
				}
			});
		}
	}

	showTextInBalloon(text: string):  void
	{
		BX.UI.Notification.Center.notify({
			id: 'einvoice-error-save-settings',
			content: Tag.render`
						<div class="bitrix-einvoice-settings-notification-wrapper">
							<span class="ui-icon-set --warning"></span>
							${text}
						</div>
					`,
			autoHideDelay: 5000,
		});
	}
}