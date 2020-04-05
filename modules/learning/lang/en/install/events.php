<?
$MESS["NEW_LEARNING_TEXT_ANSWER_NAME"] = "New Text Answer";
$MESS["NEW_LEARNING_TEXT_ANSWER_DESC"] = "#ID# - The result ID
#ATTEMPT_ID# - The attempt ID
#TEST_NAME# - The test name
#USER# - The user taking a test
#DATE# - The date and time
#QUESTION_TEXT# - The question
#ANSWER_TEXT# - The answer
#EMAIL_FROM# - The sender's e-mail address
#EMAIL_TO# - The recipient's e-mail address
#MESSAGE_TITLE# - The e-mail message subject";
$MESS["NEW_LEARNING_TEXT_ANSWER_SUBJECT"] = "#SITE_NAME#: #COURSE_NAME#: #MESSAGE_TITLE#";
$MESS["NEW_LEARNING_TEXT_ANSWER_MESSAGE"] = "Message from #SITE_NAME#
 ------------------------------------------

Course:#COURSE_NAME#
Test:#TEST_NAME#

User: #USER#
Date: #DATE#

Question:
------------------------------------------
#QUESTION_TEXT#
------------------------------------------

Answer:
------------------------------------------
#ANSWER_TEXT#
------------------------------------------

To view and edit the answer, follow the link:
http://#SERVER_NAME#/bitrix/admin/learn_test_result_edit.php?lang=en&ID=#ID#&ATTEMPT_ID=#ATTEMPT_ID#

This message has been generated automatically.";
?>