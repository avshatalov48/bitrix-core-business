import {ajax, Tag, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';

type JobData = {
	step: number;
	status: 'R' | 'P' | 'F' | 'E',
	statusMessage: string,
	formattedStatusMessage: string,
}

type GeneratorJob = {
	id: number,
	statusNode: HTMLDivElement,
	step: number,
	status: string,
	statusMessage: '',
	formattedStatusMessage: '',
};

export class Generator extends EventEmitter
{
	static STATUS_REGISTER = 'R';
	static STATUS_FINISH = 'F';
	static STATUS_ERROR = 'E';

	static START_STATUS = Generator.STATUS_REGISTER;
	static START_STEP = 0;
	static STATUS_CLASS = 'sitemap-status';

	#statusContainer: HTMLElement;
	#jobs: [GeneratorJob] = [];

	/**
	 * @param container - HTML element for print sitemap statuses
	 */
	constructor(container: HTMLElement)
	{
		super();
		this.#statusContainer = container;
		Dom.clean(this.#statusContainer);
	}

	add(sitemapId: number, jobData: ?JobData)
	{
		// todo: after finish not running again until page refresh. Need rerun
		if (
			sitemapId > 0
			&& !this.#jobs.find(job => job.id === sitemapId)
		)
		{
			const existsStatusNode = document.getElementById(Generator.STATUS_CLASS + '-' + sitemapId);
			const statusNode =
				existsStatusNode 
				|| Tag.render`
					<div id="${Generator.STATUS_CLASS}-${sitemapId}" class="${Generator.STATUS_CLASS}"></div>
				`;
			Dom.append(statusNode, this.#statusContainer);

			const newJob: GeneratorJob = {
				id: sitemapId,
				statusNode: statusNode,
				step: Generator.START_STEP,
				status: Generator.START_STATUS,
				statusMessage: '',
				formattedStatusMessage: '',
			};
			if (jobData)
			{
				Object.assign(newJob, jobData)
			}
			this.#jobs.push(newJob);

			if (newJob.formattedStatusMessage)
			{
				newJob.status !== 'E'
					? this.#printStatus(newJob.id, newJob.formattedStatusMessage)
					: this.#printError(newJob.id, newJob.formattedStatusMessage)
				;
			}

			this.#do(sitemapId);
		}
	}

	#do(jobId: number)
	{
		this.emit('onBeforeDo', jobId);

		ajax.runAction(
			"seo.api.sitemap.job.do",
			{
				data: {
					sitemapId: jobId,
				}
			},
		)
			.then(result => {
				this.emit('onAfterDo', jobId);

				if (result && result.status === 'success')
				{
					const data: JobData = result.data;

					if (data.status !== Generator.STATUS_FINISH && data.status !== Generator.STATUS_ERROR)
					{
						this.#do(jobId);
					}

					if (data.status === Generator.STATUS_FINISH)
					{
						this.#printStatus(jobId, data.formattedStatusMessage);
						this.#finish(jobId);
					}
					else if (data.status === Generator.STATUS_ERROR)
					{
						this.#printError(jobId, (data.statusMessage || 'Something went wrong'));
					}
					else
					{
						this.#printStatus(jobId, data.formattedStatusMessage);
					}
				}
				else
				{
					this.#printError(jobId, (result.error || 'Something went wrong'));
				}
			})
			.catch(err => {
				const errMsg = err.errors.pop();
				this.#printError(jobId, (errMsg ? errMsg.message : 'Something went wrong'));
			});
	}

	#finish(jobId: number)
	{
		this.#jobs = this.#jobs.filter(job => job.id !== jobId);
		this.emit('onFinish', jobId);
	}

	#printStatus(jobId: number, status: string)
	{
		const node = this.#getStatusNode(jobId);
		const message = Tag.render`<div>${status}</div>`;
		Dom.clean(node);
		Dom.append(message, node);
	}

	#printError(jobId: number, error: string)
	{
		const node = this.#getStatusNode(jobId);
		const message = Tag.render`<div>${error}</div>`;
		Dom.clean(node);
		Dom.append(message, node);
	}

	#getStatusNode(jobId: ?number): ?HTMLDivElement
	{
		const currentJob: GeneratorJob = this.#jobs.find(job => job.id === jobId);

		return currentJob ? currentJob.statusNode : null;
	}
}