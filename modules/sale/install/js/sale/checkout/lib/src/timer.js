class Timer
{
	constructor()
	{
		this.list = [];
	}

	add(fields)
	{
		if (!fields.hasOwnProperty('index'))
		{
			return false;
		}

		this.list[fields.index] = {
			id: fields.id
		};
	}

	get(index)
	{
		if (!this.list[index] || this.list[index].length <= 0)
		{
			return {};
		}

		return this.list[index];
	}

	delete(fields)
	{
		this.list.splice(fields.index, 1);
	}

	clean(fields)
	{
		let timer = this.get(fields.index);

		clearTimeout(timer.id);

		this.delete({
			index: fields.index
		});
	}

	create(time, index = 'default', callback = null, callbackParams)
	{
		this.clean({index});

		index = index == null? 'default': index;
		callback = typeof callback === 'function'? callback: function() {}

		let timer = setTimeout(callback, time);

		let item = {id: timer, index: index};

		this.add(item)
	}

	isEmpty()
	{
		return this.list.length === 0;
	}
}

export {Timer}