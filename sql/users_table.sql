-- Erase USERS table if exists

DROP TABLE IF EXISTS USERS;

-- Create USERS table

CREATE TABLE USERS (
  user_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  username VARCHAR(30) NOT NULL, 
  firstname VARCHAR(100) NOT NULL,
  lastname VARCHAR(100) NOT NULL, 
  `profile` CHAR(10) NOT NULL, 
  `password` CHAR(60) NOT NULL, 
  attempts INT NOT NULL,
  UNIQUE (username)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Insert data into USERS table

INSERT INTO USERS (user_id, username, firstname, lastname, profile, password, attempts) VALUES
(1, 'thales06', 'thales06', 'thales06', 'superadmin', '$2y$10$6XmUXzwsXV0iPB0iumf8jOUzv1QaNVs8V3E0rwN.kn9estQ5moWcO', 0),

-- Presentation data

(2, 'superadmin', 'thales06', 'thales06', 'superadmin', '$2y$10$O3UNMwwQoS03/NvRevTlb.A398UBPbSKhB0I.SIFIfBuGcysp06xy', 0),
(3, 'admin', 'thales06', 'thales06', 'admin', '$2y$10$LWgJSTxj5xoyaL3pK3TCwu7dorpVQhf8k8ETIf1r60EGoK35p.cxG', 0),
(4, 'operator', 'thales06', 'thales06', 'operator', '$2y$10$tCLGcSdGavx3Agsp5XLtHON21ONfzl1qjNG8Pzlc/3Xnq.ZFqB.PW', 0),
(5, 'user1', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 3),
(6, 'user2', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(7, 'user3', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(8, 'user4', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(9, 'user5', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(10, 'user6', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(11, 'user7', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 3),
(12, 'user8', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(13, 'user9', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(14, 'user10', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(15, 'user11', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(16, 'user12', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(17, 'user13', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 3),
(18, 'user14', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(19, 'user15', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0),
(20, 'user16', 'firstname', 'lastname', 'operator', '$2y$10$L1vCqY0CmHW2JAJpRb3ZQ.54EmpFmjW6d62LGVwQ9S.x7W1HVlAcy', 0);