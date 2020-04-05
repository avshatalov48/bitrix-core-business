import { Dom } from 'main.core';
import Popup from '../popup/popup';
import { EventEmitter, BaseEvent } from 'main.core.events';

/**
 * @deprecated
 */
export default class InputPopup
{
	constructor(params)
	{
		this.id = params.id || 'bx-inp-popup-' + Math.round(Math.random() * 1000000);
		this.handler = params.handler || false;
		this.values = params.values || false;
		this.pInput = params.input;
		this.bValues = !!this.values;
		this.defaultValue = params.defaultValue || '';
		this.openTitle = params.openTitle || '';
		this.className = params.className || '';
		this.noMRclassName = params.noMRclassName || 'ec-no-rm';
		this.emptyClassName = params.noMRclassName || 'ec-label';

		const _this = this;
		this.curInd = false;

		if (this.bValues)
		{
			this.pInput.onfocus = this.pInput.onclick = function(e) {
				if (this.value == _this.defaultValue)
				{
					this.value = '';
					this.className = _this.className;
				}
				_this.ShowPopup();
				return e.preventDefault();
			};

			this.pInput.onblur = function() {
				if (_this.bShowed)
				{
					setTimeout(function() {
						_this.ClosePopup(true);
					}, 200);
				}
				_this.OnChange();
			};
		}
		else
		{
			this.pInput.className = this.noMRclassName;
			this.pInput.onblur = this.OnChange.bind(this);
		}
	}

	ShowPopup()
	{
		if (this.bShowed)
		{
			return;
		}

		const _this = this;
		if (!this.oPopup)
		{
			const pWnd = Dom.create('DIV', { props: { className: 'bxecpl-loc-popup ' + this.className } });

			for (let i = 0, l = this.values.length; i < l; i++)
			{
				const pRow = pWnd.appendChild(Dom.create('DIV', {
					props: { id: 'bxecmr_' + i },
					text: this.values[i].NAME,
					events: {
						mouseover: function() {
							Dom.addClass(this, 'bxecplloc-over');
						},
						mouseout: function() {
							Dom.removeClass(this, 'bxecplloc-over');
						},
						click: function() {
							const ind = this.id.substr('bxecmr_'.length);
							_this.pInput.value = _this.values[ind].NAME;
							_this.curInd = ind;
							_this.OnChange();
							_this.ClosePopup(true);
						}
					}
				}));

				if (this.values[i].DESCRIPTION)
				{
					pRow.title = this.values[i].DESCRIPTION;
				}
				if (this.values[i].CLASS_NAME)
				{
					Dom.addClass(pRow, this.values[i].CLASS_NAME);
				}

				if (this.values[i].URL)
				{
					pRow.appendChild(Dom.create('a', {
						props: {
							href: this.values[i].URL,
							className: 'bxecplloc-view',
							target: '_blank',
							title: this.openTitle
						}
					}));
				}
			}

			this.oPopup = new Popup(this.id, this.pInput, {
				autoHide: true,
				offsetTop: 1,
				offsetLeft: 0,
				lightShadow: true,
				closeByEsc: true,
				content: pWnd,
				events: {
					onClose: this.ClosePopup.bind(this)
				}
			});
		}

		this.oPopup.show();
		this.pInput.select();
		this.bShowed = true;

		EventEmitter.emit(this, 'onInputPopupShow', new BaseEvent({ compatData: [this] }));
	}

	ClosePopup(bClosePopup)
	{
		this.bShowed = false;

		if (this.pInput.value === '')
		{
			this.OnChange();
		}

		EventEmitter.emit(this, 'onInputPopupClose', new BaseEvent({ compatData: [this] }));

		if (bClosePopup === true)
		{
			this.oPopup.close();
		}
	}

	OnChange()
	{
		let val = this.pInput.value;
		if (this.bValues)
		{
			if (this.pInput.value == '' || this.pInput.value == this.defaultValue)
			{
				this.pInput.value = this.defaultValue;
				this.pInput.className = this.emptyClassName;
				val = '';
			}
			else
			{
				this.pInput.className = '';
			}
		}

		if (isNaN(parseInt(this.curInd)) || this.curInd !== false && val != this.values[this.curInd].NAME)
		{
			this.curInd = false;
		}
		else
		{
			this.curInd = parseInt(this.curInd);
		}

		EventEmitter.emit(
			this,
			'onInputPopupChanged',
			new BaseEvent({ compatData: [this, this.curInd, val] })
		);

		if (this.handler && typeof this.handler == 'function')
		{
			this.handler({ ind: this.curInd, value: val });
		}
	}

	Set(ind, val, bOnChange)
	{
		this.curInd = ind;
		if (this.curInd !== false)
		{
			this.pInput.value = this.values[this.curInd].NAME;
		}
		else
		{
			this.pInput.value = val;
		}

		if (bOnChange !== false)
		{
			this.OnChange();
		}
	}

	Get(ind)
	{
		let id = false;
		if (typeof ind == 'undefined')
		{
			ind = this.curInd;
		}

		if (ind !== false && this.values[ind])
		{
			id = this.values[ind].ID;
		}

		return id;
	}

	GetIndex(id)
	{
		for (let i = 0, l = this.values.length; i < l; i++)
		{
			if (this.values[i].ID == id)
			{
				return i;
			}
		}

		return false;
	}

	Deactivate(bDeactivate)
	{
		if (this.pInput.value == '' || this.pInput.value == this.defaultValue)
		{
			if (bDeactivate)
			{
				this.pInput.value = '';
				this.pInput.className = this.noMRclassName;
			}
			else if (this.oEC.bUseMR)
			{
				this.pInput.value = this.defaultValue;
				this.pInput.className = this.emptyClassName;
			}
		}

		this.pInput.disabled = bDeactivate;
	}
}