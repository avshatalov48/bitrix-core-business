import { Type, Loc, Event, Dom, Text, Tag } from 'main.core';
import { Helper } from './helper';
import { DelayInterval } from './delay-interval';
import { MenuManager } from 'main.popup';
import { getGlobalContext } from "./automation";

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
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE'),
				this.basisFields
			);
		}
	}

	bindLabelNode()
	{
		if (this.labelNode)
		{
			Event.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
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

		const form = Dom.create("form", {
			attrs: { className: "bizproc-automation-popup-select-block" }
		});

		const radioNow = Dom.create("input", {
			attrs: {
				className: "bizproc-automation-popup-select-input",
				id: uid + "now",
				type: "radio",
				value: 'now',
				name: "type"
			}
		});
		if (delay.isNow())
		{
			radioNow.setAttribute('checked', 'checked');
		}

		const labelNow = Dom.create("label", {
			attrs: {
				className: "bizproc-automation-popup-select-wrapper",
				for: uid + "now"
			},
			children: [
				Dom.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title'},
					text: Loc.getMessage(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_BASIS_NOW' : 'BIZPROC_AUTOMATION_CMP_AT_ONCE_2')
				})
			]
		});

		const labelNowHelpNode = Dom.create('span', {
			attrs: {
				className: "bizproc-automation-status-help bizproc-automation-status-help-right",
				'data-hint': Loc.getMessage(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP_2' : 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP')
			}
		});
		labelNow.appendChild(labelNowHelpNode);

		form.appendChild(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-select-item" },
			children: [radioNow, labelNow]
		}));

		form.appendChild(this.createAfterControlNode());

		if (this.basisFields.length > 0)
		{
			form.appendChild(this.createBeforeControlNode());
			form.appendChild(this.createInControlNode());
		}

		const workTimeRadio = Dom.create("input", {
			attrs: {
				type: "checkbox",
				id: uid + "worktime",
				name: "worktime",
				value: '1',
				style: 'vertical-align: middle'
			},
			props: {
				checked: delay.workTime
			}
		});

		const workTimeHelpNode = Dom.create('span', {
			attrs: {
				className: "bizproc-automation-status-help bizproc-automation-status-help-right",
				'data-hint': Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_HELP')
			}
		});

		form.appendChild(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings-title" },
			children: [
				workTimeRadio,
				Dom.create("label", {
					attrs: {
						className: "bizproc-automation-popup-settings-lbl",
						for: uid + "worktime"
					},
					text: Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_MSGVER_1')
				}),
				workTimeHelpNode
			]
		}));

		if (this.showWaitWorkDay)
		{
			form.appendChild(this.#createWaitWorkDayNode());
		}

		const self = this;
		//init modern Help tips
		BX.UI.Hint.init(form);
		const popup = new BX.PopupWindow(Helper.generateUniqueId(), this.labelNode, {
			autoHide: true,
			closeByEsc: true,
			closeIcon: false,
			titleBar: false,
			angle: true,
			offsetLeft: 20,
			content: form,
			buttons: [
				new BX.PopupWindowButton({
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
					className: 'webform-button webform-button-create bizproc-automation-button-left',
					events: {
						click()
						{
							self.saveFormData(new FormData(form));
							this.popupWindow.close();
						}}
				})
			],
			events: {
				onPopupClose()
				{
					if (self.fieldsMenu)
					{
						self.fieldsMenu.popupWindow.close();
					}
					if (self.valueTypeMenu)
					{
						self.valueTypeMenu.popupWindow.close();
					}
					this.destroy();
				}
			},
			overlay: { backgroundColor: 'transparent' }
		});

		popup.show();
	}

	saveFormData(formData: FormData)
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
			this.delay.setInTime(formData.get('basis_in_time') ? formData.get('basis_in_time').split(':') : null);
		}
		else
		{
			this.delay.setType(type);
			this.delay.setValue(formData.get('value_' + type));
			this.delay.setValueType(formData.get('value_type_' + type));

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
						content: Loc.getMessage('BIZPROC_AUTOMATION_DELAY_MIN_LIMIT_LABEL')
					});
					this.delay.setValue(this.minLimitM);
				}
			}
			else
			{
				this.delay.setBasis(formData.get('basis_before'));
			}
		}

		this.delay.setWorkTime(formData.get('worktime'));
		this.delay.setWaitWorkDay(formData.get('wait_workday'));
		this.setLabelText();

		if (this.onchange)
		{
			this.onchange(this.delay);
		}
	}

	createAfterControlNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const radioAfter = Dom.create("input", {
			attrs: {
				className: "bizproc-automation-popup-select-input",
				id: uid,
				type: "radio",
				value: DelayInterval.DELAY_TYPE.After,
				name: "type"
			}
		});
		if (delay.type === DelayInterval.DELAY_TYPE.After && delay.value > 0)
		{
			radioAfter.setAttribute('checked', 'checked');
		}

		const valueNode = Dom.create('input', {
			attrs: {
				type: 'text',
				name: 'value_after',

				className: 'bizproc-automation-popup-settings-input'
			},
			props: {
				value: delay.type === DelayInterval.DELAY_TYPE.After && delay.value ? delay.value : (this.minLimitM || 5)
			}
		});

		const labelAfter = Dom.create("label", {
			attrs: {
				className: "bizproc-automation-popup-select-wrapper",
				for: uid
			},
			children: [
				Dom.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title'},
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_THROUGH_3')
				}),
				valueNode,
				this.createValueTypeSelector('value_type_after')
			]
		});

		if (this.useAfterBasis)
		{
			labelAfter.appendChild(Dom.create('span', {
				attrs: {className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'},
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER')
			}));

			let basisField = this.getBasisField(delay.basis, true);
			let basisValue = delay.basis;
			if (!basisField)
			{
				basisField = this.getBasisField(DelayInterval.BASIS_TYPE.CurrentDateTime, true);
				basisValue = basisField.SystemExpression;
			}

			const beforeBasisValueNode = Dom.create('input', {
				attrs: {
					type: "hidden",
					name: "basis_after",
					value: basisValue
				}
			});

			const self = this;
			const beforeBasisNode = Dom.create('span', {
				attrs: {
					className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
				},
				text: basisField ? basisField.Name : Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
				events: {
					click(event)
					{
						self.onBasisClick(event, this, function(field)
						{
							beforeBasisNode.textContent = field.Name;
							beforeBasisValueNode.value = field.SystemExpression;
						}, DelayInterval.DELAY_TYPE.After);
					}
				}
			});
			labelAfter.appendChild(beforeBasisValueNode);
			labelAfter.appendChild(beforeBasisNode);
		}

		if (!this.useAfterBasis)
		{
			const afterHelpNode = Dom.create('span', {
				attrs: {
					className: "bizproc-automation-status-help bizproc-automation-status-help-right",
					'data-hint': Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_AFTER_HELP')
				}
			});
			labelAfter.appendChild(afterHelpNode);
		}

		return Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-select-item" },
			children: [radioAfter, labelAfter]
		});
	}

	createBeforeControlNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const radioBefore = Dom.create("input", {
			attrs: {
				className: "bizproc-automation-popup-select-input",
				id: uid,
				type: "radio",
				value: DelayInterval.DELAY_TYPE.Before,
				name: "type"
			}
		});

		if (delay.type === DelayInterval.DELAY_TYPE.Before)
		{
			radioBefore.setAttribute('checked', 'checked');
		}

		const valueNode = Dom.create('input', {
			attrs: {
				type: 'text',
				name: 'value_before',

				className: 'bizproc-automation-popup-settings-input'
			},
			props: {
				value: delay.type === DelayInterval.DELAY_TYPE.Before && delay.value ? delay.value : (this.minLimitM || 5)
			}
		});

		const labelBefore = Dom.create("label", {
			attrs: {
				className: "bizproc-automation-popup-select-wrapper",
				for: uid
			},
			children: [
				Dom.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title'},
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_FOR_TIME_3')
				}),
				valueNode,
				this.createValueTypeSelector('value_type_before'),
				Dom.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'},
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BEFORE_1')
				})
			]
		});

		let basisField = this.getBasisField(delay.basis);
		let basisValue = delay.basis;
		if (!basisField)
		{
			basisField = this.basisFields[0];
			basisValue = basisField.SystemExpression;
		}

		const beforeBasisValueNode = Dom.create('input', {
			attrs: {
				type: "hidden",
				name: "basis_before",
				value: basisValue
			}
		});

		const self = this;
		const beforeBasisNode = Dom.create('span', {
			attrs: {
				className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
			},
			text: basisField ? basisField.Name : Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
			events: {
				click(event)
				{
					self.onBasisClick(
						event,
						this,
						(field) => {
							beforeBasisNode.textContent = field.Name;
							beforeBasisValueNode.value = field.SystemExpression;
						},
						DelayInterval.DELAY_TYPE.Before
					);
				}
			}
		});
		labelBefore.appendChild(beforeBasisValueNode);
		labelBefore.appendChild(beforeBasisNode);

		if (!this.useAfterBasis)
		{
			const beforeHelpNode = Dom.create('span', {
				attrs: {
					className: "bizproc-automation-status-help bizproc-automation-status-help-right",
					'data-hint': Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_BEFORE_HELP')
				}
			});
			labelBefore.appendChild(beforeHelpNode);
		}

		return Dom.create("div", {
			attrs: {className: "bizproc-automation-popup-select-item"},
			children: [radioBefore, labelBefore]
		});
	}

	createInControlNode()
	{
		const delay = this.delay;
		const uid = Helper.generateUniqueId();

		const radioIn = Dom.create("input", {
			attrs: {
				className: "bizproc-automation-popup-select-input",
				id: uid,
				type: "radio",
				value: DelayInterval.DELAY_TYPE.In,
				name: "type"
			}
		});

		if (delay.type === DelayInterval.DELAY_TYPE.In)
		{
			radioIn.setAttribute('checked', 'checked');
		}

		const labelIn = Dom.create("label", {
			attrs: {
				className: "bizproc-automation-popup-select-wrapper",
				for: uid
			},
			children: [
				Dom.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title'},
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_TIME_2')
				})
			]
		});

		let basisField = this.getBasisField(delay.basis, true);
		let basisValue = delay.basis;
		if (!basisField)
		{
			basisField = this.basisFields[0];
			basisValue = basisField.SystemExpression;
		}

		const inBasisValueNode = Dom.create('input', {
			attrs: {
				type: "hidden",
				name: "basis_in",
				value: basisValue
			}
		});

		const self = this;
		const inBasisNode = Dom.create('span', {
			attrs: {
				className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
			},
			text: basisField ? basisField.Name : Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
			events: {
				click(event)
				{
					self.onBasisClick(
						event,
						this,
						field => {
							inBasisNode.textContent = field.Name;
							inBasisValueNode.value = field.SystemExpression;
						},
						DelayInterval.DELAY_TYPE.In
					);
				},
			}
		});
		labelIn.appendChild(inBasisValueNode);
		labelIn.appendChild(inBasisNode);
		if (!this.useAfterBasis)
		{
			const helpNode = Dom.create('span', {
				attrs: {
					className: "bizproc-automation-status-help bizproc-automation-status-help-right",
					'data-hint': Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_IN_HELP')
				}
			});
			labelIn.appendChild(helpNode);
		}

		const inTime = Tag.render`
			 <span>
			 	Time: <input type="time" value="${delay.inTimeString}" name="basis_in_time"/>
			</span>
		`;

		// Dom.append(inTime, labelIn); // TODO interface

		return Dom.create("div", {
			attrs: {className: "bizproc-automation-popup-select-item"},
			children: [radioIn, labelIn]
		});
	}

	createValueTypeSelector(name)
	{
		const delay = this.delay;
		const labelTexts = {
			i: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
			h: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
			d: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D')
		};

		const label = Dom.create('label', {
			attrs: {className: 'bizproc-automation-popup-settings-link'},
			text: labelTexts[delay.valueType],
		});

		const input = Dom.create('input', {
			attrs: {
				type: 'hidden',
				name: name
			},
			props: {
				value: delay.valueType
			}
		});

		Event.bind(label, 'click', this.onValueTypeSelectorClick.bind(this, label, input));

		return Dom.create('span', {
			children: [label, input]
		});
	}

	onValueTypeSelectorClick(label, input)
	{
		const uid = Helper.generateUniqueId();

		const handler = function(event, item)
		{
			this.popupWindow.close();
			input.value = item.valueId;
			label.textContent = item.text;
		};

		const menuItems = [
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
				valueId: 'i',
				onclick: handler
			},
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
				valueId: 'h',
				onclick: handler
			},
			{
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D'),
				valueId: 'd',
				onclick: handler
			}
		];

		MenuManager.show(
			uid,
			label,
			menuItems,
			{
				autoHide: true,
				offsetLeft: 25,
				angle: { position: 'top'},
				events: {
					onPopupClose()
					{
						this.destroy();
					}
				},
				overlay: { backgroundColor: 'transparent' }
			}
		);

		this.valueTypeMenu = MenuManager.currentItem;
	}

	onBasisClick(event, labelNode, callback, delayType)
	{
		const menuItems = [];

		if (delayType === DelayInterval.DELAY_TYPE.After || delayType === DelayInterval.DELAY_TYPE.In)
		{
			menuItems.push(
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
					field: {
						Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
						SystemExpression: DelayInterval.BASIS_TYPE.CurrentDateTime,
					},
					onclick(event, item)
					{
						if (callback)
						{
							callback(item.field);
						}

						this.popupWindow.close();
					}
				},
				{
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
					field: {
						Name: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
						SystemExpression: DelayInterval.BASIS_TYPE.CurrentDate,
					},
					onclick(event, item)
					{
						if (callback)
						{
							callback(item.field);
						}

						this.popupWindow.close();
					}
				},
				{
					delimiter: true
				}
			);
		}

		for (let i = 0; i < this.basisFields.length; ++i)
		{
			if (
				delayType !== DelayInterval.DELAY_TYPE.After
				&& this.basisFields[i]['Id'].indexOf('DATE_CREATE') > -1
			)
			{
				continue;
			}

			menuItems.push({
				text: Text.encode(this.basisFields[i].Name),
				field: this.basisFields[i],
				onclick(e, item)
				{
					if (callback)
					{
						callback(item.field || item.options.field);
					}

					this.popupWindow.close();
				},
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
				offsetLeft: (BX.pos(labelNode)['width'] / 2),
				angle: { position: 'top', offset: 0 },
				overlay: { backgroundColor: 'transparent' }
			}
		);

		this.fieldsMenu = MenuManager.currentItem;
	}

	getBasisField(basis, system)
	{
		if (system && (basis === DelayInterval.BASIS_TYPE.CurrentDateTime || basis === DelayInterval.BASIS_TYPE.CurrentDateTimeLocal))
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
				fld['Id'].indexOf('DATE_MODIFY') < 0
				&& fld['Id'].indexOf('EVENT_DATE') < 0
				&& fld['Id'].indexOf('BIRTHDATE') < 0
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

		const workDayRadio = Dom.create("input", {
			attrs: {
				type: "checkbox",
				id: uid + "wait_workday",
				name: "wait_workday",
				value: '1',
				style: 'vertical-align: middle'
			},
			props: {
				checked: delay.waitWorkDay && isAvailable
			}
		});

		if (!isAvailable)
		{
			workDayRadio.disabled = true;
		}

		const workDayHelpNode = Dom.create('span', {
			attrs: {
				className: "bizproc-automation-status-help bizproc-automation-status-help-right",
				'data-hint': Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_HELP')
			}
		});

		const events = {};

		if (!isAvailable)
		{
			events.click = () => {
				if (top.BX.UI && top.BX.UI.InfoHelper)
				{
					top.BX.UI.InfoHelper.show('limit_office_worktime_responsible');
				}
			};
		}

		return Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-select-item" },
			children: [ Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings-title" },
				children: [
					workDayRadio,
					Dom.create("label", {
						attrs: {
							className: `bizproc-automation-popup-settings-lbl ${!isAvailable? 'bizproc-automation-robot-btn-set-locked' : ''}`,
							for: uid + "wait_workday"
						},
						text: Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_MSGVER_1')
					}),
					workDayHelpNode
				]
			})],
			events,
		});
	}

	#isWorkTimeAvailable(): boolean
	{
		return getGlobalContext().get('IS_WORKTIME_AVAILABLE') ?? false;
	}
}