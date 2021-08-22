import {BookingUtil, Type, Dom, Loc, SelectInput} from "calendar.resourcebooking";

export class ServiceSelector
{
	constructor(params)
	{
		this.params = Type.isPlainObject(params) ? params : {};
		this.outerCont = this.params.outerCont;
		this.fieldSettings = this.params.fieldSettings || {};
		this.create();
	}

	create()
	{
		this.serviceListOuterWrap = this.outerCont.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-service-list-wrap"}}));

		this.durationTitleId = 'duration-title-wrap-' + Math.round(Math.random() * 100000);
		this.servicesTitleWrap = this.serviceListOuterWrap
			.appendChild(Dom.create("div", {
				props: {className: "calendar-resourcebook-content-block-detail-inner"},
				html: '<div class="calendar-resourcebook-content-block-detail-resource">' +
					'<div class="calendar-resourcebook-content-block-title">' +
					'<span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span>' +
					'</div>' +
					'<div id="' + this.durationTitleId + '" class="calendar-resourcebook-content-block-title calendar-resourcebook-content-block-duration-title">' +
					'<span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span>' +
					'</div>' +
					'</div>'
			}));

		this.serviceListRowsWrap = this.serviceListOuterWrap
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail-inner"}}))
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail"}}));

		BX.bind(this.serviceListRowsWrap, 'click', this.handlePopupClick.bind(this));
		if (Type.isArray(this.fieldSettings.SERVICE_LIST) && this.fieldSettings.SERVICE_LIST.length > 0)
		{
			this.fieldSettings.SERVICE_LIST.forEach(function(service)
			{
				this.addRow(service, false);
			}, this);
		}
		else
		{
			this.addRow(false, false);
		}

		this.serviceListAddWrap = this.serviceListOuterWrap.appendChild(Dom.create("div", {props: {className: "calendar-resource-content-block-add-field"}}));

		this.serviceAddButton = this.serviceListAddWrap.appendChild(Dom.create("span", {
			props: {className: "calendar-resource-content-block-add-link calendar-resource-content-block-add-link-icon"},
			text: Loc.getMessage('USER_TYPE_RESOURCE_ADD_SERVICE'),
			events: {click: this.addRow.bind(this)}
		}));
		BX.bind(window, 'resize', this.checkDurationTitlePosition.bind(this));
		this.checkDurationTitlePosition();

		this.show(this.fieldSettings.USE_SERVICES === 'Y');
	}

	show(show)
	{
		if (show)
		{
			this.serviceListOuterWrap.style.display = '';
			Dom.addClass(this.serviceListOuterWrap, 'show');
		}
		else
		{
			this.serviceListOuterWrap.style.display = 'none';
			Dom.removeClass(this.serviceListOuterWrap, 'show');
		}
	}

	addRow(row, animation)
	{
		animation = animation !== false;

		if (!Type.isPlainObject(row))
		{
			row = {name: '', duration: this.getDefaultDuration()}
		}

		let service = {
			outerWrap: this.serviceListRowsWrap
				.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail-resource calendar-resourcebook-service-row"}}))
		};

		if (animation)
		{
			setTimeout(function(){
				Dom.addClass(service.outerWrap, 'show');
			}, 1);
		}
		else
		{
			Dom.addClass(service.outerWrap, 'show');
		}

		service.wrap = service.outerWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail-resource-inner"}}));

		service.nameInput = service.wrap.appendChild(Dom.create("input", {
			props: {
				className: "calendar-resourcebook-content-input calendar-resourcebook-service-input",
				placeholder: Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_PLACEHOLDER'),
				type: "text",
				value: row.name
			},
			attrs: {}
		}));

		service.durationInput = service.wrap.appendChild(Dom.create("input", {
			props: {
				className: "calendar-resbook-duration-input calendar-resbook-field-datetime-menu",
				type: "text",
				value: row.duration
			},
			attrs: {}
		}));

		service.durationList = new SelectInput({
			input: service.durationInput,
			getValues: function(){
				let fullday = false;
				if (Type.isFunction(this.params.getFullDayValue))
				{
					fullday = this.params.getFullDayValue();
				}
				return BookingUtil.getDurationList(fullday);
			}.bind(this),
			value: row.duration
		});

		service.deleteWrap = service.wrap.appendChild(Dom.create("DIV", {
			props: {className: "calendar-resourcebook-content-block-detail-delete"},
			html: '<span class="calendar-resourcebook-content-block-control-delete calendar-resourcebook-content-block-control-delete-detail"></span>'
		}));

		// Adjust outer wrap max height
		this.serviceListOuterWrap.style.maxHeight = Math.max(500, this.serviceListRowsWrap.childNodes.length * 45 + 100) + 'px';
	}

	checkDurationTitlePosition(timeout)
	{
		if (timeout !== false)
		{
			if (this.checkDurationTitlePositionTimeout)
			{
				clearTimeout(this.checkDurationTitlePositionTimeout);
			}

			this.checkDurationTitlePositionTimeout = setTimeout(function(){

				this.checkDurationTitlePosition(false);
			}.bind(this), 100);
			return;
		}

		let durationInput = this.serviceListOuterWrap.querySelector('input.calendar-resbook-duration-input');
		if (this.durationTitleId && BX(this.durationTitleId) && durationInput)
		{
			BX(this.durationTitleId).style.left = (durationInput.offsetLeft + 15) + 'px';
		}
	}

	getDefaultDuration()
	{
		let fullday = false;
		if (Type.isFunction(this.params.getFullDayValue))
		{
			fullday = this.params.getFullDayValue();
		}
		return fullday ? 1440 : 30;
	}

	clickHandler(e)
	{
		let target = e.target || e.srcElement;
		if (Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete')
			|| Dom.hasClass(target, 'calendar-resourcebook-content-block-detail-delete')) // Delete button
		{
			let resWrap = BX.findParent(target, {className: 'calendar-resourcebook-service-row'});
			if (resWrap)
			{
				Dom.removeClass(resWrap, 'show');
				setTimeout(function(){Dom.remove(resWrap);}, 500);
				this.checkRows();
			}
		}
	}

	getValues(e)
	{
		let
			serviceList = [],
			nameInput, durationInput,
			i, rows = this.serviceListRowsWrap.querySelectorAll('.calendar-resourcebook-service-row');

		for (i = 0; i < rows.length; i++)
		{
			if (Dom.hasClass(rows[i], 'show'))
			{
				nameInput = rows[i].querySelector('input.calendar-resourcebook-service-input');
				durationInput = rows[i].querySelector('input.calendar-resbook-duration-input');

				if (nameInput && durationInput)
				{
					serviceList.push({
						name: nameInput.value,
						duration: BookingUtil.parseDuration(durationInput.value)
					});
				}
			}
		}

		return serviceList;
	}

	checkRows()
	{
		let serviceList = this.getValues();
		if (!serviceList.length)
		{
			this.show(false);
			if (Type.isFunction(this.params.onFullClearHandler))
			{
				this.params.onFullClearHandler();
			}
			this.addRow(false, false);
		}
	}


	handlePopupClick(e)
	{
		let target = e.target || e.srcElement;
		if (Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete')
			|| Dom.hasClass(target, 'calendar-resourcebook-content-block-detail-delete')) // Delete button
		{
			let resWrap = BX.findParent(target, {className: 'calendar-resourcebook-service-row'});
			if (resWrap)
			{
				BX.removeClass(resWrap, 'show');
				setTimeout(function(){BX.remove(resWrap);}, 500);
				this.checkRows();
			}
		}
	}
}