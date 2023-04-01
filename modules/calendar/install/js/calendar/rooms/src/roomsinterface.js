import { Type, Dom, Loc, Tag, Event, Text } from 'main.core';
import { SectionInterface } from 'calendar.sectioninterface';
import { Util } from 'calendar.util';
import { EditFormRoom } from './editformroom';
import { EditFormCategory } from './editformcategory';
import { MessageBox } from 'ui.dialogs.messagebox';

export class RoomsInterface extends SectionInterface
{
	SLIDER_WIDTH = 400;
	SLIDER_DURATION = 80;
	sliderId = "calendar:rooms-slider";
	CATEGORY_ROOMS_SHOWN_ALL = 0;
	CATEGORY_ROOMS_SHOWN_SOME = 1;
	CATEGORY_ROOMS_SHOWN_NONE = 2;

	constructor({ calendarContext, readonly, roomsManager, categoryManager, isConfigureList = false })
	{
		super({ calendarContext, readonly, roomsManager });
		this.setEventNamespace('BX.Calendar.RoomsInterface');
		this.roomsManager = roomsManager;
		this.categoryManager = categoryManager;
		this.isConfigureList = isConfigureList;
		this.calendarContext = calendarContext;
		this.readonly = readonly;
		this.BX = Util.getBX();
		this.sliderOnClose = this.hide.bind(this);
		this.deleteRoomHandlerBinded = this.deleteRoomHandler.bind(this);
		this.refreshRoomsBinded = this.refreshRooms.bind(this);
		this.refreshCategoriesBinded = this.refreshCategories.bind(this);
		if (this.calendarContext !== null)
		{
			if (this.calendarContext.util.config.accessNames)
			{
				Util.setAccessNames(this.calendarContext?.util?.config?.accessNames);
			}
		}
		this.setRoomsFromManager();
		this.setCategoriesFromManager();
	}

	addEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:create',
			this.refreshRoomsBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:update',
			this.refreshRoomsBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:delete',
			this.deleteRoomHandlerBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-create',
			this.refreshRoomsBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-update',
			this.refreshRoomsBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-delete',
			this.deleteRoomHandlerBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:create',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:update',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:delete',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:pull-create',
			this.refreshCategoriesBinded,
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:pull-update',
			this.refreshCategoriesBinded,
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms.Categories:pull-delete',
			this.refreshCategoriesBinded,
		);
	}

	destroyEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:create',
			this.refreshRoomsBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:update',
			this.refreshRoomsBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:delete',
			this.deleteRoomHandlerBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-create',
			this.refreshRoomsBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-update',
			this.refreshRoomsBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-delete',
			this.deleteRoomHandlerBinded
		);


		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:create',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:update',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:delete',
			this.refreshCategoriesBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:pull-create',
			this.refreshCategoriesBinded,
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:pull-update',
			this.refreshCategoriesBinded,
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms.Categories:pull-delete',
			this.refreshCategoriesBinded,
		);
	}

	createContent()
	{
		this.DOM.outerWrap = this.renderOuterWrap();
		this.DOM.titleWrap = this.DOM.outerWrap.appendChild(this.renderTitleWrap());

		if (!this.readonly)
		{
			// #1. Controls
			this.DOM.addButton = this.DOM.titleWrap.appendChild(this.renderAddButton());

			// #2. Forms
			this.DOM.roomFormWrap = this.DOM.outerWrap.appendChild(this.renderRoomFormWrap());
		}
		this.createRoomBlocks();

		return this.DOM.outerWrap;
	}

	renderOuterWrap()
	{
		return Tag.render`
				<div class="calendar-list-slider-wrap"></div>
			`
		;
	}

	renderTitleWrap()
	{
		return Tag.render`
				<div class="calendar-list-slider-title-container">
					<div class="calendar-list-slider-title">${Loc.getMessage('EC_SECTION_ROOMS')}</div>
				</div>
			`
		;
	}

	renderAddButton()
	{
		return Tag.render`
				<span class="ui-btn-split ui-btn-light-border" style="margin-right: 0">
					<span class="ui-btn-main" onclick="${this.showEditRoomForm.bind(this)}">
						${Loc.getMessage('EC_ADD')}
					</span>
					<span id = "add-menu-button" class="ui-btn-menu" onclick="${this.showAddMenu.bind(this)}"></span>
				</span>
		`;
	}

	renderRoomFormWrap()
	{
		return Tag.render`
				<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
					<div class="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">${Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM')}</span>
					</div>
				</div>
		`;
	}

	showAddMenu(): void
	{
		const menuButtons = this.createAddMenuButtons();

		if (menuButtons && menuButtons.length > 0)
		{
			this.addRoomMenu = this.createAddMenu(menuButtons);
			this.addRoomMenu.popupWindow.show();
			this.addRoomMenu.popupWindow.subscribe('onClose', () => {
				this.allowSliderClose();
			});

			this.denySliderClose();
		}
	}

	createAddMenuButtons()
	{
		const menuButtons = [];

		menuButtons.push({
			text: Loc.getMessage('EC_ADD_LOCATION'),
			onclick: () => {
				this.addRoomMenu.close();
				this.showEditRoomForm();
			},
		});
		menuButtons.push({
			text: Loc.getMessage('EC_ADD_CATEGORY'),
			onclick: () => {
				this.addRoomMenu.close();
				this.showEditCategoryForm();
			},
		});

		return menuButtons;
	}

	createAddMenu(menuButtons)
	{
		const params = {
			offsetLeft: 20,
			closeByEsc: true,
			angle: {
				position: 'top'
			},
			autoHide: true,
			offsetTop: 0,
			cacheable: false
		};

		return new BX.PopupMenuWindow(
			'add-menu-form-' + Util.getRandomInt(),
			BX("add-menu-button"),
			menuButtons,
			params
		);
	}

	createRoomBlocks()
	{
		this.setBlocksWrap();

		if (Type.isArray(this.rooms) || Type.isObject(this.categories))
		{
			this.categories['categories'].forEach((category) => {
				if(category.rooms.length !== 0)
				{
					this.createCategoryBlock(category, this.createBlockWrap(this.DOM.blocksWrap));
				}
			});

			if(this.categories['default'].length > 0)
			{
				let defaultBlockWrap = this.createBlockWrap(this.DOM.blocksWrap);
				this.categories['default'].forEach(room => this.createRoomBlock(room, defaultBlockWrap));
			}

			this.categories['categories'].forEach((category) => {
				if(category.rooms.length === 0 && this.categoryManager.canDo('edit'))
				{
					this.createCategoryBlock(category, this.createBlockWrap(this.DOM.blocksWrap));
				}
			});
		}

		if(this.isFrozen())
		{
			this.unfreezeButtons();
		}
	}

	setRoomsFromManager()
	{
		this.rooms = this.roomsManager.getRooms()
			.filter(function(room) {
				return room.belongsToView() || room.isPseudo();
			})
		;
	}

	setCategoriesFromManager()
	{
		this.categories = this.categoryManager.getCategoriesWithRooms(this.rooms);
	}

	setBlocksWrap()
	{
		if (this.DOM.blocksWrap)
		{
			Dom.clean(this.DOM.blocksWrap);
			Dom.adjust(this.DOM.blocksWrap, {
				props: { className: '' }
			});
		}
		else
		{
			this.DOM.blocksWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div></div>
				`
			);
		}
	}

	showEditRoomForm(params = {})
	{
		if (typeof params.actionType === 'undefined')
		{
			params.actionType = 'createRoom';
		}
		this.closeForms();
		const formTitleNode = this.DOM.roomFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');

		this.editSectionForm = new EditFormRoom({
			wrap: this.DOM.roomFormWrap,
			sectionAccessTasks: this.roomsManager.getSectionAccessTasks(),
			roomsManager: this.roomsManager,
			categoryManager: this.categoryManager,
			freezeButtonsCallback: this.freezeButtons.bind(this),
			closeCallback: () => {
				this.allowSliderClose();
			}
		});

		let showAccess = true;
		if (params.room && params.room.id)
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_ROOM');
			showAccess = params.room.canDo('access');
		}
		else
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
		}

		this.editSectionForm.show({
			showAccess,
			room: params.room || {
				color: Util.getRandomColor(),
				access: this.roomsManager.getDefaultSectionAccess()
			},
			actionType: params.actionType
		});

		this.denySliderClose();
	}

	showEditCategoryForm(params = {})
	{
		if (typeof params.actionType === 'undefined')
		{
			params.actionType = 'createCategory';
		}

		this.closeForms();
		const formTitleNode = this.DOM.roomFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');

		this.editSectionForm = new EditFormCategory({
			wrap: this.DOM.roomFormWrap,
			sectionAccessTasks: this.roomsManager.getSectionAccessTasks(),
			categoryManager: this.categoryManager,
			freezeButtonsCallback: this.freezeButtons.bind(this),
			closeCallback: () => {
				this.allowSliderClose();
			}
		});

		if (params.category && params.category.id)
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_EDIT_ROOM_CATEGORY');
		}
		else
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_NEW_CATEGORY');
		}

		this.editSectionForm.show({
			category: params.category || {},
			actionType: params.actionType
		});

		this.denySliderClose();
	}

	showRoomMenu(room, menuItemNode)
	{
		const itemNode = menuItemNode.closest('[data-bx-calendar-section]')
			|| menuItemNode.closest('[ data-bx-calendar-section-without-action]')
		;

		if (Type.isElementNode(itemNode))
		{
			Dom.addClass(itemNode, 'active');
		}

		const menuItems = this.createRoomMenuButtons(room);

		if (menuItems && menuItems.length > 0)
		{
			this.roomActionMenu = this.createRoomMenu(menuItems, menuItemNode);

			this.roomActionMenu.show();
			this.roomActionMenu.popupWindow.subscribe('onClose', () => {
				if (Type.isElementNode(itemNode))
				{
					Dom.removeClass(itemNode, 'active');
				}
				this.allowSliderClose();
			});

			this.denySliderClose();
		}
		else
		{
			Dom.removeClass(itemNode, 'active');
		}
	}

	createRoomMenuButtons(room)
	{
		const menuItems = [];

		if (room.canDo('view_time') && !this.isConfigureList)
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
				onclick: () => {
					this.roomActionMenu.close();
					this.showOnlyOneSection(room, this.roomsManager.rooms);
					this.updateAllCategoriesCheckboxState();
				}
			});
		}

		if (!this.readonly && room.canDo('edit_section'))
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_EDIT'),
				onclick: () => {
					this.roomActionMenu.close();
					this.showEditRoomForm({ room: room, actionType: 'updateRoom' });
				}
			});
		}

		if (room.canDo('edit_section') && room.belongsToView())
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_DELETE'),
				onclick: () => {
					this.roomActionMenu.close();
					this.showRoomDeleteConfirm(room);
					// this.deleteRoom(room);
				}
			});
		}

		return menuItems;
	}

	createRoomMenu(menuItems, menuItemNode)
	{
		const params = {
			closeByEsc: true,
			autoHide: true,
			zIndex: this.zIndex,
			offsetTop: 0,
			offsetLeft: 9,
			angle: true,
			cacheable: false,
		};

		return top.BX.PopupMenu.create(
			'section-menu-' + Util.getRandomInt(),
			menuItemNode,
			menuItems,
			params
		);
	}

	refreshRooms()
	{
		this.setRoomsFromManager();
		this.setCategoriesFromManager();
		this.createRoomBlocks();
	}

	refreshCategories()
	{
		this.roomsManager.reloadRoomsFromDatabase().then(this.refreshRoomsBinded);
	}

	createBlockWrap(wrap)
	{
		const listWrap =
			wrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-category-widget">
						<div class="calendar-list-slider-widget-content">
							<div class="calendar-list-slider-widget-content-block">
								<ul class="calendar-list-slider-container"></ul>
							</div>
						</div>
					</div>
				`
			)
			.querySelector('.calendar-list-slider-container')
		;
		Event.bind(listWrap, 'click', this.roomClickHandler.bind(this));

		return listWrap;
	}

	createCategoryBlock(category, listWrap)
	{
		if (!category.DOM)
		{
			category.DOM = {};
		}

		category.DOM.item = listWrap.appendChild(this.renderCategoryBlockWrap(category));

		const categoryRooms = this.categoryManager.getCategoryRooms(category, this.rooms);
		if(!this.isConfigureList && categoryRooms.length)
		{
			category.setCheckboxStatus(this.determineCategoryCheckboxStatus(category, categoryRooms));
			category.DOM.checkbox =
				category.DOM.item.appendChild(this.renderCategoryBlockCheckbox(category, categoryRooms))
			;
		}

		category.DOM.title = category.DOM.item.appendChild(this.renderCategoryBlockTitle(category));

		if(this.categoryManager.canDo('edit') || category.rooms.length > 0)
		{
			category.DOM.actionCont = category.DOM.item.appendChild(this.renderCategoryBlockActionsContainer(category));
		}

		this.createCategoryBlockContent(category, listWrap);
		return category;
	}

	renderCategoryBlockWrap(category)
	{
		if (this.isConfigureList)
		{
			return Tag.render`
					<li class="calendar-list-slider-item-category"
						data-bx-calendar-category-without-action="${category.id}"
					>
					</li>
				`
			;
		}

		return Tag.render`
					<li class="calendar-list-slider-item-category" data-bx-calendar-category="${category.id}"></li>
		`;
	}

	renderCategoryBlockCheckbox(category)
	{
		let checkboxStyle = '';
		if(category.checkboxStatus === this.CATEGORY_ROOMS_SHOWN_ALL)
		{
			checkboxStyle = 'calendar-list-slider-item-checkbox-checked';
		}
		else if(category.checkboxStatus === this.CATEGORY_ROOMS_SHOWN_SOME)
		{
			checkboxStyle = 'calendar-list-slider-item-checkbox-indeterminate';
		}

		return Tag.render`
					<div class="calendar-title-checkbox calendar-list-slider-item-checkbox
						${checkboxStyle}" style="background-color: #a5abb2"
					>
					</div>
		`;
	}

	renderCategoryBlockActionsContainer(category)
	{
		return Tag.render`
					<div class="calendar-list-slider-item-actions-container
					calendar-list-slider-item-context-menu-category-wrap" 
						data-bx-calendar-category-menu="${category.id}"
					>
						<span class="calendar-list-slider-item-context-menu
							calendar-list-slider-item-context-menu-category"
						>
						</span>
					</div>
		`;
	}

	renderCategoryBlockTitle(category)
	{
		return Tag.render`
				<div class="calendar-list-slider-card-widget-title-text calendar-list-slider-item-category-text" 
					title="${Text.encode(category.name)}"
				>
					${Text.encode(category.name)}
				</div>
		`;
	}

	createCategoryBlockContent(category, wrap)
	{
		if(category.rooms.length)
		{
			category.rooms.forEach((room) => this.createRoomBlock(room, wrap));
		}
		else
		{
			wrap.appendChild(
				Tag.render`
					<li class="calendar-list-slider-card-widget-title-text">${Loc.getMessage('EC_CATEGORY_EMPTY')}</li>
				`
			);
		}
	}

	createRoomBlock(room, listWrap)
	{
		if (!room.DOM)
		{
			room.DOM = {};
		}

		room.DOM.item = listWrap.appendChild(this.renderRoomBlockWrap(room))
		room.DOM.checkbox = room.DOM.item.appendChild(this.renderRoomBlockCheckbox(room));
		room.DOM.title = room.DOM.item.appendChild(this.renderRoomBlockTitle(room));
		room.DOM.actionCont = room.DOM.item.appendChild(this.renderRoomBlockActionsContainer(room));

		return room;
	}

	renderRoomBlockWrap(room)
	{
		if (this.isConfigureList)
		{
			return Tag.render`
					<li class="calendar-list-slider-item"  data-bx-calendar-section-without-action="${room.id}"></li>
			`;
		}

		return Tag.render`
					<li class="calendar-list-slider-item" data-bx-calendar-section="${room.id}"></li>
		`;
	}

	renderRoomBlockCheckbox(room)
	{
		if (this.isConfigureList)
		{
			return Tag.render`
					<div class="calendar-field-select-icon" style="background-color: ${room.color}"></div>
			`;
		}

		return Tag.render`
				<div class="calendar-list-slider-item-checkbox 
					${room.isShown() ? 'calendar-list-slider-item-checkbox-checked' : ''}" 
					style="background-color: ${room.color}"
				>
				</div>
		`;
	}

	renderRoomBlockTitle(room)
	{
		return Tag.render`
				<div class="calendar-list-slider-item-name" title="${Text.encode(room.name)}">
					${Text.encode(room.name)}
				</div>
		`;
	}

	renderRoomBlockActionsContainer(room)
	{
		return Tag.render`
				<div class="calendar-list-slider-item-actions-container" data-bx-calendar-section-menu="${room.id}">
					<span class="calendar-list-slider-item-context-menu"></span>
				</div>
		`;
	}

	roomClickHandler(e)
	{
		const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

		if (target && target.getAttribute)
		{
			if (target.getAttribute('data-bx-calendar-category') !== null)
			{
				const category = this.categoryManager.getCategory(
					parseInt(target.getAttribute('data-bx-calendar-category'), 10)
				);
				if(category && category.rooms.length > 0)
				{
					this.switchCategory(category, this.rooms);
				}
			}
			else if (target.getAttribute('data-bx-calendar-category-menu') !== null)
			{
				let categoryId = target.getAttribute('data-bx-calendar-category-menu');
				this.showCategoryMenu(this.categoryManager.getCategory(categoryId), target);
			}
			else if (target.getAttribute('data-bx-calendar-section-menu') !== null)
			{
				let roomId = target.getAttribute('data-bx-calendar-section-menu');
				this.showRoomMenu(this.roomsManager.getRoom(roomId), target);
			}
			else if (target.getAttribute('data-bx-calendar-section') !== null)
			{
				let roomId = target.getAttribute('data-bx-calendar-section');
				const room = this.roomsManager.getRoom(roomId);
				this.switchSection(room);
				this.updateCategoryCheckboxState(this.categoryManager.getCategory(room.categoryId));
			}
		}
	}

	setRoomsForCategory(categoryId)
	{
		this.categoryManager.unsetCategoryRooms(categoryId);

		const rooms = this.roomsManager.getRooms();
		const categoryManager = this.categoryManager;

		rooms.forEach(function(room){
			if(room.categoryId === categoryId)
			{
				categoryManager.getCategory(categoryId).addRoom(room)
			}
		},this);
	}

	showOnlyOneCategory(category, sections)
	{
		for (let curSection of sections)
		{
			if (curSection.categoryId === category.id)
			{
				this.switchOnSection(curSection);
			}
			else
			{
				this.switchOffSection(curSection);
			}
		}

		this.updateAllCategoriesCheckboxState();

		this.calendarContext.reload();
	}

	showCategoryMenu(category, menuItemNode)
	{
		this.setRoomsForCategory(category.id);

		const menuItems = this.createCategoryMenuButtons(category);

		if (menuItems && menuItems.length > 0)
		{
			this.categoryActionMenu = this.createCategoryMenu(menuItems, menuItemNode);

			this.categoryActionMenu.show();
			this.categoryActionMenu.popupWindow.subscribe('onClose', () => {
				this.allowSliderClose();
			});

			this.denySliderClose();
		}
	}

	createCategoryMenuButtons(category)
	{
		const menuItems = [];

		if (this.categoryManager.canDo('view') && !this.isConfigureList && category.rooms.length > 0)
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
				onclick: () => {
					this.categoryActionMenu.close();
					this.showOnlyOneCategory(category, this.roomsManager.rooms);
				}
			});
		}

		if (!this.readonly && this.categoryManager.canDo('edit'))
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_EDIT'),
				onclick: () => {
					this.categoryActionMenu.close();
					this.showEditCategoryForm({ category: category, actionType: 'updateCategory' });
				}
			});
		}
		if (this.categoryManager.canDo('edit'))
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_DELETE'),
				onclick: () => {
					this.categoryActionMenu.close();
					this.freezeButtons();
					this.showCategoryDeleteConfirm(category);
				}
			});
		}

		return menuItems;
	}

	createCategoryMenu(menuItems, menuItemNode)
	{
		const params = {
			closeByEsc: true,
			autoHide: true,
			zIndex: this.zIndex,
			offsetTop: 0,
			offsetLeft: 9,
			angle: true,
			cacheable: false,
		};

		return top.BX.PopupMenu.create(
			'category-menu-' + Util.getRandomInt(),
			menuItemNode,
			menuItems,
			params,
		);
	}

	findCheckBoxNodes(id)
	{
		return this.DOM.blocksWrap.querySelectorAll(
			'.calendar-list-slider-item[data-bx-calendar-section=\''
			+ id
			+ '\'] .calendar-list-slider-item-checkbox'
		);
	}

	destroy(event)
	{
		if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId)
		{
			this.destroyEventEmitterSubscriptions();
			BX.removeCustomEvent('SidePanel.Slider:onCloseComplete', BX.proxy(this.destroy, this));
			BX.SidePanel.Instance.destroy(this.sliderId);
			delete this.DOM.blocksWrap;

			if (this.roomActionMenu)
			{
				this.roomActionMenu.close();
			}
		}
	}

	deleteRoomHandler(event)
	{
		if (event && event instanceof Util.getBX().Event.BaseEvent)
		{
			const data = event.getData();
			const deleteID = parseInt(data.id);

			this.rooms.forEach((room, index) => {
				if (parseInt(room.id) === deleteID && room.DOM && room.DOM.item)
				{
					Dom.addClass(room.DOM.item, 'calendar-list-slider-item-disappearing');
					setTimeout(() => {
						Dom.clean(room.DOM.item, true);
						this.rooms.splice(index, 1);
					}, 300);
				}
			}, this);

			this.closeForms();
		}
		this.refreshRooms();
	}

	deleteRoom(room)
	{
		this.roomsManager.deleteRoom(
			room.id,
			room.location_id
		);

		if (this.DOM.confirmRoomPopup)
		{
			this.DOM.confirmRoomPopup.close();
			delete this.DOM.confirmRoomPopup;
		}
		if (this.currentRoom)
		{
			delete this.currentRoom;
		}
	}

	deleteCategory(category)
	{
		this.categoryManager.deleteCategory(
			category.id
		);

		if (this.DOM.confirmCategoryPopup)
		{
			this.DOM.confirmCategoryPopup.close();
			delete this.DOM.confirmCategoryPopup;
		}
		if (this.currentCategory)
		{
			delete this.currentCategory;
		}
	}

	freezeButtons()
	{
		Dom.addClass(this.DOM.outerWrap, 'calendar-content-locked');
	}

	unfreezeButtons()
	{
		Dom.removeClass(this.DOM.outerWrap, 'calendar-content-locked');
	}

	isFrozen()
	{
		return Dom.hasClass(this.DOM.outerWrap, 'calendar-content-locked');
	}

	updateCategoryCheckboxState(category)
	{
		if(!category)
		{
			return;
		}

		const updatedCategoryCheckboxStatus = this.determineCategoryCheckboxStatus(category, this.roomsManager.rooms);

		if(category.checkboxStatus !== updatedCategoryCheckboxStatus)
		{
			category.setCheckboxStatus(updatedCategoryCheckboxStatus);
			this.setCategoryCheckboxState(this.findCategoryCheckBoxNode(category.id), updatedCategoryCheckboxStatus);
		}
	}

	determineCategoryCheckboxStatus(category, rooms)
	{
		let hasEnabled = false;
		let hasDisabled = false;

		rooms.forEach((room) => {
			if(room.categoryId === category.id)
			{
				if(room.isShown() && !hasEnabled)
				{
					hasEnabled = true;
				}

				if(!room.isShown() && !hasDisabled)
				{
					hasDisabled = true;
				}
			}
		});

		if (hasEnabled && hasDisabled)
		{
			return this.CATEGORY_ROOMS_SHOWN_SOME;
		}

		if (hasEnabled)
		{
			return this.CATEGORY_ROOMS_SHOWN_ALL;
		}

		return this.CATEGORY_ROOMS_SHOWN_NONE;
	}

	switchCategory(category, rooms)
	{
		const checkboxNode = this.findCategoryCheckBoxNode(category.id);

		switch (category.checkboxStatus)
		{
			case this.CATEGORY_ROOMS_SHOWN_SOME:
			case this.CATEGORY_ROOMS_SHOWN_NONE:
				this.switchOnCategoryRooms(category.id, rooms);
				this.setCategoryCheckboxState(checkboxNode, this.CATEGORY_ROOMS_SHOWN_ALL);
				category.setCheckboxStatus(this.CATEGORY_ROOMS_SHOWN_ALL);
				break;
			case this.CATEGORY_ROOMS_SHOWN_ALL:
				this.switchOffCategoryRooms(category.id, rooms);
				this.setCategoryCheckboxState(checkboxNode, this.CATEGORY_ROOMS_SHOWN_NONE);
				category.setCheckboxStatus(this.CATEGORY_ROOMS_SHOWN_NONE);
				break;
			default:
				break;
		}

		this.calendarContext.reload();
	}

	setCategoryCheckboxState(checkboxNode, checkboxStatus)
	{
		Dom.removeClass(checkboxNode, 'calendar-list-slider-item-checkbox-checked');
		Dom.removeClass(checkboxNode, 'calendar-list-slider-item-checkbox-indeterminate');

		switch (checkboxStatus)
		{
			case this.CATEGORY_ROOMS_SHOWN_SOME:
				Dom.addClass(checkboxNode, 'calendar-list-slider-item-checkbox-indeterminate');
				break;
			case this.CATEGORY_ROOMS_SHOWN_ALL:
				Dom.addClass(checkboxNode, 'calendar-list-slider-item-checkbox-checked');
				break;
			default:
				break;
		}
	}

	findCategoryCheckBoxNode(id)
	{
		return this.DOM.outerWrap.querySelector(
			'.calendar-list-slider-item-category[data-bx-calendar-category=\''
			+ id
			+ '\'] .calendar-list-slider-item-checkbox'
		);
	}

	switchOnCategoryRooms(categoryId, rooms)
	{
		rooms.forEach((room) =>{
			if(room.categoryId === categoryId && !room.isShown())
			{
				this.switchOnSection(room);
			}
		});
	}

	switchOffCategoryRooms(categoryId, rooms)
	{
		rooms.forEach((room) =>{
			if(room.categoryId === categoryId && room.isShown())
			{
				this.switchOffSection(room);
			}
		});
	}

	updateAllCategoriesCheckboxState()
	{
		this.categoryManager.getCategories().forEach(category => this.updateCategoryCheckboxState(category));
	}

	showRoomDeleteConfirm(room)
	{
		this.currentRoom = room;

		this.DOM.confirmRoomPopup = new MessageBox({
			message: this.getConfirmRoomInterfaceContent(Loc.getMessage('EC_ROOM_DELETE_CONFIRM')),
			minHeight: 120,
			minWidth: 280,
			maxWidth: 300,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			onOk: () => {
				this.deleteRoom(room);
			},
			onCancel: () => {
				this.DOM.confirmRoomPopup.close();
			},
			okCaption: Loc.getMessage('EC_SEC_DELETE'),
			popupOptions: {
				events: {
					onPopupClose: () => {
						delete this.DOM.confirmRoomPopup;
						delete this.currentRoom;
					},
				},
				closeByEsc: true,
				padding: 0,
				contentPadding: 0,
				animation: 'fading-slide',
			}
		});

		this.DOM.confirmRoomPopup.show();
	}

	showCategoryDeleteConfirm(category)
	{
		this.currentCategory = category;

		this.DOM.confirmCategoryPopup = new MessageBox({
			message: this.getConfirmRoomInterfaceContent(Loc.getMessage('EC_CATEGORY_DELETE_CONFIRM')),
			minHeight: 120,
			minWidth: 280,
			maxWidth: 300,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			onOk: () => {
				this.deleteCategory(category);
			},
			onCancel: () => {
				this.DOM.confirmCategoryPopup.close();
			},
			okCaption: Loc.getMessage('EC_SEC_DELETE'),
			popupOptions: {
				events: {
					onPopupClose: () => {
						this.unfreezeButtons();
						delete this.DOM.confirmCategoryPopup;
						delete this.currentCategory;
					},
				},
				closeByEsc: true,
				padding: 0,
				contentPadding: 0,
				animation: 'fading-slide',
			}
		});

		this.DOM.confirmCategoryPopup.show();
	}

	getConfirmRoomInterfaceContent(text)
	{
		return Tag.render`<div class="calendar-list-slider-messagebox-text">${text}</div>`;
	}

	keyHandler(e)
	{
		if (e.keyCode ===  Util.getKeyCode('enter'))
		{
			if (this.DOM.confirmRoomPopup && this.currentRoom)
			{
				this.deleteRoom(this.currentRoom);
			}
			if (this.DOM.confirmCategoryPopup && this.currentCategory)
			{
				this.deleteCategory(this.currentCategory);
			}
		}
	}
}