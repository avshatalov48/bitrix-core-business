import {Dom, Loc, Tag} from "main.core";
import SlotItem from "./slot-item";
import {EventEmitter} from "main.core.events";
import Base from '../base';

type SlotListOptions = {
	isHiddenOnStart: boolean,
}
export default class SlotList extends Base
{
	#layout;
	#slots;
	#selectedSlot;
	constructor(options: SlotListOptions)
	{
		super({isHiddenOnStart: options.isHiddenOnStart});
		this.#layout = {
			title: null,
			list: null,
		}
		this.#slots = [];

		this.#bindEvents();
	}

	#bindEvents()
	{
		EventEmitter.subscribe('updateSlotsList', (event) => {
			this.#slots = event.data.slots;
			this.updateSlotsList();
		});
		EventEmitter.subscribe('selectSlot', (event)=> {
			const newSelectedSlot = event.data;
			if (this.#selectedSlot !== newSelectedSlot)
			{
				this.#selectedSlot?.unSelect();
			}

			this.#selectedSlot = newSelectedSlot;
		});
	}

	getType()
	{
		return 'slot-list';
	}

	getContent(): HTMLElement
	{
		return this.#getNodeSlotList();
	}

	updateSlotsList()
	{
		Dom.clean(this.#getNodeList());

		const slotListNode = this.#getNodeListItems();

		Dom.append(slotListNode, this.#getNodeList());
		Dom.removeClass(this.#getNodeList(), '--shadow-top');
		Dom.removeClass(this.#getNodeList(), '--shadow-bottom');
	}

	#getNodeSlotList(): HTMLElement
	{
		if (!this.#layout.slotSelector)
		{
			this.#layout.slotSelector = Tag.render`
				<div class="calendar-pub__slot-list-wrap">
					${this.#getNodeTitle()}
					${this.#getNodeList()}
				</div>
			`;
		}

		return this.#layout.slotSelector;
	}

	#getNodeTitle(): HTMLElement
	{
		if (!this.#layout.title)
		{
			this.#layout.title = Tag.render`
				<div class="calendar-sharing__calendar-bar">
					<div class="calendar-pub-ui__typography-m">${Loc.getMessage('CALENDAR_SHARING_SLOTS_FREE')}</div>
				</div>
			`;
		}

		return this.#layout.title;
	}

	#getNodeList(): HTMLElement
	{
		if (!this.#layout.slots)
		{
			this.#layout.slots = Tag.render`
				<div class="calendar-sharing__calendar-block --overflow-hidden --shadow">
					${this.#getNodeListItems()}
				</div>
			`;
		}

		return this.#layout.slots;
	}

	#getNodeListItems(): HTMLElement
	{
		const currentDaySlots = this.#slots
			.map(slot => new SlotItem({
				value: {
					from: slot.timeFrom,
					to: slot.timeTo,
				}
			}));

		const result = Tag.render`
			<div class="calendar-sharing__slots">
				${currentDaySlots.map(slotItem => slotItem.render())}
			</div>
		`;

		result.addEventListener('scroll', ()=> {
			if (result.scrollTop > 0)
			{
				this.#getNodeList().classList.add('--shadow-top');
			}
			else
			{
				this.#getNodeList().classList.remove('--shadow-top');
			}

			if (result.scrollHeight > result.offsetHeight
				&& Math.ceil(result.offsetHeight + result.scrollTop) < result.scrollHeight)
			{
				this.#getNodeList().classList.add('--shadow-bottom');
			}
			else
			{
				this.#getNodeList().classList.remove('--shadow-bottom');
			}
		});

		setTimeout(()=> {
			if (result.scrollHeight > result.offsetHeight)
			{
				this.#getNodeList().classList.add('--shadow-bottom');
			}
		});

		return result;
	}
}