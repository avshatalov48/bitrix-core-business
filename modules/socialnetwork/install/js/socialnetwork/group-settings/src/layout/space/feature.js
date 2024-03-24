import { Dom, Event, Tag } from 'main.core';

export class Feature
{
	#name: string;
	#icon: string;

	constructor(name: string)
	{
		this.#name = name;
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
		return Tag.render``;
	}

	render(): HTMLElement
	{
		const { node, headerElement, toolsMore } = Tag.render`
			<div ref="node" class="ui-slider-section sn-side-panel__space-settings_section --active">
				<div ref="headerElement" class="sn-side-panel__space-settings_section-title">
					<div class="ui-icon-set --${this.getIcon()}"></div>
					<div class="sn-side-panel__space-settings_section-title-text">
						${this.getName()}
					</div>
					<div class="ui-icon-set --chevron-down"></div>
				</div>
				<div class="sn-side-panel__space-settings_section-content">
					${this.renderContent()}
				</div>
			</div>
		`;

		Event.bind(toolsMore, 'click', () => {
			console.log('show helper sidePanel');
		});

		Event.bind(headerElement, 'click', () => {
			Dom.toggleClass(node, '--active');
		});

		return node;
	}
}
