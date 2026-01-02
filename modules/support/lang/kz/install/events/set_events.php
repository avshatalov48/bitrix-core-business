<?php
$MESS["SUP_SE_TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#WHAT_CHANGE# - жүгінуде өзгергеннің тізімі
#DATE_CREATE# - жасау күні 
#TIMESTAMP# - өзгерту күні
#DATE_CLOSE# - жабылу күні
#TITLE# - жүгіну тақырыпаты
#STATUS# - жүгіну мәртебесі
#CATEGORY# - жүгіну санаты
#CRITICALITY# - жүгіну сыншылдығы
#RATE# - жауаптарды бағалау
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгінудің бастапқы көзі (web, email, телефон және т. б.)
#SPAM_MARK# - спам туралы белгі
#MESSAGES_AMOUNT# - жүгінудегі хабарламалар саны
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)
#PUBLIC_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (жария бөлімге)

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#MODIFIED_USER_ID# - жүгінуді өзгерткеннің ID
#MODIFIED_USER_LOGIN# - жүгінуді өзгерткеннің логині
#MODIFIED_USER_EMAIL# - жүгінуді өзгерткеннің EMail
#MODIFIED_USER_NAME# - жүгінуді өзгерткеннің аты
#MODIFIED_MODULE_NAME# - жүгіну өзгертілген модуль идентификаторы
#MODIFIED_TEXT# - [#MODIFIED_USER_ID#] (#MODIFIED_USER_LOGIN#) #MODIFIED_USER_NAME#

#MESSAGE_AUTHOR_USER_ID# - хабарлама авторының ID
#MESSAGE_AUTHOR_USER_NAME# - хабарлама авторының аты
#MESSAGE_AUTHOR_USER_LOGIN# - хабарлама авторының логині
#MESSAGE_AUTHOR_USER_EMAIL# - хабарлама авторының email
#MESSAGE_AUTHOR_TEXT# - [#MESSAGE_AUTHOR_USER_ID#] (#MESSAGE_AUTHOR_USER_LOGIN#) #MESSAGE_AUTHOR_USER_NAME#
#MESSAGE_AUTHOR_SID# - хабарлама авторының ерікті идентификаторы (email, телефон және т. б.)
#MESSAGE_SOURCE# - хабарлама көзі
#MESSAGE_BODY# - хабарлама мәтіні
#FILES_LINKS# - тіркелген файлдарға сілтемелер

#SUPPORT_COMMENTS# - әкімшілік түсініктеме

";
$MESS["SUP_SE_TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR_TITLE"] = "Жүгінуді автор өзгертті (автор үшін)";
$MESS["SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_MESSAGE"] = "#SERVER_NAME# сайтында сіздің # #ID# жүгінуіңіздегі өзгерістер.

#WHAT_CHANGE#
Тақырыбы: #TITLE# 

Кімнен: #MESSAGE_SOURCE##MESSAGE_AUTHOR_SID##MESSAGE_AUTHOR_TEXT#

>======================= ХАБАРЛАМА ===================================#FILES_LINKS##MESSAGE_BODY#
>=====================================================================

Авторы    - #SOURCE##OWNER_SID##OWNER_TEXT#
Жасалды  - #CREATED_TEXT##CREATED_MODULE_NAME# [#DATE_CREATE#]
Өзгертілді - #MODIFIED_TEXT##MODIFIED_MODULE_NAME# [#TIMESTAMP#]

Жауапты     - #RESPONSIBLE_TEXT#
Санаты         - #CATEGORY#
Сыншылдық       - #CRITICALITY#
Мәртебесі            - #STATUS#
Жауаптарды бағалау    - #RATE#
Қолдау деңгейі - #SLA#

Жүгінуді қарау және өңдеу үшін сілтемені пайдаланыңыз:
http://#SERVER_NAME##PUBLIC_EDIT_URL#?ID=#ID#

Жүгіну жабылғаннан кейін техникалық қолдау қызметінің жауаптарын бағалауды ұмытпауыңызды сұраймыз:
http://#SERVER_NAME##PUBLIC_EDIT_URL#?ID=#ID#

Хат автоматты түрде жазылды.
";
$MESS["SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_SUBJECT"] = "[TID##ID#] #SERVER_NAME#: Сіздің жүгінуіңіздегі өзгерістер";
$MESS["SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#WHAT_CHANGE# - жүгінуде өзгергеннің тізімі
#DATE_CREATE# - жасау күні 
#TIMESTAMP# - өзгерту күні
#DATE_CLOSE# - жабылу күні
#TITLE# - жүгіну тақырыпаты
#STATUS# - жүгіну мәртебесі
#CATEGORY# - жүгіну санаты
#CRITICALITY# - жүгіну сыншылдығы
#RATE# - жауаптарды бағалау
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгінудің бастапқы көзі (web, email, телефон және т. б.)
#SPAM_MARK# - спам туралы белгі
#MESSAGES_AMOUNT# - жүгінудегі хабарламалар саны
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)
#PUBLIC_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (жария бөлімге)

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#MODIFIED_USER_ID# - жүгінуді өзгерткеннің ID
#MODIFIED_USER_LOGIN# - жүгінуді өзгерткеннің логині
#MODIFIED_USER_EMAIL# - жүгінуді өзгерткеннің EMail
#MODIFIED_USER_NAME# - жүгінуді өзгерткеннің аты
#MODIFIED_MODULE_NAME# - жүгіну өзгертілген модуль идентификаторы
#MODIFIED_TEXT# - [#MODIFIED_USER_ID#] (#MODIFIED_USER_LOGIN#) #MODIFIED_USER_NAME#

#MESSAGE_AUTHOR_USER_ID# - хабарлама авторының ID
#MESSAGE_AUTHOR_USER_NAME# - хабарлама авторының аты
#MESSAGE_AUTHOR_USER_LOGIN# - хабарлама авторының логині
#MESSAGE_AUTHOR_USER_EMAIL# - хабарлама авторының email
#MESSAGE_AUTHOR_TEXT# - [#MESSAGE_AUTHOR_USER_ID#] (#MESSAGE_AUTHOR_USER_LOGIN#) #MESSAGE_AUTHOR_USER_NAME#
#MESSAGE_AUTHOR_SID# - хабарлама авторының ерікті идентификаторы (email, телефон және т. б.)
#MESSAGE_SOURCE# - хабарлама көзі
#MESSAGE_BODY# - хабарлама мәтіні
#FILES_LINKS# - тіркелген файлдарға сілтемелер

#SUPPORT_COMMENTS# - әкімшілік түсініктеме

";
$MESS["SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_TITLE"] = "Жүгінуді техникалық қолдау қызметкері өзгертті (автор үшін)";
$MESS["SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_MESSAGE"] = "#SERVER_NAME# сайтының техникалық қолдау қызметіне # #ID# жүгінудегі өзгерістер.
#SPAM_MARK#
#WHAT_CHANGE#
Тақырыбы: #TITLE# 

Кімнен: #MESSAGE_SOURCE##MESSAGE_AUTHOR_SID##MESSAGE_AUTHOR_TEXT#

>#MESSAGE_HEADER##FILES_LINKS##MESSAGE_BODY#
>#MESSAGE_FOOTER#

Авторы    - #SOURCE##OWNER_SID##OWNER_TEXT#
Жасалды  - #CREATED_TEXT##CREATED_MODULE_NAME# [#DATE_CREATE#]
Өзгертілді - #MODIFIED_TEXT##MODIFIED_MODULE_NAME# [#TIMESTAMP#]

Жауапты     - #RESPONSIBLE_TEXT#
Санаты         - #CATEGORY#
Сыншылдық       - #CRITICALITY#
Мәртебесі            - #STATUS#
Жауаптарды бағалау    - #RATE#
Қолдау деңгейі - #SLA#

>====================== ТҮСІНІКТЕМЕ ==================================#SUPPORT_COMMENTS#
>=====================================================================

Жүгінуді қарау және өңдеу үшін сілтемені пайдаланыңыз:
http://#SERVER_NAME##ADMIN_EDIT_URL#?ID=#ID#&lang=#LANGUAGE_ID#

Хат автоматты түрде жазылды.
";
$MESS["SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_SUBJECT"] = "[TID##ID#] #SERVER_NAME#: Жүгінудегі өзгерістер";
$MESS["SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#WHAT_CHANGE# - жүгінуде өзгергеннің тізімі
#DATE_CREATE# - жасау күні 
#TIMESTAMP# - өзгерту күні
#DATE_CLOSE# - жабылу күні
#TITLE# - жүгіну тақырыпаты
#STATUS# - жүгіну мәртебесі
#CATEGORY# - жүгіну санаты
#CRITICALITY# - жүгіну сыншылдығы
#RATE# - жауаптарды бағалау
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгінудің бастапқы көзі (web, email, телефон және т. б.)
#SPAM_MARK# - спам туралы белгі
#MESSAGES_AMOUNT# - жүгінудегі хабарламалар саны
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)
#PUBLIC_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (жария бөлімге)

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#MODIFIED_USER_ID# - жүгінуді өзгерткеннің ID
#MODIFIED_USER_LOGIN# - жүгінуді өзгерткеннің логині
#MODIFIED_USER_EMAIL# - жүгінуді өзгерткеннің EMail
#MODIFIED_USER_NAME# - жүгінуді өзгерткеннің аты
#MODIFIED_MODULE_NAME# - жүгіну өзгертілген модуль идентификаторы
#MODIFIED_TEXT# - [#MODIFIED_USER_ID#] (#MODIFIED_USER_LOGIN#) #MODIFIED_USER_NAME#

#MESSAGE_AUTHOR_USER_ID# - хабарлама авторының ID
#MESSAGE_AUTHOR_USER_NAME# - хабарлама авторының аты
#MESSAGE_AUTHOR_USER_LOGIN# - хабарлама авторының логині
#MESSAGE_AUTHOR_USER_EMAIL# - хабарлама авторының email
#MESSAGE_AUTHOR_TEXT# - [#MESSAGE_AUTHOR_USER_ID#] (#MESSAGE_AUTHOR_USER_LOGIN#) #MESSAGE_AUTHOR_USER_NAME#
#MESSAGE_AUTHOR_SID# - хабарлама авторының ерікті идентификаторы (email, телефон және т. б.)
#MESSAGE_SOURCE# - хабарлама көзі
#MESSAGE_HEADER# - \"******* ХАБАРЛАМА *******\", немесе \"******* ЖАСЫРЫН ХАБАРЛАМА *******\"
#MESSAGE_BODY# - хабарлама мәтіні
#MESSAGE_FOOTER# - \"*********************** \"
#FILES_LINKS# - тіркелген файлдарға сілтемелер

#SUPPORT_COMMENTS# - әкімшілік түсініктеме

";
$MESS["SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_TITLE"] = "Жүгінудегі өзгерістер (техникалық қолдау үшін)";
$MESS["SUP_SE_TICKET_GENERATE_SUPERCOUPON_TEXT"] = "#COUPON# - Купон
#COUPON_ID# - Купон ID 
#DATE# - Пайдалану күні
#USER_ID# - қолданған пайдаланушының ID 
#SESSION_ID# - сессия ID 
#GUEST_ID# - қонақ ID
";
$MESS["SUP_SE_TICKET_GENERATE_SUPERCOUPON_TITLE"] = "Купон қолданылды";
$MESS["SUP_SE_TICKET_NEW_FOR_AUTHOR_MESSAGE"] = "Сіздің жүгінуіңіз қабылданды, оған #ID# нөмірі берілді.

Сіз бұл хатқа жауап бермеуіңіз керек. Бұл тек техникалық қолдау  
қызметі сіздің жүгінуіңізді қабылдағанын және онымен жұмыс істейтінін растайды.

Сіздің жүгінуіңіз туралы ақпарат:

Тақырыбы          - #TITLE# 
Кімнен       - #SOURCE##OWNER_SID##OWNER_TEXT#
Санаты     - #CATEGORY#
Сыншылдық   - #CRITICALITY#

Жасалды           - #CREATED_TEXT##CREATED_MODULE_NAME# [#DATE_CREATE#]
Жауапты     - #RESPONSIBLE_TEXT#
Қолдау деңгейі - #SLA#

>======================= ХАБАРЛАМА ===================================

#FILES_LINKS##MESSAGE_BODY#

>=====================================================================

Жүгінуді қарау және өңдеу үшін сілтемені пайдаланыңыз:
http://#SERVER_NAME##PUBLIC_EDIT_URL#?ID=#ID#

Хат автоматты түрде жазылды.
";
$MESS["SUP_SE_TICKET_NEW_FOR_AUTHOR_SUBJECT"] = "[TID##ID#] #SERVER_NAME#: Сіздің жүгінуіңіз қабылданды";
$MESS["SUP_SE_TICKET_NEW_FOR_AUTHOR_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#DATE_CREATE# - жасау күні 
#TIMESTAMP# - өзгерту күні
#DATE_CLOSE# - жабылу күні
#TITLE# - жүгіну тақырыпаты
#CATEGORY# - жүгіну санаты
#STATUS# - жүгіну мәртебесі
#CRITICALITY# - жүгіну сыншылдығы
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгіну көзі (web, email, телефон және т. б.)
#SPAM_MARK# - спам туралы белгі
#MESSAGE_BODY# - хабарлама мәтіні
#FILES_LINKS# - тіркелген файлдарға сілтемелер
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)
#PUBLIC_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (жария бөлімге)

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - барлық техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#SUPPORT_COMMENTS# - әкімшілік түсініктеме

";
$MESS["SUP_SE_TICKET_NEW_FOR_AUTHOR_TITLE"] = "Жаңа жүгіну (автор үшін)";
$MESS["SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_MESSAGE"] = "#SERVER_NAME# сайтының техникалық қолдау қызметіне жаңа # #ID# жүгінуі. 
#SPAM_MARK#
Кімнен: #SOURCE##OWNER_SID##OWNER_TEXT#

Тақырыбы: #TITLE# 

>======================= ХАБАРЛАМА ===================================

#FILES_LINKS##MESSAGE_BODY#

>=====================================================================

Жауапты     - #RESPONSIBLE_TEXT#
Санаты         - #CATEGORY#
Сыншылдық       - #CRITICALITY#
Қолдау деңгейі - #SLA#
Жасалды           - #CREATED_TEXT##CREATED_MODULE_NAME# [#DATE_CREATE#]

Жүгінуді қарау және өңдеу үшін сілтемені пайдаланыңыз:
http://#SERVER_NAME##ADMIN_EDIT_URL#?ID=#ID#&lang=#LANGUAGE_ID#

Хат автоматты түрде жазылды. 
";
$MESS["SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_SUBJECT"] = "[TID##ID#] #SERVER_NAME#: Жаңа жүгіну";
$MESS["SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#DATE_CREATE# - жасау күні 
#TIMESTAMP# - өзгерту күні
#DATE_CLOSE# - жабылу күні
#TITLE# - жүгіну тақырыпаты
#CATEGORY# - жүгіну санаты
#STATUS# - жүгіну мәртебесі
#CRITICALITY# - жүгіну сыншылдығы
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгіну көзі (web, email, телефон және т. б.)
#SPAM_MARK# - спам туралы белгі
#MESSAGE_BODY# - хабарлама мәтіні
#FILES_LINKS# - тіркелген файлдарға сілтемелер
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)
#PUBLIC_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (жария бөлімге)

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - барлық техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#SUPPORT_COMMENTS# - әкімшілік түсініктеме

#COUPON# - Купон
";
$MESS["SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_TITLE"] = "Жаңа жүгіну (техникалық қолдау үшін)";
$MESS["SUP_SE_TICKET_OVERDUE_REMINDER_MESSAGE"] = "#SERVER_NAME# сайтының техникалық қолдау қызметіне # #ID# жүгінуге жауап беру қажеттігі туралы еске салу.

Қашан мерзімі өтеді - #EXPIRATION_DATE# (қалды: #REMAINED_TIME#)

>================= ЖҮГІНУ БОЙЫНША АҚПАРАТ ===========================

Тақырыбы    - #TITLE# 

Авторы   - #SOURCE##OWNER_SID##OWNER_TEXT#
Жасалды - #CREATED_TEXT##CREATED_MODULE_NAME# [#DATE_CREATE#]

Қолдау деңгейі - #SLA#

Жауапты     - #RESPONSIBLE_TEXT#
Санаты         - #CATEGORY#
Сыншылдық       - #CRITICALITY#
Мәртебесі            - #STATUS#
Жауаптарды бағалау    - #RATE#

>================ ЖАУАПТЫ ТАЛАП ЕТЕТІН ХАБАРЛАМА =========================
#MESSAGE_BODY#
>=====================================================================

Жүгінуді қарау және өңдеу үшін сілтемені пайдаланыңыз:
http://#SERVER_NAME##ADMIN_EDIT_URL#?ID=#ID#&lang=#LANGUAGE_ID#

Хат автоматты түрде жазылды.
";
$MESS["SUP_SE_TICKET_OVERDUE_REMINDER_SUBJECT"] = "[TID##ID#] #SERVER_NAME#: Жауап беру қажеттілігі туралы ескерту";
$MESS["SUP_SE_TICKET_OVERDUE_REMINDER_TEXT"] = "
#ID# - жүгіну ID 
#LANGUAGE_ID# - жүгіну байланысқан сайт тілінің идентификаторы
#DATE_CREATE# - жасау күні 
#TITLE# - жүгіну тақырыпаты
#STATUS# - жүгіну мәртебесі
#CATEGORY# - жүгіну санаты
#CRITICALITY# - жүгіну сыншылдығы
#RATE# - жауаптарды бағалау
#SLA# - техникалық қолдау деңгейі
#SOURCE# - жүгінудің бастапқы көзі (web, email, телефон және т. б.)
#ADMIN_EDIT_URL# - жүгінуді өзгертуге арналған сілтеме (әкімшілік бөлімге)

#EXPIRATION_DATE# - реакция уақытының аяқталу күні
#REMAINED_TIME# - реакция уақыты аяқталғанға дейін қанша уақыт қалды

#OWNER_EMAIL# - #OWNER_USER_EMAIL# және/немесе #OWNER_SID#
#OWNER_USER_ID# - жүгіну авторының ID
#OWNER_USER_NAME# - жүгіну авторының аты
#OWNER_USER_LOGIN# - жүгіну авторының логині
#OWNER_USER_EMAIL# - жүгіну авторының email
#OWNER_TEXT# - [#OWNER_USER_ID#] (#OWNER_USER_LOGIN#) #OWNER_USER_NAME#
#OWNER_SID# - жүгіну авторының ерікті идентификаторы (email, телефон және т. б.)

#SUPPORT_EMAIL# - #RESPONSIBLE_USER_EMAIL# немесе #SUPPORT_ADMIN_EMAIL#
#RESPONSIBLE_USER_ID# - жүгіну үшін жауаптының ID 
#RESPONSIBLE_USER_NAME# - жүгінуге жауаптының аты
#RESPONSIBLE_USER_LOGIN# - жүгінуге жауаптының логині
#RESPONSIBLE_USER_EMAIL# - жүгінуге жауаптының email
#RESPONSIBLE_TEXT# - [#RESPONSIBLE_USER_ID#] (#RESPONSIBLE_USER_LOGIN#) #RESPONSIBLE_USER_NAME#
#SUPPORT_ADMIN_EMAIL# - техникалық қолдау әкімшілерінің EMail

#CREATED_USER_ID# - жүгінуді жасаушының ID
#CREATED_USER_LOGIN# - жүгінуді жасаушының логині
#CREATED_USER_EMAIL# - жүгінуді жасаушының email
#CREATED_USER_NAME# - жүгінуді жасаушының аты
#CREATED_MODULE_NAME# - модуль идентификаторы, оның көмегімен жүгіну жасалды
#CREATED_TEXT# - [#CREATED_USER_ID#] (#CREATED_USER_LOGIN#) #CREATED_USER_NAME#

#MESSAGE_BODY# - жауап талап ететін клиенттің хабарлама мәтіні
";
$MESS["SUP_SE_TICKET_OVERDUE_REMINDER_TITLE"] = "Жауап беру қажеттілігі туралы ескерту (техникалық қолдау үшін)";
