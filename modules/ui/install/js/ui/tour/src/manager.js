import { Type } from 'main.core';
import { Guide } from './guide.js';

class Manager
{
	constructor()
	{
		this.guides = new Map();
		this.autoStartQueue = [];
		this.currentGuide = null;
	}

	create(options)
	{
		options = Type.isPlainObject(options) ? options : {};

		const id = options.id;
		if (!Type.isString(id) && id !== '')
		{
			throw new Error("'id' parameter is required.");
		}

		if (this.get(id))
		{
			throw new Error("The tour instance with the same 'id' already exists.");
		}

		const guide = new Guide(options);
		this.guides.set(guide, true);

		return guide;
	}

	add(options)
	{
		const guide = this.create(options);

		guide.subscribe('UI.Tour.Guide:onFinish', () => {
			this.handleTourFinish(guide);
		});

		if (this.currentGuide)
		{
			this.autoStartQueue.push(guide);
		}
		else
		{
			this.currentGuide = guide;
			guide.start();
		}
	}

	/**
	 * @public
	 * @param {string} id
	 * @returns {Guide|null}
	 */
	get(id)
	{
		return this.guides.get(id);
	}

	/**
	 * @public
	 * @param {string} id
	 */
	remove(id)
	{
		this.guides.delete(id);
	}

	/**
	 * @public
	 * @returns {Guide|null}
	 */
	getCurrentGuide()
	{
		return this.currentGuide;
	}

	/**
	 * @private
	 * @param {Guide} guide
	 */
	handleTourFinish(guide)
	{
		this.currentGuide = null;
		this.remove(guide.getId());

		const autoStartGuide = this.autoStartQueue.shift();
		if (autoStartGuide)
		{
			this.currentGuide = autoStartGuide;
			autoStartGuide.start();
		}
	}
}

export default new Manager();
