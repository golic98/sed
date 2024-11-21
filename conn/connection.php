<?php
try{
$conn = new PDO('mysql:host=localhost; dbname=centroescolarbd', 'eleNano', 'kjdoDIN');
} catch(PDOException $e){
   echo "Error: ". $e->getMessage();
   die();
}
?>