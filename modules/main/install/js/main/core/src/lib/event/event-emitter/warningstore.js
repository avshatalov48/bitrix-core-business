import Type from "../../type";
import Runtime from "../../runtime";

export default class WarningStore
{
	constructor()
	{
		this.warnings = new Map();
		this.printDelayed = Runtime.debounce(this.print.bind(this), 500);
	}

	add(target, eventName, listeners)
	{
		let contextWarnings = this.warnings.get(target);
		if (!contextWarnings)
		{
			contextWarnings = Object.create(null);
			this.warnings.set(target, contextWarnings);
		}

		if (!contextWarnings[eventName])
		{
			contextWarnings[eventName] = {};
		}

		contextWarnings[eventName].size = listeners.size;
		if (!Type.isArray(contextWarnings[eventName].errors))
		{
			contextWarnings[eventName].errors = [];
		}

		contextWarnings[eventName].errors.push(new Error());
	}

	print()
	{
		for (let [target, warnings] of this.warnings)
		{
			for (let eventName in warnings)
			{
				console.groupCollapsed(
					'Possible BX.Event.EventEmitter memory leak detected. ' +
					warnings[eventName].size + ' "' + eventName + '" listeners added. ' +
					'Use emitter.setMaxListeners() to increase limit.'
				);
				console.dir(warnings[eventName].errors);
				console.groupEnd();
			}
		}

		this.clear();
	}

	clear()
	{
		this.warnings.clear();
	}

	printDelayed()
	{
	}
}