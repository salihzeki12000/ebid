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
