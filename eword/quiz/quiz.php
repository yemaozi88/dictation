<?php
/*
 * 2012/10/14
 * quiz page for english word quiz of yamauchi project
 * quiz.php loads the question list and displays a question and options according to the question order
 *
 * RECEIVE
 * intro.php/result.php
 * SEND
 * result.php
 *
 * NOTE
 * quiz.php is based on quiz.cgi
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

$isFirst = $_POST['isFirst'];
$config  = parse_ini_file("config.ini", false);
include("../../_class/c_pagestyle.php");

// ====================


// ====================
// get form data
// ====================

$qNumMax   = $config["qNumMax"];

if( $isFirst == 1) // if it is the first time, get data with POST
{
	$withWav 	= $_POST['withWav'];
	$isTest  	= $_POST['isTest'];
	$trialNum 	= $_POST['trialNum'];
	$UserName   = $_POST['UserName'];
	$GroupName  = $_POST['GroupName'];
	$qSet 		= $_POST['qSet'];
	$qNumMax    = $_POST['qNumMax'];	
	$QuizNumber = 1;
	$Score 		= 0;

	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_POST["q$i"];
	}
}
else // if it is not the first time, get data with GET
{
	$withWav 	= $_GET['withWav'];
	$isTest  	= $_GET['isTest'];
	$trialNum 	= $_GET['trialNum'];
	$UserName   = $_GET['UserName'];
	$GroupName  = $_GET['GroupName'];
	$qSet 		= $_GET['qSet'];
	$qNumMax    = $_GET['qNumMax'];
	$QuizNumber = $_GET['QuizNumber'];
	$Score 		= $_GET['Score'];
	
	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_GET["q$i"];
	}	
}

if($withWav == 1 && $isTest == 1)
{
	$pageTitle = '聴いて答える問題（実力テスト）';
}
elseif($withWav == 0 && $isTest == 1)
{
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
$jsDir    = $config["jsDir"];
$qDir 	  = $config["qDir"];
$qList	  = $qDir . "/set" . $qSet . "/filelist.csv";
$wavDir   = $qDir . "/set" . $qSet;
$sqlTable = $config["sqlTable"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);


// ====================
// load the question
// ====================

$fp = fopen($qList, "r");
while($data = fgetcsv($fp))
{	
//	echo "there are ";
//	echo count($data);
//	echo " columns in the csv.";
	
	for($i=0; $i<count($data);  $i++)
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
$qLevel  	= $Q[7];


// ====================
// output page
// ====================

$i_pagestyle->print_header();
if($withWav == 1)
{
        $i_pagestyle->print_body_begin();
}
else
{
        $i_pagestyle->print_body_begin_timer_onload();
}
$i_pagestyle->print_main_begin();
$i_pagestyle->print_home_button();



if($isDebug == true)
{
	echo "
	withWav: $withWav</br>
	isTest: $isTest</br>
	isFirst: $isFirst</br>
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qList: $qList</br>
	wavDir: $wavDir</br>
	qNumMax: $qNumMax</br>
	qOrder: $qOrder[1],$qOrder[2],$qOrder[3],$qOrder[4],$qOrder[5]</br>
	QuizNumber: $QuizNumber</br>
	qNumRemain: $qNumRemain</br>
	Score: $Score</br>
	Rate: $Rate</br>
	RateFormat: $RateFormat</br>
	question: $Question</br>
	option1: $qOption[1]</br>
	option2: $qOption[2]</br>
	option3: $qOption[3]</br>
	option4: $qOption[4]</br>
	qLevel: $qLevel</br>
	";
}

echo <<<EOF
<form action="result.php" method="post" >
	<h2>第 $QuizNumber 問</h2>
	<script type="text/javascript"> timeMax = 20</script>
        <script type="text/javascript" src="$jsDir/timer3.js"></script>
	
	<p align="center">
	<table border="0">
		<tr height="80">

<!-- left-top window -->
			<td align="center" width="400">
EOF;

if( $isTest == 0 )
{
	echo "レベル $qLevel<br>\n";
}

if( $withWav == 0)
{
echo <<<EOF
				<font size="7" color="black">$Question</font>
EOF;
}
/*
else
{
echo <<<EOF
				<button type="button" id="playbutton" onclick="clickButton()" disabled="true">再生</button>
				<audio id="question" preload="auto" oncanplay="wavCanPlay();" onended="wavCanPlay();">
					<source src="$wavDir/$qOrder[$QuizNumber].wav" type="audio/wav" />
					<source src="$wavDir/$qOrder[$QuizNumber].mp3" type="audio/mp3" />
					<p>Your browser can not open wav file. Please install appropriate plugin.</p>
				</audio>\n
EOF;
}
*/

echo <<<EOF
			</td>
			
<!-- right-top window -->
			<td align="center" width="180">
<table border="0">
	<tr>
		<td width="180" height="60" align="center" bgcolor="#F5DEB3">
			<span id="RemainingTime" style="font-size:36px;">20:00</span> 秒
		</td>
	</tr>
</table>
			</td>
		</tr>

		
<!-- left-bottom window -->
		<tr height="150" valign="middle">
			<td align="center">
<table border="0">
EOF;

if($withWav == 1)
{
echo <<<EOF
<tr height="20">
	<td rowspan="4">
		<button type="button" id="playbutton" onclick="clickButton()" disabled="true">再生</button>
		<audio id="question" preload="auto" oncanplay="wavCanPlay();" onended="wavCanPlay();">
			<source src="$wavDir/$qOrder[$QuizNumber].wav" type="audio/wav" />
			<source src="$wavDir/$qOrder[$QuizNumber].mp3" type="audio/mp3" />
			<p>Your browser can not open wav file. Please install appropriate plugin.</p>
		</audio>\n
	</td>
	<td align="right" width="20">
		<input type="radio" name="userAnswer" value="1" onClick="this.form.submit()">
	</td>
	<td><font color="black">$qOption[1]</font></td>
</tr>
EOF;

	for($i = 2; $i < 5; $i++){
		echo "<tr height=\"20\">\n";
		echo "<td align=\"right\" width=\"20\">\n";
		echo "<input type=\"radio\" name=\"userAnswer\" value=\"" . $i . "\" onClick=\"this.form.submit()\">";
		echo "</td>\n";
	
		echo "<td><font color=\"black\">" . $qOption[$i] . "</font></td>\n";
		echo "</tr>\n";
	}
}
else
{
	//echo "<tr height=\"20\">";
	for($i = 1; $i < 5; $i++){
		echo "<tr height=\"20\">\n";
		echo "<td align=\"right\" width=\"20\">\n";
		echo "<input type=\"radio\" name=\"userAnswer\" value=\"" . $i . "\" onClick=\"this.form.submit()\">";
		echo "</td>\n";
	
		echo "<td><font color=\"black\">" . $qOption[$i] . "</font></td>\n";
		echo "</tr>\n";
	}
}

//$QuizNumber -= 1;
$qNumRemain = $qNumMax - $QuizNumber;
if($QuizNumer == 0)
{
	$Rate = 0;
}
else
{
	$Rate = $Score/$QuizNumber*100;
}
$RateFormat = sprintf( "%.2f", $Rate);

echo <<<EOF
</table>
			</td>
			

<!-- right-bottom window -->
			<td>
<table border="5" frame="void">
	<tr height="140">
		<td width="180" align="center">
EOF;

if($isTest == 0)
{
echo <<<EOF
		[これまでの成績]<br>
		$QuizNumber 問中 <b>$Score</b> 問正解<br> 
		(正解率 <b>$RateFormat</b>%)<br>
		<br>
EOF;

//$QuizNumber += 1;
}

if($qNumRemain==0)
{
	print("これが最後の問題です！");
}
else
{
	print("あと残り<b>$qNumRemain</b>問です。");
}


echo <<<EOF
		</td>
	</tr>
</table>
			
			</td>
		</tr>
	</table></p>

	
	<!-- <p align="center"><input type="submit" value="回答" /></p> -->

	<!-- Hidden Variables -->
	<p><input type="hidden" value="$withWav" id="withWav" name="withWav" /></p>
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" id="trialNum" name="trialNum" value="$trialNum" /></p>
	<p><input type="hidden" id="UserName" name="UserName" value="$UserName" /></p>
	<p><input type="hidden" id="GroupName" name="GroupName" value="$GroupName" /></p>	
	<p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
	<p><input type="hidden" value="$qNumMax" id="qNumMax" name="qNumMax" /></p>
	<p><input type="hidden" id="Score" name="Score" value="$Score" /></p>
	<p><input type="hidden" value="0" id="wavPlayCounter" name="wavPlayCounter" /></p>
	<p><input type="hidden" value="0000" id="ElapsedTime" name="ElapsedTime" /></p>
	<p><input type="hidden" value="2000" id="hiddenRemainingTime" name="hiddenRemainingTime" /></p>
	<p><input type="hidden" id="QuizNumber" name="QuizNumber" value="$QuizNumber" /></p>
EOF;


// question order
echo "<p>";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />";
}
echo "</p>";

echo <<<EOF
</form>
EOF;

$i_pagestyle->print_footer();
?>