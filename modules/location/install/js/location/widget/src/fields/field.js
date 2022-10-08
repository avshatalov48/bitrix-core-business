import {Tag, Event, Dom, Text} from 'main.core';
import {EventEmitter} from 'main.core.events';
import 'ui.forms';
import {ControlMode} from 'location.core';
import State from '../state';

export default class Field extends EventEmitter
{
	static #onValueChangedEvent = 'onValueChanged';
	static #onStateChangedEvent = 'onStateChanged';

	#title;
	#value;
	#type;
	#sort;
	#mode;
	#input;
	#viewContainer;
	#container = null;
	#state = State.INITIAL;

	constructor(props: FieldConstructorProps)
	{
		super(props);
		this.setEventNamespace('BX.Location.Widget.Field');

		this.#title = props.title;
		this.#type = props.type;
		this.#sort = props.sort;
	}

	get container()
	{
		return this.#container;
	}

	get state()
	{
		return this.#state;
	}

	#setState(state: string)
	{
		this.#state = state;
		this.emit(Field.#onStateChangedEvent, {state: this.#state});
	}

	render(props: FieldRenderProps): void
	{
		this.#value = typeof props.value === 'string' ? props.value : '';

		if(!ControlMode.isValid(props.mode))
		{
			BX.debug('props.mode must be valid ControlMode');
		}

		this.#mode = props.mode;

		this.#container = Tag.render`
			<div class="ui-entity-editor-content-block ui-entity-editor-field-text">
				<div class="ui-entity-editor-block-title">
					<label class="ui-entity-editor-block-title-text">${this.#title}:</label>				
				</div>
			</div>`;

		if(this.#mode === ControlMode.edit)
		{
			this.#renderEditMode(this.#container)
		}
		else
		{
			this.#renderViewMode(this.#container)
		}

		return this.#container;
	}

	#renderEditMode(container: Element)
	{
		this.#input = Tag.render`<input type="text" class="ui-ctl-element" value="${Text.encode(this.#value)}">`;
		this.#viewContainer = null;

		Event.bind(this.#input, 'focus', (e) => {
			this.#setState(State.DATA_INPUTTING);
		});

		Event.bind(this.#input, 'focusout', (e) => {
			this.#setState(State.DATA_SELECTED);
		});

		Event.bind(this.#input, 'change', (e) => {
			this.#setState(State.DATA_SELECTED);
			this.#value = this.#input.value;
			this.emit(Field.#onValueChangedEvent, {value: this});
		});

		container.appendChild(
			Tag.render`
				<div class="ui-entity-editor-content-block">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						${this.#input}
					</div>
				</div>`
		);
	}

	#renderViewMode(container: Element)
	{
		this.#input = null;

		this.#viewContainer = Tag.render`
			<div class="ui-title-6">
				${Text.encode(this.#value)}
			</div>`;

		container.appendChild(this.#viewContainer);
	}

	#refreshLayout()
	{
		if(this.#mode === ControlMode.edit)
		{
			this.#input.value = this.#value;
		}
		else
		{
			this.#viewContainer.innerHTML = Text.encode(this.#value);
		}
	}

	set type(type: number)
	{
		this.#type = type;
	}

	get type(): number
	{
		return this.#type;
	}

	set sort(sort: number)
	{
		this.#sort = sort;
	}

	get sort(): number
	{
		return this.#sort;
	}

	set value(value: string)
	{
		this.#value = typeof value === 'string' ? value : '';
		this.#refreshLayout();
	}

	get value(): string
	{
		return this.#value;
	}

	subscribeOnValueChangedEvent(listener: Function): void
	{
		this.subscribe(Field.#onValueChangedEvent, listener);
	}

	subscribeOnStateChangedEvent(listener: Function): void
	{
		this.subscribe(Field.#onStateChangedEvent, listener);
	}

	destroy()
	{
		Dom.remove(this.#container);
		Event.unbindAll(this);
		this.#container = null;
	}
}

type FieldConstructorProps = {
	title: string
}

type FieldRenderProps = {
	value: string,
	mode: string //ControlMode
}