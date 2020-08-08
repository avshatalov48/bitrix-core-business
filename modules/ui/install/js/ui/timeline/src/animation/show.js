import {Dom, Type} from 'main.core';
import {Item} from '../item';
import {Animation} from './animation';

export class Show extends Animation
{
	static EXPAND_DURATION = 150;
	static FADE_IN_DURATION = 150;

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
			if(params.item instanceof Item && Type.isDomNode(params.container) && Type.isDomNode(params.insertAfter))
			{
				this.item = params.item;
				this.container = params.container;
				this.insertAfter = params.insertAfter;
			}
		}
	}

	start(): Promise
	{
		return new Promise((resolve) => {
			if(!this.item || !this.container || !this.insertAfter)
			{
				resolve();
			}

			Dom.insertAfter(this.item.render(), this.insertAfter);

			this.expand().then(() => {
				this.fadeIn().then(() => {
					this.finish(this.item.getContainer(), resolve);
				})
			});
		});
	}

	expand(): Promise
	{
		return new Promise((resolve) => {
			const node = this.item.getContainer();

			const position = Dom.getPosition(node);
			node.style.height = 0;
			node.style.opacity = 0;
			node.style.overflow = 'hidden';

			const show = new BX.easing({
				duration: Show.EXPAND_DURATION,
				start: {
					height: 0,
				},
				finish: {
					height: position.height
				},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {
					node.style.height = state.height + 'px';
				},
				complete: resolve,
			});

			show.animate();
		});
	}

	fadeIn(): Promise
	{
		return new Promise((resolve) => {
			this.item.getContainer().style.overflow = '';
			const fadeIn = new BX.easing({
				duration: Show.FADE_IN_DURATION,
				start: {
					opacity: 0,
				},
				finish: {
					opacity: 100,
				},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {
					this.item.getContainer().style.opacity = state.opacity / 100;
				},
				complete: resolve,
			});

			fadeIn.animate();
		});
	}

	finish(node: Element, onFinish: ?Function)
	{
		this.item.getContainer().style.height = "";
		this.item.getContainer().style.opacity = "";

		if(Type.isFunction(onFinish))
		{
			onFinish();
		}
	}
}