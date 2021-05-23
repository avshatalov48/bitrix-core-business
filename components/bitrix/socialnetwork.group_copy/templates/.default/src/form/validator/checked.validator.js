import {Loc} from "main.core";

export class CheckedValidator
{
	constructor()
	{
		this.errorMessage = Loc.getMessage("SGCG_REQUIRE_ERROR");

	}

	validate(field)
	{
		return this.constructor.isValid(field);
	}

	static getType()
	{
		return "checked";
	}

	static isValid(field)
	{
		return (field ? field.checked : false);
	}

	getErrorMessage()
	{
		return this.errorMessage;
	}
}