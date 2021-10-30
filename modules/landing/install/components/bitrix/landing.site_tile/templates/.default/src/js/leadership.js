import { Tag, Event, Loc } from 'main.core';
import {EventEmitter} from 'main.core.events';

export default class LeaderShip {
	constructor(options)
	{
		this.id = options.id;
		this.item = options.item;
		this.articles = options.articles || [];

		this.$container = null;
		this.$containerClose = null;

		this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
		this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	}

	show()
	{
		this.getContainer().classList.add('--show');
		Event.bind(document.body, 'click', this.adjustCloseEditByClick);
		Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
		EventEmitter.emit('BX.Landing.SiteTile:showLeadership', this.item);
	}

	hide()
	{
		this.getContainer().classList.remove('--show');
		Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
		Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
		EventEmitter.emit('BX.Landing.SiteTile:hideLeadership', this.item);
	}

	adjustCloseEditByClick(ev)
	{
		if(	ev.type !== 'click')
		{
			return;
		}

		if(	!ev.target.closest('.landing-sites__helper-' + this.id)
			&& ev.target.className !== 'landing-sites__preview-leadership-text')
		{
			this.hide();
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
			this.hide();
		}
	}

	getContainerClose()
	{
		if(!this.$containerClose)
		{
			this.$containerClose = Tag.render`
				<div class="landing-sites__helper-close-toggler">${Loc.getMessage('LANDING_SITE_TILE_HIDE')}</div>
			`;

			Event.bind(this.$containerClose, 'click', this.hide.bind(this));
		}

		return this.$containerClose;
	}

	getContainer()
	{
		if(!this.$container)
		{
			let articlesNode = Tag.render`<div class="landing-sites__helper-list"></div>`;

			for (let i = 0; i < this.articles.length; i++)
			{
				let item = this.articles[i];
				articlesNode.appendChild(Tag.render`
					<div class="landing-sites__helper-item ${item.read ? '--read' : ''}">
						<div class="landing-sites__helper-item-title">${item.title}</div>
						<div class="landing-sites__helper-item-container">
							<div class="landing-sites__helper-item-text">${item.text}</div>
							<div class="landing-sites__helper-item-button ${item.read ? '--read' : ''}"">
								${item.read 
									? Loc.getMessage('LANDING_SITE_TILE_READ')
									: Loc.getMessage('LANDING_SITE_TILE_TO_READ')}
							</div>
						</div>
					</div>
				`);
			}

			this.$container = Tag.render`
				<div class="landing-sites__helper landing-sites__helper-${this.id}">
					<div class="landing-sites__helper-title">
						<div class="landing-sites__helper-title-text">${Loc.getMessage('LANDING_SITE_TILE_LEADERSHIP_TITLE')}</div>
						${this.getContainerClose()}
					</div>
					<div class="landing-sites__helper-container">
						${articlesNode}
					</div>
				</div>
			`;
		}

		return this.$container;
	}
}