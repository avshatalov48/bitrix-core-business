import {Type, Dom} from 'main.core';
import {Util} from 'calendar.util';

export class UserSelector
{
	constructor(params = {})
	{
		this.params = params;
		this.id = params.id || 'user_selector_' + Math.round(Math.random() * 1000000);
		this.zIndex = params.zIndex || 3100;

		this.DOM = {
			wrapNode: params.wrapNode
		};
		this.destinationInputName = params.inputName || 'EVENT_DESTINATION';

		if (Type.isArray(this.params.itemsSelected) && this.params.itemsSelected.length)
		{
			this.params.itemsSelected = this.convertAttendeesCodes(this.params.itemsSelected);
		}

		this.create();
	}

	create()
	{
		let id = this.id;
		this.DOM.socnetDestinationWrap = this.DOM.wrapNode.appendChild(Dom.create('DIV', {
			props: {className: 'event-grid-dest-wrap'},
			events: {
				click : (e) => {
					BX.SocNetLogDestination.openDialog(id);
				}
			}
		}));

		this.socnetDestinationItems = this.DOM.socnetDestinationWrap.appendChild(Dom.create('SPAN', {
			props: {className: ''},
			events: {
				click : function(e)
				{
					var targ = e.target || e.srcElement;
					if (targ.className === 'feed-event-del-but') // Delete button
					{
						top.BX.SocNetLogDestination.deleteItem(targ.getAttribute('data-item-id'), targ.getAttribute('data-item-type'), id);
						e.preventDefault();
						e.stopPropagation();
					}
				},
				mouseover: function(e)
				{
					var targ = e.target || e.srcElement;
					if (targ.className === 'feed-event-del-but') // Delete button
						BX.addClass(targ.parentNode, 'event-grid-dest-hover');
				},
				mouseout: function(e)
				{
					var targ = e.target || e.srcElement;
					if (targ.className === 'feed-event-del-but') // Delete button
						BX.removeClass(targ.parentNode, 'event-grid-dest-hover');
				}
			}
		}));

		this.socnetDestinationInputWrap = this.DOM.socnetDestinationWrap.appendChild(Dom.create('SPAN', {props: {className: 'feed-add-destination-input-box'}}));
		this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(
			Dom.create('INPUT', {
				props: {id: id + '-inp', className: 'feed-add-destination-inp'},
				attrs: {value: '', type: 'text'},
				events: {
					keydown : function(e){
						return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
							formName: id,
							inputId: id + '-inp'
						});
					},
					keyup : function(e){
						return top.BX.SocNetLogDestination.searchHandler(e, {
							formName: id,
							inputId: id + '-inp',
							linkId: 'event-grid-dest-add-link',
							sendAjax: true
						});
					}
				}
			})
		);
		this.socnetDestinationLink = this.DOM.socnetDestinationWrap.appendChild(Dom.create('SPAN', {
			html: this.params.addLinkMessage || BX.message('EC_DESTINATION_ADD_USERS'),
			props: {id: id + '-link', className: 'feed-add-destination-link'},
			events: {
				keydown : function(e){
					return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
						formName: id,
						inputId: id + '-inp'
					});
				},
				keyup : function(e){
					return top.BX.SocNetLogDestination.searchHandler(e, {
						formName: id,
						inputId: id + '-inp',
						linkId: 'event-grid-dest-add-link',
						sendAjax: true
					});
				}
			}
		}));

		// if (this.params.itemsSelected && !this.checkItemsSelected(
		// 	this.params.items,
		// 	this.params.itemsLast,
		// 	this.params.itemsSelected,
		// 	BX.proxy(this.init, this)
		// ))
		// {
		// 	return;
		// }

		this.init();
	}

	init()
	{
		if (!this.socnetDestinationInput || !this.DOM.socnetDestinationWrap || !this.params.items)
		{
			return;
		}

		if(this.params.selectGroups === false)
		{
			this.params.items.groups = {};
			this.params.items.department = {};
			this.params.items.sonetgroups = {};
		}

		if(this.params.selectUsers === false)
		{
			this.params.items.users = {};
			this.params.items.groups = {};
			this.params.items.department = {};
		}

		BX.SocNetLogDestination.init({
			name : this.id,
			searchInput : this.socnetDestinationInput,
			extranetUser :  false,
			userSearchArea: 'I',
			bindMainPopup : {
				node : this.DOM.socnetDestinationWrap,
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node : this.DOM.socnetDestinationWrap,
				offsetTop : '5px',
				offsetLeft: '15px'
			},
			callback : {
				select : this.selectCallback.bind(this),
				unSelect : this.unSelectCallback.bind(this),
				openDialog : this.openDialogCallback.bind(this),
				closeDialog : this.closeDialogCallback.bind(this),
				openSearch : this.openDialogCallback.bind(this),
				closeSearch : ()=>{this.closeDialogCallback(true);}
			},
			items : this.params.items,
			itemsLast : this.params.itemsLast,
			itemsSelected : this.params.itemsSelected,
			departmentSelectDisable: this.params.selectGroups === false
		});
	}

	closeAll()
	{
		if (top.BX.SocNetLogDestination.isOpenDialog())
		{
			top.BX.SocNetLogDestination.closeDialog();
		}
		top.BX.SocNetLogDestination.closeSearch();
	}

	selectCallback(item, type)
	{
		var
			type1 = type,
			prefix = 'S';

		if (type === 'sonetgroups')
		{
			prefix = 'SG';
		}
		else if (type === 'groups')
		{
			prefix = 'UA';
			type1 = 'all-users';
		}
		else if (type === 'users')
		{
			prefix = 'U';
		}
		else if (type === 'department')
		{
			prefix = 'DR';
		}

		this.socnetDestinationItems.appendChild(
			Dom.create("span", { attrs : {'data-id' : item.id }, props : {className : "event-grid-dest event-grid-dest-" + type1 }, children: [
					Dom.create("input", { attrs : {type : 'hidden', name : this.destinationInputName + '[' + prefix + '][]', value : item.id }}),
					Dom.create("span", { props : {className : "event-grid-dest-text" }, html : item.name}),
					Dom.create("span", { props : {className : "feed-event-del-but"}, attrs: {'data-item-id': item.id, 'data-item-type': type}})
				]})
		);

		BX.onCustomEvent('OnDestinationAddNewItem', [item]);
		this.socnetDestinationInput.value = '';
		this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	}

	unSelectCallback(item, type, search)
	{
		var elements = BX.findChildren(this.socnetDestinationItems, {attribute: {'data-id': item.id}}, true);
		if (elements != null)
		{
			for (var j = 0; j < elements.length; j++)
			{
				BX.remove(elements[j]);
			}
		}

		BX.onCustomEvent('OnDestinationUnselect');
		this.socnetDestinationInput.value = '';
		this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	}

	openDialogCallback()
	{
		BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
		BX.style(this.socnetDestinationLink, 'display', 'none');
		BX.focus(this.socnetDestinationInput);
	}

	closeDialogCallback(cleanInputValue)
	{
		if (!top.BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0)
		{
			BX.style(this.socnetDestinationInputWrap, 'display', 'none');
			BX.style(this.socnetDestinationLink, 'display', 'inline-block');
			if (cleanInputValue === true)
				this.socnetDestinationInput.value = '';

			// Disable backspace
			if (top.BX.SocNetLogDestination.backspaceDisable || top.BX.SocNetLogDestination.backspaceDisable != null)
				BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);

			BX.bind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable = function(e)
			{
				if (e.keyCode === 8)
				{
					e.preventDefault();
					return false;
				}
			});

			setTimeout(function()
			{
				BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);
				top.BX.SocNetLogDestination.backspaceDisable = null;
			}, 5000);
		}
	}

	getCodes()
	{
		var
			inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
			codes = [], i;

		for (i = 0; i < inputsList.length; i++)
		{
			codes.push(inputsList[i].value);
		}
		return codes;
	}

	getAttendeesCodes()
	{
		var
			inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
			values = [],
			i, code;

		for (i = 0; i < inputsList.length; i++)
		{
			values.push(inputsList[i].value);
		}

		return this.convertAttendeesCodes(values);
	}

	convertAttendeesCodes(values)
	{
		let attendeesCodes = {};
		if (Type.isArray(values))
		{
			values.forEach(function(code)
			{
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

	setValue(value)
	{
		if (this.socnetDestinationItems)
		{
			Dom.clean(this.socnetDestinationItems);
		}

		if (Type.isArray(value))
		{
			this.params.itemsSelected = this.convertAttendeesCodes(value);
		}
		this.init();
	}
}