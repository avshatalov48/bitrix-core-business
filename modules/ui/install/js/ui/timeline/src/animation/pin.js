import {Dom, Type} from 'main.core';
import {Item} from '../item';
import {Animation} from './animation';

export class Pin extends Animation
{
	static DURATION = 1500;

	item: Item;
	anchor: Element;
	node: Element;
	startPosition: {};

	constructor(params: {
		item: Item,
		anchor: Element,
		startPosition: {},
	})
	{
		super(params);
		if(Type.isPlainObject(params))
		{
			if(params.item instanceof Item && Type.isDomNode(params.anchor))
			{
				this.item = params.item;
				this.anchor = params.anchor;
				this.startPosition = params.startPosition;
			}
		}
	}

	start(): Promise
	{
		return new Promise((resolve) => {
			if(!this.item || !this.anchor)
			{
				resolve();
			}

			this.node = this.item.render();
			Dom.addClass(this.node, 'ui-item-detail-stream-section-top-fixed');

			this.node.style.position = "absolute";
			this.node.style.width = this.startPosition.width + "px";

			let _cloneHeight = this.startPosition.height;
			const _minHeight = 65;
			const _sumPaddingContent = 18;
			if (_cloneHeight < _sumPaddingContent + _minHeight)
				_cloneHeight = _sumPaddingContent + _minHeight;

			this.node.style.height = _cloneHeight + "px";
			this.node.style.top = this.startPosition.top + "px";
			this.node.style.left = this.startPosition.left + "px";
			this.node.style.zIndex = 960;

			document.body.appendChild(this.node);

			this._anchorPosition = Dom.getPosition(this.anchor);
			const finish = {
				top: this._anchorPosition.top,
				height: _cloneHeight + 15,
				opacity: 1
			};

			const _difference = this.startPosition.top - this._anchorPosition.bottom;
			const _deepHistoryLimit = 2 * (document.body.clientHeight + this.startPosition.height);

			if (_difference > _deepHistoryLimit)
			{
				finish.top = this.startPosition.top - _deepHistoryLimit;
				finish.opacity = 0;
			}

			let _duration = Math.abs(finish.top - this.startPosition.top) * 2;
			_duration = (_duration < Pin.DURATION) ? Pin.DURATION : _duration;

			const movingEvent = new BX.easing({
				duration : _duration,
				start : {
					top: this.startPosition.top,
					height: 0,
					opacity: 1,
				},
				finish: finish,
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {
					this.node.style.top = state.top + "px";
					this.node.style.opacity = state.opacity;
					this.anchor.style.height = state.height + "px";
				},
				complete: () => {
					this.finish(this.node, resolve);
				},
			});
			movingEvent.animate();
		});
	}

	finish(node: Element, onFinish: ?Function)
	{
		node.style.position = "";
		node.style.width = "";
		node.style.height = "";
		node.style.top = "";
		node.style.left = "";
		node.style.zIndex = "";
		this.anchor.style.height = 0;

		Dom.insertAfter(node, this.anchor);

		if(Type.isFunction(onFinish))
		{
			onFinish();
		}
	}
}