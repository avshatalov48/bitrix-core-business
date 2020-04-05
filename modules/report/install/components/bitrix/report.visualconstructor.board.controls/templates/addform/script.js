(function ()
{
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Board");
	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Board.AddFormElementHandler = function (options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Board.AddFormElementHandler.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.Board.AddFormElementHandler,
		process: function ()
		{
			switch (this.action)
			{
				case 'reloadReportHandlerList':
					this.reloadReportHandlerList();
					break;
				case 'onViewTypeSelect':
					this.onViewTypeSelect();
					break;
			}
		},
		reloadReportHandlerList: function()
		{
			//this.currentField.fieldScope.style.background = BX.Report.Dashboard.Utils.getRandomColor();
		},
		onViewTypeSelect: function()
		{

		}
	};


	BX.Report.VisualConstructor.Board.AddForm = function ()
	{
		this.addForm = document.querySelector('#report_visual_constructor_add_form');
		this.minatureContainers = this.addForm.querySelectorAll('[data-type="miniature-container"]');
		this.miniatureRemoveButtons = this.addForm.querySelectorAll('[data-role="miniature-remove-button"]');
		this.boardIdField = this.addForm.querySelector('[data-type="board-id"]');
		this.showAllButtons = this.addForm.querySelectorAll('[data-type="show-all-button"]');
		this.createWidgetByButtons = document.querySelectorAll('[data-type="create-widget-by-category"]');
		this.init();
	};

	BX.Report.VisualConstructor.Board.AddForm.prototype = {
		init: function()
		{
			for (var i = 0; i < this.showAllButtons.length; i++)
			{
				this.showAllButtons[i].addEventListener("click", this.showAllCategory.bind(this, this.showAllButtons[i]))
			}

			for (var k = 0; k < this.createWidgetByButtons.length; k++)
			{
				this.createWidgetByButtons[k].addEventListener("click", this.openCreateWidgetSlider.bind(this, this.createWidgetByButtons[k]));
			}
			for (var j = 0; j < this.minatureContainers.length; j++)
			{
				BX.bind(this.minatureContainers[j], 'click', this.miniatureContainerClickHandler.bind(this, this.minatureContainers[j]));
			}

			for (var l = 0; l < this.miniatureRemoveButtons.length; l++)
			{
				BX.bind(this.miniatureRemoveButtons[l], 'click', this.miniatureCloseClickHandler.bind(this, this.miniatureRemoveButtons[l]));
			}
		},
		showAllCategory: function(button)
		{
			var categoryKey = button.getAttribute('data-toggle-button-category-key');
			var widgetCategoryWrapper = this.addForm.querySelector('[data-container-category-key=' + categoryKey + ']');
			var categoryContainer = widgetCategoryWrapper.querySelector('.report-visualconstructor-view-miniatures-container');
			if (widgetCategoryWrapper.classList.contains('report-visualconstructor-view-miniatures-wrapper-collapsed'))
			{
				button.innerHTML = BX.message('REPORT_ADD_FORM_HIDDEN_BUTTON_TITLE');
				widgetCategoryWrapper.classList.remove('report-visualconstructor-view-miniatures-wrapper-collapsed');
				widgetCategoryWrapper.style.height = categoryContainer.offsetHeight + "px";
				widgetCategoryWrapper.style.transition = "height 500ms";
			}
			else
			{
				button.innerHTML = BX.message('REPORT_ADD_FORM_SHOW_ALL_BUTTON_TITLE');
				widgetCategoryWrapper.classList.add('report-visualconstructor-view-miniatures-wrapper-collapsed');
				widgetCategoryWrapper.style.height = "199px";
				widgetCategoryWrapper.style.transition = "height 500ms";
				widgetCategoryWrapper.style.overflow = "hidden";
			}
		},
		openCreateWidgetSlider: function (button)
		{
			this.createWidgetPanel = BX.SidePanel.Instance;
			this.createWidgetPanel.open("widget:create", {
				cacheable: false,
				contentCallback: BX.delegate(function getSliderContent(slider) {
					var promise = new BX.Promise();
					BX.Report.VC.Core.ajaxPost('widget.buildForm', {
						data: {
							params: {
								viewType: 'linearGraph',
								widgetId: 'pseudo_widget_for_add',
								boardId: this.getBoardId(),
								categoryKey: button.getAttribute('data-category-key'),
								mode: 'create'
							}
						},
						onFullSuccess: BX.delegate(function(result) {
							slider.getData().set("reportContent", result.data);
							promise.fulfill(result.data);
						}, this)
					});
					return promise;
				}, this),
				animationDuration: 100,
				width: 900,
				events: {
					onLoad: function(event) {
						var slider = event.getSlider();
						BX.html(slider.layout.content, slider.getData().get("reportContent"));
					},
					onClose: function()
					{
						BX.Report.VC.PopupWindowManager.closeAllPopups()
					}
				}
			});
		},
		miniatureContainerClickHandler: function (context, event)
		{
			var imgNode = context.querySelector('img');
			var role = event.target.getAttribute('data-role');
			if (role === 'miniature-remove-button')
			{
				return;
			}

			if (event.target !== imgNode)
			{
				//Emulate img node click if click to any node in context which no equal img node
				BX.fireEvent(imgNode, 'click');
			}
			else
			{
				BX.Report.VC.Core.ajaxSubmit(this.addForm, {
						onsuccess: BX.delegate(function (response)
						{
							BX.onCustomEvent("BX.Report.VisualConstructor.afterWidgetAdd", [response.data]);
						}, this)
					}
				);
			}
		},
		miniatureCloseClickHandler: function(closeNode)
		{
			var miniatureContainer = closeNode.parentNode;

			if (this.confirmationPopup)
			{
				this.confirmationPopup.destroy();
			}
			this.confirmationPopup = new BX.PopupWindow('visualconstructor-dashboard-remove-pattern-widget', closeNode, {
				closeByEsc: true,
				autoHide: true,
				zIndex: 9999,
				width: 290,
				angle: true,
				content: this.getPatternRemoveConfirmDialogContent(),
				buttons: [
					new BX.PopupWindowCustomButton({
						text: BX.message('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONFIRM_TEXT'),
						className: "ui-btn ui-btn-lg ui-btn-primary",
						events: {
							click: this.removePatternWidget.bind(this, miniatureContainer)
						}
					}),
					new BX.PopupWindowCustomButton({
						text: BX.message('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CANCEL_TEXT'),
						className: "ui-btn ui-btn-lg ui-btn-link",
						events: {
							click: function ()
							{
								this.confirmationPopup.close()
							}.bind(this)
						}
					})
				]
			});
			this.confirmationPopup.show();
		},
		getPatternRemoveConfirmDialogContent: function()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-pattern-widget-remove-confirm-dialog-content-container'
				},
				text: BX.message('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONTENT')
			});
		},
		removePatternWidget: function(miniatureContainer)
		{
			if (this.confirmationPopup)
			{
				this.confirmationPopup.close();
			}

			var widgetId = miniatureContainer.getAttribute('data-widget-id');
			BX.Report.VC.Core.ajaxPost('widget.removePattern', {
				data: {
					widgetId: widgetId
				},
				onFullSuccess: function()
				{
					BX.remove(miniatureContainer)
				}
			});
		},
		getBoardId: function()
		{
			return this.boardIdField.value;
		}
	};

	BX.Report.VisualConstructor.Board.ClipText = function (options)
	{
		this.node = options.node;
		this.nodeHeight = null;
		this.text = null;
		this.wrapper = null;
	};

	BX.Report.VisualConstructor.Board.ClipText.prototype = {
		getTextNode: function ()
		{
			this.text = this.node.innerHTML;

			return this.text;
		},

		getHeightNode: function ()
		{
			this.nodeHeight = this.node.offsetHeight;
			return this.nodeHeight;
		},

		getHeightWrapper: function ()
		{
			this.wrapperHeight = this.wrapper.offsetHeight;
			return this.wrapperHeight;
		},

		cleanNode: function ()
		{
			this.node.innerHTML = '';
		},

		createTextWrapper: function ()
		{
			this.wrapper = document.createElement('span');
			this.wrapper.innerHTML = this.getTextNode();

			this.cleanNode();

			return this.wrapper;
		},

		clipText: function ()
		{
			var textWidth = this.text.length;
			var text;

			for(var i = 0; i < textWidth; i++ )
			{
				text = this.text.slice(0, -i)
			}

			return text;
		},

		appendClipText: function ()
		{
			for(var i = this.nodeHeight, a = 0; i <= this.getHeightWrapper(); a++)
			{
				a++;
				this.wrapper.innerHTML = this.text.slice(0, -a) + '...';
			}
		},

		create: function ()
		{
			this.node.appendChild(this.createTextWrapper());
			this.getHeightNode() < this.getHeightWrapper() ? this.appendClipText() : null;
		}
	};

	BX.Report.VisualConstructor.Board.ClipText.createFabric = function(nodes)
	{
		for (var i=0; i < nodes.length; i++)
		{
			var textClip = new BX.Report.VisualConstructor.Board.ClipText(
				{
					node: nodes[i]
				}
			);

			textClip.create();
		}
	}

})();
