-- Erase PASSWORD table if exists

DROP TABLE IF EXISTS PASSWORD;

-- Create PASSWORD table

CREATE TABLE PASSWORD (
  password_id INT PRIMARY KEY NOT NULL,
  n INT NOT NULL,
  p INT NOT NULL,
  q INT NOT NULL,
  r INT NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Insert data into PASSWORD table

INSERT INTO PASSWORD (password_id, n, p, q, r) VALUES
(0, 0, 0, 0, 0);