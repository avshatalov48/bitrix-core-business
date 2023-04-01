// @flow
import { Util } from 'calendar.util';
import { IcalSyncPopup } from 'calendar.sync.interface';
import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MenuItem } from 'main.popup';
import { EditForm } from './editform';
import { TrackingUsersForm } from './trackingusersform';
import { TrackingGroupsForm } from './trackinggroupsform';
import { TrackingTypesForm } from './trackingtypesform';
import { SectionManager } from 'calendar.sectionmanager';
import { MessageBox } from 'ui.dialogs.messagebox';

export class SectionInterface extends EventEmitter
{
	name = 'sectioninterface';
	uid = null;
	DOM = {};
	SLIDER_WIDTH = 400;
	SLIDER_DURATION = 80;
	sliderId = "calendar:section-slider";
	denyClose = false;
	deletedSectionsIds = [];

	constructor({calendarContext, readonly, sectionManager})
	{
		super();
		this.setEventNamespace('BX.Calendar.SectionInterface');
		this.sectionManager = sectionManager;
		this.calendarContext = calendarContext;
		this.readonly = readonly;
		this.BX = Util.getBX();
		this.deleteSectionHandlerBinded = this.deleteSectionHandler.bind(this);
		this.refreshSectionListBinded = this.refreshSectionList.bind(this);
		this.keyHandlerBinded = this.keyHandler.bind(this);

		if(this.calendarContext !== null)
		{
			if (this.calendarContext.util.config.accessNames)
			{
				Util.setAccessNames(this.calendarContext?.util?.config?.accessNames);
			}
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
				onClose: this.hide.bind(this),
				onCloseComplete: this.destroy.bind(this),
				onLoad: this.onLoadSlider.bind(this)
			}
		});

		this.addEventEmitterSubscriptions();

		Event.bind(document, 'keydown', this.keyHandlerBinded);
	}

	addEventEmitterSubscriptions()
	{
		this.BX.Event.EventEmitter.subscribe(
			'BX.Calendar.Section:delete',
			this.deleteSectionHandlerBinded
		);
		this.BX.Event.EventEmitter.subscribe(
			'BX.Calendar.Section:pull-delete',
			this.deleteSectionHandlerBinded
		);
		this.BX.Event.EventEmitter.subscribe(
			'BX.Calendar.Section:edit',
			this.refreshSectionListBinded
		);

		this.BX.Event.EventEmitter.subscribe(
			'BX.Calendar.Section:pull-reload-data',
			this.refreshSectionListBinded
		);
	}

	destroyEventEmitterSubscriptions()
	{
		this.BX.Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:delete',
			this.deleteSectionHandlerBinded
		);
		this.BX.Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:pull-delete',
			this.deleteSectionHandlerBinded
		);
		this.BX.Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:edit',
			this.refreshSectionListBinded
		);
		this.BX.Event.EventEmitter.unsubscribe(
			'BX.Calendar.Section:pull-reload-data',
			this.refreshSectionListBinded
		);
	}

	escHide(event)
	{
		if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId && this.denyClose)
		{
			event.denyAction();
		}
	}

	hide(event)
	{
		if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId)
		{
			this.closeForms();
			this.destroyEventEmitterSubscriptions();
			Event.unbind(document, 'keydown', this.keyHandlerBinded);
		}
	}

	close()
	{
		BX.SidePanel.Instance.close();
	}

	destroy(event)
	{
		if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId)
		{
			this.destroyEventEmitterSubscriptions();
			Event.unbind(document, 'keydown', this.keyHandlerBinded);

			Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
			Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);

			BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
			BX.SidePanel.Instance.destroy(this.sliderId);
			delete this.DOM.localSectionListWrap;

			this.deletedSectionsIds = [];

			if (this.sectionActionMenu)
			{
				this.sectionActionMenu.close();
			}
			if (this.trackingTypesForm)
			{
				delete this.trackingTypesForm;
			}
			if (this.trackingUsersForm)
			{
				delete this.trackingUsersForm;
			}
			if (this.trackingGroupsForm)
			{
				delete this.trackingGroupsForm;
			}
		}
	}

	createContent()
	{
		this.DOM.outerWrap = Tag.render`
			<div class="calendar-list-slider-wrap"></div>
		`;
		this.DOM.titleWrap = this.DOM.outerWrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-title-container">
					<div class="calendar-list-slider-title"> 
						${Loc.getMessage('EC_SECTION_BUTTON')}
					</div>
				</div>
			`
		);
		const calendarContext = this.calendarContext || Util.getCalendarContext();

		if (calendarContext && !this.readonly)
		{
			this.DOM.sectionFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION')}
							</span>
						</div>
					</div>
				`
			);
		}

		if (
			calendarContext
			&& !this.readonly
			&&
			(
				!calendarContext.util.isUserCalendar()
				|| calendarContext.util.userIsOwner()
			)
		)
		{
			// #1. Controls
			this.createAddButton();

			// #2. Forms
			this.DOM.trackingGroupsFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP')}
							</span>
						</div>
					</div>								
				`
			);
			this.DOM.trackingUsersFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_USER')}
							</span>
						</div>
					</div>
				`
			);
			this.DOM.trackingTypesFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP')}
							</span>
						</div>
					</div>								
				`
			);
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
	}

	createSectionList()
	{
		this.sliderSections = this.sectionManager.getSections().filter(section => {
			return !this.deletedSectionsIds.find(id => id === section.id);
		});
		if (Type.isElementNode(this.DOM.sectonListOuterWrap))
		{
			Dom.remove(this.DOM.sectonListOuterWrap);
		}
		this.DOM.sectonListOuterWrap = this.DOM.outerWrap.appendChild(Tag.render`<div></div>`);
		Event.bind(this.DOM.sectonListOuterWrap, 'click', this.sectionClickHandler.bind(this));

		this.createLocalSectionsList();
		this.createExternalSectionsList();
	}

	createLocalSectionsList()
	{
		this.DOM.localSectionListWrap = this.DOM.sectonListOuterWrap.appendChild(
			this.getSectionListWrap(this.getLocalSectionListTitle())
		);

		this.createSectionsBlock({
			wrap: this.DOM.localSectionListWrap,
			sectionList: this.sliderSections.filter(section => {
				return (section.externalTypeIsLocal() && section.belongsToView()) || section.isPseudo();
			})
		});

		this.createCompanySectionList();
		this.createUsersSectionList();
		this.createGroupsSectionList();
	}

	createExternalSectionsList()
	{
		const externalSections = this.sliderSections.filter(section => {
			return !section.externalTypeIsLocal() && section.belongsToView();
		});

		this.DOM.extSectionListWrap = [];

		externalSections.forEach(section => {
			const listWrap = this.getSectionListWrapForSection(section);

			this.createSectionUnit({
				section: section,
				wrap: listWrap
			});
		});
	}

	getSectionListWrapForSection(section)
	{
		let sectionExternalType = section.getExternalType();
		if (section.isGoogle())
		{
			sectionExternalType = 'google';
		}
		if (section.data['IS_EXCHANGE'])
		{
			sectionExternalType = 'exchange';
		}

		const sectionExternalConnection = SectionManager.getSectionExternalConnection(section, sectionExternalType);
		const calendarContext = this.calendarContext || Util.getCalendarContext();

		section.data.CAL_DAV_CON = sectionExternalConnection?.addParams?.id || null;
		let key = sectionExternalType + (
			sectionExternalConnection
				? sectionExternalConnection.getId()
				: '-disconnected'
		);

		if (!Type.isElementNode(this.DOM.extSectionListWrap[key]))
		{
			const sectionListWrap = this.DOM.sectonListOuterWrap.appendChild(
				this.getSectionListWrap(this.getExternalConnectionBlockTitle(
					{
						type: sectionExternalType,
						connection: sectionExternalConnection
					})
				)
			);

			sectionListWrap.appendChild(Tag.render`
				<div class="calendar-list-slider-widget-content">
					<div class="calendar-list-slider-widget-content-block">
						${this.DOM.extSectionListWrap[key] = Tag.render`<ul class="calendar-list-slider-container"/>`}
					</div>
				</div>
			`);

			if (
				!sectionExternalConnection
				&& calendarContext
				&& calendarContext.util.userIsOwner()
				&& !section.isArchive()
				&& (
					!section.isExchange()
					||
					(!calendarContext.util.config.bExchange && section.isExchange())
				)
			)
			{
				sectionListWrap.querySelector('.calendar-list-slider-widget-content-block')
					.appendChild(
						Tag.render`
							<div data-bx-calendar-open-sync="Y" class="calendar-list-slider-card-widget-bottom-button">
								<span class="calendar-list-slider-link">
									${Loc.getMessage('EC_SEC_SLIDER_ADJUST_SYNC')}
								</span>
							</div>`
					);

				sectionListWrap.querySelector('.calendar-list-slider-card-widget-title')
					.appendChild(
						Tag.render`
							<span class="calendar-list-slider-card-widget-title-text calendar-list-title-disabled" >
								${Loc.getMessage('EC_SEC_SLIDER_SYNC_DISABLED')}
							</span>`,
					);
			}
			else if (section.isArchive())
			{
				const hintNode = sectionListWrap.querySelector('.calendar-list-slider-card-widget-title')
					.appendChild(Tag.render`
						<div class="ui-icon ui-icon-common-question calendar-list-slider-archive-hint"
						data-hint="${Loc.getMessage('EC_SEC_SLIDER_TYPE_ARCHIVE_HELPER')}">
							<i></i>	
						</div>
				`);
				if (Type.isDomNode(hintNode))
				{
					Util.initHintNode(hintNode);
				}
			}
		}

		return this.DOM.extSectionListWrap[key];
	}

	getExternalConnectionBlockTitle({type, connection}): string
	{
		let title = '';
		const connectionName = connection
			? connection.getConnectionAccountName() || connection.getConnectionName()
			: null
		;

		switch (type)
		{
			case 'google':
				if (connectionName)
				{
					title = Loc.getMessage(
						'EC_SEC_SLIDER_TYPE_GOOGLE',
						{ '#CONNECTION_NAME#': connectionName }
					);
				}
				else
				{
					title = Loc.getMessage('EC_SEC_SLIDER_TYPE_GOOGLE_DIS')
				}

				break;
			case 'office365':
				if (connectionName)
				{
					title = Loc.getMessage(
						'EC_SEC_SLIDER_TYPE_OFFICE365',
						{ '#CONNECTION_NAME#': connectionName }
					);
				}
				else
				{
					title = Loc.getMessage('EC_SEC_SLIDER_TYPE_OFFICE365_DIS')
				}
				break;
			case 'icloud':
				if (connectionName)
				{
					title = Loc.getMessage(
						'EC_SEC_SLIDER_TYPE_ICLOUD',
						{ '#CONNECTION_NAME#': connectionName }
					);
				}
				else
				{
					title = Loc.getMessage('EC_SEC_SLIDER_TYPE_ICLOUD_DIS')
				}
				break;
			case 'caldav':
				if (connectionName)
				{
					if (connection.getType() === 'yandex')
					{
						title = Loc.getMessage(
							'EC_SEC_SLIDER_TYPE_YANDEX',
							{ '#CONNECTION_NAME#': connectionName }
						);
					}
					else
					{
						title = Loc.getMessage(
							'EC_SEC_SLIDER_TYPE_CALDAV',
							{ '#CONNECTION_NAME#': connectionName }
						);
					}
				}
				else
				{
					title = Loc.getMessage('EC_SEC_SLIDER_TYPE_DEFAULT');
				}
				break;
			case 'exchange':
				title = Loc.getMessage('EC_CAL_SYNC_EXCHANGE');
				break;
			case 'archive':
				title = Loc.getMessage('EC_SEC_SLIDER_TYPE_ARCHIVE');
				break;
			default:
				title = Loc.getMessage('EC_SEC_SLIDER_TYPE_DEFAULT')
		}

		return title;
	}

	createCompanySectionList()
	{
		const sections = this.sliderSections.filter(function(section)
		{
			return section.isCompanyCalendar() && !section.belongsToView();
		});

		if (sections.length > 0)
		{
			this.DOM.localSectionListWrap.appendChild(Tag.render`
				<div class="calendar-list-slider-card-section-title">
					<span class="calendar-list-slider-card-section-title-text">${Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL')}</span>
				</div>
			`);

			this.createSectionsBlock({
				wrap: this.DOM.localSectionListWrap,
				sectionList: this.sliderSections.filter(section => {
					return section.isCompanyCalendar();
				})
			});
		}
	}

	createUsersSectionList()
	{
		this.calendarContext.util.getSuperposedTrackedUsers().forEach((user) => {
			const sections = this.sliderSections.filter((section) => {
				return !section.belongsToView()
					&& section.type === 'user'
					&& section.data.OWNER_ID === user.ID;
			});

			if (sections.length > 0)
			{
				this.DOM.localSectionListWrap.appendChild(Tag.render`
					<div class="calendar-list-slider-card-section-title">
						<span class="calendar-list-slider-card-section-title-text">${Text.encode(user.FORMATTED_NAME)}</span>
					</div>
				`);

				this.createSectionsBlock({
					wrap: this.DOM.localSectionListWrap,
					sectionList: sections
				});
			}
		}, this);
	}

	createGroupsSectionList()
	{
		const sections = this.sliderSections.filter((section) => {
			return !section.belongsToView() && section.type === 'group';
		});

		if (sections.length > 0)
		{
			this.DOM.localSectionListWrap.appendChild(Tag.render`
				<div class="calendar-list-slider-card-section-title">
					<span class="calendar-list-slider-card-section-title-text">${Loc.getMessage('EC_SEC_SLIDER_TITLE_GROUP_CAL')}</span>
				</div>
			`);

			this.createSectionsBlock({
				wrap: this.DOM.localSectionListWrap,
				sectionList: sections
			});
		}
	}

	getSectionListWrap(title)
	{
		return Tag.render`
				<div class="calendar-list-slider-card-widget">
					<div class="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">
							${title}
						</span>
					</div>
				</div>
			`;
	}

	getLocalSectionListTitle()
	{
		if (this.sectionManager.calendarType === 'user')
		{
			return Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
		}
		else if (this.sectionManager.calendarType === 'group')
		{
			return Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
		}
		else
		{
			return Loc.getMessage('EC_SEC_SLIDER_TYPE_CALENDARS_LIST');
		}
	}

	createAddButton()
	{
		if (
			this.calendarContext.util.config.perm
			&& this.calendarContext.util.config.perm.edit_section
		)
		{
			const addButtonOuter = this.DOM.titleWrap.appendChild(Tag.render`
				<span class="ui-btn-split ui-btn-light-border" style="margin-right: 0"></span>
			`);
			this.DOM.addButton = addButtonOuter.appendChild(Tag.render`
				<span class="ui-btn-main">${Loc.getMessage('EC_ADD')}</span>
			`);
			this.DOM.addButtonMore = addButtonOuter.appendChild(Tag.render`
				<span class="ui-btn-extra"></span>
			`);

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

		const menuItems = [
			new MenuItem({
				text: Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_TITLE'),
				delimiter: true
			}),
			{
				html: Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_MENU'),
				onclick: () => {
					this.addBtnMenu.close();
					this.showEditSectionForm();
				}
			},
			new MenuItem({
				text: Loc.getMessage('EC_SEC_SLIDER_POPUP_EXIST_TITLE'),
				delimiter: true
			}),
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
	}

	createSectionsBlock({sectionList, wrap})
	{
		if (Type.isArray(sectionList))
		{
			const listWrap = wrap.appendChild(Tag.render`<div class="calendar-list-slider-widget-content"></div>`)
				.appendChild(Tag.render`<div class="calendar-list-slider-widget-content-block"></div>`)
				.appendChild(Tag.render`<ul class="calendar-list-slider-container"></ul>`)
			;

			sectionList.forEach((section) => {
				this.createSectionUnit({section, wrap: listWrap});
			});
		}
	}

	createSectionUnit({section, wrap})
	{
		if (!section.DOM)
		{
			section.DOM = {};
		}
		const sectionId = section.id.toString();
		const li = wrap.appendChild(Tag.render`
			<li class="calendar-list-slider-item" data-bx-calendar-section="${sectionId}"></li>
		`);
		const checkbox = li.appendChild(Tag.render`
			<div class="calendar-list-slider-item-checkbox ${section.isShown() ? 'calendar-list-slider-item-checkbox-checked' : ''}" style="background-color: ${section.color}"></div>
		`);
		const title = li.appendChild(Tag.render`
			<div class="calendar-list-slider-item-name" title="${Text.encode(section.name)}">${Text.encode(section.name)}</div>
		`);

		section.DOM.item = li;
		section.DOM.checkbox = checkbox;
		section.DOM.title = title;

		section.DOM.actionCont = li.appendChild(Tag.render`
			<div class="calendar-list-slider-item-actions-container" data-bx-calendar-section-menu="${sectionId}">
				<span class="calendar-list-slider-item-context-menu"></span>
			</div>
		`);
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
			else if(target.getAttribute('data-bx-calendar-open-sync') !== null)
			{
				this.calendarContext.syncInterface.openSyncPanel();
			}
		}
	}

	findCheckBoxNodes(id)
	{
		return this.DOM.sectonListOuterWrap.querySelectorAll(
			'.calendar-list-slider-item[data-bx-calendar-section=\''
			+ id
			+ '\'] .calendar-list-slider-item-checkbox'
		);
	}

	switchSection(section)
	{
		const checkboxNodes = this.findCheckBoxNodes(section.id)

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


	switchOnSection(section)
	{
		const checkboxNodes = this.findCheckBoxNodes(section.id);

		for (let i = 0; i < checkboxNodes.length; i++)
		{
			if (!section.isShown())
			{
				Dom.addClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
			}
		}

		if (!section.isShown())
		{
			section.show();
		}
	}

	switchOffSection(section)
	{
		const checkboxNodes = this.findCheckBoxNodes(section.id);

		for (let i = 0; i < checkboxNodes.length; i++)
		{
			if (section.isShown())
			{
				Dom.removeClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
			}
		}

		if (section.isShown())
		{
			section.hide();
		}
	}


	showSectionMenu (section, menuItemNode)
	{
		const menuItems = [];
		const itemNode = menuItemNode.closest('[data-bx-calendar-section]');
		if (Type.isElementNode(itemNode))
		{
			Dom.addClass(itemNode, 'active');
		}

		if(section.canDo('view_time'))
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_LEAVE_ONE'),
				onclick: () => {
					this.sectionActionMenu.close();
					this.showOnlyOneSection(section, this.sectionManager.sections);
				}
			});
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

		if (section.canBeConnectedToOutlook() && section.data['EXTERNAL_TYPE'] === 'local')
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

		if (
			!section.isPseudo()
			&& section.data.EXPORT
			&& section.data.EXPORT.LINK
			&& section.data['EXTERNAL_TYPE'] === 'local'
		)
		{
			menuItems.push({
				text: Loc.getMessage('EC_ACTION_EXPORT'),
				onclick: () => {
					this.sectionActionMenu.close();

					const options = {
						sectionLink: section.data.EXPORT.LINK,
						calendarPath: this.calendarContext.util.config.path,
					};
					if (IcalSyncPopup.checkPathes(options))
					{
						IcalSyncPopup.createInstance(options).show();
					}
					else
					{
						IcalSyncPopup.showPopupWithPathesError();
					}
				}
			});
		}

		let provider = undefined;
		let connection = undefined;

		if (section.data.CAL_DAV_CON
			&& section.belongsToView()
			&& this.calendarContext.syncInterface
		)
		{
			[provider, connection] = this.calendarContext.syncInterface.getProviderById(section.data.CAL_DAV_CON);
		}

		if (
			section.canDo('edit_section')
			&& section.belongsToView()
			&& !section.isPseudo()
			&& ((!section.isGoogle() && !connection)
				|| section.data['EXTERNAL_TYPE'] === 'local'
				|| !connection
			)
		)
		{
			menuItems.push({
				text : Loc.getMessage('EC_SEC_DELETE'),
				onclick: () => {
					this.sectionActionMenu.close();
					this.showSectionConfirm('delete', section);
				}
			});
		}

		if (section.canDo('edit_section') && connection)
		{
			if (section.isGoogle() || section.isIcloud() || section.isOffice365() || section.isCalDav())
			{
				menuItems.push({
					text: Loc.getMessage('EC_ACTION_EXTERNAL_ADJUST'),
					onclick: () => {
						this.sectionActionMenu.close();
						if (provider)
						{
							provider.openActiveConnectionSlider(connection);
						}
					}
				});
			}

			if (section.isGoogle() || section.isIcloud() || section.isOffice365())
			{
				menuItems.push({
					text: Loc.getMessage('EC_ACTION_HIDE'),
					onclick: () => {
						this.sectionActionMenu.close();
						this.showSectionConfirm('hideSync', section);
					}
				});
			}

			else if (section.isCalDav())
			{
				menuItems.push({
					text: Loc.getMessage('EC_ACTION_HIDE'),
					onclick: () => {
						this.sectionActionMenu.close();
						this.showSectionConfirm('hideExternal', section);
					}
				});
			}
		}

		if (section.isPseudo() && section.taskSectionBelongToUser())
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
			showAccessControl = params.section.hasPermission('access');
		}
		else
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');
		}

		this.editSectionForm.show({
			showAccess: showAccessControl,
			allowChangeName: params.section ? !params.section.isPrimaryForConnection() : true,
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
				wrap: this.DOM.trackingTypesFormWrap,
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
		if (event && event instanceof this.BX.Event.BaseEvent)
		{
			const data = event.getData();
			const sectionId = parseInt(data.sectionId, 10);

			this.sliderSections.forEach((section, index) => {
				if (parseInt(section.id) === sectionId)
				{
					this.sectionManager.deleteSectionHandler(sectionId);
					this.deletedSectionsIds.push(sectionId);
					const deleteSectionNodes = this.DOM.sectonListOuterWrap.querySelectorAll(
						`.calendar-list-slider-item[data-bx-calendar-section='${sectionId}']`
					);

					deleteSectionNodes.forEach(node => {
						Dom.addClass(node, 'calendar-list-slider-item-disappearing');
					});

					if (!section.externalTypeIsLocal())
					{
						const listWrap = this.getSectionListWrapForSection(section);
						this.sliderSections = BX.util.deleteFromArray(this.sliderSections, index);
						setTimeout(() => {
							deleteSectionNodes.forEach(node => {
								Dom.remove(node);
							});

							if(!listWrap.querySelector('li.calendar-list-slider-item'))
							{
								Dom.remove(listWrap.closest('.calendar-list-slider-card-widget'));
							}
						}, 300);
					}
				}
			}, this);

			this.closeForms()
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

	showOnlyOneSection(section, sections)
	{
		for (let curSection of sections)
		{
			if(curSection.id === section.id)
			{
				this.switchOnSection(curSection);
			}
			else
			{
				this.switchOffSection(curSection);
			}
		}

		this.calendarContext.reload();
	}


	keyHandler(e)
	{
		if (
			e.keyCode ===  Util.getKeyCode('enter')
			&& this.DOM.confirmSectionPopup
			&& this.currentConfirmMode
			&& this.currentSection
		)
		{
			if (this.currentConfirmMode === 'delete')
			{
				this.removeSection(this.currentSection);
			}
			else if (this.currentConfirmMode === 'hideSync')
			{
				this.hideSyncSection(this.currentSection);
			}
			else if (this.currentConfirmMode === 'hideExternal')
			{
				this.hideExternalSection(this.currentSection);
			}
		}
	}

	showSectionConfirm(mode, section)
	{
		this.currentSection = section;
		this.currentConfirmMode = mode;

		const confirmCallback = this.getConfirmCallback();
		const okCaption = this.getOkCaption();

		this.DOM.confirmSectionPopup = new MessageBox({
			message: this.getSectionConfirmContent(),
			minHeight: 120,
			minWidth: 280,
			maxWidth: 300,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			onOk: confirmCallback,
			onCancel: () => {
				this.DOM.confirmSectionPopup.close();
			},
			okCaption: okCaption,
			popupOptions: {
				events: {
					onPopupClose: () => {
						delete this.DOM.confirmSectionPopup;
						delete this.currentSection;
						delete this.currentConfirmMode;
					},
				},
				closeByEsc: true,
				padding: 0,
				contentPadding: 0,
				animation: 'fading-slide',
			}
		});

		this.DOM.confirmSectionPopup.show();
	}

	getConfirmCallback()
	{
		if (this.currentConfirmMode === 'delete')
		{
			return () => {
				this.removeSection(this.currentSection);
			};
		}
		else if (this.currentConfirmMode === 'hideSync')
		{
			return () => {
				this.hideSyncSection(this.currentSection);
			};
		}
		else if (this.currentConfirmMode === 'hideExternal')
		{
			return () => {
				this.hideExternalSection(this.currentSection);
			};
		}
	}

	getOkCaption()
	{
		if (this.currentConfirmMode === 'delete')
		{
			return Loc.getMessage('EC_SEC_DELETE');
		}
		else if (this.currentConfirmMode === 'hideSync' || this.currentConfirmMode === 'hideExternal')
		{
			return Loc.getMessage('EC_CAL_SYNC_DISCONNECT')
		}
	}

	getSectionConfirmContent()
	{
		let phrase = '';

		if (this.currentConfirmMode === 'delete')
		{
			phrase = Loc.getMessage('EC_SEC_DELETE_CONFIRM');
		}
		else if (this.currentConfirmMode === 'hideSync' || this.currentConfirmMode === 'hideExternal')
		{
			phrase = Loc.getMessage('EC_CAL_GOOGLE_HIDE_CONFIRM');
		}

		return Tag.render`
			<div class="calendar-list-slider-messagebox-text">${phrase}</div>
		`;
	}

	removeSection(section)
	{
		section.remove();
		this.DOM.confirmSectionPopup.close();
	}

	hideSyncSection(section)
	{
		section.hideSyncSection();
		this.DOM.confirmSectionPopup.close();
	}

	hideExternalSection(section)
	{
		section.hideExternalCalendarSection();
		this.DOM.confirmSectionPopup.close();
	}
}