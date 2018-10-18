<?php

require '.auth.php';
$pdo = new PDO($cnn, $user, $pass);


$sql = "SELECT * FROM wp_posts WHERE post_type='product' ORDER BY post_title DESC";
echo $sql . "\n";
$stml = $pdo->prepare($sql);
$stml->execute();
$menuOrder='1000';
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $menuOrder++;
  $ID=$row['ID'];
  $sql="UPDATE wp_posts SET menu_order=$menuOrder WHERE ID=$ID";
  echo $sql . "\n";
  $other = $pdo->prepare($sql);
  $other->execute();
  echo $row['post_title']. ": " . $menuOrder ."\n";
}
