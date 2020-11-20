<?php

//上传
require('./../config.php');
$f=$webroot.'/web/'.$_REQUEST['f'];
$n=$_REQUEST['n'];
date_default_timezone_set('PRC');
$conn = new mysqli($dbserver, $dbuser, $dbpwd, $dbname);
// 检测连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$usrid = $_COOKIE['lc_uid'];
$usrpwd = $_COOKIE['lc_passw'];
$msg = '[文件]'.$f;
$roomid = $_REQUEST['cr'];
$appid = "jA2cR2eA4gG0nQ1dQ3eR2bP0wK7hA0";
$path = './../chatdata/room'.$roomid.'.txt';
$app = getappname($appid);

$sql = "SELECT * FROM lc_users WHERE id=".$usrid;
$result = $conn->query($sql);
if ($result->num_rows != 1) {
    echo "Login Error (01)";
    $conn->close();
    exit;
}
$row = $result->fetch_assoc();
if ($usrpwd == $row['pwd']) {
    ;
} else {
    echo "Login Error (02)";
    $conn->close();
    exit;
}
if ($row['status'] == -1) {
    echo "Access denied (03)";
    $conn->close();
    exit;
}

$sql = "SELECT * FROM `lc_msg`";
$result = $conn->query($sql);

$sql = "INSERT INTO `lc_msg` (`msgid`, `usrid`, `msg`, `time`, `client`, `type`, `room`, `filename`) VALUES ('".$result->num_rows."', '$usrid', '$f', '".date('Y-m-d H:i:s', time())."', '$app', '3', '$roomid', '$n')";
$result = $conn->query($sql);

if (!file_exists($path)) {
    $handle = fopen($path, 'w+');
    fwrite($handle, $row['name'].' '.date('Y-m-d H:i', time())."\r\n".$msg);
    fclose($handle);
} else {
    $handle = fopen($path, 'r');
    $content = fread($handle, filesize($path));
    $content = $row['name'].' '.date('Y-m-d H:i', time())."\r\n".$msg . "\r\n\r\n".$content;
    fclose($handle);
    $handle = fopen($path, 'w');
    fwrite($handle, $content);
    fclose($handle);
}

$pathjson = './../chatdata/room'.$roomid.'.json';
$jsoncode = '';

$arr = array('usr' => $row['name'], 'msg' => $msg, 'time' => date('Y-m-d H:i', time()), 'uid' => $usrid, 'app' => getappname($appid), 'ip' => getip());
$jsonstr = json_encode($arr);
if (!file_exists($pathjson)) {
    $handle = fopen($pathjson, 'w+');
    fwrite($handle, $jsonstr);
    fclose($handle);
} else {
    $handle = fopen($pathjson, 'r');
    $content = fread($handle, filesize($pathjson));
    $content = $jsonstr.','.$content;
    fclose($handle);
    $handle = fopen($pathjson, 'w');
    fwrite($handle, $content);
    fclose($handle);
}

$pathtime = './../chatdata/room'.$roomid.'.time.txt';
if (!file_exists($pathtime)) {
    $handle = fopen($pathtime, 'w+');
    fwrite($handle, date('YmdHis', time()));
    fclose($handle);
} else {
    $handle = fopen($pathtime, 'w');
    fwrite($handle, date('YmdHis', time()));
    fclose($handle);
}
$msg = '<div id="filebox"><h3>文件 File: <a href="'.$f.'" download="'.$n.'" target="_blank"><span style="font-size:12px;font-weight:500;color:white;">'.$n.'</span></a></h3></div>';


$htmlmsg = "<p style='font-size:20px;font-weight:500;'><abbr style='text-decoration:none;' title='uid: ".$row["id"]."  email: ".$row["email"]."'><img src='".$row["picurl"]."' height='22' width='22'/>".$row["name"]."</abbr>&nbsp;<span style='font-size:12px;font-weight:normal;'>".date('Y-m-d H:i:s', time())."</span></p><div><abbr style='text-decoration:none;' title='发送了一个文件，点击可下载。   ip: ".getip()."'>".$msg."</abbr></div>"."\n";
$pathhtml = "./../chatdata/room".$roomid.".html";
if (!file_exists($pathhtml)) {
    $handle = fopen($pathhtml, 'w+');
    fwrite($handle, $htmlmsg);
    fclose($handle);
} else {
    $handle = fopen($pathhtml, 'r');
    $content = fread($handle, filesize($pathhtml));
    $content = $htmlmsg.$content;
    fclose($handle);
    $handle = fopen($pathhtml, 'w');
    fwrite($handle, $content);
    fclose($handle);
}

$conn->close();

echo "<!DOCTYPE html><html><head><meta http-equiv='refresh' content='0.5;url=index.php'></head><body>文件发送成功！</body></html>";