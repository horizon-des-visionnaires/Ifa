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
    `ProfilPicture` LONGBLOB DEFAULT NULL,
    `ProfilDescription` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdUser`),
    CONSTRAINT unique_User_Email UNIQUE (`Email`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci");
$dsn->exec($createTableUser);

$createTablePost = ("CREATE TABLE IF NOT EXISTS
`Post` (
    `IdPost` int(11) NOT NULL AUTO_INCREMENT,
    `TitlePost` varchar(255) DEFAULT NULL,
    `ContentPost` TEXT DEFAULT NULL,
    `DatePost` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `PicturePost` LONGBLOB DEFAULT NULL,
    `Views` int(11) DEFAULT '0',
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

$createTableLike = ("CREATE TABLE IF NOT EXISTS
`Like` (
    `IdLike` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    PRIMARY KEY (`IdLike`),
    CONSTRAINT fk_User_Like FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_Like FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableLike);

$createTableLinkUser = ("CREATE TABLE IF NOT EXISTS
`LinkUser` (
    `IdLinkUser` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `Link` varchar(255) DEFAULT NULL,
    `LinkName` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdLinkUser`),
    CONSTRAINT fk_User_LinkUser FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableLinkUser);

$createTableSubscriber = ("CREATE TABLE IF NOT EXISTS
`Subscriber` (
    `Id` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `IdSubscriber` int(11) DEFAULT NULL,
    PRIMARY KEY (`Id`),
    CONSTRAINT fk_IdUser_Subscriber FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_IdSubscriber_Subscriber FOREIGN KEY (`IdSubscriber`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableSubscriber);