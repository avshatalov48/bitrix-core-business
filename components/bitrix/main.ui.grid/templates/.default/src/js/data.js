(function() {
	'use strict';

	BX.namespace('BX.Grid');

	const originalUpdatePageData = window.parent.BX.ajax.UpdatePageData;

	function disableBxAjaxUpdatePageData()
	{
		window.parent.BX.ajax.UpdatePageData = function() {};
	}

	function enableBxAjaxUpdatePageData()
	{
		window.parent.BX.ajax.UpdatePageData = originalUpdatePageData;
	}

	/**
	 * Works with requests and server response
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.Data = function(parent)
	{
		this.parent = parent;
		this.reset();
	};

	/**
	 * Reset to default values
	 * @private
	 */
	BX.Grid.Data.prototype.reset = function()
	{
		this.response = null;
		this.xhr = null;
		this.headRows = null;
		this.bodyRows = null;
		this.footRows = null;
		this.moreButton = null;
		this.pagination = null;
		this.counterDisplayed = null;
		this.counterSelected = null;
		this.counterTotal = null;
		this.limit = null;
		this.actionPanel = null;
		this.rowsByParentId = {};
		this.rowById = {};
		this.isValidResponse = null;
	};

	/**
	 * Gets filter
	 * @return {BX.Main.Filter}
	 */
	BX.Grid.Data.prototype.getParent = function()
	{
		return this.parent;
	};

	/**
	 * Validates server response
	 * @return {boolean}
	 */
	BX.Grid.Data.prototype.validateResponse = function()
	{
		if (!BX.type.isBoolean(this.isValidResponse))
		{
			this.isValidResponse = Boolean(this.getResponse()) && Boolean(BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classContainer'), true));
		}

		return this.isValidResponse;
	};

	/**
	 * Send request
	 * @param {string} [url]
	 * @param {string} [method]
	 * @param {object} [data]
	 * @param {string} [action]
	 * @param {function} [then]
	 * @param {function} [error]
	 */
	BX.Grid.Data.prototype.request = function(url, method, data, action, then, error)
	{
		if (!BX.type.isString(url))
		{
			url = '';
		}

		if (!BX.type.isNotEmptyString(method))
		{
			method = 'GET';
		}

		if (!BX.type.isPlainObject(data))
		{
			data = {};
		}

		const eventArgs =			{
			gridId: this.parent.getId(),
			url,
			method,
			data,
		};

		this.parent.disableCheckAllCheckboxes();
		BX.onCustomEvent(
			window,
			'Grid::beforeRequest',
			[this, eventArgs],
		);

		if (eventArgs.hasOwnProperty('cancelRequest') && eventArgs.cancelRequest === true)
		{
			return;
		}

		url = eventArgs.url;

		if (!BX.type.isNotEmptyString(url))
		{
			url = this.parent.baseUrl;
		}

		url = BX.Grid.Utils.addUrlParams(url, { sessid: BX.bitrix_sessid(), internal: 'true', grid_id: this.parent.getId() });

		if ('apply_filter' in data && data.apply_filter === 'Y')
		{
			url = BX.Grid.Utils.addUrlParams(url, { apply_filter: 'Y' });
		}
		else
		{
			url = BX.util.remove_url_param(url, 'apply_filter');
		}

		if ('clear_nav' in data && data.clear_nav === 'Y')
		{
			url = BX.Grid.Utils.addUrlParams(url, { clear_nav: 'Y' });
		}
		else
		{
			url = BX.util.remove_url_param(url, 'clear_nav');
		}

		url = BX.Grid.Utils.addUrlParams(url, { grid_action: action || 'showpage' });

		method = eventArgs.method;
		data = eventArgs.data;

		this.reset();

		const self = this;

		setTimeout(() => {
			const formData = BX.Http.Data.convertObjectToFormData(data);

			disableBxAjaxUpdatePageData();

			var xhr = BX.ajax({
				url: BX.Grid.Utils.ajaxUrl(url, self.getParent().getAjaxId()),
				data: formData,
				method,
				dataType: 'html',
				headers: [
					{ name: 'X-Ajax-Grid-UID', value: self.getParent().getAjaxId() },
					{ name: 'X-Ajax-Grid-Req', value: JSON.stringify({ action: action || 'showpage' }) },
				],
				processData: true,
				scriptsRunFirst: false,
				start: false,
				preparePost: false,
				onsuccess(response) {
					self.response = BX.create('div', { html: response });
					self.response = self.response.querySelector(`#${self.parent.getContainerId()}`);
					self.xhr = xhr;

					if (self.parent.getParam('HANDLE_RESPONSE_ERRORS'))
					{
						let res;

						try
						{
							res = JSON.parse(response);
						}
						catch
						{
							res = { messages: [] };
						}

						if (res.messages.length > 0)
						{
							self.parent.arParams.MESSAGES = res.messages;
							self.parent.messages.show();

							self.parent.tableUnfade();

							if (BX.type.isFunction(error))
							{
								BX.delegate(error, self)(xhr);
							}

							return;
						}
					}

					if (BX.type.isFunction(then))
					{
						self.parent.enableCheckAllCheckboxes();
						BX.delegate(then, self)(response, xhr);
					}

					enableBxAjaxUpdatePageData();
				},
				onerror(err) {
					self.error = error;
					self.xhr = xhr;

					if (BX.type.isFunction(error))
					{
						self.parent.enableCheckAllCheckboxes();
						BX.delegate(error, self)(xhr, err);
					}
				},
			});

			xhr.send(formData);
		}, 0);
	};

	/**
	 * Gets server response
	 * @return {?Element}
	 */
	BX.Grid.Data.prototype.getResponse = function()
	{
		return this.response;
	};

	/**
	 * Returns a grid container
	 * @return {?Element}
	 */
	BX.Grid.Data.prototype.getContainer = function()
	{
		const className = this.getParent().settings.get('classContainer');
		if (BX.Dom.hasClass(this.getResponse(), className))
		{
			return this.getResponse();
		}

		return BX.Grid.Utils.getByClass(this.getResponse(), className, true);
	};

	/**
	 * Gets head rows of grid from server response
	 * @return {?HTMLTableRowElement[]}
	 */
	BX.Grid.Data.prototype.getHeadRows = function()
	{
		if (!this.headRows)
		{
			this.headRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classHeadRow'));
		}

		return this.headRows;
	};

	/**
	 * Gets body rows of grid form server request
	 * @return {?HTMLTableRowElement[]}
	 */
	BX.Grid.Data.prototype.getBodyRows = function()
	{
		if (!this.bodyRows)
		{
			this.bodyRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classBodyRow'));
		}

		return this.bodyRows;
	};

	/**
	 * Gets rows by parent id
	 * @param {string|number} id
	 * @return {?HTMLTableRowElement[]}
	 */
	BX.Grid.Data.prototype.getRowsByParentId = function(id)
	{
		if (!(id in this.rowsByParentId))
		{
			this.rowsByParentId[id] = BX.Grid.Utils.getBySelector(
				this.getResponse(),
				`.${this.getParent().settings.get('classBodyRow')}[data-parent-id="${id}"]`,
			);
		}

		return this.rowsByParentId[id];
	};

	/**
	 * Gets row by row id
	 * @param {string|number} id
	 * @return {?HTMLTableRowElement}
	 */
	BX.Grid.Data.prototype.getRowById = function(id)
	{
		if (!(id in this.rowById))
		{
			this.rowById[id] = BX.Grid.Utils.getBySelector(
				this.getResponse(),
				`.${this.getParent().settings.get('classBodyRow')}[data-id="${id}"]`,
				true,
			);
		}

		return this.rowById[id];
	};

	/**
	 * Gets tfoot rows of grid from request
	 * @return {?HTMLTableRowElement[]}
	 */
	BX.Grid.Data.prototype.getFootRows = function()
	{
		if (!this.footRows)
		{
			this.footRows = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classFootRow'));
		}

		return this.footRows;
	};

	/**
	 * Gets more button from request
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getMoreButton = function()
	{
		if (!this.moreButton)
		{
			this.moreButton = BX.Grid.Utils.getByClass(
				this.getResponse(),
				this.getParent().settings.get('classMoreButton'),
				true,
			);
		}

		return this.moreButton;
	};

	/**
	 * Gets pagination of grid from request
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getPagination = function()
	{
		if (!this.pagination)
		{
			this.pagination = BX.Grid.Utils.getByClass(
				this.getResponse(),
				this.getParent().settings.get('classPagination'),
				true,
			);

			if (BX.type.isDomNode(this.pagination))
			{
				this.pagination = BX.firstChild(this.pagination);
			}
		}

		return this.pagination;
	};

	/**
	 * Gets counter of displayed rows
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getCounterDisplayed = function()
	{
		if (!this.counterDisplayed)
		{
			this.counterDisplayed = BX.Grid.Utils.getByClass(
				this.getResponse(),
				this.getParent().settings.get('classCounterDisplayed'),
				true,
			);
		}

		return this.counterDisplayed;
	};

	/**
	 * Gets counter of selected rows
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getCounterSelected = function()
	{
		if (!this.counterSelected)
		{
			this.counterSelected = BX.Grid.Utils.getByClass(
				this.getResponse(),
				this.getParent().settings.get('classCounterSelected'),
				true,
			);
		}

		return this.counterSelected;
	};

	/**
	 * Gets counter of total rows count
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getCounterTotal = function()
	{
		if (!BX.type.isDomNode(this.counterTotal))
		{
			const selector = `.${this.getParent().settings.get('classCounterTotal')} .${this.getParent().settings.get('classPanelCellContent')}`;
			this.counterTotal = BX.Grid.Utils.getBySelector(this.getResponse(), selector, true);
		}

		return this.counterTotal;
	};

	/**
	 * Gets dropdown of pagesize
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getLimit = function()
	{
		if (!this.limit)
		{
			this.limit = BX.Grid.Utils.getByClass(this.getResponse(), this.getParent().settings.get('classPageSize'), true);
		}

		return this.limit;
	};

	/**
	 * Gets dropdown of pagesize
	 * @alias BX.Grid.Data.prototype.getLimit
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getPageSize = function()
	{
		return this.getLimit();
	};

	/**
	 * Gets action panel of grid
	 * @return {?HTMLElement}
	 */
	BX.Grid.Data.prototype.getActionPanel = function()
	{
		if (!this.actionPanel)
		{
			this.actionPanel = BX.Grid.Utils.getByClass(
				this.getResponse(),
				this.getParent().settings.get('classActionPanel'),
				true,
			);
		}

		return this.actionPanel;
	};
})();
