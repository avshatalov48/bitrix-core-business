import { Reflection, Type, Event, Text, Dom, Tag, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Alert, AlertColor } from 'ui.alerts';
import { Starter } from 'bizproc.workflow.starter';

import 'sidepanel';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class WorkflowStartList
{
	gridId;
	createTemplateButton;
	errorsContainerDiv;

	#signedDocumentType: string;
	#signedDocumentId: string;
	#counters: Map = new Map();

	#canEdit: boolean;
	#bizprocEditorUrl: string;

	popupHint;
	hintTimeout;

	constructor(options)
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		this.gridId = options.gridId;
		this.createTemplateButton = options.createTemplateButton;
		this.errorsContainerDiv = options.errorsContainerDiv;
		this.#canEdit = options.canEdit;
		this.#bizprocEditorUrl = options.bizprocEditorUrl;

		if (Type.isStringFilled(options.signedDocumentType))
		{
			this.#signedDocumentType = options.signedDocumentType;
		}

		if (Type.isStringFilled(options.signedDocumentId))
		{
			this.#signedDocumentId = options.signedDocumentId;
		}
	}

	init()
	{
		BX.UI.Hint.init(document);

		if (this.getGrid())
		{
			BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(this.getGrid());
		}

		EventEmitter.subscribe('Grid::updated', this.#onAfterGridUpdated.bind(this));
	}

	editTemplate(event, templateId): void
	{
		if (!this.#canEdit)
		{
			this.showNoPermissionsHint(event.target);

			return;
		}

		if (this.#bizprocEditorUrl.length === 0)
		{
			this.showNoEditorHint(event.target);

			return;
		}

		this.openBizprocEditor(templateId);
	}

	showAngleHint(node, text)
	{
		if (this.hintTimeout)
		{
			clearTimeout(this.hintTimeout);
		}

		this.popupHint = BX.UI.Hint.createInstance({
			popupParameters: {
				width: 334,
				height: 104,
				closeByEsc: true,
				autoHide: true,
				angle: {
					offset: Dom.getPosition(node).width / 2,
				},
				bindOptions: {
					position: 'top',
				},
			},
		});

		this.popupHint.close = function()
		{
			this.hide();
		};
		this.popupHint.show(node, text);
		this.timeout = setTimeout(this.hideHint.bind(this), 5000);
	}

	hideHint()
	{
		if (this.popupHint)
		{
			this.popupHint.close();
		}
		this.popupHint = null;
	}

	showNoPermissionsHint(node)
	{
		this.showAngleHint(node, Loc.getMessage('BIZPROC_CMP_WORKKFLOW_START_LIST_START_RIGHTS_ERROR'));
	}

	showNoEditorHint(node): void
	{
		this.showAngleHint(node, Loc.getMessage('BIZPROC_CMP_WORKKFLOW_START_LIST_START_MODULE_ERROR'));
	}

	static changePin(templateId, gridId, event) {
		const eventData = event.getData();
		const button = eventData.button;

		if (Dom.hasClass(button, BX.Grid.CellActionState.ACTIVE))
		{
			BX.Bizproc.Component.WorkflowStartList.action('unpin', templateId, gridId);
			Dom.removeClass(button, BX.Grid.CellActionState.ACTIVE);
		}
		else
		{
			BX.Bizproc.Component.WorkflowStartList.action('pin', templateId, gridId);
			Dom.addClass(button, BX.Grid.CellActionState.ACTIVE);
		}

		const grid = BX.Main.gridManager.getInstanceById(gridId);
		if (grid)
		{
			BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(grid);
		}
	}

	static action(action, templateId, gridId): void
	{
		const component = 'bitrix:bizproc.workflow.start.list';

		BX.ajax.runComponentAction(component, action, {
			mode: 'class',
			data: {
				templateId,
			},
		}).then(
			(response) => {
				const grid = BX.Main.gridManager.getInstanceById(gridId);
				if (grid)
				{
					grid.reload();
				}
			},
		);
	}

	showErrors(errors: Array<{message: string}>)
	{
		this.errorsContainerDiv.style.margin = '10px';

		errors.forEach((error) => {
			const alert = new Alert({
				text: error.message,
				color: AlertColor.DANGER,
				closeBtn: true,
				animated: true,
			});

			alert.renderTo(this.errorsContainerDiv);
		});
	}

	reloadGrid()
	{
		const grid = this.getGrid();
		if (grid)
		{
			grid.reload();
		}
	}

	getGrid(): ?BX.Main.grid
	{
		if (this.gridId)
		{
			return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
		}

		return null;
	}

	startWorkflow(event: PointerEvent, templateId: number)
	{
		event.preventDefault();

		const id = Text.toNumber(templateId);
		if (id <= 0 || !this.#signedDocumentType || !this.#signedDocumentId)
		{
			return;
		}

		const afterSuccessStart = () => {
			const slider = BX.SidePanel.Instance.getSliderByWindow(window);
			if (slider)
			{
				slider.close();

				return;
			}

			if (!this.#counters.has(templateId))
			{
				this.#counters.set(templateId, 0);
			}
			this.#counters.set(templateId, this.#counters.get(templateId) + 1);

			this.getGrid()?.reload();
		};

		Starter.singleStart({
			signedDocumentId: this.#signedDocumentId,
			signedDocumentType: this.#signedDocumentType,
			templateId: id,
		}, afterSuccessStart);
	}

	#onAfterGridUpdated()
	{
		if (this.getGrid())
		{
			BX.UI.Hint.init(this.getGrid().getContainer());
			BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(this.getGrid());
		}

		this.#counters.forEach((value, key) => {
			const counter = document.querySelector(`[data-role="template-${key}-counter"]`);
			if (Type.isElementNode(counter))
			{
				Dom.clean(counter);
				Dom.append(this.#renderStartedByMeNow(key), counter);
			}
		});
	}

	static colorPinnedRows(grid) {
		grid.getRows().getRows().forEach((row) => {
			const node = row.getNode();
			if (Type.isElementNode(node.querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active')))
			{
				Dom.addClass(node, 'bizproc-workflow-start-list-item-pinned');
			}
			else
			{
				Dom.removeClass(node, 'bizproc-workflow-start-list-item-pinned');
			}
		});
	}

	#renderStartedByMeNow(templateId: number): HTMLElement
	{
		let message = Text.encode(Loc.getMessage(
			'BIZPROC_CMP_TMP_WORKKFLOW_START_LIST_START_COUNTER',
			{
				'#COUNTER#': this.#counters.get(templateId),
			},
		));

		message = message.replace('[bold]', '<span class="bizproc-workflow-start-list-column-start-counter">');
		message = message.replace('[/bold]', '</span>');

		return Tag.render`<div class="ui-typography-text-xs">${message}</div>`;
	}

	openBizprocEditor(templateId)
	{
		top.window.location.href = this.#bizprocEditorUrl.replace('#ID#', templateId);
	}
}

namespace.WorkflowStartList = WorkflowStartList;
