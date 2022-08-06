export class BaseView
{
	#wrapper: ?Element = null;

	constructor(params: Object) {
		this.#wrapper = params.wrapper;
	}

	getWrapper(): Element
	{
		return this.#wrapper;
	}

	layout(): Element
	{
		throw new Error('please implement the layout() method');
	}
}
