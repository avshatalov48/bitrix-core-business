import {Tag, Type, Runtime, Dom, Text, Event, Loc} from "main.core";
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Popup, PopupWindowButton, PopupWindowButtonLink} from "main.popup";
import type {menuOptions, PopupOptions, RowOptions} from "./menu-options";

export class Menu extends EventEmitter
{
	#popup: Popup;
	#popupOptions: PopupOptions;
	#contentData: {
		rows: Array<RowOptions & {
			targetNode?: HTMLElement,
			inputNode?: HTMLElement,
			labelNode?: HTMLElement,
		}>,
		values: Object<number, string>,
	};

	constructor(options: menuOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Activity.SetGlobalVariable.Menu');

		this.#popupOptions = {};

		if (Type.isPlainObject(options.popupOptions))
		{
			this.#popupOptions = Runtime.clone(options.popupOptions);
			this.#popupOptions.target = options.popupOptions.target;

			if (Type.isNil(this.#popupOptions.autoHide))
			{
				this.#popupOptions.autoHide = true;
			}
			if (Type.isNil(this.#popupOptions.closeByEsc))
			{
				this.#popupOptions.closeByEsc = true;
			}
			if (Type.isNil(this.#popupOptions.cacheable))
			{
				this.#popupOptions.cacheable = true;
			}
			if (!Type.isArray(this.#popupOptions.buttons))
			{
				this.#popupOptions.buttons = this.#createDefaultButtons();
			}
		}

		if (Type.isPlainObject(options.contentData)) {
			this.#contentData = Runtime.clone(options.contentData);
			if (!Type.isArrayFilled(this.#contentData.rows))
			{
				this.#contentData.rows = [];
			}
			this.#contentData.values = {};
		}

		if (Type.isPlainObject(options.events))
		{
			this.subscribeFromOptions(options.events);
		}
	}

	get target(): Element
	{
		return this.#popupOptions.target;
	}

	create(): this
	{
		if (Type.isNil(this.#popup) && Object.keys(this.#popupOptions).length > 0)
		{
			this.#popup = new Popup({
				id: this.#popupOptions.id,
				bindElement: this.#popupOptions.target,
				className: 'bizproc-automation-popup-set',
				autoHide: this.#popupOptions.autoHide,
				closeByEsc: this.#popupOptions.closeByEsc,
				offsetLeft: this.#popupOptions.offsetLeft,
				offsetTop: this.#popupOptions.offsetTop,
				overlay: this.#popupOptions.overlay,
				content: this.#createContent(),
				buttons: this.#popupOptions.buttons,
				events: this.#popupOptions.events,
			});
		}

		return this;
	}

	#createContent(): HTMLFormElement
	{
		const content = Tag.render`<form class="bizproc-automation-popup-select-block"></form>`;
		for (const index in this.#contentData.rows)
		{
			const row = this.#contentData.rows[index];
			let valueNode = '';

			if ((row.onClick))
			{
				valueNode = Tag.render`
					<div class="bizproc-automation-popup-settings-dropdown" readonly="readonly">
						${Text.encode(row.values[0]?.text || '')}
					</div>
				`;
			}
			else
			{
				valueNode = Tag.render`<select class="bizproc-automation-popup-settings-dropdown"></select>`;
				if (Type.isArrayFilled(row.values))
				{
					row.values.forEach(({id, text}) => {
						Dom.append(
							Tag.render`<option value="${Text.encode(id)}">${Text.encode(text)}</option>`,
							valueNode
						);
					});

					this.setRowValue(0, row.values[0].id);
				}

				Event.bind(
					valueNode,
					'change',
					(event) => {
						this.setRowValue(Text.toInteger(index), event.target.value)
					}
				);
			}

			Event.bind(valueNode, 'click', this.#onRowClick.bind(this, Text.toInteger(index)));

			const labelNode = Tag.render`
				<div class="bizproc-automation-robot-settings-title">
					${Text.encode(row.label ?? '')}
				</div>
			`;

			row.targetNode = valueNode;
			row.inputNode = valueNode;
			row.labelNode = labelNode;

			Dom.append(
				Tag.render`
					<div class="bizproc-automation-popup-settings">
					${labelNode}
					${valueNode}
				</div>
				`,
				content
			);
		}

		return content;
	}

	createEmptyRow(index: number): HTMLElement
	{
		const node = Tag.render`<div class="bizproc-automation-popup-settings-dropdown" readonly="readonly"></div>`;
		Event.bind(node, 'click', this.#onRowClick.bind(this, Text.toInteger(index)));

		return node;
	}

	#onRowClick(rowIndex: number)
	{
		if (Type.isFunction(this.#contentData.rows[rowIndex]?.onClick))
		{
			const event = new BaseEvent({data: {menu: this}});
			event.setTarget(this.#contentData.rows[rowIndex].targetNode);
			this.#contentData.rows[rowIndex].onClick.call(this, event);
		}
	}

	#createDefaultButtons(): []
	{
		return [
			new PopupWindowButton({
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
				className: 'webform-button webform-button-create',
				events: {
					click: function ()
					{
						const event = new BaseEvent({
							data: {
								menu: this,
								values: this.#contentData.values,
								target: this.#popupOptions.target,
							}
						});
						this.emit('onApplyChangesClick', event);
						this.close();
					}.bind(this),
				},
			}),
			new PopupWindowButtonLink({
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CANCEL'),
				className: 'popup-window-button-link',
				events: {
					click: function ()
					{
						this.emit('onDiscardChangesClick', new BaseEvent({}));
						this.close();
					}.bind(this),
				},
			}),
		];
	}

	show()
	{
		if (Type.isNil(this.#popup))
		{
			this.create();
			if (!this.#popup)
			{
				return;
			}
		}

		if (this.#popup.isShown())
		{
			return;
		}

		this.#popup.show();
	}

	close()
	{
		if (Type.isNil(this.#popup))
		{
			return;
		}

		if (this.#popup.isShown())
		{
			this.#popup.close();
		}
	}

	destroy()
	{
		this.#contentData.values = {};
		this.#contentData.rows.forEach((row) => {
			delete row.targetNode;
			delete row.inputNode;
			delete row.labelNode;
		});

		if (!this.#popup)
		{
			return;
		}

		if (!this.#popup.isDestroyed())
		{
			this.#popup.destroy();
		}
		this.#popup = null;
	}

	getRowValue(rowIndex: number): ?string
	{
		return this.#contentData.values[rowIndex] ?? null;
	}

	setRowValue(rowIndex: number, value: string, text?: string)
	{
		if (
			Type.isNumber(rowIndex)
			&& rowIndex < this.#contentData.rows.length
			&& Type.isString(value)
		)
		{
			this.#contentData.values[rowIndex] = value;
			if (this.#contentData.rows[rowIndex].inputNode)
			{
				this.#contentData.rows[rowIndex].inputNode.value = value; // ?
				if (Type.isStringFilled(text))
				{
					this.#contentData.rows[rowIndex].inputNode.innerText = Text.encode(text);
				}
			}

			this.emit(
				'onSetRowValue',
				new BaseEvent({
					data: {
						value,
						rowIndex: Text.toInteger(rowIndex),
						menu: this,
					},
				})
			);
		}
	}

	getRowTarget(rowIndex: number): ?HTMLElement
	{
		return this.#contentData.rows[rowIndex]?.targetNode ?? null;
	}

	getRowInput(rowIndex: number): ?HTMLElement
	{
		return this.#contentData.rows[rowIndex]?.inputNode ?? null;
	}

	replaceRowTarget(rowIndex: number, target: HTMLElement, input: any)
	{
		if (
			Type.isNumber(rowIndex)
			&& rowIndex < this.#contentData.rows.length
		)
		{
			if (
				Type.isElementNode(this.#contentData.rows[rowIndex].targetNode)
				&& Type.isElementNode(target)
			)
			{
				Dom.replace(this.#contentData.rows[rowIndex].targetNode, target);

				this.#contentData.rows[rowIndex].targetNode = target;
				this.#contentData.rows[rowIndex].inputNode = input;
			}
		}
	}

	setRowLabel(rowIndex: number, label: string)
	{
		if (
			Type.isNumber(rowIndex)
			&& rowIndex < this.#contentData.rows.length
			&& Type.isStringFilled(label)
			&& Type.isElementNode(this.#contentData.rows[rowIndex]?.labelNode)
		)
		{
			this.#contentData.rows[rowIndex].labelNode.innerText = Text.encode(label);
		}
	}
}