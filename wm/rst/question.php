<?php
/*
 * 2018/05/05
 * show the quiz for the working memory test of yamauchi project
 *
 * RECEIVE
 * SEND
 *
 * NOTE:
 * qNum: quiz_num in the database.
 * 
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = false;

$config   = parse_ini_file("../config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$srcDir    = $config["srcDir"];
$jsDir     = $config["jsDir"];
$pageTitle = $config["pageTitle"];
//$wavDir	   = $config["wavDir"];

$timeLimit = $config["timeLimit"];

$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();


// ====================
// wav files to be loaded
// ====================

$isFirst = 0;
if(isset($_POST['isFirst']))
{
    $isFirst = $_POST['isFirst'];
}

if($isFirst == 1) // if it is the first time
{
        $isListening = $_POST['isListening'];
	$isTest    = $_POST['isTest'];
	$trialNum  = $_POST['trialNum'];
        if($isTest == 0){
            $qNumMax = 1;
        }else{
            $qNumMax = $config["qNumMax_rst"];    
        }
	$UserName  = $_POST['UserName'];
	$GroupName = $_POST['GroupName'];
	$qSet	   = $_POST['qSet'];
	$QuizNumber = 1;
		
	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_POST["q$i"];
	}	    
}
else
{
        $isListening = $_GET['isListening'];
    	$isTest     = $_GET['isTest'];
        if($isTest == 0){
            $qNumMax = 1;
        }else{
            $qNumMax = $config["qNumMax_rst"];    
        }        
	$trialNum   = $_GET['trialNum'];
	$UserName   = $_GET['UserName'];
	$GroupName  = $_GET['GroupName'];
	$qSet	    = $_GET['qSet'];
	$QuizNumber = $_GET['QuizNumber'];
	
	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_GET["q$i"];
	}
}

//$textNum = $qSet * ( $qOrder[$QuizNumber] - 1 ) + 1;


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
            isListening: $isListening</br>
	isFirst: $isFirst</br>
	isTest: $isTest</br>	
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qOrder: $qOrder[1],$qOrder[2],$qOrder[3]</br>
	QuizNumber: $QuizNumber</br>
	qNumMax: $qNumMax</br>
	sqlTableQuestion: $sqlTableQuestion</br>
	";
}

echo <<<EOF

	<h2>第 $QuizNumber 問</h2>
	<script type="text/javascript"> var qWord1 = []; </script>
	<script type="text/javascript"> var qSentence = []; </script>
        
<!--    <script type="text/javascript" src="play.js"></script> -->
        
        <script type="text/javascript"> timeMax = $timeLimit</script>
        <script type="text/javascript" src="$jsDir/timer3.js"></script>
EOF;

for ($i = 1; $i <= $qSet; $i++)
{
	$sql_select = "SELECT quiz_num, question, last_word, answer
		FROM $sqlTableQuestion
		WHERE is_listening = 0 AND is_test = $isTest AND quiz_set = $qSet AND quiz_num = ($QuizNumber -1) * $qSet + $i";
	$sql_result = mysql_query($sql_select);

	$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);

        $qNum      = $row["quiz_num"];
	$qSentence = $row["question"];
	$qWord     = $row["last_word"];
        $qWord1    = $qWord[0];
        
	/*
	 * send variable 'qWord' to Javascript
	 */
echo <<<EOF

        <script type="text/javascript"> 
            qWord1[$i]    = "$qWord1";
            qSentence[$i] = "$qSentence";
        </script>
EOF;
}        
    
$i = 1;
echo <<<EOF
<h3>文の正誤</h3>
<center>
<table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
    <tr>
        <td width="300" align="center">
            <div id="qTitle">$qSet 文中 第 0 文目</div>
        </td>
        <td width="150" align="center" bgcolor="#FFFFFF">
            <span id="RemainingTime" style="font-size:24px;">$timeLimit:00</span> 秒
        </td>
    </tr>
    <tr>
        <td colspan="2" height="100">
            <div id="qText"><center>ここに問題が表示されます。</center></div>            
        </td>
    </tr>

    <tr >
        <td align="center" valign="middle">
            <div id="aTF"></div>
        </td>
        
        <td>
            <center><button type="button" id="showbutton" style="color:white;background-color: #4d1a00;border-color:white" onclick="clickButton_show($i, $qSet)">問題を見る</button></center>
        </td>

<!--
        <td align = "center">
            <button type="button" id="goNext" disabled onclick="clickButton_next($i, $qSet)">
                次へ
            </button>              
        </td>
-->
    </tr>
</table>
</center>

<br>
<h3>最後の単語</h3>
<form action="result.php" method="post">
EOF;

for ($i = 1; $i <= $qSet; $i++){
echo <<<EOF
    <div id="aWord$i"></div>
EOF;
}

echo <<<EOF
<div id="sendResult"></div>
    <!-- Hidden Variables -->
    <p><input type="hidden" value="0" id="isListening" name="isListening" /></p>
    <p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
    <p><input type="hidden" value="$isFirst" id="isFirst" name="isFirst" /></p>
    <p><input type="hidden" value="1" id="isFirst" name="isFirst" /></p>
    <p><input type="hidden" value="$trialNum" id="trialNum" name="trialNum" /></p>
    <p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
    <p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>
    <p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
    <p><input type="hidden" id="QuizNumber" name="QuizNumber" value="$QuizNumber" /></p>

    <!-- not used, just to let timer work. -->
    <p><input type="hidden" value="" id="ElapsedTime" name="ElapsedTime" /></p>
    <p><input type="hidden" value="" id="hiddenRemainingTime" name="hiddenRemainingTime" /></p>          
    
    <!-- User answers. -->
    <p><input type="hidden" value="" id="ElapsedTimeAll" name="ElapsedTimeAll" /></p>
    <p><input type="hidden" value="" id="TFall" name="TFall" /></p>

EOF;

// question order
echo "\n<p>\n";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />\n";
}
echo "</p>\n";
echo "</form>";
           
$i_mysql->close();
$i_pagestyle->print_footer();
?>