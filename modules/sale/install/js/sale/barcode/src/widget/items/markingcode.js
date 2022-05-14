import {Tag, Event} from 'main.core';

export default class Markingcode
{
	constructor(props)
	{
		this._id = props.id || 0;
		this._input = null;
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
		let readonly = this._readonly ? ' readonly="readonly"' : ''
		this._input = Tag.render`<input type="text" ${readonly}>`;
		this._input.value = this._value;

		Event.bind(this._input, 'keypress', this.onKeyPress.bind(this));
		Event.bind(this._input, 'change', this.onChange.bind(this));

		return this._input;
	}

	onChange(e)
	{
		this._value = e.target.value;
		this._eventEmitter.emit('onChange', this);
	}

	onKeyPress(e)
	{
		/**
		 * @see https://stackoverflow.com/questions/48296955/ascii-control-character-html-input-text
		 */
		if (e.charCode === 29)
		{
			this._input.value += String.fromCharCode(e.which);
		}
	}

	onChangeSubscribe(callback)
	{
		this._eventEmitter.subscribe('onChange', callback);
	}
}
