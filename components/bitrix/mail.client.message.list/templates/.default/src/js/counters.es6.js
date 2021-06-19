import { BaseEvent, EventEmitter } from "main.core.events";

export class Counters
{
	counters = [];
	hiddenCountersForTotalCounter =[];
	#name;

	constructor(name)
	{
		this.#name = name;
	}

	getName()
	{
		return this.#name;
	}

	setHiddenCountersForTotalCounter(...counterNames)
	{
		for (let counter of counterNames)
		{
			this.hiddenCountersForTotalCounter[counter] = 'disabled';
		}
	}

	getTotalCounter()
	{
		let counters = 0;
		for (let name in this.counters)
		{
			if(name in this.hiddenCountersForTotalCounter) continue;
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
			resultCounters[path] = counter['count'];
		}

		const event = new BaseEvent({
			data: {
				counters: resultCounters,
				name: this.getName(),
			}
		});
		EventEmitter.emit('BX.Mail.Home:updatingCounters', event);
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
		if(!this.isExists(name))
		{
			return "no counter";
		}
		this.counters[name] += Number(count);
	}

	lowerCounter(name,count = 1 )
	{
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

			resultCounters[name] = this.getCounter(name);
		}

		const event = new BaseEvent({
			data: {
				counters: resultCounters,
				name: this.getName(),
			}
		});
		EventEmitter.emit('BX.Mail.Home:updatingCounters', event);
	}
}