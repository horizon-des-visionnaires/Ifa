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
    `IsAdmin` tinyint(1) DEFAULT '0',
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
    `Views` int(11) DEFAULT '0',
    `IdUser` int(11) DEFAULT NULL,
    PRIMARY KEY (`IdPost`),
    CONSTRAINT fk_User_UserPost FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePost);

$createTablePicturePost = ("CREATE TABLE IF NOT EXISTS
`PicturePost` (
    `IdPicturePost` int(11) NOT NULL AUTO_INCREMENT,
    `IdPost` int(11) DEFAULT NULL,
    `PicturePost` LONGBLOB DEFAULT NULL,
    PRIMARY KEY (`IdPicturePost`),
    CONSTRAINT fk_Post_PicturePost FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePicturePost);

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
`LikeFavorites` (
    `Id` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    `IsLike` tinyint(1) DEFAULT '0',
    `IsFavorites` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`Id`),
    CONSTRAINT fk_User_Like FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_Like FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableLike);

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

$createTableRequestPassPro = ("CREATE TABLE IF NOT EXISTS
`RequestPassPro` (
    `IdRequest` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `UserJob` varchar(255) DEFAULT NULL,
    `UserAge` int(11) DEFAULT NULL,
    `Description` TEXT DEFAULT NULL,
    `IdentityCardRecto` LONGBLOB DEFAULT NULL,
    `IdentityCardVerso` LONGBLOB DEFAULT NULL,
    `IsRequestValid` tinyint(1) DEFAULT '0',
    `UserPicture` LONGBLOB DEFAULT NULL,
    `UserAdress` varchar(255) DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdRequest`),
    CONSTRAINT fk_IdUser_RequestPassPro FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableRequestPassPro);

$createTableUserMessages = ("CREATE TABLE IF NOT EXISTS
`UserMessages` (
    `IdMessage` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `Message` TEXT DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdMessage`),
    CONSTRAINT fk_IdUser_UserMessages FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableUserMessages);