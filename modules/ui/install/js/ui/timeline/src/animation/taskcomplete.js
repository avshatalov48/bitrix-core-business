import {Dom, Tag, Type} from 'main.core';
import {Item} from '../item';
import {Animation} from './animation';

export class TaskComplete extends Animation
{
	static DURATION = 1200;

	task: Item;
	item: Item;
	insertAfter: Element;

	constructor(params: {
		item: Item,
		task: Item,
		insertAfter: Element,
	})
	{
		super(params);
		if(Type.isPlainObject(params))
		{
			if(
				params.item instanceof Item &&
				params.task instanceof Item &&
				Type.isDomNode(params.insertAfter)
			)
			{
				this.item = params.item;
				this.task = params.task;
				this.insertAfter = params.insertAfter;
			}
		}
	}

	start(): Promise
	{
		return new Promise((resolve) => {
			if(!this.item || !this.task || !this.container || !this.insertAfter)
			{
				resolve();
			}

			const node = this.item.render();
			const taskNode = this.task.getContainer();
			const startPosition = Dom.getPosition(taskNode);

			node.style.position = "absolute";
			node.style.width = taskNode.offsetWidth + "px";
			node.style.top = startPosition.top + "px";
			node.style.left = startPosition.left + "px";
			node.style.zIndex = "999";
			Dom.addClass(node, 'ui-item-detail-stream-section-show');
			document.body.appendChild(node);

			this.anchor = Tag.render`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-shadow"></div>`;
			Dom.prepend(this.anchor, this.container);
			if(Type.isDomNode(this.insertAfter))
			{
				Dom.insertAfter(this.anchor, this.insertAfter);
			}

			taskNode.style.height = taskNode.offsetHeight + 'px';
			Dom.addClass(taskNode, 'ui-item-detail-stream-section-hide');

			setTimeout(function() {
				const taskHeight = taskNode.offsetHeight;
				this.anchor.style.height = taskHeight + "px";
				Dom.remove(taskNode);

				Dom.removeClass(node, 'ui-item-detail-stream-section-show');

				const movingEvent = new BX.easing({
					duration : 800,
					start : { top: Dom.getPosition(node).top, height: taskHeight},
					finish: { top: Dom.getPosition(this.anchor).top, height: Dom.getPosition(node).height},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step: ((state) => {
						node.style.top = state.top + "px";
						this.anchor.style.height = state.height + "px";
					}),
					complete: (() => {
						this.finish(node, resolve);
					})
				});
				movingEvent.animate();
			}.bind(this), 200);
		});
	}

	finish(node: Element, onFinish: ?Function)
	{
		node.style.position = "";
		node.style.width = "";
		node.style.top = "";
		node.style.left = "";
		node.style.zIndex = "";

		Dom.insertAfter(node, this.anchor);
		Dom.remove(this.anchor);
		this.anchor = null;

		if(Type.isFunction(onFinish))
		{
			onFinish();
		}
	}
}