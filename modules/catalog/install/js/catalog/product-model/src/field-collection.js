import {Type} from 'main.core';
import {ProductModel} from "catalog.product-model";

export class FieldCollection
{
	changedFields: Map = new Map();
	fields: Map = new Map();

	constructor(model: ProductModel = {})
	{
		this.model = model;
	}

	getFields(): {}
	{
		return Object.fromEntries(this.fields);
	}

	getField(fieldName: string): any
	{
		return this.fields.get(fieldName);
	}

	setField(fieldName: string, value: any): FieldCollection
	{
		const oldValue = this.fields.get(fieldName);
		this.fields.set(fieldName, value);
		if (!this.changedFields.has(fieldName) && oldValue !== value)
		{
			this.changedFields.set(fieldName, oldValue);
		}

		return this;
	}

	isChanged(): boolean
	{
		return (this.changedFields.size > 0);
	}

	clearChanged(savingFieldNames: [] = null): FieldCollection
	{
		if (Type.isNil(savingFieldNames))
		{
			this.changedFields.clear();
		}
		else
		{
			savingFieldNames.forEach((name) => {
				this.removeFromChanged(name)
			});
		}

		return this;
	}

	removeFromChanged(fieldName): FieldCollection
	{
		this.changedFields.delete(fieldName);

		return this;
	}

	getChangedFields(): {}
	{
		const changedFieldValues = {};

		this.fields.forEach((value, key) => {
			if (this.changedFields.has(key))
			{
				changedFieldValues[key] = value;
			}
		})

		return {...changedFieldValues};
	}

	getChangedValues(): {}
	{
		const changedFieldValues = {};

		this.changedFields.forEach((value, key) => {
			changedFieldValues[key] = value;
		})

		return {...changedFieldValues};
	}

	initFields(fields): FieldCollection
	{
		this.fields.clear();
		this.clearChanged();
		if (Type.isObject(fields))
		{
			Object.keys(fields).forEach((key) => {
				this.fields.set(key, fields[key])
			});
		}

		return this;
	}
}
