<?php
 require "vendor/autoload.php";//this is needed to import the mini library that's saving your butt.
$db = isset($_GET["db"]) ? trim($_GET["db"]) : "";
$insert_type = isset($_GET["type"]) ? trim(strtolower($_GET["type"])) : '';
$file_name = isset($_GET["file"]) ? trim($_GET["file"]) : '';
//header("Content-Type: text/html; charset=ISO-8859-1");//to try to prevent the black diamond character :)
//UK
//setlocale(LC_ALL, 'en_GB');//to try to prevent the black diamond character :)

if(empty($db))
	exit("please put in the db name in the url ah e.g ?db=wert ");

if(empty($file_name))
	exit("please put in a file name in the url naw and make sure its in the same folder as this php file, e.g ?db=".$db."&file=the_file.csv");

if(!file_exists($file_name))
	exit("failed to open file, check if file exists, make sure its in the same folder as this php file.");

if(empty($insert_type) || ($insert_type !== "simple" && $insert_type !== "normalize") )
	exit("please add the type of inserting using the 'type' param,e.g?db=".$db."&file=".$file_name."&type=simple <br/> note that if you want the states to be inserted in the state table separately and linked to the other table (i.e normalization), set type=normalize , else if you just want it to be put all in 1 table, set type=simple");

/** MySQL database name */
define('DB_NAME', $db);

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

 $db = new DBCon();
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
 /** inserting the states */
 function insert_states(){
	 global $dbq,$file_name;
	 //create the table first
	$table_creation = '
	CREATE TABLE IF NOT EXISTS `states` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`state` varchar(200) COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	'; 
	 $dbq->query($table_creation);
	 
	$file = fopen($file_name,'r');

	$current_state = '';//so we can keep changing from time to time	 
	$values = array();//store
	while($line = fgetcsv($file)){
		//fgetcsv() reads one line at a time, so...
		$values[] = $line;//keep storing till you're tired
	}
	//now loop
	$inserted_num = 0;
	$failed_num = 0;
	for($i=0; $i < count($values); $i++){
		$value_to_check = trim(strtolower($values[$i][0]));//we use this to determine if we're in a title row
		//e.g S/N,LGAs,Senatorial Districts,Federal constituencies cause we wont need to insert this as a row into the table
		if($value_to_check === "s/n"){/*this is the title part, so we ignore, since our code is looping the file line by line*/}
		else if($value_to_check != "s/n" && !is_numeric($value_to_check) && !empty($value_to_check)){//this is the line that has the state name, so insert
			$data = array('state'=>'?');
			$bind = array(trim($values[$i][0]));
			if($dbq->add('states',$data,$bind))
				$inserted_num++;
			else
				$failed_num++;
		}
		else{//ignore
			}
	}
	echo "Successful state inserts : ".$inserted_num."<br/> Failed state inserts : ".$failed_num."<br/>";
	if(!empty($dbq->err_msg))
		echo "error message: <strong>".$dbq->err_msg."</strong><br/>";
	echo "<strong>Please if you want to rerun this program, dont forget to clear the table(s), i can't be doing that one for you again, you owe me pizza</strong><br/><br/><br/>";
	fclose($file); 
 }
 
 /**inserting the districts */
 function insert_the_stuff(){
	 global $dbq,$insert_type,$file_name;
	//create the table first
	$state_create = '`state` varchar(200) COLLATE utf8_bin NOT NULL,';//inside the create table, 
	if($insert_type == 'normalize')//since we're normalizing, its gonna be a relationship something, dude, don't bring soffiyya here too, :(
		$state_create = '`state` int(11) NOT NULL,';
	
	$table_creation = '
	CREATE TABLE IF NOT EXISTS `districts` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`s_n` int(11) NOT NULL,
	`lga` varchar(200) COLLATE utf8_bin DEFAULT NULL,
	`senatorial_district` varchar(200) COLLATE utf8_bin DEFAULT NULL,
	`federal_constituency` varchar(200) COLLATE utf8_bin DEFAULT NULL,
	'.$state_create.'
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	'; 
	 $dbq->query($table_creation);
	
	$file = fopen($file_name,'r');
 
	$current_state = '';//so we can keep changing from time to time	 
	$values = array();//store
	while($line = fgetcsv($file)){
		//fgetcsv() reads one line at a time, so...
		$values[] = $line;//keep storing till you're tired
	}
	//now loop
	$inserted_num = 0;
	$failed_num = 0;
	for($i=0; $i < count($values); $i++){
		$value_to_check = trim(strtolower($values[$i][0]));//we use this to determine if we're in a title row
		//e.g S/N,LGAs,Senatorial Districts,Federal constituencies cause we wont need to insert this as a row into the table
		if($value_to_check === "s/n"){/*this is the title part, so we ignore, since our code is looping the file line by line*/}
		else if($value_to_check != "s/n" && !is_numeric($value_to_check) && !empty($value_to_check)){//this is the line that has the state name, so update a variable
			$current_state = $value_to_check;
		}
		else if(!empty($value_to_check)){//normal insert
			//check if the table is to be normalized
			if($insert_type == 'normalize'){//get the id of the state
				$dbq->get('SELECT id from states where LOWER(state) = ?',[$current_state]);
				$current_state_val = $dbq->record[0]['id'];
			}
			else
				$current_state_val = $current_state;
			$data = array('s_n'=>'?','lga'=>'?','senatorial_district'=>'?','federal_constituency'=>'?','state'=>'?');
			$bind =  array(trim($values[$i][0]),iconv("UTF-8", "ISO-8859-1//IGNORE",trim($values[$i][1])),iconv("UTF-8", "ISO-8859-1//IGNORE",trim($values[$i][2])),iconv("UTF-8", "ISO-8859-1//IGNORE",trim($values[$i][3])),trim($current_state_val));
			//var_dump($bind);
			if($dbq->add('districts',$data,$bind))
				$inserted_num++;
			else{
				//var_dump($bind);
				$failed_num++;
			}
		}
	}
	echo "Successful districts inserts : ".$inserted_num."<br/> Failed districts inserts : ".$failed_num."<br/>'";
	if(!empty($dbq->err_msg))
		echo "error message: <strong>".$dbq->err_msg."</strong><br/>";
	echo"<strong>Please if you want to rerun this program, dont forget to clear the table(s), i can't be doing that one for you again, you owe me pizza</strong>";
	fclose($file);
}
 
 //now run
 if($insert_type == 'normalize'){
	 insert_states();
	 insert_the_stuff();
 }
 else{//simple, insert all to one table
	 insert_the_stuff();
 }