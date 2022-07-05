<?php
	session_start();
	if (!isset($_SESSION['account'])) { header('Location: http://localhost:8000/index.php'); }
	require_once(__DIR__ . "/../Model/manageDatabase.php");
	$db = new ManageDatabase;

	if (!isset($_SERVER['QUERY_STRING'])) {
        exit();
  } else {
  	parse_str($_SERVER['QUERY_STRING'], $query);
    if (isset($query['picId']) and isset($query['origin'])) {
			$origin = $query['origin'];
			$pic = $db->getPicture($query['picId']);
			if ((isset($pic[0]) and $pic[0] === false) or count($pic) === 0) {
				exit();
			}
		} else { exit(); }
	}

	if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['storagePath'])) {
		$db->deleteLikesOfPicture($_POST['storagePath']);
		$db->deleteCommentsOfPicture($_POST['storagePath']);
		$db->deletePicture($_POST['storagePath']);
	} elseif (isset($_POST['like'])) {
		if ($_POST['like'] === 'Like') {
			$db->createLike($_SESSION['account'], $query['picId']);
		} elseif ($_POST['like'] === 'Unlike') {
			$db->deleteLike($_SESSION['account'], $query['picId']);
		}
	} elseif (isset($_POST['commentSubmit'])) {
		$comment = htmlspecialchars(trim($_POST['commentInput']));
		$db->createCommentAndSendNotification($_SESSION['account'],
			$query['picId'], $comment);
		unset($_POST['commentSubmit']);
		unset($_POST['commentInput']);
	}

	$pic = $pic[0];
	$picLikes = count($db->getLikesOfPicture($query['picId']));
	$ILiked = $db->getILiked($_SESSION['account'], $query['picId']);
	$picComments = $db->getCommentsOfPicture($query['picId']);
?>

<?php require(__DIR__ . "/../View/header/base-header.html"); ?>

<button class="backButton" onClick="window.location.href='/<?=$origin?>.php'">
	Back
</button>

<h1 class="pageTitle">Picture</h1>
<div class="wrapperPictureInfo">
	<div class="block1PictureInfo">
		<p>Author: <?= $pic->account_id ?></p><br>
		<p>Creation time: <?= $pic->creationtime ?></p><br>
		<img src='<?= $pic->imageData ?>' width='320' height='240'>
		<br><br>
		<form class="block1Like"
					action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST">
			<?php if (!$ILiked) { ?>
		    	<button type="submit" name="like" value="Like">Like</Button><br/>
			<?php } else { ?>
		    	<button type="submit" name="like" value="Unlike">Unlike</Button><br/>
			<?php } ?>
		</form>
		<button><?= $picLikes ?></button>
		<br><br><br>
		<?php
			if ($pic->account_id === $_SESSION['account']) {
				echo "<button class='deleteButton' " .
					"onClick='deletePicture(`$pic->storagepath`)'>Delete</button><br>";
			}
		?>
	</div>

	<div class="block3PictureInfo">
		<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST">
		    <input type="text" name="commentInput" maxlength="300" required/>
		    <button type="submit" name="commentSubmit">comment</Button><br/>
		</form>
		<br>
			<?php
				foreach ($picComments as $comment) {
					echo "<p class='comment'><strong>$comment->commenter_id:</strong>" .
					"<br>$comment->content<br>
						<small>$comment->time</small><br></p>";
		    	}
			?>
	<div>
</div>

<script>
	function deletePicture(storagePath) {
		//Call php to delete picture
		fetch("http://localhost:8000<?=$_SERVER['REQUEST_URI']?>", {
			method: "POST", //This should be delete but we use post instead because php can easily parse it
			headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
	    body: `storagePath=${storagePath}`
	  });
		window.location.reload();//Refresh the page to see new picture in picture list
		window.location.href="/<?=$origin?>.php";
	}
</script>
