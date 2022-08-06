import {Tag, Cache, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import ColorValue from '../color_value';
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
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BaseProcessor');
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
		return Type.isNull(value);
	}

	getNullValue(): IColorValue
	{
		return new ColorValue;
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
		if (Type.isNull(this.getValue()))
		{
			return {[this.getVariableName()]: null};
		}

		return {[this.getVariableName()]: this.getValue().getStyleString()};
	}

	/**
	 * Set value by new format
	 * @param value {string: string}
	 */
	setProcessorValue(value: {string: string})
	{
		// Just get last css variable
		const processorProperty = this.getVariableName()[this.getVariableName().length - 1];
		this.cache.delete('value');
		this.setValue(value[processorProperty]);
	}

	/**
	 * Set old-type value by computedStyle
	 * @param value {string: string} | null
	 */
	setDefaultValue(value: {string: string} | null)
	{
		if (!Type.isNull(value))
		{
			const inlineProperty = this.getProperty()[this.getProperty().length - 1];
			if (inlineProperty in value)
			{
				this.setValue(value[inlineProperty]);
				this.cache.delete('value');
				this.unsetActive();

				return;
			}
		}
		this.setValue(null);
		this.cache.set('value', null);
	}

	setValue(value: string | {string: string} | null)
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

	defineActiveControl(items, currentNode)
	{
	}

	setActiveControl(controlName)
	{
	}

	prepareProcessorValue(processorValue, defaultValue, data)
	{
		return processorValue;
	}
}