<?php
require_once('config.php');
require_once('AfricasTalkingGateway.php');


$dsn = 'mysql:dbname=ussd;host=127.0.0.1;'; //database name
$user = 'root'; //mysql user 
$password = ''; //mysql password

//  Create a PDO instance that will allow you to access your database
try {
    $dbh = new PDO($dsn, $user, $password);
}
catch(PDOException $e) {
    //var_dump($e);
    echo("PDO error occurred");
}
catch(Exception $e) {
    //var_dump($e);
    echo("Error occurred");
}

// Reads the variables sent via POST from our gateway
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$ussd_string = $_POST["text"];

//Declare global variables for the african talking to see

 $regNo=""; 
 $class="";
 
 
  //set default level to zero 
  
  
 $level = 0;
 
 
 /* Split text input based on asteriks(*) 
  * Africa's talking appends asteriks for after every menu level or input 
  * One needs to split the response from Africa's Talking in order to determine 
  * the menu level and input for each level 
  * */ 
 
 if($ussd_string != ""){  
$ussd_string=  str_replace("#", "*", $ussd_string);  
$ussdString_explode = explode("*", $ussd_string);  
$level = count($ussdString_explode);  
}

  // Get menu level from ussd_string reply 
  
if ($level==0){  
displaymenu();  
}  
function displaymenu(){  
$ussd_text="Welcome to MATOKEO-PAP System. <br/> Please reply with: <br/>1. Get Results<br/>2. About<br/>";  
ussd_proceed($ussd_text);  
}

/* The ussd_proceed function appends CON to the USSD response your application gives. 
  * This informs Africa's Talking USSD gateway and consequently Safaricom's 
  * USSD gateway that the USSD session is till in session or should still continue 
  * Use this when you want the application USSD session to continue 
 */
 function ussd_proceed($ussd_text){ 
     echo "CON $ussd_text";
 }
 
 /* This ussd_stop function appends END to the USSD response your application gives. 
  * This informs Africa's Talking USSD gateway and consecuently Safaricom's 
  * USSD gateway that the USSD session should end. 
  * Use this when you to want the application session to terminate/end the application 
 */ 
 function ussd_stop($ussd_text){ 
     echo "END $ussd_text"; 
 }
 
 
 if ($level>0){  
switch ($ussdString_explode[0])  
{  
case 1:  
Login($ussdString_explode,$dbh,$phoneNumber);  
break;  
case 2:  
about($ussdString_explode);  
break;  
default:
$ussd_text = "Incorrect Input.Please Try Again!"; 
    ussd_stop($ussd_text); 
}  
}
function login($details,$dbh,$phoneNumber){  
if (count($details)==1){  
$ussd_text="<br/> Enter your Admission Number (Registration No)";  
ussd_proceed($ussd_text);  
}  
else if (count($details)==2){  
$ussd_text="CON <br/> Enter Your Id_Number";  
ussd_proceed($ussd_text);  
}  
else if (count($details)==3){  
$ussd_text="CON <br/> Enter Your Name";  
ussd_proceed($ussd_text);  
}  
else if(count($details) == 4){  
$rollid=$details[1];  
$id_num=$details[2]; 
$name=$details[3];
$phone=$phoneNumber;

$_SESSION['rollid']=$rollid;

$qery = "SELECT   tblstudents.StudentName,tblstudents.RollId,tblstudents.NtnalId,tblstudents.RegDate,tblstudents.StudentId,tblstudents.Status from tblstudents  where tblstudents.RollId=:rollid and tblstudents.NtnalId=:id_num";
$stmt = $dbh->prepare($qery);
$stmt->bindParam(':rollid',$rollid,PDO::PARAM_STR);
$stmt->bindParam(':id_num',$id_num,PDO::PARAM_STR);
$stmt->execute();

$query ="select t.StudentName,t.RollId,t.marks,SubjectId,tblsubjects.SubjectName from (select sts.StudentName,sts.RollId,tr.marks,SubjectId from tblstudents as sts join  tblresult as tr on tr.StudentId=sts.StudentId) as t join tblsubjects on tblsubjects.id=t.SubjectId where (t.RollId=:rollid)";
$query= $dbh -> prepare($query);
$query->bindParam(':rollid',$rollid,PDO::PARAM_STR);
$query-> execute();  
$results = $query -> fetchAll(PDO::FETCH_OBJ);
if($countrow=$query->rowCount()>0)
{ 
echo " END Welcome :".$name ." <br/>
Admission No: " . $rollid. "<br/>" .  
"Your Results are:<br/>";
	
foreach($results as $result){

echo
 $result->SubjectName." ".
 $result->marks. "<br/>";

}} }
 
}
 // Function that hanldles About menu 
 function about($ussd_text) 
 { 
     $ussd_text = "This is a university results retrieval System"; 
     ussd_stop($ussd_text); 
 }
 # close the pdo connection  
$dbh = null;
 ?>