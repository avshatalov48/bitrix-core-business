import { Dom, Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	follow: boolean,
	canUse: boolean,
}

export class Follow extends EventEmitter
{
	#follow: boolean;
	#canUse: boolean;
	#disabled: boolean;
	#node: HTMLElement;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Follow');

		this.#follow = params.follow;
		this.#canUse = params.canUse === true;

		this.#disabled = false;

		this.#bindEvents();
	}

	render(): HTMLElement
	{
		const followId = 'spaces-settings-follow';

		const iconClass = this.#follow ? '--sound-on' : '--sound-off';

		const disabled = this.#canUse ? '' : '--disabled';

		this.#node = Tag.render`
			<div
				data-id="${followId}"
				class="sn-spaces__popup-item --mini ${disabled}"
			>
				<div class="sn-spaces__popup-icon-round">
					<div
						class="ui-icon-set ${iconClass}"
						style="--ui-icon-set__icon-size: 22px;"
					></div>
				</div>
				<div class="sn-spaces__popup-icon-round-name">
					${this.#getLabel(this.#follow)}
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

		this.#follow = !this.#follow;

		this.emit('update', this.#follow);

		this.#changeIcon(this.#follow);

		this.#changeLabel(this.#follow);
	}

	#changeIcon(follow: boolean)
	{
		const iconNode = this.#node.querySelector('.ui-icon-set');
		if (follow)
		{
			Dom.removeClass(iconNode, '--sound-off');
			Dom.addClass(iconNode, '--sound-on');
		}
		else
		{
			Dom.removeClass(iconNode, '--sound-on');
			Dom.addClass(iconNode, '--sound-off');
		}
	}

	#changeLabel(follow: boolean)
	{
		const nameNode = this.#node.querySelector('.sn-spaces__popup-icon-round-name');
		nameNode.textContent = this.#getLabel(follow);
	}

	#getLabel(follow: boolean): string
	{
		return follow
			? Loc.getMessage('SN_SPACES_MENU_FOLLOW_N')
			: Loc.getMessage('SN_SPACES_MENU_FOLLOW_Y')
		;
	}

	#bindEvents(): void
	{
		EventEmitter.subscribe('followChanged', function(event) {
			this.#follow = event.data.isFollowed;
			this.#changeLabel(this.#follow);
			this.#changeIcon(this.#follow);
		}.bind(this));
	}
}