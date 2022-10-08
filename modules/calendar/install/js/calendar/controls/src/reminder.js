import {Loc, Type, Dom, Tag, Event} from "main.core";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {MenuManager, Popup} from 'main.popup';
import {Util} from 'calendar.util';

export class Reminder extends EventEmitter
{
	static timeValueList = null;
	defaultReminderTime = 540; // 9.00
	fullDayMode = false;
	extendedMode = true;
	selectedValues = [];
	controlList = {};
	viewMode = false;
	DOM = {};
	changedByUser = false;

	constructor(params)
	{
		super();

		this.setEventNamespace('BX.Calendar.Controls.Reminder');
		this.values = this.getValues();

		this.id = params.id || 'reminder-' + Math.round(Math.random() * 1000000);
		this.zIndex = params.zIndex || 3200;
		this.rawValuesMap = new Map();

		this.viewMode = params.viewMode === true;
		this.changeCallack = params.changeCallack;
		this.showPopupCallBack = params.showPopupCallBack;
		this.hidePopupCallBack = params.hidePopupCallBack;

		this.create(params);
		this.setValue(params.selectedValues || []);
		this.bindEventHandlers();
	}

	create(params)
	{
		if (Type.isElementNode(params.wrap))
		{
			this.DOM.wrap = params.wrap;
		}

		if (!Type.isElementNode(this.DOM.wrap))
		{
			return;
		}

		Dom.addClass(this.DOM.wrap, 'calendar-notification-values');

		if (!this.viewMode)
		{
			this.DOM.addButton = this.DOM.wrap.appendChild(Tag.render`
				<span class="calendar-notification-add-btn-wrap">
					<span class="calendar-notification-text">${Loc.getMessage('EC_REMIND1_ADD')}</span>
					<span class="calendar-notification-btn-container calendar-notification-btn-add">
						<span class="calendar-notification-icon"></span>
					</span>
				</span>`);
		}
	}

	bindEventHandlers()
	{
		if (Type.isDomNode(this.DOM.addButton))
		{
			Event.bind(this.DOM.addButton, 'click', this.showPopup.bind(this));
		}

		if (Type.isDomNode(this.DOM.wrap))
		{
			Event.bind(this.DOM.wrap, 'click', this.handleClick.bind(this));
		}
	}

	getValues()
	{
		let values = [];

		if (!this.fullDayMode)
		{
			values = values.concat([
				{value: 0, label: Loc.getMessage("EC_REMIND1_0"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_0")},
				{value: 5, label: Loc.getMessage("EC_REMIND1_5"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_5")},
				{value: 10, label: Loc.getMessage("EC_REMIND1_10"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_10")},
				{value: 15, label: Loc.getMessage("EC_REMIND1_15"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_15")},
				{value: 20, label: Loc.getMessage("EC_REMIND1_20"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_20")},
				{value: 30, label: Loc.getMessage("EC_REMIND1_30"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_30")},
				{value: 60, label: Loc.getMessage("EC_REMIND1_60"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_60")},
				{value: 120, label: Loc.getMessage("EC_REMIND1_120"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_120")}
				//{value: 1440, label: Loc.getMessage("EC_REMIND1_1440"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_1440")},
				//{value: 2880, label: Loc.getMessage("EC_REMIND1_2880"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_2880")}
			]);
		}

		if (this.extendedMode)
		{
			values = values.concat([
				{
					id: 'time-menu-day-0',
					label: Loc.getMessage("EC_REMIND1_DAY_0"),
					dataset: {
						mode: 'time-menu',
						daysBefore: 0,
						time: this.defaultReminderTime
					}
				},
				{
					id: 'time-menu-day-1',
					label: Loc.getMessage("EC_REMIND1_DAY_1"),
					dataset: {
						mode: 'time-menu',
						daysBefore: 1,
						time: this.defaultReminderTime
					}
				},
				{
					id: 'time-menu-day-2',
					label: Loc.getMessage("EC_REMIND1_DAY_2"),
					dataset: {
						mode: 'time-menu',
						daysBefore: 2,
						time: this.defaultReminderTime
					}
				},
				{
					id: 'custom',
					label: Loc.getMessage("EC_REMIND1_CUSTOM"),
					dataset: {
						mode: 'custom'
					}
				}
			]);
		}

		return values;
	}

	setValue(reminderList, emitChanges = true, changedByUser = true)
	{
		this.selectedValues.forEach((value) => {
			this.removeValue(value, emitChanges);
		});

		if (Type.isArray(reminderList))
		{
			reminderList.forEach((value) => {this.addValue(value, emitChanges, changedByUser)}, this);
		}
	}

	getSelectedValues()
	{
		return this.selectedValues;
	}

	getSelectedRawValues()
	{
		let selectedRawValues = [];
		this.selectedValues.forEach((value) => {

			selectedRawValues.push(this.rawValuesMap.get(value) || value);
		});
		return selectedRawValues;
	}

	setFullDayMode(fullDayMode)
	{
		if (fullDayMode !== this.fullDayMode)
		{
			this.fullDayMode = fullDayMode;
			this.values = this.getValues();
		}
	}

	showPopup(params = {})
	{
		const _this = this;
		const menuItems = [];

		this.values.forEach((item) => {
			if (item.mode === 'time-menu'
				|| item.mode === 'custom'
				|| !BX.util.in_array(item.value, this.selectedValues))
			{
				let menuItem = {};

				if (item.dataset && item.dataset.mode === 'time-menu')
				{
					menuItem.id = item.id;
					let defaultReminderTime = Util.getTimeByInt(this.defaultReminderTime);

					menuItem.text = item.label.replace('#TIME#', Util.formatTime(defaultReminderTime.hour, defaultReminderTime.min));

					menuItem.dataset = BX.util.objectMerge({
						type: 'submenu-list',
						value: this.defaultReminderTime
					}, item.dataset);

					menuItem.items = this.getSubmenuTimeValues(menuItem, item.label, params);

					menuItem.onclick = (function ()
					{
						return function ()
						{
							_this.addValue({
								before: item.dataset.daysBefore,
								time: item.dataset.time
							});

							BX.defer(function(){_this.reminderMenu.close();}, _this)();
							if (Type.isFunction(params.addValueCallback))
							{
								params.addValueCallback();
							}
						}
					})();
				}
				else if (item.dataset && item.dataset.mode === 'custom')
				{
					menuItem.id = 'custom';
					menuItem.text = item.label;
					menuItem.items = [{id: 'tmp', text: 'tmp'}];
				}
				else
				{
					menuItem.text = item.label;
					menuItem.onclick = (function (value, mode)
					{
						return () => {
							_this.addValue(value);
							_this.reminderMenu.close();
							if (Type.isFunction(params.addValueCallback))
							{
								params.addValueCallback();
							}
						}
					})(item.value, item.mode);
				}

				menuItems.push(menuItem);
			}
		}, this);

		this.reminderMenu = MenuManager.create(
			this.id,
			params.bindTarget || this.DOM.addButton,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 9,
				angle: true,
				cacheable: false
			}
		);

		let adjustSubmenuPopup = this.adjustSubmenuPopup.bind(this);
		let closeSubmenuPopup = this.closeSubmenuPopup.bind(this);
		EventEmitter.subscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
		EventEmitter.subscribe('BX.Main.Popup:onClose', closeSubmenuPopup);

		this.reminderMenu.popupWindow.subscribe('onClose', () => {
			EventEmitter.unsubscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
			EventEmitter.unsubscribe('BX.Main.Popup:onClose', closeSubmenuPopup);
		});
		this.reminderMenu.show();
	}

	getSubmenuTimeValues(parentItem, parentItemMessage, params)
	{
		let menuItems = [];
		Reminder.getTimeValueList(60).forEach(function(menuItem)
		{
			menuItems.push({
				id: 'time-' + menuItem.value,
				dataset: {
					value: menuItem.value,
					daysBefore: parentItem.dataset.daysBefore
				},
				text: menuItem.label,
				onclick: function(e, item)
				{
					let time = Util.getTimeByInt(item.dataset.value);
					let parentMenuItem = this.reminderMenu.getMenuItem(parentItem.id);
					if (parentMenuItem)
					{
						parentMenuItem.setText(parentItemMessage.replace('#TIME#', Util.formatTime(time.hour, time.min)));
					}

					this.addValue({
						time: item.dataset.value,
						before: item.dataset.daysBefore
					});

					BX.defer(function(){this.reminderMenu.close();}, this)();
					if (Type.isFunction(params.addValueCallback))
					{
						params.addValueCallback();
					}

				}.bind(this)
			});
		}, this);
		return menuItems;
	}

	addValue(value, emitChanges = true, changedByUser = true)
	{
		let item;
		const formattedValue = Reminder.formatValue(value);

		if (Type.isPlainObject(value) && value.count)
		{
			value = parseInt(formattedValue);
		}

		if (Type.isPlainObject(value) && !this.selectedValues.includes(formattedValue))
		{
			if (Type.isInteger(parseInt(value.before)) && Type.isInteger(parseInt(value.time)))
			{
				item = this.DOM.wrap.appendChild(Tag.render`
					<span class="calendar-reminder-item">
						<span class="calendar-reminder-item-title">
							${Reminder.getReminderLabel(value)}
						</span>
					</span>`);

				if (!this.viewMode)
				{
					item.appendChild(Tag.render`<span class="calendar-reminder-clear-icon" data-bxc-value="${formattedValue}"/>`);
				}
			}
			else if (value.type === 'date' && Type.isDate(value.value))
			{
				item = this.DOM.wrap.appendChild(Tag.render`<span class="calendar-reminder-item">
					<span class="calendar-reminder-item-title">
						${Util.formatDateUsable(value.value) + ' ' + Util.formatTime(value.value)}
					</span>
				</span>`);

				if (!this.viewMode)
				{
					item.appendChild(Tag.render`<span class="calendar-reminder-clear-icon" data-bxc-value="${formattedValue}"/>`);
				}
			}

			this.selectedValues.push(formattedValue);
			this.controlList[formattedValue] = item;
			this.rawValuesMap.set(formattedValue, value);
		}
		else if (parseInt(value) >= 0 && !this.selectedValues.includes(formattedValue))
		{
			for (let i = 0; i < this.values.length; i++)
			{
				if (this.values[i].value === parseInt(value))
				{
					item = this.DOM.wrap.appendChild(Tag.render`
					<span class="calendar-reminder-item">
						<span class="calendar-reminder-item-title">
							${this.values[i].shortLabel || this.values[i].label}
						</span>
					</span>`);

					if (!this.viewMode)
					{
						item.appendChild(Tag.render`<span class="calendar-reminder-clear-icon" data-bxc-value="${formattedValue}"/>`);
					}

					this.selectedValues.push(formattedValue);
					this.controlList[formattedValue] = item;
					this.rawValuesMap.set(formattedValue, value);
					break;
				}
			}

			if (item === undefined)
			{
				item = this.DOM.wrap.appendChild(Dom.create('SPAN', {
					props: {className: 'calendar-reminder-item'},
					text: Reminder.getText(value)
				}));

				if (!this.viewMode)
				{
					item.appendChild(Dom.create('SPAN', {
						props: {className: 'calendar-reminder-clear-icon'},
						events: {click: function(){this.removeValue(value);}.bind(this)}
					}));
				}

				this.selectedValues.push(value);
				this.controlList[value] = item;
				this.rawValuesMap.set(value, value);
			}
		}

		if (this.changeCallack)
		{
			this.changeCallack(this.selectedValues);
		}

		if (emitChanges)
		{
			this.emit('onChange', new BaseEvent({data: {values: this.selectedValues}}));
		}
		this.changedByUser = emitChanges && changedByUser;

		if (Type.isElementNode(this.DOM.addButton))
		{
			this.DOM.wrap.appendChild(this.DOM.addButton);
		}
	}

	removeValue(value, emitChanges = true)
	{
		if (this.controlList[value] && Type.isDomNode(this.controlList[value]))
		{
			Dom.remove(this.controlList[value]);
		}
		this.selectedValues = BX.util.deleteFromArray(this.selectedValues, BX.util.array_search(value, this.selectedValues));

		if (this.changeCallack)
		{
			this.changeCallack(this.selectedValues);
		}

		if (emitChanges)
		{
			this.emit('onChange', new BaseEvent({data: {values: this.selectedValues}}));
			this.changedByUser = true;
		}
	}

	static getTimeValueList(mode = 30)
	{
		if (!Reminder.timeValueList)
		{
			Reminder.timeValueList = [];
			let i;
			for (i = 0; i < 24; i++)
			{
				Reminder.timeValueList.push({value: i * 60, label: Util.formatTime(i, 0)});
				if (mode <= 30)
				{
					Reminder.timeValueList.push({value: i * 60 + 30, label: Util.formatTime(i, 30)});
				}
			}
		}
		return Reminder.timeValueList;
	}

	handleClick(e)
	{
		let target = e.target || e.srcElement;
		let remValue = target.getAttribute('data-bxc-value');

		if (!Type.isNull(remValue) && this.selectedValues.includes(remValue))
		{
			this.removeValue(remValue);
		}

		if (Dom.hasClass(target, 'calendar-reminder-item-title'))
		{
			this.showPopup({

				bindTarget: target,
				addValueCallback: ()=>{
					const removeIcon = target.parentNode.querySelector('.calendar-reminder-clear-icon');
					if (Type.isElementNode(removeIcon)
						&& !Type.isNull(removeIcon.getAttribute('data-bxc-value')))
					{
						this.removeValue(removeIcon.getAttribute('data-bxc-value'));
					}
				}
			});
		}
	}

	static inputCalendarClosePopupHandler(e)
	{
		BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	}

	static getReminderLabel(value)
	{
		let label = '';
		value.before = parseInt(value.before);
		value.time = parseInt(value.time);
		if (Type.isInteger(value.before) && Type.isInteger(value.time) && [0, 1, 2].includes(value.before))
		{
			let time = Util.getTimeByInt(value.time);

			label = Loc.getMessage('EC_REMIND1_DAY_' + value.before + '_SHORT').replace('#TIME#', Util.formatTime(time.hour, time.min))
		}
		return label;
	}

	// Used to scroll into view and highlight default item in time menu
	adjustSubmenuPopup(event)
	{
		if (event instanceof BaseEvent)
		{
			let submenuPopup = event.getTarget();

			if (submenuPopup instanceof Popup)
			{
				if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId()))
				{
					this.adjustTimeSubmenuPopup(submenuPopup);
				}
				else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId()))
				{
					this.adjustCalendarSubmenuPopup(submenuPopup);
				}
			}
		}
	}

	closeSubmenuPopup(event)
	{
		if (event instanceof BaseEvent)
		{
			let submenuPopup = event.getTarget();

			if (submenuPopup instanceof Popup)
			{
				if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId()))
				{
				}
				else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId()))
				{
					let layout = submenuPopup.bindElement;
					let textNode = layout.querySelector('.menu-popup-item-text');

					if (Type.isDomNode(textNode))
					{
						Dom.clean(textNode);
						textNode.innerHTML = Loc.getMessage("EC_REMIND1_CUSTOM");
					}
				}
			}
		}
	}

	adjustTimeSubmenuPopup(popup)
	{
		let selectedMenuItem = popup.getContentContainer().querySelector('span[data-value="' + this.defaultReminderTime + '"]');
		if (Type.isDomNode(selectedMenuItem))
		{
			setTimeout(()=>{
				popup.getContentContainer().scrollTop = parseInt(selectedMenuItem.offsetTop) - 10;
				Dom.addClass(selectedMenuItem, 'menu-popup-item-open');
			}, 50);
		}
	}

	adjustCalendarSubmenuPopup(popup)
	{
		let layout = popup.bindElement;

		let textNode = layout.querySelector('.menu-popup-item-text');
		if (Type.isDomNode(textNode))
		{
			Dom.clean(textNode);
			const input = textNode.appendChild(Tag.render`<input id="inp-${Math.round(Math.random() * 100000)}" type="text" class="calendar-field calendar-field-datetime" value="" autocomplete="off" placeholder="${Loc.getMessage('EC_REMIND1_CUSTOM_PLACEHOLDER')}"/>`);
			const calendarControl = BX.calendar.get();

			// Hacks for BX.calendar - it works as singleton and has troubles with using inside menupopups
			// We trying to reinitialize it everytime
			if (calendarControl.popup)
			{
				calendarControl.popup.destroy();
				calendarControl.popup = null;
				calendarControl._current_layer = null;
				calendarControl._layers = {};
			}
			if (calendarControl.popup_month)
			{
				calendarControl.popup_month.destroy();
				calendarControl.popup_month = null;
			}
			if (calendarControl.popup_year)
			{
				calendarControl.popup_year.destroy();
				calendarControl.popup_year = null;
			}
			

			calendarControl.Show({
				node: input,
				value: Util.formatDateTime(Util.getUsableDateTime(new Date())),
				field: input,
				bTime: true,
				bHideTime: false
			});

			let calendarPopup = calendarControl.popup;
			calendarPopup.cacheable = false;
			if (calendarPopup && calendarPopup.popupContainer)
			{
				let calendarWrap = calendarPopup.popupContainer.querySelector('.bx-calendar');
				if (Type.isDomNode(calendarWrap))
				{
					popup.contentContainer.appendChild(calendarWrap);
				}
				calendarPopup.close();
				MenuManager.destroy(calendarPopup.uniquePopupId);
			}

			Event.bind(input, 'change', () => {
				let
					value = input.value,
					dateValue = Util.parseDate(value);

				if (Type.isDate(dateValue))
				{
					this.addValue({type: 'date', value: dateValue});
					this.reminderMenu.close();
				}
			});
		}
	}

	setViewMode(viewMode)
	{
		this.viewMode = viewMode;

		if (this.viewMode)
		{
			Dom.addClass(this.DOM.wrap, 'calendar-reminder-readonly');
			if (Type.isElementNode(this.DOM.addButton))
			{
				this.DOM.addButton.style.display = 'none';
			}
		}
		else
		{
			Dom.removeClass(this.DOM.wrap, 'calendar-reminder-readonly');
		}
	}

	wasChangedByUser()
	{
		return this.changedByUser;
	}

	static getText(value)
	{
		let
			tempValue = value,
			dividers = [60, 24], //list of time dividers
			messageCodes = ['EC_REMIND1_MIN_COUNT', 'EC_REMIND1_HOUR_COUNT', 'EC_REMIND1_DAY_COUNT'],
			result = '';

		for (let i = 0; i < messageCodes.length; i++)
		{
			if (tempValue < dividers[i] || i === dividers.length)
			{
				result = Loc.getMessage(messageCodes[i]).toString();
				result = result.replace('\#COUNT\#', tempValue.toString());
				break;
			}
			else
			{
				tempValue = Math.ceil(tempValue / dividers[i]);
			}
		}

		return result;
	}

	static formatValue(remindValue)
	{
		if (Type.isPlainObject(remindValue)
			&& Type.isInteger(parseInt(remindValue.before))
			&& Type.isInteger(parseInt(remindValue.time)))
		{
			return 'daybefore|' + remindValue.before + '|' + remindValue.time;
		}
		else if (Type.isPlainObject(remindValue) && Type.isDate(remindValue.value))
		{
			return 'date|' + Util.formatDateTime(remindValue.value);
		}
		else if (Type.isPlainObject(remindValue) && remindValue.type)
		{
			if (remindValue.type === 'min')
			{
				return remindValue.count.toString();
			}
			if (remindValue.type === 'hour')
			{
				return (parseInt(remindValue.count) * 60).toString();
			}
			if (remindValue.type === 'day')
			{
				return (parseInt(remindValue.count) * 60 * 24).toString();
			}
		}

		return remindValue.toString();
	}
}
