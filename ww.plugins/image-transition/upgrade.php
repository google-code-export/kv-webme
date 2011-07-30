<?php
if ($version=='0') { // add table
	dbQuery(
		'create table if not exists image_transitions( 
			id int auto_increment not null primary key,
			directory text,
			trans_type text,
			pause int default 3000
		)default charset=utf8;'
	);
	$version=1;
}
if ($version=='1') { // link url
	dbQuery('alter table image_transitions add url text');
	$version=2;
}
