import {Dom, Tag, Type} from 'main.core';
import {Item} from '../item';
import {Animation} from './animation';

export class Drop extends Animation
{
	static DEFAULT_TIMEOUT = 150;
	static DURATION = 1200;

	item: Item;
	container: Element;
	insertAfter: Element;

	constructor(params: {
		item: Item,
		container: Element,
		insertAfter: Element,
	})
	{
		super(params);
		if(Type.isPlainObject(params))
		{
			if(params.item instanceof Item && Type.isDomNode(params.container))
			{
				this.item = params.item;
				this.container = params.container;
				this.insertAfter = params.insertAfter;
			}
		}
	}

	start(): Promise
	{
		const timeout = Drop.DEFAULT_TIMEOUT;

		return new Promise((resolve) => {
			if(!this.item || !this.container)
			{
				resolve();
			}

			setTimeout(() => {
				this.createGhost(this.item.render(), resolve);
			}, timeout);
		});
	}

	createGhost(node: Element, onFinish: Function)
	{
		node.style.position = "absolute";
		node.style.width = this.container.offsetWidth + "px";
		node.style.top = Dom.getPosition(this.container).top + "px";
		node.style.left = Dom.getPosition(this.container).left + "px";
		document.body.appendChild(node);

		this.anchor = Tag.render`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-shadow"></div>`;
		Dom.prepend(this.anchor, this.container);
		if(Type.isDomNode(this.insertAfter))
		{
			Dom.insertAfter(this.anchor, this.insertAfter);
		}

		this.moveGhost(node, onFinish);
	}

	moveGhost(node: Element, onFinish: ?Function)
	{
		const anchorPosition = Dom.getPosition(this.anchor);
		const startPosition = Dom.getPosition(this.container);

		const movingEvent = new BX.easing({
			duration : Drop.DURATION,
			start : { top: startPosition.top, height: 0},
			finish: { top: anchorPosition.top - 5, height: Dom.getPosition(node).height},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step: ((state) => {
				node.style.top = state.top + "px";
				this.anchor.style.height = state.height + "px";
			}),
			complete: (() => {
				this.finish(node, onFinish);
			})
		});

		movingEvent.animate();
	}

	finish(node: Element, onFinish: ?Function)
	{
		node.style.position = "";
		node.style.width = "";
		node.style.height = "";
		node.style.top = "";
		node.style.left = "";
		node.style.opacity = "";

		Dom.insertAfter(node, this.anchor);
		Dom.remove(this.anchor);
		this.anchor = null;

		if(Type.isFunction(onFinish))
		{
			onFinish();
		}
	}
}
