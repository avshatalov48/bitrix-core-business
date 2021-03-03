"use strict";

import {Util} from "calendar.util";
import {Type, Event, Loc, Tag, Dom} from 'main.core';
import {Entry, EntryManager} from "calendar.entry";
import {MeetingStatusControl, Reminder} from "calendar.controls";
import {BaseEvent} from 'main.core.events';

export class EventViewForm {
	permissions = {};
	name = 'eventviewform';
	uid = null;
	DOM = {};

	constructor(options = {})
	{
		this.type = options.type || 'user';
		this.ownerId = options.ownerId || 0;
		this.userId = options.userId || 0;
		this.sliderId = "calendar:view-entry-slider";
		this.zIndex = 3100;
		this.entryId = options.entryId || null;
		this.entryDateFrom = options.entryDateFrom || null;
		this.timezoneOffset = options.timezoneOffset || null;
		this.BX = Util.getBX();

		this.sliderOnClose = this.hide.bind(this);
		this.sliderOnLoad = this.onLoadSlider.bind(this);
	}

	initInSlider(slider, promiseResolve)
	{
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.sliderOnClose);
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this));

		//this.BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.destroy.bind(this));

		this.createContent(slider).then(function(html)
			{
				if (Type.isFunction(promiseResolve))
				{
					promiseResolve(html);
				}
			}.bind(this)
		);

		this.opened = true;
	}

	show(params = {})
	{
		if (params.entryId)
		{
			this.entryId = params.entryId;
		}

		this.BX.SidePanel.Instance.open(this.sliderId, {
			contentCallback: this.createContent.bind(this),
			label: {
				text: Loc.getMessage('CALENDAR_EVENT'),
				bgColor: "#55D0E0"
			},
			events: {
				onClose: this.sliderOnClose,
				onCloseComplete: this.destroy.bind(this),
				onLoad: this.sliderOnLoad
			}
		});
		this.opened = true;
	}

	isOpened()
	{
		return this.opened;
	}

	hide(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			this.BX.removeCustomEvent("SidePanel.Slider::onClose", this.sliderOnClose);
		}
	}

	destroy(event)
	{
		this.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this));
		this.BX.SidePanel.Instance.destroy(this.sliderId);
		Util.closeAllPopups();
		this.opened = false;
	}

	onLoadSlider(event)
	{
		let slider = event.getSlider();
		this.DOM.content = slider.layout.content;

		// Used to execute javasctipt and attach CSS from ajax responce
		this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));
		this.initControls(this.uid);
	}

	createContent(slider)
	{
		return new Promise((resolve) => {
			this.BX.ajax.runAction('calendar.api.calendarajax.getViewEventSlider', {
				analyticsLabel: {calendarAction: 'view_event', formType: 'full'},
				data: {
					entryId: this.entryId,
					dateFrom: Util.formatDate(this.entryDateFrom),
					timezoneOffset: this.timezoneOffset
				}
			}).then((response) => {
					let html = '';
					if ((Type.isFunction(slider.isOpen) && slider.isOpen()) || slider.isOpen === true)
					{
						html = this.BX.util.trim(response.data.html);
						slider.getData().set("sliderContent", html);
						let params = response.data.additionalParams;

						this.userId = params.userId;
						this.uid = params.uniqueId;
						this.entryUrl = params.entryUrl;
						this.handleEntryData(params.entry, params.userIndex, params.section);
					}
					resolve(html);
				},
				(response) => {
					this.displayError(response.errors);
					resolve(response);
				});
		});
	}

	initControls(uid)
	{
		this.DOM.title = this.DOM.content.querySelector(`#${uid}_title`);
		this.DOM.formWrap = this.DOM.content.querySelector(`#${uid}_form_wrap`);
		this.DOM.form = this.DOM.content.querySelector(`#${uid}_form`);
		this.DOM.buttonSet = this.DOM.content.querySelector(`#${uid}_buttonset`);
		this.DOM.editButton = this.DOM.content.querySelector(`#${uid}_but_edit`);
		this.DOM.delButton = this.DOM.content.querySelector(`#${uid}_but_del`);
		this.DOM.sidebarInner = this.DOM.content.querySelector(`#${uid}_sidebar_inner`);
		this.DOM.chatLink = this.DOM.content.querySelector(`#${uid}_but_chat`);

		if (this.DOM.chatLink)
		{
			Event.bind(this.DOM.chatLink, 'click', () => {
				EntryManager.openChatForEntry({
					entryId: this.entry.parentId,
					entry: this.entry
				});
			});
		}

		if (this.DOM.buttonSet)
		{
			this.initPlannerControl(uid);
			this.initUserListControl(uid);
		}

		if (this.DOM.content.querySelector(`#${uid}_time_inner_wrap`).offsetHeight > 50)
		{
			this.BX.addClass(this.DOM.content.querySelector(`#${uid}_time_wrap`), 'calendar-slider-sidebar-head-long-time');
		}

		if (this.canDo(this.entry, 'edit') && this.DOM.editButton)
		{
			Event.bind(this.DOM.editButton, 'click', () => {
				this.BX.SidePanel.Instance.close(false, function ()
				{
					EntryManager.openEditSlider({
						entry: this.entry,
						type: this.type,
						ownerId: this.ownerId,
						userId: this.userId
					});
				}.bind(this));
			});
		}
		else
		{
			this.BX.remove(this.DOM.editButton);
		}

		if (this.DOM.sidebarInner)
		{
			// Reminder
			this.DOM.reminderWrap = this.DOM.sidebarInner.querySelector('.calendar-slider-sidebar-remind-wrap');
			if (Type.isDomNode(this.DOM.reminderWrap))
			{
				let viewMode = !this.canDo(this.entry, 'edit')
					&& this.entry.getCurrentStatus() === false;

				this.reminderControl = new Reminder({
					wrap: this.DOM.reminderWrap,
					zIndex: this.zIndex,
					viewMode: viewMode
				});
				this.reminderControl.setValue(this.entry.getReminders());

				if (!viewMode)
				{
					this.reminderControl.subscribe('onChange', function (event) {
						if (event instanceof BaseEvent)
						{
							this.reminderValues = event.getData().values;
							this.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
								data: {
									entryId: this.entry.id,
									userId: this.userId,
									reminders: this.reminderValues
								}
							});
						}
					}.bind(this));
				}
			}

			let items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');
			if (items.length >= 2)
			{
				this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
			}
		}
		//this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(Tag.render`<span></span>`);
		if (this.canDo(this.entry, 'delete'))
		{
			Event.bind(this.DOM.delButton, 'click', ()=>{
				EntryManager.deleteEntry(this.entry);
			});
		}
		else
		{
			this.BX.remove(this.DOM.delButton);
		}

		this.BX.viewElementBind(
			uid + '_' + this.entry.id + '_files_wrap',
			{
				showTitle: true
			},
			function(node)
			{
				return Type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
			}
		);

		if (this.entry && this.entry.isMeeting())
		{
			this.initAcceptMeetingControl(uid);

			const attendees = this.entry.getAttendees();
			if (Type.isArray(attendees))
			{
				if (window.location.host === 'cp.bitrix.ru'
					&& this.DOM.chatLink
					&& attendees.length > 1
					&& attendees.find((user)=>{return user.STATUS !== 'N' && parseInt(user.ID) === parseInt(this.userId);})
				)
				{
					this.DOM.chatLink.style.display = '';
				}
			}
		}

		if (this.DOM.sidebarInner)
		{
			let items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');
			if (items.length >= 2)
			{
				this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
			}
		}

		this.DOM.copyButton = this.DOM.content.querySelector(`#${uid}_copy_url_btn`);
		if (this.DOM.copyButton)
		{
			Event.bind(this.DOM.copyButton, 'click', this.copyEventUrl.bind(this));
		}
	}

	handleEntryData(entryData, userIndex, sectionData)
	{
		this.entry = new Entry({data: entryData, userIndex: userIndex});

		if (Type.isPlainObject(sectionData))
		{
			this.permissions = sectionData.PERM;
		}
	}

	initPlannerControl(uid)
	{
		this.plannerId = uid + '_view_slider_planner';
		this.DOM.plannerWrap = this.DOM.content.querySelector(`#${uid}_view_planner_wrap`);

		setTimeout(function()
		{
			if (this.DOM.plannerWrap)
			{
				this.BX.removeClass(this.DOM.plannerWrap, 'hidden');
			}
		}.bind(this), 500);

		setTimeout(()=>{
			if (this.DOM.plannerWrap && this.DOM.plannerWrap.offsetWidth)
			{
				this.BX.onCustomEvent('OnCalendarPlannerDoResize', [
					{
						plannerId: this.plannerId,
						timeoutCheck: true,
						width: this.DOM.plannerWrap.offsetWidth
					}
				]);
			}
		}, 200);

		Event.bind(window, 'resize', ()=>{
			if (this.DOM.plannerWrap && this.DOM.plannerWrap.offsetWidth)
			{
				this.BX.onCustomEvent('OnCalendarPlannerDoResize', [
					{
						plannerId: this.plannerId,
						timeoutCheck: true,
						width: this.DOM.plannerWrap.offsetWidth
					}
				]);
			}
		});
	}

	initUserListControl(uid)
	{
		let userList = {y : [], i: [], q: [], n: []};

		if (this.entry.isMeeting())
		{
			this.entry.getAttendees().forEach(function(user)
			{
				if (user.STATUS === 'H')
				{
					userList.y.push(user);
				}
				else if (userList[user.STATUS.toLowerCase()])
				{
					userList[user.STATUS.toLowerCase()].push(user);
				}
			}, this);
		}

		this.DOM.attendeesListY = this.DOM.content.querySelector(`#${uid}_attendees_y`);
		this.DOM.attendeesListN = this.DOM.content.querySelector(`#${uid}_attendees_n`);
		this.DOM.attendeesListQ = this.DOM.content.querySelector(`#${uid}_attendees_q`);
		this.DOM.attendeesListI = this.DOM.content.querySelector(`#${uid}_attendees_i`);

		Event.bind(this.DOM.attendeesListY, 'click', ()=>{this.showUserListPopup(this.DOM.attendeesListY, userList.y);});
		Event.bind(this.DOM.attendeesListN, 'click', ()=>{this.showUserListPopup(this.DOM.attendeesListN, userList.n);});
		Event.bind(this.DOM.attendeesListQ, 'click', ()=>{this.showUserListPopup(this.DOM.attendeesListQ, userList.q);});
		Event.bind(this.DOM.attendeesListI, 'click', ()=>{this.showUserListPopup(this.DOM.attendeesListI, userList.i);});
	}

	showUserListPopup(node, userList)
	{
		if (this.userListPopup)
		{
			this.userListPopup.close();
		}

		if (userList && userList.length)
		{
			this.DOM.userListPopupWrap = this.BX.create('DIV', {props: {className: 'calendar-user-list-popup-block'}});
			userList.forEach(function (user)
			{
				let userWrap = this.DOM.userListPopupWrap.appendChild(this.BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card'}}));

				userWrap.appendChild(this.BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-avatar'}}))
					.appendChild(this.BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-item'}}))
					.appendChild(this.BX.create('IMG', {props: {width: 34, height: 34, src: user.AVATAR}}));

				if (user.EMAIL_USER)
				{
					userWrap.appendChild(this.BX.create("DIV", {props: {className: 'calendar-slider-sidebar-user-info'}}))
						.appendChild(this.BX.create("span", {
							text: user.DISPLAY_NAME
						}));
				}
				else
				{
					userWrap.appendChild(this.BX.create("DIV", {props: {className: 'calendar-slider-sidebar-user-info'}}))
						.appendChild(this.BX.create("A", {
							props: {
								href: user.URL ? user.URL : '#', className: 'calendar-slider-sidebar-user-info-name'
							}, text: user.DISPLAY_NAME
						}));
				}
			}, this);

			this.userListPopup = this.BX.PopupWindowManager.create("user-list-popup-" + Math.random(), node, {
				autoHide: true,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				resizable: false,
				lightShadow: true,
				content: this.DOM.userListPopupWrap,
				className: 'calendar-user-list-popup',
				zIndex: 4000
			});

			this.userListPopup.setAngle({offset: 36});
			this.userListPopup.show();
			this.BX.addCustomEvent(this.userListPopup, 'onPopupClose', ()=>{this.userListPopup.destroy();});
		}
	}

	initAcceptMeetingControl(uid)
	{
		this.DOM.statusButtonset = this.DOM.content.querySelector(`#${uid}_status_buttonset`);
		this.DOM.statusButtonset.style.marginRight = '12px';

		if (this.entry.getCurrentStatus() === 'H' || this.entry.getCurrentStatus() === false)
		{
			Dom.remove(this.DOM.statusButtonset);
		}
		else
		{
			this.statusControl = new MeetingStatusControl(
			{
				wrap: this.DOM.statusButtonset,
				currentStatus: this.DOM.content.querySelector(`#${uid}_current_status`).value || this.entry.getCurrentStatus()
			});

			this.statusControl.subscribe('onSetStatus', function(event)
				{
					if (event instanceof BaseEvent)
					{
						const result = EntryManager.setMeetingStatus(this.entry, event.getData().status);
						if (!result)
						{
							this.statusControl.setStatus(this.entry.getCurrentStatus(), false);
						}
					}
				}.bind(this)
			);
		}
	}

	copyEventUrl()
	{
		if(!this.entryUrl || !this.BX.clipboard.copy(this.entryUrl))
		{
			return;
		}

		this.timeoutIds = this.timeoutIds || [];
		let popup = new this.BX.PopupWindow(
			'calendar_clipboard_copy',
			this.DOM.copyButton,
			{
				content: Loc.getMessage('CALENDAR_TIP_TEMPLATE_LINK_COPIED'),
				darkMode: true,
				autoHide: true,
				zIndex: 1000,
				angle: true,
				offsetLeft: 20,
				cachable: false
			}
		);
		popup.show();

		let timeoutId;
		while(timeoutId = this.timeoutIds.pop())
		{
			clearTimeout(timeoutId);
		}
		this.timeoutIds.push(setTimeout(function(){popup.close();}, 1500));
	}

	displayError(errors = [])
	{
		//errors
	}

	canDo(entry, action)
	{
		if ((action === 'edit' || action === 'delete'))
		{
			// if ((entry.isMeeting() && entry.id !== entry.parentId)
			// 	|| entry.isResourcebooking())
			// {
			// 	return false;
			// }

			return this.permissions.edit;
		}

		if ((action === 'view'))
		{
			return this.permissions.view_full;
		}

		return false;
	}
}