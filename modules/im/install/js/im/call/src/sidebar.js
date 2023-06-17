import {Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import './css/sidebar.css';

const Events = {
	onClose: 'onClose',
	onDestroy: 'onDestroy',
	onCloseClicked: 'onCloseClicked',
}

export class Sidebar extends EventEmitter
{
	constructor(options)
	{
		super()
		this.setEventNamespace("BX.Call.SideBar");
		this.container = options.container;

		this.width = BX.prop.getInteger(options, 'width', 200);

		this.elements = {
			root: null,
			close: null,
			contentContainer: null,
		}

		if (options.events)
		{
			for (let eventName in options.events)
			{
				if (options.events.hasOwnProperty(eventName))
				{
					this.subscribe(eventName, options.events[eventName]);
				}
			}
		}
	}

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {
				className: "bx-messenger-call-sidebar-root"
			},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-call-sidebar-labels"},
					style: {top: '39px' /*'17px'*/},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-call-sidebar-label"},
							style: {maxWidth: '40px'},
							children: [Dom.create("div", {
								props: {className: "bx-messenger-call-sidebar-label-icon-box"},
								attrs: {title: BX.message("IM_M_CALL_BTN_CLOSE")},
								children: [
									Dom.create("div", {
										props: {className: "bx-messenger-call-sidebar-label-icon bx-messenger-call-sidebar-label-icon-close"},
									})
								]
							})],
							events: {
								click: () => this.emit(Events.onCloseClicked)
							}
						})
					]
				}),
				this.elements.contentContainer = Dom.create("div", {
					props: {className: "bx-messenger-call-sidebar-content-container"}
				})
			]
		});
		this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');

		return this.elements.root;
	}

	setWidth(width)
	{
		if (this.width == width)
		{
			return;
		}

		this.width = width;
		this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');
	}

	open(animation)
	{
		animation = animation !== false;
		return new Promise((resolve) =>
		{
			this.container.appendChild(this.render());
			if (animation)
			{
				this.elements.root.classList.add('opening');
				this.elements.root.addEventListener(
					'animationend',
					() =>
					{
						this.elements.root.classList.remove('opening');
						resolve();
					},
					{
						once: true
					}
				)
			}
			else
			{
				resolve();
			}
		})
	}

	close(animation)
	{
		animation = animation !== false;
		return new Promise((resolve) =>
		{
			if (animation)
			{
				this.elements.root.classList.add('closing');
				this.elements.root.addEventListener(
					'animationend',
					() =>
					{
						this.container.removeChild(this.elements.root);
						this.emit(Events.onClose);
						resolve();
					},
					{
						once: true
					}
				)
			}
			else
			{
				this.container.removeChild(this.elements.root);
				this.emit(Events.onClose);
				resolve();
			}
		})
	}

	toggleHidden(hidden)
	{
		this.elements.root.classList.toggle('hidden', hidden);
	}

	destroy()
	{
		this.emit(Events.onDestroy);
		this.unsubscribeAll(Events.onClose);
		this.unsubscribeAll(Events.onDestroy);
		this.eventEmitter = null;
		this.elements = null;
		this.container = null;
	}
}