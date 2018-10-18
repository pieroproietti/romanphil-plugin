<?php


$root='/var/www/html';
require_once( $root . '/wp-config.php' );
$wpdb = new \wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

global $wpdb;
$menuOrder='1000';

$sql = "SELECT * FROM wp_posts WHERE post_type='product' ORDER BY post_title DESC";
echo $sql . "\n";

$posts = $wpdb->get_results($sql);
foreach ($posts as $post => $o) {
  $menuOrder++;
  $sql="UPDATE wp_posts SET menu_order=$menuOrder WHERE ID=$o->ID";  
  echo $sql . "\n";
  $wpdb->query($sql);
}
