import {Validation, Loc, Type} from "main.core";

export class DateValidator
{
	constructor()
	{
		this.errorMessage = Loc.getMessage("SGCG_FORMAT_ERROR");
	}

	validate(value)
	{
		return this.constructor.isValid(value);
	}

	static getType()
	{
		return "date";
	}

	static isValid(value)
	{
		//todo
		return true;
	}

	getErrorMessage()
	{
		return this.errorMessage;
	}
}