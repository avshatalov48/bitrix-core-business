import { Type, Tag, Dom, Loc } from 'main.core';
import TouchDragListener from './touchdraglistener';
import 'ui.fonts.roboto';
import './style.css';

export default class BottomSheet
{
	constructor({
		content,
		help,
		className,
		padding
	})
	{
		this.content = Type.isDomNode(content) ? content : null;
		this.className = Type.isString(className) ? className : '';
		this.padding = Type.isString(padding) || Type.isNumber(padding) ? padding : null;

		this.help = null;
		switch (true)
		{
			case Type.isString(help):
				this.help = help;
				break;
			case Type.isFunction(help):
				this.help = help;
				break;
		}

		this.layout = {
			wrapper: null,
			container: null,
			content: null,
			overlay: null,
			close: null,
			help: null
		};

		this.halfOfHeight = 0;
		this.currentHeight = null;

		this.sheetListener = new TouchDragListener({
			element: this.#getPanel(),
			touchStartCallback: ({element, active, initialY, currentY, yOffset}) => {
				element.style.setProperty('--translateY', 'translateY(0)');
				element.style.setProperty('transition', 'unset');
			},
			touchEndCallback: ({element, active, initialY, currentY, yOffset}) => {
				element.style.setProperty(
					'transition',
					'transform .3s'
				);
				element.style.setProperty(
					'--translateY',
					'translateY(' + currentY + 'px)'
				);

				if (parseInt(currentY) > this.halfOfHeight)
				{
					this.close();
				}
			},
			touchMoveCallback: ({element, active, initialY, currentY, yOffset}) => {
				
				if (currentY <= 0)
				{
					return;
				}

				if (currentY <= -40)
				{
					currentY = -41 + currentY / 10;
				}

				element.style.setProperty(
					'--translateY',
					'translateY(' + currentY + 'px)'
				);
			}
		});

		if (this.content)
		{
			this.setContent(this.content);
		}
	}

	#getOverlay()
	{
		if (!this.layout.overlay)
		{
			this.layout.overlay = Tag.render`
				<div class="ui-bottomsheet__overlay"></div>
			`;

			this.layout.overlay.addEventListener('click', this.close.bind(this));
		}

		return this.layout.overlay;
	}

	#getHelp()
	{
		if (!this.layout.help)
		{
			if (Type.isString(this.help))
			{
				this.layout.help = Tag.render`
					<a href="${this.help}" class="ui-bottomsheet__panel-control--item --cursor-pointer">
						<span class="ui-bottomsheet__panel-control--item-text">${Loc.getMessage('UI_BOTTOMSHEET_HELP')}</span>
					</a>
				`;
			}

			if (Type.isFunction(this.help))
			{
				this.layout.help = Tag.render`
					<div class="ui-bottomsheet__panel-control--item --cursor-pointer">
						<div class="ui-bottomsheet__panel-control--item-text">${Loc.getMessage('UI_BOTTOMSHEET_HELP')}</div>
					</div>
				`;

				this.layout.help.addEventListener('click', ()=> {
					this.help();
				});
			}
		}

		return this.layout.help;
	}

	#getClose()
	{
		if (!this.layout.close)
		{
			this.layout.close = Tag.render`
				<div class="ui-bottomsheet__panel-control--item --cursor-pointer --close">
					<div class="ui-bottomsheet__panel-control--item-text">${Loc.getMessage('UI_BOTTOMSHEET_CLOSE')}</div>
				</div>
			`;

			this.layout.close.addEventListener('click', this.close.bind(this));
		}

		return this.layout.close;
	}

	#getPanel()
	{
		if (!this.layout.container)
		{
			const panelWrapper = Tag.render`
				<div class="ui-bottomsheet__panel-wrapper">
					${this.#getContent()}
				</div>
			`;

			if (this.padding || this.padding === 0)
			{
				let padding;

				switch(true)
				{
					case Type.isString(this.padding):
						padding = this.padding;
						break;

					case Type.isNumber(this.padding):
						padding = this.padding + 'px';
						break;
				}

				panelWrapper.style.setProperty(
					'padding',
					padding
				);
			}

			this.layout.container = Tag.render`
				<div class="ui-bottomsheet__panel">
					<div class="ui-bottomsheet__panel-control">
						${this.help ? this.#getHelp() : ''}
						<div class="ui-bottomsheet__panel-handler"></div>
						${this.#getClose()}
					</div>
					${panelWrapper}
				</div>
			`;
		}

		return this.layout.container;
	}

	#getContent()
	{
		if (!this.layout.content)
		{
			this.layout.content = Tag.render`
				<div class="ui-bottomsheet__panel-content"></div>
			`;
		}

		return this.layout.content;
	}

	#getWrapper()
	{
		if (!this.layout.wrapper)
		{
			this.layout.wrapper = Tag.render`
				<div class="ui-bottomsheet ui-bottomsheet__scope ${this.className}"></div>
			`;
		}

		return this.layout.wrapper;
	}
	
	setContent(content: HTMLElement)
	{
		if (Type.isDomNode(content))
		{
			Dom.clean(this.#getContent());
			this.#getContent().appendChild(content);
		}

		if (Type.isString(content))
		{
			Dom.clean(this.#getContent());
			this.#getContent().innerText = content;
		}
	}

	adjustPosition()
	{

	}

	adjustSize()
	{
		if (this.currentHeight !== this.#getPanel().offsetHeight)
		{
			let currentHeight = this.currentHeight;
			let newHeight = this.#getPanel().offsetHeight;
			this.#getPanel().style.setProperty(
				'height',
				currentHeight + 'px'
			);

			setTimeout(()=> {
				currentHeight = this.#getPanel().offsetHeight;
				this.#getPanel().style.setProperty(
					'height',
					newHeight + 'px'
				);

				const adjustHeight = ()=> {
					this.#getPanel().style.removeProperty(
						'height',
						newHeight + 'px'
					);
					this.#getPanel().removeEventListener('transitionend', adjustHeight)
				};

				this.#getPanel().addEventListener('transitionend', adjustHeight);

				this.currentHeight = newHeight;
				this.halfOfHeight = this.currentHeight / 2;
			});
		}
	}

	close()
	{
		if (this.#getWrapper().parentNode)
		{
			this.#getPanel().classList.remove('--show');
			this.#getOverlay().classList.remove('--show');

			const animationProgress = () => {
				this.#getWrapper().classList.remove('--show');
				this.#getPanel().removeEventListener('transitionend', animationProgress);
			}

			this.#getPanel().addEventListener('transitionend', animationProgress)
		}
	}

	show()
	{
		if (!this.#getWrapper().parentNode)
		{
			this.#getWrapper().appendChild(this.#getOverlay());
			this.#getWrapper().appendChild(this.#getPanel());
			document.body.appendChild(this.#getWrapper());
		}

		this.#getWrapper().classList.add('--show');

		setTimeout(()=> {
			this.currentHeight = this.#getPanel().offsetHeight;
			this.halfOfHeight = this.currentHeight / 2;
			this.#getPanel().classList.add('--show');
			this.#getOverlay().classList.add('--show');
		});
	}
}