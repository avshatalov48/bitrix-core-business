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
})();