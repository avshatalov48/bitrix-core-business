<?
$MESS ['PROVIDE_LINK_BY_TIP'] = "Параметр определяет, что будет передано в целевой input формы при выборе местоположения: CODE или ID";
$MESS ['JS_CONTROL_GLOBAL_ID_TIP'] = "Строковый идентификатор, который позволяет напрямую обратиться к javascript-объекту селектора извне, используя объект window.BX.locationSelectors";
$MESS ['JS_CALLBACK_TIP'] = "Javascript-функция, которая вызывается при каждом изменении значения селектора. Функция должна быть определена в контексте объекта window, например:<br />
window.locationUpdated = function(id)<br />
{<br />
&nbsp;console.log(arguments);<br />
&nbsp;console.log(this.getNodeByLocationId(id));<br />
}";