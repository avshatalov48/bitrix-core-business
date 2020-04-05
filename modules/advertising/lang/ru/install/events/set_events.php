<?
$MESS ['ADV_BANNER_STATUS_CHANGE_NAME'] = "Изменился статус баннера";
$MESS ['ADV_BANNER_STATUS_CHANGE_DESC'] = "#EMAIL_TO# - EMail получателя сообщения (#OWNER_EMAIL#)
#ADMIN_EMAIL# - EMail пользователей имеющих роль \"менеджер баннеров\" и \"администратор\"
#ADD_EMAIL# - EMail пользователей имеющих право управления баннерами контракта
#STAT_EMAIL# - EMail пользователей имеющих право просмотра баннеров конракта
#EDIT_EMAIL# - EMail пользователей имеющих право модификации некоторых полей контракта
#OWNER_EMAIL# - EMail пользователей имеющих какое либо право на контракт
#BCC# - скрытая копия (#ADMIN_EMAIL#)
#ID# - ID баннера
#CONTRACT_ID# - ID контракта
#CONTRACT_NAME# - заголовок контракта
#TYPE_SID# - ID типа
#TYPE_NAME# - заголовок типа
#STATUS# - статус
#STATUS_COMMENTS# - комментарий к статусу
#NAME# - заголовок баннера
#GROUP_SID# - группа баннера
#INDICATOR# - показывается ли баннер на сайте ?
#ACTIVE# - флаг активности баннера [Y | N]
#MAX_SHOW_COUNT# - максимальное количество показов баннера
#SHOW_COUNT# - сколько раз баннер был показан на сайте
#MAX_CLICK_COUNT# - максимальное количество кликов на баннер
#CLICK_COUNT# - сколько раз кликнули на баннер
#DATE_LAST_SHOW# - дата последнего показа баннера
#DATE_LAST_CLICK# - дата последнего клика на баннер
#DATE_SHOW_FROM# - дата начала показа баннера
#DATE_SHOW_TO# - дата окончания показа баннера
#IMAGE_LINK# - ссылка на изображение баннера
#IMAGE_ALT# - текст всплывающей подсказки на изображении
#URL# - URL на изображении
#URL_TARGET# - где развернуть URL изображения
#CODE# - код баннера
#CODE_TYPE# - тип кода баннера (text | html)
#COMMENTS# - комментарий к баннеру
#DATE_CREATE# - дата создания баннера
#CREATED_BY# - кем был создан баннер
#DATE_MODIFY# - дата изменения баннера
#MODIFIED_BY# - кем изменен баннер
";
$MESS ['ADV_BANNER_STATUS_CHANGE_SUBJECT'] = "[BID##ID#] #SITE_NAME#: Изменился статус баннера - [#STATUS#]";
$MESS ['ADV_BANNER_STATUS_CHANGE_MESSAGE'] = "Статус баннера # #ID# изменился на [#STATUS#].

>=================== Параметры баннера ===============================

Баннер   - [#ID#] #NAME#
Контракт - [#CONTRACT_ID#] #CONTRACT_NAME#
Тип      - [#TYPE_SID#] #TYPE_NAME#
Группа   - #GROUP_SID#

----------------------------------------------------------------------

Активность: #INDICATOR#

Период    - [#DATE_SHOW_FROM# - #DATE_SHOW_TO#]
Показан   - #SHOW_COUNT# / #MAX_SHOW_COUNT# [#DATE_LAST_SHOW#]
Кликнули  - #CLICK_COUNT# / #MAX_CLICK_COUNT# [#DATE_LAST_CLICK#]
Флаг акт. - [#ACTIVE#]
Статус    - [#STATUS#]
Комментарий:
#STATUS_COMMENTS#
----------------------------------------------------------------------

Изображение - [#IMAGE_ALT#] #IMAGE_LINK#
URL         - [#URL_TARGET#] #URL#

Код: [#CODE_TYPE#]
#CODE#

>=====================================================================

Создан  - #CREATED_BY# [#DATE_CREATE#]
Изменен - #MODIFIED_BY# [#DATE_MODIFY#]

Для просмотра параметров баннера воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/adv_banner_edit.php?ID=#ID#&CONTRACT_ID=#CONTRACT_ID#&lang=#LANGUAGE_ID#

Письмо сгенерировано автоматически.
";
$MESS ['ADV_CONTRACT_INFO_NAME'] = "Параметры рекламного контракта";
$MESS ['ADV_CONTRACT_INFO_DESC'] = "#MESSAGE# - сообщение
#EMAIL_TO# - EMail получателя сообщения (#OWNER_EMAIL#)
#ADMIN_EMAIL# - EMail пользователей имеющих роль \"менеджер баннеров\" и \"администратор\"
#ADD_EMAIL# - EMail пользователей имеющих право управления баннерами контракта
#STAT_EMAIL# - EMail пользователей имеющих право просмотра статистики баннеров конракта
#EDIT_EMAIL# - EMail пользователей имеющих право модификации некоторых полей контракта
#OWNER_EMAIL# - EMail пользователей имеющих какое либо право на контракт
#BCC# - скрытая копия (#ADD_EMAIL#)
#ID# - ID контракта
#INDICATOR# - показываются ли баннеры контракта на сайте ?
#ACTIVE# - флаг активности контракта [Y | N]
#NAME# - заголовок контракта
#DESCRIPTION# - описание контракта
#MAX_SHOW_COUNT# - максимальное количество показов всех баннеров контракта
#SHOW_COUNT# - сколько раз в сумме показали баннеры контракта
#MAX_CLICK_COUNT# - максимальное количество кликов на все баннеры контракта
#CLICK_COUNT# - количество кликов на все баннеры контракта
#BANNERS# - количество баннеров контракта
#DATE_SHOW_FROM# - дата начала показа баннеров
#DATE_SHOW_TO# - дата окончания показа баннеров
#DATE_CREATE# - дата создания контракта
#CREATED_BY# - кем был создан контракт
#DATE_MODIFY# - дата изменения контракта
#MODIFIED_BY# - кем был изменен контракт
";
$MESS ['ADV_CONTRACT_INFO_SUBJECT'] = "[CID##ID#] #SITE_NAME#: Параметры рекламного контракта";
$MESS ['ADV_CONTRACT_INFO_MESSAGE'] = "#MESSAGE#
Контракт: [#ID#] #NAME#
#DESCRIPTION#
>================== Параметры контракта ==============================

Активность: #INDICATOR#

Период    - [#DATE_SHOW_FROM# - #DATE_SHOW_TO#]
Показано  - #SHOW_COUNT# / #MAX_SHOW_COUNT#
Кликнули  - #CLICK_COUNT# / #MAX_CLICK_COUNT#
Флаг акт. - [#ACTIVE#]

Баннеров  - #BANNERS#
>=====================================================================

Создан  - #CREATED_BY# [#DATE_CREATE#]
Изменен - #MODIFIED_BY# [#DATE_MODIFY#]

Для просмотра параметров контракта воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/adv_contract_edit.php?ID=#ID#&lang=#LANGUAGE_ID#

Письмо сгенерировано автоматически.
";
?>