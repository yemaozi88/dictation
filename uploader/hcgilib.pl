#use strict;
# ============================================================
# へっぽこ CGI ライブラリ
# hcgilib.pl v0.98 copyright(c)2000/01/01 - S.Washio
# URL:  http://homepage1.nifty.com/~heppoko/
# MAIL: heppoko@kun.ne.jp
# ============================================================
# Perl の CGI プログラムから利用されるパッケージを集めた
# ライブラリです．v0.7 から一つのファイルに統合されました．


# ============================================================
# ウェブページのアクセスログ等を扱うためのパッケージ
# accesslog v0.71
# ============================================================
package accesslog;

# ============================================================
# record 関数
# ============================================================
#
# この関数は，ウェブページのアクセス状況を記録するための
# 関数です．
#
# この関数の特徴を以下に示します．
#
# a. アクセスされた時間，リモートホスト名およびIPアドレス，アクセスされた
#    場所等を記録
#
# b. 特定のホスト名やIPアドレスに対してアクセス状況を記録しないように設定
#    することが可能

# ------------------------------------------------------------
# ☆使用法
# ------------------------------------------------------------
# ◎関数の使用例を示します
#
# 使用例1: accesslog::record('log/access.log');
# 使用例2: accesslog::record('accesslog/access.log', 'bbs1');
# 使用例3: accesslog::record('accesslog/access.log', 'bbs1', '.hosts_ignore');
#
# 注意: 文字コードの関係上，識別用テキストには日本語を使わない方が
#       無難です．SJIS と EUC が混在する場合があります．

# ◎ログの例を示します
#
# DATE=99/08/01 TIME=16:16 HOST=cow ADDR=192.168.10.10
#      NAME=bbs1
#      REF=http://homepage1.nifty.com/~heppoko/cgi-bin/minibbs.cgi
#      AGENT=Mozilla/4.08 [Vine-ja] (X11; I; Linux 2.0.36 i686)

# ◎ログには記録しないホスト名(またはIPアドレス)を記述したファイルは
#   以下のような形式に従って下さい
#
# [.hosts_ignore]
# hoge
# abc.co.*
# 202.123.45.12
#
# これらのホストはアクセス記録には残りません．
# ホスト名やIPアドレスには正規表現を使用することが出来ます．
#
# 注意: ホスト名の前後に不要な空白などは入れないで下さい．

# ------------------------------------------------------------
# ☆注意
# ------------------------------------------------------------
# この関数によって作成されるログを解析するためには，
# acana.cgi v0.84 以降が必要になります．それ以前のバージョンでは
# 解析が正しく行われません．

# ------------------------------------------------------------
# ☆関数本体
# ------------------------------------------------------------
# IN : $log_filename = アクセスログファイル名, )
#     [$id_text]     = [識別用テキスト]
#     [$ih_filename] = [ログには記録しないホスト名(またはIPアドレス)を記述したファイル]
# OUT: $err = エラーメッセージ(エラーがない場合はnull)
sub record {
  # ---------- 初期設定 ----------
  # アクセスログファイル名, 識別用テキスト, 無視ホスト名記述ファイル名
  my ($log_filename, $log_page_name, $log_ignore_filename) = @_;
  
  # ---------- アクセスログ ----------
  # 時刻の記録
  my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = getTime();
  # リモートホスト情報の記録
  my ($host,$addr,$ref,$agent) = getRemoteHostInfo();
  
  # ログ記録フラグ
  my $flag = 'true';

  # 識別テキストから改行コード等を取り除く
  $log_page_name =~ s/\r?\n|\r//g;
  
  # 無視ホスト名の取得およびマッチング
  if($log_ignore_filename) {
    open(READ, "< $log_ignore_filename");
    while(<READ>) {
      chomp;	# 行末コードの削除
      if($host =~ /^$_$/ || $addr =~ /^$_$/) {
        $flag = 'false';   # ログは記録しない
      }
    }
    close(READ);
  }
  # ログの記録
  if($flag eq 'true') {
    open(WRITE, ">> $log_filename") || return("error in accesslog::record: couldn't write log-file $log_filename");
    print WRITE "DATE=$year/$mon/$mday TIME=$hour:$min HOST=$host ADDR=$addr\n\tNAME=$log_page_name\n\tREFERER=$ref\n\tAGENT=$agent\n\n";
    close(WRITE);
  }
  return();
}

# ============================================================
# getTime 関数
# ============================================================
# 4桁化された年号や0詰めを行った時刻等を取得します．
# Ex. $year = 2000, $mon = 02, ...
#
# OUT: ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)
sub getTime {
  my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
  $mon++;
  $year += 1900;
  $mon = ($mon<10) ? "0$mon" : $mon;
  $mday = ($mday<10) ? "0$mday" : $mday;
  $hour = ($hour<10) ? "0$hour" : $hour;
  $min = ($min<10) ? "0$min" : $min;
  return($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst);
}

# ============================================================
# getRemoteHostInfo 関数
# ============================================================
# リモートホストの情報を取得します．
#
# OUT: ($host,$addr,$ref,$agent)
sub getRemoteHostInfo {
  my $host = $ENV{'REMOTE_HOST'};
  my $addr = $ENV{'REMOTE_ADDR'};
  my $ref  = $ENV{'HTTP_REFERER'};
  my $agent = $ENV{'HTTP_USER_AGENT'};
  return($host,$addr,$ref,$agent);
}


# ============================================================
# フォームから送られたデータを扱うための【クラス】
# FormData v0.98 : CAUTION! 【deprecated 2000.09.23】
# ============================================================
package FormData;
# ------------------------------------------------------------
# ☆使用法 - このクラスの使用は推奨されません!
# ------------------------------------------------------------
# 以下のように，フォーム等から Perl の CGI プログラムを呼び出します．
#
#  <FORM ENCTYPE="multipart/form-data" ACTION="upload.cgi" METHOD=POST>
#    送信ファイル名: <INPUT TYPE="FILE" NAME="save" SIZE=48> <BR>
#    ユーザ名: <INPUT TYPE="text" NAME="username" SIZE=10> <BR>
#    <INPUT TYPE="SUBMIT" VALUE="Upload">
#  </FORM>
#
# Perl の CGI プログラムの中で以下のように使用し，送られてくるデータを
# 取り出します．
#
# [upload.cgi]
#
# # オブジェクトの実装
# $myFormData = FormData->new(100000);
#
# # POST のみを受け付ける場合
# $myFormData->setGET('false');
# $myFormData->setPOST('true');
# 
# # フォームからのデータの受信 (データがオブジェクトに取りこまれる)
# $myFormData->receive();
#
# # ファイル名とデータ値のハッシュの参照を取得
# $filenames = $myFormData->getFileNames();
# $values = $myFormData->getValues();
#
# # 取得したファイル名とデータ値を取り出す
# if($$values{'page'} eq 'Upload') {
#   $username = $$values{'username'};
#   $filename = $$filenames{'save'};
#   $filedata = $$values{'save'};
#   # ファイルの書きこみ．バイナリモードで書きこまないとだめよん．
#   open(BWRITE, "> $filename");
#   binmode BWRITE;
#   print BWRITE $filedata;
#   close(BWRITE);
# }

# ☆ちょっと解説☆
#
# $values (= $myFormData->getValues()): 
# $filenames (= $myFormData->getFileNames()): 
#
# $values はフォームから送られてきたデータが格納されるハッシュへの参照を
# 表しますす．ハッシュのキーとして，フォームで NAME= で指定した単語を
# 使用します．
#
# 例えば，呼び出し元のフォームの一部が，
#
# <INPUT TYPE="text" NAME="username" SIZE=10>
#
# ならば，フォームに入力されたテキストを 'username' というキーで取得
# 出来ます．
#
# $username = $$values{'username'};     <- $username の中にテキストが入る
#
# 他の(TYPE="text"以外の)フォームに関してもほぼ同様です．TYPE="hidden" の場合は
# VALUE= で指定した値が入ります．
#
# TYPE="FILE" の場合，例えば
#
# <INPUT TYPE="FILE" NAME="save" SIZE=48>
#
# のような場合は，$filenames が指し示すハッシュにファイル名が格納されます．
# また，$values が指し示すハッシュにはファイルの中身が入ります．
#
# $filename = $$filenames{'save'};
# $filedata = $$values{'save'};     # バイナリデータが入るので処理には注意せよ

# ☆補足☆
#
# GET もサポートされており，
#
# <a href="upload.cgi?page=upload&sort=normal">アップロードのページへ</a>
#
# などとすれば，上記の例と同様の手順で，
#
# if($$values{'page'} eq 'upload' && $$values{'sort'} eq 'normal') { ... }
#
# のような使い方が可能となります．GET の場合は $filenames は意味を持ちません．

# 注意:用途の関係上，複数のインスタンスを生成することは無意味です．
#      複数のインスタンスを生成したとしても，全ての変数はクラス変数として
#      共有されてしまうので，誤動作の原因となります．また，receive() メソッドは
#      【一度だけ】呼び出します．
#
#      receive() メソッドを呼び出すと STDIN は空になってしまいます．つまり，
#      このクラスを利用する場合は全てのデータをこのクラス経由で取り出す必要が
#      あります．

# --------------------------------------------------
# クラス変数定義
# --------------------------------------------------
my ($max_datasize, $rec_data, $is_ok, $post_ok, $get_ok, $err_mes, $c_length);
my (%filenames, %values);

# ============================================================
# コンストラクタ
# ============================================================
# IN : $max_datasize = 取得するデータサイズの上限(byte 単位)
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  shift;
  $max_datasize = shift;
  $is_ok = 'false';
  $post_ok = 'false';
  $get_ok = 'false';
  return($self);
}

# ============================================================
# setPOST 関数
# ============================================================
# POST されたデータを受け取るかどうかを設定する．
#
# IN : $post_ok = 受け取るなら 'true' 受け取らないなら 'false'
sub setPOST {
  shift;
  $post_ok = shift;
}

# ============================================================
# setGET 関数
# ============================================================
# GET されたデータ を受け取るかどうかを設定する．
#
# IN : $get_ok = 受け取るなら 'true' 受け取らないなら 'false'
sub setGET {
  shift;
  $get_ok = shift;
}

# ============================================================
# isOK 関数
# ============================================================
# データの受信状況を取得する．
#
# OUT: $is_ok = 受信出来たなら 'true' 受信に失敗したなら 'false'
sub isOK {
  return($is_ok);
}

# ============================================================
# getErrMes 関数
# ============================================================
# エラーメッセージを取得する．
#
# OUT: $err_msg = エラーメッセージ(エラーがない時はnull)
sub getErrMes {
  return($err_mes);
}

# ============================================================
# getContentLength 関数
# ============================================================
# 受信データのサイズ(ヘッダ等も含めたサイズ:byte)を取得する．
#
# OUT: $c_length = 受信データのサイズ(byte)
sub getContentLength {
  return($c_length);
}

# ============================================================
# getRecData 関数
# ============================================================
# 受信データ(生データ)への参照を取得する．
#
# OUT: \$rec_data = 受信データへの参照
sub getRecData {
  return(\$rec_data);
}

# ============================================================
# getQueryString 関数 2000.09.10
# ============================================================
# QueryString(生データ，デコード済み) を取得する．
#
# OUT: QueryString
sub getQueryString {
  my $STR;
  $STR = $ENV{'QUERY_STRING'};
  # 多バイトコードのデコード
  $STR =~ tr/+/ /;
  $STR =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C",hex($1))/eg;
  return($STR);
}

# ============================================================
# getFileNames 関数
# ============================================================
# ファイル名が格納されたハッシュへの参照を取得します．
#
# OUT: \%filenames = ハッシュへの参照
sub getFileNames {
  return(\%filenames);
}

# ============================================================
# getValues 関数
# ============================================================
# データが格納されたハッシュへの参照を取得します．
#
# OUT: \%values = ハッシュへの参照
sub getValues {
  return(\%values);
}

# ============================================================
# receive 関数
# ============================================================
# フォームからのデータを受け取ります．
sub receive {
  # ローカル変数
  my $STR;
  # バイナリモードで受信
  binmode STDIN;
  # データサイズの取得
  $c_length = $ENV{'CONTENT_LENGTH'};
  # データサイズが指定より大きい場合は受信不可能
  if($max_datasize && $c_length > $max_datasize) {
    $is_ok = 'false';
    $err_mes = "error in FormData::receive: too much data : $c_length byte (MAX: $max_datasize byte)";
    return();
  }
  # POST/GET 両方が許可されていない場合はエラー
  if($post_ok ne 'true' && $get_ok ne 'true') {
    $is_ok = 'false';
    $err_mes = "error in FormData::receive: POST or/and GET option must be set";
    return();
  }
  # 受信を OK 状態に

  $is_ok = 'true';
  # POST の場合
  if($post_ok eq 'true' && $ENV{'REQUEST_METHOD'} eq 'POST') {
    read(STDIN, $rec_data, $c_length);
    # 標準入力の読みこみ位置を初期化する
    # FIXME: 標準出力のハンドルはいじれない! この処理は無意味．．．
    seek STDIN, 0, 0;
    # マルチパート形式の場合
    if($ENV{'CONTENT_TYPE'} =~ /multipart\/form-data/) {
      $err_mes = _decode();
      if($err_mes) {
        $is_ok = 'false';
        return();
      }
    } else {
      $STR = $rec_data;
    }
  }
  elsif($get_ok eq 'true' && $ENV{'REQUEST_METHOD'} eq 'GET') {
    # edited 2000.09.10
    $STR = getQueryString();
  } else {
    $is_ok = 'false';
    $err_mes = "error in FormData::receive: POST or GET is requested, but not permitted";
    return();
  }    

  # マルチパート形式以外のデータの処理
  my @params = split(/&/, $STR);
  foreach (@params) {
    my($name, $value) = split(/=/, $_);
    $values{$name} = $value;
  }
  return();
}

# ============================================================
# _decode 関数 (private)
# ============================================================
# この関数は，ウェブページのフォームからマルチパート形式で
# POST されたデータをデコードする関数です．
#
# OUT: $err = エラーメッセージ(正常時はnull)
sub _decode {
  # 定数(通常は書き換えてはいけません)
  
  # 空白行最大行数(無限ループを回避するため)
  my $max_space_lines = 10; # 数値は適当だが，経験上たぶん大丈夫だと思う．
  
  # ローカル変数宣言
  my(@buffer, $header, $name, $boundary, $i);
  
  # マルチパート形式のデータはこんな感じ↓
  #        -----------------------AaB03x
  #         Content-type: multipart/form-data, boundary=AaB03x
  #
  #        -----------------------AaB03x
  #        content-disposition: form-data; name="field1"
  #
  #        Joe Blow
  #        -----------------------AaB03x
  #        content-disposition: form-data; name="pics"; filename="file1.txt"
  #        Content-Type: text/plain
  #
  #         ... contents of file1.txt ...
  #        -----------------------AaB03x--
  #
  # 注: RFC1867 文書によれば，各行は \r\n で改行されているらしい．
  #     でも，サーバによって多少変わる時が．．．もちろんサーバ側が悪いんだけど．
  
  # マルチパート境界線の取得
  $ENV{'CONTENT_TYPE'} =~ /boundary=\"?([^\";,]+)\"?/;
  $boundary = $1;
  # ヘッダからの取得に失敗した場合は，データから取得する
  if(!$boundary) {
    $rec_data =~ /^--(.*?)\r?\n/;
    $boundary = $1;
  }

  # 境界線が見つからない場合は異常終了
  if(!$boundary) {
    return("error in FormData::_decode: boundary not found (invalid input data)");
  }

  # 受信データの分割
  @buffer = split(/\r?\n--$boundary--\r?\n|\r?\n?--$boundary\r?\n/, $rec_data);

  # データの抽出
  for ($i=0; $i<=$#buffer; $i++) {
    # データが空でない場合
    if(length($buffer[$i])>0) {
      # ヘッダの抽出およびヘッダを含む行の削除
      if($buffer[$i] =~ /Content-Disposition: (.*)/) {
        $header = $1;
        $buffer[$i] =~ s/.*\r?\n//;
      } else {
        return("error in FormData::_decode: header not found (invalid input data)");
      }
      
      # 空白行まで続く他のヘッダを削除
      my $l = 0;
      while(!($buffer[$i] =~ /^\s*\r?\n/)) {
        $buffer[$i] =~ s/.*\r?\n//;
        if(++$l >= $max_space_lines) {
          return("error in FormData::_decode: data format error (invalid input data)");
        }
      }
      
      # 空白行を削除
      $buffer[$i] =~ s/.*\r?\n//;
      
      # name を取得
      $header =~ /name=\"(.*?)\"/;
      $name = $1;
      # filename を取得(filename がない場合は name が入る)
      $header =~ /filename=\"(.*?)\"/;
      $filenames{$name} = $1;
      # value の取得
      $values{$name} = $buffer[$i];
    }
  }
  return ();
}


# ============================================================
# フォームから送られたデータを扱うための【クラス】
# FormData2 v0.5 : FormData の代替クラス．メモリ使用量大幅減．
# ============================================================
package FormData2;
# ------------------------------------------------------------
# ☆FormData との違い
# ------------------------------------------------------------
# FormData:  送信されたデータを全て変数に格納します．
#            処理は高速ですが，送信されるデータによっては
#            メモリを膨大に消費してしまいます．従って，
#            使用が推奨されません．
#
# FormData2: 送信されたデータがファイルの場合に限り，一時
#            ディレクトリにファイルを格納します．それ以外の
#            データは変数に格納されます．FormDataよりも処理は
#            若干遅いはずですが，送信されるデータ量が増えても
#            メモリ消費量が増えないのでサーバにかかる負荷を
#            抑えることが出来ます．
#
# ------------------------------------------------------------
# ☆使用法
# ------------------------------------------------------------
# 以下のように，フォーム等から Perl の CGI プログラムを呼び出します．
#
#  <FORM ENCTYPE="multipart/form-data" ACTION="upload.cgi" METHOD=POST>
#    送信ファイル名: <INPUT TYPE="FILE" NAME="save" SIZE=48> <BR>
#    ユーザ名: <INPUT TYPE="text" NAME="username" SIZE=10> <BR>
#    <INPUT TYPE="SUBMIT" VALUE="Upload">
#  </FORM>
#
# Perl の CGI プログラムの中で以下のように使用し，送られてくるデータを
# 取り出します．
#
#  [upload.cgi]
#
#  # オブジェクトの実装
#  $myFormData = FormData2->new();
#
#  # 受信データサイズの上限(byte)を設定: Optional, DEF=4096
#  $myFormData->setMaxDataSize(4096);
#
#  # 受信バッファサイズ(byte)を設定: Optional, DEF=4096
#  $myFormData->setBufferSize(4096);
#
#  # 一時ディレクトリ(707)を設定(ディレクトリは予め作成しておく)
#  $myFormData->setTmpDir("upload_tmp_dir");
#
#  # POST のみを受け付ける場合
#  $myFormData->setGET('false');
#  $myFormData->setPOST('true');
# 
#  # フォームからのデータの受信 (データがオブジェクトに取りこまれる)
#  $err_message = $myFormData->receive();
#
#  # データが格納されたハッシュへの"参照"を取得
#  $values = $myFormData->getValues();
#
#  # 送信されたファイルやデータを取り出す
#  if($$values{'page'} eq 'Upload') {
#    $username = $$values{'username'};
#    ($filename, $filepath) = split(/:/, $$values{'save'});
#    rename($filepath, "upload/$filename");
#  }

# ☆ちょっと解説☆
#
# 「$values (= $myFormData->getValues()) に格納されるデータについて」
#
# $values はフォームから送られてきたデータが格納されるハッシュへの参照を
# 表しますす．ハッシュのキーとして，フォームで NAME= で指定した単語を
# 使用します．
#
# 例えば，呼び出し元のフォームの一部が，
#
#  <INPUT TYPE="text" NAME="username" SIZE=10>
#
# ならば，"フォームに入力されたテキスト"を 'username' というキーで取得
# 出来ます．
#
#  $username = $$values{'username'};     <- $username の中にテキストが入る
#
# 他の(TYPE="text"以外の)フォームに関してもほぼ同様です．TYPE="hidden" の場合は
# VALUE= で指定した値が入ります．
#
# -------------------
#
# TYPE="FILE" の場合，例えば
#
#  <INPUT TYPE="FILE" NAME="save" SIZE=48>
#
# のような場合は，ハッシュにはファイル名と一時ファイルのパスが格納されます．
# それは，"ファイル名:一時ファイルのパス"という形式になっています．例えば
# 以下のようになっています．
#
#  test.txt:upload_tmp_dir/test98135966293.tmp    <- これが $$values{'save'} の値
#
# 従って，以下のようにファイル名と一時ファイルのパスを取り出します
#
# ($filename, $filepath) = split(/:/, $$values{'save'});
#
# あとは，一時ファイルを移動し，取得したファイル名または任意のファイル名に変更します．
#
#  rename($filepath, "upload/$filename");

# ☆補足☆
#
# GET もサポートされており，
#
# <a href="upload.cgi?page=upload&sort=normal">アップロードのページへ</a>
#
# などとすれば，上記の例と同様の手順で，
#
# if($$values{'page'} eq 'upload' && $$values{'sort'} eq 'normal') { ... }
#
# のような使い方が可能となります．

# 注意:用途の関係上，複数のインスタンスを生成することは無意味です．
#      複数のインスタンスを生成したとしても，全ての変数はクラス変数として
#      共有されてしまうので，誤動作の原因となります．また，receive() メソッドは
#      【一度だけ】呼び出します．
#
#      receive() メソッドを呼び出すと STDIN は空になってしまいます．つまり，
#      このクラスを利用する場合は全てのデータをこのクラス経由で取り出す必要が
#      あります．

# --------------------------------------------------
# クラス変数定義
# --------------------------------------------------
my ($buf_size, $tmp_dir);

# ============================================================
# コンストラクタ
# ============================================================
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  $max_datasize = 4096;
  $buf_size = 4096;
  $tmp_dir = '.';
  $post_ok = 'false';
  $get_ok = 'false';
  return($self);
}

# ============================================================
# setMaxDataSize 関数
# ============================================================
# POST によって送信されるデータサイズの上限を設定する．(DEF:4096)
#
# IN : $max_datasize = データサイズの上限(byte 単位)
sub setMaxDataSize {
  shift;
  $max_datasize = shift;
}

# ============================================================
# setBufferSize 関数
# ============================================================
# データ受信バッファのサイズを設定する．(DEF:4096)
#
# IN : $buf_size = データ受信バッファのサイズ(byte 単位)
sub setBufferSize {
  shift;
  $buf_size = shift;
}

# ============================================================
# setTmpDir 関数
# ============================================================
# ファイル格納用一時ディレクトリを設定する．(DEF:'.')
# <INPUT TYPE="FILE" ...> で送信されたファイルはこの
# ディレクトリに格納される．
#
# IN : $tmp_dir = ファイル格納用一時ディレクトリ
sub setTmpDir {
  shift;
  $tmp_dir = shift;
}

# ============================================================
# setPOST 関数
# ============================================================
# POST されたデータを受け取るかどうかを設定する．(DEF:'false')
#
# IN : $post_ok = 受け取るなら 'true' 受け取らないなら 'false'
sub setPOST {
  shift;
  $post_ok = shift;
}

# ============================================================
# setGET 関数
# ============================================================
# GET されたデータ を受け取るかどうかを設定する．(DEF:'false')
#
# IN : $get_ok = 受け取るなら 'true' 受け取らないなら 'false'
sub setGET {
  shift;
  $get_ok = shift;
}

# ============================================================
# getContentLength 関数
# ============================================================
# 受信データのサイズ(ヘッダ等も含めたサイズ:byte)を取得する．
#
# OUT: $c_length = 受信データのサイズ(byte)
sub getContentLength {
  return($c_length);
}

# ============================================================
# getValues 関数
# ============================================================
# データが格納されたハッシュへの参照を取得する．
#
# 多くの場合，このハッシュにはフォームに入力されたデータが格納される．
# <INPUT TYPE="FILE" ...> のフォームからファイルが送信された場合には，
# そのファイル名(パスを含む)が格納される．ファイル自体は一時格納用
# ディレクトリに保存される．
#
# OUT: \%values = ハッシュへの参照
sub getValues {
  return(\%values);
}

# ============================================================
# receive 関数
# ============================================================
# OUT: エラーメッセージ(正常終了時はnull)
# フォームからのデータを受け取ります．
sub receive {
  # ローカル変数
  my $STR;
  # データサイズの取得
  $c_length = $ENV{'CONTENT_LENGTH'};
  # データサイズが指定より大きい場合は受信不可能
  if($max_datasize && $c_length > $max_datasize) {
    return("error in FormData2::receive: too much data : $c_length byte (MAX: $max_datasize byte)");
  }
  # POST/GET 両方が許可されていない場合はエラー
  if($post_ok ne 'true' && $get_ok ne 'true') {
    return("error in FormData2::receive: POST or/and GET option must be set");
  }

  # POST の場合
  if($post_ok eq 'true' && $ENV{'REQUEST_METHOD'} eq 'POST') {
    # マルチパート形式の場合
    if($ENV{'CONTENT_TYPE'} =~ /multipart\/form-data/) {
      my $err_mes = _decode();
      if($err_mes) {
        return($err_mes);
      }
    } else {
      read(STDIN, $STR, $c_length);
    }
  }
  elsif($get_ok eq 'true' && $ENV{'REQUEST_METHOD'} eq 'GET') {
    $STR = _getQueryString();
  } else {
    return("error in FormData2::receive: POST or GET is requested, but not permitted");
  }    

  # マルチパート形式以外のデータの処理
  my @params = split(/&/, $STR);
  foreach (@params) {
    my($name, $value) = split(/=/, $_);
    if(!$values{$name}) {
      $values{$name} = $value;
    } else {
      return("error in FormData2::receive: $name=... is already defined");
    }
  }
  return();
}

# ============================================================
# _getQueryString 関数 2000.09.10
# ============================================================
# QueryString(生データ，デコード済み) を取得する．
#
# OUT: QueryString
sub _getQueryString {
  my $STR = $ENV{'QUERY_STRING'};
  # 多バイトコードのデコード
  $STR =~ tr/+/ /;
  $STR =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C",hex($1))/eg;
  return($STR);
}

# ============================================================
# _decode 関数 (private)
# ============================================================
# この関数は，ウェブページのフォームからマルチパート形式で
# POST されたデータをデコードする関数です．
#
# OUT: $err = エラーメッセージ(正常時はnull)
sub _decode {
  # マルチパート形式のデータはこんな感じ↓
  #        -----------------------AaB03x
  #         Content-type: multipart/form-data, boundary=AaB03x
  #
  #        -----------------------AaB03x
  #        content-disposition: form-data; name="field1"
  #
  #        Joe Blow
  #        -----------------------AaB03x
  #        content-disposition: form-data; name="pics"; filename="file1.txt"
  #        Content-Type: text/plain
  #
  #         ... contents of file1.txt ...
  #        -----------------------AaB03x--
  #
  # 注: RFC1867 文書によれば，各行は \r\n で改行されているらしい．
  #     でも，環境によって多少変わる時が．．．RFC文書にしたがってよぉ? (T-T)
  
  # ローカル変数宣言
  my($buf1, $buf2, $boundary, $rcode);
  
  # バイナリモードで受信
  binmode STDIN;

  # データの一行目(境界線)を読み込む
  $buf1 = <STDIN>;
    
  # 改行コードの解析(\r\nとは限らないので…RFCに従ってくれ?)
  if($buf1 =~ /(\r?\n|\r)/) {
    $rcode = $1;
  } else {
    return("error in FormData2::_decode: unknown new line code (invalid input data)");
  }
  
  # マルチパート境界線の取得
  $ENV{'CONTENT_TYPE'} =~ /boundary=\"?([^\";,]+)\"?/i;
  $boundary = $1;
  # ヘッダからの取得に失敗した場合は，データから取得する
  if(!$boundary) {
    $buf1 =~ /^--(.*?)$rcode/;
    $boundary = $1;
  }
  # 境界線が見つからない場合は異常終了
  if(!$boundary) {
    return("error in FormData2::_decode: boundary not found (invalid input data)");
  }
  # バッファ1にデータを読み込む
  read(STDIN, $buf1, $buf_size);

  # multipart 部解析
  while($buf1) {
    my($name, $filename, $filepath, $binded_buf, $bindex);
    # Content-Disposition: の行から name の抽出
    if($buf1 =~ /^Content-Disposition:.*;\s*name=\"?([^\";,]+)\"?.*$rcode/i) {
      $name = $1;
    } else {
      return("error in FormData2::_decode: header not found (invalid input data)");
    }
    # Content-Disposition: の行から filename の抽出(あれば)
    if($buf1 =~ /^Content-Disposition:.*;\s*filename=\"?([^\";,]+)\"?.*$rcode/i) {
      $filename = $1;
      # ファイル名からディレクトリ名などの余計な成分を切り取る
      $filename =~ s/^.*\\|^.*\///;
      # 一時保存用ファイル名の決定
      $filepath = $filename;
      $filepath =~ s/\./\_/g;
      $filepath = "$tmp_dir/$filepath" . "_" . time . substr(rand, 2, 9) . '.tmp';
    }

    # 空白行まで続く他のヘッダを削除(他のヘッダは無視)
    until ($buf1 =~ /^\s*$rcode/) {
      if(!$buf1) {
        return("error in FormData2::_decode: blank line not found (invalid input data)");
      }
      $buf1 =~ s/^.*$rcode//;
    }
    $buf1 =~ s/^\s*$rcode//;

    my ($value, $flg);
    # 送信データがファイルの場合は，変数に値を入れるのではなく一時ファイルを作成する
    if($filename) {
      open(BWRITE, "> $filepath") || return("error in FormData2::_decode: couldn't write upload_tmp_file");
      binmode BWRITE;
    }
    while($buf1 && !$flg) {
      # バッファ2にデータを読み込む
      read(STDIN, $buf2, $buf_size);
      # FIXME?:連結バッファの作成(処理上必要だが，処理速度の低下の要因になりそう)
      $binded_buf = $buf1 . $buf2;
      # ボーダ行を探す
      $bindex = index($binded_buf, "--$boundary");
      if($bindex < 0) {
        # ボーダが見つからない場合
        if($filename) {
          print BWRITE $buf1;
        } else {
          $value .= $buf1;
        }
      } else {
        my ($rindex, $tmp);
        # ボーダが見つかった場合
        $tmp = substr($binded_buf, 0, $bindex, '');
        $rindex = index($tmp, $rcode, length($tmp)-length($rcode));
        $tmp = substr($tmp, 0, $rindex);
        if($filename) {
          print BWRITE $tmp;
          $value = "$filename:$filepath";
        } else {
          $value .= $tmp;
        }
        # ボーダの削除
        $binded_buf =~ s/^--$boundary.*$rcode//;
        # ボーダ以下のデータだけを残し，新たに読み込んだデータに繋げる
        # これをやらないとヘッダがぶち切れる可能性が生じる
        read(STDIN, $buf2, $buf_size);
        $buf2 = $binded_buf . $buf2;
        # マルチパートの一部分が終了
        $flg = 'true';
      }
      $buf1 = $buf2;
    }
    if($filename) {
      close(BWRITE);
    }
    if(!$values{$name}) {
      $values{$name} = $value;
    } else {
      return("error in FormData2::_decode: $name=... is already defined");
    }
  }
  return();
}


# ============================================================
# html 出力を簡単に実現する【クラス】
# EzHTML v0.32
# ============================================================
package EzHTML;
# ------------------------------------------------------------
# ☆使用法
# ------------------------------------------------------------
# Perl の CGI プログラムの中で以下のように使用します．
#
# # オブジェクトの実装
# $myEzHTML = EzHTML->new("bbs.cgi", "掲示板", "#D8FFD8", "back.gif", "#E8E8FF",
#                         "http://hoge.com/", "ほげのページ",
#                         "http://homepage1.nifty.ne.jp/~heppoko/", "bbs v0.1");
#
# # エラーログを記録するファイルの指定
# $myEzHTML->setErrlogFilename("data/errlog.log");
# 
# # エラーページの表示
# $myEzHTML->error("ファイルが見つかりません", 3);
#
# # ページの表示
# $myEzHTML->printHeader();
# print "<center>以下の質問に答えてね!</center>";
# ...
# print "<a href="#top">ページの一番上に戻る</a>";
# print "<hr>";
# $myEzHTML->printFooter();

# ============================================================
# コンストラクタ
# ============================================================
# IN : ($top_page_url, $title, $title_bgcolor, $bg_filename, $bgcolor,
#       $admin_page_url, $admin_page_title, $support_page_url, $softname,
#       $title_color, $title_bg_filename)
#       = (トップページのURL, タイトル文字, タイトル(の背景)の色, 背景画像, 背景色,
#          管理者ページのURL, 管理者ページのタイトル, サポートページのURL, ソフト名,
#          タイトルの色，タイトルの背景画像)
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  shift;
  ($self->{'top_page_url'}, $self->{'title'}, $self->{'title_bgcolor'}, $self->{'bg_filename'},
   $self->{'bgcolor'}, $self->{'admin_page_url'}, $self->{'admin_page_title'},
   $self->{'support_page_url'}, $self->{'softname'},$self->{'title_color'},
   $self->{'title_bg_filename'}) = @_;
  return($self);
}

# ============================================================
# setErrlogFilename 関数
# ============================================================
# エラーログを記録するファイル名を指定します．
# 指定がない(この関数が呼ばれない)場合はログを記録しません．
# IN : $filename ログファイル名
sub setErrlogFilename {
  my $self = shift;
  $self->{'errlog_filename'} = shift;
}

# ============================================================
# setRetPageURL 関数
# ============================================================
# 画面左下のリンクを押した時に戻るページを指定します．
# 指定がない(この関数が呼ばれない)場合はトップページに戻ります．
#
# IN : $url URL
sub setRetPageURL {
  my $self = shift;
  $self->{'ret_page_url'} = shift;
}


# ============================================================
# printHeader 関数
# ============================================================
# ヘッダ部分を出力します．
sub printHeader {
  my $self = shift;
print <<EOF;
Content-type: text/html; charset=UTF-8\n
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
  <title>$self->{'title'}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <!--検索ロボットに対する設定 -->
<!--
  <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
-->
  <!-- フォントなどの基本設定 -->
<!--
  <link rel="stylesheet" type="text/css" href="../../style/base.css" media="all" />
-->
  <!-- レイアウトを規定 -->
<!--
  <link rel="stylesheet" type="text/css" href="../../css/table.css" title="gavo internal style" media="all"/>
-->
  <link rel="stylesheet" href="../../css/uploader.css" type="text/css" />
</head>
EOF
  print "<body>\n<div id=\"WRAPPER\" class=\"fullscreen\">\n"
}

# ============================================================
# printFooter 関数
# ============================================================
# フッタ部分を出力します．
sub printFooter {
  my $self = shift;
  print <<EOF;
  <div id="FOOTER">
      <hr />
<!--
      <address>
      広瀬・峯松研究室 / Hirose &amp; Minematsu Lab.<br />
      COPYRIGHT &copy; 2006-2008 Hirose &amp; Minematsu Laboratory All Rights Reserved.<br />
      <a href="mailto:webmaster\@gavo.t.u-tokyo.ac.jp" title="mail to webmaster">webmaster\@gavo.t.u-tokyo.ac.jp</a>
      </address>
-->
      </div><!--id="FOOTER"-->
  </div><!--id="WRAPPER"-->
  </body>
</html>
EOF
}

# ============================================================
# printSimpleHeader 関数
# ============================================================
# タイトル等を一切表示しないヘッダ部分を出力します．
sub printSimpleHeader {
  my $self = shift;
print <<EOF;
Content-type: text/html\n\n
<HTML>
  <HEAD>
    <TITLE>$self->{'title'}</TITLE>
    <meta http-equiv="Content-Type" content="text/html; charset=x-euc-jp">
    <link rel="stylesheet" href="upload.css" type="text/css">
  </HEAD>
EOF
  if(-e $self->{'bg_filename'}) {
    print "  <BODY class=\"upload\" background=\"$self->{'bg_filename'}\" BGCOLOR=\"$self->{'bgcolor'}\" link=\"#ffff55\" vlink=\"#ffff55\" alink=\"#ffff55\">\n";
  } else {
    print "  <BODY class=\"upload\" BGCOLOR=\"$self->{'bgcolor'}\" link=\"#ffff55\" vlink=\"#ffff55\" alink=\"#ffff55\">\n";
  }
}

# ============================================================
# printSimpleFooter 関数
# ============================================================
# タイトル等を一切表示しないフッタ部分を出力します．
sub printSimpleFooter {
  my $self = shift;
print <<EOF;
  </body>
</html>
EOF
}

# ============================================================
# printMenuFrame 関数
# ============================================================
# メニューを持ったフレームを表示します．
# IN : $p, $con, $main = メニューの割合またはドット数, メニューページの URL, メインページの URL
sub printMenuFrame {
  my $self = shift;
  my ($p, $con, $main) = @_;
print <<EOF;
Content-type: text/html\n\n
<HTML>
  <HEAD>
    <TITLE>$self->{'title'}</TITLE>
    <meta http-equiv="Content-Type" content="text/html; charset=x-euc-jp">
  </HEAD>
  <frameset cols="$p,*">
    <frame name="contents" src="$con">
    <frame name="main" src="$main">
    <noframes>
      <body>
        <p>このページにはフレームが使用されていますが，お使いのブラウザではサポートされていません．下のリンクをクリックしてください．</p>
        <P align=center>
          <A HREF="$main">フレームの使われていないページに行きます</A>
        </P>
      </body>
    </noframes>
  </frameset>
</html>
EOF
}

# ============================================================
# error 関数
# ============================================================
# エラー画面
# IN : ($mes, $ret_url, $ret_time) =
#                 (メッセージ，[エラー表示時間])
sub error {
  my $self = shift;
  $self->_ecm_sub('エラー', @_);
}

# ============================================================
# caution 関数
# ============================================================
# 警告画面
# IN : ($mes, $ret_url, $ret_time) =
#                 (メッセージ，[エラー表示時間])
sub caution {
  my $self = shift;
  $self->_ecm_sub('警告', @_);
}

# ============================================================
# message 関数
# ============================================================
# メッセージ画面
# IN : ($mes, $ret_url, $ret_time) =
#                 (メッセージ，[エラー表示時間])
sub message {
  my $self = shift;
  $self->_ecm_sub('メッセージ', @_);
}

# ============================================================
# _ecm_sub 関数(プライベート)
# ============================================================
# エラー/警告/メッセージ表示用サブルーチン
# IN : ($mes, $ret_url, $ret_time) =
#                 (メッセージ，[エラー表示時間])
sub _ecm_sub {
  my $self = shift;
  # エラーログを記録する
  if($self->{'errlog_filename'}) {
    accesslog::record("$self->{'errlog_filename'}", "$_[1]");
  }
  $self->printHeader();
  if($#_>=2) {
    if($self->{'ret_page_url'}) {
      print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$_[2]; URL=$self->{'ret_page_url'}\">\n";
    } else {
      print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$_[2]; URL=$self->{'top_page_url'}\">\n";
    }
  }
  print "<center><font color=\"red\"><h3>$_[0]</h3></font></center><p>$_[1]</p>";
  $self->printFooter();
  exit(0);
}


# ============================================================
# パスワードを管理する【クラス】．GUI 表示をサポート．
# Passwd v0.1
# ============================================================
package Passwd;
# ------------------------------------------------------------
# ☆使用法
# ------------------------------------------------------------
# Perl の CGI プログラムの中で以下のように使用します．
#
# # オブジェクトの実装
# $myPasswd = Passwd->new("data/.admin_passwd",
#                          'upload.cgi', '管理者', \$myEzHTML, \$myFormData);
#
# # パスワードを読み込む，必要ならば GUI により設定する．
# $myPasswd->loadPasswd();
# 
# # クッキーを利用したパスワードの認証を行う．
# $myPasswd->requestPasswd();

# ============================================================
# コンストラクタ
# ============================================================
# IN : ($passwd_filename, $prog_filename, $name, \$pEzHTML, \$pFormData)
#       = (パスワードファイル名, 呼び出し元の CGI プログラムの場所,
#          パスワード設定対象者名('管理者','ユーザ'等),
#          EzHTML オブジェクトへの参照, 
#          FormData オブジェクトへの参照)
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  shift;
  ($self->{'passwd_filename'}, $self->{'prog_filename'}, $self->{'name'},
   $self->{'pEzHTML'}, $self->{'pFormData'}) = @_;
  $self->{'values'} = ${$self->{'pFormData'}}->getValues();
  return($self);
}

# ============================================================
# isCorrect 関数
# ============================================================
# パスワードが一致するかどうかを調べる．
#
# IN : パスワード(生)
# OUT: 一致 'true' 不一致 'false'
sub isCorrect {
  my $self = shift;
  my $passwd = shift;
  my $cpasswd = crypt($passwd, 'passwd');
  if($self->{'crypt_passwd'} eq $cpasswd) {
    return('true');
  } else {
    return('false');
  }
}

# ============================================================
# loadPasswd 関数
# ============================================================
# パスワードファイルの存在を調べ，ファイルが存在しないならば
# パスワードの設定を要求する．存在するならパスワードを読み込む．
#
sub loadPasswd {
  my $self = shift;
  # パスワードの取得
  if(-e "$self->{'passwd_filename'}") {
    $self->{'crypt_passwd'} = $self->_get_crypt_passwd();
  }
  # passwd が送られた場合はパスワードの設定(完了)ページへ
  elsif(${$self->{'values'}}{'page_Passwd_Obj'} eq 'passwd_Passwd_Obj') {
    $self->_passwd_set_page();
  }
  # それ以外の場合はパスワードの設定を要求するページへ
  else {
    $self->_passwd_request_page();
  }
}

# ============================================================
# requestPasswd 関数
# ============================================================
# パスワードの認証を行う．パスワードが正しい場合には
# クッキーを設定し，以後認証を省略する．
#
sub requestPasswd {
  my $self = shift;
  # 認証済みのブラウザの場合は要求を行わない
  my $passwd = cookie::getCookie("Passwd_Obj");
  if($self->isCorrect($passwd) eq 'true') {
    return();
  }
    
  # パスワードファイルが無い場合はエラー
  if(!(-e "$self->{'passwd_filename'}")) {
    ${$self->{'pEzHTML'}}->error("$self->{'name'}のパスワードファイルの読み込みに失敗しました", 3);
  }
  # passwd が送られた場合はパスワードの認証(完了)ページへ
  elsif(${$self->{'values'}}{'page_Passwd_Obj'} eq 'passwd2_Passwd_Obj') {
    $self->_passwd_set_page2();
  }
  # それ以外の場合はパスワードの認証を要求するページへ
  else {
    $self->_passwd_request_page2();
  }
}

# ============================================================
# _get_crypt_passwd 関数
# ============================================================
# 暗号化されたパスワードを取得します．
#
# OUT: 暗号化されたパスワード文字列
sub _get_crypt_passwd {
  my $self = shift;
  # 暗号化されたパスワードの取得
  open(READ, "< $self->{'passwd_filename'}") || ${$self->{'pEzHTML'}}->error("$self->{'name'}のパスワードファイルの読み込みに失敗しました", 3);
  my $cpasswd = <READ>;
  close(READ);
  
  # (通常はありえないが)パスワードが空だとまずいので，適当な文字を入れておく．
  # 本来は暗号化された文字が入るので，適当な文字を入れても問題はない．．．はず．
  if($cpasswd eq '') {
    $cpasswd = 'hogehoge';
  }
  return($cpasswd);
}

# ============================================================
# _passwd_request_page 関数
# ============================================================
# パスワードの設定要求ページを表示します．
sub _passwd_request_page {
  my $self = shift;
  # ページの表示
  ${$self->{'pEzHTML'}}->printHeader();
print <<EOF;
<center><font color=#ff0000><h3>$self->{'name'}のパスワードの設定</h3></font></center>
$self->{'name'}のパスワードを入力してください
<center>
<FORM ACTION="$self->{'prog_filename'}" METHOD=POST>
<INPUT TYPE="hidden" NAME="page_Passwd_Obj" VALUE="passwd_Passwd_Obj">
<table border="0">
<tr>
  <td>パスワード: </td>
  <td><INPUT TYPE="password" NAME="passwd1_Passwd_Obj" SIZE=10 MAXLENGTH="10"></td>
</tr>
<tr>
  <td>確認: </td>
  <td><INPUT TYPE="password" NAME="passwd2_Passwd_Obj" SIZE=10 MAXLENGTH="10"></td>
</tr>
</table>
<INPUT TYPE="SUBMIT" VALUE="OK">
</FORM>
</center>
EOF
${$self->{'pEzHTML'}}->printFooter();
  exit(0);
}

# ============================================================
# _passwd_request_page2 関数
# ============================================================
# パスワードの認証要求ページを表示します．
sub _passwd_request_page2 {
  my $self = shift;
# ページの表示
  ${$self->{'pEzHTML'}}->printHeader();
print <<EOF;
<center><font color=#ff0000><h3>$self->{'name'}のパスワードの認証</h3></font></center>
$self->{'name'}のパスワードを入力してください
<center>
<FORM ACTION="$self->{'prog_filename'}" METHOD=POST>
<INPUT TYPE="hidden" NAME="page_Passwd_Obj" VALUE="passwd2_Passwd_Obj">
<table border="0">
<tr>
  <td>パスワード: </td>
  <td><INPUT TYPE="password" NAME="passwd_Passwd_Obj" SIZE=10 MAXLENGTH="10"></td>
</tr>
</table>
<INPUT TYPE="SUBMIT" VALUE="OK">
</FORM>
</center>
EOF
${$self->{'pEzHTML'}}->printFooter();
  exit(0);
}

# ============================================================
# _passwd_set_page 関数
# ============================================================
# パスワードの設定(完了)ページを表示します．
sub _passwd_set_page {
  my $self = shift;
  my ($passwd1, $passwd2, $cpasswd);

  # パスワードの取得
  $passwd1 = ${$self->{'values'}}{'passwd1_Passwd_Obj'};
  $passwd2 = ${$self->{'values'}}{'passwd2_Passwd_Obj'};
  
  # パスワードの設定
  if($passwd1 eq '' && $passwd2 eq '') {
    # パスワードが空だったら
    ${$self->{'pEzHTML'}}->error("パスワードが入力されていません．もう一度パスワードを入力してください", 3);
  }
  elsif($passwd1 ne $passwd2) {
    # パスワードが一致しない場合
    ${$self->{'pEzHTML'}}->error("パスワードが一致しません．もう一度パスワードを入力してください", 3);
  } else {
    # パスワードの書き込み
    open(WRITE, "> $self->{'passwd_filename'}") || ${$self->{'pEzHTML'}}->error("$self->{'name'}のパスワードファイルの書き込みに失敗しました", 3);
    $cpasswd = crypt($passwd1, 'passwd');
    print WRITE $cpasswd;
    close(WRITE);
    ${$self->{'pEzHTML'}}->caution("$self->{'name'}のパスワードを設定しました．パスワードを忘れないように気を付けて下さい．忘れてしまった場合はパスワードファイル $self->{'passwd_filename'} を削除して再度設定して下さい", 5);
  }
}

# ============================================================
# _passwd_set_page2 関数
# ============================================================
# パスワードの認証(完了)ページを表示します．
sub _passwd_set_page2 {
  my $self = shift;
  my ($passwd);

  # パスワードの取得
  $passwd = ${$self->{'values'}}{'passwd_Passwd_Obj'};

  # パスワードの設定
  if($self->isCorrect($passwd) eq 'true') {
    cookie::setCookie("Passwd_Obj", $passwd, 1);
    ${$self->{'pEzHTML'}}->message("パスワードの認証が正しく行われました．同一のブラウザを使用する限り，以後24時間は認証が省略されます．但し，ブラウザのクッキーの使用を許可しておく必要があります．", 5);
  } else {
    ${$self->{'pEzHTML'}}->error("パスワードが違います", 3);
  }
}


# ============================================================
# クッキーを扱うためのパッケージ
# cookie v0.4
# ============================================================
package cookie;

# ============================================================
# setCookie 関数
# ============================================================
# クッキーを設定します．html を表示する【前】に呼び出してください．
# IN : ($title, $data, $exp)
#        = (識別用タイトル, データ, 有効期限(単位:日))
sub setCookie {
  my ($title, $data, $exp) = @_;
  my ($secg,$ming,$hourg,$mdayg,$mong,$yearg,$wdayg) = gmtime(time + $exp*24*60*60);
  my @mons = ('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
  my @days = ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
  my $dateg = sprintf("%s, %02d\-%s\-%04d %02d:%02d:%02d GMT",
                      $days[$wdayg],$mdayg,$mons[$mong],$yearg+1900,$hourg,$ming,$secg);
  $data =~ s/(.)/join('', '%', sprintf("%lx", ord($1)))/eg;
  $data =~ tr/ /+/;
  print "Set-Cookie: $title=$data; expires=$dateg\n";
}

# ============================================================
# getCookie 関数
# ============================================================
# クッキーを取り出します．
# IN : $title 識別用タイトル
# OUT: $data データ
sub getCookie {
  my $title = $_[0];
  my $cookie = $ENV{'HTTP_COOKIE'};
  my @pairs = split(/;/,$cookie);
  my $str;
  foreach (@pairs) {
    if($_ =~ /$title=(.*)/) {
      $str = $1;
      $str =~ tr/+/ /;
      $str =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C",hex($1))/eg;
      return($str);
    }
  }
}

# ============================================================
# ファイルを扱うためのパッケージ
# file v0.2
# ============================================================
package file;

# ============================================================
# delete 関数
# ============================================================
# ファイルまたはディレクトリをまるごと削除します．
# IN : $name = 削除対象ファイル/ディレクトリ名
# OUT: 正常終了=1, 異常終了=0
sub delete {
    my $name = shift;
    if(-d $name) {
      return _delete_dir($name);
    } else {
      return unlink($name);
    }
}

sub _delete_dir {
  my $dirname = shift;
  my $flg = 1;
  # 2000.09.19
  opendir(DIR,"$dirname");
  my @dirs = readdir(DIR);
  closedir(DIR);
  foreach (@dirs) {
    # カレント・親ディレクトリは削除しない(無限ループ回避)
    if($_ ne '.' && $_ ne '..') {
      my $f = "$dirname/$_";
      if(-d $f) {
        $flg &= _delete_dir($f);
      } else {
        $flg &= unlink($f);
      }
    }
  }
  $flg &= rmdir($dirname);
  return $flg;
}

# ============================================================
# copy 関数
# ============================================================
# ファイルをコピーします．
# IN : ($src, $dest) = (コピー元ファイル名，コピー先ファイル名)
# OUT: 正常終了=1, 異常終了=0
sub copy {
    my ($src, $dest) = @_;
    my (@buf);
    if(-e $dest) { return 0; }
    open(BREAD, "< $src") || return 0;
    binmode BREAD;
    @buf = <BREAD>;
    close(BREAD);
    open(BWRITE, "> $dest") || return 0;
    binmode BWRITE;
    print BREAD @buf;
    close(BWRITE);
    return 1;
}

# ============================================================
# move 関数
# ============================================================
# ファイルまたはディレクトリをまるごと移動します．
# IN : ($src, $dest) = (移動元ファイル/ディレクトリ名，移動先ファイル/ディレクトリ名)
# OUT: 正常終了=1, 異常終了=0
sub move {
    my ($src, $dest) =@_;
    return rename($src, $dest);
}

# ============================================================
# ロックを扱うためのクラス
# lock v0.1
# ============================================================
package Lock;
# ------------------------------------------------------------
# ☆使用法
# ------------------------------------------------------------
# Perl の CGI プログラムの中で以下のように使用します．
#
# # オブジェクトの実装
# $myLock = Lock->new("data/lock", 10);
#
# # ロックをかける
# $myLock->lock();
#
# # ファイルアクセス処理
# ...
#
# # ロックの解除
# $myLock->unlock();
# 

# ============================================================
# コンストラクタ
# ============================================================
# IN : ($lock_dirname, $max_wait_time)
#       = (ロックディレクトリ名 (親ディレクトリは707/777),
#          ロック解除待ち時間の最大値(秒));
# OUT: クラス
sub new {
  my $self = {};
  bless($self);
  shift;
  ($self->{'lock_dirname'}, $self->{'max_wait_time'}) = @_;
  return($self);
}

# ============================================================
# lock 関数
# ============================================================
# ロックをかけます．他のプロセスによって既にロックがかかっている
# 場合は，指定の時間だけロックが解除されるのを待ちます．ロックが
# 解除されない場合はエラーを返します．
#
# OUT: 正常終了=1, 異常終了=0
sub lock {
  my $self = shift;
  my $ts = time;
  # 他のプロセスが存在する場合は待つ
  while (!mkdir($self->{'lock_dirname'}, 0755)) {
    sleep(1); my $te = time;
    if($te - $ts > $self->{'max_wait_time'}) {
      return 0;
    }
  }
  return 1;
}

# ============================================================
# unlock 関数
# ============================================================
# ロックを解除します．
# IN : $name = ロックディレクトリ名 (親ディレクトリは707/777)
# OUT: 正常終了=1, 異常終了=0
sub unlock {
  my $self = shift;
  return rmdir($self->{'lock_dirname'});
}

1;
