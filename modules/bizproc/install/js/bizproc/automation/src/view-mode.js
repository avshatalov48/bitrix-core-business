export class ViewMode
{
	#mode: number;
	#properties: Object;

	static #none = 0;
	static #view = 1;
	static #edit = 2;
	static #manage = 3;

	constructor(mode: number)
	{
		this.#mode = mode;
		this.#properties = {};
	}

	static none(): ViewMode
	{
		return new ViewMode(ViewMode.#none)
	}

	isNone(): boolean
	{
		return this.#mode === ViewMode.#none;
	}

	static view(): ViewMode
	{
		return new ViewMode(ViewMode.#view);
	}

	isView(): boolean
	{
		return this.#mode === ViewMode.#view;
	}

	static edit(): ViewMode
	{
		return new ViewMode(ViewMode.#edit);
	}

	isEdit(): boolean
	{
		return this.#mode === ViewMode.#edit;
	}

	static manage(): ViewMode
	{
		return new ViewMode(ViewMode.#manage);
	}

	isManage(): boolean
	{
		return this.#mode === ViewMode.#manage;
	}

	setProperty(name: string, value: any): ViewMode
	{
		this.#properties[name] = value;

		return this;
	}

	getProperty(name: string, defaultValue: any = null): ?any
	{
		if (this.#properties.hasOwnProperty(name))
		{
			return this.#properties[name];
		}

		return defaultValue;
	}

	static fromRaw(mode: number): ViewMode
	{
		if (ViewMode.getAll().includes(mode))
		{
			return new ViewMode(mode);
		}

		return ViewMode.none();
	}

	intoRaw(): number
	{
		return this.#mode;
	}

	static getAll(): Array<number>
	{
		return [
			this.#none,
			this.#view,
			this.#edit,
			this.#manage,
		];
	}
}
