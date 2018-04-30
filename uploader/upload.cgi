#!/usr/local/bin/perl
#use strict;                # 変数の宣言を厳密に行う場合は有効に
# ============================================================
# ファイルをアップロードするための CGI 
# upload.cgi v2.5, copyright(c)1999/04/01- Heppoko
# URL:  http://homepage1.nifty.com/~heppoko/
# MAIL: heppoko@kun.ne.jp
# ============================================================
# このプログラムは，ウェブページにファイルをアップロードするための
# プログラムです．ftp 等を使わずファイルを投稿出来ます．
# また，アップロードされたファイルを管理する機能もあります．
#
# 機能の紹介などについては，同梱の readme.html を参照して下さい．

# ============================================================
# ☆プログラム配置例
# ============================================================
# cgi-bin/（任意のディレクトリ）
#    |
#    |-- upload.cgi   (705) ... 【必須】CGI プログラム (同梱)
#    |-- hcgilib.pl   (604) ... 【必須】CGI ライブラリ
#    |-- jcode.pl     (604) ... 【必須】日本語変換ライブラリ
#    |-- back.gif     (604) ... 壁紙を設定する場合のみ必要
#    |-- tback.gif    (604) ... タイトル部分に壁紙を設定する場合のみ必要
#    |-- .hosts_deny  (604) ... UP不許可ホストを設定する場合のみ必要
#    |-- .hosts_allow (604) ... UP許可ホストを設定する場合のみ必要
#    |-- .uploadrc    (604) ... リソースを設定する場合のみ必要
#    |-- 
#    |-- gif/         (705) ... 【必須】gif データ用ディレクトリ
#    |    |-- ***.gif (604) ... 【必須】gif データ (同梱)
#    |-- data/        (707) ... 【必須】データ用ディレクトリ (作る)
#    |-- upload/      (707) ... 【必須】ファイルアップロード用ディレクトリ (作る)
#
# 注:データ用ディレクトリにはパスワードファイル等が保存されるため，
#    ブラウザからアクセス出来ない場所に設定することが推奨されます．
#    アクセス制限が不可能な場合は，ディレクトリ名を推測しにくい名前に
#    変更することをお勧めします．例: data -> d3r4hoge

# ☆アップロード許可・不許可ホスト設定ファイルは以下のように書きます
#
# [.hosts_deny / .hosts_allow]

# cow.momo.or.jp
# 210.100.10.*
# ^hoge.*
#
# 上記の例のように Perl の正規表現を使うことができます．
#
# ちなみに，許可ホストの方が不許可ホストよりも優先されます．
# 特定のホストのホストからのアクセスのみを許可する場合は
# 以下のように書けば良いことになります．
#
# [.hosts_deny]
# .*
#
# [.hosts_allow]
# cow.momo.or.jp
#
# この例では cow.momo.or.jp のみがアップロード許可ホストになります．

# ☆リソースファイルは以下のように書きます
#
# [.uploadrc]
# $uploaddir = 'upload2';
# $hcgilibname = '../cgi-lib/hcgilib.pl';
# ...
# $admin_page_title = 'へにょへにょはうすトップページ';
#
# (変更する定数だけを書いておけばOKです)
#
# リソースファイル中の定数の設定は，以下↓の定数定義の設定よりも
# 優先されます．リソースファイルを書いておけば，CGI プログラムの
# バージョンを上げる時の作業が楽になります．
#
# 但し，リソースファイルの中で日本語を使用する場合には，この
# プログラムと同じ文字コードを使用する必要があります．
# 標準では EUC コードとなっていますので，リソースファイル
# も EUC コードで記述して下さい．
#
# 定数が追加・変更される場合がありますので，たまには
# 以下↓の定数定義も見てやって下さいまし．

# ============================================================
# ☆使用法
# ============================================================
# 単純に呼び出すだけで OK です．
#
#  <a href="upload.cgi">あっぷろーど</a>

# ============================================================
# ☆プログラム本体
# ============================================================

# --------------------------------------------------
# 定数定義
# --------------------------------------------------

#
# 変更可能な定数
#

# 管理データ保存用ディレクトリ: 707 にしておく必要があります．
# ブラウザからアクセス出来ない場所に設定することを推奨します
$datadir = 'data';

# アップロード先ディレクトリ: 707 にしておく必要があります
$uploaddir = 'upload';

# アップロード先ディレクトリの URL 表現:
# @nifty 等のように，CGI 用のディレクトリとデータ用のディレクトリの
# URL が異なるサーバの場合に指定します．極悪サーバの IIS にも有効？
#$uploadurl = 'http://homepage1.nifty.com/~heppoko/upload';

# gif ファイル(同梱)を置いたディレクトリ:
$gifdir = 'gif';

# ライブラリの場所
$hcgilibname = 'hcgilib.pl';

# jcode ライブラリの場所
$jcodelibname = 'jcode.pl';

# 壁紙の場所
#$bg_filename = 'back.gif';

# タイトルの背景画像の場所
#$title_bg_filename = 'tback.gif';

# 背景色
$bgcolor = "#FFEFDF";

# タイトルの色
$title_color = "black";

# タイトルの背景色
$title_bgcolor = "#EFFFEF";

# 登録可能なファイルのサイズの上限(byte 単位):
# 1つのファイルで $max_filesize byte を越えるものは登録出来ません
$max_filesize = 10000000;

# ファイルサーバの容量(byte 単位):
# 全ファイルのサイズが $max_all_filesize byte を越えることは出来ません
$max_all_filesize = 100000000;

# 同時にアップロードするファイルの数
$file_num = 3;

# エラーログ記録用フラグ: エラーログを残す場合は true に設定します
# エラーログは $datadir 以下に .errlog というファイル名で保存されます
# ログはへっぽこ CGI のアクセスログ解析などで解析できます．
$record_errlog = 'true';

# 管理者パスワードを使用したときに，アップロードに関する全ての制限を
# 無くします．(どこからでも，どんなファイルでもアップロード可能になる)
# 但し，パスワードが盗まれた場合の危険が増大しますのでご注意下さい．
$admin_upload_all = 'false';

# ユーザの認証(ログイン手続き)を実行します．クッキー機能を使用すること
# によって，一度認証を終えたブラウザからは以後24時間認証が省略されます．
# (但し，本来は httpd のユーザ認証を使用するべきです．この機能のセキュ
# リティレベルはあまり高くありません)
$user_certification = 'false';

# ユーザ(および管理者)のパスワードでのみアップロードを可能にします．
# それ以外のパスワードをアップロード時に使用することは出来ません．
$user_only_upload = 'false';

# ファイルのダウンロード数を表示する場合に true に設定します．
# 但し，false の場合でもダウンロード数は記録されています．
$download_count = 'true';

# このページのタイトル
$title = '英単語クイズ関連データアップロードページ';

# 管理者(この CGI を設置した人)のウェブページ
$admin_page_url = 'http://www.waschbaerli.com/index.cgi';

# 管理者(この CGI を設置した人)のウェブページのタイトル
$admin_page_title = 'The nest of little raccoon';

# 注意：ファイル，ディレクトリ名はこの cgi プログラムからの相対位置の
#       指定になります．

# リソースファイルがあれば読み込む．リソースファイルには，上記の変数の
# 設定を記述することが出来ます．リソースファイルの変数の設定が優先されます．
if(-e ".uploadrc") { require ".uploadrc"; }

#
# 変更すべきでない定数
#

# このプログラムの場所
$prog_filename = 'upload.cgi';

# アップロードを許可するホスト(またはIPアドレス)を書いたファイル
$hosts_allow_filename = '.hosts_allow';

# アップロードを許可しないホスト(またはIPアドレス)を書いたファイル
$hosts_deny_filename = '.hosts_deny';

# 管理者のパスワードを記録するファイル($datadir以下に自動的に作成される)
$admin_passwd_filename = '.admin_passwd';

# ユーザのパスワードを記録するファイル($datadir以下に自動的に作成される)
$user_passwd_filename = '.user_passwd';

# ファイル情報を記録するファイル($userdatadir以下に自動的に作成される)
$fileinfo_filename = '.fileinfo';

# ディスク情報を記録するファイル($datadir以下に自動的に作成される)
$diskinfo_filename = '.diskinfo';

# ロックディレクトリ名($datadir以下に自動的に作成される)
$lock_dirname = '.upload_lock';

# ロックエラーのカウント用ファイル($datadir以下に自動的に作成される)
$lock_error_count_filename = '.lock_err';

# エラーログファイル($datadir以下に自動的に作成される)
$errlog_filename = '.errlog';

# ファイル一時格納用ディレクトリ($datadir以下に自動的に作成される)
$upload_tmp_dir = '.upload_tmp_dir';

# カレントディレクトリマーカ画像場所
$cd_filename = "$gifdir/cd.gif";

# ファイルロック解除の最大待ち時間(秒)
$max_wait_time = 5;

# ロックディレクトリが残ってしまったと判断するまでのlockエラー回数
$max_lock_error_count = 10;

# フォームデータ受信バッファサイズ(byte)
$form_rec_buf_size = 4096;

# ファイル名に使用出来る文字を制限する
$restrict_filename = 'true';

# 編集可能ファイル．通常は自動判別によって編集可能(テキスト)ファイルが決定
# されるが，文字コードの関係で自動判別を誤る場合がある(多い)．ここで明示的に
# 指定することによってそれを回避する．
@editable_filename = ('\.txt');

# アップロード禁止ファイル．セキュリティ上アップされるとまずいファイル等．
# 追加するのは構いませんが，【デフォルトのものを消すべきではありません】．
# html ファイルをアップロード出来るようにするのは大変危険です．
@ng_filename = ('^\.htaccess$', '^\.htpasswd$', '^\.upload_lock$', '\..?htm.?$', '\.cgi$');

# アップロード許可ファイル．アップロード禁止ファイルよりも優先されます．
@ok_filename = ();

# 例: *.txt のみをアップ可能にしたい場合
# ng_filename = ('.*');
# ok_filename = ('\.txt$');

# 拡張子等に応じて画像を表示
$image{'txt'} = "$gifdir/text.gif";
$image{'doc'} = "$gifdir/text.gif";
$image{'log'} = "$gifdir/text.gif";

$image{'gif'} = "$gifdir/image.gif";
$image{'jpg'} = "$gifdir/image.gif";
$image{'jpeg'} = "$gifdir/image.gif";
$image{'png'} = "$gifdir/image.gif";
$image{'bmp'} = "$gifdir/image.gif";

$image{'mid'} = "$gifdir/sound.gif";
$image{'wav'} = "$gifdir/sound.gif";

$image{'zip'} = "$gifdir/arc.gif";
$image{'lzh'} = "$gifdir/arc.gif";
$image{'tgz'} = "$gifdir/arc.gif";
$image{'gz'} = "$gifdir/arc.gif";

$image{'exe'} = "$gifdir/exe.gif";

# フォルダの扱いはやや特殊
$image{'folder'} = "$gifdir/folder.gif";

# 標準画像
$image{'std_image'} = "$gifdir/file.gif";

# ソフト名
$softname = 'upload v2.5';

# サポートページの場所
$support_page_url = 'http://homepage1.nifty.com/~heppoko/';

# --------------------------------------------------
# サブルーチンのみで使用されるグローバル変数の定義
# --------------------------------------------------
# オブジェクト
my ($myEzHTML, $myFormData, $myPasswd, $myPasswd2, $myLock, $myFileInfo);
# 参照
my ($values);
# スカラー
my ($host_name, $ipaddr);
my ($sec,$min,$hour,$mday,$mon,$year);
my ($host_allow, $userupdir, $userupurl, $userdatadir, $subdir, $sortmethod, $downfile);
my ($all_filesize);
my ($d_passwd, $d_comment_up, $d_email, $d_username, $d_override);
my ($d_cookie, $f_cookie, $d_comment_md);
my ($form_error);

# --------------------------------------------------
# メイン処理(特に理由がない限り変更しないで下さい)
# --------------------------------------------------

# ライブラリを読み込む
require $hcgilibname;
require $jcodelibname;

# リモートホスト名とアドレスの取得
($host_name,$ipaddr) = accesslog::getRemoteHostInfo();
# ホスト名が分からない場合は IP アドレスを使用
$host_name = $host_name ? $host_name : $ipaddr;

# 時刻の記録
($sec,$min,$hour,$mday,$mon,$year) = accesslog::getTime();

# ezhtml オブジェクトの実装
$myEzHTML = EzHTML->new($prog_filename, $title, $title_bgcolor, 
                        $bg_filename, $bgcolor,
                        $admin_page_url, $admin_page_title, 
                        $support_page_url, $softname,
                        $title_color, $title_bg_filename);
if($record_errlog eq 'true') {
  $myEzHTML->setErrlogFilename("$datadir/$errlog_filename");
}

# 一時ディレクトリを空にする
if(!-e "$datadir/$upload_tmp_dir") {
  mkdir("$datadir/$upload_tmp_dir", 0777) || error("<font color=\"red\">致命的エラー</font>: 一時ディレクトリ $datadir/$upload_tmp_dir の作成に失敗しました");
}

# FormData2 オブジェクトの実装
$myFormData = FormData2->new();
# 受信データサイズの上限を設定
$myFormData->setMaxDataSize($max_filesize);
# フォームデータ受信バッファサイズを設定
$myFormData->setBufferSize($form_rec_buf_size);
# 一時ディレクトリを設定
$myFormData->setTmpDir("$datadir/$upload_tmp_dir");
# POST と GET の両方を受け付ける
$myFormData->setGET('true');
$myFormData->setPOST('true');
# フォームから送られるデータの受信
$form_error = $myFormData->receive();
# ファイル名とデータ値のハッシュの参照を取得
$values = $myFormData->getValues();

# 管理者用 Passwd オブジェクトの実装
$myPasswd = Passwd->new("$datadir/$admin_passwd_filename",
			$prog_filename, '管理者', \$myEzHTML, \$myFormData);
# 管理者パスワードを読み込む，必要ならば設定する．
$myPasswd->loadPasswd();

# ユーザ用 Passwd オブジェクトの実装
$myPasswd2 = Passwd->new("$datadir/$user_passwd_filename",
                         $prog_filename, 'ユーザ', \$myEzHTML, \$myFormData);
# ユーザパスワードを読み込む，必要ならば設定する．
$myPasswd2->loadPasswd();
# ユーザパスワードの認証を行う．
if($user_certification eq 'true') {
  $myPasswd2->requestPasswd();
}

# ロックオブジェクトの実装
$myLock = Lock->new("$datadir/$lock_dirname", $max_wait_time);

# ファイル情報オブジェクトの実装
$myFileInfo = FileInfo->new();

# ---------------------------

# リモートホストがアップロードを許可されているかどうかの取得
$host_allow = check_remotehost();

# ユーザディレクトリ，サブディレクトリの取得
($userupdir, $userupurl, $userdatadir, $subdir, $downfile) = get_dirs();

# ソート方法の取得
$sortmethod = get_sortmethod();

# ファイル情報をグローバル変数に読み込む
load_fileinfo($myFileInfo, $userdatadir);

# ディスク情報を読み込む
$all_filesize = load_diskinfo();

# クッキー情報読み込み
($d_username, $d_passwd, $d_comment_up, $d_comment_md, $d_cookie, $d_override, $d_email) 
    = load_cookie();
# クッキーを使用するかどうか(チェックボックスの設定)
$f_cookie = get_value('cookie');

# メッセージ表示後に戻るページを指定
$myEzHTML->setRetPageURL("$prog_filename?page=Main&dir=$subdir&sort=$sortmethod");

#
# どこのフォームからのデータなのかを判定し，適切な処理を行う
#

# 送信されたファイルのサイズが大き過ぎるか，不正なデータが送られている場合は
# エラーメッセージを表示
if($form_error) {
  error("ファイルのサイズが大きすぎるか，または不正なデータが送られています．<P>エラーメッセージ : <BR> $form_error</P> 問題が解決しない場合は，管理者に問い合わせてみて下さい");
}

# ページの指定がない場合または Main の要求があるならメインページへ
if(!get_value('page') || get_value('page') eq 'Main') {
  main_page();
}
# Help の要求だったらヘルプページへ
elsif(get_value('page') eq 'Help') {
  help_page();
}
# Tree の要求だったら部分ツリー表示ページへ
elsif(get_value('page') eq 'Tree') {
  tree_page('tree');
}
# AllTree の要求だったらツリー表示ページへ
elsif(get_value('page') eq 'AllTree') {
  tree_page('all_tree');
}
# 情報の変更 の要求だったら情報変更ページへ
elsif(get_value('page') eq '情報の変更') {
  edit_info_page();
}
# 情報の更新 の要求だったら情報更新ページへ
elsif(get_value('page') eq '情報の更新') {
  fix_info_page();
}
# 編集 の要求だったら編集ページへ
elsif(get_value('page') eq '編集') {
  edit_file_page();
}
# ファイルの更新 の要求だったらファイル更新ページへ
elsif(get_value('page') eq 'ファイルの更新') {
  fix_file_page();
}
# 移動 の要求だったら移動ページへ
elsif(get_value('page') eq '移動') {
  move_page();
}
# 移動する! の要求だったら移動ページへ
elsif(get_value('page') eq 'ファイルの移動') {
  move_file_page();
}
# 削除 の要求だったら削除ページへ
elsif(get_value('page') eq '削除') {
  delete_page();
}
# Upload の要求だったらアップロードページへ
elsif(get_value('page') eq 'アップロード') {
  upload_page();
}
# ディレクトリの作成 の要求だったらディレクトリ作成ページへ
elsif(get_value('page') eq 'ディレクトリの作成') {
  mkdir_page();
}
# Download の要求だったらダウンロード処理を行う
elsif(get_value('page') eq 'Download') {
  file_download($userupurl,$downfile);
}
# わけのわからない状態だったらエラーメッセージを表示
else {
  my $ks = join(',', keys %$values);
  my $vs = join(',', values %$values);
  error("POST/GET で送信されたデータが不正です<br>keys=$ks<br>values=$vs");
}

# 終了
exit(0);

# --------------------------------------------------
# サブルーチン
# --------------------------------------------------

#
# ページ表示関連
#

# メインページ
sub main_page {
  my ($ratio, $pdir, $sdir, $fimg, @indexes);
  # ディスク使用率の計算
  $ratio = int($all_filesize*100/$max_all_filesize);
  # 親ディレクトリの取得
  if($subdir =~ /(.*)\//) { $pdir = $1; }
  # フォルダ画像の取得
  $fimg = get_image('folder');
  # サブディレクトリ
  $sdir = $subdir ? $subdir : '/';

  $myEzHTML->printHeader();

  my $fall_filesize = get_fnumber($all_filesize);
  my $fmax_all_filesize = get_fnumber($max_all_filesize); 
print <<EOF;
    <center>
      <h3>■　英単語クイズ関連データアップロードページ　■</h3>
      <P>
        [<a href="#download">ファイルの管理</a>][<a href="#upload">アップロード</a>][<a href="#mkdir">ディレクトリの作成</a>][<a href="$prog_filename?page=Help&dir=$subdir&sort=$sortmethod#help">ヘルプ</a>]
      </P>
    </center>

    <HR>

    <center>
      <a name="download"><h3>□　ファイルの管理　□</h3></a>
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
        <P>
          <table border="0">
            <tr>
              <td align="right">ディスク使用量:</td>
              <td>$fall_filesize byte / $fmax_all_filesize byte ($ratio%)</td>
            </tr>
          </table>
        </P>
        <P><font size=+1>[<a href="$prog_filename?page=Tree&dir=$subdir&sort=$sortmethod#tree">部分ツリー表示</a>][<a href="$prog_filename?page=AllTree&dir=$subdir&sort=$sortmethod#tree">ツリー表示</a>]</font></P>
        <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$pdir&sort=$sortmethod#download">一つ上のディレクトリへ</a>]</font></P>
      <table border="0">
        <tr>
          <td align="right">現在のディレクトリ名:</td>
          <td><img src="$fimg" alt="+"><font size=+1> $sdir </font></td>
        </tr>
      </table>
        <table border="0" WIDTH="80%" cellpadding="2">
          <tr align="center" BGCOLOR="#FFD8D8">
            <th nowrap> <a href="$prog_filename?page=Main&dir=$subdir&sort=filename#download">ファイル名</a> </th>
            <th nowrap> 容量(byte)</th>
            <th nowrap> <a href="$prog_filename?page=Main&dir=$subdir&sort=date#download">登録日</a> </th>
            <th nowrap> コメント </th>
            <th nowrap> <a href="$prog_filename?page=Main&dir=$subdir&sort=username#download">ユーザ名</a> </th>
EOF
    if($download_count eq 'true') {
      print "            <th> DL </th>\n";
    }
print <<EOF;
            <th> Check </th>
          </tr>
          <tr BGCOLOR="#D8FFD8">
            <td nowrap> <img src="$fimg" alt="+"><a href="$prog_filename?page=Main&dir=$pdir&sort=$sortmethod#download"> ．． </a> </td>
            <td align="center"> (directory) </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
EOF
    if($download_count eq 'true') {
      print "            <td>&nbsp;</td>\n";
    }
print <<EOF;
            <td>&nbsp;</td>
          </tr>
EOF

# ディレクトリリスト(テーブル)の表示
   foreach my $index ($myFileInfo->getDirIndexes($sortmethod)) {
     my ($filename, $filesize, $date, $comment,
         $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);
print <<EOF;
          <tr BGCOLOR="#D8FFD8">
            <td nowrap><img src="$fimg" alt="+"><a href="$prog_filename?page=Main&dir=$subdir/$filename&sort=$sortmethod#download"> $filename </a> </td>
            <td align="center"> (directory) </td>
            <td align="center"> $date </td>
            <td> $comment </td>
            <td align="center" nowrap>
EOF
  if($email) {
    print "              <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "              $username\n";
  }    
print <<EOF;
            </td>
EOF
  if($download_count eq 'true') {
    print "          <td>&nbsp;</td>\n";
  }
     print "            <td align=\"center\"> <INPUT TYPE=\"checkbox\" NAME=\"$index\"> </td>\n";
     print "          </tr>\n";
  }

  # ファイルリスト(テーブル)の表示
  foreach my $index ($myFileInfo->getFileIndexes($sortmethod)) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);
    my $img = get_image($filename);
    my $ffilesize = get_fnumber($filesize);
print <<EOF;
          <tr BGCOLOR="#FFFFD8">
            <td nowrap><img src="$img" alt="-"><a href="$prog_filename?page=Download&dir=$subdir&sort=$sortmethod&filename=$filename"> $filename </a></td>
            <td align="right"> $ffilesize </td>
            <td align="center"> $date </td>
            <td> $comment </td>
            <td align="center" nowrap>
EOF
  if($email) {
    print "              <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "              $username\n";
  }    
print <<EOF;
            </td>
EOF
    if($download_count eq 'true') {
      print "            <td align=\"right\">$downcount </td>\n";
    }
    print "            <td align=\"center\"> <INPUT TYPE=\"checkbox\" NAME=\"$index\"> </td>\n";
    print "          </tr>\n";
  }

my $cafs = $myFileInfo->getAllFileSize();
my $fcafs = get_fnumber($cafs);
print <<EOF;
          <tr BGCOLOR="#D8D8D8">
            <td align="center"> 合計 </td>
            <td align="right"> $fcafs </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
EOF
    if($download_count eq 'true') {
      print "            <td>&nbsp;</td>\n";
    }
print <<EOF;
            <td>&nbsp;</td>
          </tr>
        </table>
		<br>
        パスワード: <INPUT TYPE="password" NAME="passwd" VALUE="$d_passwd" SIZE="10" MAXLENGTH="10">
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="情報の変更"> 
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="編集"> 
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="移動"> 
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="削除">
		<br>
      </FORM>
    </center>

<!--
    <ul>
      <dt>[<a href="#top">MENU へ戻る</a>]
    </ul>
-->

	<br>
    <hr>

    <center>
      <a name="upload"><h3>□　アップロード　□</h3></a>
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
        <table border="0">
EOF
for(my $i=0; $i<$file_num; $i++) {
  my $num = $i + 1;
print <<EOF;
          <tr>
            <td align="right">送信ファイル名$num:</td>
            <td><INPUT TYPE="FILE" NAME="save$i" SIZE="48"></td>
          </tr>
EOF
}
print <<EOF;
          <tr>
            <td align="right">コメント:</td>
            <td><INPUT TYPE="text" NAME="comment" VALUE="$d_comment_up" SIZE="50"></td>
          </tr>
          <tr>
            <td align="right">E-MAIL:</td>
            <td><INPUT TYPE="text" NAME="email" VALUE="$d_email" SIZE="50"></td>
          </tr>
          <tr>
            <td align="right">ユーザ名: </td> <td> <INPUT TYPE="text" NAME="username" VALUE="$d_username" SIZE="10" MAXLENGTH="10"> パスワード: <INPUT TYPE="password" NAME="passwd" VALUE="$d_passwd" SIZE="10" MAXLENGTH="10"> </td>
            <td> </td>
          </tr>
          <tr>
            <td> </td>
            <td>
EOF

my ($attr1, $attr2);
if($d_override eq 'on') { $attr1 = "checked"; }
if($d_cookie eq 'on') { $attr2 = "checked"; }
print <<EOF;
              ファイルの上書き: <INPUT TYPE="checkbox" NAME="override" $attr1>
              設定を保存: <INPUT TYPE="checkbox" NAME="cookie" $attr2>
            </td>
          </tr>
        </table>
		<br>
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="アップロード">
		<br>
      </FORM>
    </center>
 
<!--
    <ul>
    <dt>[<a href="#top">MENU へ戻る</a>]
    </ul>
-->

	<br>
    <HR>
  
    <center>
      <a name="mkdir"><h3>□　ディレクトリの作成　□</h3></a>
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
        <table border="0">
          <tr>
            <td align="right">ディレクトリ名:</td>
            <td><INPUT TYPE="text" NAME="filename" SIZE="20"></td>
          </tr>
          <tr>
            <td align="right">コメント:</td>
            <td><INPUT TYPE="text" NAME="comment" VALUE="$d_comment_md" SIZE="50"></td>
          </tr>
          <tr>
            <td align="right">E-MAIL:</td>
            <td><INPUT TYPE="text" NAME="email" VALUE="$d_email" SIZE="50"></td>
          </tr>
          <tr>
            <td align="right">ユーザ名: </td> <td> <INPUT TYPE="text" NAME="username" VALUE="$d_username" SIZE="10" MAXLENGTH="10"> パスワード: <INPUT TYPE="password" NAME="passwd" VALUE="$d_passwd" SIZE="10" MAXLENGTH="10">
EOF
if($d_cookie eq 'on') { $attr1 = "checked"; }
print <<EOF;
              設定を保存: <INPUT TYPE="checkbox" NAME="cookie" $attr1>
            </td>
          </tr>
        </table>
		<br>
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="ディレクトリの作成">
		<br>
      </FORM>
    </center>
 
<!--
    <ul>
    <dt>[<a href="#top">MENU へ戻る</a>]
    </ul>
-->

	<br>
    <HR>

EOF
  $myEzHTML->printFooter();
  exit(0);
}

# ヘルプページ
sub help_page {
  $myEzHTML->printHeader();
print <<EOF;
    <center>
      <a name="help"><h3>HELP</h3></a>
      <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$subdir&sort=$sortmethod#download">現在のディレクトリに戻る</a>]</font></P>
      <P>★ファイルの管理★</P>
      <table border="0" WIDTH="95%" cellpadding="2">
        <tr BGCOLOR="FFFFD8">
          <td>ファイルのダウンロード</td>
          <td>ファイル名をクリックして下さい．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>ディレクトリの移動</td>
          <td>ディレクトリ名をクリックして下さい．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>ツリー表示</td>
          <td>ディレクトリ・ファイルのツリー構造を表示します．「部分ツリー表示」では，現在のディレクトリ以外のファイルは表示しません．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>情報の変更</td>
          <td>ファイル名やコメント等を変更します．対象にチェックを入れ，<font color="green">アップロード時に設定したパスワードと同じパスワード</font>を入力したのちに[情報の変更]ボタンを押して下さい．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>ファイルの編集</td>
          <td>テキストファイルの編集を行います．対象にチェックを入れ，<font color="green">アップロード時に設定したパスワードと同じパスワード</font>を入力したのちに[編集]ボタンを押して下さい．テキストファイルの判別に失敗する場合が多々ありますので，その場合は管理者にお問い合わせ下さい．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>ファイルの移動</td>
          <td>対象にチェックを入れ，<font color="green">アップロード時に設定したパスワードと同じパスワード</font>を入力したのちに[移動]ボタンを押して下さい．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>ファイルの削除</td>
          <td>対象にチェックを入れ，<font color="green">アップロード時に設定したパスワードと同じパスワード</font>を入力したのちに[削除]ボタンを押して下さい．</td>
        </tr>
      </table>
      <P>★アップロード★</P>
      <table border="0" WIDTH="95%" cellpadding="2">
        <tr BGCOLOR="FFFFD8">
          <td>アップロード</td>
          <td>送信ファイル名(<font color="red">「半角英数字」及び「-(ハイフン)」「_(アンダーバー)」「.(ドット)」のみ使用可</font>)，コメント(省略可)，E-MAILアドレス(省略可)，ユーザ名(省略不可)，パスワード(省略可)を指定した後に[アップロード]ボタンを押して下さい．</td>
        </tr>
      </table>
      <P>★ディレクトリの作成★</P>
      <table border="0" WIDTH="95%" cellpadding="2">
        <tr BGCOLOR="FFFFD8">
          <td>ディレクトリの作成</td>
          <td>ディレクトリ名(<font color="red">「半角英数字」及び「-(ハイフン)」「_(アンダーバー)」「.(ドット)」のみ使用可</font>)，コメント(省略可)，E-MAILアドレス(省略可)，ユーザ名(省略不可)，パスワード(省略可)を入力した後に[ディレクトリの作成]ボタンを押して下さい．ただし，複数階層のサブディレクトリを一度の操作で作成することは出来ません．</td>
        </tr>
      </table>
      <P>★その他★</P>
      <table border="0" WIDTH="95%" cellpadding="2">
        <tr BGCOLOR="FFFFD8">
          <td>TIPS1</td>
          <td>「ファイル名」「登録日」「ユーザ名」の表示をクリックすれば，各項目別にソートすることが出来ます．</td>
        </tr>
        <tr BGCOLOR="FFFFD8">
          <td>TIPS2</td>
          <td>「設定を保存」をチェックしておけば，ユーザ名やパスワード等をブラウザに記憶させることが出来ます．保存されたユーザ名やパスワード等は<font color="green">全ての機能で共通に使用</font>されます．</td>
        </tr>
      </table>
    </center>
EOF
  $myEzHTML->printFooter();
  exit(0);
}

# ファイルの削除ページ
sub delete_page {
  my ($message, @onn);

  # チェックされたファイルの取得
  foreach my $key (get_keys()) {
    my $value = get_value($key);
    if($value eq 'on') {
      $key = sprintf("%05d", $key);
      $onn[$#onn+1] = $key;
    }
  }

  if($#onn < 0) {
    error("削除対象ファイルがチェックされていません", 3);
  }

  # ファイルの削除
  foreach my $index (reverse sort @onn) {
    my $tmp = delete_sub($index);
    if($tmp) {
      $message .= $tmp ."<BR>";
    }
  }
  # moved to 2000.08.29-a
  # ファイル情報の書きこみ
  save_fileinfo($myFileInfo, $userdatadir);
  # ディスク情報の書き込み
  save_diskinfo($all_filesize);
  message($message, 3);
}

# ファイルの削除サブルーチン
sub delete_sub {
  my $index = shift;
  my ($passwd, $crypt_passwd);

  # ファイル名等の取得
  my ($filename, $filesize, $date, $comment,
      $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);

  # パスワードの取得
  $passwd = get_value('passwd');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);
   
  # パスワードが合っていたら(または管理者パスワードなら)，
  # 指定のファイルを削除
  if($cpasswd eq $crypt_passwd ||
     $myPasswd->isCorrect($passwd) eq 'true') {
    if($filesize > 0) {
      # ファイルの場合は単に削除
      if(!file::delete("$userupdir/$filename")) {
        error("<font color=\"red\">致命的エラー</font>: $filename の削除に失敗しました．CGI の設定または登録されたデータが異常です");
      }
    } else {
      # ディレクトリの場合はサブディレクトリごと削除
      $all_filesize -= dirsize("$userdatadir/$filename");
      if(!file::delete("$userupdir/$filename") ||
         !file::delete("$userdatadir/$filename")) {
        error("<font color=\"red\">致命的エラー</font>: ディレクトリ $filename の削除に失敗しました");
      }
    }
    
    # ディスク使用量更新
    $all_filesize -= $filesize;
    # ファイル情報の更新: データの削除
    $myFileInfo->delete($index);
    # moved from 2000.08.29-a
    return "$filename を削除しました";
  } else {
    return "$filename を削除する権限がありません";
  }
}

# 情報更新ページ
sub edit_info_page {
  my ($passwd, $onn_all, @onn);
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  # サブディレクトリ
  my $sdir = $subdir ? $subdir : '/';

  # パスワードの取得
  $passwd = get_value('passwd');
  # チェックされたファイルの取得
  foreach my $key (get_keys()) {
    my $value = get_value($key);
    if($value eq 'on') {
      $key = sprintf("%05d", $key);
      $onn[$#onn+1] = $key;
    }
  }

  if($#onn < 0) {
    error("編集対象ファイルがチェックされていません", 3);
  }

  $onn_all = join(':', @onn);
  $myEzHTML->printHeader();
print <<EOF;
    <center>
      <a name="edit"><h3>情報の変更</h3></a>
      <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$subdir&sort=$sortmethod#download">現在のディレクトリに戻る</a>]</font></P>
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="onn" VALUE="$onn_all">
        <INPUT TYPE="hidden" NAME="passwd" VALUE="$passwd">
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
      <table border="0">
        <tr>
          <td align="right">現在のディレクトリ名:</td>
          <td><img src="$fimg" alt="+"><font size=+1> $sdir </font></td>
        </tr>
      </table>
        <table border="0" WIDTH="80%" cellpadding="2">
          <tr align="center" BGCOLOR="#FFD8D8">
            <th nowrap> ファイル名 </th>
            <th nowrap> 容量(byte) </th>
            <th nowrap> 登録日 </th>
            <th nowrap> コメント </th>
            <th nowrap> ユーザ名 </th>
          </tr>
EOF
  foreach my $index (@onn) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);
    my $img = $filesize == 0 ? get_image('folder') : get_image($filename);
    my $color = $filesize == 0 ? "#D8FFD8" : "#FFFFD8";
    my $ffilesize = get_fnumber($filesize);
print <<EOF;
          <tr BGCOLOR="$color">
            <td><img src="$img" alt="-"><INPUT TYPE="text" NAME="filename:$index" VALUE="$filename" SIZE="16"></td>
EOF
  if($filesize) {
    print "            <td align=\"right\"> $ffilesize </td>\n";
  } else {
    print "            <td align=\"center\"> (directory) </td>\n";
  }
print <<EOF;
            <td align="center"> $date </td>
            <td><INPUT TYPE="text" NAME="comment:$index" VALUE="$comment" SIZE="50"></td>
            <td align="center" nowrap>
EOF
  if($email) {
    print "              <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "              $username\n";
  }    
print <<EOF;
            </td>
          </tr>
EOF
    }
print <<EOF;
          <tr BGCOLOR="#D8D8D8">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="情報の更新">
      </FORM>
    </center>
EOF
  $myEzHTML->printFooter();
}

# 情報更新ページ
sub fix_info_page {
  my ($message, @onn);

  # チェックされたファイルの取得
  @onn = split(/:/,get_value('onn'));

  # ファイルの更新
  foreach my $index (@onn) {
    my $tmp = fix_info_sub($index);
    if($tmp) {
      $message .= $tmp ."<BR>";
    }
  }
  # メッセージなし=ファイルが一つも更新されていない
  if(!$message) {
    error("ファイル情報は更新されませんでした",3);
  }
  # moved to 2000.08.29-b
  # ファイル情報の書きこみ
  save_fileinfo($myFileInfo, $userdatadir);
  message($message, 3);
}

# ファイルの情報変更サブルーチン
sub fix_info_sub {
  my $index = shift;
  my ($passwd, $crypt_passwd);
  my ($new_filename, $new_comment);
  my ($filename, $filesize, $date, $comment,
      $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);

  # ファイル名等の取得
  $new_filename = get_value("filename:$index");
  $new_comment = get_value("comment:$index");
  # パスワードの取得
  $passwd = get_value('passwd');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);

  # ディレクトリかどうかのチェック
  my $dflg = ($filesize == 0) ? 'true' : 'false';
  
  # ファイル名のセキュリティチェック
  $new_filename = sandbox_filename($new_filename, $passwd, $dflg);
  # コメントのセキュリティチェック
  $new_comment = sandbox_comment($new_comment);

  # ファイル名が指定されていない場合は中断
  if($new_filename =~ /^\s*$/) {
    return "$filename の新しいファイル名が指定されていません";
  }

  # 情報が全く変わっていなかったら
  if($filename eq $new_filename && $comment eq $new_comment) {
    return();
  }

  # 変更後のファイル名と同一名のファイルまたはディレクトリが存在する場合
  if(-e "$userupdir/$new_filename" && $filename ne $new_filename) {
    return "$new_filename は既に登録されています．情報は変更されませんでした";
  }

  # パスワードが合っていたら(または管理者パスワードなら)，
  # 指定のファイルを編集
  if($cpasswd eq $crypt_passwd ||
     $myPasswd->isCorrect($passwd) eq 'true') {
      my $rnOK = 1;
      if($filename ne $new_filename) {
        $rnOK = rename("$userupdir/$filename", "$userupdir/$new_filename");
        # ディレクトリの場合は情報ファイルディレクトリ名も変更する
        if($filesize == 0) {
          $rnOK &= rename("$userdatadir/$filename", "$userdatadir/$new_filename");
        }
      }
      if($rnOK) {
        # ファイル情報の更新
        $myFileInfo->replace($index, $new_filename, $filesize, $date, $new_comment,
                             $username, $cpasswd, $addr, $downcount, $email);
        # moved from 2000.08.29-b
        return "$filename の情報を変更しました";
      } else {
        error("<font color=\"red\">致命的エラー</font>: $filename の情報の変更に失敗しました");
      }
  } else {
    return "$filename の情報を変更する権限がありません";
  }
}

# ファイル更新ページ 2000.09.10
sub edit_file_page {
  my ($passwd, $onn_all, @onn);
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  # サブディレクトリ
  my $sdir = $subdir ? $subdir : '/';

  # パスワードの取得
  $passwd = get_value('passwd');
  # チェックされたファイルの取得
  foreach my $key (get_keys()) {
    my $value = get_value($key);
    if($value eq 'on') {
      $key = sprintf("%05d", $key);
      $onn[$#onn+1] = $key;
    }
  }

  if($#onn < 0) {
    error("編集対象ファイルがチェックされていません", 3);
  }

  $onn_all = join(':', @onn);
  $myEzHTML->printHeader();
print <<EOF;
    <center>
      <a name="edit"><h3>ファイルの編集</h3></a>
      <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$subdir&sort=$sortmethod#download">現在のディレクトリに戻る</a>]</font></P>
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="onn" VALUE="$onn_all">
        <INPUT TYPE="hidden" NAME="passwd" VALUE="$passwd">
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
      <table border="0">
        <tr>
          <td align="right">現在のディレクトリ名:</td>
          <td><img src="$fimg" alt="+"><font size=+1> $sdir </font></td>
        </tr>
      </table>
        <table border="0" WIDTH="80%" cellpadding="2">
          <tr align="center" BGCOLOR="#FFD8D8">
            <th nowrap> ファイル名 </th>
            <th nowrap> 容量(byte) </th>
            <th nowrap> 登録日 </th>
            <th nowrap> コメント </th>
            <th nowrap> ユーザ名 </th>
          </tr>
EOF
  foreach my $index (@onn) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);
    my $img = $filesize == 0 ? get_image('folder') : get_image($filename);
    my $color = $filesize == 0 ? "#D8FFD8" : "#FFFFD8";
    my $ffilesize = get_fnumber($filesize);
print <<EOF;
          <tr BGCOLOR="$color">
            <td><img src="$img" alt="-"> $filename </td>
EOF
  if($filesize) {
    print "            <td align=\"right\"> $ffilesize </td>\n";
  } else {
    print "            <td align=\"center\"> (directory) </td>\n";
  }
print <<EOF;
            <td align="center"> $date </td>
            <td> $comment </td>
            <td align="center" nowrap>
EOF
  if($email) {
    print "              <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "              $username\n";
  }    
print <<EOF;
            </td>
          </tr>
EOF
# 編集可能ファイルの抽出
my $editable = 'false';
    foreach (@editable_filename) {
      if($filename =~ /$_/i) { $editable = 'true'; }
    }
# テキストファイルのみ編集可能
if((!-d "$userupdir/$filename" && $editable eq 'true') || -T "$userupdir/$filename") {
  open(BREAD, "< $userupdir/$filename") || error_unlock("<font color=\"red\">致命的エラー</font>: 編集対象ファイル $userupdir/$filename の読み込みに失敗しました");
  binmode BREAD;
  my $body = join('', <BREAD>);
  close(BREAD);
  # ファイルの漢字コードは変更しない．そのため，漢字コード情報を記憶しておく．
  my $kcode = jcode::getcode(\$body);
  my ($sk1, $sk2, $sk3);
  if($kcode eq 'euc') {
    $sk1 = " SELECTED";
  } elsif($kcode eq 'sjis') {
    $sk2 = " SELECTED";
  } else {
    $sk3 = " SELECTED";
  }
  $body = jcode::euc($body);
  # 改行コードも変更しない．そのため，改行コードの情報を記憶しておく．
  my ($rcode, $sr1, $sr2, $sr3);
  if($body =~ /\r\n/) {
    $rcode = "crlf"; $sr1 = " SELECTED";
  } elsif($body =~ /\n/) {
    $rcode = "lf"; $sr2 = " SELECTED";
  } elsif($body =~ /\r/) {
    $rcode = "cr"; $sr3 = " SELECTED";
  } else {
    $rcode = "crlf"; $sr1 = " SELECTED";
  }
  $body =~ s/(\r?\n|\r)/\n/g;
print <<EOF;
          <tr>
            <td COLSPAN="5" BGCOLOR="#F0F0F0">
              <center>
                <textarea NAME="body:$index" rows="10" cols="100%" wrap=off>$body</textarea><br>
                漢字コード:
                <SELECT NAME="kcode:$index">
                  <OPTION VALUE="euc"$sk1>euc</OPTION>
                  <OPTION VALUE="sjis"$sk2>sjis</OPTION>
                  <OPTION VALUE="jis"$sk3>jis</OPTION>
                </SELECT>
                &nbsp;
                改行コード:
                <SELECT NAME="rcode:$index">
                  <OPTION VALUE="crlf"$sr1>crlf</OPTION>
                  <OPTION VALUE="lf"$sr2>lf</OPTION>
                  <OPTION VALUE="cr"$sr3>cr</OPTION>
                </SELECT>
             </center>
            </td>
          </tr>
EOF
} else {
print <<EOF;
          <tr>
            <td COLSPAN="5" BGCOLOR="#F0F0F0"> テキストファイル以外の編集はできません </td>
          </tr>
EOF
}
    }
print <<EOF;
          <tr BGCOLOR="#D8D8D8">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <INPUT TYPE="SUBMIT" NAME="page" VALUE="ファイルの更新">
      </FORM>
    </center>
EOF
  $myEzHTML->printFooter();
}

# ファイル更新ページ
sub fix_file_page {
  my ($message, @onn);

  # チェックされたファイルの取得
  @onn = split(/:/,get_value('onn'));

  # ファイルの更新
  foreach my $index (@onn) {
    my $tmp = fix_file_sub($index);
    if($tmp) {
      $message .= $tmp ."<BR>";
    }
  }	
  # メッセージなし=ファイルが一つも更新されていない
  if(!$message) {
    error("ファイルは更新されませんでした",3);
  }
  # moved to 2000.08.29-b
  # ファイル情報の書きこみ
  save_fileinfo($myFileInfo, $userdatadir);
  # ディスク情報の書き込み
  save_diskinfo($all_filesize);
  message($message, 3);
}

# ファイルの変更サブルーチン
sub fix_file_sub {
  my $index = shift;
  my ($passwd, $crypt_passwd);
  my ($body, $kcode, $rcode);
  my ($filename, $filesize, $date, $comment,
      $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);

  # ファイルの中身の取得
  $body = get_value("body:$index");
  # (元の)漢字コードの取得
  $kcode = get_value("kcode:$index");
  # (元の)改行コードの取得
  $rcode = get_value("rcode:$index");
  # パスワードの取得
  $passwd = get_value('passwd');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);

  # ファイルの中身が送られて来ない場合 -> 編集対象ではない
  if(!$body) {
    return();
  }
  
  # パスワードが合っていたら(または管理者パスワードなら)，
  # 指定のファイルを編集
  if($cpasswd eq $crypt_passwd ||
     $myPasswd->isCorrect($passwd) eq 'true') {
    # 改行コードを元に戻す
    if($rcode eq 'crlf') {
      $body =~ s/(\r?\n|\r)/\r\n/g;
    }
    if($rcode eq 'lf') {
      $body =~ s/(\r?\n|\r)/\n/g;
    }
    if($rcode eq 'cr') {
      $body =~ s/(\r?\n|\r)/\r/g;
    }
    # 文字コードを元に戻す
    $body = jcode::to($kcode, $body);
    # ファイルサイズの更新
    $all_filesize -= $filesize;
    $filesize = length($body);
    $all_filesize += $filesize;
    # ファイルの保存
    open(BWRITE, "> $userupdir/$filename") || error_unlock("<font color=\"red\">致命的エラー</font>: ファイルの書きこみに失敗しました");
    binmode BWRITE;
    print BWRITE $body;
    close(BWRITE);
    # ファイル情報の更新
    $myFileInfo->replace($index, $filename, $filesize, $date, $comment,
                         $username, $cpasswd, $addr, $downcount, $email);
    return "$filename を変更しました";
  } else {
    return "$filename を変更する権限がありません";
  }
}

# ツリー表示
sub tree_page {
  my $all_tree_flg = shift;
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  # サブディレクトリ
  my $sdir = $subdir ? $subdir : '/';
  $myEzHTML->printHeader();
print <<EOF;
    <center>
      <a name="tree"><h3>ツリー表示</h3></a>
      <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$subdir&sort=$sortmethod#download">現在のディレクトリに戻る</a>]</font></P>
      <table border="0">
        <tr>
          <td align="right">現在のディレクトリ名:</td>
          <td><img src="$fimg" alt="+"><font size=+1> $sdir </font></td>
        </tr>
      </table>
EOF
    print_dir_tree($all_tree_flg);
  print "    </center>\n";
  $myEzHTML->printFooter();
}

# ファイルの移動
sub move_page {
  my ($passwd, $onn_all, @onn);
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  # サブディレクトリ
  my $sdir = $subdir ? $subdir : '/';

  # パスワードの取得
  $passwd = get_value('passwd');
  # チェックされたファイルの取得
  foreach my $key (get_keys()) {
    my $value = get_value($key);
    if($value eq 'on') {
      $key = sprintf("%05d", $key);
      $onn[$#onn+1] = $key;
    }
  }

  if($#onn < 0) {
    error("移動対象ファイルがチェックされていません", 3);
  }

  $onn_all = join(':', @onn);
  $myEzHTML->printHeader();
print <<EOF;
    <center>
      <a name="move"><h3>ファイルの移動</h3></a>
      <P><font size=+1>[<a href="$prog_filename?page=Main&dir=$subdir&sort=$sortmethod#download">現在のディレクトリに戻る</a>]</font></P>
      <table border="0">
        <tr>
          <td align="right">現在のディレクトリ名:</td>
          <td><img src="$fimg" alt="+"><font size=+1> $sdir </font></td>
        </tr>
      </table>
      <table border="0" WIDTH="80%" cellpadding="2">
        <tr align="center" BGCOLOR="#FFD8D8">
          <th nowrap> ファイル名 </th>
          <th nowrap> 容量(byte) </th>
          <th nowrap> 登録日 </th>
          <th nowrap> コメント </th>
          <th nowrap> ユーザ名 </th>
        </tr>
EOF
  foreach my $index (@onn) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);
    my $img = $filesize == 0 ? $fimg : get_image($filename);
    my $color = $filesize == 0 ? "#D8FFD8" : "#FFFFD8";
    my $ffilesize = get_fnumber($filesize);
print <<EOF;
        <tr BGCOLOR="$color">
          <td nowrap><img src="$img" alt="-">
EOF
  if($filesize) {
    print "            <a href=\"$prog_filename?page=Download&dir=$subdir&sort=$sortmethod&filename=$filename\">\n";
  } else {
print "            <a href=\"$prog_filename?page=Main&dir=$subdir/$filename&sort=$sortmethod#download\">\n";
  }
print <<EOF;
              $filename
            </a>
          </td>
EOF
  if($filesize) {
    print "            <td align=\"right\"> $ffilesize </td>\n";
  } else {
    print "            <td align=\"center\"> (directory) </td>\n";
  }
print <<EOF;
          <td align="center"> $date </td>
          <td> $comment </td>
          <td align="center" nowrap>
EOF
  if($email) {
    print "            <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "            $username\n";
  }    
print <<EOF;
          </td>
        </tr>
EOF
  }
print <<EOF;
          <tr BGCOLOR="#D8D8D8">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
      </table>
    </center>

    <P></P>

    <center>
      <P>[移動先]</P>
EOF
  print_dir_tree('move', $passwd, $onn_all);
  print "    </center>\n";
  $myEzHTML->printFooter();
}

# ファイル移動
sub move_file_page {
  my ($message, @onn);

  # チェックされたファイルの取得
  @onn = split(/:/,get_value('onn'));

  # ファイルの移動
  foreach my $index (reverse sort @onn) {
    my $tmp = move_file_sub($index);
    if($tmp) {
      $message .= $tmp ."<BR>";
    }
  }	
  # moved to 2000.08.29-c
  # ファイル情報の保存
  save_fileinfo($myFileInfo, $userdatadir);
  message($message, 3);
}

# ファイル移動サブルーチン
sub move_file_sub {
  my $index = shift;
  my ($passwd, $crypt_passwd, $to_dir);

  # ファイル名等の取得
  my ($filename, $filesize, $date, $comment,
      $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);

  # 移動先の取得
  $to_dir = get_value('to_dir');
  # パスワードの取得
  $passwd = get_value('passwd');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);
  
  # 移動先に同一名のファイルまたはディレクトリが存在する場合
  if(-e "$uploaddir$to_dir/$filename") {
      return "$filename は既に登録されています．移動は実行されませんでした";
  }

  # ディレクトリをそのサブディレクトリに移動することは出来ない
  if("$uploaddir$to_dir" =~ /$userupdir\/$filename.*/) {
      return "$filename をそのサブディレクトリに移動することは出来ません";
  }

  # パスワードが合っていたら(または管理者パスワードなら)，
  # 指定のファイルを移動
  if($cpasswd eq $crypt_passwd ||
     $myPasswd->isCorrect($passwd) eq 'true') {
      my $rnOK = file::move("$userupdir/$filename", "$uploaddir$to_dir/$filename");
      # ディレクトリの場合は情報ファイルディレクトリも移動する
      if($filesize == 0) {
        $rnOK &= file::move("$userdatadir/$filename", "$datadir$to_dir/$filename");
      }
      if($rnOK) {
        # ファイル情報の更新
        $myFileInfo->delete($index);
        # moved from 2000.08.29-c
        my $fi = FileInfo->new();
        load_fileinfo($fi, "$datadir$to_dir");
        $fi->append($filename, $filesize, $date, $comment,
                    $username, $cpasswd, $addr, $downcount, $email);
        save_fileinfo($fi, "$datadir$to_dir");
        return "$filename を $to_dir へ移動しました";
      } else {
        error("<font color=\"red\">致命的エラー</font>: $filename の移動に失敗しました");
      }
  } else {
    return "$filename を移動する権限がありません";
  }
}

# ディレクトリのツリー構造をプリントする(汎用性なし)
sub print_dir_tree {
  my ($mode, $passwd, $onn_all) = @_;
  my $attr1;
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  if($mode eq 'move') {
print <<EOF;
      <FORM ENCTYPE="multipart/form-data" ACTION="$prog_filename" METHOD=POST>
        <INPUT TYPE="hidden" NAME="onn" VALUE="$onn_all">
        <INPUT TYPE="hidden" NAME="passwd" VALUE="$passwd">
        <INPUT TYPE="hidden" NAME="dir" VALUE="$subdir">
        <INPUT TYPE="hidden" NAME="sort" VALUE="$sortmethod">
EOF
  }
print <<EOF;
      <table border="0" WIDTH="80%" cellpadding="2">
        <tr align="center" BGCOLOR="#FFD8D8">
          <th nowrap> ファイル名 </th>
          <th nowrap> 登録日 </th>
          <th nowrap> コメント </th>
          <th nowrap> ユーザ名 </th>
EOF
  if($mode eq 'move') {
    print "          <th nowrap> 移動先 </th>\n";
  }
  print "        </tr>\n";
  $attr1 = !$subdir ? "BGCOLOR=\"#D8D8FF\" BACKGROUND=\"$cd_filename\"" : "";
print <<EOF;
        <tr BGCOLOR="#D8FFD8">
            <td $attr1 nowrap> <img src="$fimg" alt="+"> <a href="$prog_filename?page=Main&dir=&sort=$sortmethod#download"> / </a> </td>
            <td align="center" $attr1>&nbsp;</td>
            <td $attr1>&nbsp;</td>
            <td align="center" $attr1>&nbsp;</td>
EOF
  if($mode eq 'move') {
    if($subdir) {
      print "            <td align=\"center\" $attr1> <input type=\"radio\" name=\"to_dir\" value=\"\"></td>\n";
    } else {
      print "            <td $attr1>&nbsp;</td>\n";
    }
  }
  print "        </tr>\n";
  print_dir_tree_sub('', 1, $mode);
print <<EOF;
          <tr BGCOLOR="#D8D8D8">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
EOF
  if($mode eq 'move') {
    print "            <td>&nbsp;</td>\n";
  }
  print "          </tr>\n";
  print "      </table>\n";
  if($mode eq 'move') {
    print "        <INPUT TYPE=\"SUBMIT\" NAME=\"page\" VALUE=\"ファイルの移動\">\n"; 
  }
}

# ディレクトリのツリー構造をプリントする(汎用性なし)
sub print_dir_tree_sub {
  my ($sdir, $depth, $mode, $tbar) = @_;
  my $attr1;
  # フォルダ画像の取得
  my $fimg = get_image('folder');
  my $fi = FileInfo->new();
  load_fileinfo($fi, "$datadir$sdir");
  my @didxs = $fi->getDirIndexes();
  my @fidxs = $fi->getFileIndexes();
  foreach my $index (@didxs) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $fi->get($index);
    my $bar; $tb;
    if($index == $didxs[$#didxs] && $#fidxs < 0) {
      $bar = $tbar . "<img src=\"$gifdir/haji.gif\" alt=\"└\"> ";
      $tb = $tbar . "<img src=\"$gifdir/space.gif\" alt=\"　\"> ";
    } else {
      $bar = $tbar . "<img src=\"$gifdir/fusi.gif\" alt=\"├\"> ";
      $tb = $tbar . "<img src=\"$gifdir/tate.gif\" alt=\"│\"> ";
    }
    $attr1 = $subdir eq "$sdir/$filename" ? "BGCOLOR=\"#D8D8FF\" BACKGROUND=\"$cd_filename\"" : "";
print <<EOF;
        <tr BGCOLOR="#D8FFD8">
          <td $attr1 nowrap>$bar<img src="$fimg" alt="+"><a href="$prog_filename?page=Main&dir=$sdir/$filename&sort=$sortmethod#download"> $filename </a> </td>
          <td align="center" $attr1> $date </td>
          <td $attr1> $comment </td>
          <td align="center" $attr1 nowrap>
EOF
  if($email) {
    print "            <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "          $username\n";
  }    
print <<EOF;
          </td>
EOF
    if($mode eq 'move') {
      if($subdir eq "$sdir/$filename") {
        print "            <td align=\"center\" $attr1>&nbsp;</td>\n";
      } else {
        print "            <td align=\"center\" $attr1> <input type=\"radio\" name=\"to_dir\" value=\"$sdir/$filename\"></td>\n";
      }
    }
    print "        </tr>\n";
    print_dir_tree_sub("$sdir/$filename", $depth+1, $mode, $tb);
  }
  if($mode eq 'tree' && $subdir eq $sdir || $mode eq 'all_tree') {
    foreach my $index (@fidxs) {
      my ($filename, $filesize, $date, $comment,
          $username, $cpasswd, $addr, $downcount, $email) = $fi->get($index);
    my $bar; $tb;
    if($index == $fidxs[$#fidxs]) {
      $bar = $tbar . "<img src=\"$gifdir/haji.gif\" alt=\"└\"> ";
      $tb = $tbar . "<img src=\"$gifdir/space.gif\" alt=\"　\"> ";
    } else {
      $bar = $tbar . "<img src=\"$gifdir/fusi.gif\" alt=\"├\"> ";
      $tb = $tbar . "<img src=\"$gifdir/tate.gif\" alt=\"│\"> ";
    }
      my $img = get_image($filename);
print <<EOF;
        <tr BGCOLOR="#FFFFD8">
            <td nowrap>$bar<img src="$img" alt="-"><a href="$prog_filename?page=Download&dir=$sdir&sort=$sortmethod&filename=$filename"> $filename </a></td>
          <td align="center"> $date </td>
            <td> $comment </td>
            <td align="center" nowrap>
EOF
  if($email) {
    print "              <a href=\"mailto:$email\">$username</a>\n";
  } else {
    print "            $username\n";
  }    
print <<EOF;
            </td>
          </tr>
EOF
   }
  }
}

# ファイルアップロードページ
sub upload_page {
  my ($message);

  # 結果表示用
  $message = '';
  for(my $i=0; $i<$file_num; $i++) {
    my $tmp = upload_page_sub($i);
    if($tmp) {
      $message .= $tmp ."<BR>";
    }
  }
  # メッセージなし=ファイル名が一つも指定されていない
  if(!$message) {
    error("ファイル名が指定されていません",3);
  }

  # moved to 2000.08.29-d
  # ファイル情報の書きこみ 
  save_fileinfo($myFileInfo, $userdatadir);
  # ディスク情報の書き込み
  save_diskinfo($all_filesize);
  message($message, 3);
}

# ファイルアップロードページのサブルーチン
sub upload_page_sub {
  my ($filepath, $filename, $filesize, $comment, $email, $username, $passwd, $crypt_passwd, $override, $ovm);
  my ($date, $next_all_filesize, $admin_crypt_passwd);
  my $i = shift;

  # ファイル名と一時ファイルのパスの取得
  ($filename, $filepath) = split(/:/, get_value("save$i"));
  # ファイルサイズの取得
  $filesize = -s $filepath;
  # 登録日の設定
  $date = "$year.$mon.$mday";
  # コメントの取得
  $comment = get_value('comment');
  # E-MAILの取得
  $email   = get_value('email');
  # ユーザ名の取得
  $username = get_value('username');
  # パスワードの取得
  $passwd = get_value('passwd');
  # 上書きフラグの取得 
  $override = get_value('override');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);

  # ファイルがアップロードされた場合のディスク容量の試算
  $next_all_filesize = $all_filesize+$filesize;

  # ファイル名のセキュリティチェック
  $filename = sandbox_filename($filename, $passwd);
  # コメントのセキュリティチェック
  $comment = sandbox_comment($comment);
  # ユーザ名のセキュリティチェック
  $username = sandbox_username($username);

  # ファイル名が指定されていない場合は中断
  if($filename =~ /^\s*$/) {
    return();
  }

  # 上書きを許可している場合を除き，同一名のファイルが存在していた場合は中断
  if($myFileInfo->getIndexByFileName($filename) >= 0 && $override ne 'on') {
    return "$filename は既に登録されています．別の名前で保存して下さい";
  }

  # 全ファイルのサイズが許容量を越える場合は中断
  # FIXME: 上書き登録時の挙動は正しくない(オーバーしていない場合もオーバーと判定される場合あり)
  if($next_all_filesize > $max_all_filesize) {
    error("サーバに登録出来る容量 ($max_all_filesize byte) を越えました．現時点で $all_filesize byte の容量が使用されています．$filename のサイズは $filesize byte であるため，登録は出来ません．<br>適切なファイルを削除したのちに再登録を行うか，管理者に問い合わせてみて下さい");
  }

  # ファイルが空の場合は中断
  if($filesize == 0) {
    return "$filename は空のファイル，または存在しないファイルです";
  }

  # ファイルを保存出来る場合

  # ディスク使用量の更新
  $all_filesize = $next_all_filesize;

  # 上書き許可時にファイルが存在する場合は，ファイルおよび管理データを削除
  if($myFileInfo->getIndexByFileName($filename) >= 0) {
    # FIXME: 削除の権限を調べる．インチキな判定法
    if(delete_sub($myFileInfo->getIndexByFileName($filename)) =~ /削除する権限がありません/) {
      return "$filename を上書きする権限がありません";
    }
    $ovm = '上書き';
  }

  # データファイルの移動(一時ディレクトリからの移動)
  if(!file::move("$filepath", "$userupdir/$filename")) {
    error_unlock("<font color=\"red\">致命的エラー</font>: ファイルのアップロードに失敗しました");
  }
  
  # ファイル情報の書き込み
  $myFileInfo->append($filename, $filesize, $date, $comment,
                      $username, $crypt_passwd, $host_name, 0, $email);
  # moved from 2000.08.29-d
  # クッキーぱくぱく
  if($f_cookie eq 'on') {
    save_cookie($username, $passwd, $comment, $d_comment_md, $f_cookie, $override, $email);
  }
  return "$filename をサーバーに<font color=\"red\">$ovm</font>登録しました";
}

# ディレクトリの作成ページ
sub mkdir_page {
  my ($filename, $comment, $email, $username, $passwd, $crypt_passwd, $date);
  my ($admin_crypt_passwd);

  # ディレクトリ名の取得
  $filename = get_value('filename');
  # 登録日の設定
  $date = "$year.$mon.$mday";
  # コメントの取得
  $comment = get_value('comment');
  # E-MAILの取得
  $email   = get_value('email');
  # ユーザ名の取得
  $username = get_value('username');
  # パスワードの取得
  $passwd = get_value('passwd');
  # 暗号化パスワードの取得
  $crypt_passwd = crypt($passwd, $filename);

  # ファイル名のセキュリティチェック
  $filename = sandbox_filename($filename, $passwd, 'true');
  # コメントのセキュリティチェック
  $comment = sandbox_comment($comment);
  # ユーザ名のセキュリティチェック
  $username = sandbox_username($username);

  # ディレクトリ名が指定されていない場合は中断
  if($filename =~ /^\s*$/) {
    error("ディレクトリ名が指定されていません",3);
  }

  # 同一名のディレクトリまたはファイルが存在していた場合は中断
  if($myFileInfo->getIndexByFileName($filename) >= 0) {
    error("$filename は既に登録されています．別の名前で保存して下さい");
  }
  
  # ディレクトリを作成出来る場合
  
  # ディレクトリの作成
  mkdir("$userdatadir/$filename", 0777) || error("<font color=\"red\">致命的エラー</font>: ディレクトリ $filename の作成に失敗しました");
  mkdir("$userupdir/$filename", 0777) || error("<font color=\"red\">致命的エラー</font>: ディレクトリ $filename の作成に失敗しました");

  # ディレクトリ情報の書き込み
  $myFileInfo->append($filename, 0, $date, $comment,
                      $username, $crypt_passwd, $host_name, 0, $email);
  save_fileinfo($myFileInfo, $userdatadir);
  # クッキーぱくぱく
  if($f_cookie eq 'on') {
    save_cookie($username, $passwd, $d_comment_up, $comment, $f_cookie, $d_override, $email);
  }
  message("ディレクトリ $filename を作成しました", 2);
}

# ファイルのダウンロード
# IN : $fn ファイル名
sub file_download {
  my ($dir, $fn) = @_;
  my $index = $myFileInfo->getIndexByFileName($fn);
  # ファイル名等の取得
  my ($filename, $filesize, $date, $comment,
      $username, $cpasswd, $addr, $downcount, $email) = $myFileInfo->get($index);

  # ファイル情報の更新: ダウンロード数をインクリメント
  $myFileInfo->replace($index, $filename, $filesize, $date, $comment,
                       $username, $cpasswd, $addr, $downcount+1, $email);
  save_fileinfo($myFileInfo, $userdatadir);

  # 指定ファイルの表示
  print "Content-type: text/html\n";
  print "Location: $dir/$filename\n\n";
  exit(0);
}

#
# データ処理関連
#

# アップロード許可が許可されているホストかどうかを調べます．
# @return true  ... OK
#         false ... NG
sub check_remotehost {
  my $flag = 'true';
  # アップロード不許可ホスト名の取得およびマッチング
  if(-e $hosts_deny_filename) {
    open(READ, "< $hosts_deny_filename") || error("<font color=\"red\">致命的エラー</font>: アップロード不許可ホストファイルの読み込みに失敗しました");
    while(<READ>) {
      chomp; if($host_name =~ /^$_$/ || $ipaddr =~ /^$_$/) { $flag = 'false'; }
    }
    close(READ);
  }
  
  # アップロード許可ホスト名の取得およびマッチング
  if(-e $hosts_allow_filename) {
    open(READ, "< $hosts_allow_filename") || error("<font color=\"red\">致命的エラー</font>: アップロード許可ホストファイルの読み込みに失敗しました");
    while(<READ>) {
      chomp; if($host_name =~ /^$_$/ || $ipaddr =~ /^$_$/) { $flag = 'true'; }
    }
    close(READ);
  }
  return($flag);
}

# ユーザディレクトリ，サブディレクトリの取得
sub get_dirs {
  my($subdir, $userupdir, $userupurl, $userdatadir, $downfile);

  # 不正アクセスの検出
  if(get_value('dir') =~ /\.\./) {
    caution("<font color=\"red\">警告</font>: ".get_value('dir').": 不正アクセスが検出されました", 3);
  }
  if(get_value('filename') =~ /\.\./) {
    caution("<font color=\"red\">警告</font>: ".get_value('filename').": 不正アクセスが検出されました", 3);
  }

  if(!$uploadurl) {
      $uploadurl = $uploaddir;
  }

  if(get_value('dir')) {
    $subdir = get_value('dir');
    $userupdir = "$uploaddir$subdir";
    $userupurl = "$uploadurl$subdir";
    $userdatadir = "$datadir$subdir";
  } else {
    $subdir = '';
    $userupdir = $uploaddir;
    $userupurl = $uploadurl;
    $userdatadir = $datadir;
  }
  if(get_value('filename')) {
    $downfile = get_value('filename')
  }
  return($userupdir, $userupurl, $userdatadir, $subdir, $downfile);
}

# ソート方法の取得
sub get_sortmethod {
  my($sortmethod);
  if(get_value('sort')) {
    $sortmethod = get_value('sort');
  } else {
    $sortmethod = 'date'; # デフォルトでは日付順にソート
  }
  return($sortmethod);
}

# ファイル種別に応じた画像の取得
# IN : $filename ファイル名または 'folder', 'std_image'
# OUT: 画像ファイル名
sub get_image {
  my $filename = shift;
  my ($suffix, $ifn);
  if($filename =~ /.*\.(.*)/) {
      $suffix = lc($1);
  }

  if(!$suffix) {
      $suffix = lc($filename);
  }
  $ifn = $image{$suffix};
  if(!$ifn) {
      $ifn = $image{'std_image'};
  }
  return($ifn);
}

# 数値に , をつける
# IN : 数値
# OUT: , がついた数値
sub get_fnumber {
  my $n = shift;
  while($n =~ s/(\d+)(\d\d\d)/$1,$2/) {}
  return $n;
}

#
# セキュリティ関係
#

# ファイル名のチェック及び修正
# IN : $filename ファイル名
#      $passwd 入力されたパスワード(ファイル取り扱い権限チェック用)
#      $dflg ディレクトリの場合は 'true' を設定 (扱いがファイルとやや異なるため)
# OUT: 修正されたファイル名
sub sandbox_filename {
    my ($filename, $passwd, $dflg) = @_;
    my ($admin_up_all, $us_on_up);

    # 不正アクセス検出(get_dirs メソッドを経由していれば，ここでは必要がない処理だが念のため)
    if($filename =~ /\.\./) {
      caution("<font color=\"red\">警告</font>: $filename: 不正アクセスが検出されました", 3);
    }

  # 管理者特権が利用されているかどうかを調べる
    if($admin_upload_all eq 'true' && 
       $myPasswd->isCorrect($passwd) eq 'true') {
      $admin_up_all = 'true';
    } else {
      $admin_up_all = 'false';
    }

    # アップロードが許可されていないホストの場合は中断
    if($host_allow ne 'true' && $admin_up_all ne 'true') {
      error("大変申し訳ありませんが，貴方のホストからの操作は許可されていません");
    }

    # ファイルを操作する権限があるかどうかを調べる
    if($user_only_upload ne 'true' || 
       $myPasswd->isCorrect($passwd) eq 'true' ||
       $myPasswd2->isCorrect($passwd) eq 'true') {
      $us_on_up = 'true';
    } else {
      $us_on_up = 'false';
    }
    # アップロードが許可されていないパスワードの場合は中断
    if($us_on_up ne 'true') {
      error("大変申し訳ありませんが，ご使用頂いたパスワードは無効です");
    }

  # アップロード等が許可されているファイル名かどうかを調べる
  my $save_ok = 'true';
    foreach (@ng_filename) {
      if($filename =~ /$_/i) { $save_ok = 'false'; }
    }
    foreach (@ok_filename) {
      if($filename =~ /$_/i) { $save_ok = 'true'; }
    }
    # セーブ禁止ファイルの場合は処理を中断
    if($save_ok ne 'true' && $admin_up_all ne 'true' && $dflg ne 'true') {
      error("$filename は登録が禁止されているファイルです");
    }

    # サブディレクトリは指定できない
    if($filename =~ /\// || $filename =~ /\\/) {
      error("$filename: サブディレクトリを指定することは出来ません");
    }

    # ファイル名には特定の文字しか使えないようにする．
    my $tmp = $filename;
    $tmp =~ s/[\-_0-9A-Za-z\.]//g;
    if($tmp && $restrict_filename eq 'true') {
      error("$filename は不適切なファイル名です．ファイル名には「半角英数字」及び「-(ハイフン)」「_(アンダーバー)」「.(ドット)」のみが使用可能です");
    }

    return $filename
}

# コメントのチェック及び修正
# IN : $comment コメント
# OUT: 修正されたコメント
sub sandbox_comment {
    my $comment = shift;
    # 一部の文字はエンティティ(または擬似エンティティ)に変換
    ($comment) = get_entity($comment);
    # コメントが空だったら適当「なし」とする
    if($comment =~ /^\s*$/) {
      $comment = 'なし';
    }
    return $comment;
}

# ユーザ名のチェック及び修正
# IN : $username ユーザ名
# OUT: 修正されたユーザ名
sub sandbox_username {
    my $username = shift;
    # 一部の文字はエンティティ(または擬似エンティティ)に変換
    ($username) = get_entity($username);
    # ユーザ名が指定されていない場合は中断
    if($username =~ /^\s*$/) {
      error("ユーザ名が指定されていません");
    }
    return $username;
}

# 文字をエンティティ(または擬似エンティティ)に変換
# IN : @strs_in 文字列のリスト
# OUT: 変換された文字列のリスト
sub get_entity {
    my @strs_in = @_;
    my @strs_out;
    foreach my $str (@strs_in) {
      $str =~ s/</&lt;/g;
      $str =~ s/>/&gt;/g;
      $str =~ s/"|"/&quot;/g;
      $str =~ s/:/&colon;/g;
      $strs_out[$#strs_out+1] = $str;
    }
    return @strs_out;
}

# 擬似エンティティを元に戻す
# IN : @strs_in 文字列のリスト
# OUT: 変換された文字列のリスト
sub repair_pseudo_entity {
    my @strs_in = @_;
    my @strs_out;
    foreach my $str (@strs_in) {
      $str =~ s/&colon;/:/g;
      $strs_out[$#strs_out+1] = $str;
    }
    return @strs_out;
}

# 送信されたパラメータの値の取りだし
# IN : $key キー
# OUT: 値
sub get_value {
    my $key = shift;
    return ($$values{$key});
}

# 送信されたパラメータのキーのリストの取りだし
# OUT: キーのリスト
sub get_keys {
    return (keys %$values);
}

#
# ディスクアクセス等
#

# ファイル情報(生データ)を読みこむ
# IN : $fInfo ... ファイル情報クラス(参照)
#      $dirname ... ファイル情報が置かれたディレクトリ名
sub load_fileinfo {
    my $fInfo = shift;
    my $dirname = shift;
    my @info;
    # 情報ファイルが無い場合は，処理を中断して戻る
    if(!-e "$dirname/$fileinfo_filename") {
      return();
    }
    my_lock();
    open(READ, "< $dirname/$fileinfo_filename") || error_unlock("<font color=\"red\">致命的エラー</font>: ファイル情報 $dirname/$fileinfo_filename の読み込みに失敗しました");
    @info = <READ>;
    close(READ);
    my_unlock();

  foreach(@info) {
    chomp; $fInfo->append(repair_pseudo_entity(split(/:/, $_)));
  }
}

# ファイル情報(生データ)を書きこむ
# IN : $fInfo ... ファイル情報クラス
#      $dirname ... ファイル情報が置かれたディレクトリ名
sub save_fileinfo {
    my $fInfo = shift;
    my $dirname = shift;
    my_lock();
    open(WRITE, "> $dirname/$fileinfo_filename") || error_unlock("<font color=\"red\">致命的エラー</font>: ファイル情報 $dirname/$fileinfo_filename の書き込みに失敗しました");
  for(my $i=0; $i<$fInfo->length(); $i++) {
    my $data = join(':', get_entity($fInfo->get($i)));
    print WRITE "$data\n";
  }
  close(WRITE);
  my_unlock();
}

# ディスク情報をグローバル変数に読み込む
# OUT: ディスク使用量
sub load_diskinfo {
  my $afs;
  # 情報ファイルが無い場合は，処理を中断して戻る
  if(!-e "$datadir/$diskinfo_filename") {
    return 0;
  }
  my_lock();
  open(READ, "< $datadir/$diskinfo_filename") || error_unlock("<font color=\"red\">致命的エラー</font>: ディスク情報 $datadir/$diskinfo_filename の読み込みに失敗しました");
  $afs = <READ>;
  close(READ);
  my_unlock();
  return $afs;
}

# ディスク情報の書きこみ
# IN : ディスク使用量
sub save_diskinfo {
  my $afs = shift;
  my_lock();
  open(WRITE, "> $datadir/$diskinfo_filename") || error_unlock("<font color=\"red\">致命的エラー</font>: ディスク情報 $datadir/$diskinfo_filename の書き込みに失敗しました");
  print WRITE $afs;
  close(WRITE);
  my_unlock();
}

# 特定のディレクトリ以下ののファイル容量を取得
# IN : $dirname ディレクトリ名(絶対パス)
# OUT: 容量(byte)
sub dirsize {
  my $dirname = shift;
  my $afs;
  # 2000.09.19
  my $fi = FileInfo->new();
  load_fileinfo($fi, "$dirname");
  my @didxs = $fi->getDirIndexes();
  my @fidxs = $fi->getFileIndexes();
  foreach my $index (@fidxs) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $fi->get($index);
    $afs += $filesize;
  }
  # サブディレクトリも
  foreach my $index (@didxs) {
    my ($filename, $filesize, $date, $comment,
        $username, $cpasswd, $addr, $downcount, $email) = $fi->get($index);
    $afs += dirsize("$dirname/$filename");
  }
  return($afs);
}

# クッキー情報の読み込み
# OUT: ($d_username, $d_passwd, $d_comment_up, $d_comment_md, $d_cookie, $d_override, $d_email)
sub load_cookie {
    return repair_pseudo_entity(split(/:/, cookie::getCookie("UPLOAD")));
}

# クッキー情報の書き込み
# IN: ($d_username, $d_passwd, $d_comment_up, $d_comment_md, $d_cookie, $d_override, $d_email)
sub save_cookie {
  my $data = join(':', get_entity(@_));
  cookie::setCookie("UPLOAD", $data, 60);
}


#
# 汎用
#

# ファイルの読み込みにロックを掛ける
sub my_lock {
  if(!$myLock->lock()) {
    # ロックが続く場合はロックディレクトリが残っている可能性があるので削除
    open(READ, "< $datadir/$lock_error_count_filename");
    my $c = <READ>;
    close(READ);
    if(++$c >= $max_lock_error_count) {
      my_unlock();
      $c = 0;
    }
    open(WRITE, "> $datadir/$lock_error_count_filename");
    print WRITE $c;
    close(WRITE);
    $myEzHTML->error("現在サーバが混雑しておりますので，後程アクセスして下さい");
  }
}

# ロックを解除する
sub my_unlock {
  $myLock->unlock();
}

# エラーメッセージの前にロックを解除
sub error_unlock {
  my_unlock();
  error(@_);
}

# エラーメッセージ
sub error {
  # 一時ファイルが存在する場合は削除
  for(my $i=0; $i<$file_num; $i++) {
    my ($filename, $filepath) = split(/:/, get_value("save$i"));
    if($filepath) {
      file::delete($filepath);
    }
  }
  $myEzHTML->error(@_);
}

sub message {
  $myEzHTML->message(@_);
}

sub catuion {
  $myEzHTML->caution(@_);
}

# --------------------------------------------------
# クラス定義
# --------------------------------------------------
#
# ============================================================
# あるディレクトリのファイル情報を表すクラス
# FileInfo
# ============================================================
package FileInfo;
# ============================================================
# コンストラクタ
# ============================================================
# IN : なし
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  shift;
  $self->{'length'} = -1;
  return($self);
}

# ============================================================
# append 関数
# ============================================================
# ファイル情報の追加
# IN : ($filename, $filesize, $date, $comment,
#       $username, $crypt_passwd, $addr, $downcount, $email)
sub append {
  my $self = shift;
  my $index = ++$self->{'length'};
  $self->{'filename'}[$index] = shift;
  $self->{'filesize'}[$index] = shift;
  $self->{'date'}[$index] = shift;
  $self->{'comment'}[$index] = shift;
  $self->{'username'}[$index] = shift;
  $self->{'crypt_passwd'}[$index] = shift;
  $self->{'addr'}[$index] = shift;
  $self->{'downcount'}[$index] = shift;
  $self->{'email'}[$index] = shift;

  $self->{'flag'}{$self->{'filename'}[$index]} = $index+1;
}

# ============================================================
# replace 関数
# ============================================================
# ファイル情報の置き換え
# IN : ($index, $filename, $filesize, $date, $comment,
#       $username, $crypt_passwd, $addr, $downcount, $email)
sub replace {
  my $self = shift;
  my $index = shift;

  # インデックスが不正な場合は何もしない
  if($index > $self->{'length'}) {
    return();
  }

  $self->{'flag'}{$self->{'filename'}[$index]} = 0;

  $self->{'filename'}[$index] = shift;
  $self->{'filesize'}[$index] = shift;
  $self->{'date'}[$index] = shift;
  $self->{'comment'}[$index] = shift;
  $self->{'username'}[$index] = shift;
  $self->{'crypt_passwd'}[$index] = shift;
  $self->{'addr'}[$index] = shift;
  $self->{'downcount'}[$index] = shift;
  $self->{'email'}[$index] = shift;
  $self->{'length'} = $self->{'length'} > $index ? $self->{'length'} : $index;

  $self->{'flag'}{$self->{'filename'}[$index]} = $index+1;
}

# ============================================================
# delete 関数
# ============================================================
# ファイル情報の削除
# IN : $index
sub delete {
  my $self = shift;
  my $index = shift;

  # インデックスが不正な場合は何もしない
  if($index > $self->{'length'}) {
    return();
  }

  $self->{'flag'}{$self->{'filename'}[$index]} = 0;

  my($a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8);

  # うーん，何かやり方を間違えてるような気が…
  
  $a1 = $self->{'filename'};
  $a2 = $self->{'filesize'};
  $a3 = $self->{'date'};
  $a4 = $self->{'comment'};
  $a5 = $self->{'username'};
  $a6 = $self->{'crypt_passwd'};
  $a7 = $self->{'addr'};
  $a8 = $self->{'downcount'};
  $a9 = $self->{'email'};

  splice(@$a1, $index,1);
  splice(@$a2, $index,1);
  splice(@$a3, $index,1);
  splice(@$a4, $index,1);
  splice(@$a5, $index,1);
  splice(@$a6, $index,1);
  splice(@$a7, $index,1);
  splice(@$a8, $index,1);
  splice(@$a9, $index,1);

  $self->{'length'}--;
}

# ============================================================
# get 関数
# ============================================================
# ファイル情報の取得
# IN : $index
# OUT: ($filename, $filesize, $date, $comment,
#       $username, $crypt_passwd, $addr, $downcount, $email)
sub get {
  my $self = shift;
  my $index = shift;
  return ($self->{'filename'}[$index], $self->{'filesize'}[$index],
          $self->{'date'}[$index], $self->{'comment'}[$index],
          $self->{'username'}[$index], $self->{'crypt_passwd'}[$index],
          $self->{'addr'}[$index], $self->{'downcount'}[$index], $self->{'email'}[$index]);
}

# ============================================================
# getIndexByFileName 関数
# ============================================================
# インデックスをファイル名から取得します
# IN : $filename
# OUT: $index
sub getIndexByFileName {
  my $self = shift;
  my $filename = shift;
  return $self->{'flag'}{$filename}-1;
}

# ============================================================
# getAllFileSize 関数
# ============================================================
# ファイルサイズの合計
# OUT: $all_filesize
sub getAllFileSize {
  my $self = shift;
  my $afs = 0;
  for(my $i=0; $i<=$self->{'length'}; $i++) {
    $afs += $self->{'filesize'}[$i];
  }
  return $afs;
}

# ============================================================
# getFileIndexes 関数
# ============================================================
# ファイル(非ディレクトリ)のインデックス列を返します．キーが与えられた場合にはソートを行います．
# IN : $key = ソート対象 (省略時 'date')
# OUT: @indexes
sub getFileIndexes {
  my $self = shift;
  my $key = shift;
  my @fi = ();
  $key = $key ? $key : 'date';
  # インデックステーブルの作成
  my (%table, @array);
  for(my $i=0; $i<=$self->{'length'}; $i++) {
    # 同一の日付をハッシュに格納するために最後にインデックスを付加しておく．
    my $k = $self->{$key}[$i] . sprintf("%05d", $i);
    $table{$k} = $i;
    $array[$i] = $k;
  }
  @array = sort @array;
  foreach my $k (@array) {
    my $i = $table{$k};
    if($self->{'filesize'}[$i] > 0) {
      push(@fi, $i);
    }
  }
  return @fi;
}

# ============================================================
# getDirIndexes 関数
# ============================================================
# ディレクトリのインデックス列を返します
# IN : $key = ソート対象 (省略時 'date')
# OUT: @indexes
sub getDirIndexes {
  my $self = shift;
  my $key = shift;
  my @fi = ();
  $key = $key ? $key : 'date';
  # インデックステーブルの作成
  my (%table, @array);
  for(my $i=0; $i<=$self->{'length'}; $i++) {
    # 同一の日付をハッシュに格納するために最後にインデックスを付加しておく．
    my $k = $self->{$key}[$i] . sprintf("%05d", $i);
    $table{$k} = $i;
    $array[$i] = $k;
  }
  @array = sort @array;
  foreach my $k (@array) {
    my $i = $table{$k};
    if($self->{'filesize'}[$i] <= 0) {
      push(@fi, $i);
    }
  }
  return @fi;
}

# ============================================================
# length 関数
# ============================================================
# ファイル数の取得 ($indexの最大値+1)
# OUT: ファイル数
sub length {
  my $self = shift;
  return $self->{'length'}+1;
}
