import {Event} from "main.core";

export class Control
{
	node: ?HTMLElement;
	parent: ?Control;
	defaultValue: string;
	onChange: function;

	constructor(node: HTMLElement)
	{
		this.node = node;

		return this;
	}

	setParent(parent: Control)
	{
		this.parent = parent;

		return this;
	}

	setDefaultValue(defaultValue: string)
	{
		this.defaultValue = defaultValue;

		return this;
	}

	setChangeHandler(onChange: function)
	{
		Event.bind(this.node, "change", onChange);
	}

	setClickHandler(onClick: function)
	{
		Event.bind(this.node, "click", onClick);
	}

	getValue(): string
	{
		return (this.parent && this.parent.getValue() !== true)
			? this.defaultValue
			: this.getValueInternal()
		;
	}

	/**
	 * @private
	 */
	getValueInternal()
	{
		//if(this.node.type === 'checkbox')
		//{
		//	return this.node.checked;
		//}

		//return this.node.value;
		return this.node;
	}
}