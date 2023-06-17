import {Dom, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Popup} from 'main.popup';
import {BackgroundDialog} from './background_dialog';
import {View} from '../view/view';
import '../css/promo-popup.css';

const Events = {
	onActionClick: 'onActionClick',
	onClose: 'onClose'
};

export class PromoPopup
{
	constructor(options)
	{
		options = Type.isPlainObject(options) ? options : {};
		this.promoCode = Type.isStringFilled(options.promoCode) ? options.promoCode : '';
		this.bindElement = options.bindElement;

		this.elements = {
			root: null
		};
		this.popup = null;
		this.dontShowAgain = false;
		this.eventEmitter = new EventEmitter(this, "BX.Call.PromoPopup");

		if (options.events)
		{
			this.subscribeToEvents(options.events);
		}
	};

	subscribeToEvents(events)
	{
		for (let eventName in events)
		{
			if (events.hasOwnProperty(eventName))
			{
				this.eventEmitter.subscribe(eventName, events[eventName]);
			}
		}
	};

	render()
	{
		this.elements.root = Dom.create("div", {
			props: {className: "bx-call-promo-container"},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-promo-content"},
					children: [
						Dom.create("div", {
							props: {className: "bx-call-promo-icon-section"},
							children: [
								Dom.create("div", {
									props: {className: "bx-call-promo-icon"}
								})
							]
						}),
						Dom.create("div", {
							props: {className: "bx-call-promo-text-section"},
							children: [
								Dom.create("div", {
									props: {className: "bx-call-promo-title"},
									text: BX.message("IM_CALL_DOCUMENT_PROMO_TITLE")
								}),
								Dom.create("div", {
									props: {className: "bx-call-promo-text"},
									html: BX.message("IM_CALL_DOCUMENT_PROMO_TEXT")
								}),
								Dom.create("div", {
									props: {className: "bx-call-promo-refuse"},
									children: [
										Dom.create("input", {
											attrs: {type: "checkbox"},
											props: {
												className: "bx-call-promo-refuse-checkbox",
												id: "bx-call-promo-refuse-checkbox"
											},
											events: {
												change: this.onCheckboxChange.bind(this)
											}
										}),
										Dom.create("label", {
											attrs: {for: "bx-call-promo-refuse-checkbox"},
											props: {className: "bx-call-promo-refuse-text"},
											text: BX.message("IM_CALL_DOCUMENT_PROMO_DONT_SHOW_AGAIN")
										})
									]
								})
							]
						}),
						Dom.create("div", {
							props: {className: "bx-call-promo-button-section"},
							children: [
								Dom.create("button", {
									props: {className: "bx-call-promo-button bx-call-promo-button-action ui-btn ui-btn-round"},
									text: BX.message("IM_CALL_DOCUMENT_PROMO_ACTION"),
									events: {
										click: this.onActionClick.bind(this)
									}
								}),
								Dom.create("button", {
									props: {className: "bx-call-promo-button bx-call-promo-button-action-close ui-btn ui-btn-round"},
									text: BX.message("IM_CALL_DOCUMENT_PROMO_ACTION_CLOSE"),
									events: {
										click: this.close.bind(this)
									}
								}),
							]
						})
					]
				})
			]
		});
	};

	show()
	{
		if (!this.elements.root)
		{
			this.render();
		}

		this.createPopup();
		this.popup.show();
	};

	close()
	{
		if (!this.popup)
		{
			return false;
		}

		this.popup.close();
	};

	createPopup()
	{
		this.popup = new Popup({
			id: 'bx-call-promo-popup',
			bindElement: this.bindElement,
			targetContainer: document.body,
			content: this.elements.root,
			cacheable: false,
			closeIcon: true,
			bindOptions: {
				position: "top"
			},
			angle: {position: "bottom", offset: 49},
			className: 'bx-call-promo-popup',
			contentBackground: 'unset',
			events: {
				onPopupClose: this.onPopupClose.bind(this),
			}
		});
	};

	onPopupClose()
	{
		this.popup.destroy();
		this.destroy();
	};

	onCheckboxChange(event)
	{
		this.dontShowAgain = event.currentTarget.checked;
	};

	onActionClick()
	{
		this.eventEmitter.emit(Events.onActionClick);
	};

	destroy()
	{
		this.eventEmitter.emit(Events.onClose, {dontShowAgain: this.dontShowAgain});

		this.eventEmitter.unsubscribeAll(Events.onClose);
		this.eventEmitter = null;
		this.elements = null;
	};
}

PromoPopup.Events = Events;

export class PromoPopup3D
{
	callView: View

	constructor(options)
	{
		options = Type.isPlainObject(options) ? options : {};

		this.callView = options.callView;
		this.bindElement = options.bindElement;

		this.popup = null;

		options.events = Type.isPlainObject(options.events) ? options.events : {};
		this.events = {
			onActionClick: options.events.onActionClick ? options.events.onActionClick : () => {},
			onClose: options.events.onClose ? options.events.onClose : () => {},
		};
	};

	show()
	{
		this.createPopup();
		this.popup.show();

		BX.bind(BX('promo-popup-3d-button'), "click", this.openWindow.bind(this));
	};

	openWindow()
	{
		BackgroundDialog.open({tab: 'mask'});
		setTimeout(() => this.close(), 100);
	}

	openLearningPopup()
	{
		const bindElement = BX('bx-messenger-videocall-panel-item-with-arrow-camera');
		if (!bindElement)
		{
			return true;
		}

		const title = BX.message('IM_PROMO_3DAVATAR_30112022_LEARNING_TITLE');
		const description = BX.message('IM_PROMO_3DAVATAR_30112022_LEARNING_TEXT');

		const content = `
			<div class="promo-popup-3d-learning-content">
				<h4 class="ui-typography-heading-h4 promo-popup-3d-learning-content__title">${title}</h4>
				<p class="promo-popup-3d-learning-content__description">${description}</p>
			</div>
		`;

		this.popup = new Popup({
			id: 'bx-call-promo-learning-popup',
			bindElement: bindElement,
			targetContainer: document.body,
			content: content,
			cacheable: false,
			closeIcon: true,
			autoHide: true,
			closeByEsc: true,
			bindOptions: {
				position: "top", forceTop: -100, forceLeft: 100, forceBindPosition: true
			},
			angle: {position: "top", offset: 49},
			className: 'bx-call-promo-popup-learn',
			contentBackground: 'unset',
			events: {
				onPopupClose: () =>
				{
					this.events.onClose();
				},
			}
		});

		this.popup.show();

		this.callView.subscribe(
			View.Event.onDeviceSelectorShow,
			() => this.popup ? this.popup.close() : ''
		);
	}

	close()
	{
		if (!this.popup)
		{
			return false;
		}

		this.popup.close();
	};

	createPopup()
	{
		const title = BX.message('IM_PROMO_3DAVATAR_30112022_TITLE');
		const description = BX.message('IM_PROMO_3DAVATAR_30112022_TEXT');
		const btnText = BX.message('IM_PROMO_3DAVATAR_30112022_BUTTON');

		const content = `
			<div class="promo-popup-3d-content">
				<div class="promo-popup-3d-content__masks-container">
					<div class="promo-popup-3d-content__mask --left-2 --bear"></div>
					<div class="promo-popup-3d-content__mask --left-1 --pole-bear"></div>
					<div class="promo-popup-3d-content__mask --center --fox"></div>
					<div class="promo-popup-3d-content__mask --right-1 --santa"></div>
					<div class="promo-popup-3d-content__mask --right-2 --owl"></div>
				</div>
				<h3 class="ui-typography-heading-h2 promo-popup-3d-content__title">${title}</h3>
				<p class="promo-popup-3d-content__description">${description}</p>
				<div class="promo-popup-3d-content__actions-btn">
					<span class="ui-btn btn-primary ui-btn-lg ui-btn-round ui-btn-primary" id="promo-popup-3d-button">${btnText}</span>
				</div>
			</div>
		`;

		this.popup = new Popup({
			id: 'bx-call-promo-popup-3d',
			bindElement: this.bindElement,
			targetContainer: document.body,
			content: content,
			cacheable: false,
			closeIcon: true,
			overlay: {
				backgroundColor: '#000',
				opacity: 40,
			},
			width: 531,
			minHeight: 481,
			bindOptions: {
				position: "top"
			},
			className: 'bx-call-promo-popup-3d-masks',
			events: {
				onPopupClose: this.onPopupClose.bind(this),
			}
		});
	};

	onPopupClose()
	{
		this.popup.destroy();
		this.openLearningPopup();
	};
}

PromoPopup3D.Events = Events;

