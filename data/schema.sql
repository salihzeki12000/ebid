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


CREATE TABLE IF NOT EXISTS `category` (
  `categoryid` INT NOT NULL AUTO_INCREMENT,
  `cname` VARCHAR(45) NULL,
  `parentid` INT NULL,
  `childrenid` VARCHAR(45) NULL,
  PRIMARY KEY (`categoryid`))
ENGINE = InnoDB;

INSERT INTO `category` (`categoryid`, `cname`, `parentid`, `childrenid`) VALUES
  (1, 'Books', NULL, NULL),
  (2, 'Electronics', NULL, '5, 6, 7, 8'),
  (3, 'Toys', NULL, NULL),
  (4, 'Entertainment', NULL, '9, 10, 11'),
  (5, 'Computers & tablets', 2, NULL),
  (6, 'Cameras', 2, NULL),
  (7, 'TV', 2, NULL),
  (8, 'Cell phone', 2, NULL),
  (9, 'Music', 4, NULL),
  (10, 'DVD & movies', 4, NULL),
  (11, 'Video games', 4, NULL);

CREATE TABLE IF NOT EXISTS `Product` (
  `pid` INT NOT NULL,
  `pname` VARCHAR(50) NOT NULL,
  `description` VARCHAR(45) NULL,
  `startPrice` DECIMAL(8,2) NOT NULL,
  `expectPrice` DECIMAL(8,2) NULL,
  `buyNowPrice` DECIMAL(8,2) NULL,
  `defualtImg` VARCHAR(45) NULL,
  `otherImgs` VARCHAR(255) NULL,
  `startTime` DATETIME NOT NULL,
  `endTime` DATETIME NOT NULL,
  `categoryid` INT NULL,
  `shippingCost` DECIMAL(6,2),
  `auction` BLOB NULL,
  PRIMARY KEY (`pid`),
  CONSTRAINT `categoryid`
    FOREIGN KEY (`categoryid`)
    REFERENCES `category` (`categoryid`)
  )
ENGINE = InnoDB;
