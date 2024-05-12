import { Type, Loc, Event, Dom, Text, Tag } from 'main.core';
import { Helper } from './helper';
import { DelayInterval } from './delay-interval';
import { InlineTimeSelector } from './selectors/inline-time-selector';
import { MenuManager, Popup, MenuItem } from 'main.popup';
import { getGlobalContext } from './automation';
import { Button } from 'ui.buttons';
import { DateTimeFormat } from 'main.date';

import 'ui.forms';
import 'ui.icon-set.actions';
import 'ui.hint';

export class DelayIntervalSelector
{
	basisFields: Array;
	onchange: () => void;
	labelNode;
	useAfterBasis;
	minLimitM;
	showWaitWorkDay;

	delay;

	constructor(options)
	{
		this.basisFields = [];
		this.onchange = null;

		if (Type.isPlainObject(options))
		{
			this.labelNode = options.labelNode;
			this.useAfterBasis = options.useAfterBasis;

			if (Type.isArray(options.basisFields))
			{
				this.basisFields = options.basisFields;
			}
			this.onchange = options.onchange;
			this.minLimitM = options.minLimitM;
			this.showWaitWorkDay = options.showWaitWorkDay;
		}
	}

	init(delay)
	{
		this.delay = delay;
		this.setLabelText();
		this.bindLabelNode();
		this.prepareBasisFields();
	}

	setLabelText()
	{
		if (this.delay && this.labelNode)
		{
			this.labelNode.textContent = this.delay.format(
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE_2'),
				this.basisFields,
			);
		}
	}

	bindLabelNode()
	{
		if (this.labelNode)
		{
			Event.bind(this.labelNode, 'click', this.onLabelClick.bind(this));
		}
	}

	onLabelClick(event)
	{
		this.showDelayIntervalPopup();
		event.preventDefault();
	}

	showDelayIntervalPopup()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const { root: form, workTimeCheckBox } = Tag.render`
			 <form class="bizproc-automation-popup-select-block">
				${this.#createNowControlNode(uid)}
				${this.createAfterControlNode()}
				${this.basisFields.length > 0 ? this.createBeforeControlNode() : ''}
				${this.basisFields.length > 0 ? this.createInControlNode() : ''}
				<div class="bizproc-automation-popup-settings__subtitle ui-typography-heading-h6">
					${Loc.getMessage('BIZPROC_JS_AUTOMATION_DELAY_INTERVAL_ADDITIONAL_SETTINGS')}
				</div>
				<div class="bizproc-automation-popup-settings__checkbox-label">
					<input
						ref="workTimeCheckBox"
						class="bizproc-automation-popup-settings__checkbox"
						type="checkbox"
						id="${uid}worktime"
						name="worktime"
						value="1"
						style="vertical-align: middle"
					/>
					<label for="${uid}worktime" class="bizproc-automation-popup-settings-lbl">
						${Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_MSGVER_1')}
					</label>
					<span 
						class="bizproc-automation-status-help bizproc-automation-status-help-right"
						data-hint="${Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_HELP')}"
					></span>
				</div>
				${this.showWaitWorkDay ? this.#createWaitWorkDayNode() : ''}
			</form>
		`;
		if (delay.workTime)
		{
			Dom.attr(workTimeCheckBox, 'checked', 'checked');
		}
		BX.UI.Hint.init(form);

		const popup = new Popup({
			id: Helper.generateUniqueId(),
			bindElement: this.labelNode,
			content: form,
			closeByEsc: true,
			buttons: [
				new Button({
					color: Button.Color.PRIMARY,
					text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CHOOSE_BUTTON_CAPS'),
					onclick: () => {
						this.saveFormData(new FormData(form));
						popup.close();
					},
				}),
				new Button({
					color: Button.Color.LINK,
					text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CANCEL_BUTTON_CAPS'),
					onclick: () => {
						popup.close();
					},
				}),
			],
			width: 482,
			padding: 20,
			closeIcon: false,
			autoHide: true,
			events: {
				onPopupClose: () => {
					if (this.fieldsMenu)
					{
						this.fieldsMenu.popupWindow.close();
					}

					if (this.valueTypeMenu)
					{
						this.valueTypeMenu.popupWindow.close();
					}

					popup.destroy();
				},
			},
			titleBar: false,
			angle: {
				offset: 40,
			},
			overlay: { backgroundColor: 'transparent' },
		});
		popup.show();
	}

	#createNowControlNode(uid: string): HTMLDivElement
	{
		const labelText = Loc.getMessage(
			this.useAfterBasis
				? 'BIZPROC_AUTOMATION_CMP_BASIS_NOW'
				: 'BIZPROC_AUTOMATION_CMP_AT_ONCE_2',
		);

		const hintText = Loc.getMessage(
			this.useAfterBasis
				? 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP_2'
				: 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP',
		);

		const { root, labelAfter, radioNow } = Tag.render`
			<div class="bizproc-automation-popup-select-item">
				<label
					ref="labelAfter"
					class="bizproc-automation-popup-select__wrapper --first ui-ctl ui-ctl-radio ui-ctl-w100"
					for="${uid}now"
					data-role="select-item"
				>
					<input 
						ref="radioNow"
						class="bizproc-automation-popup-select__input ui-ctl-element"
						id="${uid}now"
						type="radio"
						value="now"
						name="type"
					/>
					<span class="bizproc-automation-popup-settings__text --first">${labelText}</span>
					<span
						class="bizproc-automation-status__help"
						data-hint="${hintText}"
					></span>
				</label>
			</div>
		`;
		Event.bind(radioNow, 'change', this.#onChangeDelayIntervalType.bind(this, labelAfter));

		if (this.delay.isNow())
		{
			radioNow.setAttribute('checked', 'checked');
			Dom.addClass(labelAfter, '--active');
		}

		return root;
	}

	#onChangeDelayIntervalType(labelNode)
	{
		document.querySelectorAll('[data-role="select-item"]').forEach((node) => {
			Dom.removeClass(node, '--active');
		});
		Dom.addClass(labelNode, '--active');
	}

	saveFormData(formData: FormData)
	{
		this.#saveDelayIntervalTypeFromForm(formData);

		if (!this.delay.isNow())
		{
			const timeName = `basis_in_time_${Text.encode(this.delay.type)}`;
			this.delay.setInTime(this.#parseInTimeValue(formData.get(timeName)));
		}
		this.delay.setWorkTime(formData.get('worktime'));
		this.delay.setWaitWorkDay(formData.get('wait_workday'));
		this.setLabelText();

		if (this.onchange)
		{
			this.onchange(this.delay);
		}
	}

	#saveDelayIntervalTypeFromForm(formData: FormData)
	{
		const type = formData.get('type');

		if (type === 'now')
		{
			this.delay.setNow();
		}
		else if (type === DelayInterval.DELAY_TYPE.In)
		{
			this.delay.setType(DelayInterval.DELAY_TYPE.In);
			this.delay.setValue(0);
			this.delay.setValueType('i');
			this.delay.setBasis(formData.get('basis_in'));
		}
		else
		{
			this.delay.setType(type);
			this.delay.setValue(formData.get(`value_${type}`));
			this.delay.setValueType(formData.get(`value_type_${type}`));

			if (type === DelayInterval.DELAY_TYPE.After)
			{
				if (this.useAfterBasis)
				{
					this.delay.setBasis(formData.get('basis_after'));
				}
				else
				{
					this.delay.setBasis(DelayInterval.BASIS_TYPE.CurrentDateTime);
				}

				if (
					this.minLimitM > 0
					&& this.delay.basis === DelayInterval.BASIS_TYPE.CurrentDateTime
					&& this.delay.valueType === 'i'
					&& this.delay.value < this.minLimitM
				)
				{
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('BIZPROC_AUTOMATION_DELAY_MIN_LIMIT_LABEL'),
					});
					this.delay.setValue(this.minLimitM);
				}
			}
			else
			{
				this.delay.setBasis(formData.get('basis_before'));
			}
		}
	}

	#parseInTimeValue(value): ?[]
	{
		if (Type.isStringFilled(value))
		{
			const result: string = value.trim();

			if (/^\d{2}:\d{2}\s?[ap]?m?$/.test(result))
			{
				if (result.includes('am'))
				{
					return [
						String(Text.toInteger(result.slice(0, 2)) % 12).padStart(2, '0'),
						String(Text.toInteger(result.slice(3)) % 60).padStart(2, '0'),
					];
				}

				if (result.includes('pm'))
				{
					return [
						String((Text.toInteger(result.slice(0, 2)) % 12) + 12).padStart(2, '0'),
						String(Text.toInteger(result.slice(3)) % 60).padStart(2, '0'),
					];
				}

				return [
					String(Text.toInteger(result.slice(0, 2)) % 24).padStart(2, '0'),
					String(Text.toInteger(result.slice(3)) % 60).padStart(2, '0'),
				];
			}
		}

		return null;
	}

	createAfterControlNode(): HTMLElement
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const valueAfter = (
			delay.type === DelayInterval.DELAY_TYPE.After && delay.value
				? delay.value
				: (this.minLimitM || 5)
		);

		const hiddenRow = this.#createHiddenRow(DelayInterval.DELAY_TYPE.After, 'value_type_after');
		const chevron = this.#createShowHiddenRowChevron(hiddenRow, delay.valueType !== 'd', 'value_type_after');

		const { root, labelAfter, radioAfter } = Tag.render`
			<div class="bizproc-automation-popup-select-item">
				<label
					ref="labelAfter" 
					class="bizproc-automation-popup-select__wrapper ui-ctl ui-ctl-radio ui-ctl-w100"
					for="${uid}"
					data-role="select-item"
				>
					<div class="bizproc-automation-popup-select__visible-row">
						<input 
							ref="radioAfter"
							type="radio"
							id="${uid}"
							class="bizproc-automation-popup-select__input ui-ctl-element"
							value="${DelayInterval.DELAY_TYPE.After}"
							name="type"
						/>
						<span class="bizproc-automation-popup-settings__text --first">
							${Loc.getMessage('BIZPROC_AUTOMATION_CMP_THROUGH_3')}
						</span>
						<input
							type="text"
							name="value_after"
							class="bizproc-automation-popup-settings__input"
							value="${Text.encode(valueAfter)}"
						/>
						${this.createValueTypeSelector('value_type_after')}
						${this.#createAfterBasis()}
						${this.useAfterBasis ? chevron : ''}
					</div>
					${this.useAfterBasis ? hiddenRow : ''}
				</label>
			</div>
		`;
		Event.bind(radioAfter, 'change', this.#onChangeDelayIntervalType.bind(this, labelAfter));

		if (delay.type === DelayInterval.DELAY_TYPE.After && delay.value > 0)
		{
			radioAfter.setAttribute('checked', 'checked');
			Dom.addClass(labelAfter, '--active');

			if (delay.valueType === 'd' && this.delay.inTime)
			{
				Dom.addClass(hiddenRow, '--visible');
				Dom.addClass(chevron, '--active');
			}
		}

		return root;
	}

	#createHiddenRow(delayIntervalType: string, role: string): HTMLDivElement
	{
		return Tag.render`
			<div class="bizproc-automation-popup-select__hidden-row" data-role="hidden_row_${role}">
				${this.#createTimeSelector(delayIntervalType)}
			</div>
		`;
	}

	#createShowHiddenRowChevron(hiddenRow: HTMLElement, disabled: boolean, type: string): HTMLDivElement
	{
		const chevron = Tag.render`
			<div 
				class="ui-icon-set --chevron-down bizproc-automation-popup-select__chevron"
				data-role="chevron_${type}"
			></div>
		`;

		if (disabled)
		{
			this.#disableSetTimeRow(chevron, hiddenRow);
		}

		Event.bind(chevron, 'click', () => {
			if (Dom.hasClass(chevron, '--disabled'))
			{
				return;
			}

			Dom.toggleClass(chevron, '--active');
			Dom.toggleClass(hiddenRow, '--visible');
		});

		return chevron;
	}

	#disableSetTimeRow(chevron, hiddenRow)
	{
		Dom.removeClass(chevron, '--active');
		Dom.addClass(chevron, '--disabled');
		Dom.attr(chevron, {
			'data-hint-html': 'Y',
			'data-hint-no-icon': 'Y',
		});
		chevron.dataset.hint = Loc.getMessage('BIZPROC_JS_AUTOMATION_DELAY_INTERVAL_CHEVRON_DISABLED');
		Dom.removeClass(hiddenRow, '--visible');
		BX.UI.Hint.initNode(chevron);
	}

	#enableSetTimeRow(chevron, hiddenRow)
	{
		Dom.replace(
			chevron,
			this.#createShowHiddenRowChevron(
				hiddenRow,
				false,
				Dom.attr(chevron, 'data-role').replace('chevron_', ''),
			),
		);
	}

	#createAfterBasis()
	{
		if (!this.useAfterBasis)
		{
			return '';
		}

		const delay = this.delay;

		let basisField = this.getBasisField(delay.basis, true);
		let basisValue = delay.basis;
		if (!basisField)
		{
			basisField = this.getBasisField(DelayInterval.BASIS_TYPE.CurrentDateTime, true);
			basisValue = basisField.SystemExpression;
		}

		const beforeBasisNodeText = (
			basisField
				? Text.encode(basisField.Name)
				: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD')
		);

		const { root, beforeBasisValueNode, beforeBasisNode } = Tag.render`
			<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER')}
			</span>
			<input ref="beforeBasisValueNode" type="hidden" name="basis_after" value="${Text.encode(basisValue)}">
			<span class="bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis">
				<span ref="beforeBasisNode">
					${Text.encode(beforeBasisNodeText)}
				</span>
			</span>
		`;
		Event.bind(beforeBasisNode, 'click', (event) => {
			const callback = (field) => {
				beforeBasisNode.textContent = Text.encode(field.Name);
				beforeBasisValueNode.value = field.SystemExpression;
			};
			this.onBasisClick(event, beforeBasisNode, callback, DelayInterval.DELAY_TYPE.After);
		});

		return root;
	}

	createBeforeControlNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const valueBefore = (
			delay.type === DelayInterval.DELAY_TYPE.Before && delay.value
				? delay.value
				: (this.minLimitM || 5)
		);

		const hiddenRow = this.#createHiddenRow(DelayInterval.DELAY_TYPE.Before, 'value_type_before');
		const chevron = this.#createShowHiddenRowChevron(hiddenRow, delay.valueType !== 'd', 'value_type_before');

		const { root, labelBefore, radioBefore } = Tag.render`
			<div class="bizproc-automation-popup-select-item">
				<label
					ref="labelBefore"
					class="bizproc-automation-popup-select__wrapper ui-ctl ui-ctl-radio ui-ctl-w100"
					for="${uid}"
					data-role="select-item"
				>
					<div class="bizproc-automation-popup-select__visible-row"> 
						<input
							ref="radioBefore"
							type="radio"
							id="${uid}"
							class="bizproc-automation-popup-select__input ui-ctl-element"
							value="${DelayInterval.DELAY_TYPE.Before}"
							name="type"
						/>
						<span class="bizproc-automation-popup-settings__text --first">
							${Loc.getMessage('BIZPROC_AUTOMATION_CMP_FOR_TIME_3')}
						</span>
						<input
							type="text"
							name="value_before"
							class="bizproc-automation-popup-settings__input"
							value="${Text.encode(valueBefore)}"
						/>
						${this.createValueTypeSelector('value_type_before')}
						${this.#createBeforeBasis()}
						${chevron}
					</div>
					${hiddenRow}
				</label>
			</div>
		`;
		Event.bind(radioBefore, 'change', this.#onChangeDelayIntervalType.bind(this, labelBefore));

		if (delay.type === DelayInterval.DELAY_TYPE.Before)
		{
			radioBefore.setAttribute('checked', 'checked');
			Dom.addClass(labelBefore, '--active');

			if (delay.valueType === 'd' && this.delay.inTime)
			{
				Dom.addClass(hiddenRow, '--visible');
				Dom.addClass(chevron, '--active');
			}
		}

		return root;
	}

	#createBeforeBasis()
	{
		const delay = this.delay;

		let basisField = this.getBasisField(delay.basis);
		let basisValue = delay.basis;
		if (!basisField)
		{
			basisField = this.basisFields[0];
			basisValue = basisField.SystemExpression;
		}

		const { root, beforeBasisValueNode, beforeBasisNode } = Tag.render`
			<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_BEFORE_1')}
			</span>
			<input ref="beforeBasisValueNode" type="hidden" name="basis_before" value="${basisValue}">
			<span class="bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis">
				<span ref="beforeBasisNode">
					${basisField ? basisField.Name : Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD')}
				</span>
			</span>
		`;
		Event.bind(beforeBasisNode, 'click', (event) => {
			const callback = (field) => {
				beforeBasisNode.textContent = Text.encode(field.Name);
				beforeBasisValueNode.value = Text.encode(field.SystemExpression);
			};
			this.onBasisClick(event, beforeBasisNode, callback, DelayInterval.DELAY_TYPE.Before);
		});

		return root;
	}

	createInControlNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const hiddenRow = this.#createHiddenRow(DelayInterval.DELAY_TYPE.In, 'value_type_in');
		const chevron = this.#createShowHiddenRowChevron(hiddenRow, false, 'value_type_in');

		const { root, labelIn, radioIn } = Tag.render`
			<div class="bizproc-automation-popup-select-item">
				<label
					ref="labelIn"
					class="bizproc-automation-popup-select__wrapper --last ui-ctl ui-ctl-radio ui-ctl-w100"
					for="${uid}"
					data-role="select-item"
				>
					<div class="bizproc-automation-popup-select__visible-row">
						<input 
							ref="radioIn"
							class="bizproc-automation-popup-select__input ui-ctl-element" 
							id="${uid}" 
							type="radio" 
							value="${DelayInterval.DELAY_TYPE.In}" 
							name="type"
						>
						${this.#createInBasis()}
						${chevron}
					</div>
					${hiddenRow}
				</label>
			</div>
		`;
		Event.bind(radioIn, 'change', this.#onChangeDelayIntervalType.bind(this, labelIn));

		if (delay.type === DelayInterval.DELAY_TYPE.In)
		{
			radioIn.setAttribute('checked', 'checked');
			Dom.addClass(labelIn, '--active');

			if (this.delay.inTime)
			{
				Dom.addClass(hiddenRow, '--visible');
				Dom.addClass(chevron, '--active');
			}
		}

		return root;
	}

	#createInBasis()
	{
		const delay = this.delay;

		let basisField = this.getBasisField(delay.basis, true);
		let basisValue = delay.basis;
		if (!basisField)
		{
			basisField = this.basisFields[0];
			basisValue = basisField.SystemExpression;
		}

		const { root, inBasisValueNode, inBasisNode } = Tag.render`
			<span class="bizproc-automation-popup-settings__text --first">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_TIME_2')}
			</span>
			<input ref="inBasisValueNode" type="hidden" name="basis_in" value="${basisValue}"/>
			<span class="bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis">
				<span ref="inBasisNode">
					${basisField ? basisField.Name : Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD')}
				</span>
			</span>
		`;
		Event.bind(inBasisNode, 'click', (event) => {
			const callback = (field) => {
				inBasisNode.textContent = Text.encode(field.Name);
				inBasisValueNode.value = Text.encode(field.SystemExpression);
			};
			this.onBasisClick(event, inBasisNode, callback, DelayInterval.DELAY_TYPE.In);
		});

		return root;
	}

	#createTimeSelector(delayType: string): HTMLSpanElement
	{
		const value: [] = delayType === this.delay.type ? this.delay.inTime : [];
		const formattedValue = this.#formatTimeToString(value ?? []);

		const { root, input } = Tag.render`
			<div class="bizproc-automation-popup-settings__text">
				<span style="margin-right: 10px">
					${Loc.getMessage('BIZPROC_JS_AUTOMATION_DELAY_INTERVAL_SET_TIME_LABEL')}
				</span>
				<input
					ref="input"
					type="text"
					name="basis_in_time_${Text.encode(delayType)}"
					class="bizproc-automation-delay-interval-set-time bizproc-automation-popup-settings__input"
					autocomplete="off"
					value="${Text.encode(formattedValue)}"
				/>
			</div>
		`;

		(new InlineTimeSelector({ context: { fields: [] }, showValuesSelector: false })).renderTo(input);

		return root;
	}

	#formatTimeToString(time: []): string
	{
		const dateFormat = (
			BX.Main.Date.convertBitrixFormat(Loc.getMessage('FORMAT_DATE'))
				.replace(/:?\s*s/, '')
		);
		const timeFormat = (
			BX.Main.Date.convertBitrixFormat(Loc.getMessage('FORMAT_DATETIME'))
				.replace(`${dateFormat} `, '')
				.replace(':s', '')
		);

		const date = new Date();
		date.setHours(time[0] ?? 0, time[1] ?? 0, 0, 0);

		return Type.isArrayFilled(time) ? DateTimeFormat.format(timeFormat, date) : '';
	}

	createValueTypeSelector(name)
	{
		const delay = this.delay;
		const labelTexts = {
			i: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
			h: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
			d: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D'),
		};

		const { root, label, input } = Tag.render`
			<span>
				<label ref="label" class="bizproc-automation-popup-settings-link">
					${Text.encode(labelTexts[delay.valueType])}
				</label>
				<input ref="input" type="hidden" name="${Text.encode(name)}" value="${Text.encode(delay.valueType)}"/>
			</span>
		`;

		Event.bind(label, 'click', this.onValueTypeSelectorClick.bind(this, label, input));

		return root;
	}

	onValueTypeSelectorClick(label, input)
	{
		const uid = Helper.generateUniqueId();

		const handler = (event, item: MenuItem) => {
			item.getMenuWindow().close();
			// eslint-disable-next-line no-param-reassign
			input.value = item.valueId;
			// eslint-disable-next-line no-param-reassign
			label.textContent = item.text;

			if (item.valueId === 'd')
			{
				this.#enableSetTimeRow(
					document.querySelector(`[data-role="chevron_${input.name}"]`),
					document.querySelector(`[data-role="hidden_row_${input.name}"]`),
				);
			}
			else
			{
				this.#disableSetTimeRow(
					document.querySelector(`[data-role="chevron_${input.name}"]`),
					document.querySelector(`[data-role="hidden_row_${input.name}"]`),
				);
			}
		};

		const menuItems = [
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
				valueId: 'i',
				onclick: handler,
			},
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
				valueId: 'h',
				onclick: handler,
			},
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D'),
				valueId: 'd',
				onclick: handler,
			},
		];

		MenuManager.show(
			uid,
			label,
			menuItems,
			{
				autoHide: true,
				offsetLeft: 25,
				angle: { position: 'top' },
				events: {
					onPopupClose()
					{
						this.destroy();
					},
				},
				overlay: { backgroundColor: 'transparent' },
			},
		);

		this.valueTypeMenu = MenuManager.currentItem;
	}

	onBasisClick(event, labelNode, callback, delayType)
	{
		const menuItems = [];

		const onMenuItemClick = (e, item: MenuItem) => {
			if (callback)
			{
				callback(item.field || item.options.field);
			}

			item.getMenuWindow().close();
		};

		if (delayType === DelayInterval.DELAY_TYPE.After || delayType === DelayInterval.DELAY_TYPE.In)
		{
			menuItems.push(
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
					field: {
						Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
						SystemExpression: DelayInterval.BASIS_TYPE.CurrentDateTime,
					},
					onclick: onMenuItemClick,
				},
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
					field: {
						Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
						SystemExpression: DelayInterval.BASIS_TYPE.CurrentDate,
					},
					onclick: onMenuItemClick,
				},
				{
					delimiter: true,
				},
			);
		}

		for (let i = 0; i < this.basisFields.length; ++i)
		{
			if (
				delayType !== DelayInterval.DELAY_TYPE.After
				&& this.basisFields[i].Id.includes('DATE_CREATE')
			)
			{
				continue;
			}

			menuItems.push({
				text: Text.encode(this.basisFields[i].Name),
				field: this.basisFields[i],
				onclick: onMenuItemClick,
			});
		}

		let menuId = labelNode.getAttribute('data-menu-id');
		if (!menuId)
		{
			menuId = Helper.generateUniqueId();
			labelNode.setAttribute('data-menu-id', menuId);
		}

		MenuManager.show(
			menuId,
			labelNode,
			menuItems,
			{
				autoHide: true,
				offsetLeft: (Dom.getPosition(labelNode).width / 2),
				angle: { position: 'top', offset: 0 },
				overlay: { backgroundColor: 'transparent' },
			},
		);

		this.fieldsMenu = MenuManager.currentItem;
	}

	getBasisField(basis, system)
	{
		if (
			system
			&& (
				basis === DelayInterval.BASIS_TYPE.CurrentDateTime
				|| basis === DelayInterval.BASIS_TYPE.CurrentDateTimeLocal
			)
		)
		{
			return {
				Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
				SystemExpression: DelayInterval.BASIS_TYPE.CurrentDateTime,
			};
		}

		if (system && basis === DelayInterval.BASIS_TYPE.CurrentDate)
		{
			return {
				Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
				SystemExpression: DelayInterval.BASIS_TYPE.CurrentDate,
			};
		}

		let field = null;
		for (let i = 0; i < this.basisFields.length; ++i)
		{
			if (basis === this.basisFields[i].SystemExpression)
			{
				field = this.basisFields[i];
			}
		}

		return field;
	}

	prepareBasisFields()
	{
		const fields = [];
		for (let i = 0; i < this.basisFields.length; ++i)
		{
			const fld = this.basisFields[i];
			if (
				!fld.Id.includes('DATE_MODIFY')
				&& !fld.Id.includes('EVENT_DATE')
				&& !fld.Id.includes('BIRTHDATE')
			)
			{
				fields.push(fld);
			}
		}

		this.basisFields = fields;
	}

	#createWaitWorkDayNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();
		const isAvailable = this.#isWorkTimeAvailable();

		const { root, workDayCheckbox } = Tag.render`
			<div class="bizproc-automation-popup-select-item">
				<div class="bizproc-automation-popup-settings__checkbox-label">
					<input
						ref="workDayCheckbox"
						class="bizproc-automation-popup-settings__checkbox"
						type="checkbox"
						id="${`${uid}wait_workday`}"
						name="wait_workday"
						value="1"
						style="vertical-align: middle"
					/>
					<label
						class="bizproc-automation-popup-settings-lbl ${isAvailable ? '' : 'bizproc-automation-robot-btn-set-locked'}"
						for="${`${uid}wait_workday`}"
					>${Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_MSGVER_1')}</label>
					<span
						class="bizproc-automation-status-help bizproc-automation-status-help-right"
						data-hint="${Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_HELP')}"
					></span>
				</div>
			</div>
		`;
		if (delay.waitWorkDay && isAvailable)
		{
			Dom.attr(workDayCheckbox, 'checked', 'checked');
		}

		if (!isAvailable)
		{
			Event.bind(root, 'click', () => {
				if (top.BX.UI && top.BX.UI.InfoHelper)
				{
					top.BX.UI.InfoHelper.show('limit_office_worktime_responsible');
				}
			});
			workDayCheckbox.disabled = true;
		}

		return root;
	}

	#isWorkTimeAvailable(): boolean
	{
		return getGlobalContext().get('IS_WORKTIME_AVAILABLE') ?? false;
	}
}
