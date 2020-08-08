import {Dom, Type} from 'main.core';
import {Animation} from './animation';

export class Hide extends Animation
{
	static DURATION = 1000;

	node: Element;

	constructor(params: {
		node: Element,
	})
	{
		super(params);
		if(Type.isPlainObject(params))
		{
			if(Type.isDomNode(params.node))
			{
				this.node = params.node;
			}
		}
	}

	start(): Promise
	{
		return new Promise((resolve) => {
			if(!this.node)
			{
				resolve();
			}
			const node = this.node;
			const wrapperPosition = Dom.getPosition(node);

			const hideEvent = new BX.easing({
				duration : Hide.DURATION,
				start : {
					height: wrapperPosition.height,
					opacity: 1,
					marginBottom: 15
				},
				finish: {
					height: 0,
					opacity: 0,
					marginBottom: 0
				},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {
					if(node)
					{
						node.style.height = state.height + "px";
						node.style.opacity = state.opacity;
						node.style.marginBottom = state.marginBottom;
					}
				},
				complete: () => {
					this.finish(node, resolve);
				}
			});

			hideEvent.animate();
		});
	}

	finish(node: Element, onFinish: ?Function)
	{
		Dom.remove(node);

		if(Type.isFunction(onFinish))
		{
			onFinish();
		}
	}
}