DROP TABLE if EXISTS `IAD_Bid`;
DROP TABLE if EXISTS `IAD_Product`;
DROP TABLE if EXISTS `IAD_Category`;
DROP TABLE if EXISTS `IAD_User`;

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

CREATE TABLE `IAD_Category` (
  `categoryId` INT NOT NULL AUTO_INCREMENT,
  `cname` VARCHAR(45) NULL,
  `parentId` INT NULL,
  `childrenId` VARCHAR(45) NULL,
  PRIMARY KEY (`categoryid`))
  ENGINE = InnoDB;

CREATE TABLE `IAD_Product` (
  `pid` INT NOT NULL  AUTO_INCREMENT,
  `pname` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `startPrice` DECIMAL(8,2) NOT NULL,
  `expectPrice` DECIMAL(8,2) NULL,
  `buyNowPrice` DECIMAL(8,2) NULL,
  `currentPrice` DECIMAL(8,2) NULL,
  `defaultImage` VARCHAR(255) NULL,
  `imageLists` TEXT NULL,
  `startTime` DATETIME NOT NULL,
  `endTime` DATETIME NOT NULL,
  `categoryId` INT NULL,
  `shippingType` INT NULL,
  `shippingCost` DECIMAL(6,2),
  `auction` TINYINT NULL,
  `seller` INT,
  `condition` INT NOT NULL,
  `status` INT NULL,
  PRIMARY KEY (`pid`),
  CONSTRAINT `categoryId`
  FOREIGN KEY (`categoryId`)
  REFERENCES `IAD_Category` (`categoryId`),
  CONSTRAINT `sellerConst`
  FOREIGN KEY (`seller`)
  REFERENCES `IAD_User` (`uid`)
)
  ENGINE = InnoDB;

INSERT INTO `IAD_User` (`uid`, `username`, `password`, `email`, `address`, `state`, `zipcode`, `roles`) VALUES
(1, 'yanwsh', '8XB0aKJ8LomcivlSKVDZ3alWSGQoXQkqi5u2szg8atB3s5vS4sNyVuNKDLzRu75xwu3Irfx6MRKvNPxY8BpUHA==', 'yanwsh@vip.qq.com', '', '', '', 'ROLE_USER');

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
  (11, 'Video games', 8, NULL),
  (12, 'Home & garden', NULL, NULL),
  (13, 'Motors', NULL, '14, 15, 16'),
  (14, 'Cars & trucks', 13, NULL),
  (15, 'Motorcycles', 13, NULL),
  (16, 'Passenger vehicles', 13, NULL),
  (17, 'Other', NULL, '18,19'),
  (18, 'Health & beauty', 17, NULL),
  (19, 'Musical instruments & gear', 17, NULL);

-- -----------------------------------------------------
-- Table `IAD_Final_Project`.`bid`
-- -----------------------------------------------------
CREATE TABLE `IAD_Bid` (
  `bid` INT NOT NULL  AUTO_INCREMENT,
  `uid` INT NOT NULL,
  `pid` INT NOT NULL,
  `bidPrice` DECIMAL(8,2) NOT NULL,
  `bidTime` DATETIME NOT NULL,
  `status` INT NOT NULL,
  INDEX `pid_idx` (`pid` ASC),
  PRIMARY KEY (`bid`),
  CONSTRAINT `uid`
  FOREIGN KEY (`uid`)
  REFERENCES `IAD_User` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `Pid`
  FOREIGN KEY (`pid`)
  REFERENCES `IAD_Product` (`pid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;