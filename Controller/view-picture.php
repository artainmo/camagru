<button onClick="window.location.href='/gallery.php'">Back</button>
<?php
	session_start();
	require_once(__DIR__ . "/../Model/manageDatabase.php");
	$db = new ManageDatabase;

	if (!isset($_SERVER['QUERY_STRING'])) {
        exit();
    } else {
        parse_str($_SERVER['QUERY_STRING'], $query);
        if (isset($query['picId'])) {
			$pic = $db->getPicture($query['picId']);
			if ((isset($pic[0]) and $pic[0] === false) or count($pic) === 0) { 
				exit();
			}
		} else { exit(); }
	}

	if (isset($_POST['like'])) {
		if ($_POST['like'] === 'Like') {
			$db->createLike($_SESSION['account'], $query['picId']);
		} elseif ($_POST['like'] === 'Unlike') {
			$db->deleteLike($_SESSION['account'], $query['picId']);
		}
	} elseif (isset($_POST['commentSubmit'])) {
		$db->createCommentAndSendNotification($_SESSION['account'], 
			$query['picId'], $_POST['commentInput']);
		unset($_POST['commentSubmit']);
		unset($_POST['commentInput']);
	}	

	$pic = $pic[0];
	$picLikes = count($db->getLikesOfPicture($query['picId']));
	$ILiked = $db->getILiked($_SESSION['account'], $query['picId']);
	$picComments = $db->getCommentsOfPicture($query['picId']);
?>

<h3>Picture</h3>
<p>Author: <?= $pic->account_id ?></p>
<p>Creation time: <?= $pic->creationtime ?></p>
<img src='<?= $pic->imageData ?>' width='320' height='240'>
<br><br>

<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST">
	<?php if (!$ILiked) { ?>
    	<button type="submit" name="like" value="Like">Like</Button><br/>
	<?php } else { ?>
    	<button type="submit" name="like" value="Unlike">Unlike</Button><br/>
	<?php } ?>
</form>
<p>Likes: <?= $picLikes ?></p>

<h4>Comments</h4>
<ul>
	<?php 
		foreach ($picComments as $comment) {
			echo "<li><p><strong>$comment->commenter_id:</strong> $comment->content<br>
				<small>$comment->time</small></li>";
    	}
	?>
</ul>
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST">
    <input type="text" name="commentInput" required/>
    <button type="submit" name="commentSubmit">comment</Button><br/>
</form>
