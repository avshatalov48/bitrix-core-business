//@flow
import {type ProcessOptions} from './process-types';
import {Process} from './process';

/**
 * @namespace {BX.UI.StepProcessing}
 */
export class ProcessManager
{
	static instances: Map<string, Process>;

	static create(props: ProcessOptions): Process
	{
		if (!this.instances)
		{
			this.instances = new Map();
		}

		let process = new Process(props);
		this.instances.set(process.getId(), process);

		return process;
	}

	static get(id: string): ?Process
	{
		if (this.instances)
		{
			if (this.instances.has(id))
			{
				return this.instances.get(id);
			}
		}

		return null;
	}

	static has(id: string): boolean
	{
		if (this.instances)
		{
			return this.instances.has(id);
		}

		return false;
	}

	static delete(id: string): void
	{
		if (this.instances)
		{
			if (this.instances.has(id))
			{
				this.instances.get(id).destroy();
				this.instances.delete(id);
			}
		}
	}
}
