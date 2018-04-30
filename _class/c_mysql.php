<?php
/*
 * 2012/10/13
 * class for MySQL
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */
 
class c_mysql
{
    public $var = 'a default value';
    private $database_servername = 'mysql583.phy.lolipop.jp';
    private $database_username = 'LA08824220';
    private $database_password = 'wwe2nl124b';
    private $database_name = 'LA08824220-database';

/*
    private $database_servername = '127.0.0.1';
    private $database_username = 'root';
    private $database_password = 'root';
    private $database_name = 'waschbaerli';
*/
    
    private $connection;
	
	public function connect()
	{
		$this->connection = mysql_connect($this->database_servername, $this->database_username, $this->database_password);
		if (!$this->connection)
		{ 
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db($this->database_name, $this->connection);
	}
	
	public function close()
	{
		mysql_close($this->connection);
	}
}
?>