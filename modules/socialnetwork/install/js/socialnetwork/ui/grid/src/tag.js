import {ajax, Loc, Runtime} from 'main.core';
import {BaseEvent} from 'main.core.events';

import {Actions as ActionsController} from './actions';

export class Tag
{
	static options = {};

	static setOptions(options)
	{
		Tag.options = options;
	}

	static onTagClick(field)
	{
		const { filter } = Tag.options;
		filter.toggleByField(field);
	}

	static onTagAddClick(groupId, event)
	{
		Runtime.loadExtension('socialnetwork.entity-selector').then(exports => {
			const onRowUpdate = (event: BaseEvent) => {
				const { id } = event.getData();

				if (id === groupId)
				{
/*
					const row = ActionsController.getGridInstance().getRows().getById(id);
					const button = row.getCellById('TAGS').querySelector('.main-grid-tag-add');

					dialog.setTargetNode(button);
 */
				}
			};

			const onRowRemove = (event: BaseEvent) => {
/*
				const {id} = event.getData();
				if (id === groupId)
				{
					dialog.hide();
				}
*/
			};
			const onTagsChange = (event: BaseEvent) => {

				const dialog = event.getTarget();
				const tags = dialog.getSelectedItems().map(item => item.getId());

				void Tag.update(groupId, tags);
			};
			const { Dialog, Footer } = exports;
			const dialog = new Dialog({
				targetNode: event.getData().button,
				enableSearch: true,
				width: 350,
				height: 400,
				multiple: true,
				dropdownMode: true,
				compactView: true,
				context: 'SONET_GROUP_TAG',
				entities: [
					{
						id: 'project-tag',
						options: {
							groupId,
						},
					},
				],
				searchOptions: {
					allowCreateItem: true,
					footerOptions: {
						label: Loc.getMessage('SOCNET_ENTITY_SELECTOR_TAG_FOOTER_LABEL'),
					},
				},
				footer: Footer,
				footerOptions: {
					tagCreationLabel: true,
				},
				events: {
					onShow: () => {
/*
						EventEmitter.subscribe('Tasks.Projects.Grid:RowUpdate', onRowUpdate);
						EventEmitter.subscribe('Tasks.Projects.Grid:RowRemove', onRowRemove);
*/
					},
					onHide: () => {
/*
						EventEmitter.unsubscribe('Tasks.Projects.Grid:RowUpdate', onRowUpdate);
						EventEmitter.unsubscribe('Tasks.Projects.Grid:RowRemove', onRowRemove);
*/
					},
					'Search:onItemCreateAsync': (event: BaseEvent) => {
						return new Promise((resolve) => {
							const {searchQuery} = event.getData();
							const name = searchQuery.getQuery().toLowerCase();
							const dialog: Dialog = event.getTarget();

							setTimeout(() => {
								const item = dialog.addItem({
									id: name,
									entityId: 'project-tag',
									title: name,
									tabs: 'all',
								});
								if (item)
								{
									item.select();
								}
								resolve();
							}, 1000);
						});
					},
					'Item:onSelect': onTagsChange,
					'Item:onDeselect': onTagsChange,
				},
			});

			dialog.show();
		});
	}

	static update(groupId, tagList)
	{
		ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId: groupId,
				fields: {
					KEYWORDS: tagList.join(','),
				},
			},
		}).then(
			(response) => {
			},
			(response) => {
			}
		).catch(
			(response) => {
			}
		);

		ActionsController.hideActionsPanel();
		ActionsController.unselectRows();
	}
}
