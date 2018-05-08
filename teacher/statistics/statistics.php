<?php
/*
 * 2014/01/02
 * display the statistics for english word quiz of yamauchi project
 *
 * RECEIVE
 * select_date.php
 *
 * HISTORY
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
$qLevelMax = $config["qLevelMax"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();

// ====================

// ====================
// get form data
// ====================

$isTest    = 1;
$GroupName = $_GET['GroupName'];
$withWav   = $_GET['withWav'];
$UserName  = $_GET['UserName'];
$DateJ     = $_GET['DateJ'];
$DateE     = $_GET['DateE'];

$DateE_from = $DateE . ' 00:00:00';
$DateE_till = $DateE . ' 23:59:00';	


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
	pageTitle: $pageTitle</br>
	sqlTable: $sqlTable</br>
    qLevelMax: $qLevelMax</br>
	isTest: $isTest</br>
	withWav: $withWav</br>
    GroupName: $GroupName</br>
	UserName: $UserName</br>
    DateJ: $DateJ</br>
    DateE: $DateE</br>
	";
}


if($UserName == '_all')
{
    $sql_select = "SELECT timestamp, username 
        FROM $sqlTable
        WHERE groupname = '$GroupName' 
            AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
        GROUP BY username
        HAVING username <> ''
        ORDER BY username ASC";
    $sql_result = mysql_query($sql_select);
        
    $i = 0;
	while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)){
		$UserNameArray[$i] = $row["username"];
        $i = $i + 1;
    } // while
    $UserNameMax = $i;


    // ============================================================
    // rate of correct answer - all
    // ============================================================
echo <<<EOF
    <a href="#list_all">全体結果の一覧</a> / 
    <a href="#list_correct">正答した問題の結果一覧</a> / 
    <a href="#list_wrong">誤答した問題の結果一覧</a></br>
    
    </br>

    <a name="list_all"><h2>$DateJ - 全体結果の一覧</h2></a>
    <h3>正答率 [%]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {    
        /* get trial number */
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        /*
        if($isDebug == true)
        {
            printf("trialNum: %d\n", $trialNum);
        }
        */       
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["rate"]);
                $totalNum[$row["quiz_level"]] = $row["total"];
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["rate"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }

            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");
    echo("</br>");
    
        
    // ============================================================
    // reaction speed - all
    // ============================================================
echo <<<EOF
    <h3>反応速度 [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( AVG( elapsed_time ) , 2 ) AS average
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["average"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( AVG( elapsed_time ) , 2 ) AS average
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["average"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }  

            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // standard deviation - all
    // ============================================================
echo <<<EOF
    <h3>ばらつき [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["std"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["std"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }  

            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // stability - all
    // ============================================================
echo <<<EOF
    <h3>安定性</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["cv"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["cv"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }  

            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br></br><hr></br></br>"); 
    
    
    
    
    
    // ============================================================
    // rate of correct answer - correct
    // ============================================================
echo <<<EOF
    <a name="list_correct"><h2>$DateJ - 正答した問題の結果一覧</h2></a>
    <h3>正答率 [%]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {    
        /* get trial number */
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        /*
        if($isDebug == true)
        {
            printf("trialNum: %d\n", $trialNum);
        }
        */       
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {                
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["rate"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( SUM( is_correct )/COUNT( is_correct )*100, 2) AS rate
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["rate"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }  
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");
    echo("</br>");
    
        
    // ============================================================
    // reaction speed - correct
    // ============================================================
echo <<<EOF
    <h3>反応速度 [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( AVG( elapsed_time ) , 2 ) AS average
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["average"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( AVG( elapsed_time ) , 2 ) AS average
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 1
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["average"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // standard deviation - correct
    // ============================================================
echo <<<EOF
    <h3>ばらつき [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["std"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 1
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["std"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // stability - correct
    // ============================================================
echo <<<EOF
    <h3>安定性</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["cv"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 1
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["cv"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br></br><hr></br></br>");  
    
    

    
    
    // ============================================================
    // rate of correct answer - wrong
    // ============================================================
echo <<<EOF
    <a name="list_wrong"><h2>$DateJ - 誤答した問題の結果一覧</h2></a>
    <h3>誤答率 [%]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {    
        /* get trial number */
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        /*
        if($isDebug == true)
        {
            printf("trialNum: %d\n", $trialNum);
        }
        */       
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( (1 - SUM( is_correct )/COUNT( is_correct ))*100, 2) AS rate
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {                
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%.2f</td>",
                $UserName, $row["rate"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( (1-SUM( is_correct )/COUNT( is_correct ))*100, 2) AS rate
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["rate"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.1f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }  
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");
    echo("</br>");
    
        
    // ============================================================
    // reaction speed - wrong
    // ============================================================
echo <<<EOF
    <h3>反応速度 [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( AVG( elapsed_time ) , 2 ) AS average
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                    AND is_correct = 0";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["average"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( AVG( elapsed_time ) , 2 ) AS average
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 0
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["average"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // standard deviation - wrong
    // ============================================================
echo <<<EOF
    <h3>ばらつき [秒]</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                    AND is_correct = 0";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["std"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time ) , 2 ) AS std
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 0
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["std"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    // ============================================================
    // stability - wrong
    // ============================================================
echo <<<EOF
    <h3>安定性</h3>
 	<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
        <tr>
			<th align="center">氏名</th>
			<th align="center">全体</th>
EOF;

    for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++)
    {
			printf("<th align=\"center\">レベル%d</th>\n", $qLevel);
    }
    printf("        </tr>\n");

    for($i = 0; $i < $UserNameMax; $i++)
    {   
        /* get trial number*/
        $UserName = $UserNameArray[$i];
        $sql_select = "SELECT quiz_level, 
            MAX( trial_number ) AS trial_num_max 
            FROM $sqlTable 
            WHERE with_wav	= '$withWav'
                AND is_test     = '$isTest'
                AND username    = '$UserName'
                AND groupname   = '$GroupName'";
        $sql_result = mysql_query($sql_select);
        $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
        $trialNumMax = $row["trial_num_max"];
        $trialNum   = $trialNumMax;
        
        if($trialNum != 0)
        {
            /* all */
            $sql_select = "SELECT quiz_level, 
                    FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
                FROM $sqlTable 
                WHERE with_wav       = '$withWav'
                    AND is_test      = '$isTest'
                    AND username     = '$UserName'
                    AND groupname    = '$GroupName'
                    AND trial_number = '$trialNum'
                    AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                    AND is_correct = 0";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                printf ("<tr>           
                <td align=\"center\">%s</td> 
                <td align=\"right\">%s</td>",
                $UserName, $row["cv"]);
            }

            /* each level */
            $sql_select = "SELECT quiz_level,
                FORMAT( STDDEV_SAMP( elapsed_time )/AVG( elapsed_time ) , 2 ) AS cv
            FROM $sqlTable 
            WHERE with_wav	 = '$withWav'
                AND is_test      = '$isTest'
                AND username     = '$UserName'
                AND groupname    = '$GroupName'
                AND trial_number = '$trialNum'
                AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
                AND is_correct = 0
            GROUP BY quiz_level
            ORDER BY quiz_level ASC";
            $sql_result = mysql_query($sql_select);

            while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) { 
                $quiz_level = $row["quiz_level"];
                $resultArray[$quiz_level] = $row["cv"];
            }
            for($qLevel = 1; $qLevel < $qLevelMax+1; $qLevel++){
                if($resultArray[$qLevel] != 99999)
                {
                    printf("<td align=\"right\">%.2f</td>", $resultArray[$qLevel]);
                }
                else
                {
                    printf("<td align=\"right\"></td>");                    
                }
                $resultArray[$qLevel] = 99999;
            }
            
            echo("</tr>\n");
        } // if(trialNum != 0)
    } // while
    echo("</table></p>\n");   
    echo("</br>");
    
    
    
    
    
}
else // =======================================================================
{
    // get trial number
    $sql_select = "SELECT MAX( trial_number ) AS trial_num_max 
        FROM $sqlTable 
        WHERE with_wav	= '$withWav'
            AND is_test = '$isTest'
            AND username =  '$UserName'
            AND groupname =  '$GroupName'";
    $sql_result = mysql_query($sql_select);
    $row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
    $trialNumMax = $row["trial_num_max"];

    $trialNum   = $trialNumMax;
    if($isDebug == true)
    {
        printf("trialNum: %d\n", $trialNum);
    }
    

echo <<<EOF
	<h2>$UserName さんの回答結果</h2>
	$UserName さんは、
            <font color="red"><b>$DateJ</b></font>に 
            <font color="red"><b>$trialNumMax</b></font> 回 
            <font color="red"><b> 
EOF;

if($withWav == 0)
{
    echo "見て答える問題（テスト）";
}
else
{
    echo "聴いて答える問題（テスト）";    
}


echo <<<EOF
        </b></font>に挑戦しました。</br>
	以下はその最新の結果です。</br>
EOF;

	
	// ====================
	// Statistics for all
	// ====================

	// get results from mysql
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
            AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'";
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
		<td align=\"right\">%.1f</td> 
		<td align=\"right\">%.2f</td> 
		<td align=\"right\">%.2f</td>
		<td align=\"right\">%.2f</td>
		</tr>\n", 
		"1-7", $row["total"], $row["score"], $row["rate"], $row["average"], $row["std"], $row["cv"]);
		$totalNum[$row["quiz_level"]] = $row["total"];
	}
	echo("</table></p>\n");
	
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
            AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
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
		<td align=\"right\">%.1f</td> 
		<td align=\"right\">%.2f</td> 
		<td align=\"right\">%.2f</td>
		<td align=\"right\">%.2f</td>
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
            AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
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
		<td align=\"center\">%.1f</td>
		<td align=\"right\">%.2f</td> 
		<td align=\"right\">%.2f</td>
		<td align=\"right\">%.2f</td>
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
            AND timestamp BETWEEN '$DateE_from' AND '$DateE_till'
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
		<td align=\"right\">%.1f</td> 
		<td align=\"right\">%.2f</td> 
		<td align=\"right\">%.2f</td>
		<td align=\"right\">%.2f</td>
		</tr>\n", 
		$row["quiz_level"], $row["total"], $row["total"]/$totalNum[$row["quiz_level"]]*100, $row["average"], $row["std"], $row["cv"]);
	}
	echo("</table></p>\n");
} // if($UserName, '_all')


$i_mysql->close();
$i_pagestyle->print_footer();
?>