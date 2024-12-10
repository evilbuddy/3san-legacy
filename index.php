<!DOCTYPE html>

<?php
require("db.php");
?>

<html>

<head>
	<title>3san</title>
	<link rel="icon" type="image/x-icon" href="media/icon.ico">

	<link rel="stylesheet" href="style/theme.css">
	<link rel="stylesheet" href="style/index.css">

	<script>
		function board(id) {
			window.open("board.php?id=" + id, "_self");
		}
	</script>
</head>

<body>
	<img id="logo" src="media/logo.svg">
	<h1 id="title">san~san</h1>

		<?php
foreach ($db->query("SELECT * FROM boards") as $board) {
	echo("<div class=\"board\" onclick=\"board('{$board["id"]}')\">");
	echo("<h1>{$board["id"]} - {$board["name"]}</h1>");
	echo("</div>");
}
		?>
</body>

</html>