import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Event, Loc, Dom} from 'main.core';

import './css/zeroing.css';

export default class Zeroing extends EventEmitter
{
	static ACTIVE_CLASS: string = 'active';

	constructor()
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Zeroing');
		Event.bind(this.getLayout(), 'click', () => this.onClick());
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`<div class="landing-ui-field-color-zeroing">
				<div class="landing-ui-field-color-zeroing-preview">
					<div class="landing-ui-field-color-zeroing-state"></div>
				</div>
				<span class="landing-ui-field-color-primary-text">
					${Loc.getMessage('LANDING_FIELD_COLOR-ZEROING_TITLE_2')}
				</span>
			</div>`;
		});
	}

	onClick()
	{
		this.emit('onChange', {color: null});
	}

	setActive()
	{
		Dom.addClass(this.getLayout(), Zeroing.ACTIVE_CLASS);
	}

	unsetActive()
	{
		Dom.removeClass(this.getLayout(), Zeroing.ACTIVE_CLASS);
	}
}
