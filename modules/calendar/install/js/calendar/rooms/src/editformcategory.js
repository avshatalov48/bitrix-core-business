import { Tag, Dom, Loc, Event} from 'main.core';
import { EditForm } from '../../sectioninterface/src/editform';
import { Util } from 'calendar.util';
import { Dialog, TagSelector } from 'ui.entity-selector';

export class EditFormCategory extends EditForm
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Calendar.Rooms.EditFormCategory');
		this.DOM.outerWrap = options.wrap;
		this.categoryManager = options.categoryManager;
		this.zIndex = options.zIndex || 3100;
		this.closeCallback = options.closeCallback;
		this.BX = Util.getBX();
		this.keyHandlerBinded = this.keyHandler.bind(this);
		this.preparedSelectedRooms = [];
		this.freezeButtonsCallback = options.freezeButtonsCallback;
	}

	show(params = {})
	{
		this.setParams(params);

		if(this.category && this.category.rooms)
		{
			this.preparedSelectedRooms = this.prepareRoomsForDialog(this.category.rooms);
		}

		this.create();

		Event.bind(document, 'keydown', this.keyHandlerBinded);
		Dom.addClass(this.DOM.outerWrap, 'show');

		if (this.category)
		{
			this.setInputValues(this.category);
		}

		this.setFocusOnInput();

		this.isOpenedState = true;
	}

	setParams(params)
	{
		this.actionType = params.actionType;
		this.category = params.category;
	}

	setInputValues()
	{
		if(this.category.name)
		{
			this.DOM.categoryTitleInput.value = this.category.name;
		}
	}

	setFocusOnInput()
	{
		BX.focus(this.DOM.categoryTitleInput);
		if (this.DOM.categoryTitleInput.value !== '')
		{
			this.DOM.categoryTitleInput.select();
		}
	}

	create(params)
	{
		this.wrap = this.getSliderContentWrap();
		this.DOM.formFieldsWrap = this.getFormFieldsWrap(this.wrap);

		this.DOM.categoryTitleInput = this.createTitleInput(this.DOM.formFieldsWrap);
		this.DOM.locationSelector = this.DOM.formFieldsWrap.appendChild(this.renderRoomSelector());
		this.createButtons(this.DOM.formFieldsWrap);

		this.isCreated = true;
	}

	getSliderContentWrap()
	{
		let sliderContentWrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
		if (sliderContentWrap)
		{
			Dom.clean(sliderContentWrap);
		}
		else
		{
			sliderContentWrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-form-content"></div>
				`
			);
		}

		return sliderContentWrap;
	}

	getFormFieldsWrap(wrap)
	{
		return wrap.appendChild(
				Tag.render`
				<div class="calendar-list-slider-widget-content"></div>
			`
			)
			.appendChild(
				Tag.render`
				<div class="calendar-list-slider-widget-content-block"></div>
			`
		);
	}

	createTitleInput(wrap)
	{
		return wrap.appendChild(
			Tag.render`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<input type="text" placeholder="${Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE')}" 
							class="calendar-field calendar-field-string"
						/>
					</div>
				</div>
		`)
		.querySelector('.calendar-field')
		;
	}

	renderRoomSelector()
	{
		const roomSelector = this.renderRoomSelectorWrap();

		this.roomTagSelector = this.createRoomTagSelector();

		this.roomTagSelector.renderTo(roomSelector.querySelector('.calendar-list-slider-card-widget-title'));
		if(this.roomTagSelector.isRendered())
		{
			this.onAfterRoomSelectorRender();
		}

		return roomSelector;
	}

	renderRoomSelectorWrap()
	{
		return Tag.render`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block" >
						<div class ="calendar-list-slider-card-widget-title" style="border: none">
							<span class="calendar-list-slider-card-widget-title-text">
								${Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR')}
							</span>
						</div>
					</div>
				</div>
		`;
	}

	createRoomTagSelector()
	{
		return new TagSelector({
			placeholder: Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR_PLACEHOLDER'),
			textBoxWidth:320,
			dialogOptions: {
				context: 'CALENDAR_CONTEXT',
				width: 315,
				height: 280,
				compactView: true,
				showAvatars: true,
				dropdownMode: true,
				preload: true,
				entities: [
					{
						id: 'room',
						dynamicLoad: true,
						filters: [
							{
								id: 'calendar.roomFilter',
							},
						],
					},
				],
				selectedItems: this.preparedSelectedRooms,
				tabs: [
					{
						id: 'room',
						title: 'rooms',
						itemOrder: { title: 'asc' },
						icon: 'none',
						stubOptions: { title: Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR_STUB') },
					},
				],
			},
		});
	}

	onAfterRoomSelectorRender()
	{
		//make avatar containers in input smaller and hide tab icon
		Dom.addClass(this.roomTagSelector.getDialog().getContainer(), 'calendar-category-form-room-selector-dialog');
		Dom.addClass(this.roomTagSelector.getContainer(), 'calendar-category-form-room-tag-selector');

		//make entity selector input style similar to other inputs in room slider
		Dom.addClass(this.roomTagSelector.getOuterContainer(), 'calendar-field-tag-selector-outer-container');
		Dom.addClass(this.roomTagSelector.getTextBox(), 'calendar-field-tag-selector-text-box');
	}

	createButtons(wrap)
	{
		this.buttonsWrap = wrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-btn-container"></div>
			`
		);

		if (this.actionType === 'createCategory')
		{
			this.renderCreateButton(this.buttonsWrap);
		}
		else if (this.actionType === 'updateCategory')
		{
			this.renderUpdateButton(this.buttonsWrap);
		}

		this.renderCancelButton(this.buttonsWrap);
	}

	renderCreateButton(wrap)
	{
		this.saveBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
			className: 'ui-btn ui-btn-success',
			events: { click: this.createCategory.bind(this) }
		});
		this.saveBtn.renderTo(wrap);
	}

	renderUpdateButton(wrap)
	{
		this.saveBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
			className: 'ui-btn ui-btn-success',
			events: { click: this.updateCategory.bind(this) }
		});
		this.saveBtn.renderTo(wrap);
	}

	renderCancelButton(wrap)
	{
		new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
			className: 'ui-btn ui-btn-link',
			events: { click: this.checkClose.bind(this) }
		}).renderTo(wrap);
	}

	createCategory()
	{
		if(this.freezeButtonsCallback)
		{
			this.freezeButtonsCallback();
		}
		this.saveBtn.setWaiting(true);
		const selectedRooms = this.getSelectedRooms();
		this.categoryManager.createCategory({
				name: this.DOM.categoryTitleInput.value,
				rooms: selectedRooms,
		})
		.then(() => {
			this.saveBtn.setWaiting(false);
			this.close();
		});
	}

	updateCategory()
	{
		if(this.freezeButtonsCallback)
		{
			this.freezeButtonsCallback();
		}
		const newSelectedRooms = this.prepareRoomsBeforeUpdate(this.getSelectedRooms());
		const oldSelectedRooms = this.prepareRoomsBeforeUpdate(this.preparedSelectedRooms);

		const toAddCategory = newSelectedRooms.filter(x => !oldSelectedRooms.includes(x));
		const toRemoveCategory = oldSelectedRooms.filter(x => !newSelectedRooms.includes(x));
		this.saveBtn.setWaiting(true);
		this.categoryManager.updateCategory({
			toAddCategory,
			toRemoveCategory,
			id: this.category.id,
			name: this.DOM.categoryTitleInput.value,
		})
		.then(() => {
			this.saveBtn.setWaiting(false);
			this.close();
		});
	}

	getSelectedRooms()
	{
		const items = this.roomTagSelector.getDialog().getSelectedItems();
		const rooms = [];
		items.map(item => rooms.push(item.id));

		return rooms;
	}

	keyHandler(e)
	{
		if (this.roomTagSelector.getDialog().isOpen())
		{
			return;
		}
		if (e.keyCode === Util.getKeyCode('escape'))
		{
			this.checkClose();
		}
		else if (e.keyCode === Util.getKeyCode('enter') && this.actionType === 'createCategory')
		{
			this.createCategory();
		}
		else if (e.keyCode === Util.getKeyCode('enter') && this.actionType === 'updateCategory')
		{
			this.updateCategory();
		}
	}

	prepareRoomsForDialog(rooms)
	{
		return rooms.map((room) => {
			return {
				id: room.id,
				entityId: 'room',
				title: room.name,
				avatarOptions: {
					'bgColor': room.color,
					'bgSize': '22px',
					'bgImage': 'none',
				},
				tabs: 'room',
			}
		});
	}

	prepareRoomsBeforeUpdate(rooms)
	{
		if(!rooms)
		{
			return  [];
		}

		return rooms.map((room) => {
			if(room.id)
			{
				return parseInt(room.id, 10);
			}

			return parseInt(room, 10);
		});
	}
}