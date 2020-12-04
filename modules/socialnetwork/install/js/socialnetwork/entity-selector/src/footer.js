import { Type, Tag, Loc, Runtime, Dom, Validation } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { DefaultFooter } from 'ui.entity-selector';
import type { Dialog, TabOptions } from 'ui.entity-selector';

export default class Footer extends DefaultFooter
{
	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.handleDialogDestroy = this.handleDialogDestroy.bind(this);
		this.handleSliderMessage = this.handleSliderMessage.bind(this);

		this.bindEvents();
	}

	getContent(): HTMLElement | HTMLElement[] | string | null
	{
		return this.cache.remember('content', () => {
			const inviteEmployeeLink = this.getOption('inviteEmployeeLink');
			const inviteGuestLink = this.getOption('inviteGuestLink');

			if (inviteEmployeeLink && inviteGuestLink)
			{
				const phrase =
					Tag.render`<div>${Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_EMPLOYEE_OR_GUEST')}</div>`
				;

				const employee = phrase.querySelector('employee');
				const guest = phrase.querySelector('guest');
				const spans = Array.from(phrase.querySelectorAll('span'));

				phrase.replaceChild(
					Tag.render`
						<span 
							class="ui-selector-footer-link ui-selector-footer-link-add" 
							onclick="${this.handleInviteEmployeeClick.bind(this)}">${
							employee.innerHTML
						}</span>
					`,
					employee
				);

				const guestLink = Tag.render`
					<span 
						class="ui-selector-footer-link" 
						onclick="${this.handleInviteGuestClick.bind(this)}">${
						guest.innerHTML
					}</span>
				`;

				phrase.replaceChild(guestLink, guest);
				this.createHint(guestLink);

				spans.forEach(span => {
					phrase.replaceChild(
						Tag.render`<span class="ui-selector-footer-conjunction">${span.innerHTML}</span>`,
						span
					);
				});

				// Get rid of the outer <div>
				const fragment = document.createDocumentFragment();
				Array.from(phrase.childNodes).forEach(element => {
					fragment.appendChild(element);
				});

				return fragment;
			}
			else if (inviteEmployeeLink)
			{
				return Tag.render`
					<span 
						class="ui-selector-footer-link ui-selector-footer-link-add" 
						onclick="${this.handleInviteEmployeeClick.bind(this)}">${
						Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_EMPLOYEE')
					}</span>
				`;
			}
			else if (inviteGuestLink)
			{
				const guestLink = Tag.render`
					<span class="ui-selector-footer-link ui-selector-footer-link-add" 
						onclick="${this.handleInviteGuestClick.bind(this)}">${
						Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITE_GUEST')
					}</span>
				`;

				this.createHint(guestLink);

				return guestLink;
			}

			return null;
		});
	}

	createHint(link: HTMLElement): void
	{
		Runtime.loadExtension('ui.hint').then(() => {
			const hint = BX.UI.Hint.createInstance({ popupParameters: { zIndex: this.getDialog().getZindex() + 1 }});
			const node = hint.createNode(Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITED_GUEST_HINT'));
			Dom.insertAfter(node, link);
		});
	}

	handleInviteEmployeeClick(): void
	{
		const inviteEmployeeLink = this.getOption('inviteEmployeeLink');
		const zIndex = this.getDialog().getZindex() >= 3000 ? this.getDialog().getZindex() + 10 : null;

		if (Type.isStringFilled(inviteEmployeeLink))
		{
			const entity = this.getDialog().getEntity('user');
			const userOptions = entity.getOptions() || {};

			BX.SidePanel.Instance.open(
				inviteEmployeeLink,
				{
					zIndex,
					allowChangeHistory: false,
					cacheable: false,
					requestMethod: 'post',
					requestParams: {
						componentParams: JSON.stringify({
							'USER_OPTIONS': userOptions
						})
					},
					data: {
						entitySelectorId: this.getDialog().getId()
					}
				}
			);
		}
	}

	handleInviteGuestClick(): void
	{
		const inviteGuestLink = this.getOption('inviteGuestLink');
		const zIndex = this.getDialog().getZindex() >= 3000 ? this.getDialog().getZindex() + 10 : null;

		if (Type.isStringFilled(inviteGuestLink))
		{
			const entity = this.getDialog().getEntity('user');
			const userOptions = entity.getOptions() || {};

			const rows = [];
			const searchQuery = this.getDialog().getTagSelectorQuery();
			if (Validation.isEmail(searchQuery))
			{
				rows.push({ 'email': searchQuery });
			}

			BX.SidePanel.Instance.open(
				inviteGuestLink,
				{
					width: 1200,
					zIndex,
					allowChangeHistory: false,
					cacheable: false,
					requestMethod: 'post',
					requestParams: {
						componentParams: JSON.stringify({
							'USER_OPTIONS': userOptions,
							'ROWS': rows
						})
					},
					data: {
						entitySelectorId: this.getDialog().getId()
					}
				}
			);
		}
	}

	bindEvents(): void
	{
		this.getDialog().subscribe('onDestroy', this.handleDialogDestroy);
		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.handleSliderMessage);
	}

	unbindEvents(): void
	{
		this.getDialog().unsubscribe('onDestroy', this.handleDialogDestroy);
		EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.handleSliderMessage);
	}

	handleDialogDestroy(): void
	{
		this.unbindEvents();
	}

	handleSliderMessage(event: BaseEvent): void
	{
		const [messageEvent] = event.getData();
		const slider = messageEvent.getSender();

		if (slider.getData().get('entitySelectorId') !== this.getDialog().getId())
		{
			return;
		}

		if (
			messageEvent.getEventId() === 'BX.Intranet.Invitation:onAdd' ||
			messageEvent.getEventId() === 'BX.Intranet.Invitation.Guest:onAdd'
		)
		{
			const { users } = messageEvent.getData();
			this.addUsers(users);
		}
	}

	addUsers(users: Array): void
	{
		if (!Type.isArrayFilled(users))
		{
			return;
		}

		let tab = this.getDialog().getRecentTab() || this.getDialog().getTab('invited-users');
		if (!tab)
		{
			tab = this.getDialog().addTab(this.createTab());
		}

		users.forEach(user => {
			if (!Type.isPlainObject(user))
			{
				return;
			}

			const item = this.getDialog().addItem(
				Object.assign({}, user, { tabs: tab.getId(), sort: 2 })
			);

			if (item)
			{
				item.select();
			}
		});

		this.getDialog().selectTab(tab.getId());
	}

	createTab(): TabOptions
	{
		const icon =
			'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2217%22%20height%3D%2217%22%20fill%' +
			'3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20fill-rule%3D%22evenodd' +
			'%22%20clip-rule%3D%22evenodd%22%20d%3D%22M8.447%200a8.447%208.447%200%20100%2016.894A8.447%208' +
			'.447%200%20008.447%200zM2.785%2013.289c-.02.018-.116.09-.234.177a7.704%207.704%200%2001-1.846' +
			'-5.02c0-.36.025-.717.073-1.068.442.572.927%201.346%201.094%201.578.272.379.575.751.84%201.136.' +
			'444.648.91%201.535.673%202.34-.102.346-.328.627-.6.857zm8.92-6.27s.402%201.575%202.122%201.445c.' +
			'544-.041.509%201.413.162%202.014-.43.744-.318%201.627-.725%202.37-.256.467-.69.814-1.035%201.214-' +
			'.43.5-.913.984-1.631.921-.474-.04-.67-.565-.763-.939-.23-.928-.39-2.828-.39-2.828s-.668-1.443-2.' +
			'177-1.003c-1.509.44-1.816-.728-1.859-1.84C5.307%205.63%207.614%205.34%207.881%205.389c.383.069%201' +
			'.507.262%201.79.418.956.528%201.935-.2%201.858-.585-.077-.385-2.453-.939-3.296-.999-.842-.06-.92.24' +
			'-1.151-.014-.187-.205-.015-.53.116-.703.93-1.225%202.48-1.522%203.791-2.16.02-.01.051-.08.087-.184a7' +
			'.72%207.72%200%20012.846%201.81%207.719%207.719%200%20011.894%203.091c-.28.165.277-.057-1.185.284-1' +
			'.462.34-2.926.672-2.926.672z%22%20fill%3D%22%23ACB2B8%22/%3E%3C/svg%3E'
		;

		return {
			id: 'invited-users',
			title: Loc.getMessage('SOCNET_ENTITY_SELECTOR_INVITED_USERS_TAB_TITLE'),
			bgColor: {
				selected: '#f7a700',
				selectedHovered:  '#faac09',
			},
			icon: {
				//default: '/bitrix/js/socialnetwork/entity-selector/src/images/invited-users-tab-icon.svg',
				//selected: '/bitrix/js/socialnetwork/entity-selector/src/images/invited-users-tab-icon-selected.svg'
				default: icon,
				selected: icon.replace(/ACB2B8/g, 'fff'),
			}
		};
	}
}