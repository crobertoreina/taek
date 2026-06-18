<?php
$c = new mysqli('localhost','root','','taekdb');
echo "=== participantes ===\n";
$r = $c->query('DESCRIBE participantes');
while ($w = $r->fetch_assoc()) {
    echo $w['Field'] . ' ' . $w['Type'] . ($w['Key'] ? ' [' . $w['Key'] . ']' : '') . ' Default:' . ($w['Default'] ?? 'NULL') . "\n";
}
echo "\n=== torneos ===\n";
$r = $c->query('DESCRIBE torneos');
while ($w = $r->fetch_assoc()) {
    echo $w['Field'] . ' ' . $w['Type'] . ($w['Key'] ? ' [' . $w['Key'] . ']' : '') . ' Default:' . ($w['Default'] ?? 'NULL') . "\n";
}
$c->close();
?>
