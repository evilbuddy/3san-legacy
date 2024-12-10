<!DOCTYPE html>

<?php
require("db.php");

$board = $_GET["id"];
?>

<html>

<head>
	<title>3san</title>
	<link rel="icon" type="image/x-icon" href="media/icon.ico">

	<link rel="stylesheet" href="style/theme.css">
	<link rel="stylesheet" href="style/board.css">

	<script>
		function board(id) {
			window.open("board.php?id=" + id, "_self");
		}
	</script>
</head>

<body>
	<img id="logo" src="media/logo.svg">
	<?php
	$req = $db->prepare("SELECT * FROM boards WHERE id = ?");
	$req->execute([$board]);
	echo("<h1 id=\"title\">{$req->fetch()["name"]}</h1>");
	?>
		<?php
foreach ($db->query("SELECT * FROM messages WHERE board = '{$board}' AND parent IS NULL") as $thread) {
	echo("<div class=\"thread\">");
	echo("<div class=\"img\"><img src=\"uploads/{$thread["id"]}.webp\"></div>");
	echo("<div class=\"content\"><p>{$thread["body"]}</p></div>");
	foreach ($db->query("SELECT * FROM messages WHERE parent = '{$thread["id"]}'") as $message) {
		echo("<p class=\"message\">{$message["body"]}</p>");
	}
}
		?>
</body>

</html>