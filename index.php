<!DOCTYPE html>

<?php
require("db.php");
?>

<html>
<head>
	<title>san〜san</title>
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
			<h1>Rules</h1>
		</div>
		<ol>
			<li>You will not upload, post, discuss, request, or link to anything that violates local or United States law.</li>
			<li>You will immediately cease access to the site if you are under the age of 18.</li>
			<li>You will not post or request personal information ("dox") or calls to invasion ("raids").</li>
			<li>Advertising is strictly prohibited.</li>
			<li>Do not upload images containing additional data such as embedded sounds, documents, archives, etc.</li>
			<li>Linking to possible shocking or malicious content will not be tolerated.</li>
			<li>Keep all posts, uploads and discussions in touch with the board's subject.</li>
			<li>Do not post any content that can be considered Not Safe For Work ("NSFW") outside of boards in the NSFW category.</li>
			<li>Do not post any of the following: <ul>
				<li>Troll posts</li>
				<li>Racism</li>
				<li>Grotesque (Guro, Gore) images</li>
				<li>Dubs or GET posts, including "Roll for X" images</li>
			</ul></li>
		</ol>
	</div>

	<div class="window">
		<div class="title">
			<h1>Boards</h1>
		</div>
		<table id="boards"><tr>
<?php

$boards = $db->query("SELECT * FROM boards ORDER BY category");
$category = "";

foreach($boards as $board) {
	if($category != $board["category"]) {
		if($category != "") {
			echo "</td>";
		}

		$category = $board["category"];

		echo "<td><p class=\"category\">" . $category . "</p>";
	}

	$id = $board["id"];
	$name = $board["name"];
	echo "<a class=\"link\" href=\"board.php?id=" . $id . "\">" .$name . "</a><br>";
}

?>
		</tr>
		</table>
	</div>
</body>
</html>

