import {Type} from "main.core";
import {BaseSource, SourceCreationError} from "location.core";
import Loader from "./loader";
import AutocompleteService from "./autocompleteservice";
import Map from "./map";
import PhotoService from "./photoservice";
import GeocodingService from "./geocodingservice";

export class Google extends BaseSource
{
	static code = 'GOOGLE';
	#languageId = '';
	#sourceLanguageId = '';
	#loaderPromise = null;
	#map;
	#photoService;
	#geocodingService;
	#autocompleteService;

	constructor(props: Object)
	{
		super(props);

		if(!Type.isString(props.languageId) || props.languageId.trim() === '')
		{
			throw new SourceCreationError('props.languageId must be a string');
		}

		this.#languageId = props.languageId;

		if(!Type.isString(props.sourceLanguageId) || props.sourceLanguageId.trim() === '')
		{
			throw new SourceCreationError('props.sourceLanguageId must be a string');
		}

		this.#sourceLanguageId = props.sourceLanguageId;

		if(!Type.isString(props.apiKey) || props.apiKey.trim() === '')
		{
			throw new SourceCreationError('props.apiKey must be a string');
		}

		this.#loaderPromise = Loader.load(props.apiKey, props.sourceLanguageId);

		this.#map = new Map({
			googleSource: this,
			languageId: this.#languageId,
		});

		this.#autocompleteService = new AutocompleteService({
			googleSource: this,
			languageId: this.#languageId
		});

		this.#photoService = new PhotoService({
			googleSource: this,
			map: this.#map
		});

		this.#geocodingService = new GeocodingService({
			googleSource: this,
			map: this.#map
		});
	}

	get sourceCode(): string
	{
		return Google.code;
	}

	get loaderPromise(): Promise
	{
		return this.#loaderPromise;
	}

	get map()
	{
		return this.#map;
	}

	get autocompleteService()
	{
		return this.#autocompleteService;
	}

	get photoService()
	{
		return this.#photoService;
	}

	get geocodingService()
	{
		return this.#geocodingService;
	}

	get languageId()
	{
		return this.#languageId;
	}
}