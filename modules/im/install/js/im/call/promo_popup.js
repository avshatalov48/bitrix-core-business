;(function()
{
	BX.namespace("BX.Call");

	var Events = {
		onActionClick: 'onActionClick',
		onClose: 'onClose'
	};

	BX.Call.PromoPopup = function(options)
	{
		options = BX.type.isPlainObject(options) ? options : {};
		this.promoCode = BX.type.isStringFilled(options.promoCode) ? options.promoCode : '';
		this.bindElement = options.bindElement;

		this.elements = {
			root: null
		};
		this.popup = null;
		this.dontShowAgain = false;
		this.eventEmitter = new BX.Event.EventEmitter(this, "BX.Call.PromoPopup");

		if (options.events)
		{
			this.subscribeToEvents(options.events);
		}
	};

	BX.Call.PromoPopup.prototype.subscribeToEvents = function(events)
	{
		for (var eventName in events)
		{
			if (events.hasOwnProperty(eventName))
			{
				this.eventEmitter.subscribe(eventName, events[eventName]);
			}
		}
	};

	BX.Call.PromoPopup.prototype.render = function()
	{
		this.elements.root = BX.create("div", {
			props: { className: "bx-call-promo-container" },
			children: [
				BX.create("div", {
					props: { className: "bx-call-promo-content" },
					children: [
						BX.create("div", {
							props: { className: "bx-call-promo-icon-section" },
							children: [
								BX.create("div", {
									props: { className: "bx-call-promo-icon" }
								})
							]
						}),
						BX.create("div", {
							props: { className: "bx-call-promo-text-section" },
							children: [
								BX.create("div", {
									props: { className: "bx-call-promo-title" },
									text: BX.message("IM_CALL_DOCUMENT_PROMO_TITLE")
								}),
								BX.create("div", {
									props: { className: "bx-call-promo-text" },
									html: BX.message("IM_CALL_DOCUMENT_PROMO_TEXT")
								}),
								BX.create("div", {
									props: { className: "bx-call-promo-refuse" },
									children: [
										BX.create("input", {
											attrs: { type: "checkbox" },
											props: {
												className: "bx-call-promo-refuse-checkbox",
												id: "bx-call-promo-refuse-checkbox"
											},
											events: {
												change: this.onCheckboxChange.bind(this)
											}
										}),
										BX.create("label", {
											attrs: { for: "bx-call-promo-refuse-checkbox" },
											props: { className: "bx-call-promo-refuse-text" },
											text: BX.message("IM_CALL_DOCUMENT_PROMO_DONT_SHOW_AGAIN")
										})
									]
								})
							]
						}),
						BX.create("div", {
							props: { className: "bx-call-promo-button-section" },
							children: [
								BX.create("button", {
									props: { className: "bx-call-promo-button bx-call-promo-button-action ui-btn ui-btn-round"},
									text: BX.message("IM_CALL_DOCUMENT_PROMO_ACTION"),
									events: {
										click: this.onActionClick.bind(this)
									}
								}),
								BX.create("button", {
									props: { className: "bx-call-promo-button bx-call-promo-button-action-close ui-btn ui-btn-round"},
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

	BX.Call.PromoPopup.prototype.show = function()
	{
		if (!this.elements.root)
		{
			this.render();
		}

		this.createPopup();
		this.popup.show();
	};

	BX.Call.PromoPopup.prototype.close = function()
	{
		if (!this.popup)
		{
			return false;
		}

		this.popup.close();
	};

	BX.Call.PromoPopup.prototype.createPopup = function()
	{
		var self = this;

		this.popup = new BX.PopupWindow('bx-call-promo-popup', this.bindElement, {
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
				onPopupClose: self.onPopupClose.bind(self),
			}
		});
	};

	BX.Call.PromoPopup.prototype.onPopupClose = function()
	{
		this.popup.destroy();
		this.destroy();
	};

	BX.Call.PromoPopup.prototype.onCheckboxChange = function(event)
	{
		this.dontShowAgain = event.currentTarget.checked;
	};

	BX.Call.PromoPopup.prototype.onActionClick = function()
	{
		this.eventEmitter.emit(Events.onActionClick);
	};

	BX.Call.PromoPopup.prototype.destroy = function()
	{
		if (this.dontShowAgain && BX.MessengerPromo)
		{
			BX.MessengerPromo.save(this.promoCode);
		}

		this.eventEmitter.emit(Events.onClose);
		this.eventEmitter.unsubscribeAll(Events.onClose);
		this.eventEmitter = null;
		this.elements = null;
	};

	BX.Call.PromoPopup.Events = Events;


	BX.Call.PromoPopup3D = function(options)
	{
		options = BX.type.isPlainObject(options) ? options : {};

		this.promoCode = BX.type.isStringFilled(options.promoCode) ? options.promoCode : '';
		this.bindElement = options.bindElement;

		this.popup = null;


		options.events = BX.type.isPlainObject(options.events) ? options.events : {};
		this.events = {};
		this.events.onActionClick = options.events.onActionClick? options.events.onActionClick: () => {};
		this.events.onClose = options.events.onClose? options.events.onClose: () => {};
	};

	BX.Call.PromoPopup3D.prototype.show = function()
	{
		this.createPopup();
		this.popup.show();

		BX.bind(BX('promo-popup-3d-button'), "click", this.openWindow.bind(this));
	};

	BX.Call.PromoPopup3D.prototype.openWindow = function()
	{
		BX.Call.Hardware.BackgroundDialog.open({tab: 'mask'});
		setTimeout(() => this.close(), 100);
	}

	BX.Call.PromoPopup3D.prototype.openLearningPopup = function()
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

		this.popup = new BX.PopupWindow('bx-call-promo-learning-popup', bindElement, {
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
				onPopupClose: () => {
					this.events.onClose();
				},
			}
		});

		this.popup.show();

		window.BXIM.callController.callView.eventEmitter.subscribe(
			BX.Call.View.Event.onDeviceSelectorShow,
			() => this.popup ? this.popup.close(): ''
		);
	}

	BX.Call.PromoPopup3D.prototype.close = function()
	{
		if (!this.popup)
		{
			return false;
		}

		this.popup.close();
	};

	BX.Call.PromoPopup3D.prototype.createPopup = function()
	{
		var self = this;

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

		this.popup = new BX.PopupWindow('bx-call-promo-popup-3d', this.bindElement, {
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
				onPopupClose: self.onPopupClose.bind(self),
			}
		});
	};

	BX.Call.PromoPopup3D.prototype.onPopupClose = function()
	{
		if (BX.MessengerPromo && this.promoCode)
		{
			BX.MessengerPromo.save(this.promoCode);
		}

		this.popup.destroy();
		this.openLearningPopup();
	};

	BX.Call.PromoPopup3D.Events = Events;

})();