drop table if exists `IAD_User`;

CREATE TABLE `IAD_User` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `address` VARCHAR(45) NULL,
  `state` VARCHAR(45) NULL,
  `zipcode` VARCHAR(10) NULL,
  `roles`  VARCHAR(255) NULL,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB;

INSERT INTO `IAD_User` (`uid`, `username`, `password`, `email`, `address`, `state`, `zipcode`, `roles`) VALUES
(1, 'yanwsh', '8XB0aKJ8LomcivlSKVDZ3alWSGQoXQkqi5u2szg8atB3s5vS4sNyVuNKDLzRu75xwu3Irfx6MRKvNPxY8BpUHA==', 'yanwsh@vip.qq.com', '', '', '', 'ROLE_USER');


CREATE TABLE IF NOT EXISTS `IAD_Category` (
  `categoryId` INT NOT NULL AUTO_INCREMENT,
  `cname` VARCHAR(45) NULL,
  `parentId` INT NULL,
  `childrenId` VARCHAR(45) NULL,
  PRIMARY KEY (`categoryid`))
  ENGINE = InnoDB;

INSERT INTO `IAD_Category` (`categoryId`, `cname`, `parentId`, `childrenId`) VALUES
  (1, 'Books', NULL, NULL),
  (2, 'Electronics', NULL, '3, 4, 5, 6'),
  (3, 'Computers & tablets', 2, NULL),
  (4, 'Cameras', 2, NULL),
  (5, 'TV', 2, NULL),
  (6, 'Cell phones', 2, NULL),
  (7, 'Toys', NULL, NULL),
  (8, 'Entertainment', NULL, '9, 10, 11'),
  (9, 'Music', 8, NULL),
  (10, 'DVD & Movies', 8, NULL),
  (11, 'Video games', 8, NULL);

CREATE TABLE IF NOT EXISTS `IAD_Product` (
  `pid` INT NOT NULL,
  `pname` VARCHAR(50) NOT NULL,
  `description` VARCHAR(45) NULL,
  `startPrice` DECIMAL(8,2) NOT NULL,
  `expectPrice` DECIMAL(8,2) NULL,
  `buyNowPrice` DECIMAL(8,2) NULL,
  `defualtImg` VARCHAR(45) NULL,
  `imageLists` VARCHAR(255) NULL,
  `startTime` DATETIME NOT NULL,
  `endTime` DATETIME NOT NULL,
  `categoryId` INT NULL,
  `shippingCost` DECIMAL(6,2),
  `auction` TINYINT NULL,
  PRIMARY KEY (`pid`),
  CONSTRAINT `categoryId`
  FOREIGN KEY (`categoryId`)
  REFERENCES `IAD_Category` (`categoryId`)
)
  ENGINE = InnoDB;
