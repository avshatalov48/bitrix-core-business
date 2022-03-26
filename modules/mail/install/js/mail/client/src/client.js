import { FilterToolbar } from 'mail.client.filtertoolbar';
import { Binding } from 'mail.client.binding';
import { EventEmitter } from "main.core.events";

export class Client
{
	#filter;
	#filterToolbar;
	#binding;
	#mailboxId;

	constructor(config ={
		filterId: '',
		mailboxId: 0,
	})
	{
		this.#mailboxId = config['mailboxId'];

		this.#filter = BX.Main.filterManager.getById(config['filterId']);
		const mailCounterWrapper = document.querySelector('[data-role="mail-counter-toolbar"]');
		const filterToolbar = new FilterToolbar({
			wrapper: mailCounterWrapper,
			filter: this.#filter,
		});
		filterToolbar.build();
		this.#filterToolbar = filterToolbar;

		this.#binding = new Binding(this.#mailboxId);
		Binding.initButtons();

		EventEmitter.subscribe('Grid::updated', (event) => {
			const [grid] = event.getCompatData();
			if(grid !== {} && grid !== undefined && BX.Mail.Home.Grid.getId() === grid.getId())
			{
				Binding.initButtons();
			}
		});

	}

	getFilterToolbar()
	{
		return this.#filterToolbar;
	}
}