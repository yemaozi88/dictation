<?php
/*
 * 2012/10/13
 * class for MySQLi
 *
 * HISTORY
 * 2018/07/14 upgraded from mysql to mysqli.
 * 
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */
 
class c_mysqli
{

    private $database_servername = 'mysql583.phy.lolipop.jp';
    private $database_username = 'LA08824220';
    private $database_password = 'wwe2nl124b';
    private $database_name = 'LA08824220-database';

/*
    private $database_servername = '127.0.0.1';
    private $database_username = 'root';
    private $database_password = '';
    private $database_name = 'dictation';
*/
	
    public function connect()
    {        
        $this->mysqli = new mysqli($this->database_servername, $this->database_username, $this->database_password, $this->database_name);
        if ($this->mysqli->connect_error) {
            error_log($this->mysqli->connect_error);
            exit;
        }
    }

    public function close()
    {
        $this->mysqli->close();
    }
}

?>