import {Loc, Type} from "main.core";

export class RequireValidator
{
	constructor()
	{
		this.errorMessage = Loc.getMessage("SGCG_REQUIRE_ERROR");

	}

	validate(value)
	{
		return this.constructor.isValid(value);
	}

	static getType()
	{
		return "require";
	}

	static isValid(value)
	{
		if (Type.isArray(value))
		{
			return value.length > 0;
		}
		else
		{
			return (value !== undefined) && (String(value).trim().length > 0);
		}
	}

	getErrorMessage()
	{
		return this.errorMessage;
	}
}