;(function() {
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');
	BX.namespace('BX.Report.VisualConstructor.Board');

	/**
	 * @param options
	 * @extends {BX.VisualConstructor.Widget}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Board.CreateFormWidgetPreset = function (options)
	{
		BX.VisualConstructor.Widget.call(this, options);
		this.draggable = false;
	};

	BX.Report.VisualConstructor.Board.CreateFormWidgetPreset.prototype = {
		__proto__: BX.VisualConstructor.Widget.prototype,
		constructor: BX.Report.VisualConstructor.Board.CreateFormWidgetPreset,
		getControlsContainer: function()
		{
			var controlsContainer = BX.Report.Dashboard.Widget.prototype.getControlsContainer.call(this);
			controlsContainer.classList.add('report-widget-configurations-control-hidden');
			return controlsContainer;
		},
		renderTo: function (node)
		{
			BX.cleanNode(node);
			this.loaded = true;
			node.style.height = this.getHeight() + 'px';
			node.appendChild(this.render());
			if (!this.getContent().getHeight())
			{
				node.style.overflowY = 'auto';
			}
			else
			{
				node.style.overflow = 'hidden';
			}

			BX.onCustomEvent(this, 'Dashboard.Board.Widget:onAfterRender');
		}
	};

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.PreviewBlock = function(options)
	{
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
		this.previewBlock = options.previewBlock;
		this.viewMiniatureBoxes = this.previewBlock.querySelectorAll('[data-type="view-miniature-box"]');
		this.id = this.fieldScope.id;
		this.value = options.value;
		this.widgetOptions = options.widgetOptions;
		this.previewBlockWidgetContainer = options.previewBlockWidgetContainer;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.PreviewBlock.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.PreviewBlock,
		init: function ()
		{
			for (var i = 0; i < this.viewMiniatureBoxes.length; i++)
			{
				this.viewMiniatureBoxes[i].addEventListener("click", this.handlerMiniatureBoxClick.bind(this, this.viewMiniatureBoxes[i]));
				this.viewMiniatureBoxes[i].addEventListener("mouseover", this.handlerMiniatureBoxMouseOver.bind(this, this.viewMiniatureBoxes[i]));
				this.viewMiniatureBoxes[i].addEventListener("mouseout", this.handlerMiniatureBoxMouseOut.bind(this, this.viewMiniatureBoxes[i]));
			}

			this.setPresetWidget(new BX.Report.VisualConstructor.Board.CreateFormWidgetPreset(this.widgetOptions));
		},
		handlerMiniatureBoxClick: function (viewMiniatureNode)
		{
			var viewKey = viewMiniatureNode.getAttribute('data-view-key');
			if (this.value === viewKey)
			{
				return;
			}
			var changeWithoutConfigurationsReloadCallback = this.changeView.bind(this, viewKey);
			var changeWithConfigurationsReloadCallback = this.openConfirmationPopup.bind(this, viewMiniatureNode);
			this.checkCompatibilityWithNewViewType(viewKey, changeWithoutConfigurationsReloadCallback, changeWithConfigurationsReloadCallback);

		},
		checkCompatibilityWithNewViewType: function(newViewKey, trueCallback, falseCallback)
		{
			BX.Report.VC.Core.ajaxPost('widget.checkIsCompatibleWithSelectedView', {
				data: {
					params: {
						newViewKey: newViewKey,
						oldViewKey: this.value
					}
				},
				onFullSuccess: BX.delegate(function(response)
				{
					if (response.data.isCompatible === true)
					{
						trueCallback.call();
					}
					else
					{
						falseCallback.call();
					}
				}, this)
			});
		},
		openConfirmationPopup: function(viewMiniatureNode)
		{
			var viewKey = viewMiniatureNode.getAttribute('data-view-key');
			if (this.confirmationPopup)
			{
				this.confirmationPopup.destroy();
			}
			this.confirmationPopup = new BX.PopupWindow('visualconstructor-dashboard-confirm-popup', viewMiniatureNode, {
				closeByEsc: true,
				offsetLeft: 30,
				autoHide: true,
				zIndex: 9999,
				width: 310,
				angle: true,
				cacheable: false,
				content: this.getConfirmDialogContent(),
				buttons: [
					new BX.PopupWindowCustomButton({
						text: BX.message('REPORT_CHANGE_VIEW_CHANGE_CONFIRM_BUTTON_TITLE'),
						className: "ui-btn ui-btn-lg ui-btn-primary",
						events: {
							click: this.handleConfirmButtonClick.bind(this, viewKey)
						}
					}),
					new BX.PopupWindowCustomButton({
						text: BX.message('REPORT_CHANGE_VIEW_CHANGE_CANCEL_BUTTON_TITLE'),
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
		changeView: function (viewKey)
		{
			this.setValue(viewKey);
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this, {mode: 'reloadWidgetPreview'}]);
		},
		changeViewWithReloadConfigurations: function (viewKey)
		{
			this.setValue(viewKey);
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this, {mode: 'reloadConfigurations'}]);
		},
		getConfirmDialogContent: function ()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-preview-block-confirm-dialog-content-container'
				},
				children: [
					BX.create('div', {
						attrs: {
							className: 'report-preview-block-confirm-dialog-title-container'
						},
						html: BX.message('REPORT_CHANGE_VIEW_ATTENTION_TITLE')
					}),
					BX.create('div', {
						attrs: {
							className: 'report-preview-block-confirm-dialog-text-container'
						},
						html: BX.message('REPORT_CHANGE_VIEW_ATTENTION_TEXT')
					})
				]
			});
		},
		setValue: function(value)
		{
			this.fieldScope.value = value;
			this.value = value;
			this.markViewMiniature(value);
		},
		markViewMiniature: function (viewKey)
		{
			for (var i = 0; i < this.viewMiniatureBoxes.length; i++)
			{
				this.viewMiniatureBoxes[i].classList.remove('report-widget-view-miniature-container-active');
			}
			this.previewBlock.querySelector('[data-view-key=' + viewKey + ']').classList.add('report-widget-view-miniature-container-active');
		},
		handlerChangePseudoWidget: function(viewKey, result)
		{
			this.setPresetWidget(new BX.Report.VisualConstructor.Board.CreateFormWidgetPreset(result.data.widget.pseudoWidget));
		},
		handleConfirmButtonClick: function(viewKey)
		{
			this.confirmationPopup.close();
			this.changeViewWithReloadConfigurations(viewKey);
		},
		handlerMiniatureBoxMouseOver: function(viewMiniatureNode)
		{
			if (this.miniatureNameHighlightPopup && this.miniatureNameHighlightPopup.bindElement === viewMiniatureNode)
			{
				return;
			}
			else if (this.miniatureNameHighlightPopup)
			{
				this.miniatureNameHighlightPopup.destroy();
			}

			var miniatureTitle = viewMiniatureNode.querySelector('img').getAttribute('title');

			this.miniatureNameHighlightPopup = new BX.PopupWindow('visualconstructor-dashboard-miniature-name-popup', viewMiniatureNode, {
				closeByEsc: true,
				autoHide: true,
				offsetLeft: 35,
				zIndex: 9999,
				darkMode: true,
				bindOptions: {
					position: 'top'
				},
				angle: {
					position: 'bottom'
				},
				cacheable: false,
				content: BX.create('div', {
					attrs: {
						className: 'report-preview-block-miniature-name-wrapper'
					},
					html: miniatureTitle
				}),
				targetContainer: document.body
			});

			this.miniatureNameHighlightPopup.show();
		},
		handlerMiniatureBoxMouseOut: function (viewMiniatureNode, event)
		{
			var e = event.toElement || event.relatedTarget;
			if (!e || !e.parentNode || e.parentNode === viewMiniatureNode || e === viewMiniatureNode) {
				return;
			}
			this.miniatureNameHighlightPopup.destroy();
		},
		showLoader: function ()
		{
			BX.cleanNode(this.previewBlockWidgetContainer);
			var lazyLoadContainer = this.presetWidget.getLazyLoadPresetContainer();
			lazyLoadContainer.classList.remove('report-visualconstructor-dashboard-widget-lazy-load-preset-disable');
			lazyLoadContainer.style.backgroundColor = '#fcfcfc';
			this.previewBlockWidgetContainer.appendChild(lazyLoadContainer);
		},
		setPresetWidget: function(widget)
		{
			this.presetWidget = widget;
			this.presetWidget.renderTo(this.previewBlockWidgetContainer);
			var previewBlockParent = this.previewBlockWidgetContainer.parentNode;
			var previewBlock = this.previewBlockWidgetContainer;
			if (previewBlockParent.clientHeight / previewBlock.clientHeight > 1)
			{
				var emptyCellSkeleton = this.getEmptyCellSkeleton();
				emptyCellSkeleton.style.height = previewBlock.clientHeight + 'px';
				previewBlockParent.appendChild(emptyCellSkeleton)
			}
		},
		getEmptyCellSkeleton: function()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-preview-block-empty-miniature-container'
				}
			});
		}
	}


})();