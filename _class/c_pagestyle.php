<?php
/*
 * 2012/10/13
 * class for page style
 *
 * FUNCTIONS
 * - set_variables($page_title, $src_dir)
 * - disp_variables()
 * - print_header()
 * - print_header_top()
 * - print_header_timer_start()
 * - print_groupSelecter($logo_dir)
 * - print_footer()
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


class c_pagestyle
{	
	// pre defined variables
	private $home_url	= "http://www.waschbaerli.com/dictation/index.php";
        //private $home_url       = "http://localhost/waschbaerli/dictation/index.php";
	private $site_title	= "English Word Quiz(test version)";	
	private $char_set 	= 'Shift_JIS';

	// variables those will be set by set_variables()
	private $page_title     = "";
	private $src_dir 	= "";
	private $css_dir 	= "";
	
	
	public function set_variables($page_title, $src_dir)
	{
		$this->page_title = $page_title;
		$this->src_dir	  = $src_dir;
		$this->css_dir	  = $src_dir . "/style.css";
	}
	
	
	public function disp_variables()
	{
		echo "home url:" . $this->home_url . "<br />";
		echo "site title: " . $this->site_title . "<br />";
		echo "page title: " . $this->page_title . "<br />";
		echo "char set: " . $this->char_set . "<br />";
		echo "source directory: " . $this->src_dir . "<br />";
		echo "css directory: " . $this->css_dir . "<br />";
	}
	
	
	public function print_header()
	{
		echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
	<meta http-equiv="Content/Type" content="application/xhtml+xml; charset=$this->char_set" />
	<!-- <meta name="keywords" content="type keywords" /> //>
	<!-- <meta name="description" content="type description" /> -->
	<title>$this->site_title</title>
	<link rel="stylesheet" href="$this->css_dir" type="text/css" />
</head>
EOF;
        }


        public function print_body_begin(){
            
            echo <<<EOF
            <body>
EOF;
        }
        
        
        public function print_body_begin_timer_onload(){
            
            echo <<<EOF
            <body onload="tid = setInterval('countTime()', 10)" onunload="clearInterval(tid)">
EOF;
	}
	
        
        public function print_home_button(){
            echo <<<EOF
	<div id="pannavi">
<a href=$this->home_url>HOME</a> > $this->page_title
	</div>
EOF;
	}

        
        public function print_main_begin()
        {
            echo <<<EOF
<!-- begin: main -->
<div id="main">
	
	<div id="header"><!-- begin: header -->
		<h1><!-- You should be the change that you want to see in the world - Mahatma Gandhi --></h1>
		<table border="0" cellpadding="0" cellspacing="0" width="900">
			<tr>
				<td><div id="table-left"><h2>$this->page_title</h2></div></td>
			<!--
				<td><div id="table-right"><b>TEL&FAX: +81-3-5841-6393</b><br /><img src="src/icon.gif" alt="" border="0" /> <a href="profile.html">会社情報</a>　<img src="src/icon.gif" alt="" border="0" /> <a href="contact.html">お問い合わせ</a></div></td>
			-->
			</tr>
		</table>
		<hr>
	</div><!-- end: header -->
	
	
<!-- begin: main contents -->
	<div id="container">
	<div id="contents">
EOF;
	}
	
	public function print_groupSelecter($logo_dir)
	{
		echo <<<EOF
<table border="0">
<tr>
<!--
	<td><input type="radio" name="GroupName" value="tiu" checked="checked"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/tiu.png" width="220">
	</td>
-->
	<td><input type="radio" name="GroupName" value="soka" checked="checked"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/soka.png" width="220">
	</td>
            
	<td><input type="radio" name="GroupName" value="aoyama"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/aoyama.gif" width="220">
	</td>
</tr>
<tr>
<!--
	<td><input type="radio" name="GroupName" value="chuo"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/chuo.gif" width="220">
	</td>
-->
	<td><input type="radio" name="GroupName" value="soka2"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/soka.png" width="220"></br>
                <center><b><span style="line-height:50%">通信教育部</span></b></center>
	</td>                        

	<td><input type="radio" name="GroupName" value="seijo"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/seijo.gif" width="220">
	</td>
</tr>
<tr>
	<td><input type="radio" name="GroupName" value="u-tokyo"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/u-tokyo.gif" width="220">
	</td>

<!--
	<td><input type="radio" name="GroupName" value="tottori"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/tottori.png" width="220">
	</td>
-->
	<td><input type="radio" name="GroupName" value="kansai-u"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/kansai-u.gif" width="220">
	</td>
</tr>
<tr>
	<td><input type="radio" name="GroupName" value="zeneiren"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/zeneiren.png" width="220">
	</td>

	<td><input type="radio" name="GroupName" value="tokai"></td>
	<td width="250" height="80" valign="center">
		<img src="$logo_dir/tokai.png" height="80">
	</td>
</tr>
<tr>
	<td><input type="radio" name="GroupName" value="guest"></td>
	<td>その他</td>
		
	<td></td><td></td>
</tr>
</table>
EOF;
	}
	
	public function print_footer()
	{
		echo <<<EOF
	<p class="back"><a href="#header">>TOP</a></p>
	<br />
	</div>
<!-- end: main contents -->

	<div id="menu"><!-- begin: menu -->
		<div class="menulist">

<h2>CONTENTS</h2>
</br>
<a href="http://www.waschbaerli.com/dictation/">トップ</a>
</br>
<p>英単語クイズ</p>
<a href="http://www.waschbaerli.com/dictation/eword/quiz/index.php?withWav=1&isTest=0">聴いて答える問題（練習）</a>
<a href="http://www.waschbaerli.com/dictation/eword/quiz/index.php?withWav=1&isTest=1">聴いて答える問題（テスト）</a>
<a href="http://www.waschbaerli.com/dictation/eword/quiz/index.php?withWav=0&isTest=0">見て答える問題（練習）</a>
<a href="http://www.waschbaerli.com/dictation/eword/quiz/index.php?withWav=0&isTest=1">見て答える問題（テスト）</a>

<p>作動記憶クイズ</p>
<a href="http://www.waschbaerli.com/dictation/wm/lst/index.php?isTest=0">聴いて答える問題(練習)</a>
<a href="http://www.waschbaerli.com/dictation/wm/lst/index.php?isTest=1">聴いて答える問題(テスト)</a>
<a href="http://www.waschbaerli.com/dictation/wm/rst/index.php?isTest=0">見て答える問題(練習)</a>
<a href="http://www.waschbaerli.com/dictation/wm/rst/index.php?isTest=1">見て答える問題(テスト)</a>
</br>
		</div><!-- end:menulist -->

先生は
<a href="http://www.waschbaerli.com/dictation/teacher/index.php">こちら</a>
からどうぞ。</br>
</br>
	</div><!-- end: menu -->
	
	</div><!-- end: contents -->

	<div id="footer"><!-- begin: footer -->
Copyright (C) 2011 Tokyo International University All Rights Reserved. design by <a href="http://tempnate.com/">tempnate</a>
	</div><!-- end: footer -->
</div>
<!-- end: main -->

	</body>
</html>
EOF;
	}

}
?>