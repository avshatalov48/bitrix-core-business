import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Type, Dom} from 'main.core';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {Loc} from 'landing.loc';

import './css/style.css';

export default class FieldActionPanel extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.FormSettingsPanel.FieldRules.FieldActionPanel');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		if (Type.isPlainObject(options.style))
		{
			Dom.style(this.getLayout(), options.style);
		}
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-rule-field-action-panel">
					${this.getActionPanel().getLayout()}
					<div class="landing-ui-rule-field-action-panel-decoration">
						<div class="landing-ui-rule-field-action-panel-decoration-v-line"></div>
						<div class="landing-ui-rule-field-action-panel-decoration-h-line"></div>
					</div>
				</div>
			`;
		});
	}

	getActionPanel(): ActionPanel
	{
		return this.cache.remember('actionPanel', () => {
			return new ActionPanel({
				left: [
					{
						id: 'addCondition',
						text: Loc.getMessage('LANDING_RULE_GROUP_ADD_FIELD_CONDITION'),
						onClick: () => {
							this.emit('onAddCondition');
						},
					},
				],
			});
		});
	}
}
