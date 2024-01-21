import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

export class Roles extends EventEmitter
{
	constructor()
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Roles');
	}

	render(): HTMLElement
	{
		const rolesId = 'spaces-settings-roles';

		const node = Tag.render`
			<div
				data-id="${rolesId}"
				class="sn-spaces__popup-item --mini Roles&Rights"
			>
				<div class="sn-spaces__popup-icon-round">
					<div
						class="ui-icon-set --crown-1"
						style="--ui-icon-set__icon-size: 22px;"
					></div>
				</div>
				<div class="sn-spaces__popup-icon-round-name">
					${Loc.getMessage('SN_SPACES_MENU_ROLES')}
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => this.emit('click'));

		return node;
	}
}
