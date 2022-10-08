import {EventEmitter} from "main.core.events";
import {Tag} from "main.core";

export default class Switch extends EventEmitter
{
	static STATE_OFF = 0;
	static STATE_ON = 1;

	static #onToggleEvent = "onToggleEvent";

	#state;
	#titleContainer;
	#titles = ['on', 'off'];

	constructor(props = {})
	{
		super();
		this.setEventNamespace('BX.Location.Widget.Switch');
		this.#state = props.state;
		this.#titles = props.titles;
	}

	set state(state: string)
	{
		this.#state = state;

		if(this.#titleContainer)
		{
			this.#titleContainer.innerHTML = this.#getTitle();
		}
	}

	get state(): string
	{
		return this.#state;
	}

	#getTitle()
	{
		return this.#titles[this.#state];
	}

	render(mode: number)
	{
		this.#titleContainer = Tag.render`			
			<span class="ui-link ui-link-secondary ui-entity-editor-block-title-link">
				${this.#getTitle()}
			</span>`;

		this.#titleContainer.addEventListener(
			'click',
			(event) => {
				this.state = this.#state === Switch.STATE_OFF ? Switch.STATE_ON : Switch.STATE_OFF;
				this.emit(Switch.#onToggleEvent, {state: this.#state});
				event.stopPropagation();
				return false;
			}
		);

		this.#titleContainer.addEventListener(
			'mouseover',
			(event) => {
				event.stopPropagation();
			}
		);

		return this.#titleContainer;
	}

	subscribeOnToggleEventSubscribe(listener: Function): void
	{
		this.subscribe(Switch.#onToggleEvent, listener);
	}
}
