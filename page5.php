<?php
session_start();
if(!isset($_SESSION['email'])) { header("Location: page4.php"); exit; }

// Connexion BD
$host="localhost"; $user="root"; $pass="root"; $dbname="clientsdb";
$conn=new mysqli($host,$user,$pass,$dbname);
if($conn->connect_error) die("Erreur: ".$conn->connect_error);

// Création table chat
$conn->query("CREATE TABLE IF NOT EXISTS chat (
    num INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    date_message DATETIME NOT NULL
)");

// Traitement formulaire
$pseudo=''; 
if($_SERVER['REQUEST_METHOD']==='POST'){
    $pseudo=trim($_POST['pseudo']);
    $message=trim($_POST['message']);
    if(!empty($pseudo)&&!empty($message)){
        $pseudo=htmlentities($pseudo);
        $message=htmlentities($message);
        $stmt=$conn->prepare("SELECT * FROM chat WHERE pseudo=? AND message=?");
        $stmt->bind_param("ss",$pseudo,$message);
        $stmt->execute();
        $res=$stmt->get_result();
        if($res->num_rows===0){
            $stmt_insert=$conn->prepare("INSERT INTO chat (pseudo,message,date_message) VALUES (?,?,NOW())");
            $stmt_insert->bind_param("ss",$pseudo,$message);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
        // Supprimer anciens messages
        $conn->query("DELETE FROM chat WHERE num NOT IN (SELECT num FROM (SELECT num FROM chat ORDER BY date_message DESC LIMIT 100) tmp)");
    }else{
        echo "<p style='color:red;'>Pseudo et message ne peuvent pas être vides.</p>";
    }
}

// Pagination
$messagesParPage=10;
$totalDesMessages=$conn->query("SELECT COUNT(*) as total FROM chat")->fetch_assoc()['total'];
$nombreDePages=ceil($totalDesMessages/$messagesParPage);
$page=isset($_GET['page'])?(int)$_GET['page']:1;
if($page<1)$page=1;
$debut=($page-1)*$messagesParPage;
$messages=$conn->query("SELECT * FROM chat ORDER BY date_message DESC LIMIT $debut,$messagesParPage");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mini-chat</title>
<script>
function updateDate(){
    const now=new Date();
    document.getElementById('date').textContent=now.toLocaleString();
}
setInterval(updateDate,1000);
</script>
</head>
<body>
<h2>Mini-chat</h2>
<p>Date et heure : <span id="date"></span></p>
<p>Connecté : <?php echo $_SESSION['prenom'].' '.$_SESSION['nom'].' ('.$_SESSION['email'].')'; ?></p>

<form method="POST">
    <input type="text" name="pseudo" placeholder="Votre pseudo" value="<?php echo htmlentities($pseudo); ?>" required>
    <input type="text" name="message" placeholder="Votre message" required>
    <button type="submit">Envoyer</button>
</form>
<hr>
<h3>Derniers messages</h3>
<?php while($row=$messages->fetch_assoc()){
    echo "<p><strong>".$row['pseudo']."</strong> [".$row['date_message']."] : ".$row['message']."</p>";
} ?>
<p>Pages :
<?php for($i=1;$i<=$nombreDePages;$i++){
    if($i==$page) echo " <strong>$i</strong> ";
    else echo " <a href='page5.php?page=$i'>$i</a> ";
} ?>
</p>
</body>
</html>