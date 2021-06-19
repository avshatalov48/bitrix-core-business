// @flow
import {Util} from 'calendar.util';
import {Type, Event, Loc, Tag, Dom} from 'main.core';
import {Entry, EntryManager} from "calendar.entry";
import {MeetingStatusControl, Reminder} from "calendar.controls";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {EditForm} from "./editform"
import {TrackingUsersForm} from "./trackingusersform"
import {TrackingGroupsForm} from "./trackinggroupsform"
import {TrackingTypesForm} from "./trackingtypesform"
import { CalendarSection } from 'calendar.sectionmanager';

export class SectionInterface extends EventEmitter
{
	name = 'sectioninterface';
	uid = null;
	DOM = {};
	SLIDER_WIDTH = 400;
	SLIDER_DURATION = 80;
	sliderId = "calendar:section-slider";
	denyClose = false;

	constructor({calendarContext, readonly, sectionManager})
	{
		super();
		this.setEventNamespace('BX.Calendar.SectionInterface');
		this.sectionManager = sectionManager;
		this.calendarContext = calendarContext;
		this.readonly = readonly;
		this.BX = Util.getBX();
		this.sliderOnClose = this.hide.bind(this);
		this.deleteSectionHandlerBinded = this.deleteSectionHandler.bind(this);
		this.refreshSectionListBinded = this.refreshSectionList.bind(this);

		if (this.calendarContext.util.config.accessNames)
		{
			Util.setAccessNames(this.calendarContext?.util?.config?.accessNames);
		}
	}

	show()
	{
		this.BX.SidePanel.Instance.open(this.sliderId, {
			contentCallback: this.createContent.bind(this),
			width: this.SLIDER_WIDTH,
			animationDuration: this.SLIDER_DURATION,
			events: {
				onCloseByEsc: this.escHide.bind(this),
				onClose: this.sliderOnClose,
				onCloseComplete: this.destroy.bind(this),
				onLoad: this.onLoadSlider.bind(this)
			}
		});

		this.addEventEmitterSubscriptions();
	}

	addEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Section:delete',
			this.deleteSectionHandlerBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Section:pull-delete',
			this.deleteSectionHandlerBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Section:edit',
			this.refreshSectionListBinded);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Section:pull-edit',
			this.refreshSectionListBinded
		);
	}

	destroyEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:delete',
			this.deleteSectionHandlerBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:pull-delete',
			this.deleteSectionHandlerBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:edit',
			this.refreshSectionListBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:pull-edit',
			this.refreshSectionListBinded
		);
	}

	escHide(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId && this.denyClose)
		{
			event.denyAction();
		}
	}

	hide(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			this.closeForms();
			this.destroyEventEmitterSubscriptions();
		}
	}

	close()
	{
		BX.SidePanel.Instance.close();
	}

	destroy(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			this.destroyEventEmitterSubscriptions();

			Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
			Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);

			BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
			BX.SidePanel.Instance.destroy(this.sliderId);
			delete this.DOM.sectionListWrap;

			//this.calendarContext.enableKeyHandler();
			if (this.sectionActionMenu)
			{
				this.sectionActionMenu.close();
			}
		}
	}

	createContent()
	{
		// this.BX.onCustomEvent(top, 'onCalendarBeforeCustomSliderCreate');
		this.DOM.outerWrap = Dom.create('DIV', {props: {className: 'calendar-list-slider-wrap'}});
		this.DOM.titleWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-list-slider-title-container'}, html: '<div class="calendar-list-slider-title">' + Loc.getMessage('EC_SECTION_BUTTON') + '</div>'}));

		if (!this.readonly)
		{
			// #1. Controls
			this.createAddButton();

			// #2. Forms
			this.DOM.sectionFormWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
				html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION') + '</span></div>'
			}));

			this.DOM.trackingCompanyFormWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
				html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP') + '</span></div>'
			}));

			this.DOM.trackingUsersFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div>
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP')}
							</span>
						</div>
					</div>
				`
			);

			this.DOM.trackingGroupsFormWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
				html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP') + '</span></div>'
			}));
		}

		// #3. List of sections
		this.createSectionList();

		return this.DOM.outerWrap;
	}

	onLoadSlider(event)
	{
		this.slider = event.getSlider();
		this.sliderId = this.slider.getUrl();
		this.DOM.content = this.slider.layout.content;

		// Used to execute javasctipt and attach CSS from ajax responce
		//this.BX.html(this.slider.layout.content, this.slider.getData().get("sliderContent"));

		// this.initControls(this.uid);
		// this.setFormValues();
	}

	createSectionList()
	{
		let title;
		this.sliderSections = this.sectionManager.getSections();
		const type = this.sectionManager.calendarType;

		if (type === 'user')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
		}
		else if (type === 'group')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
		}
		else
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TYPE_CALENDARS_LIST');
		}

		if (this.DOM.sectionListWrap)
		{
			Dom.clean(this.DOM.sectionListWrap);
			Dom.adjust(this.DOM.sectionListWrap, {
				props: {className: 'calendar-list-slider-card-widget'},
				html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
			});
		}
		else
		{
			this.DOM.sectionListWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-widget'},
				html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
			}));
		}

		this.createSectionBlock({
			wrap: this.DOM.sectionListWrap,
			sectionList: this.sliderSections.filter(function(section){
				return section.belongsToView() || section.isPseudo();
			})
		});

		// Company calendar
		let sections = this.sliderSections.filter(function(section)
		{
			return section.isCompanyCalendar() && !section.belongsToView();
		});

		if (sections.length > 0)
		{
			this.DOM.sectionListWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-section-title'},
				html: '<span class="calendar-list-slider-card-section-title-text">' + Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL') + '</span>'
			}));

			this.createSectionBlock({
				wrap: this.DOM.sectionListWrap,
				sectionList: this.sliderSections.filter(function (section)
				{
					return section.isCompanyCalendar();
				})
			});
		}

		// Users calendars
		this.calendarContext.util.getSuperposedTrackedUsers().forEach((user) => {
			const sections = this.sliderSections.filter((section) => {
				return !section.belongsToView()
					&& section.type === 'user'
					&& section.data.OWNER_ID === user.ID;
			});

			if (sections.length > 0)
			{
				this.DOM.sectionListWrap.appendChild(Dom.create('DIV', {
					props: {className: 'calendar-list-slider-card-section-title'},
					html: '<span class="calendar-list-slider-card-section-title-text">' + BX.util.htmlspecialchars(user.FORMATTED_NAME) + '</span>'
				}));
				this.createSectionBlock({
					wrap: this.DOM.sectionListWrap,
					sectionList: sections
				});
			}
		}, this);

		// Groups calendars
		sections = this.sliderSections.filter((section) => {
			return !section.belongsToView() && section.type === 'group';
		});

		if (sections.length > 0)
		{
			this.DOM.sectionListWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-list-slider-card-section-title'},
				html: '<span class="calendar-list-slider-card-section-title-text">' + Loc.getMessage('EC_SEC_SLIDER_TITLE_GROUP_CAL') + '</span>'
			}));
			this.createSectionBlock({
				wrap: this.DOM.sectionListWrap,
				sectionList: sections
			});
		}
	}

	createAddButton()
	{
		if (
			this.calendarContext.util.config.perm
			&& this.calendarContext.util.config.perm.edit_section
		)
		{
			const addButtonOuter = this.DOM.titleWrap.appendChild(
				Dom.create('SPAN',
					{
						props: {className: 'ui-btn-split ui-btn-light-border'},
						style: {marginRight: 0}
					}
				)
			);

			this.DOM.addButton = addButtonOuter.appendChild(Dom.create('SPAN', {props: {className: 'ui-btn-main'}, text: Loc.getMessage('EC_ADD')}));
			this.DOM.addButtonMore = addButtonOuter.appendChild(Dom.create('SPAN', {props: {className: 'ui-btn-extra'}}));

			Event.bind(this.DOM.addButtonMore, 'click', this.showAddButtonPopup.bind(this));
			Event.bind(this.DOM.addButton, 'click', this.showEditSectionForm.bind(this));
		}
	}

	showAddButtonPopup()
	{
		if (this.addBtnMenu && this.addBtnMenu.popupWindow && this.addBtnMenu.popupWindow.isShown())
		{
			return this.addBtnMenu.close();
		}

		const submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';
		const menuItems = [
				{
					html: '<span>' + Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_TITLE') + '</span>',
					className: submenuClass
				},
				{
					html: Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_MENU'),
					onclick: () => {
						this.addBtnMenu.close();
						this.showEditSectionForm();
					}
				},
				{
					html: '<span>' + Loc.getMessage('EC_SEC_SLIDER_POPUP_EXIST_TITLE') + '</span>',
					className: submenuClass
				},
				{
					html: Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP'),
					onclick: () => {
						this.addBtnMenu.close();
						this.showTrackingTypesForm();
					}
				},
				{
					html: Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_USER'),
					onclick: () => {
						this.addBtnMenu.close();
						this.showTrackingUsersForm();
					}
				},
				{
					html: Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
					onclick: () => {
						this.addBtnMenu.close();
						this.showTrackingGroupsForm();
					}
				}
			];

		this.addBtnMenu = this.BX.PopupMenu.create(
			'add-btn-' + Util.getRandomInt(),
			this.DOM.addButtonMore,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 15,
				angle: true,
				cacheable: false
			}
		);

		this.addBtnMenu.show();

		//Dom.addClass(_this.sectionField.select, 'active');
		// this.denySliderClose();

		// top.BX.addCustomEvent(this.addBtnMenu.popupWindow, 'onPopupClose', function()
		// {
		// 	_this.allowSliderClose();
		// });
	}

	createSectionBlock({sectionList, wrap})
	{
		if (Type.isArray(sectionList))
		{
			const listWrap = wrap.appendChild(
				Dom.create('DIV', {props: {className: 'calendar-list-slider-widget-content'}}))
					.appendChild(Dom.create('DIV', {props: {className: 'calendar-list-slider-widget-content-block'}}))
					.appendChild(Dom.create('UL', {props: {className: 'calendar-list-slider-container'}}));

			Event.bind(listWrap, 'click', this.sectionClickHandler.bind(this));

			sectionList.forEach((section) => {

				if (!section.DOM)
				{
					section.DOM = {};
				}

				const sectionId = section.id.toString();
				const li = listWrap.appendChild(Dom.create('LI', {
					props: {className: 'calendar-list-slider-item'},
					attrs: {'data-bx-calendar-section': sectionId}
				}));

				const checkbox = li.appendChild(Dom.create('DIV', {
					props: {
						className: 'calendar-list-slider-item-checkbox'
							+ (section.isShown() ? ' calendar-list-slider-item-checkbox-checked' : '')
					},
					style: {backgroundColor: section.color}
				}));

				const title = li.appendChild(Dom.create('DIV', {
					props: {
						className: 'calendar-list-slider-item-name',
						title: section.name,
					},
					text: section.name
				}));

				section.DOM.item = li;
				section.DOM.checkbox = checkbox;
				section.DOM.title = title;

				//if (sectionId !== 'tasks' || this.calendarContext.util.userIsOwner())
				{
					const actionCont = li.appendChild(Dom.create('DIV', {
						props: {className: 'calendar-list-slider-item-actions-container'},
						attrs: {'data-bx-calendar-section-menu': sectionId},
						html: '<span class="calendar-list-slider-item-context-menu"></span>'
					}));
					section.DOM.actionCont = actionCont;
				}

			});
		}
	}

	sectionClickHandler(e)
	{
		const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

		if (target && target.getAttribute)
		{
			if (target.getAttribute('data-bx-calendar-section-menu') !== null)
			{
				let sectionId = target.getAttribute('data-bx-calendar-section-menu');
				sectionId = sectionId === 'tasks' ? sectionId : parseInt(sectionId);
				this.showSectionMenu(this.sectionManager.getSection(sectionId), target);
			}
			else if(target.getAttribute('data-bx-calendar-section') !== null)
			{
				this.switchSection(this.sectionManager.getSection(target.getAttribute('data-bx-calendar-section')));
			}
		}
	}

	switchSection(section)
	{
		const checkboxNodes = this.DOM.sectionListWrap.querySelectorAll(
			'.calendar-list-slider-item[data-bx-calendar-section=\''
			+ section.id
			+ '\'] .calendar-list-slider-item-checkbox'
		);

		for (let i = 0; i < checkboxNodes.length; i++)
		{
			if (section.isShown())
			{
				Dom.removeClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
			}
			else
			{
				Dom.addClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
			}
		}

		if (section.isShown())
		{
			section.hide();
		}
		else
		{
			section.show();
		}

		// TODO: should use eventEmtter
		this.calendarContext.reload();
	}

	showSectionMenu (section, menuItemNode)
	{
		const menuItems = [];
		const itemNode = menuItemNode.closest('[data-bx-calendar-section]');
		if (Type.isElementNode(itemNode))
		{
			Dom.addClass(itemNode, 'active');
		}

		if (!section.isPseudo() && section.getLink() && !section.belongsToView())
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_OPEN_LINK'),
				href: section.getLink()
			});
		}

		if (!this.readonly && section.canDo('edit_section') && !section.isPseudo())
		{
			menuItems.push({
				text : Loc.getMessage('EC_SEC_EDIT'),
				onclick: () => {
					this.sectionActionMenu.close();
					this.showEditSectionForm({section: section});
				}
			});
		}

		if (section.isSuperposed() && !section.belongsToView())
		{
			menuItems.push({
				text : Loc.getMessage('EC_SEC_HIDE'),
				onclick: () => {
					this.hideSuperposedHandler(section);
					this.sectionActionMenu.close();
				}
			});
		}

		if (section.canBeConnectedToOutlook())
		{
			menuItems.push({
				text : Loc.getMessage('EC_SEC_CONNECT_TO_OUTLOOK'),
				onclick: () => {
					this.sectionActionMenu.close();
					section.connectToOutlook();
					this.close();
				}
			});
		}

		if (!section.isPseudo() && section.data.EXPORT && section.data.EXPORT.LINK)
		{
			menuItems.push({
				text: Loc.getMessage('EC_ACTION_EXPORT'),
				onclick: () => {
					this.sectionActionMenu.close();
					const options = {
						sectionLink: section.data.EXPORT.LINK,
						calendarPath: this.calendarContext.util.config.path,
					};
					if (BX.Calendar.Sync.Interface.IcalSyncPopup.checkPathes(options))
					{
						BX.Calendar.Sync.Interface.IcalSyncPopup.createInstance(options).show();
					}
					else
					{
						BX.Calendar.Sync.Interface.IcalSyncPopup.showPopupWithPathesError();
					}
				}
			});
		}

		if (section.canDo('edit_section') && section.belongsToView() && !section.isPseudo())
		{
			menuItems.push({
				text : Loc.getMessage('EC_SEC_DELETE'),
				onclick: () => {
					this.sectionActionMenu.close();
					section.remove();
				}
			});
		}

		if ((section.isGoogle() || section.isCalDav()) && section.canDo('edit_section'))
		{
			menuItems.push({
				text : Loc.getMessage('EC_ACTION_REFRESH'),
				onclick: () => {
					this.sectionActionMenu.close();
					this.calendarContext.reload({syncGoogle: true});
					this.close();
				}
			});

			if (this.calendarContext.syncInterface && this.calendarContext.syncInterface.syncButton)
			{
				menuItems.push({
					text : Loc.getMessage('EC_ACTION_EXTERNAL_ADJUST'),
					onclick: () => {
						this.sectionActionMenu.close();
						this.calendarContext.syncInterface.syncButton.handleClick();
					}
				});
			}

			menuItems.push({
				text: Loc.getMessage('EC_ACTION_HIDE'),
				onclick: () => {
					this.sectionActionMenu.close();
					section.hideGoogle();
				}
			});
		}

		if (section.isPseudo())
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_EDIT'),
				onclick: () => {
					this.sectionActionMenu.close();
					this.showEditSectionForm({
						section: section
					});
				}
			});

			menuItems.push({
				text : Loc.getMessage('EC_SEC_TASK_HIDE'),
				onclick: () => {
					this.sectionActionMenu.close();
					BX.userOptions.save('calendar', 'user_settings', 'showTasks', 'N');

					Dom.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
					setTimeout(() => {
						Dom.clean(section.DOM.item, true);
						BX.reload();
					}, 300);
				}
			});
		}

		if (menuItems && menuItems.length > 0)
		{
			this.sectionActionMenu = top.BX.PopupMenu.create(
				'section-menu-' + Util.getRandomInt(),
				menuItemNode,
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

			this.sectionActionMenu.show();
			this.sectionActionMenu.popupWindow.subscribe('onClose', ()=>{
				if (Type.isElementNode(itemNode))
				{
					Dom.removeClass(itemNode, 'active');
				}
				this.allowSliderClose();
			});

			this.denySliderClose();
		}
	}

	denySliderClose()
	{
		this.denyClose = true;
	}

	allowSliderClose()
	{
		this.denyClose = false;
	}

	closeForms()
	{
		if (this.addBtnMenu)
		{
			this.addBtnMenu.close();
		}

		if (this.editSectionForm)
		{
			this.editSectionForm.close();
		}

		if (this.trackingUsersForm)
		{
			this.trackingUsersForm.close();
		}

		if (this.trackingGroupsForm)
		{
			this.trackingGroupsForm.close();
		}

		if (this.trackingTypesForm)
		{
			this.trackingTypesForm.close();
		}
	}

	showEditSectionForm(params ={})
	{
		this.closeForms();
		const formTitleNode = this.DOM.sectionFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');

		this.editSectionForm = new EditForm({
			wrap: this.DOM.sectionFormWrap,
			sectionAccessTasks: this.sectionManager.getSectionAccessTasks(),
			sectionManager: this.sectionManager,
			closeCallback: () => {
				this.allowSliderClose();
			}
		});

		let showAccessControl = true;
		if (params.section && (!params.section.belongsToView() || params.section.isPseudo()))
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_PERSONAL');
			showAccessControl = false;
		}
		else if (params.section && params.section.id)
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION');
			showAccessControl = params.section.canDo('access');
		}
		else
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');
		}

		this.editSectionForm.show({
			showAccess: showAccessControl,
			section: params.section || {
				color: Util.getRandomColor(),
				access: this.sectionManager.getDefaultSectionAccess()
			}
		});

		this.denySliderClose();
	}

	showTrackingTypesForm()
	{
		this.closeForms();

		if (!this.trackingTypesForm)
		{
			this.trackingTypesForm = new TrackingTypesForm({
				wrap: this.DOM.trackingCompanyFormWrap,
				superposedSections: this.sectionManager.getSuperposedSectionList(),
				closeCallback: () => {
					this.allowSliderClose();
				}
			});
		}

		this.trackingTypesForm.show();
		this.denySliderClose();
	}

	showTrackingUsersForm()
	{
		this.closeForms();

		if (!this.trackingUsersForm)
		{
			this.trackingUsersForm = new TrackingUsersForm({
				wrap: this.DOM.trackingUsersFormWrap,
				trackingUsers: this.calendarContext.util.getSuperposedTrackedUsers(),
				superposedSections: this.sectionManager.getSuperposedSectionList(),
				closeCallback: () => {
					this.allowSliderClose();
				}
			});
		}

		this.trackingUsersForm.show();
		this.denySliderClose();
	}

	showTrackingGroupsForm()
	{
		this.closeForms();

		if (!this.trackingGroupsForm)
		{
			const superposedSections = this.sectionManager.getSuperposedSectionList();
			const trackingGroups = this.calendarContext.util.getSuperposedTrackedGroups();

			superposedSections.forEach((section) => {
				if (section.getType() === 'group'
					&& !trackingGroups.includes(section.getOwnerId()))
				{
					trackingGroups.push(section.getOwnerId());
				}
			});

			this.trackingGroupsForm = new TrackingGroupsForm({
				wrap: this.DOM.trackingGroupsFormWrap,
				trackingGroups: trackingGroups,
				superposedSections: superposedSections,
				closeCallback: () => {
					this.allowSliderClose();
				}
			});
		}

		this.trackingGroupsForm.show();
		this.denySliderClose();
	}

	deleteSectionHandler(event)
	{
		if (event && event instanceof Util.getBX().Event.BaseEvent)
		{
			const data = event.getData();
			const sectionId = parseInt(data.sectionId, 10);

			this.sliderSections.forEach((section, index) => {

				if (parseInt(section.id) === sectionId && section.DOM && section.DOM.item)
				{
					Dom.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
					setTimeout(() => {
						Dom.clean(section.DOM.item, true);
						this.sliderSections = BX.util.deleteFromArray(this.sliderSections, index);
					}, 300);
				}
			}, this);
		}
	}

	hideSuperposedHandler(section)
	{
		const superposedSections = this.sectionManager.getSuperposedSectionList();
		const sections = [];
		let i;

		for (i = 0; i < superposedSections.length; i++)
		{
			if (parseInt(section.id) !== parseInt(superposedSections[i].id))
			{
				sections.push(parseInt(superposedSections[i].id));
			}
		}

		BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
				data: {
					sections: sections
				}
			})
			.then(
				// Success
				(response) => {
					BX.reload();
				},
				// Failure
				(response) => {
					Util.displayError(response.errors);
				}
			);
	}

	refreshSectionList()
	{
		this.createSectionList();
	}
}