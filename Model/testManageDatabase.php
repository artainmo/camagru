<?php

require __DIR__ . "/manageDatabase.php";

$db = new ManageDatabase;

echo "\033[01;32mACCOUNT TABLE TESTS \033[0m\n";
print_r($db->createAccount("art", "pass", "art@gmail.com"));
print_r($db->getAccount("art"));
echo "Password? " . ($db->verifyPasswordAccount("dwq", "pass")?'true':'false'). "\n";
echo "Password? " . ($db->verifyPasswordAccount("art", "pass")?'true':'false'). "\n";
echo "Password? " . ($db->verifyPasswordAccount("art", "dwq")?'true':'false'). "\n";
print_r($db->updateAccount("art", "email", "artainmo@student.s19.be"));
print_r($db->getAccount("art"));
print_r($db->updateAccount("art", "picture_comment_email_notification", 1));
print_r($db->getAccount("art"));
print_r($db->updateAccount("art", "picture_comment_email_notification", 0));
print_r($db->getAccount("art"));
print_r($db->deleteAccount("art"));

echo "\033[01;32mPICTURES TABLE TESTS \033[0m\n";
$db->createAccount("art", "pass", "art@gmail.com");

print_r($db->createPicture("selfie", "art"));
print_r($db->getPictures());
print_r($db->getPicture("public/picture/selfie"));
print_r($db->deletePicture("public/picture/selfie"));

$db->deleteAccount("art");

echo "\033[01;32mLIKES TABLE TESTS \033[0m\n";
$db->createAccount("art", "pass", "art@gmail.com");
$db->createPicture("selfie", "art");

print_r($db->createLike("art", "public/picture/selfie"));
print_r($db->getLikesOfPicture("public/picture/selfie"));
print_r($db->deleteLike("art", "public/picture/selfie"));

$db->deletePicture("public/picture/selfie");
$db->deleteAccount("art");

echo "\033[01;32mCOMMENTS TABLE TESTS \033[0m\n";
$db->createAccount("art", "pass", "art@gmail.com");
$db->createPicture("selfie", "art");

print_r($db->createComment("art", "public/picture/selfie", "nice"));
$comments = $db->getCommentsOfPicture("public/picture/selfie");
print_r($comments);
print_r($db->deleteComment($comments[0]->id));

$db->deletePicture("public/picture/selfie");
$db->deleteAccount("art");
