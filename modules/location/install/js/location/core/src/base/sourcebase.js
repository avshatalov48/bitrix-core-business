import AutocompleteServiceBase from "./autocompleteservicebase";
import PhotoServiceBase from "./photoservicebase";
import MapBase from "./mapbase";

/**
 * Base class for the sources
 */
export default class SourceBase
{
	get sourceCode(): string
	{
		throw new Error('Must be implemented');
	}

	get map(): MapBase
	{
		throw new Error('Must be implemented');
	}

	get autocompleteService(): AutocompleteServiceBase
	{
		throw new Error('Must be implemented');
	}

	get photoService(): PhotoServiceBase
	{
		throw new Error('Must be implemented');
	}

	get geocodingService()
	{
		throw new Error('Must be implemented');
	}
}