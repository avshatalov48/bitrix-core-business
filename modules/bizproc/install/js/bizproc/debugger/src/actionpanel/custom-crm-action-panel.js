import {Loc, Text} from 'main.core';
import {ActionPanelGuide} from "../tourguide/action-panel-guide";
import {EventEmitter} from 'main.core.events';
import Automation from "../automation";
import {Manager} from "bizproc.debugger";

export class CustomCrmActionPanel
{
	#actionPanel: BX.UI.ActionPanel = null;
	#grid: BX.CRM.Kanban.Grid = null;
	#guides: Array<ActionPanelGuide> = [];
	#debuggerInstance: Automation;

	constructor(grid: BX.CRM.Kanban.Grid, debuggerInstance: Automation)
	{
		this.#grid = grid;
		this.#debuggerInstance = debuggerInstance;

		this.#actionPanel = new BX.UI.ActionPanel({
			removeLeftPosition: true,
			maxHeight: 58,
			parentPosition: 'bottom',
			autoHide: false,
		});

		this.#appendItems();

		const onHideActionPanelHandler = this.#onHideActionPanel.bind(this);
		EventEmitter.subscribe(this.#actionPanel, 'BX.UI.ActionPanel:hidePanel', onHideActionPanelHandler);
	}

	#appendItems()
	{
		this.#actionPanel.appendItem({
			id: 'fix_entity',
			text: Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FIX_DEAL_ACTION_1'),
			onclick: this.fixEntityAction.bind(this)
		});

		this.#actionPanel.appendItem({
			id: 'remove_entity',
			text: this.#getRemoveEntityActionText(),
			onclick: this.removeEntityAction.bind(this)
		});

		this.#actionPanel.appendItem({
			id: 'finish_debug',
			text: Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FINISH_DEBUG_ACTION'),
			onclick: function () {
				Manager.Instance.askFinishSession(this.#debuggerInstance.session).then(
					() => {
						this.stopActionPanel();
					},
					(response) => {
						this.#handleRejectResponse(response, 'finish_debug');
					}
				);
			}.bind(this),
			}
		);
	}

	get actionPanel(): BX.UI.ActionPanel
	{
		return this.#actionPanel;
	}

	#onHideActionPanel()
	{
		this.#guides.forEach((guide) => {
			guide.finish();
		})
	}

	fixEntityAction()
	{
		const checkedIds = this.#getCheckedIdsInBpStyle();
		if (checkedIds.length !== 1)
		{
			const guide = new ActionPanelGuide({
				target: this.actionPanel.getItemById('fix_entity').layout.container,
				title: Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FIX_DEAL_COUNT_ERROR_TITLE'),
				article: 'limit_office_bp_designer', // todo: replace,
			});
			this.#guides.push(guide);

			guide.start();

			return;
		}

		this.#debuggerInstance.session.fixateDocument(checkedIds[0]).then(
			() => {
				this.stopActionPanel();
				Manager.Instance.requireSetFilter(this.#debuggerInstance.session, true);
				if (this.#debuggerInstance.settings.get('popup-collapsed'))
				{
					this.#debuggerInstance.getMainView().showExpanded();
				}
			},
			(response) => {
				this.#handleRejectResponse(response, 'fix_entity');
			}
		);
	}

	#getRemoveEntityActionText(): string
	{
		return `
			<span>${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_REMOVE_DEAL_ACTION_1'))}</span>
		`;
	}

	removeEntityAction()
	{
		const checkedIds = this.#getCheckedIdsInBpStyle();

		this.#debuggerInstance.session.removeDocuments(checkedIds).then(
			() => {
				this.actionPanel.hidePanel();
				this.#grid.reload();
			},
			(response) => {
				this.#handleRejectResponse(response, 'remove_entity');
			}
		);
	}

	#getCheckedIdsInBpStyle(): Array
	{
		const checkedIds = this.#grid.getCheckedId();

		// todo: get EntityType from another place
		return checkedIds.map(id => 'DEAL_' + id);
	}

	stopActionPanel()
	{
		this.actionPanel.hidePanel();
		this.#grid.resetActionPanel();
		this.#grid.stopActionPanel();
	}

	#handleRejectResponse(response, actionId)
	{
		if (!response.errors)
		{
			return;
		}

		let message = '';
		response.errors.forEach((error) => {
			message = message + '\n' + error.message;
		});

		const guide = new ActionPanelGuide({
			target: this.actionPanel.getItemById(actionId).layout.container,
			title: message,
			article: 'limit_office_bp_designer', // todo: replace,
		});
		this.#guides.push(guide);

		guide.start();
	}
}