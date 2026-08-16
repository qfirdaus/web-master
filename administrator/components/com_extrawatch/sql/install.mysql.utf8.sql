create table if not exists `#__extrawatch_settings` (
id int auto_increment primary key,
`key` varchar(50),
value varchar(1024)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_general_ci;

#CREATE INDEX extrawatch_settings_key_idx ON #__extrawatch_settings(`key`);


