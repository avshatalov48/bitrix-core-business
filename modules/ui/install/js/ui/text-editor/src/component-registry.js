type Component = {
	callback: Function,
};

export default class ComponentRegistry
{
	#components: Map<string, Component> = new Map();

	register(name: string, callback: () => {}): void
	{
		this.#components.set(this.constructor.#normalizeName(name), { callback });
	}

	create(name): Object
	{
		const component: Component = this.#components.get(this.constructor.#normalizeName(name));

		return component ? component.callback() : null;
	}

	static #normalizeName(name: string): string
	{
		return String(name).toLowerCase();
	}
}
