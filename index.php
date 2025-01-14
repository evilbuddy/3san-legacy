<!DOCTYPE html>

<?php
require("db.php");
?>

<html>
<head>
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="media/icon.ico">
	<script src="script.js"></script>
</head>

<body style="width:60em">
	<img src="media/logo.svg">

	<div class="window">
		<div class="title">
			<img src="media/cross.png" onclick="win_close(this)">
			<h1>What is 3san?</h1>
		</div>
		<p>3san (san-san) is a (very) simple image board where anyone can post comments and share Images.</p>
	</div>

	<div class="window">
		<div class="title">
			<h1>Boards</h1>
		</div>
		<?php
			$boards = $db->query("SELECT * FROM boards");
			foreach($boards as $board) {
				$id = $board["id"];
				$name = $board["name"];
				echo "<a class=\"link\" href=\"board.php?id=" . $id . "\">" .$name . "</a>";
			}
		?>
	</div>
</body>
</html>
