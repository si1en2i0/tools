<?php

/**
 * VCF Creator
 *
 * @author Lanisle
 */

/* CONFIG */

define('SRCFILE', './contacts.txt');
define('DESTDIR', './iphone_contacts');

/* PREPARE */

$raw = file_get_contents(SRCFILE);
if (!file_exists(DESTDIR)) {
	mkdir(DESTDIR);
}

/* START */

foreach (explode("\n", $raw) as $item) {
	$contact = parse_contact(trim($item));
	create_vcf($contact);
}

echo 'Done ', date('Y-m-d H:i:s');

/* HELPERS */

function parse_contact($item)
{
	$parts = explode(',', $item);
	$names = explode(' ', array_shift($parts));

	if (count($names) == 2) {
		list($name1, $name2) = $names;
	} else {
		$name1 = $names[0];
		$name2 = '';
	}

	// $tels = implode(', ', $parts);
	// echo "{$name1}{$name2}, Tels: {$tels}<br />\n";
	
	return array(
		'name1' => $name1,
		'name2' => $name2,
		'tels' => $parts
	);
}

function create_vcf($contact)
{
	$tels = array();
	foreach ($contact['tels'] as $tel) {
		$tels[] = "TEL;type=CELL:{$tel}";
	}
	$tels = implode("\n", $tels);

	$fullname = $contact['name1'] . $contact['name2'];
	
	$content = <<<EOT
BEGIN:VCARD
VERSION:3.0
N:{$contact['name1']};{$contact['name2']};;;
FN:{$fullname}
{$tels}
END:VCARD\n\n
EOT;
	
	// 同步推只支持导入GBK编码的VCF
	file_put_contents(DESTDIR . "/{$fullname}.vcf", $content);
}