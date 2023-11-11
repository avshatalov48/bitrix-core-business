import { Loc } from 'main.core';

export class Operator
{
	static EMPTY = 'empty';
	static NOT_EMPTY = '!empty';
	static EQUAL = '=';
	static NOT_EQUAL = '!=';
	static CONTAIN = 'contain';
	static NOT_CONTAIN = '!contain';
	static IN = 'in';
	static NOT_IN = '!in';
	static GREATER_THEN = '>';
	static GREATER_THEN_OR_EQUAL = '>=';
	static LESS_THEN = '<';
	static LESS_THEN_OR_EQUAL = '<=';
	static MODIFIED = 'modified';
	static BETWEEN = 'between';

	static getAll(): []
	{
		return [
			this.NOT_EMPTY,
			this.EMPTY,
			this.EQUAL,
			this.NOT_EQUAL,
			this.CONTAIN,
			this.NOT_CONTAIN,
			this.IN,
			this.NOT_IN,
			this.GREATER_THEN,
			this.GREATER_THEN_OR_EQUAL,
			this.LESS_THEN,
			this.LESS_THEN_OR_EQUAL,
			this.MODIFIED,
			this.BETWEEN,
		];
	}

	static getAllLabels(): {}
	{
		return Object.fromEntries([
			[this.EMPTY, Loc.getMessage('BIZPROC_JS_CONDITION_EMPTY')],
			[this.NOT_EMPTY, Loc.getMessage('BIZPROC_JS_CONDITION_NOT_EMPTY')],
			[this.EQUAL, Loc.getMessage('BIZPROC_JS_CONDITION_EQ')],
			[this.NOT_EQUAL, Loc.getMessage('BIZPROC_JS_CONDITION_NE')],
			[this.CONTAIN, Loc.getMessage('BIZPROC_JS_CONDITION_CONTAIN')],
			[this.NOT_CONTAIN, Loc.getMessage('BIZPROC_JS_CONDITION_NOT_CONTAIN')],
			[this.IN, Loc.getMessage('BIZPROC_JS_CONDITION_IN')],
			[this.NOT_IN, Loc.getMessage('BIZPROC_JS_CONDITION_NOT_IN')],
			[this.GREATER_THEN, Loc.getMessage('BIZPROC_JS_CONDITION_GT')],
			[this.GREATER_THEN_OR_EQUAL, Loc.getMessage('BIZPROC_JS_CONDITION_GTE')],
			[this.LESS_THEN, Loc.getMessage('BIZPROC_JS_CONDITION_LT')],
			[this.LESS_THEN_OR_EQUAL, Loc.getMessage('BIZPROC_JS_CONDITION_LTE')],
			[this.BETWEEN, Loc.getMessage('BIZPROC_JS_CONDITION_BETWEEN')],
			[this.MODIFIED, Loc.getMessage('BIZPROC_JS_CONDITION_MODIFIED')],
		]);
	}

	static getOperatorLabel(operator: string): string
	{
		return this.getAllLabels()[operator] ?? '';
	}

	static getOperatorFieldTypeFilter(operator: string, isRobot: boolean = false): []
	{
		if (!this.getAll().includes(operator))
		{
			return [];
		}

		if (operator === this.BETWEEN)
		{
			return ['int', 'double', 'date', 'datetime', 'time'];
		}

		return [];
	}

	static getAllSortedForBp(): []
	{
		return [
			this.EQUAL,
			this.NOT_EQUAL,
			this.GREATER_THEN,
			this.GREATER_THEN_OR_EQUAL,
			this.LESS_THEN,
			this.LESS_THEN_OR_EQUAL,
			this.IN,
			this.NOT_IN,
			this.CONTAIN,
			this.NOT_CONTAIN,
			this.NOT_EMPTY,
			this.EMPTY,
			this.MODIFIED,
			this.BETWEEN,
		];
	}

	static getOperatorsWithoutRenderValue(): []
	{
		return [this.EMPTY, this.NOT_EMPTY, this.MODIFIED];
	}
}
