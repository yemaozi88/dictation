<?php
/*
 * 2015/09/23
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

$aspxURL = $config["aspxURL"];

$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableOption   = $config["sqlTableOption"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();


$isTest      = $_POST['isTest'];
$isFirst     = $_POST['isFirst'];
$trialNumber = $_POST['trialNumber'];
$UserName    = $_POST['UserName'];
$GroupName   = $_POST['GroupName'];
$sentenceNumber = $_POST['sentenceNumber'];
$practiceNumber = $_POST['practiceNumber'];
$GOP = $_POST['GOP'];
$questionNumberMax = $_POST['questionNumberMax'];


// ====================
// check user answers
// ====================

$sql_select = "SELECT question_number, question_answer FROM gop_question WHERE sentence_id = $sentenceNumber";
$sql_result = mysql_query($sql_select);
while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
    $questionAnswerArray[$row["question_number"]] = $row["question_answer"];
}

for($i = 1; $i < $questionNumberMax+1; $i++)
{
    $userAnswerArray[$i] = $_POST["q$i"];
    if ($userAnswerArray[$i] == $questionAnswerArray[$i]){
        $isCorrectArray[$i] = 1;
    }else{
        $isCorrectArray[$i] = 0;
    }
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
        aspxURL: $aspxURL</br>
        sentenceNumberMax: $sentenceNumberMax</br>
        sentenceNumber: $sentenceNumber</br>
        practiceNumberMax: $practiceNumberMax</br>
        practiceNumber: $practiceNumber</br>
        GOP: $GOP</br>
        questionNumberMax: $questionNumberMax</br>
        userAnswer: $userAnswerArray[1], $userAnswerArray[2]</br>
        questionAnswer: $questionAnswerArray[1], $questionAnswerArray[2]</br>
        sqlTableQuestion: $sqlTableQuestion</br>
        sqlTableOption: $sqlTableOption</br>
        sqlTableResult: $sqlTableResult</br>
    ";
}

        
echo <<<EOF
<form action="result.php" method="post" >
    <script type="text/javascript"> var qWord1 = []; </script>
    <script type="text/javascript" src="play.js"></script>

    <h2> 第 $sentenceNumber 文  の練習結果 </h2>
        
    <p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">

        <tr>
            <td width="80" align="center">GOP</td>
            <td width="80" align="center">$GOP</td>
            <td width="80" align="center"></td>        
        </tr>

EOF;

for($i = 1; $i < $questionNumberMax+1; $i++)
{
    printf("<tr>\n");    
    printf("<td align=\"center\">第 %d 問</td>\n", $i);
    printf("<td align=\"center\">%d</td>\n", $userAnswerArray[$i]);
    printf("<td align=\"center\">%d</td>\n", $isCorrectArray[$i]);
    printf("</tr>\n");    
}

echo <<<EOF
    </table></p>
EOF;


// send results to mysql
$isCorrect = 0;
for($i = 1; $i < $questionNumberMax+1; $i++)
{
    $sql_insert = "INSERT INTO $sqlTableResult(
        is_test, trial_number, username, groupname, sentence_id, practice_number, question_number, user_answer, is_correct) 
        VALUES ('$isTest', '$trialNumber', '$UserName', '$GroupName', '$sentenceNumber', $practiceNumber, '$i', '$userAnswerArray[$i]', '$isCorrect')";
    mysql_query($sql_insert);
}


if ($practiceNumber < $practiceNumberMax)
{
    $practiceNumber = $practiceNumber + 1;
}
else 
{
    $sentenceNumber = $sentenceNumber + 1;
    $practiceNumber = 1;
}

if ($sentenceNumber <= $sentenceNumberMax){
    $link = '<a href=' . $aspxURL . '?isTest=' . $isTest . '&isFirst=' . $isFirst . '&trialNumber=' . $trialNumber . '&UserName=' . $UserName . '&GroupName=' . $GroupName . '&sentenceNumber=' . $sentenceNumber . '&practiceNumber=' . $practiceNumber;
    $link = $link . '>次の問題</a>';
    echo $link;
}
else
{
    printf("<p>問題はこれで全部です。お疲れさまでした。</p>");
}
    
$i_mysql->close();
$i_pagestyle->print_footer();
?>