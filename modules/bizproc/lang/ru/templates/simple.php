<?
$MESS ['BPT_SM_NAME'] = "Простое утверждение/голосование";
$MESS ['BPT_SM_DESC'] = "Рекомендуется для ситуаций, когда требуется принятие решения простым большинством голосов. В его рамках можно включить в список голосующих нужных сотрудников, дать возможность комментировать свое решение голосовавшим. По окончании голосования всем участникам сообщается принятое решение.";
$MESS ['BPT_SM_TITLE1'] = "Последовательный бизнес-процесс";
$MESS ['BPT_SM_TASK1_TITLE'] = "Необходимо утвердить документ \"{=Document:NAME}\"";
$MESS ['BPT_SM_TASK1_TEXT'] = "Вы должны утвердить или отклонить документ \"{=Document:NAME}\".
 
Для утверждения документа перейдите по ссылке #BASE_HREF##TASK_URL#

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BPT_SM_ACT_TITLE'] = "Почтовое сообщение";
$MESS ['BPT_SM_APPROVE_NAME'] = "Проголосуйте, пожалуйста, за документ.";
$MESS ['BPT_SM_APPROVE_DESC'] = "Вам необходимо проголосовать за документ \"{=Document:NAME}\".

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BPT_SM_APPROVE_TITLE'] = "Голосование за документ";
$MESS ['BPT_SM_ACT_NAME_1'] = "Последовательность действий";
$MESS ['BPT_SM_MAIL1_SUBJ'] = "Голосование по \"{=Document:NAME}: Документ принят";
$MESS ['BPT_SM_MAIL1_TEXT'] = "Голосование по документу \"{=Document:NAME}\" завершено. 

Документ принят {=ApproveActivity1:ApprovedPercent}% голосов.


Утвердили документ: {=ApproveActivity1:ApprovedCount}
Отклонили документ: {=ApproveActivity1:NotApprovedCount}";
$MESS ['BPT_SM_MAIL1_TITLE'] = "Документ принят";
$MESS ['BPT_SM_STATUS'] = "Утвержден";
$MESS ['BPT_SM_STATUS2'] = "Статус: Утвержден";
$MESS ['BPT_SM_PUB'] = "Публикация документа";
$MESS ['BPT_SM_MAIL2_SUBJ'] = "Голосование по \"{=Document:NAME}: Документ отклонен";
$MESS ['BPT_SM_MAIL2_TEXT'] = "Голосование по документу \"{=Document:NAME}\" завершено. 

Документ отклонен.

Утвердили документ: {=ApproveActivity1:ApprovedCount}
Отклонили документ: {=ApproveActivity1:NotApprovedCount}";
$MESS ['BPT_SM_MAIL2_TITLE'] = "Документ отклонен";
$MESS ['BPT_SM_MAIL2_STATUS'] = "Отклонен";
$MESS ['BPT_SM_MAIL2_STATUS2'] = "Статус: Отклонен";
$MESS ['BPT_SM_PARAM_NAME'] = "Голосующие";
$MESS ['BPT_SM_PARAM_DESC'] = "Список пользователей, участвующих в голосовании.";
?>