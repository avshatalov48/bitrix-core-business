<?
$MESS["PROVIDE_LINK_BY_TIP"] = "Specifies whether the CODE or ID will be passed to an input control when selecting a location.";
$MESS["JS_CONTROL_GLOBAL_ID_TIP"] = "The string ID to call the selector's JavaScript object externally using the window.BX.locationSelectors object.";
$MESS["JS_CALLBACK_TIP"] = "JavaScript function called whenever a selector value changes. The function has to be defined in the window object, for example:<br />
window.locationUpdated = function(id)<br />
{<br />
&nbsp;console.log(arguments);<br />
&nbsp;console.log(this.getNodeByLocationId(id));<br />
}";
$MESS["PRESELECT_TREE_TRUNK_TIP"] = "If the location tree has an unforking branch directly from the root, it will be initially selected.";
$MESS["PRECACHE_LAST_LEVEL_TIP"] = "If selected, the last selected level will be preloaded when showing the component. Otherwise, data will be loaded when the drop-down list is first accessed.";
?>