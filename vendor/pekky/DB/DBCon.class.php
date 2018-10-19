<?php
/*
 *
*/
	//namespace pp\DB;
	/**
	 * DATABASE CONNECTION
	 * 
	 * @author Precious Omonzejele<omonze@peepsipi.com>
	 *
	*/
	 class DBCon{
		protected $con;// mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
		protected $server,$username,$password,$db;
		//protected $sql;
		public $con_err_msg;//connection error stuff.
		protected $db_type = '';//the current database type, if not a correct database type is used, it defaults to mysql
		protected $db_types = array(
			'pdo:mysql' => 'pdo',
			'mysqli' => 'mysqli'
		);//the list of available database types
		//protected $_pdo_attr_emulate_prepares = '';//PDO stuff
		//protected $_pdo_attr_errmode = '';//PDO stuff
		protected $pdo_args = array();
		
		/**
		 * Constructor 
		 * 
		 * @param string $db_type, to know which db type to use, default value is 'mysql'
		*/
		function __construct($db_type = 'pdo'){
			$this->db_type = trim($db_type);
		}
		/**
		 * CREATING A CONNECTION TO THE DATABASE
		 * 
		 * @param string $server the server name
		 * @param string $username, the username
		 * @param string $password, the password
		 * @param string $db, the database name
		 * @return array returns the [con_obj,db_type], the db_type is needed to be passed too.
		 */
		function connect($server,$username,$password,$db,$charset = "utf8mb4",$pdo_args = array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)){
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->db = $db;
			$this->pdo_args = $pdo_args;
			//do a connection based on the type
			switch($this->db_type){
				case $this->db_types['mysqli']:
					$this->con = new mysqli($this->server,$this->username,$this->password,$this->db);
					if($this->is_con_error())
						return false;
				break;
				default :
				try{
				$this->con = new PDO('mysql:host='.$this->server.';dbname='.$this->db.';charset='.$charset, $this->username, $this->password,
				$this->pdo_args);
				}
				catch(PDOException $ex){
					$this->con_err_msg = $ex->getMessage();
					return false;
				}
		
			}
			 return array($this->con,$this->db_type);
		}

		
		/**
		 * Setting connection object :(
		 * 
		 * Not sure of this, but it's my way of getting the query class to be able to get the con object
		 * @param string $server the server name
		 */
		protected function connect_obj($con){
			$this->con = $con;
		}
		/**
		 * CREATING A CONNECTION TO THE DATABASE
		 * Based on the type of database specified in the constructor
		 * 
		 * 
		 * @param string $server the server name
		 * @param string $username, the username
		 * @param string $password, the password
		 * @param string $db, the database name
		*/
		function connect_type($server,$username,$password,$db){
			switch(trim($this->db_type)){
				case 'mysqli':

				break;
			}

		}

		/**
		 * Opens the database connection if it's closed
		 * 
		 * @param object $con the connector object
		 */
		public function open($con){
			if(!$this->con){
				$this->con = $con; 
			}
		}

		/**
		 * Closes the database connection if it's opened
		 */
		public function close(){
			//close according the the type of connection
			switch(trim($this->db_type)){
				case $this->db_types['mysqli']:
					if(!$this->con->ping())
						$this->con->close();
				break;

				default:
					if($this->con){
						$this->con = null; 
					}
			}
		}

		/**
		 * Checks if the connection had an error
		 * 
		 * This is useful when some errors cant really be caught, did it especially
		 * for mysqli bla, checking everytime if mysqli::error is not empty is tiring 
		 * 
		 * @return bool true if there's an error, false otherwise
		 */
		private function is_con_error(){
			switch($this->db_type){
				case $this->db_types['mysqli']:
					if($this->con->connect_error){//error
						$this->con_err_msg = $this->con->error;
						return true;
					}
					else{//no error, clear error
						//$this->clear_error();
						return false;
					}
				break;
				//no default, cause default is pdo

			}
		}

		/**
		 * Gets the connection info of the respective server
		 * 
		 * @return object|false returns returns an object of the con or false otherwise
		 */
		public function get_connection_info(){
			if($this->con)
				return $this->con;
			else
				return false;
		}
	}
	