import {Tag, Event} from 'main.core';

export default class Markingcode
{
	constructor(props)
	{
		this._id = props.id || 0;
		this._value = props.value || '';
		this._readonly = props.readonly;
		this._eventEmitter = new Event.EventEmitter();
	}

	get id()
	{
		return this._id;
	}

	get value()
	{
		return this._value;
	}

	render()
	{
		let readonly = this._readonly ? ' readonly="readonly"' : '',
			input = Tag.render`<input type="text" onchange="${this.onChange.bind(this)}"${readonly}>`;

		input.value = this._value;
		return input;
	}

	onChange(e)
	{
		this._value = e.target.value;
		this._eventEmitter.emit('onChange', this);
	}

	onChangeSubscribe(callback)
	{
		this._eventEmitter.subscribe('onChange', callback);
	}
}