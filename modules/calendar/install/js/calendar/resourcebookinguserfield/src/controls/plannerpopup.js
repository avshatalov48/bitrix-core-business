import { Type, Dom, Event, BookingUtil } from "calendar.resourcebooking";
import { Popup } from 'main.popup';

export class PlannerPopup
{
	constructor(params)
	{
	}

	show(params)
	{
		if (!params)
		{
			params = {};
		}
		this.params = params;
		this.bindNode = params.bindNode;
		this.plannerId = this.params.plannerId;
		this.config = this.params.plannerConfig;

		if (this.isShown() || !this.bindNode)
		{
			return;
		}

		if (this.lastPlannerIdShown && this.lastPlannerIdShown !== this.plannerId)
		{
			this.close({animation: false});
		}

		this.currentEntries = [];

		this.plannerWrap = Dom.create('DIV', {
			attrs: {
				id: this.plannerId,
				className: 'calendar-planner-wrapper'
			}
		});
		
		this.popup = new Popup(this.plannerId + "_popup",
			this.bindNode,
			{
				autoHide: false,
				closeByEsc: true,
				offsetTop: - parseInt(this.bindNode.offsetHeight) - 20,
				offsetLeft: this.bindNode.offsetWidth + 38,
				lightShadow: true,
				content: this.plannerWrap
			})

		this.popup.setAngle({offset: 100, position: 'left'});
		this.popup.show();
		
		this.lastPlannerIdShown = this.plannerId;

		let
			bindPos = BX.pos(this.bindNode),
			winSize = BX.GetWindowSize();

		this.plannerWidth = winSize.innerWidth - bindPos.right - 160;
		this.config.width = this.plannerWidth;
		
		if (this.popup && this.popup.popupContainer)
		{
			Dom.addClass(this.popup.popupContainer, 'calendar-resbook-planner-popup');
			Dom.addClass(this.popup.popupContainer, 'show');
			this.popup.popupContainer.style.width = (this.plannerWidth + 40) + 'px';
			Event.bind(document, 'click', this.handleClick.bind(this));
		}
		this.showPlanner();

		BX.addCustomEvent(this.popup, 'onPopupClose', this.close.bind(this));
	}

	update(params, refreshParams)
	{
		if (!this.isShown())
		{
			return;
		}

		let
			codes = [], i, k, code,
			codeIndex = {},
			plannerConfig = BX.clone(this.config, true),
			fromTimestamp, toTimestamp,
			dateFrom, dateTo;

		if (Type.isPlainObject(this.lastUpdateParams) && Type.isPlainObject(params) && refreshParams !== true)
		{
			for (k in params)
			{
				if (params.hasOwnProperty(k))
				{
					this.lastUpdateParams[k] = params[k];
				}
			}
			params = this.lastUpdateParams;
		}

		// Save selector information
		if (Type.isPlainObject(params))
		{
			this.lastUpdateParams = params;
		}

		params.focusSelector = params.focusSelector !== false;

		if (params.from && params.to)
		{
			dateFrom = BookingUtil.parseDate(params.from);
			dateTo = BookingUtil.parseDate(params.to);
			fromTimestamp = dateFrom.getTime();
			toTimestamp = dateTo.getTime();
		}
		else
		{
			if (params.selector.fullDay)
			{
				fromTimestamp = params.selector.from.getTime() - BookingUtil.getDayLength() * 12;
				toTimestamp = params.selector.from.getTime() + BookingUtil.getDayLength() * 14;
			}
			else
			{
				fromTimestamp = params.selector.from.getTime() - BookingUtil.getDayLength() * 3;
				toTimestamp = params.selector.from.getTime() + BookingUtil.getDayLength() * 5;
			}

			dateFrom = new Date(fromTimestamp);
			dateTo = new Date(toTimestamp);

			plannerConfig.scaleDateFrom = dateFrom;
			plannerConfig.scaleDateTo = dateTo;
		}

		if (Type.isArray(params.userList))
		{
			for (i = 0; i < params.userList.length; i++)
			{
				code = 'U' + params.userList[i].id;
				if (!codeIndex[code])
				{
					codes.push(code);
					codeIndex[code] = true;
				}
			}
		}

		if (Type.isArray(params.selectedUsers))
		{
			for (i = 0; i < params.selectedUsers.length; i++)
			{
				code = 'U' + params.selectedUsers[i];
				if (!codeIndex[code])
				{
					codes.push(code);
					codeIndex[code] = true;
				}
			}
		}

		let requestData = {
			codes: codes,
			resources: params.resourceList,
			from: BookingUtil.formatDate(null, fromTimestamp / 1000),
			to: BookingUtil.formatDate(null, toTimestamp / 1000),
			currentEventList: this.params.currentEventList || []
		};

		if (this.checkUpdateParams(requestData) && this.isShown())
		{
			this.showPlannerLoader();
			BX.ajax.runAction('calendar.api.resourcebookingajax.getplannerdata', {
				data: requestData
			}).then(function (response)
				{
					this.hidePlannerLoader();

					if (this.lastRequestData)
					{
						this.lastRequestData.response = response;
					}

					this.currentEntries = response.data.entries;
					this.currentAccessibility = response.data.accessibility;
					this.currentLoadedDataFrom = dateFrom;
					this.currentLoadedDataTo = dateTo;

					if (Type.isArray(response.data.entries))
					{
						response.data.entries.forEach(function(entry)
						{
							entry.selected = ((entry.type === 'user'
								&& params.selectedUsers.find(function(userId){return parseInt(entry.id) === parseInt(userId);}))
								||
								(entry.type === 'resource'
									&& params.selectedResources.find(function(item){return entry.type === item.type && parseInt(entry.id) === parseInt(item.id);}))
							);
						});
					}

					if (this.isShown())
					{
						BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
							{
								plannerId: this.plannerId,
								config: plannerConfig,
								focusSelector: params.focusSelector,
								selector: {
									from: params.selector.from,
									to: params.selector.to,
									fullDay: params.selector.fullDay,
									animation: params.focusSelector,
									updateScaleLimits: params.focusSelector
								},
								data: {
									entries: response.data.entries,
									accessibility: response.data.accessibility
								},
								loadedDataFrom: dateFrom,
								loadedDataTo: dateTo,
								show: false
							}
						]);
					}
				}.bind(this));
		}
		else if (Type.isPlainObject(this.lastRequestData.response))
		{
			let response = this.lastRequestData.response;
			this.currentEntries = response.data.entries;
			this.currentAccessibility = response.data.accessibility;
			this.currentLoadedDataFrom = dateFrom;
			this.currentLoadedDataTo = dateTo;

			if (Type.isArray(response.data.entries))
			{
				response.data.entries.forEach(function(entry)
				{
					entry.selected = ((entry.type === 'user'
						&& params.selectedUsers.find(function(userId){return parseInt(entry.id) === parseInt(userId);}))
						||
						(entry.type === 'resource'
							&& params.selectedResources.find(function(item){return entry.type === item.type && parseInt(entry.id) === parseInt(item.id);}))
					);
				});
			}

			if (this.isShown())
			{
				BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
					{
						plannerId: this.plannerId,
						config: plannerConfig,
						focusSelector: params.focusSelector,
						selector: {
							from: params.selector.from,
							to: params.selector.to,
							fullDay: params.selector.fullDay,
							animation: params.focusSelector,
							updateScaleLimits: params.focusSelector
						},
						data: {
							entries: response.data.entries,
							accessibility: response.data.accessibility
						},
						loadedDataFrom: dateFrom,
						loadedDataTo: dateTo,
						show: false
					}
				]);
			}
		}
	}

	checkUpdateParams (requestData)
	{
		let requestPlannerUpdate = false;
		if (!this.lastRequestData || this.lastRequestPlannerId !== this.plannerId)
		{
			requestPlannerUpdate = true;
		}

		// 1. Compare dates
		if (!requestPlannerUpdate && requestData.from !== this.lastRequestData.from)
		{
			requestPlannerUpdate = true;
		}
		// 2. Compare users
		if (
			!requestPlannerUpdate
			&& Type.isArray(requestData.codes) && Type.isArray(this.lastRequestData.codes)
			&& BX.util.array_diff(requestData.codes, this.lastRequestData.codes).length > 0
		)
		{
			requestPlannerUpdate = true;
		}

		// 3. Compare resources
		if (!requestPlannerUpdate && Type.isArray(requestData.resources) && Type.isArray(this.lastRequestData.resources))
		{
			if (requestData.resources.length !== this.lastRequestData.resources.length)
			{
				requestPlannerUpdate = true;
			}
			else
			{
				let resIndex = {};
				requestData.resources.forEach(function (res)
				{
					resIndex[res.type + '_' + res.id] = true
				});

				this.lastRequestData.resources.forEach(function(res)
				{
					if (!resIndex[res.type + '_' + res.id])
					{
						requestPlannerUpdate = true;
					}
				});
			}
		}

		// Save request data for future comparing
		if (requestPlannerUpdate)
		{
			this.lastRequestData = requestData;
			this.lastRequestPlannerId = this.plannerId;
		}

		return requestPlannerUpdate;
	}

	showPlanner()
	{
		this.planner = new CalendarPlanner(
			this.params.plannerConfig,
			{
				config: this.config,
				data: {
					accessibility: this.currentAccessibility || {},
					entries: this.currentEntries
				},
				selector: {
					from: this.params.selector.from,
					to: this.params.selector.to,
					fullDay: this.params.selector.fullDay,
					updateScaleLimits: true,
					updateScaleType: false,
					focus: true,
					RRULE: false,
					animation: false
				},
				loadedDataFrom: this.currentLoadedDataFrom,
				loadedDataTo: this.currentLoadedDataTo,
				focusSelector: true,
				plannerId: this.plannerId,
				show: true
			}
		);

		// planner events
		if (Type.isFunction(this.params.selectorOnChangeCallback))
		{
			BX.addCustomEvent('OnCalendarPlannerSelectorChanged', this.params.selectorOnChangeCallback);
		}
		if (Type.isFunction(this.params.selectEntriesOnChangeCallback))
		{
			BX.addCustomEvent('OnCalendarPlannerSelectedEntriesOnChange', this.params.selectEntriesOnChangeCallback);
		}
		if (Type.isFunction(this.params.checkSelectorStatusCallback))
		{
			BX.addCustomEvent('OnCalendarPlannerSelectorStatusOnChange', this.params.checkSelectorStatusCallback);
		}

		BX.addCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(function(params)
		{
			this.update({
				from: params.from,
				to: params.to,
				focusSelector: params.focusSelector === true
			});
		}, this));

	}

	showPlannerLoader()
	{
		if (this.planner && this.planner.outerWrap)
		{
			if (this.loader)
			{
				Dom.remove(this.loader);
			}
			this.loader = this.planner.outerWrap.appendChild(BookingUtil.getLoader(150));
		}
	}

	hidePlannerLoader()
	{
		if (this.loader)
		{
			Dom.remove(this.loader);
			this.loader = false;
		}
	}

	close(params)
	{
		if (this.popup)
		{
			if (params && params.animation)
			{
				Dom.removeClass(this.popup.popupContainer, 'show');
				setTimeout(BX.delegate(function()
				{
					params.animation = false;
					this.close(params);
				}, this), 300);
			}
			else
			{
				BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
				BX.removeCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
				this.popup.destroy();
				this.planner = null;
				this.popup = null;
			}
		}
	}

	isShown()
	{
		return this.lastPlannerIdShown === this.plannerId
			&& this.popup
			&& this.popup.isShown()
		;
	}

	getPlannerId()
	{
		if (typeof this.plannerId === 'undefined')
		{
			this.plannerId = 'calendar-planner-' + Math.round(Math.random() * 100000);
		}
		return this.plannerId;
	}

	handleClick(e)
	{
		let target = e.target || e.srcElement;
		if (
			this.isShown()
			&& !BX.isParentForNode(this.bindNode, target)
			&& !BX.isParentForNode(BX('BXSocNetLogDestination'), target)
			&& !BX.isParentForNode(this.popup.popupContainer, target)
			&& !Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete')
		)
		{
			if (!document.querySelector('div.popup-window-resource-select'))
			{
				this.close({animation: true});
			}
		}
	}
}





