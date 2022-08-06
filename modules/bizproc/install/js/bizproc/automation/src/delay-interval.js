import { Type, Loc } from 'main.core';

export class DelayInterval
{
	static BASIS_TYPE = {
		CurrentDate: '{=System:Date}',
		CurrentDateTime: '{=System:Now}',
		CurrentDateTimeLocal: '{=System:NowLocal}',
	};

	static DELAY_TYPE = {
		After: 'after',
		Before: 'before',
		In: 'in',
	};

	#basis: string;
	#type: string;
	#value: number;
	#valueType: string;
	#workTime: boolean;
	#localTime: boolean;

	constructor(params: ?Object)
	{
		this.#basis = DelayInterval.BASIS_TYPE.CurrentDateTime;
		this.#type = DelayInterval.DELAY_TYPE.After;
		this.#value = 0;
		this.#valueType = 'i';
		this.#workTime = false;
		this.#localTime = false;

		if (Type.isPlainObject(params))
		{
			if (params['type'])
			{
				this.setType(params['type']);
			}
			if (params['value'])
			{
				this.setValue(params['value']);
			}
			if (params['valueType'])
			{
				this.setValueType(params['valueType']);
			}
			if (params['basis'])
			{
				this.setBasis(params['basis']);
			}
			if (params['workTime'])
			{
				this.setWorkTime(params['workTime']);
			}
			if (params['localTime'])
			{
				this.setLocalTime(params['localTime']);
			}
		}
	}

	get basis()
	{
		return this.#basis;
	}

	get type()
	{
		return this.#type;
	}

	get value()
	{
		return this.#value;
	}

	get valueType()
	{
		return this.#valueType;
	}

	get workTime()
	{
		return this.#workTime;
	}

	get localTime()
	{
		return this.#localTime;
	}

	clone()
	{
		return new DelayInterval({
			type: this.#type,
			value: this.#value,
			valueType: this.#valueType,
			basis: this.#basis,
			workTime: this.#workTime,
			localTime: this.#localTime,
		});
	}

	static isSystemBasis(basis: string): boolean
	{
		return (
			basis === this.BASIS_TYPE.CurrentDate
			|| basis === this.BASIS_TYPE.CurrentDateTime
			|| basis === this.BASIS_TYPE.CurrentDateTimeLocal
		);
	}

	static fromString(intervalString, basisFields): this
	{
		intervalString = intervalString.toString();
		const params = {
			basis: DelayInterval.BASIS_TYPE.CurrentDateTime,
			i: 0,
			h: 0,
			d: 0,
			workTime: false,
			localTime: false
		};

		if (intervalString.indexOf('=dateadd(') === 0 || intervalString.indexOf('=workdateadd(') === 0)
		{
			if (intervalString.indexOf('=workdateadd(') === 0)
			{
				intervalString = intervalString.substring(13);
				params['workTime'] = true;
			}
			else
			{
				intervalString = intervalString.substring(9);
			}

			const fnArgs = intervalString.split(',');
			params['basis'] = fnArgs[0].trim();
			fnArgs[1] = fnArgs[1].replace(/['")]+/g, '');
			params['type'] = fnArgs[1].indexOf('-') === 0 ? DelayInterval.DELAY_TYPE.Before : DelayInterval.DELAY_TYPE.After;

			let match;
			const re = /s*([\d]+)\s*(i|h|d)\s*/ig;
			while (match = re.exec(fnArgs[1]))
			{
				params[match[2]] = parseInt(match[1]);
			}
		}
		else
		{
			params['basis'] = intervalString;
		}

		if (!DelayInterval.isSystemBasis(params['basis']) && BX.type.isArray(basisFields))
		{
			let found = false;
			for (let i = 0, s = basisFields.length; i < s; ++i)
			{
				if (params['basis'] === basisFields[i].SystemExpression || params['basis'] === basisFields[i].Expression)
				{
					params['basis'] = basisFields[i].SystemExpression;
					found = true;
					break;
				}
			}
			if (!found)
			{
				params['basis'] = DelayInterval.BASIS_TYPE.CurrentDateTime;
			}
		}

		const minutes = params['i'] + params['h'] * 60 + params['d'] * 60 * 24;

		if (minutes % 1440 === 0)
		{
			params['value'] = minutes / 1440;
			params['valueType'] = 'd';
		}
		else if (minutes % 60 === 0)
		{
			params['value'] = minutes / 60;
			params['valueType'] = 'h';
		}
		else
		{
			params['value'] = minutes;
			params['valueType'] = 'i';
		}

		if (!params['value'] && params['basis'] !== DelayInterval.BASIS_TYPE.CurrentDateTime && params['basis'])
		{
			params['type'] = DelayInterval.DELAY_TYPE.In;
		}

		return new DelayInterval(params);
	};

	static fromMinutes(minutes): Array<string>
	{
		let value;
		let type;

		if (minutes % 1440 === 0)
		{
			value = minutes / 1440;
			type = 'd';
		}
		else if (minutes % 60 === 0)
		{
			value = minutes / 60;
			type = 'h';
		}
		else
		{
			value = minutes;
			type = 'i';
		}

		return [value, type];
	}

	static toMinutes(value, valueType): number
	{
		let result = 0;

		switch (valueType)
		{
			case 'i':
				result = value;
				break;
			case 'h':
				result = value * 60;
				break;
			case 'd':
				result = value * 60 * 24;
				break;
		}

		return result;
	}

	setType(type): this
	{
		if (
			type !== DelayInterval.DELAY_TYPE.After
			&& type !== DelayInterval.DELAY_TYPE.Before
			&& type !== DelayInterval.DELAY_TYPE.In
		)
		{
			type = DelayInterval.DELAY_TYPE.After;
		}
		this.#type = type;

		return this;
	}

	setValue(value): this
	{
		value = parseInt(value);
		this.#value = value >= 0 ? value : 0;

		return this;
	}

	setValueType(valueType: string): this
	{
		if (valueType !== 'i' && valueType !== 'h' && valueType !== 'd')
		{
			valueType = 'i';
		}

		this.#valueType = valueType;

		return this;
	}

	setBasis(basis: string): this
	{
		if (Type.isString(basis) && basis !== '')
		{
			this.#basis = basis;
		}

		return this;
	}

	setWorkTime(flag): this
	{
		this.#workTime = !!flag;

		return this;
	}

	setLocalTime(flag): this
	{
		this.#localTime = !!flag;

		return this;
	}

	isNow(): boolean
	{
		return (
			this.#type === DelayInterval.DELAY_TYPE.After
			&& this.#basis === DelayInterval.BASIS_TYPE.CurrentDateTime
			&& !this.#value
		);
	}

	setNow(): void
	{
		this.setType(DelayInterval.DELAY_TYPE.After);
		this.setValue(0);
		this.setValueType('i');
		this.setBasis(DelayInterval.BASIS_TYPE.CurrentDateTime);
	}

	serialize(): Object
	{
		return {
			type: this.#type,
			value: this.#value,
			valueType: this.#valueType,
			basis: this.#basis,
			workTime: this.#workTime ? 1 : 0
		}
	}

	toExpression(basisFields, workerExpression): string
	{
		let basis = this.#basis ? this.#basis : DelayInterval.BASIS_TYPE.CurrentDate;

		if (!DelayInterval.isSystemBasis(basis) && Type.isArray(basisFields))
		{
			for (let i = 0, s = basisFields.length; i < s; ++i)
			{
				if (basis === basisFields[i].SystemExpression)
				{
					basis = basisFields[i].Expression;
					break;
				}
			}
		}

		if (!this.#workTime && (this.#type === DelayInterval.DELAY_TYPE.In || this.isNow()))
		{
			return basis;
		}

		let days = 0;
		let hours = 0;
		let minutes = 0;

		switch (this.#valueType)
		{
			case 'i':
				minutes = this.#value;
				break;
			case 'h':
				hours = this.#value;
				break;
			case 'd':
				days = this.#value;
				break;
		}

		let add = '';

		if (this.#type === DelayInterval.DELAY_TYPE.Before)
		{
			add = '-';
		}

		if (days > 0)
		{
			add += days + 'd';
		}
		if (hours > 0)
		{
			add += hours + 'h';
		}
		if (minutes > 0)
		{
			add += minutes + 'i';
		}

		const fn = this.#workTime ? 'workdateadd' : 'dateadd';

		if (fn === 'workdateadd' && add === '')
		{
			add = '0d';
		}

		let worker = '';
		if (fn === 'workdateadd' && workerExpression)
		{
			worker = workerExpression;
		}

		return '='+ fn + '(' + basis + ',"' + add + '"' + (worker ? ',' + worker : '') + ')';
	}

	format(emptyText, fields)
	{
		let str = emptyText;

		if (this.#type === DelayInterval.DELAY_TYPE.In)
		{
			str = Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_TIME');
			if (Type.isArray(fields))
			{
				for (let i = 0; i < fields.length; ++i)
				{
					if (this.#basis === fields[i].SystemExpression)
					{
						str += ' ' + fields[i].Name;
						break;
					}
				}
			}
		}
		else if (this.#value)
		{
			const prefix = (
				this.#type === DelayInterval.DELAY_TYPE.After
					? Loc.getMessage('BIZPROC_AUTOMATION_CMP_THROUGH')
					: Loc.getMessage('BIZPROC_AUTOMATION_CMP_FOR_TIME_1')
			);

			str = prefix + ' ' + this.getFormattedPeriodLabel(this.#value, this.#valueType);

			if (Type.isArray(fields))
			{
				const fieldSuffix = (
					this.#type === DelayInterval.DELAY_TYPE.After
						? Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER')
						: Loc.getMessage('BIZPROC_AUTOMATION_CMP_BEFORE_1')
				);
				for (let i = 0; i < fields.length; ++i)
				{
					if (this.#basis === fields[i].SystemExpression)
					{
						str += ' ' + fieldSuffix + ' ' + fields[i].Name;
						break;
					}
				}
			}
		}

		if (this.#workTime)
		{
			str += ', ' + Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_WORKTIME');
		}

		return str;
	}

	getFormattedPeriodLabel(value, type)
	{
		const label = value + ' ';
		let labelIndex = 0;
		if (value > 20)
		{
			value = (value % 10);
		}

		if (value === 1)
		{
			labelIndex = 0;
		}
		else if (value > 1 && value < 5)
		{
			labelIndex = 1;
		}
		else
		{
			labelIndex = 2;
		}

		const labels = DelayInterval.getPeriodLabels(type);

		return label + (labels ? labels[labelIndex] : '');
	}

	static getPeriodLabels(period)
	{
		let labels = [];
		if (period === 'i')
		{
			labels = [
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN1'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN2'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN3')
			];
		}
		else if (period === 'h')
		{
			labels = [
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR1'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR2'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR3')
			];
		}
		else if (period === 'd')
		{
			labels = [
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY1'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY2'),
				Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY3')
			];
		}

		return labels;
	}
}