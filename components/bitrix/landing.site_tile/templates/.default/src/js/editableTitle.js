import { Tag, Event, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

export default class EditableTitle {
	constructor(options)
	{
		this.title = options.title;
		this.phone = options.phone;
		this.type = options.type;
		this.item = options.item;
		this.url = options.url;
		this.disabled = options.disabled || false;
		this.isEditMode = false;

		this.$container = null;
		this.$containerInput = null;
		this.$containerTitle = null;
		this.$containerEditIcon = null;

		this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
		this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	}

	static get getTitle()
	{
		return this.title;
	}

	getContainerEdit()
	{
		if(!this.$containerEditIcon)
		{
			this.$containerEditIcon = Tag.render`<div class="landing-sites__title-edit"></div>`;
			// Event.bind(this.$containerEditIcon, 'click', this.adjustEditMode.bind(this));
		}

		return this.$containerEditIcon;
	}

	adjustEditMode()
	{
		this.isEditMode
			? this.closeEdit()
			: this.openEdit();
	}

	openEdit()
	{
		this.isEditMode = true;
		this.getContainer().classList.add('--edit');
		this.getContainerInput().select();
		this.getContainerInput().focus();
		this.getContainerInput().value = this.title;
		Event.bind(document.body, 'click', this.adjustCloseEditByClick);
		Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	}

	adjustCloseEditByClick(ev)
	{
		if(ev.type !== 'click')
		{
			return;
		}

		if(	ev.target !== this.getContainerInput()
			&& ev.target !== this.getContainerEdit())
		{
			this.closeEdit();
		}
	}

	adjustCloseEditByKeyDown(ev)
	{
		if(ev.type !== 'keydown')
		{
			return;
		}

		if(ev.keyCode === 27) // close by Escape
		{
			this.closeEdit();
			return;
		}

		if(ev.keyCode === 13) // close by Enter
		{
			this.closeEdit();
			this.updateTitle(this.getContainerInput().value);
		}
	}

	closeEdit()
	{
		this.isEditMode = false;
		this.getContainer().classList.remove('--edit');
		Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
		Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	}

	updateTitle(title: string)
	{
		if(	this.getContainerInput().value !== this.getContainerTitle().innerText
			&& this.getContainerInput().value !== '')
		{
			this.title = title;
			this.getContainerTitle().innerText = title;
			let type = this.type[0].toUpperCase() + this.type.slice(1);
			EventEmitter.emit('BX.Landing.SiteTile:update' + type, {
				item: this.item,
				title: this.title
			});
		}
	}

	getContainerInput()
	{
		if(!this.$containerInput)
		{
			this.$containerInput = Tag.render`<input
				value="${Text.encode(this.title)}"
				type="text"
				class="landing-sites__title-input">
			`;
		}

		return this.$containerInput;
	}

	getContainerTitle()
	{
		if(!this.$containerTitle)
		{
			let value;

			if(this.phone)
			{
				value = this.phone;
			}

			if(this.title)
			{
				value = this.title
			}

			this.$containerTitle = Tag.render`
				<div class="landing-sites__title-text --sub">
					${Text.encode(value)}
				</div>`;
		}

		return this.$containerTitle;
	}

	getContainer()
	{
		if(!this.$container)
		{
			if (this.disabled)
			{
				this.$container = Tag.render`
					<span class="landing-sites__title">
						${this.getContainerInput()}
						${this.getContainerTitle()}
					</span>
				`;
			}
			else
			{
				this.$container = Tag.render`
					<a href="${this.url}" class="landing-sites__title">
						${this.getContainerInput()}
						${this.getContainerTitle()}
						${this.getContainerEdit()}
					</a>
				`;
			}
		}

		return this.$container;
	}
}
