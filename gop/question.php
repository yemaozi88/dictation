<?php
/*
 * 2013/09/13
 * play a sound file for the working memory test of yamauchi project
 *
 * RECEIVE
 * SEND
 *
 * HISTORY

 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = true;

$config   = parse_ini_file("config.ini", false);
include("../_class/c_pagestyle.php");
include("../_class/c_mysql.php");

// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$sentenceNumberMax = $config["sentenceNumberMax"];
$practiceNumberMax = $config["practiceNumberMax"];

$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableGOPscore = $config["sqlTableGOPscore"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();


$isTest      = $_GET['isTest'];
$isFirst     = $_GET['isFirst'];
$trialNumber = $_GET['trialNumber'];
$UserName    = $_GET['UserName'];
$GroupName   = $_GET['GroupName'];
$textID      = 1;
$practiceNumber = $_GET['practiceNumber'];
//$GOP = $_GET['GOP'];
for($i = 0; $i < $sentenceNumberMax; $i++)
{
        $GOP[$i] = $_GET["GOP$i"];
}

// ====================
// output page
// ====================

$i_pagestyle->print_header();

if($isDebug == true)
{
    echo "
        isTest: $isTest</br>
        isFirst: $isFirst</br>
        trialNumber: $trialNumber</br>
        UserName: $UserName</br>
        GroupName: $GroupName</br>
        practiceNumberMax: $practiceNumberMax</br>
        practiceNumber: $practiceNumber</br>
        sqlTableQuestion: $sqlTableQuestion</br>
        sqlTableGOPscore: $sqlTableGOPscore</br>
        sqlTableResult: $sqlTableResult</br>
        GOP:</br>
        ";
    for($i = 0; $i < $sentenceNumberMax; $i++)
    {
        echo "$i: $GOP[$i]</br>";
    }
}

echo <<<EOF
<form action="result.php" method="post" >
    <script type="text/javascript"> var qWord1 = []; </script>
    <script type="text/javascript" src="play.js"></script>

    <h2> 内容に関する問題: $practiceNumber 回目 </h2>
EOF;

    /* send GOP score to SQL */
    for($i = 0; $i < $sentenceNumberMax; $i++)
    {
        $sql_insert = "INSERT INTO $sqlTableGOPscore(
        is_test, trial_number, username, groupname, text_id, sentence_number, gop
        ) VALUES (
        '$isTest', '$trialNumber', '$UserName', '$GroupName', '$textID', '$i', '$GOP[$i]')";
        mysql_query($sql_insert);    
    }
        
    /* get question from mysql */
    $qList = "filelist1.csv";
    
    $fp = fopen($qList, "r");
    while($data = fgetcsv($fp))
    {
        $questionNumber   = $data[0];
        $questionContents = mb_convert_encoding($data[1], "UTF-8", "auto");
        $qOption[1]       = mb_convert_encoding($data[2], "UTF-8", "auto");
        $qOption[2]       = mb_convert_encoding($data[3], "UTF-8", "auto");
        $qOption[3]       = mb_convert_encoding($data[4], "UTF-8", "auto");
        $qOption[4]       = mb_convert_encoding($data[5], "UTF-8", "auto");
        $answer           = $data[6];

echo <<<EOF
        
        <table width="500" border="0">
            <tr><td colspan="2"> 第 $questionNumber 問　$questionContents </td></tr>
EOF;
        for($optionNumber = 1; $optionNumber < 5; $optionNumber++){
            echo "\n";
            echo "\t\t<tr height=\"20\"><td align=\"right\" width=\"20\">\n";
            echo "\t\t<input type=\"radio\" name=\"userAnswer$questionNumber\" value=\"" . $optionNumber . "\"></td>\n";
            echo "\t\t<td><font color=\"black\">" . $qOption[$optionNumber] . "</font></td></tr>\n";
            echo "\n";
        }
echo <<<EOF
        
        </table></br>
        
EOF;
    }
    fclose($fp);       
        
/*
    $sql_select_question = "SELECT question_number, question_contents, option1, option2, option3, option4 FROM $sqlTableQuestion WHERE text_id = $textID";
    $sql_result_question = mysql_query($sql_select_question);
    //mysql_set_charset('utf8');
    while ($row = mysql_fetch_array($sql_result_question, MYSQL_ASSOC)) {        
        $questionNumber   = $row["question_number"];
        $questionContents = $row["question_contents"];
        //$questionContents  = mb_convert_encoding($questionContents_, "UTF-8", "auto");
        $optionContents[1] = $row["option1"];
        $optionContents[2] = $row["option2"];
        $optionContents[3] = $row["option3"];
        $optionContents[4] = $row["option4"];
        printf("</p>問題 %d: %s</p></br>", $questionNumber, $questionContents);
        for ($optionNumber = 1; $optionNumber < 5; $optionNumber++){
            printf("%d: %s</br>", $optionNumber, $optionContents[$optionNumber]);
        }
    } // question
*/
    
/*
    $sql_select = "SELECT question_number, question_contents, option_number, option_contents 
        FROM gop_question 
        inner join gop_option on gop_question.question_id = gop_option.question_id 
        WHERE sentence_id = 1";
    $sql_result = mysql_query($sql_select);
    $questionNumber_ = 0;
    while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
        $questionNumber   = $row["question_number"];
        $questionContents = $row["question_contents"];
        $optionNumber     = $row["option_number"];
        $optionContents   = $row["option_contents"];

        
        if ($questionNumber_ != $questionNumber){
            printf("<p>第 %d 問 %s</p>", $questionNumber, $questionContents);
        }

echo <<<EOF
        <input type="radio" name="q$questionNumber" value="$optionNumber">$optionContents</br>
EOF;

//        printf("<option value="%d. %s</br>", $optionNumber, $optionContents);
        $questionNumber_ = $questionNumber;
    }
        
        // INSERT INTO $sqlTableResult(
	//is_test, trial_number, username, groupname, quiz_number, gop) VALUES ('$isTest, '$trialNum', '$UserName', '$GroupName', '$quiz_number', '$GOP')";

	//mysql_query($sql_insert);
*/
echo <<<EOF
	<br>
<!-- send variables to the next page as hidden -->
   <p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
   <p><input type="hidden" value="$isFirst" id="isFirst" name="isFirst" /></p>
   <p><input type="hidden" value="$trialNumber" id="trialNumber" name="trialNumber" /></p>
   <p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>	
   <p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>  
   <p><input type="hidden" value="$textID" id="textID" name="textID" /></p>  
   <p><input type="hidden" value="$practiceNumber" id="practiceNumber" name="practiceNumber" /></p>

    <input type="submit" value="回答を送信する">
</form>
EOF;
    
$i_mysql->close();
$i_pagestyle->print_footer();
?>