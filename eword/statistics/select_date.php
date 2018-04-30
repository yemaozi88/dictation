<?php
/*
 * 2013/12/31
 * date selector of the statistics for english word quiz of yamauchi project
 *
 * RECEIVE
 * select_group.php
 * SEND
 * statistics.php
 *
 * HISTORY
 * 2015/10/20 added 'all' option
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */

 
 
// ====================
// configuration
// ====================

$isDebug = false;

$config  = parse_ini_file("config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$pageTitle = $config["pageTitle"];
$srcDir    = $config["srcDir"];
$logoDir   = $config["logoDir"];
$sqlTable  = $config["sqlTable"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();

// ====================

// ====================
// get form data
// ====================

//$withWav   = 1;
$withWav   = $_POST['withWav'];
$GroupName = $_POST['GroupName'];


// ====================
// output page
// ====================

$i_pagestyle->print_header();

if($isDebug == true)
{
	echo "
	pageTitle: $pageTitle</br>
        withWav: $withWav</br>
	GroupName: $GroupName</br>
	sqlTable: $sqlTable</br>
	";
}


/*
SELECT timestamp,
DATE_FORMAT(timestamp, '%Y年%m月%d日') AS dateJ,
username
FROM `eword_result` 
WHERE groupname = 'tiu'
 */
 
$sql_select = "SELECT timestamp,
		DATE_FORMAT(timestamp, '%Y年%m月%d日') as dateJ,
		DATE_FORMAT(timestamp, '%Y-%m-%d') as dateE,
		username 
	FROM $sqlTable
	WHERE groupname = '$GroupName' AND is_test = '1'
	GROUP BY dateJ
    ORDER BY dateJ ASC";
$sql_result = mysql_query($sql_select);


if(mysql_num_rows($sql_result)==0)
{
	echo "<h2>選択された場所ではテストが実施されていません</h2>";
}
else
{
	echo "\n\t<h2>誰のいつの結果を表示しますか？</h2>\n";

	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)){
		$TimeStamp = $row["timestamp"];
		$DateJ = $row["dateJ"];
		$DateE = $row["dateE"];
		$DateE_from = $DateE . ' 00:00:00';
		$DateE_till = $DateE . ' 23:59:00';	
		echo "\t<h3>$DateJ 実施</h3>\n";

		$sql_select2 = "SELECT timestamp, username 
			FROM $sqlTable
			WHERE groupname = '$GroupName' 
				AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
			GROUP BY username
			HAVING username <> ''
			ORDER BY username ASC";
		$sql_result2 = mysql_query($sql_select2);

		echo "\t<table>\n";
		while ($row = mysql_fetch_array($sql_result2, MYSQL_ASSOC)){
			$UserName = $row["username"];
				
			$link = '<a href=statistics.php?GroupName=' . $GroupName . '&withWav=' . $withWav . '&UserName=' . $UserName . '&DateJ=' . $DateJ . '&DateE=' . $DateE;
			$link = $link . '>' . $UserName . '</a>';

			echo "\t\t<tr><td>\n";
			echo "$link\n";
			echo "\t\t</td></tr>\n";
		}
        
        /*
         * display the statistics of all students
         */
		$UserName = "_all";				
		$link = '<a href=statistics.php?GroupName=' . $GroupName . '&withWav=' . $withWav . '&UserName=' . $UserName . '&DateJ=' . $DateJ . '&DateE=' . $DateE;
		$link = $link . '> 一覧を表示</a>';

		echo "\t\t<tr height=50><td>\n";
		echo "$link\n";
		echo "\t\t</td></tr>\n";
        
		echo "\t</table>\n\n";
	}
}


$i_mysql->close();
$i_pagestyle->print_footer();
?>