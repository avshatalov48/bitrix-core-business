import { ajax, AjaxError, AjaxResponse, Dom, Event, Tag, Text, Type } from 'main.core';
import { Popup } from 'main.popup';
import { Loader } from 'main.loader';
import { Member } from './membership-request-panel';

type Params = {
	groupId: number,
	bindElement: HTMLElement,
	pathToUser: string,
}

export class MembersList
{
	#popup: Popup;
	#loader: Loader;
	#groupId: number;
	#pathToUser: string;
	#pageNum: number;

	#listMembers: Set;

	constructor(params: Params)
	{
		this.#groupId = Type.isInteger(parseInt(params.groupId, 10)) ? parseInt(params.groupId, 10) : 0;

		this.#popup = this.#createPopup(params.bindElement);
		this.#pathToUser = params.pathToUser;

		this.#listMembers = new Set();

		this.#pageNum = 1;
	}

	show()
	{
		this.#popup.toggle();
	}

	#createPopup(bindElement: HTMLElement): Popup
	{
		const popup = new Popup({
			id: 'sn-mrp-members-popup',
			className: 'sn-mrp-members-popup',
			bindElement: bindElement,
			autoHide: true,
			closeByEsc: true,
			content: this.#renderPopupContent(),
			events: {
				onAfterPopupShow: () => {
					const popupContainer = popup.getContentContainer();
					const listContainer = popupContainer.querySelector('.sn-mrp-members-popup-content');
					const list = popupContainer.querySelector('.sn-mrp-members-popup-inner');

					this.#bindPopupScroll(list);

					this.#showLoader(listContainer);

					// eslint-disable-next-line promise/catch-or-return
					this.#appendMembers(list).then(() => {
						this.#destroyLoader();
						this.#bindPopupScroll(list);
					});
				},
			},
		});

		return popup;
	}

	#appendMembers(list: HTMLElement): Promise
	{
		// eslint-disable-next-line promise/catch-or-return
		return this.#getList()
			.then((listMembers: Array<Member>) => {
				listMembers.forEach((member: Member) => {
					this.#listMembers.add(member);
					Dom.append(this.#renderMember(member), list);
				});
			})
		;
	}

	#renderPopupContent(): HTMLElement
	{
		return Tag.render`
			<div class="sn-mrp-members-popup-container">
				<div class="sn-mrp-members-popup-content">
					<div class="sn-mrp-members-popup-content-box">
						<div class="sn-mrp-members-popup-inner"></div>
					</div>
				</div>
			</div>
		`;
	}

	#renderMember(member: Member): HTMLElement
	{
		let photoIcon = '<i></i>';
		if (member.photo)
		{
			photoIcon = `<i style="background-image: url('${encodeURI(member.photo)}')"></i>`;
		}

		return Tag.render`
			<a
				class="sn-mrp-members-popup-item"
				href="${Text.encode(this.#pathToUser.replace('#user_id#', member.id))}"
				data-id="sn-mrp-members-popup-item-${member.id}"
			>
				<span class="sn-mrp-members-popup-avatar-new">
					<div class="ui-icon ui-icon-common-user sn-mrp-members-popup-avatar-img">
						${photoIcon}
					</div>
					<span></span>
				</span>
				<span class="sn-mrp-members-popup-name">
					${Text.encode(member.name)}
				</span>
			</a>
		`;
	}

	#bindPopupScroll(list: HTMLElement)
	{
		Event.bind(list, 'scroll', () => {
			if (list.scrollTop > (list.scrollHeight - list.offsetHeight) / 1.5)
			{
				this.#appendMembers(list);

				Event.unbindAll(list, 'scroll');
			}
		});
	}

	#showLoader(target: HTMLElement)
	{
		this.#loader = new Loader({
			target: target,
			size: 40,
		});

		this.#loader.show();
	}

	#destroyLoader()
	{
		this.#loader.destroy();
	}

	#getList(): Promise
	{
		return new Promise((resolve) => {
			ajax.runAction('socialnetwork.api.workgroup.getListIncomingUsers', {
				data: {
					groupId: this.#groupId,
					pageNum: this.#pageNum,
				},
			})
				.then((response: AjaxResponse) => {
					this.#pageNum++;
					resolve(response.data);
				})
				.catch((error: AjaxError) => {
					this.#consoleError('getList', error);
				})
			;
		});
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`MembershipRequestPanel.MembersList: ${action} error`, error);
	}
}
