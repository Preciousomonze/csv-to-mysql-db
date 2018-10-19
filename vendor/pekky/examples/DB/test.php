<?php
  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $password = isset($_GET["password"]) ? hash_password($_GET["password"]) : false;
  
  $db = new DBCon();//can be left empty, since the default is pdo
  
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);//note that -> in php is same as . in c# so its like saying $db.connect()
  
 $dbq = new Query($con);//after initializing a connection, make sure you pass the value into the constructor, like new Query($con);
 $dbq->set_fetch_mode("assoc");//read on this method

 //check if the user is inactive
 //a sample query, you can pass querys as prepared objects or pass them normally
 $dbq->get("select id from users WHERE active = 0 and email = ? and password = ? ",[$email,$password]);


 $dbq->row_count;//checking how many rows returned like mysql_num_rows

$dbq->err_msg;//stores error messages if an error occured
//inserting into a db table
$data = array('fullname'=>'?','email'=>'?','password'=>'?','phone'=>'?','address'=>'?','activation_link'=>'?','date_time'=> 'NOW()');
$dbq->add("table name",$data, array($fullname,$email,$password,$phone,$address,$link) );

//updating a record
$data = array('fullname'=>'?','email'=>'?','phone'=>'?','address'=>'?','date_time_updated'=>'NOW()');
$binding = array($fullname,$email,$phone,$address);
 $condition = "WHERE id = 7";
$dbq->change("users",$data,$condition,$binding);

//deleting a record
$condition = "where bla bla = 2";//can be empty
$bind_data = array('2');//incase you're adding a ? to the query,which means its a prepared statement, otherwise, ignore it
$dbq->remove("table name",$condition,$bind_data);

//NOTE THAT THE add(), get(),change(),remove() method will always return false if there's a query error


//ALSO NOTE THE '?' are for prepared statements stuff, its advisable to use prepared statement if you getting inputs from outside to prevent sql injection
