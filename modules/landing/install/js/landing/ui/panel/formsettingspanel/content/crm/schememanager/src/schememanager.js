type SchemesType = Array<{
	id: number,
	name: string,
	description: string,
	dynamic: boolean,
	entities: Array<string>,
	specularId: number,
	mainEntity: number,
	hasInvoice: boolean
}>;

export class SchemeManager
{
	#schemes: SchemesType;
	#defaultSchemes: SchemesType;

	constructor(schemes)
	{
		this.#schemes = schemes;
		this.#defaultSchemes = this.#schemes.filter((scheme) => !scheme.dynamic);
	}

	isInvoice(schemeId: number): boolean
	{
		return this.findSchemeById(schemeId).hasInvoice;
	}

	findSchemeById(schemeId: number)
	{
		return this.#schemes.find((scheme) => scheme.id === schemeId);
	}

	getSpecularSchemeId(schemeId: number): number
	{
		return this.findSchemeById(schemeId).specularId;
	}

	isDefaultScheme(schemeId: number)
	{
		return this.#defaultSchemes.findIndex((scheme) => scheme.id === schemeId) !== -1;
	}
}