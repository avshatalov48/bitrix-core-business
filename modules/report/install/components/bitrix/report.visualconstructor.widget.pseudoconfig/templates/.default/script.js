;(function(){
	BX.namespace('BX.VisualConstructor.Widget');
	BX.VisualConstructor.Widget.PseudoReportConfigs = function (options)
	{
		this.pseudoConfigurationScope = options.pseudoConfigurationScope || {};
		this.form = BX.findParent(this.pseudoConfigurationScope, {tag: 'form'});
	};
	BX.VisualConstructor.Widget.PseudoReportConfigs.prototype = {


	}
})();