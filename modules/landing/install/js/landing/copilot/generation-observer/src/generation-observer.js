export class GenerationObserver
{
	static INTERVAL = 30000;

	generationId: number;
	#intervalId: ?number = null;

	constructor(generationId: number)
	{
		this.generationId = generationId;
		this.onGenerationRestart = this.onGenerationRestart.bind(this);
	}

	observe()
	{
		this.restartObserve();

		BX.PULL.subscribe({
			type: BX.PullClient.SubscriptionType.Server,
			moduleId: 'landing',
			callback: (event) => {
				if (
					event.params.generationId !== undefined
					&& event.params.generationId !== this.generationId
				)
				{
					return;
				}

				const command = event.command;

				if (
					command === 'LandingCopilotGeneration:onStepExecute'
					|| command === 'LandingCopilotGeneration:onPreviewImageCreate'
					|| command === 'LandingCopilotGeneration:onCopilotImageCreate'
				)
				{
					this.restartObserve();
				}

				if (command === 'LandingCopilotGeneration:onCopilotTimeIsOver')
				{
					this.onGenerationRestart();
				}

				if (
					command === 'LandingCopilotGeneration:onGenerationError'
					|| command === 'LandingCopilotGeneration:onGenerationFinish'
				)
				{
					this.stopObserve();
				}
			},
		});
	}

	restartObserve()
	{
		if (this.#intervalId)
		{
			clearInterval(this.#intervalId);
		}

		this.#intervalId = setInterval(this.onGenerationRestart, GenerationObserver.INTERVAL);
	}

	stopObserve()
	{
		if (this.#intervalId)
		{
			clearInterval(this.#intervalId);
		}
	}

	onGenerationRestart()
	{
		BX.ajax.runAction(
			'landing.api.copilot.executeGeneration',
			{
				data: {
					generationId: this.generationId,
				},
			},
		);

		this.restartObserve();
	}
}
