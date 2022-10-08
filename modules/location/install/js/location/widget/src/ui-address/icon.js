import {EventEmitter} from "main.core.events";
import {Tag} from "main.core";

export default class Icon extends EventEmitter
{
	static #onClickEvent = 'onClick';

	static TYPE_CLEAR = 'clear';
	static TYPE_SEARCH = 'search';
	static TYPE_LOADER = 'loader';

	#type = Icon.TYPE_SEARCH;
	#domNode;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Location.Widget.Icon');
	}

	set type(type: string): void
	{
		this.#type = type;

		if(this.#domNode)
		{
			this.#domNode.className = this.#getClassByType(this.#type);
		}
	}

	#getClassByType(iconType: string): void
	{
		let iconClass = '';

		if(iconType === Icon.TYPE_CLEAR)
		{
			iconClass = "ui-ctl-after ui-ctl-icon-btn ui-ctl-icon-clear";
		}
		else if(iconType === Icon.TYPE_SEARCH)
		{
			iconClass = "ui-ctl-after ui-ctl-icon-search";
		}
		else if(iconType === Icon.TYPE_LOADER)
		{
			iconClass = "ui-ctl-after ui-ctl-icon-loader";
		}
		else
		{
			BX.debug('Wrong icon type');
		}

		return iconClass;
	}

	render(props)
	{
		this.#type = props.type;
		this.#domNode = Tag.render`<div class="${this.#getClassByType(this.#type)}"></div>`;

		this.#domNode.addEventListener(
			'click',
			(e) => {
				this.emit(Icon.#onClickEvent);
			}
		);

		return this.#domNode;
	}

	subscribeOnClickEvent(listener: Function): void
	{
		this.subscribe(Icon.#onClickEvent, listener);
	}
}