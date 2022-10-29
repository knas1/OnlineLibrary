<?php
require_once 'config/database.php';

function filterString($field){

  $field= filter_var( trim($field), FILTER_SANITIZE_STRING);

  if (empty($field)) {
    return false;
  }else {
    return $field;
  }
}

function filterEmail($field){

  $field= filter_var(trim($field), FILTER_SANITIZE_EMAIL);

  if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
    return $field;
  }else {
    return false;
  }
}

function canUpload($file){

  $allowed = [
    'jpg'=>'image/jpeg',
    'png'=>'image/png',
    'gif'=>'image/gif'
  ];

  $maxFileSize= 8*1024;

  $fileMimeType = mime_content_type($file['tmp_name']);
  $fileSize = $file['size'];
  if (!in_array($fileMimeType,$allowed)) return "File not allowed ";

  if ($fileSize>$maxFileSize) $_SESSION["success_message"] = "File size not allowed, your file size is $fileSize and allowed size is $maxFileSize";

  return true;
}



$nameError = $emailError = $messageError = $documentError  ='';
$name = $email = $message = $document = '';

$uploadDir="uploads";

if ($_SERVER["REQUEST_METHOD"]=="POST") {

  $name = filterString($_POST['name']);
  if(!$name){
     $_SESSION["contact_form"]["name"]="";
     $nameError = "Your name is empty";
   }else {
      $_SESSION["contact_form"]["name"]=$name;
   }

  $email=filterEmail($_POST['email']);
  if (!$email){
     $_SESSION["contact_form"]["email"]='';
     $emailError ='your email is invalid';
   }else {
      $_SESSION["contact_form"]["email"]=$email;
   }

  $message=filterString($_POST['message']);
  if(!$message){
    $_SESSION["contact_form"]["message"]="";
   $messageError="Your message is invalid";
 }else {
   $_SESSION["contact_form"]["message"]=$message;
 }
  //echo "<pre>";print_r($_POST); print_r($_FILES);echo "</pre>";
  if(isset($_FILES['document']) && $_FILES['document']['error']==0){

    $canUpload = canUpload($_FILES['document']);

    //To make sure they have write all information before making a directory
    if($canUpload===true && $message && $email && $name){

      if(!is_dir($uploadDir)){
        // you might need to use umask(0); to give 777 primmsion for the file then use mkdir($uploadDir,0775)
        mkdir($uploadDir);
      }

      $filename = time().$_FILES['document']['name'];
      move_uploaded_file($_FILES['document']['tmp_name'] , $uploadDir."/".$filename);


  }else
    $documentError = $canUpload;


  }

  if (!$nameError && !$emailError && !$messageError) {

    $filename ? $filepath = $uploadDir.'/'.$filename : $filepath ='';

    $InsertQuery= $mysqli->prepare("insert into messages(name,email,document,message)
                                  values (?, ?, ?, ?)");
    $InsertQuery->bind_param("ssss",$dbContacterName,$dbEmail,$dbDocument,$dbMessage);

    $dbContacterName=$name;
    $dbEmail=$email;
    $dbDocument=$filepath;
    $dbMessage=$message;

    $InsertQuery->execute();


    //$messageQuery=$mysqli->query("insert into messages(name,email,document,service_id,message)
                          //        values ('$name','$email','$filepath',".$_POST['service_id'].", '$message')
                            //    ");


    header("Location: contact.php");
    //unset($_SESSION["contact_form"]);
  }
}
