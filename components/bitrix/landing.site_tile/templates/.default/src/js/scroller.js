import { Tag, Loc, Event, Dom } from 'main.core';

export default class Scroller {
	constructor(options)
	{
		this.grid = options.grid;
		this.scrollerText = options.scrollerText;
		this.$container = null;
		this.$lastItem = null;
		this.bindEvents();
		this.init();
	}

	bindEvents()
	{
		Event.bind(window, 'scroll', this.adjustPosition.bind(this));
	}

	show()
	{
		if(!this.getContainer().classList.contains('--show'))
		{
			this.getContainer().classList.remove('--hide');
			this.getContainer().classList.add('--show');
		}
	}

	hide()
	{
		if(!this.getContainer().classList.contains('--hide'))
		{
			this.getContainer().classList.remove('--show');
			this.getContainer().classList.add('--hide');
		}
	}
	
	adjustPosition()
	{
		if(!this.$lastItem)
		{
			this.$lastItem = this.grid.getItems()[this.grid.getItems().length - 1].getContainer();
		}

		this.$lastItem.getBoundingClientRect().top > document.documentElement.clientHeight
			? this.show()
			: this.hide();
	}

	getContainer()
	{
		if(!this.$container)
		{
			this.$container = Tag.render`
				<div class="landing-sites__scroller landing-sites__scope">
					<div class="landing-sites__scroller-button">
						<div class="landing-sites__scroller-icon"></div>
						<div class="landing-sites__scroller-text">
							${this.scrollerText
								? this.scrollerText
								: Loc.getMessage('LANDING_SITE_TILE_SCROLLER_SITES')}
						</div>
					</div>
				</div>
			`;

			Event.bind(this.$container, 'click', ()=> {
				let offsetY = window.pageYOffset;
				let timer = setInterval(()=> {
					if(
						(window.pageYOffset + 30) >= this.$lastItem.getBoundingClientRect().top + window.pageYOffset - document.body.clientTop
						|| window.pageYOffset + window.innerHeight >= document.body.scrollHeight
					)
					{
						clearInterval(timer);
					}
					offsetY = offsetY + 10;
					window.scrollTo(0,offsetY);
				}, 10);
			});
		}

		return this.$container;
	}
	
	init()
	{
		document.body.appendChild(this.getContainer());
		this.adjustPosition();
	}
}