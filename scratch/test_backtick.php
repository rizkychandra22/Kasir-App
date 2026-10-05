<?php

$html = '<div style=`width: 50%`>test</div>';
$doc = new DOMDocument();
@$doc->loadHTML($html);
$div = $doc->getElementsByTagName('div')->item(0);
echo "style attr: " . $div->getAttribute('style') . "\n";
foreach ($div->attributes as $attr) {
    echo $attr->name . ' = ' . $attr->value . "\n";
}
