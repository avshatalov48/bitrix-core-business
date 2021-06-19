export class CalendarViewSettingsSlider
{
	constructor(params)
	{
		this.id = 'calendar_custom_settings_' + Math.round(Math.random() * 1000000);
		this.zIndex = 3100;
		this.sliderId = "calendar:resbook-settings-slider";

		this.SLIDER_WIDTH = 400;
		this.SLIDER_DURATION = 80;
		this.DOM = {};

		this.params = params;
	}

	show()
	{
		BX.SidePanel.Instance.open(this.sliderId, {
			contentCallback: BX.delegate(this.create, this),
			width: this.SLIDER_WIDTH,
			animationDuration: this.SLIDER_DURATION
		});

		this.hideHandler = this.hide.bind(this);
		this.destroyHandler = this.destroy.bind(this);
		BX.addCustomEvent("SidePanel.Slider:onClose", this.hideHandler);
		BX.addCustomEvent("SidePanel.Slider:onCloseComplete", );
	}

	close()
	{
		BX.SidePanel.Instance.close();
	}

	hide(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			// if (this.denyClose)
			// {
			// 	event.denyAction();
			// }
			// else
			// {
				BX.removeCustomEvent("SidePanel.Slider:onClose", this.hideHandler);
			//}
		}
	}

	destroy(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.destroyHandler);
			BX.SidePanel.Instance.destroy(this.sliderId);
		}
	}

	create()
	{
		let promise = new BX.Promise();

		let html = '<div class="webform-buttons calendar-form-buttons-fixed">' +
			'<span id="' + this.id + '_save" class="webform-small-button webform-small-button-blue">' + BX.message('USER_TYPE_RESOURCE_SAVE') + '</span>' +
			'<span id="' + this.id + '_close" class="webform-button-link">' + BX.message('USER_TYPE_RESOURCE_CLOSE') + '</span>' +
			'</div>' +
			'<div class="calendar-slider-calendar-wrap">' +
			'<div class="calendar-slider-header"><div class="calendar-head-area"><div class="calendar-head-area-inner"><div class="calendar-head-area-title">' +
			'<span class="calendar-head-area-name">' + BX.message('USER_TYPE_RESOURCE_SETTINGS') + 			'</span>' +
			'</div></div></div></div>' +
			'<div class="resource-booking-slider-workarea"><div class="resource-booking-slider-content"><div id="' + this.id + '_content" class="resource-booking-settings"></div></div></div></div>';

		promise.fulfill(html);
		setTimeout(this.initControls.bind(this), 100);

		return promise;
	}

	initControls()
	{
		this.DOM.content = BX(this.id + '_content');

		BX.bind(BX(this.id + '_save'), 'click', this.save.bind(this));
		BX.bind(BX(this.id + '_close'), 'click', this.close.bind(this));

		// 1. Field
		if (this.params && BX.type.isArray(this.params.filterSelectValues))
		{
			this.DOM.fieldOuterWrap = this.DOM.content.appendChild(BX.create('DIV', {attrs: {className: 'calendar-settings-control'}}));
			this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {
				attrs: {className: 'calendar-settings-control-name'},
				text: BX.message('USER_TYPE_RESOURCE_FILTER_NAME')
			}));

			this.DOM.fieldSelect = this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {attrs: {className: 'calendar-field-container calendar-field-container-select'}}))
				.appendChild(BX.create('DIV', {attrs: {className: 'calendar-field-block'}}))
				.appendChild(BX.create('select', {attrs: {className: 'calendar-field calendar-field-select'}}));

			this.params.filterSelectValues.forEach(function(value){
				this.DOM.fieldSelect.options.add(
					new Option(value.TEXT, value.VALUE, this.params.filterSelect === value.VALUE, this.params.filterSelect === value.VALUE));
			}, this);
		}
	}

	save()
	{
		let entityType = this.params.entityType || 'none';
		BX.userOptions.save('calendar', 'resourceBooking', entityType, this.DOM.fieldSelect.value);
		this.close();
		BX.reload();
	}
}



