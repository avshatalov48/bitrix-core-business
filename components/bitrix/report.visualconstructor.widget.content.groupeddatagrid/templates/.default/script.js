;(function(){
	BX.namespace('BX.VisualConstructor');
	BX.VisualConstructor.GroupedDataGrid =  function(options)
	{
		this.context = options.context;
		this.rows = this.context.querySelectorAll('[data-role="report-operator"]') || [];
		this.blockContainer = this.context.querySelector('[data-role="report-widget-triple-data-with-progress"]');
		this.wrapper = this.context.querySelector('[data-role="report-widget-triple-data-with-progress-wrapper"]');
		this.actionControlNode = this.context.querySelector('[data-role="reports-more-users"]');
		this.containerHeight = null;
		this.layout = {
			container: null,
			header: null,
			content: null
		};

		this.init();
	};


	BX.VisualConstructor.GroupedDataGrid.prototype = {
		init: function ()
		{
			for(var i = 0; i < this.rows.length; i++)
			{
				BX.bind(this.getHeader(this.rows[i]), 'click', this.handleClickOnRow.bind(this, this.rows[i]))
			}
			BX.bind(this.actionControlNode, 'click', this.toggleWidgetHeight.bind(this));

		},
		handleClickOnRow: function(row)
		{
			this.expendWidgetHeight();

			if(this.getContent(row).offsetHeight > 0)
			{
				this.collapseRow(row);
			}
			else
			{
				this.expendRow(row);
			}
		},
		toggleWidgetHeight: function()
		{
			BX.hasClass(this.blockContainer, 'report-widget-triple-data-with-progress-open') ? this.collapseWidgetHeight() : this.expendWidgetHeight()
		},
		expendWidgetHeight: function()
		{
			if (!this.containerHeight)
			{
				this.containerHeight = this.blockContainer.offsetHeight;
			}

			BX.addClass(this.blockContainer, 'report-widget-triple-data-with-progress-open');
			this.blockContainer.style.height = this.wrapper.offsetHeight + 'px';
			this.blockContainer.addEventListener("transitionend", function() {
				if (BX.hasClass(this.blockContainer, 'report-widget-triple-data-with-progress-open'))
				{
					this.blockContainer.style.height = 'auto'
				}
			}.bind(this), false);
			this.actionControlNode.textContent = BX.message('REPORT_GROUPED_DATA_GRID_CLOSE_TITLE');
		},
		collapseWidgetHeight: function()
		{
			this.blockContainer.style.height = this.blockContainer.offsetHeight + 'px';
			this.collapseAllRows();
			BX.removeClass(this.blockContainer, 'report-widget-triple-data-with-progress-open');
			setTimeout(function() {
				this.blockContainer.style.height = this.containerHeight + 'px';
			}.bind(this),0);
			this.actionControlNode.textContent = BX.message('REPORT_GROUPED_DATA_GRID_MORE_TITLE');
		},
		collapseAllRows: function()
		{
			for(var i = 0; i < this.rows.length; i++)
			{
				this.collapseRow(this.rows[i]);
			}
		},
		expendRow: function(row)
		{
			var content = this.getContent(row).firstElementChild;
			var contentHeight = content.offsetHeight;
			this.getContent(row).parentNode.classList.add('report-operator-open');
			this.getContent(row).style.height = contentHeight + 'px';
		},
		collapseRow: function(row)
		{
			this.getContent(row).parentNode.classList.remove('report-operator-open');
			this.getContent(row).style.height = '0';
		},
		getHeader: function (item)
		{
			return item.querySelector('[data-role="report-operator-header"]')
		},

		getContent: function (item)
		{
			return item.querySelector('[data-role="report-operator-content"]')
		}
	};

})();