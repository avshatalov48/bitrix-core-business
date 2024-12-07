import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Event, Loc, Text} from 'main.core';

import './css/reset.css';

export default class Reset extends EventEmitter
{
	constructor(options)
	{
		super();
		this.options = options;
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Reset');
		Event.bind(this.getLayout(), 'click', () => this.onClick());

		const hint = BX.UI.Hint.createInstance({
			popupParameters: {
				targetContainer: options.contentRoot,
				padding: 0,
			}
		});
		hint.init(this.getLayout());
	}

	getLayout(): HTMLElement
	{
		if (this.options && !this.options.styleNode)
		{
			return null;
		}

		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-reset-container">
					<div class="landing-ui-field-color-reset"
						data-hint="${Loc.getMessage('LANDING_FIELD_COLOR-RESET_HINT_2')}"
						data-hint-no-icon
					>
					</div>
				</div>
			`;
		});
	}

	onClick()
	{
		this.emit('onReset');
	}
}
