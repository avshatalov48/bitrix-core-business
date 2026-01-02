import { Type, Dom, Text, Loc, ajax, Runtime, Tag, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Alert, AlertColor } from 'ui.alerts';
import { Router } from 'bizproc.router';

import './style.css';

import 'ui.design-tokens';

export class UserProcessesStart
{
	gridId: string;
	#counters: Map = new Map();
	onElementCreatedHandler: any = null;

	constructor(options: {
		gridId: string,
		errors: Array<{ message: string }>,
	})
	{
		if (Type.isPlainObject(options))
		{
			this.gridId = options.gridId;

			if (Type.isArray(options.errors))
			{
				this.showErrors(options.errors);
			}
		}

		this.init();
	}

	init(): void
	{
		BX.UI.Hint.init(document);

		if (this.getGrid())
		{
			BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(this.getGrid());
		}

		EventEmitter.subscribe('Grid::updated', this.#onAfterGridUpdated.bind(this));
		this.subscribeCustomEvents();
	}

	subscribeCustomEvents()
	{
		if (this.onElementCreatedHandler === null)
		{
			this.onElementCreatedHandler = this.onElementCreated.bind(this);
			EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onElementCreatedHandler);
		}
	}

	unsubscribeCustomEvents()
	{
		if (this.onElementCreatedHandler)
		{
			EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.onElementCreatedHandler);
			this.onElementCreatedHandler = null;
		}
	}

	destroy()
	{
		this.unsubscribeCustomEvents();
	}

	static changePin(iblockId: number, gridId: string, event: Event): void
	{
		const eventData = event.getData();
		const button = eventData.button;

		if (Dom.hasClass(button, BX.Grid.CellActionState.ACTIVE))
		{
			BX.Bizproc.Component.UserProcessesStart.#action('unpin', iblockId, gridId);
			Dom.removeClass(button, BX.Grid.CellActionState.ACTIVE);
		}
		else
		{
			BX.Bizproc.Component.UserProcessesStart.#action('pin', iblockId, gridId);
			Dom.addClass(button, BX.Grid.CellActionState.ACTIVE);
		}

		const grid = BX.Main.gridManager.getInstanceById(gridId);
		if (grid)
		{
			BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(grid);
		}
	}

	static #action(action: string, iblockId: number, gridId: string): void
	{
		const component = 'bitrix:bizproc.user.processes.start';

		ajax.runComponentAction(component, action, {
			mode: 'class',
			data: {
				iblockId,
			},
		}).then(
			(response) => {
				const grid = BX.Main.gridManager.getInstanceById(gridId);
				if (grid)
				{
					grid.reload();
				}
			},
		).catch(() => {});
	}

	static colorPinnedRows(grid) {
		grid.getRows().getRows().forEach((row) => {
			const node = row.getNode();
			if (Type.isElementNode(node.querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active')))
			{
				Dom.addClass(node, 'bizproc-user-processes-start-item-pinned');
			}
			else
			{
				Dom.removeClass(node, 'bizproc-user-processes-start-item-pinned');
			}
		});
	}

	#onAfterGridUpdated()
	{
		if (this.getGrid())
		{
			BX.UI.Hint.init(this.getGrid().getContainer());
			BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(this.getGrid());
		}

		this.#counters.forEach((value, key) => {
			const counter = document.querySelector(`[data-role="iblock-${key}-counter"]`);
			if (Type.isElementNode(counter))
			{
				Dom.clean(counter);
				Dom.append(this.#renderStartedByMeNow(key), counter);
			}
		});
	}

	#renderStartedByMeNow(iblockId: number): HTMLElement
	{
		let message = Text.encode(Loc.getMessage(
			'BIZPROC_USER_PROCESSES_START_COUNTER',
			{
				'#COUNTER#': this.#counters.get(iblockId),
			},
		));

		message = message.replace('[bold]', '<span class="bizproc-user-processes-start-column-start-counter">');
		message = message.replace('[/bold]', '</span>');

		return Tag.render`<div class="ui-typography-text-xs">${message}</div>`;
	}

	startWorkflow(event: PointerEvent, iBlockTypeId: string, iblockId: number, iBlockName: string)
	{
		event.preventDefault();

		Runtime.loadExtension('lists.element.creation-guide')
			.then(({ CreationGuide }) => {
				CreationGuide?.open({
					iBlockTypeId,
					iBlockId: iblockId,
					analyticsP1: iBlockName,
				});
			})
			.catch(() => {})
			.finally(() => {});
	}

	onElementCreated(event): void
	{
		const [sliderEvent] = event.getCompatData();

		if (sliderEvent.getEventId() === 'BX.Lists.Element.CreationGuide:onElementCreated')
		{
			const eventArgs = sliderEvent.getData();
			if (!this.#counters.has(eventArgs.iBlockId))
			{
				this.#counters.set(eventArgs.iBlockId, 0);
			}
			this.#counters.set(eventArgs.iBlockId, this.#counters.get(eventArgs.iBlockId) + 1);
			this.reloadGrid();
			BX.SidePanel.Instance.getSliderByWindow(window).close();
		}
		else
		{
			this.reloadGrid();
		}
	}

	showErrors(errors: Array<{ message: string }>): void
	{
		if (!Type.isArrayFilled(errors))
		{
			if (!Type.isArray(errors))
			{
				console.error(errors);
			}

			return;
		}

		const errorsContainer = document.getElementById('bp-user-processes-errors-container');

		if (errorsContainer)
		{
			let errorCounter = 0;
			const fixStyles = () => {
				if (errorCounter > 0)
				{
					Dom.style(errorsContainer, { margin: '10px' });
				}
				else
				{
					Dom.style(errorsContainer, { margin: '0px' });
				}
			};

			for (const error of errors)
			{
				errorCounter += 1;

				const alert = new Alert({
					text: Text.encode(error.message),
					color: AlertColor.DANGER,
					closeBtn: true,
					animated: true,
				});

				alert.renderTo(errorsContainer);

				if (alert.getCloseBtn())
				{
					// eslint-disable-next-line no-loop-func
					alert.getCloseBtn().onclick = () => {
						errorCounter -= 1;
						fixStyles();
					};
				}
			}

			fixStyles();
		}
	}

	reloadGrid(): void
	{
		this.getGrid()?.reload();
	}

	getGrid(): ?BX.Main.grid
	{
		if (this.gridId)
		{
			return BX.Main.gridManager?.getInstanceById(this.gridId);
		}

		return null;
	}

	editTemplate(event: Event, bizprocEditorUrl: string, canEdit: boolean): void
	{
		if (!canEdit)
		{
			this.showNoPermissionsHint(event.target);

			return;
		}

		if (bizprocEditorUrl.length === 0)
		{
			this.showNoEditorHint(event.target);

			return;
		}

		top.window.location.href = bizprocEditorUrl;
	}

	editTemplateConstants(templateId: string, signedDocumentType: string): void
	{
		Router.openWorkflowChangeConstants({
			templateId,
			signedDocumentType,
		});
	}

	showAngleHint(node: Element, text: string): void
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

		this.popupHint.close = function(): void
		{
			this.hide();
		};
		this.popupHint.show(node, text);
		this.timeout = setTimeout(this.hideHint.bind(this), 5000);
	}

	hideHint(): void
	{
		if (this.popupHint)
		{
			this.popupHint.close();
		}
		this.popupHint = null;
	}

	showNoPermissionsHint(node: Element): void
	{
		this.showAngleHint(node, Loc.getMessage('BIZPROC_USER_PROCESSES_START_TEMPLATE_RIGHTS_ERROR'));
	}

	showNoEditorHint(node: Element): void
	{
		this.showAngleHint(node, Loc.getMessage('BIZPROC_USER_PROCESSES_START_TEMPLATE_MODULE_ERROR'));
	}
}
