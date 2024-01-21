import { BaseSettingsElement } from './base-settings-element';
import { BaseField } from 'ui.form-elements.view';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { ErrorCollection } from './error-collection';

export class SettingsField extends BaseSettingsElement
{
	#fieldView: BaseField;

	constructor(params)
	{
		super(params);
		if (!(params.fieldView instanceof BaseField))
		{
			throw new Error(`Unexpected field type, expected "BaseField"`);
		}
		this.#fieldView = params.fieldView;
		EventEmitter.subscribe('BX.UI.FormElement.Field:onFailedSave', this.#onFailedSave.bind(this));
	}

	getFieldView(): BaseField
	{
		return this.#fieldView;
	}

	render(): HTMLElement
	{
		return this.getFieldView().render();
	}

	renderErrors(): HTMLElement
	{
		this.getFieldView().setErrors(this.getErrorCollection().getAll());

		return this.getFieldView().renderErrors();
	}

	#extractErrorsFromEvent(event: BaseEvent): Array<String>
	{
		let errors = {};
		for (const type in event.data.errors)
		{
			errors = { ...errors, ...event.data.errors[type] };
		}

		return errors[this.getFieldView().getName()] ?? [];
	}

	#onFailedSave(event: BaseEvent)
	{
		let fieldErrors = this.#extractErrorsFromEvent(event);
		this.getErrorCollection().clear();
		this.setErrorCollection(new ErrorCollection(fieldErrors));
		this.renderErrors();
	}
}