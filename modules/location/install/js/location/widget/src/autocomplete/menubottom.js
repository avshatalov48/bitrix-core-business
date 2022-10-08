import {EventEmitter} from 'main.core.events';
import {Tag} from 'main.core';

export default class MenuBottom extends EventEmitter
{
	#node;
	#leftItemNodeContainer;
	#rightItemNodeContainer;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Location.Widget.Autocomplete.MenuBottom');
	}

	render()
	{
		this.#leftItemNodeContainer = Tag.render`<div class="location-map-popup-item--info-left"></div>`;
		this.#rightItemNodeContainer = Tag.render`<div></div>`;

		this.#node = Tag.render`
			<div>
				<span class="location-map-popup-item--info"> 		
					${this.#leftItemNodeContainer}
					${this.#rightItemNodeContainer}
				</span>			
			</div>
		`;

		return this.#node;
	}

	setRightItemNode(node: Element): void
	{
		while (this.#rightItemNodeContainer.firstChild)
		{
			this.#rightItemNodeContainer.removeChild(this.#rightItemNodeContainer.firstChild);
		}

		this.#rightItemNodeContainer.appendChild(node);
	}

	setLeftItemNode(node: Element): void
	{
		while (this.#leftItemNodeContainer.firstChild)
		{
			this.#leftItemNodeContainer.removeChild(this.#leftItemNodeContainer.firstChild);
		}

		this.#leftItemNodeContainer.appendChild(node);
	}
}