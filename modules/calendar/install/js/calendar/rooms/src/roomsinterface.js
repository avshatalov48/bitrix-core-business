import { Type, Dom, Loc, Tag, Event } from 'main.core';
import { SectionInterface } from 'calendar.sectioninterface';
import { Util } from 'calendar.util';
import { EditFormRoom } from './editformroom';

export class RoomsInterface extends SectionInterface
{
	SLIDER_WIDTH = 400;
	SLIDER_DURATION = 80;
	sliderId = "calendar:rooms-slider";
	constructor({ calendarContext, readonly, roomsManager, isConfigureList = false })
	{
		super({ calendarContext, readonly, roomsManager });
		this.setEventNamespace('BX.Calendar.RoomsInterface');
		this.roomsManager = roomsManager;
		this.isConfigureList = isConfigureList;
		this.calendarContext = calendarContext;
		this.readonly = readonly;
		this.BX = Util.getBX();
		this.sliderOnClose = this.hide.bind(this);
		this.deleteRoomHandlerBinded = this.deleteRoomHandler.bind(this);
		this.refreshRoomListBinded = this.refreshRoomList.bind(this);
		if(this.calendarContext !== null)
		{
			if (this.calendarContext.util.config.accessNames)
			{
				Util.setAccessNames(this.calendarContext?.util?.config?.accessNames);
			}
		}
	}

	addEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:create',
			this.refreshRoomListBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:update',
			this.refreshRoomListBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:delete',
			this.deleteRoomHandlerBinded
		);

		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-create',
			this.refreshRoomListBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-update',
			this.refreshRoomListBinded
		);
		Util.getBX().Event.EventEmitter.subscribe(
			'BX.Calendar.Rooms:pull-delete',
			this.deleteRoomHandlerBinded
		);
	}

	destroyEventEmitterSubscriptions()
	{
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:create',
			this.refreshRoomListBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:update',
			this.refreshRoomListBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:delete',
			this.deleteRoomHandlerBinded
		);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-create',
			this.refreshRoomListBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-update',
			this.refreshRoomListBinded
		);
		Util.getBX().Event.EventEmitter.unsubscribe(
			'BX.Calendar.Rooms:pull-delete',
			this.deleteRoomHandlerBinded
		);
	}

	createContent()
	{
		this.DOM.outerWrap = Tag.render`
			<div class="calendar-list-slider-wrap"></div>
		`;
		this.DOM.titleWrap = this.DOM.outerWrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-title-container">
					<div class="calendar-list-slider-title">${Loc.getMessage('EC_SECTION_ROOMS')}</div>
				</div>
			`
		);
		if (!this.readonly)
		{
			// #1. Controls
			this.createAddButton();

			// #2. Forms
			this.DOM.roomFormWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">${Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM')}</span>
						</div>
					</div>
				`
			);
		}
		this.createRoomList();
		return this.DOM.outerWrap;
	}

	createAddButton()
	{
		//add button in slider list of meeting rooms
		this.actionType = 'createRoom';
		const addButtonOuter = this.DOM.titleWrap.appendChild(
			Tag.render`
				<span class="ui-btn-light-border" style="margin-right: 0"></span>
			`
		);
		this.DOM.addButton = addButtonOuter.appendChild(
			Tag.render`
				<span class="ui-btn" onclick="${this.showEditRoomForm.bind(this)}">${Loc.getMessage('EC_ADD')}</span>
			`
		);
	}

	createRoomList()
	{
		let title;
		this.sliderRoom = this.roomsManager.getRooms();
		// title = Loc.getMessage('EC_SEC_SLIDER_TYPE_ROOM_LIST');
		if (this.DOM.roomListWrap)
		{
			Dom.clean(this.DOM.roomListWrap);
			Dom.adjust(this.DOM.roomListWrap, {
				props: { className: 'calendar-list-slider-card-widget' }
			});
		}
		else
		{
			this.DOM.roomListWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-card-widget">
					</div>
				`
			);
		}

		this.createRoomBlock({
			wrap: this.DOM.roomListWrap,
			roomList: this.sliderRoom.filter(function(room) {
				return room.belongsToView() || room.isPseudo();
			})
		});
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
			closeCallback: () => {
				this.allowSliderClose();
			}
		});

		let showAccessControl = true;
		if (params.room && params.room.id)
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_ROOM');
			showAccessControl = params.room.canDo('access');
		}
		else
		{
			formTitleNode.innerHTML = Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
		}

		this.editSectionForm.show({
			showAccess: showAccessControl,
			room: params.room || {
				color: Util.getRandomColor(),
				access: this.roomsManager.getDefaultSectionAccess()
			},
			actionType: params.actionType
		});

		this.denySliderClose();
	}

	showRoomMenu(room, menuItemNode)
	{
		const menuItems = [];
		const itemNode = menuItemNode.closest('[data-bx-calendar-section]')
			|| menuItemNode.closest('[ data-bx-calendar-section-without-action]');
		if (Type.isElementNode(itemNode))
		{
			Dom.addClass(itemNode, 'active');
		}

		if(room.canDo('view_time') && !this.isConfigureList)
		{
			menuItems.push({
				text: Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
				onclick: () => {
					this.roomActionMenu.close();
					this.showOnlyOneSection(room, this.roomsManager.rooms);
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
					this.deleteRoom(room);
				}
			});
		}
		if (menuItems && menuItems.length > 0)
		{
			this.roomActionMenu = top.BX.PopupMenu.create(
				'section-menu-' + Util.getRandomInt(),
				menuItemNode,
				menuItems,
				{
					closeByEsc: true,
					autoHide: true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 9,
					angle: true,
					cacheable: false
				}
			);

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

	refreshRoomList()
	{
		this.createRoomList();
	}

	createRoomBlock({ wrap, roomList })
	{
		if (Type.isArray(roomList))
		{
			const listWrap = wrap.appendChild(
				Tag.render`
					<div class="calendar-list-slider-widget-content"></div>
				`
				)
				.appendChild(
					Tag.render`
					<div class="calendar-list-slider-widget-content-block"></div>
					`
				)
				.appendChild(
					Tag.render`
					<ul class="calendar-list-slider-container"></ul>
					`
				);
			Event.bind(listWrap, 'click', this.roomClickHandler.bind(this));

			roomList.forEach((room) => {
				if (!room.DOM)
				{
					room.DOM = {};
				}
				const roomId = room.id;

				let li;
				let checkbox;
				if(this.isConfigureList)
				{
					li = listWrap.appendChild(
						Tag.render`
						<li class="calendar-list-slider-item"  data-bx-calendar-section-without-action="${roomId}"></li>
					`
					);
					checkbox = li.appendChild(Dom.create('DIV', {
						props: {
							className: 'calendar-field-select-icon'
						},
						style: { backgroundColor: room.color }
					}));
				}
				else
				{
					li = listWrap.appendChild(
						Tag.render`
						<li class="calendar-list-slider-item" data-bx-calendar-section="${roomId}"></li>
					`
					);
					checkbox = li.appendChild(Dom.create('DIV', {
						props: {
							className: 'calendar-list-slider-item-checkbox'
								+ (room.isShown() ? ' calendar-list-slider-item-checkbox-checked' : '')
						},
						style: { backgroundColor: room.color }
					}));
				}

				const title = li.appendChild(
					Tag.render`
					<div class="calendar-list-slider-item-name" title="${BX.util.htmlspecialchars(room.name)}">${BX.util.htmlspecialchars(room.name)}</div>
					`
				);

				room.DOM.item = li;
				room.DOM.checkbox = checkbox;
				room.DOM.title = title;

				room.DOM.actionCont = li.appendChild(
				Tag.render`
					<div class="calendar-list-slider-item-actions-container" data-bx-calendar-section-menu="${roomId}">
						<span class="calendar-list-slider-item-context-menu"></span>
					</div>
				`
				);
			});
		}
	}

	roomClickHandler(e)
	{
		const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

		if (target && target.getAttribute)
		{
			if (target.getAttribute('data-bx-calendar-section-menu') !== null)
			{
				let roomId = target.getAttribute('data-bx-calendar-section-menu');
				this.showRoomMenu(this.roomsManager.getRoom(roomId), target);
			}
			else if (target.getAttribute('data-bx-calendar-section') !== null)
			{
				let roomId = target.getAttribute('data-bx-calendar-section');
				this.switchSection(this.roomsManager.getRoom(roomId));
			}
		}
	}

	findCheckBoxNodes(id)
	{
		return this.DOM.roomListWrap.querySelectorAll(
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
			delete this.DOM.roomListWrap;

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

			this.sliderRoom.forEach((room, index) => {
				if (parseInt(room.id) === deleteID && room.DOM && room.DOM.item)
				{
					Dom.addClass(room.DOM.item, 'calendar-list-slider-item-disappearing');
					setTimeout(() => {
						Dom.clean(room.DOM.item, true);
						this.sliderRoom.splice(index, 1);
					}, 300);
				}
			}, this);

			this.closeForms();
		}
	}

	deleteRoom(room)
	{
		this.roomsManager.deleteRoom(
			room.id,
			room.location_id
		);
	}
}
