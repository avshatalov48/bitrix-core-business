import { Dom, Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	pin: boolean,
	canUse: boolean,
}

export class Pin extends EventEmitter
{
	#pin: boolean;
	#disabled: boolean;
	#canUse: boolean;
	#node: HTMLElement;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Pin');

		this.#pin = params.pin;
		this.#canUse = params.canUse === true;

		this.#disabled = false;

		this.#bindEvents();
	}

	render(): HTMLElement
	{
		const pinId = 'spaces-settings-pin';

		const iconClass = this.#pin ? '--pin-2' : '--pin-1';

		const disabled = this.#canUse ? '' : '--disabled';

		this.#node = Tag.render`
			<div
				data-id="${pinId}"
				class="sn-spaces__popup-item --mini ${disabled}"
			>
				<div class="sn-spaces__popup-icon-round">
					<div
						class="ui-icon-set ${iconClass}"
						style="--ui-icon-set__icon-size: 22px;"
					></div>
				</div>
				<div class="sn-spaces__popup-icon-round-name">
					${this.#getLabel(this.#pin)}
				</div>
			</div>
		`;

		Event.bind(this.#node, 'click', this.#toggle.bind(this));

		return this.#node;
	}

	unDisable()
	{
		this.#disabled = false;
	}

	#toggle()
	{
		if (this.#disabled)
		{
			return;
		}

		this.#disabled = true;

		this.#pin = !this.#pin;

		this.emit('update', this.#pin);

		this.#changeIcon(this.#pin);

		this.#changeLabel(this.#pin);
	}

	#changeIcon(pin: boolean)
	{
		const iconNode = this.#node.querySelector('.ui-icon-set');
		if (pin)
		{
			Dom.removeClass(iconNode, '--pin-1');
			Dom.addClass(iconNode, '--pin-2');
		}
		else
		{
			Dom.removeClass(iconNode, '--pin-2');
			Dom.addClass(iconNode, '--pin-1');
		}
	}

	#changeLabel(pin: boolean)
	{
		const nameNode = this.#node.querySelector('.sn-spaces__popup-icon-round-name');
		nameNode.textContent = this.#getLabel(pin);
	}

	#getLabel(pin: boolean): string
	{
		return pin
			? Loc.getMessage('SN_SPACES_MENU_PIN_N')
			: Loc.getMessage('SN_SPACES_MENU_PIN_Y')
		;
	}

	#bindEvents(): void
	{
		EventEmitter.subscribe('pinChanged', function(event) {
			this.#pin = event.data.isPinned;
			this.#changeLabel(this.#pin);
			this.#changeIcon(this.#pin);
		}.bind(this));
	}
}