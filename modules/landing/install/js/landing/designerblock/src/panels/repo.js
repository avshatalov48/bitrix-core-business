import {RepoPanel, RepoElementType} from '../ui/panels/repo';

export type RepoManagerOptions = {
	repository: Array<RepoElementType>,
	onElementSelect: (RepoElementType) => {}
};

export class RepoManager
{
	constructor(options: RepoManagerOptions)
	{
		this.panel = new RepoPanel({
			onElementSelect: options.onElementSelect
		});

		this.panel.addRepository(options.repository);
	}

	showPanel()
	{
		this.panel.show().then();
	}
}
