import { Tag, Dom, Loc, Event} from 'main.core';
import { EditForm } from '../../sectioninterface/src/editform';
import { Util } from 'calendar.util';

export class EditFormRoom extends EditForm
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Calendar.Rooms.EditFormRoom');

		this.DOM.outerWrap = options.wrap;
		this.roomsManager = options.roomsManager;
		this.capacityNumbers = [3, 5, 7, 10, 25];
		this.zIndex = options.zIndex || 3100;
		this.closeCallback = options.closeCallback;
		this.BX = Util.getBX();
		this.keyHandlerBinded = this.keyHandler.bind(this);
	}

	show(params = {})
	{
		this.actionType = params.actionType;
		this.room = params.room;
		this.create();
		this.showAccess = params.showAccess !== false;
		if (this.showAccess)
		{
			Dom.style(this.DOM.accessLink, 'display', null);
			Dom.style(this.DOM.accessWrap, 'display', null);
		}
		else
		{
			Dom.style(this.DOM.accessLink, 'display', 'none');
			Dom.style(this.DOM.accessWrap, 'display', 'none');
		}

		Event.bind(document, 'keydown', this.keyHandlerBinded);
		Dom.addClass(this.DOM.outerWrap, 'show');

		if (params.room)
		{
			if (params.room.color)
			{
				this.setColor(params.room.color);
			}

			this.setAccess(params.room.access || params.room.data.ACCESS || {});

			if (params.room.name)
			{
				this.DOM.roomsTitleInput.value = params.room.name;
			}

			if(params.room.capacity)
			{
				this.DOM.roomsCapacityInput.value = params.room.capacity;
			}
		}

		BX.focus(this.DOM.roomsTitleInput);
		if (this.DOM.roomsTitleInput.value !== '')
		{
			this.DOM.roomsTitleInput.select();
		}

		this.isOpenedState = true;
	}

	create()
	{
		this.wrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
		if (this.wrap)
		{
			Dom.clean(this.wrap);
		}
		else
		{
			this.wrap = this.DOM.outerWrap.appendChild(
				Tag.render`
					<div class="calendar-form-content"></div>
				`);
		}

		this.DOM.formFieldsWrap = this.wrap.appendChild(
			Tag.render`
			<div class="calendar-list-slider-widget-content"></div>
			`)
			.appendChild(
				Tag.render`
				<div class="calendar-list-slider-widget-content-block"></div>`
			);

		// Title
		this.DOM.roomsTitleInput = this.DOM.formFieldsWrap.appendChild(
			Tag.render`
			<div class="calendar-field-container calendar-field-container-string"></div>`
			)
			.appendChild(
				Tag.render`
			<div class="calendar-field-block"></div>`
			)
			.appendChild(
				Tag.render`
			<input type="text" placeholder="${Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE')}" 
			class="calendar-field calendar-field-string"/>`);

		//Capacity
		this.DOM.roomsCapacityInput = this.DOM.formFieldsWrap.appendChild(
				Tag.render`
			<div class="calendar-field-container calendar-field-container-string"></div>`
			)
			.appendChild(
				Tag.render`
			<div class="calendar-field-block"></div>`
			)
			.appendChild(
				Tag.render`
					<div class ="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">
							${Loc.getMessage('EC_SEC_SLIDER_SECTION_CAPACITY')}
						</span>	
					</div>						
					`
			)
			.appendChild(
				Tag.render`
			<input type="number" class="calendar-field calendar-field-number" placeholder="0"/>`);

		this.DOM.optionsWrap = this.DOM.formFieldsWrap.appendChild(
			Tag.render`
			<div class="calendar-list-slider-new-calendar-options-container"></div>`
		);

		this.initSectionColorSelector();

		this.initAccessController();

		// Buttons
		this.buttonsWrap = this.DOM.formFieldsWrap.appendChild(
			Tag.render`
			<div class="calendar-list-slider-btn-container"></div>`
		);
		if (this.actionType === 'createRoom')
		{
			this.saveBtn = new BX.UI.Button({
				text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
				className: 'ui-btn ui-btn-success',
				events: { click: this.createRoom.bind(this) }
			});
			this.saveBtn.renderTo(this.buttonsWrap);
		}
		else if (this.actionType === 'updateRoom')
		{
			this.saveBtn = new BX.UI.Button({
				text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
				className: 'ui-btn ui-btn-success',
				events: { click: this.updateRoom.bind(this) }
			});
			this.saveBtn.renderTo(this.buttonsWrap);
		}
		new BX.UI.Button({
			text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
			className: 'ui-btn ui-btn-link',
			events: { click: this.checkClose.bind(this) }
		}).renderTo(this.buttonsWrap);

		this.isCreated = true;
	}

	createRoom()
	{
		this.saveBtn.setWaiting(true);
		this.roomsManager.createRoom({
				name: this.DOM.roomsTitleInput.value,
				capacity: this.DOM.roomsCapacityInput.value,
				color: this.color,
				access: this.access
			})
			.then(() => {
				this.saveBtn.setWaiting(false);
				this.close();
			});
	}

	initAccessController()
	{
		this.buildAccessController();
		this.initDialogStandard();
		this.initAccessSelectorPopup();
	}

	updateRoom()
	{
		this.saveBtn.setWaiting(true);
		this.roomsManager.updateRoom({
				id: this.room.id,
				location_id: this.room.location_id,
				name: this.DOM.roomsTitleInput.value,
				capacity: this.DOM.roomsCapacityInput.value,
				color: this.color,
				access: this.access
			})
			.then(() => {
				this.saveBtn.setWaiting(false);
				this.close();
			});
	}

	keyHandler(e)
	{
		if(e.keyCode === Util.getKeyCode('escape'))
		{
			this.checkClose();
		}
		else if(e.keyCode === Util.getKeyCode('enter') && this.actionType === 'createRoom')
		{
			this.createRoom();
		}
		else if(e.keyCode === Util.getKeyCode('enter') && this.actionType === 'updateRoom')
		{
			this.updateRoom();
		}
	}
}