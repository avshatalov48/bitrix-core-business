import AddressLink from "./addresslink";

export default class AddressLinkCollection
{
	#links = [];

	constructor(props = {})
	{
		this.links = !!props.links ? props.links : [];
	}

	set links(links: Array): void
	{
		if(!Array.isArray(links))
		{
			throw new Error('links must be array!');
		}

		for(let link of links)
		{
			this.addLink(link);
		}
	}

	get links()
	{
		return this.#links;
	}

	addLink(link: AddressLink)
	{
		if(!(link instanceof AddressLink))
		{
			throw new Error('Argument link must be instance of Field!');
		}

		this.#links.push(link);
	}

	clearLinks()
	{
		this.#links = [];
	}
}