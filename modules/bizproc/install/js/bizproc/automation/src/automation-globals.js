import {Type} from 'main.core';
import {Globals} from "bizproc.globals";

export class AutomationGlobals
{
	#globalVariables: [] = [];
	#globalConstants: [] = [];

	constructor(parameters: {
		variables: ?[],
		constants: ?[],
	})
	{
		if (Type.isArrayFilled(parameters.variables))
		{
			const variables = [];
			parameters.variables.forEach((property) => {
				variables.push(
					this.#getAutomationGlobalsProperty(property.Id, property, Globals.Manager.Instance.mode.variable)
				);
			});

			this.#globalVariables = variables;
		}

		if (Type.isArrayFilled(parameters.constants))
		{
			const constants = [];
			parameters.constants.forEach((property) => {
				constants.push(
					this.#getAutomationGlobalsProperty(property.Id, property, Globals.Manager.Instance.mode.constant)
				);
			});

			this.#globalConstants = constants;
		}
	}

	get globalVariables(): []
	{
		return this.#globalVariables;
	}

	set globalVariables(variables: [])
	{
		if (!Type.isArray(variables))
		{
			return;
		}

		this.#globalVariables = variables;
	}

	get globalConstants(): []
	{
		return this.#globalConstants;
	}

	set globalConstants(constants: [])
	{
		if (!Type.isArray(constants))
		{
			return;
		}

		this.#globalConstants = constants;
	}

	#isCorrectMode(mode): boolean
	{
		return Type.isStringFilled(mode) && Object.values(Globals.Manager.Instance.mode).includes(mode);
	}

	#getAutomationGlobalsProperty(id: string, property: {}, mode: string): {
		ObjectId: string,
		SuperTitle: string,
		Id: string,
		Name: string,
		Type: string,
		BaseType: string,
		Expression: string,
		SystemExpression: string,
		Options: any,
		Multiple: boolean,
		Visibility: string,
	}
	{
		return {
			ObjectId: this.#getObjectId(mode),
			SuperTitle: String(property.VisibilityName),
			Id: String(id),
			Name: String(property.Name),
			Type: String(property.Type),
			BaseType: String(property.BaseType || property.Type),
			Expression:
				Type.isStringFilled(property.Expression)
					? property.Expression
					: this.#getExpression(property.Name, property.VisibilityName)
			,
			SystemExpression:
				Type.isStringFilled(property.SystemExpression)
					? property.SystemExpression
					: this.#getSystemExpression(mode, id)
			,
			Options: property.Options,
			Multiple: Type.isBoolean(property.Multiple) ? property.Multiple : property.Multiple === 'Y',
			Visibility: String(property.Visibility),
		};
	}

	#getExpression(name, visibilityName): string
	{
		return '{{' + String(visibilityName) + ': ' + String(name) + '}}'
	}

	#getSystemExpression(mode, id): string
	{
		return '{=' + this.#getObjectId(mode) + ':' + String(id) + '}';
	}

	#getObjectId(mode): string
	{
		return (mode === Globals.Manager.Instance.mode.variable) ? 'GlobalVar' : 'GlobalConst'
	}

	updateGlobals(mode: string, updatedGlobals: {})
	{
		if (!this.#isCorrectMode(mode) || Object.keys(updatedGlobals).length < 1)
		{
			return;
		}

		let globals = this.#getGlobals(mode);

		const newGlobals = [];
		for (const id in updatedGlobals)
		{
			const property = updatedGlobals[id];
			const index = globals.findIndex(prop => prop.Id === id);
			if (index > -1)
			{
				if (globals[index].Name !== property.Name)
				{
					globals[index].Name = property.Name;
					globals[index].Expression = this.#getExpression(property.Name, property.VisibilityName);
				}

				continue;
			}

			newGlobals.push(this.#getAutomationGlobalsProperty(id, property, mode));
		}

		if (Type.isArrayFilled(newGlobals))
		{
			globals = globals.concat(newGlobals);
		}

		this.#setGlobals(mode, globals);
	}

	deleteGlobals(mode: string, deletedGlobals: [])
	{
		if (!this.#isCorrectMode(mode) || !Type.isArrayFilled(deletedGlobals))
		{
			return;
		}

		const globals = this.#getGlobals(mode);

		deletedGlobals.forEach((id) => {
			const index = globals.findIndex((prop) => prop.Id === id);
			if (index > -1)
			{
				globals.splice(index, 1);
			}
		});

		this.#setGlobals(mode, globals);
	}

	#getGlobals(mode): []
	{
		if (mode === Globals.Manager.Instance.mode.variable)
		{
			return this.globalVariables;
		}

		if (mode === Globals.Manager.Instance.mode.constant)
		{
			return this.globalConstants;
		}
	}

	#setGlobals(mode, globals)
	{
		if (mode === Globals.Manager.Instance.mode.variable)
		{
			this.#globalVariables = globals;
		}

		if (mode === Globals.Manager.Instance.mode.constant)
		{
			this.#globalConstants = globals;
		}
	}
}