import { Loc, Tag } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { TagSelector } from 'ui.entity-selector';
import { Feature } from './feature';
import { Controller } from 'socialnetwork.controller';

export class Tags extends Feature
{
	#groupId: number;
	#name: string;
	#icon: string;

	#layout: {
		container: HTMLElement,
		selectorContainer: HTMLElement,
	};

	constructor(groupId: number)
	{
		super();

		this.#groupId = groupId;

		this.#layout = {};
		this.#name = Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS');
		this.#icon = 'tag';
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
		const { node, selector } = Tag.render`
			<div ref="node" class="sn-side-panel__space-settings_section-content-wrapper">
				<div
					ref="selector"
					class="sn-side-panel__space-settings_section-content-wrapper-block"
				></div>
			</div>
		`;

		this.#layout.container = node;
		this.#layout.selectorContainer = selector;

		return node;
	}

	renderSelector(): void
	{
		const saveTags = (selectedTags: Array) => {
			const tags = [];
			selectedTags.forEach((item) => {
				tags.push(item.id);
			});

			Controller.changeTags(this.#groupId, tags);
		};

		const tagSelector = new TagSelector({
			addButtonCaption: Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD'),
			addButtonCaptionMore: Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD_TAG'),
			dialogOptions: {
				width: 350,
				height: 300,
				offsetLeft: 50,
				compactView: true,
				preload: true,
				context: 'PROJECT_TAG',
				searchTabOptions: {
					stubOptions: {
						title: Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_SEARCH_FAILED'),
						subtitle: Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_SEARCH_ADD_HINT'),
						arrow: true,
					},
				},
				entities: [
					{
						id: 'project-tag',
						options: {
							groupId: this.#groupId,
						},
					},
				],
				searchOptions: {
					allowCreateItem: true,
					footerOptions: {
						label: Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD_FOOTER_LABEL'),
					},
				},
				events: {
					'Item:onSelect': (event) => {
						saveTags(event.getTarget().getSelectedItems());
					},
					'Item:onDeselect': (event) => {
						saveTags(event.getTarget().getSelectedItems());
					},
					'Search:onItemCreateAsync': (event: BaseEvent) => {
						return new Promise((resolve) => {
							const { searchQuery } = event.getData();
							const name = searchQuery.getQuery().toLowerCase();
							const dialog = event.getTarget();

							setTimeout(() => {
								const tagsList = name.split(',');

								tagsList.forEach((tag) => {
									const item = dialog.addItem({
										id: tag,
										entityId: 'project-tag',
										title: tag,
										tabs: ['all', 'recents'],
									});
									item.select();
								});

								resolve();
							}, 1000);
						});
					},
				},
			},
		});

		tagSelector.renderTo(this.#layout.selectorContainer);
	}
}
