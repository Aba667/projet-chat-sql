<?php
session_start();

// Connexion BD
$host="localhost"; $user="root"; $pass="root"; $dbname="clientsdb";
$conn=new mysqli($host,$user,$pass,$dbname);
if($conn->connect_error) die("Erreur de connexion: ".$conn->connect_error);

// Vérifier champs
if(empty($_POST['email']) || empty($_POST['password'])) die("Tous les champs sont obligatoires");

$email=$_POST['email']; $password=$_POST['password'];

$stmt=$conn->prepare("SELECT * FROM clients WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$res=$stmt->get_result();

if($res->num_rows==0) die("Email introuvable");
$user=$res->fetch_assoc();

if(password_verify($password,$user['password'])){
    $_SESSION['id']=$user['id'];
    $_SESSION['nom']=$user['nom'];
    $_SESSION['prenom']=$user['prenom'];
    $_SESSION['email']=$user['email'];
    header("Location: page5.php");
    exit;
}else{
    echo "Mot de passe incorrect";
}

$stmt->close(); $conn->close();
?>