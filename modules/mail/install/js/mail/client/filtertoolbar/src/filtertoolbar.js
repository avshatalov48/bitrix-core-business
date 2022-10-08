import { Tag } from 'main.core';
import './css/style.css';
import { Loc } from 'main.core';
import { BaseEvent, EventEmitter } from "main.core.events";
import 'ui.fonts.opensans';

export class FilterToolbar
{
	#wrapper;
	#filter;
	#statusBtn = false;
	#counterBtn;
	#filterApi;
	#readAllBtn;
	#counter;
	#filterTitle;

	constructor(config = {
		wrapper: [],
		filter: [],
	})
	{
		EventEmitter.subscribe('BX.Main.Filter:apply', (event) => {

			let isSeen = this.#filter.getFilterFieldsValues()['IS_SEEN'];

			if(isSeen === 'N')
			{
				this.activateBtn();
			}
			else
			{
				this.deactivateBtn();
			}
		});

		this.#wrapper = config['wrapper'];
		this.#filter = config['filter'];
		this.#filterApi = this.#filter.getApi();
	}

	setCount(num)
	{
		num = Number(num);
		num = isNaN(num) ? 0 : num;

		if(num !== undefined)
		{
			this.#counter.textContent = num;

			if(num !== 0)
			{
				this.#counter.classList.remove('mail-counter-zero');
			}
			else
			{
				this.#counter.classList.add('mail-counter-zero');
			}

		}
	}

	activateBtn()
	{
		this.#statusBtn = true;
		this.#counterBtn.classList.add('mail-msg-counter-number-selected');
	}

	deactivateBtn()
	{
		this.#statusBtn = false;
		this.#counterBtn.classList.remove('mail-msg-counter-number-selected');
	}

	onClickFilterButton()
	{
		if(!this.#statusBtn)
		{
			this.activateBtn();
			this.setUnreadFilter();
		}
		else
		{
			this.deactivateBtn();
			this.removeUnreadFilter();
		}
	}

	removeUnreadFilter()
	{
		if (!!this.#filter && (this.#filter instanceof BX.Main.Filter))
		{
			this.#filterApi.setFields({
				'DIR': this.#filter.getFilterFieldsValues()['DIR'],
			});
			this.#filterApi.apply();
		}
	}

	hideReadAllBtn()
	{
		this.#readAllBtn.classList.add('mail-toolbar-hide-element');
	}

	showReadAllBtn()
	{
		this.#readAllBtn.classList.remove('mail-toolbar-hide-element');
	}

	hideCounter()
	{
		this.#counterBtn.classList.add('mail-toolbar-hide-element');
		this.#filterTitle.classList.add('mail-toolbar-hide-element');
	}

	showCounter()
	{
		this.#counterBtn.classList.remove('mail-toolbar-hide-element');
		this.#filterTitle.classList.remove('mail-toolbar-hide-element');
	}

	setUnreadFilter()
	{
		if (!!this.#filter && (this.#filter instanceof BX.Main.Filter))
		{
			this.#filterApi.setFields({
				'DIR': this.#filter.getFilterFieldsValues()['DIR'],
				'IS_SEEN': 'N'
			});
			this.#filterApi.apply();
		}
	}

	build(config ={
		filterId: '',
	})
	{
		const mailFilterToolbar = Tag.render`<div class="mail-filter-toolbar">
			<div class="mail-filter-counter" data-role="mail-filter-counter">
				<div data-role="mail-filter-title">
					${Loc.getMessage("MAIL_FILTER_TOOLBAR_TITLE")}
				</div>
			</div>
		</div>`;

		const counterBtn = Tag.render`<span class="mail-toolbar-counter">
			<span class="mail-msg-counter-number" data-role="unread-counter-number"></span>
			<span class="mail-msg-counter-text">${Loc.getMessage("MAIL_FILTER_NOT_READ")}</span>
			<span class="mail-msg-counter-remove"></span>
		</span>`;

		const readAllBtn = Tag.render`<span class="mail-toolbar-counter">
			<span class="mail-msg-counter-text">${Loc.getMessage("MAIL_FILTER_READ_ALL")}</span>
		</span>`;

		this.#counter = counterBtn.querySelector('[data-role="unread-counter-number"]');
		this.#filterTitle = mailFilterToolbar.querySelector('[data-role="mail-filter-title"]');

		this.#readAllBtn = readAllBtn;
		this.#counterBtn = counterBtn;

		counterBtn.onclick = ()=>
		{
			this.onClickFilterButton()
		}

		readAllBtn.onclick = ()=>
		{
			BX.Mail.Client.Message.List['mail-client-list-manager'].onReadClick('all');
			this.removeUnreadFilter();
		}

		const mailFilterCounter = mailFilterToolbar.querySelector('[data-role="mail-filter-counter"]');

		mailFilterCounter.append(counterBtn);
		mailFilterCounter.append(readAllBtn);
		this.#wrapper.append(mailFilterToolbar);

		EventEmitter.subscribe('BX.Mail.Home:updatingCounters', function(event) {

			if(event['data']['name'] === 'dirs')
			{
				const counters = event['data']['counters'];
				const hidden = event['data']['hidden'];
				const currentDir = event['data']['selectedDirectory'];
				let currentFolderCount = counters[currentDir];

				if(currentDir !== '')
				{
					this.showReadAllBtn()
				}
				else
				{
					currentFolderCount = event['data']['total'];
					this.hideReadAllBtn()
				}

				if(hidden[currentDir] && currentDir !== '')
				{
					this.hideCounter();
				}
				else
				{
					this.setCount(currentFolderCount);
					this.showCounter();
				}
			}
		}.bind(this));

	}
}