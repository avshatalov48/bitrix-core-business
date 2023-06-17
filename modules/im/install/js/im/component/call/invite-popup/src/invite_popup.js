import {Dom, Text, Type} from 'main.core';
import {Popup} from 'main.popup';

export class InvitePopup
{
	constructor(config)
	{
		if (!Type.isPlainObject(config))
		{
			config = {};
		}
		this.idleUsers = config.idleUsers || [];
		this.recentUsers = [];
		this.bindElement = config.bindElement;
		this.viewElement = config.viewElement || document.body;

		this.allowNewUsers = config.allowNewUsers;

		this.elements = {
			root: null,
			inputBox: null,
			input: null,
			destinationContainer: null,
			contactList: null,
			moreButton: null
		};

		this.popup = null;
		this.zIndex = config.zIndex || 0;
		this.darkMode = config.darkMode;

		this.searchPhrase = '';
		this.searchNext = 0;
		this.searchResult = [];
		this.searchTotalCount = 0;

		this.searchTimeout = 0;

		this.fetching = false;

		this.callbacks = {
			onSelect: Type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing,
			onClose: Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
		}
	}

	show()
	{
		if (!this.elements.root)
		{
			this.render();
		}
		this.createPopup();
		this.popup.show();

		if (this.allowNewUsers)
		{
			this.showLoader();
			this.getRecent().then(this.updateContactList.bind(this));
		}
		else
		{
			this.updateContactList();
		}
	}

	close()
	{
		if (this.popup)
		{
			this.popup.close();
		}
		clearTimeout(this.searchTimeout);
	}

	createPopup()
	{
		this.popup = new Popup({
			id: 'bx-call-popup-invite',
			bindElement: this.bindElement,
			targetContainer: this.viewElement,
			zIndex: this.zIndex,
			lightShadow: true,
			darkMode: this.darkMode,
			autoHide: true,
			closeByEsc: true,
			content: this.elements.root,
			bindOptions: {
				position: "top"
			},
			angle: {position: "bottom", offset: 49},
			cacheable: false,
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message("IM_CALL_INVITE_INVITE"),
					className: "popup-window-button-accept",
					events: {
						click: () =>
						{
							if (this.selectedUser)
							{
								this.callbacks.onSelect({
									user: this.selectedUser
								});
							}
						}
					}
				}),
				new BX.PopupWindowButton({
					text: BX.message("IM_CALL_INVITE_CANCEL"),
					events: {
						click: () => this.popup.close()
					}
				})
			],
			events: {
				onPopupDestroy: () =>
				{
					this.popup = null;
					this.elements.contactList = null;
					clearTimeout(this.searchTimeout);
					this.callbacks.onDestroy();
				}
			}
		});
		Dom.addClass(this.popup.popupContainer, "bx-messenger-mark");
	}

	getRecent()
	{
		return new Promise((resolve) =>
		{
			BX.rest.callMethod("im.recent.get", {
				"SKIP_OPENLINES": "Y",
				"SKIP_CHAT": "Y"
			}).then((response) =>
			{
				const answer = response.answer;
				this.recentUsers = Object.values(answer.result).map(r => r.user).filter(user => !user.bot && !user.network);
				resolve();
			});
		});
	}

	search()
	{
		return new Promise((resolve) =>
		{
			this.searchResult = this.recentUsers.filter((element) =>
			{
				return element.name.toString().toLowerCase().includes(this.searchPhrase.toLowerCase());
			});
			this.searchTotalCount = this.searchResult.length;
			this.searchNext = 0;

			if (this.searchPhrase.length < 3)
			{
				resolve();
				return;
			}

			BX.rest.callMethod("im.search.user.list", {"FIND": this.searchPhrase}).then((response) =>
			{
				const answer = response.answer;

				this.searchTotalCount = answer.total;
				this.searchNext = answer.next;

				const existsUserId = this.searchResult.map(element => parseInt(element.id));
				const result = Object.values(answer.result).filter((element) =>
				{
					if (element.bot || element.network)
					{
						return false;
					}
					return !existsUserId.includes(parseInt(element.id));
				});

				this.searchResult = this.searchResult.concat(result);
				this.searchTotalCount = this.searchResult.length;

				resolve();
			});
		});
	}

	fetchMoreSearchResults()
	{
		return new Promise((resolve) =>
		{
			BX.rest.callMethod("im.search.user.list", {
				"FIND": this.searchPhrase,
				"OFFSET": this.searchNext
			}).then((response) =>
			{
				const answer = response.answer;

				this.searchTotalCount = answer.total;
				this.searchNext = answer.next;

				const existsUserId = this.searchResult.map(element => parseInt(element.id));
				const result = Object.values(answer.result).filter((element) =>
				{
					if (element.bot || element.network)
					{
						return false;
					}
					return !existsUserId.includes(parseInt(element.id));
				});

				this.searchResult = this.searchResult.concat(result);
				this.searchTotalCount = this.searchResult.length;

				resolve(result);
			});
		})
	}

	render()
	{
		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-popup-newchat-wrap"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-popup-newchat-caption"},
					text: BX.message("IM_CALL_INVITE_INVITE_USER")
				})
			]
		});

		this.elements.inputBox = Dom.create("div", {
			props: {className: "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-dest bx-messenger-popup-newchat-dest-even"},
			children: [
				this.elements.destinationContainer = Dom.create("span", {
					props: {className: "bx-messenger-dest-items"}
				}),
				this.elements.input = Dom.create("input", {
					props: {className: "bx-messenger-input"},
					attrs: {
						type: "text",
						placeholder: this.allowNewUsers ? BX.message('IM_M_SEARCH_PLACEHOLDER') : BX.message('IM_M_CALL_REINVITE_PLACEHOLDER'),
						value: '',
						disabled: !this.allowNewUsers
					},
					events: {
						keyup: this._onInputKeyUp.bind(this)
					}
				})
			]
		});
		this.elements.root.appendChild(this.elements.inputBox);

		this.elements.contactList = Dom.create("div", {
			props: {className: "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-cl bx-messenger-recent-wrap"},
			children: []
		});

		this.elements.root.appendChild(this.elements.contactList);
	}

	updateDestination()
	{
		if (!this.elements.inputBox)
		{
			return;
		}

		Dom.clean(this.elements.destinationContainer);

		if (this.selectedUser)
		{
			this.elements.destinationContainer.appendChild(this.renderDestinationUser(this.selectedUser));
			this.elements.input.style.display = "none";
		}
		else
		{
			this.elements.input.style.removeProperty("display");
			this.elements.input.focus();
		}
	}

	updateContactList()
	{
		Dom.clean(this.elements.contactList);

		if (this.elements.contactList)
		{
			this.elements.contactList.appendChild(this.renderContactList());
		}
	}

	showLoader()
	{
		Dom.clean(this.elements.contactList);

		this.elements.contactList.appendChild(Dom.create("div", {
			props: {className: "bx-messenger-cl-item-load"},
			text: BX.message('IM_CL_LOAD')
		}));
	}

	renderContactList()
	{
		const result = document.createDocumentFragment();
		if (this.idleUsers.length > 0)
		{
			result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_CALL_PARTICIPANTS")));

			for (let i = 0; i < this.idleUsers.length; i++)
			{
				result.appendChild(this.#renderUser(this.idleUsers[i]));
			}
		}

		if (Type.isStringFilled(this.searchPhrase))
		{
			if (this.searchResult.length > 0)
			{
				result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_SEARCH_RESULTS")));
				for (let i = 0; i < this.searchResult.length; i++)
				{
					result.appendChild(this.#renderUser(this.searchResult[i]));
				}

				if (this.searchTotalCount > this.searchResult.length)
				{
					this.elements.moreButton = this.renderMoreButton();
					result.appendChild(this.elements.moreButton);
				}
			}
			else
			{

			}
		}
		else if (this.recentUsers.length > 0)
		{
			result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_RECENT")));
			for (let i = 0; i < this.recentUsers.length; i++)
			{
				result.appendChild(this.#renderUser(this.recentUsers[i]));
			}
		}
		return result;
	}

	/**
	 * @param {string} text
	 * @return {Element}
	 */
	renderSeparator(text)
	{
		return Dom.create("div", {
			props: {className: "bx-messenger-chatlist-group"},
			children: [
				Dom.create("span", {
					props: {className: "bx-messenger-chatlist-group-title"},
					text: text
				})
			]
		})
	}

	#renderUser(userData)
	{
		const element = BX.MessengerCommon.drawContactListElement({
			'id': userData.id,
			'data': this.escapeUserData(userData),
			'showUserLastActivityDate': true,
			'showLastMessage': false,
			'showCounter': false,
		});

		BX.bind(element, 'click', () => this.setSelectedUser(userData));

		return element;
	}

	renderDestinationUser(userData)
	{
		return Dom.create("span", {
			props: {className: "bx-messenger-dest-block"},
			children: [
				Dom.create("span", {
					props: {className: "bx-messenger-dest-text"},
					text: Text.decode(userData.name),
				}),
				Dom.create("span", {
					props: {className: "bx-messenger-dest-del"},
					events: {
						click: this.removeSelectedUser.bind(this)
					}
				})
			]
		})
	}

	renderMoreButton()
	{
		return Dom.create("div", {
			props: {className: "bx-messenger-chatlist-more-wrap"},
			events: {
				click: this._onMoreButtonClick.bind(this)
			},
			children: [
				Dom.create("span", {
					props: {className: "bx-messenger-chatlist-more"},
					text: BX.message("IM_CALL_INVITE_MORE") + " " + (this.searchTotalCount - this.searchResult.length)
				})
			]
		});
	}

	setSelectedUser(userData)
	{
		this.selectedUser = userData;
		this.updateDestination();
	}

	removeSelectedUser()
	{
		this.selectedUser = null;
		this.updateDestination();
	}

	escapeUserData(userData)
	{
		return {
			...userData,

			name: Text.encode(userData.name),
			first_name: Text.encode(userData.first_name),
			last_name: Text.encode(userData.last_name),
			work_position: Text.encode(userData.work_position),
			external_auth_id: Text.encode(userData.external_auth_id),
			status: Text.encode(userData.status),
		}
	}

	_onMoreButtonClick()
	{
		if (this.fetching)
		{
			return;
		}

		this.fetching = true;
		this.fetchMoreSearchResults().then((moreUsers) =>
		{
			const df = document.createDocumentFragment();
			let newMoreButton = null;
			for (let i = 0; i < moreUsers.length; i++)
			{
				df.appendChild(this.#renderUser(moreUsers[i]));
			}

			if (this.searchTotalCount > this.searchResult.length)
			{
				newMoreButton = this.renderMoreButton();
				df.appendChild(newMoreButton);
			}

			BX.replace(this.elements.moreButton, df);
			this.elements.moreButton = newMoreButton;

			this.fetching = false;
		});
	}

	_onInputKeyUp(event: KeyboardEvent)
	{
		if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
		{
			return false;
		}

		if (event.keyCode == 27 && this.elements.input.value !== '')
		{
			this.elements.input.value = '';
			event.stopPropagation();
		}

		if (this.searchTimeout)
		{
			clearTimeout(this.searchTimeout);
		}

		this.searchTimeout = setTimeout(
			() =>
			{
				this.searchPhrase = this.elements.input.value;
				if (!Type.isStringFilled(this.searchPhrase))
				{
					this.updateContactList();
				}
				else
				{
					this.search().then(() => this.updateContactList())
				}
			},
			300
		);
	}
}
