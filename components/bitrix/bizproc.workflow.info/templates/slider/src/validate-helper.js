import { Type, Loc } from 'main.core';
import { TaskField, TaskFieldError } from './types';

export class ValidateHelper
{
	static #NEED_PATTERN = new Set([
		'S:HTML',
		'email',
		'phone',
		'web',
		'im',
		'E:EList',
	]);

	static #MULTIPLE_WITH_NO_BRACKETS = new Set([
		...ValidateHelper.#NEED_PATTERN,
		'user',
		'S:employee',
		'sms_sender',
		'mail_sender',
	]);

	static checkRequiredFieldsFilled(formData: FormData, requiredFields: Array<TaskField>): Array<TaskFieldError>
	{
		const errors = [];

		for (const requiredField of requiredFields)
		{
			const fieldId = this.#getFieldId(requiredField);
			const values = this.#getFieldValues(formData, requiredField, fieldId);

			if (!Type.isArrayFilled(values))
			{
				const originalFieldId = requiredField.FieldId ?? requiredField.Id;
				if (!formData.keys().every((key) => !key.includes(originalFieldId)))
				{
					continue;
				}
			}

			if (this.#isFieldEmpty(requiredField, values))
			{
				errors.push({
					message: Loc.getMessage('BPWFI_SLIDER_ARGUMENT_NULL', { '#PARAM#': requiredField.Name }),
					fieldId: requiredField.Id,
				});
			}
		}

		return errors;
	}

	static #getFieldId(field: TaskField): string
	{
		let fieldId = Type.isNil(field.FieldId) ? field.Id : field.FieldId;
		if (field.Multiple || field.Type === 'S:DiskFile')
		{
			fieldId = ValidateHelper.#MULTIPLE_WITH_NO_BRACKETS.has(field.Type)
				? fieldId
				: `${fieldId}[]`;
		}

		return fieldId;
	}

	static #getFieldValues(formData: FormData, field: TaskField, fieldId: string): Array
	{
		return ValidateHelper.#NEED_PATTERN.has(field.Type)
			? this.#getPatternValues(formData, this.#getPattern(field, fieldId))
			: formData.getAll(fieldId);
	}

	static #isFieldEmpty(field: TaskField, values: any): boolean
	{
		if (field.Multiple || field.Type === 'S:DiskFile' || ValidateHelper.#NEED_PATTERN.has(field.Type))
		{
			return !Type.isArrayFilled(values) || values.every((value) => this.#isValueEmpty(field, value));
		}

		return this.#isValueEmpty(field, values[0]);
	}

	static #isValueEmpty(field: TaskField, value: any): boolean
	{
		if (field.Type === 'file')
		{
			return Type.isFile(value) && value.name === '';
		}

		return !Type.isStringFilled(value);
	}

	static #getPattern(field: TaskField, fieldId: string): string
	{
		if (field.Type === 'S:HTML')
		{
			return field.Multiple ? `${fieldId}\\[n\\d+\\]\\[TEXT\\]` : `${fieldId}\\[TEXT\\]`;
		}

		if (field.Type === 'E:EList')
		{
			return field.Multiple ? `${fieldId}\\[[n]?\\d+\\]\\[VALUE\\]` : `^${fieldId}$`;
		}

		if (field.Type === 'email' || field.Type === 'phone' || field.Type === 'web' || field.Type === 'im')
		{
			return `${fieldId}\\[${field.Type.toUpperCase()}\\]\\[n\\d+\\]\\[VALUE\\]`;
		}

		return '';
	}

	static #getPatternValues(formData: FormData, pattern: string): []
	{
		const values = [];
		for (const [key, value] of formData.entries())
		{
			if (new RegExp(pattern).test(key))
			{
				values.push(value);
			}
		}

		return values;
	}
}
