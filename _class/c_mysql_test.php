<?php
/*
 * 2012/10/13
 * test code for mysql class
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */
 /* TODO BY AKI or else no kiss: Built in some security so you can only post from the wanted aspx page */
include("./c_mysql.php");

$i_mysql = new c_mysql();
$i_mysql->connect();

//$sql_insert = "INSERT INTO quiz_elistening(no, word, option1, option2, option3, option4, answer) VALUES ('1', 'celebrate', '"祝う"', '祝う', '祝う', '祝う', '2')";

//$sql_insert = "INSERT INTO word_result(username, quiz_set, quiz_number, quiz_level, user_answer, is_correct, elapsed_time) VALUES ('aki', 'A', '6', '3', '1', '1', '1.07')";
$username 		= $_POST['username'];
$quiz_set 		= $_POST['quiz_set'];
$quiz_number 	= $_POST['quiz_number'];
$quiz_level 	= $_POST['quiz_level'];
$user_answer 	= $_POST['user_answer'];
$is_correct 	= $_POST['is_correct'];
$elapsed_time 	= $_POST['elapsed_time'];
$sql_insert = "INSERT INTO test(username, quiz_set, quiz_number, quiz_level, user_answer, is_correct, elapsed_time) VALUES ('$username', '$quiz_set', $quiz_number, $quiz_level, $user_answer, $is_correct, $elapsed_time)";
print "$sql_insert";

mysql_query($sql_insert);
//$sql_select = "SELECT avg(elapsed_time) as averagetime FROM word_result where username = 'test' and timestamp between '2013-01-01' and '2013-05-19' order by timestamp desc";
//$sql_result = mysql_query($sql_select);
//while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
//    printf ("<p>It took %s seconds on average</p>", $row["averagetime"]);
//}
$i_mysql->close();
?>