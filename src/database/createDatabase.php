<?php

require_once 'database/connectDB.php';
require 'vendor/autoload.php';

$dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
$dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$createTableUser = ("CREATE TABLE IF NOT EXISTS
`User` (
    `IdUser` int(11) NOT NULL AUTO_INCREMENT,
    `FirstName` varchar(255) DEFAULT NULL,
    `LastName` varchar(255) DEFAULT NULL,
    `Email` varchar(255) DEFAULT NULL,
    `UserPassword` varchar(255) DEFAULT NULL,
    `IsPro` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`IdUser`),
    CONSTRAINT unique_User_Email UNIQUE (`Email`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableUser);

$createTablePost = ("CREATE TABLE IF NOT EXISTS
`Post` (
    `IdPost` int(11) NOT NULL AUTO_INCREMENT,
    `TitlePost` varchar(255) DEFAULT NULL,
    `ContentPost` TEXT DEFAULT NULL,
    `DatePost` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `PicturePost` LONGBLOB DEFAULT NULL,
    PRIMARY KEY (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePost);

$createTableUserPost = ("CREATE TABLE IF NOT EXISTS
`UserPost` (
    `Id`int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    PRIMARY KEY (`Id`),
    CONSTRAINT fk_User_UserPost FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_UserPost FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableUserPost);

$createTableComment = ("CREATE TABLE IF NOT EXISTS
`Comment` (
    `IdComment`int(11) NOT NULL AUTO_INCREMENT,
    `ContentComment` TEXT DEFAULT NULL,
    `DateComment` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    PRIMARY KEY (`IdComment`),
    CONSTRAINT fk_User_Comment FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_Comment FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableComment);