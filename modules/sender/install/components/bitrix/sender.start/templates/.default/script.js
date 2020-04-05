;(function ()
{
	BX.namespace('BX.Sender');
	if (BX.Sender.Start)
	{
		return;
	}

	var Helper = BX.Sender.Helper;
	var Page = BX.Sender.Page;

	/**
	 * Manager.
	 *
	 */
	function Manager(params)
	{
	}
	Manager.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.isAdAvailable = params.isAdAvailable;

		var otherButton = Helper.getNode('letter-other', this.context);
		var otherContainer = Helper.getNode('letter-other-cont', this.context);
		BX.bind(otherButton, 'click', Helper.display.toggle.bind(Helper.display, otherContainer, false));

		var buttons = Helper.getNodes('letter-add', this.context);
		buttons.forEach(function (node) {
			var self = this;
			var path = node.getAttribute('data-bx-url');
			BX.bind(node, 'click', function (e) {
				e.stopPropagation();
				e.preventDefault();

				var isAvailable = node.getAttribute('data-available') === 'y';
				if (!isAvailable && BX.Sender.B24License)
				{
					BX.Sender.B24License.showPopup('Ad');
					return;
				}

				Page.open(path);
			});
		}, this);
	};
	Manager.prototype.initNode = function (node)
	{

	};

	BX.Sender.Start = new Manager;

})(window);