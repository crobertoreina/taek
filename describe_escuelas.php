<?php
$c = new mysqli('localhost','root','','taekdb');
$r = $c->query('DESCRIBE escuelas');
while ($w = $r->fetch_assoc()) {
    echo $w['Field'] . ' ' . $w['Type'] . ($w['Key'] ? ' [' . $w['Key'] . ']' : '') . ' Default:' . ($w['Default'] ?? 'NULL') . "\n";
}
$c->close();
?>
