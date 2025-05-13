CREATE DATABASE echoes CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

USE echoes;

CREATE TABLE `channel_access` (
  `channelID` int(11) NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `channel_list` (
  `channelID` int(11) NOT NULL,
  `friendshipID` int(11) DEFAULT NULL,
  `groupID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `file` (
  `fileID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `unique_name` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `friendship` (
  `friendshipID` int(11) NOT NULL,
  `user1ID` int(11) NOT NULL,
  `user2ID` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `statusID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `friendshipstatus` (
  `statusID` int(11) NOT NULL,
  `initiator` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `group_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `ownerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `message` (
  `messageID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `replyTo` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(10) NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `refresh_token` (
  `ID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `displayName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profilePicture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

ALTER TABLE `channel_access`
  ADD KEY `userID` (`userID`),
  ADD KEY `channelID` (`channelID`);

ALTER TABLE `channel_list`
  ADD PRIMARY KEY (`channelID`),
  ADD KEY `friendshipID` (`friendshipID`),
  ADD KEY `groupID` (`groupID`);

ALTER TABLE `file`
  ADD PRIMARY KEY (`fileID`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `friendship`
  ADD PRIMARY KEY (`friendshipID`),
  ADD KEY `user1ID` (`user1ID`),
  ADD KEY `user2ID` (`user2ID`),
  ADD KEY `statusID` (`statusID`);

ALTER TABLE `friendshipstatus`
  ADD PRIMARY KEY (`statusID`),
  ADD KEY `initiator` (`initiator`);

ALTER TABLE `group_info`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `message`
  ADD PRIMARY KEY (`messageID`),
  ADD KEY `channelID` (`channelID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `replyTo` (`replyTo`);

ALTER TABLE `refresh_token`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `channel_list`
  MODIFY `channelID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `file`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `friendship`
  MODIFY `friendshipID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `friendshipstatus`
  MODIFY `statusID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `group_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `message`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `refresh_token`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `channel_access`
  ADD CONSTRAINT `channel_access_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `channel_access_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `channel_list` (`channelID`);

ALTER TABLE `channel_list`
  ADD CONSTRAINT `channel_list_ibfk_1` FOREIGN KEY (`friendshipID`) REFERENCES `friendship` (`friendshipID`),
  ADD CONSTRAINT `channel_list_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`id`);

ALTER TABLE `file`
  ADD CONSTRAINT `file_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

ALTER TABLE `friendship`
  ADD CONSTRAINT `friendship_ibfk_1` FOREIGN KEY (`user1ID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `friendship_ibfk_2` FOREIGN KEY (`user2ID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `friendship_ibfk_3` FOREIGN KEY (`statusID`) REFERENCES `friendshipstatus` (`statusID`);

ALTER TABLE `friendshipstatus`
  ADD CONSTRAINT `friendshipStatus_ibfk_1` FOREIGN KEY (`initiator`) REFERENCES `user` (`userID`);

ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`channelID`) REFERENCES `channel_list` (`channelID`),
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`replyTo`) REFERENCES `message` (`messageID`);

ALTER TABLE `refresh_token`
  ADD CONSTRAINT `refresh_token_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);
