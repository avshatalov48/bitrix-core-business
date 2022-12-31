import { BookingUtil, Event, Loc, Dom } from 'calendar.resourcebooking';

export class UserSelectorFieldEditControl
{
	constructor(params)
	{
		this.params = params || {};
		this.id = this.params.id || 'user-selector-' + Math.round(Math.random() * 100000);
		this.wrapNode = this.params.wrapNode;
		this.destinationInputName = this.params.inputName || 'EVENT_DESTINATION';
		this.params.selectGroups = false;
		this.addMessage = this.params.addMessage || BX.message('USER_TYPE_RESOURCE_ADD_USER');
		this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;

		if (!this.params.itemsSelected)
		{
			this.params.itemsSelected = this.getSocnetDestinationConfig('itemsSelected');
		}

		this.DOM = {
			outerWrap: this.params.outerWrap,
			wrapNode: this.params.wrapNode
		};

		this.create();
	}

	create ()
	{
		if (this.DOM.outerWrap)
		{
			Dom.addClass(this.DOM.outerWrap, 'calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));
		}

		let id = this.id;

		BX.bind(this.wrapNode, 'click', BX.delegate(function (e)
		{
			let target = e.target || e.srcElement;
			if (target.className === 'calendar-resourcebook-content-block-control-delete') // Delete button
			{
				BX.SocNetLogDestination.deleteItem(target.getAttribute('data-item-id'), target.getAttribute('data-item-type'), id);
				let block = BX.findParent(target, {className: 'calendar-resourcebook-content-block-control-inner'});
				if (block && BX.hasClass(block, 'shown'))
				{
					BX.removeClass(block, 'shown');
					setTimeout(function(){BX.remove(block);}, 300);
				}
			}
			else
			{
				BX.SocNetLogDestination.openDialog(id);
			}
		}, this));

		this.socnetDestinationInputWrap = this.wrapNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-resourcebook-destination-input-box'}}));
		this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(BX.create('INPUT', {
			props: {
				id: id + '-inp',
				className: 'calendar-resourcebook-destination-input'
			},
			attrs: {
				value: '',
				type: 'text'
			},
			events: {
				keydown: function (e)
				{
					return BX.SocNetLogDestination.searchBeforeHandler(e, {
						formName: id, inputId: id + '-inp'
					});
				},
				keyup: function (e)
				{
					return BX.SocNetLogDestination.searchHandler(e, {
						formName: id,
						inputId: id + '-inp',
						linkId: 'event-grid-dest-add-link',
						sendAjax: true
					});
				}
			}
		}));

		this.socnetDestinationLink = this.wrapNode.appendChild(BX.create('DIV', {
			props: {className: 'calendar-resourcebook-content-block-control-text calendar-resourcebook-content-block-control-text-add'},
			text: this.addMessage
		}));

		this.init();
	}

	show ()
	{
		if (this.DOM.outerWrap)
		{
			Dom.addClass(this.DOM.outerWrap, 'shown');
		}
	}

	hide ()
	{
		if (this.DOM.outerWrap)
		{
			BX.removeClass(this.DOM.outerWrap, 'shown');
		}
	}

	isShown ()
	{
		if (this.DOM.outerWrap)
		{
			return BX.hasClass(this.DOM.outerWrap, 'shown');
		}
	}

	init ()
	{
		if (!this.socnetDestinationInput || !this.wrapNode)
			return;

		let _this = this;

		this.params.items = this.getSocnetDestinationConfig('items');
		this.params.itemsLast = this.getSocnetDestinationConfig('itemsLast');

		if (this.params.selectGroups === false)
		{
			this.params.items.groups = {};
			this.params.items.department = {};
			this.params.items.sonetgroups = {};
		}

		BX.SocNetLogDestination.init({
			name: this.id,
			searchInput: this.socnetDestinationInput,
			extranetUser: false,
			userSearchArea: 'I',
			bindMainPopup: {
				node: this.wrapNode, offsetTop: '5px', offsetLeft: '15px'
			},
			bindSearchPopup: {
				node: this.wrapNode, offsetTop: '5px', offsetLeft: '15px'
			},
			callback: {
				select: BX.proxy(this.selectCallback, this),
				unSelect: BX.proxy(this.unSelectCallback, this),
				openDialog: BX.proxy(this.openDialogCallback, this),
				closeDialog: BX.proxy(this.closeDialogCallback, this),
				openSearch: BX.proxy(this.openDialogCallback, this),
				closeSearch: function ()
				{
					_this.closeDialogCallback(true);
				}
			},
			items: this.params.items,
			itemsLast: this.params.itemsLast,
			itemsSelected: this.params.itemsSelected,
			departmentSelectDisable: this.params.selectGroups === false
		});
	}

	closeAll ()
	{
		if (BX.SocNetLogDestination.isOpenDialog())
		{
			BX.SocNetLogDestination.closeDialog();
		}
		BX.SocNetLogDestination.closeSearch();
	}

	selectCallback(item, type)
	{
		if (type === 'users')
		{
			this.addUserBlock(item);
			BX.onCustomEvent('OnResourceBookDestinationAddNewItem', [item, this.id]);
			this.socnetDestinationInput.value = '';
		}
	}

	addUserBlock(item, animation)
	{
		if (this.checkLimit && !this.checkLimit())
		{
			return BookingUtil.showLimitationPopup();
		}

		if (this.getAttendeesCodesList().includes(item.id))
		{
			return;
		}

		const blocks = this.wrapNode.querySelectorAll(`calendar-resourcebook-content-block-control-inner[data-id='${item.id}']`);
		for (let i = 0; i < blocks.length; i++)
		{
			BX.remove(blocks[i]);
		}

		const itemWrap = this.wrapNode.appendChild(BX.create("DIV", {
			attrs: {
				'data-id': item.id, className: "calendar-resourcebook-content-block-control-inner green"
			},
			html: '<div class="calendar-resourcebook-content-block-control-text">' + item.name + '</div>' + '<div data-item-id="' + item.id + '" data-item-type="users" class="calendar-resourcebook-content-block-control-delete"></div>' + '<input type="hidden" name="' + this.destinationInputName + '[U][]' + '" value="' + item.id + '">'
		}));

		if (animation !== false)
		{
			setTimeout(BX.delegate(function (){Dom.addClass(itemWrap, 'shown');}, this), 1);
		}
		else
		{
			Dom.addClass(itemWrap, 'shown');
		}

		this.wrapNode.appendChild(this.socnetDestinationInputWrap);
		this.wrapNode.appendChild(this.socnetDestinationLink);
	}

	unSelectCallback(item)
	{
		let elements = BX.findChildren(this.wrapNode, {attribute: {'data-id': item.id}}, true);
		if (elements != null)
		{
			for (let j = 0; j < elements.length; j++)
			{
				BX.remove(elements[j]);
			}
		}

		BX.onCustomEvent('OnResourceBookDestinationUnselect', [item, this.id]);
		this.socnetDestinationInput.value = '';
		this.socnetDestinationLink.innerHTML = this.addMessage;
	}

	openDialogCallback ()
	{
		BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
		BX.style(this.socnetDestinationLink, 'display', 'none');
		BX.focus(this.socnetDestinationInput);
	}

	closeDialogCallback(cleanInputValue)
	{
		if (!BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0)
		{
			BX.style(this.socnetDestinationInputWrap, 'display', 'none');
			BX.style(this.socnetDestinationLink, 'display', 'inline-block');
			if (cleanInputValue === true)
				this.socnetDestinationInput.value = '';

			// Disable backspace
			if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
				BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

			BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(e)
			{
				if (e.keyCode === 8)
				{
					e.preventDefault();
					return false;
				}
			});

			setTimeout(function()
			{
				BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
				BX.SocNetLogDestination.backspaceDisable = null;
			}, 5000);
		}
	}

	getCodes()
	{
		let
			inputsList = this.wrapNode.getElementsByTagName('INPUT'),
			codes = [], i, value;

		for (i = 0; i < inputsList.length; i++)
		{
			value = BX.util.trim(inputsList[i].value);
			if (value)
			{
				codes.push(inputsList[i].value);
			}
		}
		return codes;
	}

	getAttendeesCodes()
	{
		let
			inputsList = this.wrapNode.getElementsByTagName('INPUT'),
			values = [],
			i;

		for (i = 0; i < inputsList.length; i++)
		{
			values.push(inputsList[i].value);
		}

		return this.convertAttendeesCodes(values);
	}

	convertAttendeesCodes(values)
	{
		let attendeesCodes = {};

		if (BX.type.isArray(values))
		{
			values.forEach(function(code){
				if (code.substr(0, 2) === 'DR')
				{
					attendeesCodes[code] = "department";
				}
				else if (code.substr(0, 2) === 'UA')
				{
					attendeesCodes[code] = "groups";
				}
				else if (code.substr(0, 2) === 'SG')
				{
					attendeesCodes[code] = "sonetgroups";
				}
				else if (code.substr(0, 1) === 'U')
				{
					attendeesCodes[code] = "users";
				}
			});
		}

		return attendeesCodes;
	}

	getAttendeesCodesList(codes)
	{
		let result = [];
		if (!codes)
			codes = this.getAttendeesCodes();
		for (let i in codes)
		{
			if (codes.hasOwnProperty(i))
			{
				result.push(i);
			}
		}
		return result;
	}

	getSocnetDestinationConfig(key)
	{
		let
			res,
			socnetDestination = this.params.socnetDestination || {};

		if (key === 'items')
		{
			res = {
				users: socnetDestination.USERS || {},
				groups: socnetDestination.EXTRANET_USER === 'Y' || socnetDestination.DENY_TOALL
					? {}
					: {UA: {id: 'UA', name: BX.message('USER_TYPE_RESOURCE_TO_ALL_USERS')}},
				sonetgroups: socnetDestination.SONETGROUPS || {},
				department: socnetDestination.DEPARTMENT || {},
				departmentRelation: socnetDestination.DEPARTMENT_RELATION || {}
			};
		}
		else if (key === 'itemsLast' && socnetDestination.LAST)
		{
			res = {
				users: socnetDestination.LAST.USERS || {},
				groups: socnetDestination.EXTRANET_USER === 'Y' ? {} : {UA: true},
				sonetgroups: socnetDestination.LAST.SONETGROUPS || {},
				department: socnetDestination.LAST.DEPARTMENT || {}
			};
		}
		else if (key === 'itemsSelected')
		{
			res = socnetDestination.SELECTED || {};
		}
		return res || {};
	}

	getSelectedValues()
	{
		let
			result = [], i,
			inputs = this.wrapNode.querySelectorAll('input');

		for (i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type === 'hidden' && inputs[i].value)
			{
				if (inputs[i].value.substr(0, 1) === 'U')
				{
					result.push(parseInt(inputs[i].value.substr(1)));
				}
			}
		}

		return result;
	}

	setValues(userList, trigerOnChange)
	{
		let i, user;
		const blocks = this.wrapNode.querySelectorAll('.calendar-resourcebook-content-block-control-inner');
		for (i = 0; i < blocks.length; i++)
		{
			BX.remove(blocks[i]);
		}

		for (i = 0; i < userList.length; i++)
		{
			if (BX.SocNetLogDestination.obItems[this.id]['users'])
			{
				user = BX.SocNetLogDestination.obItems[this.id]['users']['U' + userList[i]];
				if (user)
				{
					this.addUserBlock({
						id: 'U' + userList[i],
						name: user.name
					}, false);
				}
			}
		}

		if (trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
		{
			setTimeout(BX.proxy(this.onChangeCallback, this), 100);
		}
	}

	getId()
	{
		return this.id;
	}
}