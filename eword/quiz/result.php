<?php
/*
 * 2012/10/14
 * result page for english word quiz of yamauchi project
 * result.php receives the user answer from quiz.php and outputs the result
 *
 * RECEIVE
 * quiz.php
 * SEND
 * quiz.php
 *
 * NOTE
 * result.php is based on result.cgi
 *
 * HISTORY
 * 2013/06/27 combine practice and test modes.
 * 2013/06/02 changed word_practice into test mode (no result, SQL settings etc.)
 * 2013/01/29 added qLevel
 * 2012/10/23 added a question set selector
 * 2012/10/16 modified so that variables are loaded from .ini file
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = false;

$config   = parse_ini_file("config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$srcDir    = $config["srcDir"];
$qList 	   = $config["qList"];
$imgDir    = $config["imgDir"];
$qNumMax   = $config["qNumMax"];
$sqlTable  = $config["sqlTable"];

$i_pagestyle = new c_pagestyle();
$i_mysql = new c_mysql();


// ====================
// get form data
// ====================

$withWav 		= $_POST['withWav'];
$isTest  		= $_POST['isTest'];
$trialNum 		= $_POST['trialNum'];
$UserName   	= $_POST['UserName'];
$GroupName  	= $_POST['GroupName'];
$qSet 			= $_POST['qSet'];
$qNumMax    	= $_POST['qNumMax'];
$QuizNumber 	= $_POST{'QuizNumber'};
$Score 			= $_POST['Score'];	

$wavPlayCounter = $_POST['wavPlayCounter'];
$userAnswer 	= $_POST['userAnswer'];


for($i = 1; $i < $qNumMax+1; $i++)
{
	$qOrder[$i] = $_POST["q$i"];
}


if($withWav == 1 && $isTest == 1)
{
	$qSet = 'R'; // default
	$pageTitle = '聴いて答える問題（実力テスト）';
}
elseif($withWav == 0 && $isTest == 1)
{
	$qSet = 'R';
	$pageTitle = '見て答える問題（実力テスト）';
}
elseif($withWav == 1 && $isTest == 0)
{
	$pageTitle = '聴いて答える問題（練習）';
}
elseif($withWav == 0 && $isTest == 0)
{
	$pageTitle = '見て答える問題（練習）';
}
$srcDir   = $config["srcDir"];
$qDir 	  = $config["qDir"];
$qList	  = $qDir . "/set" . $qSet . "/filelist.csv";
$wavDir   = $qDir . "/set" . $qSet;
$sqlTable = $config["sqlTable"];

$i_pagestyle->set_variables($pageTitle, $srcDir);

$ElapsedTime_s  = intval(substr($_POST['ElapsedTime'], 0, 2));
$ElapsedTime_ss = intval(substr($_POST['ElapsedTime'], 2, 4));


// ====================
// load the question
// ====================

$fp = fopen($qList, "r");
while($data = fgetcsv($fp))
{
	for($i=0; $i<count($data); $i++)
	{
		if($data[0] == $qOrder[$QuizNumber])
		{
			$Q[$i] = mb_convert_encoding($data[$i], "UTF-8", "auto");
		}
	}
}
fclose($fp);

$Question 	= $Q[1];
$qOption[1] = $Q[2];
$qOption[2] = $Q[3];
$qOption[3] = $Q[4];
$qOption[4] = $Q[5];
$Answer 	= $Q[6];
$qLevel 	= $Q[7];

//$DateTime = date( "Y/m/d H:i:s", time() );

// ====================
// output page
// ====================

$i_pagestyle->print_header();
$i_pagestyle->print_body_begin();
$i_pagestyle->print_main_begin();
$i_pagestyle->print_home_button();



if($isDebug == true)
{
	echo "
	withWav: $withWav</br>
	isTest: $isTest</br>
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	pageTitle: $pageTitle</br>
	group: $GroupName</br>
	qSet: $qSet</br>
	qNumMax: $qNumMax</br>
	qOrder: $qOrder[1],$qOrder[2],$qOrder[3],$qOrder[4],$qOrder[5]</br>
	QuizNumber: $QuizNumber</br>
	Score: $Score</br>
	wavPlayCounter: $wavPlayCounter</br>
	userAnswer: $userAnswer</br>
	ElapsedTime_s: $ElapsedTime_s</br>
	ElapsedTime_ss: $ElapsedTime_ss</br>
	Question: $Question</br>
	option1: $qOption[1]</br>
	option2: $qOption[2]</br>
	option3: $qOption[3]</br>
	option4: $qOption[4]</br>
	Answer: $Answer</br>
	sqlTable: $sqlTable</br>
	";
}

if ( $isTest == 0 )
{
	echo "<h2>第 $QuizNumber 問の回答結果</h2>\n";
}
else
{
	echo "<h2>回答は正常に送信されました</h2>\n";
}


$isCorrect = 0;
if($withWav == 0 || $wavPlayCounter != 0)
{
	if( $userAnswer == $Answer )
	{
		$Score 		+= 1;
		$isCorrect  = 1;
		if ( $isTest == 0 )
		{
			echo "<h3>正解です！</h3>\n";
		}
	}
	else
	{
		if ( $isTest == 0 )
		{
			echo "<h3>不正解です</h3>\n";
			echo "出題された単語は <Font Color=\"#ff0000\">$Question</font> でした。<br>\n";
			echo "意味は <b>$qOption[$Answer]</b> です。<br>\n";
		}
	}
}
else
{
	$isCorrect  = -1;
	$ElapsedTime_s  = 999;
	$ElapsedTime_ss = 99;
		
	echo "<h3>音声が再生されませんでした。</h3>\n";
	echo "音声を再生せずに送信された回答は無効です。<br>\n";
}
echo "<br>\n";


$Rate 		= $Score/$QuizNumber*100;
$RateFormat = sprintf( "%.2f", $Rate);

$ElapsedTimeFormat = $ElapsedTime_s + $ElapsedTime_ss/100;

if ( $isTest == 0 )
{
echo <<<EOF
	[回答時間] $ElapsedTimeFormat 秒</br>
	[これまでの正解数] $QuizNumber 問中 <b>$Score</b>　問正解　(正解率 $RateFormat %)</br>
	</br>
EOF;
}

// send results to mysql
$i_mysql->connect();
if ($withWav == 1)
{
$sql_insert = "INSERT INTO $sqlTable(
with_wav, is_test, trial_number, username, groupname, quiz_set, quiz_number, quiz_level, user_answer, is_correct, wav_play_counter, elapsed_time) VALUES ('$withWav', '$isTest', '$trialNum', '$UserName', '$GroupName', '$qSet', '$qOrder[$QuizNumber]', '$qLevel', '$userAnswer', '$isCorrect', '$wavPlayCounter', '$ElapsedTimeFormat')";
}
else
{
$sql_insert = "INSERT INTO $sqlTable(with_wav, is_test, trial_number, username, groupname, quiz_set, quiz_number, quiz_level, user_answer, is_correct, elapsed_time) VALUES ('$withWav', '$isTest', '$trialNum', '$UserName', '$GroupName', '$qSet', '$qOrder[$QuizNumber]', '$qLevel', '$userAnswer', '$isCorrect', '$ElapsedTimeFormat')";
}
mysql_query($sql_insert);
$i_mysql->close();

// go to next question
if ($QuizNumber < $qNumMax)
{
	$QuizNumber += 1;
	$link = '<a href=quiz.php?withWav=' . $withWav . '&isTest=' . $isTest . '&trialNum=' . $trialNum . '&UserName=' . $UserName . '&GroupName=' . $GroupName . '&qNumMax=' . $qNumMax . '&qSet=' . $qSet . '&QuizNumber=' . $QuizNumber . '&Score=' . $Score;
	for($i = 1; $i < $qNumMax+1; $i++){
		$link = $link . '&q' . $i . '=' . $qOrder[$i];
	}
	$link = $link . '>次の問題</a>'; 
EOF;

if ( $isTest == 1 )
{
echo <<<EOF
	<table border="0">
		<tr height="80">
			<td align="center" width="400">
				<font color="white">レベル $qLevel</font><br>
				<font size="7" color="white">$Question</font>
			</td>
			
			<td align="center" width="180">
			</td>
		</tr>
		
		<tr height="150" valign="middle">
			<td align="center">
EOF;
}
			echo $link;

if ( $isTest == 1 )
{
echo <<<EOF
			</td>
			
			<td></td>
		</tr>
	</table>
EOF;
}
}
else
{
	echo "<p>これで問題は終わりです。お疲れさまでした。</p>\n";
	
	if($isTest == 1)
	{
// ====================
// Statistics for all
// ====================

	// get results from mysql
	$i_mysql->connect();
		$sql_delete = "DELETE FROM $sqlTable 
		WHERE with_wav	= '$withWav'
			AND is_test   = '$isTest'
			AND username  = '$UserName'
			AND groupname = '$GroupName'
			AND trial_number = '$trialNum'
			AND quiz_set = '$qSet'
			AND result_id NOT IN 
				(SELECT max_result_id FROM
					(SELECT MAX(result_ID) AS max_result_id, quiz_level, quiz_number 
					FROM $sqlTable 
					WHERE with_wav = '$withWav'
					AND username   = '$UserName'
					AND groupname  = '$GroupName'
					AND trial_number = '$trialNum'
					AND quiz_set = '$qSet'
					GROUP BY `quiz_number`) tmp);";
		mysql_query($sql_delete);
		
		$sql_select = "SELECT quiz_level, 
			SUM( is_correct ) AS score, 
			COUNT( is_correct ) AS total,
			FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate,
			FORMAT( AVG( elapsed_time ) , 2 ) AS average, 
			FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std,
			FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
		FROM $sqlTable 
		WHERE with_wav	= '$withWav'
			AND is_test = '$isTest'
			AND username =  '$UserName'
			AND groupname =  '$GroupName'
			AND trial_number = '$trialNum'";
	$sql_result = mysql_query($sql_select);

echo <<<EOF
<h3> 全体結果 </h3>	
	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
		<tr>
			<th align="center">単語レベル</th>
			<th align="center">正答数</th>
			<th align="center">正答率[%]</th>			
			<th align="right">平均[秒]</th>
			<th align="right">ばらつき</th>
			<th align="right">安定性</th>
		</tr>
EOF;

	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
		printf ("<tr>
		<td align=\"center\">%s</td>
		<td align=\"center\">%s 問中　%s　問 </td>
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td>
		<td align=\"right\">%s</td>
		</tr>\n", 
		"1-7", $row["total"], $row["score"], $row["rate"], $row["average"], $row["std"], $row["cv"]);
		$totalNum[$row["quiz_level"]] = $row["total"];
	}
	echo("</table></p>\n");
EOF;
	
	$sql_select = "SELECT quiz_level, 
			SUM( is_correct ) AS score, 
			COUNT( is_correct ) AS total,
			FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate,
			FORMAT( AVG( elapsed_time ) , 2 ) AS average, 
			FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std,
			FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
		FROM $sqlTable 
		WHERE with_wav	= '$withWav'
			AND is_test = '$isTest'
			AND username =  '$UserName'
			AND groupname =  '$GroupName'
			AND trial_number = '$trialNum'
		GROUP BY quiz_level
		ORDER BY quiz_level ASC";
	$sql_result = mysql_query($sql_select);
	
echo <<<EOF
	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
		<tr>
			<th align="center">単語レベル</th>
			<th align="center">正答数</th>
			<th align="center">正答率[%]</th>			
			<th align="right">平均[秒]</th>
			<th align="right">ばらつき</th>
			<th align="right">安定性</th>
		</tr>
EOF;

	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
		printf ("<tr>
		<td align=\"center\">%s</td>
		<td align=\"center\">%s 問中　%s　問 </td>
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td>
		<td align=\"right\">%s</td>
		</tr>\n", 
		$row["quiz_level"], $row["total"], $row["score"], $row["rate"], $row["average"], $row["std"], $row["cv"]);
		$totalNum[$row["quiz_level"]] = $row["total"];
	}
	echo("</table></p>\n");


	// ====================
	// Statistics for correct answers
	// ====================

	// get results from mysql
	$sql_select = "SELECT quiz_level, 
			COUNT( is_correct ) AS total,
			FORMAT( AVG( elapsed_time ) , 2 ) AS average, 
			FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std,
			FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
		FROM $sqlTable 
		WHERE with_wav	= '$withWav'
			AND is_test = '$isTest'
			AND username = '$UserName'
			AND groupname = '$GroupName'
			AND trial_number = '$trialNum'
			AND is_correct = 1
		GROUP BY quiz_level 
		ORDER BY quiz_level ASC";
	$sql_result = mysql_query($sql_select);

	echo <<<EOF
	<h3> 正答した問題の結果　</h3>

	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
		<tr>
			<th align="center">単語レベル</th>
			<th align="center">正答数[問]</th>
			<th align="right">正答率[%]</th>				
			<th align="right">平均[秒]</th>
			<th align="right">ばらつき</th>
			<th align="right">安定性</th>
		</tr>
EOF;

	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
		printf ("<tr>
		<td align=\"center\">%s</td>
		<td align=\"center\">%s</td>
		<td align=\"center\">%.4s</td>
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td>
		<td align=\"right\">%s</td>
		</tr>\n", 
		$row["quiz_level"], $row["total"], $row["total"]/$totalNum[$row["quiz_level"]]*100, $row["average"], $row["std"], $row["cv"]);
	}
	echo("</table></p>\n");

	

	// ====================
	// Statistics for wrong answers
	// ====================

	// get results from mysql
	$sql_select = "SELECT quiz_level, 
			SUM( is_correct ) AS score, 
			COUNT( is_correct ) AS total,
			FORMAT( AVG( elapsed_time ) , 2 ) AS average, 
			FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std,
			FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
		FROM $sqlTable 
		WHERE with_wav	= '$withWav'
			AND is_test = '$isTest'
			AND username = '$UserName'
			AND groupname = '$GroupName'
			AND trial_number = '$trialNum'
			AND is_correct = 0
		GROUP BY quiz_level 
		ORDER BY quiz_level ASC";
	$sql_result = mysql_query($sql_select);

	echo <<<EOF
	<h3> 誤答した問題の結果　</h3>

	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
		<tr>
			<th align="center">単語レベル</th>
			<th align="center">誤答数[問]</th>
			<th align="right">誤答率[%]</th>				
			<th align="right">平均[秒]</th>
			<th align="right">ばらつき</th>
			<th align="right">安定性</th>
		</tr>
EOF;

	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
		printf ("<tr>
		<td align=\"center\">%s</td>
		<td align=\"center\">%s</td>
		<td align=\"right\">%.4s</td> 
		<td align=\"right\">%s</td> 
		<td align=\"right\">%s</td>
		<td align=\"right\">%s</td>
		</tr>\n", 
		$row["quiz_level"], $row["total"], $row["total"]/$totalNum[$row["quiz_level"]]*100, $row["average"], $row["std"], $row["cv"]);
	}
	echo("</table></p>\n");

	
	$i_mysql->close();
	printf("</table></p>");
	} // if $isTest
	
	echo "<p><img src=\"";
	if($Rate == 100)
	{
		echo "$imgDir/10jump.JPG";
	}
	elseif($Rate > 75)
	{
		echo "$imgDir/05happy.JPG";
	}
	elseif($Rate > 50)
	{
		echo "$imgDir/01mameda.JPG";
	}
	elseif($Rate > 25)
	{
		echo "$imgDir/04disappointed.JPG";
	}
	elseif($Rate > 0)
	{
		echo "$imgDir/23scolded.JPG";
	}
	elseif($Rate == 0)
	{
		echo "$imgDir/14no.JPG";
	}
	echo "\" width=\"180\"><br></p>";
}


$i_pagestyle->print_footer();
?>