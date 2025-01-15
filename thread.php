<!DOCTYPE html>

<?php
require("db.php");

if(isset($_POST["Comment"])) {
	$i = 0;
	$name = "Anonymous";

	if(isset($_POST["Name"]) && trim($_POST["Name"]) != "") {
		$name = $_POST["Name"];
	}

	if(file_exists($_FILES["File"]["tmp_name"])) {
		$i = 1;
	}

	$r = $db->prepare("INSERT INTO replies
		(thread, image, timestamp, name, comment)
		VALUES (:b, :i, :t, :n, :c)");
	$p = $r->execute([
		":b" => $_POST["Thread"],
		":i" => $i,
		":t" => time(),
		":n" => $name,
		":c" => $_POST["Comment"]
	]);

	if($i != 0) {
		$p = $db->prepare("SELECT * FROM threads WHERE board = :b AND name = :n AND comment = :c");
		$p->execute([
			":b" => $_POST["Board"],
			":n" => $name,
			":c" => $_POST["Comment"]
		]);

		$type = mime_content_type($_FILES["File"]["tmp_name"]);
		$convert = [
			"image/bmp" => function($filename) { return imagecreatefrombmp($filename); },
			"image/gif" => function($filename) { return imagecreatefromgif($filename); },
			"image/png" => function($filename) { return imagecreatefrompng($filename); },
			"image/tga" => function($filename) { return imagecreatefromtga($filename); },
			"image/avif" => function($filename) { return imagecreatefromavif($filename); },
			"image/jpeg" => function($filename) { return imagecreatefromjpeg($filename); },
			"image/webp" => function($filename) { return imagecreatefromwebp($filename); }
		];

		imagewebp($convert[$type]($_FILES["File"]["tmp_name"]), "uploads/threads/" . $p->fetch()["id"] . ".webp");
	}
}
?>

<html>
<head>
<?php
$id = $_GET["id"];

$postr = $db->prepare("SELECT * FROM threads WHERE id = ?");
$postr->execute([$_GET["id"]]);
$post = $postr->fetch();

$boardr = $db->prepare("SELECT * FROM boards WHERE id = ?");
$boardr->execute([$post["board"]]);
$board = $boardr->fetch();

echo "<title>[". $board["id"] . "] - " . $board["name"] . " - 3san</title>";
?>
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="media/icon.ico">
	<script src="script.js"></script>
</head>

<body>
	<a id="home" class="link" href="index.php">[home]</a>
	<img src="media/logo.svg">
<?php echo "<h1 class=\"title\">/" . $board["id"] . "/ - " . $board["name"] . "</h1>"; ?>
<h1 class="title link" onclick="showReplyForm()">[Post a Reply]</h1>
<div id="reply" class="window hidden">
	<div class="title">
		<img src="media/cross.png" onclick="win_hide(this)">
<?php
echo "<h1> Reply to Thread #" . $id . "</h1>"
?>
	</div>
	<form id="post" method="POST" enctype="multipart/form-data">
		<table>
			<tr>
				<th>Name</th>
				<td><input name="Name" placeholder="Anon"></td>
			</tr>
			<tr class="comment">
				<th>Comment</th>
				<td><textarea name="Comment"></textarea></td>
			</tr>
			<tr>
				<th>File</th>
				<td><input type="file" name="File" placeholder="Anon"></td>
			</tr>
		</table>
		<input type="hidden" name="Thread" value="<?php echo $id; ?>">
		<input type="submit" value="Post">
	</form>
</div>

<?php
$image = "uploads/threads/" . $post["id"] . ".webp";
$dimensions = getimagesize($image)[0] . "x" . getimagesize($image)[1];

$timestamp = date("d/m/y H:i:s (e)", $post["timestamp"]);
	
$units = array("B", "KB", "MB");
$bytes = max(filesize($image), 0);
$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
$pow = min($pow, count($units) - 1);
$bytes /= (1 << (10 * $pow));
$size = round($bytes, 2) . $units[$pow];
	
echo "<div class=\"post\">";
echo "<div class=\"image\">";
echo "<p><a href=\"" . $image . "\">File</a> (" . $size . ", " . $dimensions . ")</p>";
echo "<img src=\"" . $image . "\">";
echo "</div>";
echo "<h1><a href=\"thread.php?id=" . $post["id"] . "\">" . $post["name"] . "</a>";
echo "<p>" . $timestamp . "</p>";
echo "<p>#" . $post["id"] . "</p>";
echo "</h1>";
echo "<p class=\"comment\">" . nl2br($post["comment"]) . "</p>";
echo "</div>";

$page = 1;

if(isset($_GET["page"])) {
	$page = $_GET["page"];
}

$max = 15;
$min = ($page - 1) * $max;

$posts = $db->prepare("SELECT * FROM replies WHERE thread = :b LIMIT :min, :max");
$posts->bindParam(":b", $_GET["id"], PDO::PARAM_STR);
$posts->bindParam(":min", $min, PDO::PARAM_INT);
$posts->bindParam(":max", $max, PDO::PARAM_INT);
$posts->execute();

foreach($posts as $post) {
	$image = "uploads/replies/" . $post["id"] . ".webp";
	$dimensions = getimagesize($image)[0] . "x" . getimagesize($image)[1];

	$timestamp = new DateTime("@" . $post["timestamp"], new DateTimeZone("UTC"));
	
	$units = array("B", "KB", "MB");
	$bytes = max(filesize($image), 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= (1 << (10 * $pow));
	$size = round($bytes, 2) . $units[$pow];
	
	echo "<div class=\"reply\" id=\"" . $post["id"] . "\">";

	if($post["image"] != 0) {
		echo "<div class=\"image\">";
		echo "<p><a href=\"" . $image . "\">File</a> (" . $size . ", " . $dimensions . ")</p>";
		echo "<img src=\"" . $image . "\">";
		echo "</div>";
	}

	echo "<h1><a href=\"thread.php?id=" . $post["id"] . "\">" . $post["name"] . "</a>";
	echo "<p>" . $timestamp->format("d/m/y H:i:s") . " (UTC)</p>";
	echo "<a href=\"javascript:addReply('" . $post["id"] . "')\">#" . $post["id"] . "</a>";
	echo "</h1>";
	echo "<p>";

	$comment = "";

	foreach(explode("\n", $post["comment"]) as $line) {
		if(substr($line, 0, 2) == ">>") {
			echo "<a href=\"#" . substr($line, 2) . "\">" . $line . "</a>";
		} else {
			echo $line;
		}

		echo "<br>";
	}

	echo "</p>";
	echo "</div>";
}

echo "<p>";
echo "<a";
if($page > 1) {
	$url = $_SERVER["REQUEST_URI"];
	$np = str_replace("page=" . $_GET["page"], "page=" . $_GET["page"] - 1, $url);

	echo " href=\"" . $np . "\"";
}
echo ">[prev]</a>";

$pagesr = $db->prepare("SELECT COUNT(*) FROM threads WHERE board = :b");
$pagesr->execute([
	":b" => $_GET["id"],

]);
$pages = $pagesr->fetch()[0];
$pages = ceil($pages / 15);

echo " page " . $page . "/" . $pages . " ";

echo "<a";
if($page < $pages) {
	$url = $_SERVER["REQUEST_URI"];
	$np = str_replace("page=" . $_GET["page"], "page=" . $_GET["page"] + 1, $url);

	echo " href=\"" . $np . "\"";
}
echo "[next]></a>";
echo "</p>";
?>
</body>
</html>
