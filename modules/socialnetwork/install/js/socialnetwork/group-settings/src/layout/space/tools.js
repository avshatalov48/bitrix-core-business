import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { Switcher } from 'ui.switcher';
import { Feature } from './feature';

import type { GroupFeature } from '../../type';
import { Controller } from 'socialnetwork.controller';

export class Tools extends Feature
{
	#groupId: number;
	#features: Map<GroupFeature>;
	#name: string;
	#icon: string;

	constructor(groupId: number, features: Array<GroupFeature>)
	{
		super();

		const availableFeaturesList = new Set(['tasks', 'calendar', 'files']);

		const availableFeatures = features
			.filter((feature: GroupFeature) => availableFeaturesList.has(feature.featureName))
		;

		this.#groupId = Type.isUndefined(groupId) ? 0 : parseInt(groupId, 10);
		this.#features = new Map();
		this.#name = Loc.getMessage('SN_SIDE_PANEL_SPACE_SETTINGS');
		this.#icon = 'settings';

		availableFeatures.forEach((feature: GroupFeature) => {
			this.#features.set(feature.id, feature);
		});
	}

	getName(): string
	{
		return this.#name;
	}

	getIcon(): string
	{
		return this.#icon;
	}

	renderContent(): HTMLElement
	{
		const { node } = Tag.render`
			<div ref="node" class="sn-side-panel__space-settings_section-content-wrapper">
				<div class="sn-side-panel__space-settings_section-content-wrapper-block">
					<div class="ui-alert ui-alert-primary ui-alert-xs">
						<div class="ui-alert-message">
							<div class="sn-side-panel__space-settings_alert-text">
								${Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS')}
								<span ref="toolsMore" class="sn-side-panel__space-settings_alert-text-more">
									<!--${Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_MORE')}-->
								</span>
							</div>
						</div>
					</div>
					${this.#switcherItems(this.#features)}
				</div>
			</div>
		`;

		return node;
	}

	#switcherItems(features: Map<GroupFeature>): HTMLElement
	{
		return Tag.render`
			<div class="sn-side-panel__space-settings_section-content-wrapper-block-content">
				${[...features.values()].map((feature: GroupFeature) => this.#switcherItemRender(feature))}
			</div>
		`;
	}

	#switcherItemRender(feature: GroupFeature): HTMLElement
	{
		const name = feature.customName === '' ? feature.name : feature.customName;
		const featureCustom = feature.customName === '' ? '--standard' : '--custom';

		const { node, switcherToggle, switcherContainer, editButton, refreshButton, inputFeature } = Tag.render`
			<div ref="node" class="sn-side-panel__space-settings_section-item ${featureCustom}">
				<div class="sn-side-panel__space-settings_section-switcher-toggle">
					<div ref="switcherContainer" class="sn-side-panel__space-settings_switcher"></div>
					<div ref="switcherToggle" class="sn-side-panel__space-settings_section-item-text">
						${Text.encode(feature.name)}
					</div>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-inline sn-side-panel__space-settings_section-item-input-block">
						<input ref="inputFeature"  name="tasks_name" type="text" class="ui-ctl-element" data-role="feature-input-text" value="${Text.encode(name)}">
					</div>
				</div>
			<div ref="editButton" class="ui-icon-set --pencil-40 sn-side-panel__space-settings_edit-btn" style="--ui-icon-set__icon-size: 19px;"></div>
			<div ref="refreshButton" class="ui-icon-set --undo-1 sn-side-panel__space-settings_refresh" style="--ui-icon-set__icon-size: 19px;"></div>
			</div>
		`;

		const switcher = new Switcher({
			node: switcherContainer,
			size: 'extra-small',
			color: 'primary',
			checked: feature.active,
			handlers: {
				toggled: () => {
					this.#toggleActive(feature.id);
					this.#save(feature.id);
				},
			},
		});

		Event.bind(switcherToggle, 'click', () => {
			switcher.check(!feature.active);
		});

		Event.bind(editButton, 'click', () => {
			Dom.removeClass(node, '--standard');
			Dom.addClass(node, '--custom');
		});

		Event.bind(refreshButton, 'click', () => {
			Dom.addClass(node, '--standard');
			Dom.removeClass(node, '--custom');
			this.#changeName(feature.id);
			inputFeature.value = feature.name;
		});

		Event.bind(inputFeature, 'blur', () => {
			this.#changeName(feature.id, inputFeature.value.trim());
		});

		return node;
	}

	#save(featureId: number)
	{
		Controller.changeFeature(this.#groupId, this.#features.get(featureId));
	}

	#toggleActive(featureId: number)
	{
		const feature = this.#features.get(featureId);
		feature.active = !feature.active;

		this.#features.set(featureId, feature);
	}

	#changeName(featureId: number, name: string)
	{
		if (name)
		{
			this.#features.get(featureId).customName = name;
		}
		else
		{
			this.#features.get(featureId).customName = null;
		}

		Controller.changeFeature(this.#groupId, this.#features.get(featureId));
	}
}
