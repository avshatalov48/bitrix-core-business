import { BaseEvent, EventEmitter } from "main.core.events";

export class Counters
{
	counters = [];
	hiddenCountersForTotalCounter = [];
	shortcuts = [];
	#name;
	selectedDirectory;

	constructor(name,selectedDirectory)
	{
		this.#name = name;
		this.setDirectory(selectedDirectory);
	}

	getDirPath(shortcut)
	{
		if(shortcut === undefined)
		{
			shortcut = '';
		}

		if(this.shortcuts[shortcut] !== undefined)
		{
			return this.shortcuts[shortcut];
		}

		return shortcut;
	}

	getShortcut(path)
	{
		//because they have a closure
		return this.getDirPath(path);
	}

	setDirectory(name)
	{
		if(name === undefined)
		{
			name = '';
		}

		if(this.shortcuts[name])
		{
			this.selectedDirectory = this.shortcuts[name];
		}
		else
		{
			this.selectedDirectory = name;
		}

		let resultCounters = {};
		resultCounters[this.selectedDirectory] = this.getCounter(this.selectedDirectory);
		this.senCounterUpdateEvent(resultCounters);
	}

	setShortcut(shortcutName,name)
	{
		//backlink
		this.shortcuts[shortcutName] = name;
		this.shortcuts[name] = shortcutName;
	}

	getName()
	{
		return this.#name;
	}

	setHiddenCountersForTotalCounter(counterNames)
	{
		for (let counter of counterNames)
		{
			this.hiddenCountersForTotalCounter[counter] = 'disabled';
		}
	}

	isHidden(name):boolean
	{
		if(this.hiddenCountersForTotalCounter[name] === 'disabled')
		{
			return true;
		}
		return false;
	}

	getTotalCounter()
	{
		let counters = 0;
		for (let name in this.counters)
		{
			if(name in this.hiddenCountersForTotalCounter)
			{
				continue;
			}
			counters += this.counters[name];
		}

		return counters;
	}

	getCounterObjects()
	{
		return this.counters;
	}

	getCounter(name)
	{
		return this.counters[name];
	}

	addCounter(name,count)
	{
		this.counters[name] = Number(count);
		return this.counters[name];
	}

	addCounters(counters)
	{
		let resultCounters = {};

		for (let i = 0; i < counters.length; i++)
		{
			const counter = counters[i]
			const path = counter['path'];
			this.addCounter(path,counter['count']);

			if(this.shortcuts[path])
			{
				resultCounters[this.shortcuts[path]] = counter['count'];
			}
			else
			{
				resultCounters[path] = counter['count'];
			}

		}

		this.senCounterUpdateEvent(resultCounters);
	}

	/*Set counters as when adding. Old counters with different names are retained*/
	setCounters(counters)
	{
		this.addCounters(counters);
	}

	isExists(name)
	{
		return this.counters[name] !== undefined;
	}

	increaseCounter(name,count = 1 )
	{
		if(name in this.hiddenCountersForTotalCounter)
		{
			return "hidden counters for total counter";
		}

		if(!this.isExists(name))
		{
			return "no counter";
		}
		this.counters[name] += Number(count);
	}

	lowerCounter(name,count = 1 )
	{
		if(name in this.hiddenCountersForTotalCounter)
		{
			return "hidden counters for total counter";
		}

		if(!this.isExists(name))
		{
			return "no counter";
		}

		const newValue = this.counters[name] - Number(count);
		if(newValue < 0)
		{
			return "negative value";
		}

		this.counters[name] = newValue;
	}

	/*Change counters by rule*/
	updateCounters(counters = [
		{
			name: 'counter1',
			count: 2,
			increase: false,
			lower: true,
		},
		{
			name: 'counter2',
			count: 2,
			increase: true,
			lower: false,
		},
	])
	{
		let resultCounters = {};
		let countersAreNotLoadedFromTheServer = false;

		for (let i = 0; i < counters.length; i++)
		{
			const counter = counters[i];
			const name = counter['name'];

			if(counter['lower'])
			{
				if(this.lowerCounter(name,counter['count']) === "negative value")
				{
					countersAreNotLoadedFromTheServer = true;
				}
			}

			if(counter['increase'] && countersAreNotLoadedFromTheServer === false)
			{
				this.increaseCounter(name,counter['count'])
			}

			if(this.shortcuts[name])
			{
				resultCounters[this.shortcuts[name]] = this.getCounter(name);
			}
			else
			{
				resultCounters[name] = this.getCounter(name);
			}
		}

		this.senCounterUpdateEvent(resultCounters);
	}

	senCounterUpdateEvent(counters)
	{
		const event = new BaseEvent({
			data: {
				counters: counters,
				hidden: this.hiddenCountersForTotalCounter,
				selectedDirectory: this.selectedDirectory,
				name: this.getName(),
			}
		});

		EventEmitter.emit('BX.Mail.Home:updatingCounters', event);
	}

}