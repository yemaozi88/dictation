<?php
/*
 * 2012/10/23
 * index page for english word quiz of yamauchi project
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$pageTitle = "速読と速聴のための英単語力クイズ";
$srcDir    = "../_src";
include("./_class/c_pagestyle.php");

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);


// ====================
// output page
// ====================

$i_pagestyle->print_header();
$i_pagestyle->print_body_begin();
$i_pagestyle->print_main_begin();



echo <<<EOF
<h2>速読と速聴のための英単語力クイズへようこそ！</h2>
<div Align="right">
	<img src="http://users009.lolipop.jp/cnt/accnt.php?cnt_id=2014252&ac_id=LA08824220&mode=total">
</div>
</br>
<img src="top_slim.jpg" alt="" class="head_photo" border="0" />

EOF;

$i_pagestyle->print_footer();
?>