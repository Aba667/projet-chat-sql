<?php
session_start();

// Connexion BD
$host = "localhost";
$user = "root";
$pass = "root";
$dbname = "clientsdb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Erreur de connexion : ".$conn->connect_error);

// Vérifier champs
if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['password'])) {
    die("Tous les champs doivent être remplis.");
}

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Vérifier doublon email
$stmt = $conn->prepare("SELECT * FROM clients WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows>0) die("Email déjà utilisé");

// Insérer client
$stmt = $conn->prepare("INSERT INTO clients (nom, prenom, email, password) VALUES (?,?,?,?)");
$stmt->bind_param("ssss",$nom,$prenom,$email,$password);
if($stmt->execute()) {
    // Création session
    $_SESSION['nom']=$nom;
    $_SESSION['prenom']=$prenom;
    $_SESSION['email']=$email;

    header("Location: page5.php");
    exit;
} else {
    echo "Erreur lors de l'inscription";
}

$stmt->close();
$conn->close();
?>