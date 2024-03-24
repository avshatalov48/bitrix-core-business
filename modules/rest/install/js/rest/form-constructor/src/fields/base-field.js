import { Type, Text } from 'main.core';
import type { FieldConfig } from '../types';
import { EventEmitter } from 'main.core.events';

export class BaseField extends EventEmitter
{
	options: FieldConfig;
	readySave: boolean;
	value: any;

	constructor(options: FieldConfig)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoice.Field');
		this.options = options;
		this.value = this.options.value ?? null;
		this.readySave = !(Type.isNil(this.value) || this.value === '');
		this.options.id = Type.isStringFilled(this.options.id) ? this.options.id : Text.getRandom(8);
	}

	getId(): string
	{
		return this.options.id;
	}

	getName(): string
	{
		return this.options.name;
	}

	getContent(): HTMLElement
	{
		throw new Error('Must be implemented in a child class');
	}

	isReadySave(): boolean
	{
		return this.readySave;
	}

	getValue(): any
	{
		return this.value;
	}
}