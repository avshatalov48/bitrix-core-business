<?
$MESS["FORUM_RATING_NAME"] = "Forum";
$MESS["FORUM_RATING_USER_VOTE_TOPIC_NAME"] = "Voting For User's Topics In Forums";
$MESS["FORUM_RATING_USER_VOTE_TOPIC_FORMULA_DESC"] = "Total - the voting result; K - user defined transition factor.";
$MESS["FORUM_RATING_USER_VOTE_TOPIC_FIELDS_COEFFICIENT"] = "Transition Factor:";
$MESS["FORUM_RATING_USER_VOTE_POST_NAME"] = "Voting For User's Posts In Forums";
$MESS["FORUM_RATING_USER_VOTE_POST_FORMULA_DESC"] = "Total - the voting result; K - user defined transition factor.";
$MESS["FORUM_RATING_USER_VOTE_POST_FIELDS_COEFFICIENT"] = "Transition Factor:";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_NAME"] = "User Forum Activity";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_DESC"] = "The estimation uses the count of topics and posts created today; for the last 7 days and for the last 30 days.";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FORMULA_DESC"] = "T<sub>1</sub>, T<sub>7</sub>, T<sub>30</sub>, T<sub>all</sub> - number of topics created: today; for the last week; for the last month and eternally, respectively;<br>
K<sub>T1</sub>, K<sub>T7</sub>, K<sub>T30</sub>, K<sub>Tall</sub> - user defined factors for topics created: today; for the last week; for the last month and eternally, respectively.<br>
P<sub>1</sub>, P<sub>7</sub>, P<sub>30</sub>, P<sub>all</sub> - number of posts created: today; for the last week; for the last month and eternally, respectively;<br>
K<sub>P1</sub>, K<sub>P7</sub>, K<sub>P30</sub>, K<sub>Pall</sub> - user defined factors for posts created: today; for the last week; for the last month and eternally, respectively.";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_TODAY_TOPIC_COEF"] = "K<sub>T1</sub>, K<sub>T7</sub>, K<sub>T30</sub> - user defined factors for today's, last week and last month topics.<br>";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_WEEK_TOPIC_COEF"] = "P<sub>1</sub>, P<sub>7</sub>, P<sub>30</sub> - number of posts created today, for the last week and for the last month, respectively;<br>";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_MONTH_TOPIC_COEF"] = "K<sub>P1</sub>, K<sub>P7</sub>, K<sub>P30</sub> - user defined factors for today's, last week and last month posts.";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_ALL_TOPIC_COEF"] = "Factor For Topics Older Than Month:";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_TODAY_POST_COEF"] = "Rating Multiplier For Today's Posts:";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_WEEK_POST_COEF"] = "Rating Multiplier For Recent Week Posts:";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_MONTH_POST_COEF"] = "Rating Multiplier For Recent Month Posts:";
$MESS["FORUM_RATING_USER_RATING_ACTIVITY_FIELDS_ALL_POST_COEF"] = "Factor For Posts Older Than Month:";
$MESS["EXCEPTION_USER_RATING_FORUM_ACTIVITY_TEXT"] = "New indexes need to be created to enable the calculation of user forum activity.";
$MESS["FORUM_RATING_USER_VOTE_TOPIC_DESC"] = "Calculate rating using the voting results for the specified number of days.<br> Set days to \"0\" to use all data for calculation.";
$MESS["FORUM_RATING_USER_VOTE_TOPIC_LIMIT_NAME"] = "Day Span:";
$MESS["FORUM_RATING_USER_VOTE_POST_DESC"] = "Calculate rating using the voting results for the specified number of days.<br> Set days to \"0\" to use all data for calculation.";
$MESS["FORUM_RATING_USER_VOTE_POST_LIMIT_NAME"] = "Day Span:";
?>