import {Tag, Cache, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {IColorValue} from '../types/i_color_value';

export default class BaseProcessor extends EventEmitter
{
	variableName: string | [string];
	className: string;
	property: string | [string];
	options: [];
	pseudoClass: ?string;

	constructor(options: {})
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.property = 'color';
		this.options = options;
		this.pseudoClass = null;
	}

	getProperty(): [string]
	{
		return Type.isArray(this.property)
			? this.property
			: [this.property];
	}

	getVariableName(): [string]
	{
		return Type.isArray(this.variableName)
			? this.variableName
			: [this.variableName];
	}

	isNullValue(value: ?string): boolean
	{
		return value === null;
	}

	getPseudoClass(): ?string
	{
		return this.pseudoClass;
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return this.buildLayout();
		});
	}

	buildLayout(): HTMLElement
	{
		return Tag.render`<div>Base processor</div>`;
	}

	getClassName(): [string]
	{
		return [this.className];
	}

	getValue(): ?IColorValue
	{
	}

	getStyle(): {string: ?string}
	{
		if (this.getValue() === null)
		{
			return {[this.getVariableName()]: null};
		}

		return {[this.getVariableName()]: this.getValue().getStyleString()};
	}

	/**
	 * Set value by new format
	 * @param value {string: string}
	 */
	setProcessorValue(value: {string: string}): void
	{
		// Just get last css variable
		const processorProperty = this.getVariableName()[this.getVariableName().length - 1];
		this.setValue(value[processorProperty]);
	}

	/**
	 * Set old-type value by computedStyle
	 * @param value {string: string} | null
	 */
	setDefaultValue(value: {string: string} | null): void
	{
		if (value !== null)
		{
			const inlineProperty = this.getProperty()[this.getProperty().length - 1];
			if (inlineProperty in value)
			{
				this.setValue(value[inlineProperty]);
				this.unsetActive();

				return;
			}
		}
		this.setValue(null);
	}

	setValue(value: string | {string: string} | null): void
	{
	}

	onReset()
	{
		this.emit('onReset');
	}

	unsetActive()
	{
	}

	onChange()
	{
		this.cache.delete('value');
		this.emit('onChange');
	}
}