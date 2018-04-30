<?php
/*
 * 2013/05/19
 * statistics of English quiz
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */
 
 
// ====================
// configuration
// ====================

$quiz_set = 'R';
$from_when = '2013-01-01';
$till_when = '2013-05-19';

$pageTitle = "統計";
$srcDir    = "../../../_src";

include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================


// ====================
// output page
// ====================

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();

$i_pagestyle->print_header();


$sql_select = "SELECT username, 
		format(AVG( elapsed_time ), 2) AS average, 
		format(MAX( elapsed_time ), 2) AS slowest, 
		format(MIN( elapsed_time ), 2) AS fastest, 
		SUM(is_correct) AS score, 
		COUNT(is_correct) AS total
	FROM word_result
    WHERE quiz_set = '$quiz_set' and timestamp between '$from_when' and '$till_when'
    GROUP BY username
    HAVING username <> '' AND username <> 'kunikoshi' AND username <> 'test'
    ORDER BY AVG( elapsed_time ) ASC";
$sql_result = mysql_query($sql_select);

echo <<<EOF
<h2> $from_when から $till_when までの結果 </h2>

<h3>問題セット $quiz_set</h3>
<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
	<tr>
	<th align="center">No</th>
	<th align="left">ID</th>	
	<th align="right">平均[秒]</th>
	<th align="right">最短[秒]</th>
	<th align="right">最長[秒]</th>
	<th align="center">正解数</th>
	</tr>
EOF;

$i = 1;
while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
	//$disp_average = number_format($row["average"], 2);
	//$disp_fastest = number_format($row["fastest"], 2);
	//$disp_slowest = number_format($row["slowest"], 2);
	printf ("<tr>
	<td align=\"right\">%s</td>
	<td align=\"left\">%s</td> 
	<td align=\"right\">%s</td> 
	<td align=\"right\">%s</td> 
	<td align=\"right\">%s</td> 
	<td align=\"center\">%s/%s</td></tr>\n", 
	$i, $row["username"], $row["average"], $row["fastest"], $row["slowest"], $row["score"], $row["total"]);
	$i += 1;
}
printf("</table></p>");

$i_mysql->close();
$i_pagestyle->print_footer();
?>