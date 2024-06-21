--  Data start

--  (1, 'PROG_1, PROG_2', 'codage', 'creertest <test>avec option -c ou -p selon besoin', 'TOUS'),
--  (2, 'PROG_1, PROG_2', 'codage', 'modini <test>', 'TOUS'),
--  (3, 'PROG_1, PROG_2', 'codage', 'Vérifier que les dates dans le .tp correspond au fichier définition dans REF_CONTEXT/<contexte>/definition', NULL),
--  (4, 'PROG_1, PROG_2', 'codage', 'Mettre une instruction set_1553_Error pour chaque instrument PL utilisé : Set_1553_Error ("1", "TIMEOUT"); /* POSEIDON 1 = 1, POSEIDON 2 = 4 */ Set_1553_Error ("7", "TIMEOUT"); /* DORIS 1 = 7, DORIS 2 = 10 */', 'PL, POS3'),
--  (5, 'PROG_1, PROG_2', 'codage', "Autoriser l'utilisation de la FDTM durant le test", 'TOUS, sauf deploiement'),
--  (6, 'PROG_1, PROG_2', 'codage', 'Modifier la configuration du RM en cas de reconfiguration inattendue durant le test', 'TOUS, MeO'),
--  (7, 'PROG_1, PROG_2', 'codage', "Envoyer les TC spécifiques de reprise de contexte des SADM spécifiques avant l'envoi des TC decontexte", 'post RDP1'),
--  (8, 'PROG_1, PROG_2', 'codage', "Modifier la FDIR pour le health status GYRO pendant le mode TEST après l'envoi des TC de contexte", 'annulé'),
--  (9, 'PROG_1, PROG_2', 'codage', 'La procedure de WarmStart à utiliser se trouve dans la librairie cc_proc_warmstart. Elle porte le nom WS_miss5_<ctx_reprise>_PM<A ou B>(<tGPSWarmStart>).', 'MeO, GPS, warmstart'),
--  (10, 'PROG_1, PROG_2', 'exécution', 'Vérifier que outputs et outputs_ctx sont accessibles en écriture', 'TOUS'),
--  (11, 'PROG_1, PROG_2', 'exécution', 'Vérifier la date de la dernière calibration. Elle doit être < 15 jours sinon, il faut calibrer les gyros', 'BF GYRO, calibration'),
--  (12, 'PROG_1, PROG_2', 'exécution', 'Mettre à jour le fichier gyrostd.cal dans $SGSE_HOME/CURRENT_CONF/CHR et $SGSE_HOME/CONF/CHR en fonction des gyros utilisés', 'BF GYRO'),
--  (13, 'PROG_2', 'exécution', 'Modifier le fichier plbs.chr pour configurer le paquet DIODE sur le RT, la sous-adresse, la fréquence souhaitée (si paquet diode nécessaire)', 'PL'),
--  (14, 'PROG_2', 'exécution', "Connecter l'EBB POS-3 en fonction du PM utilisé (si besoin)", 'PL, POS3'),
--  (15, 'PROG_1, PROG_2', 'exécution', "Vérifier que l'alimentation du DHU est ON", 'TOUS'),
--  (16, 'PROG_2', 'exécution', "Mettre ON les alimentations de l'EBB POS-3 quand nécessaire", 'PL, POS3'),
--  (17, 'PROG_1, PROG_2', 'analyse', 'ana_fonc <test> -c -v', 'TOUS'),
--  (18, 'GENERIQUE', 'préparation', 'réception équipement: liste des documents à demander', 'TOUS'),
--  (19, 'GENERIQUE', 'préparation', 'expédition équipement: liste des documents à fournir', 'TOUS');

-- Data end

-- Erase good practices tables if exists

DROP TABLE IF EXISTS GOODPRACTICE_PROGRAM;
DROP TABLE IF EXISTS GOODPRACTICE_KEYWORD;
DROP TABLE IF EXISTS KEYWORD;
DROP TABLE IF EXISTS PROGRAM;
DROP TABLE IF EXISTS GOODPRACTICE;
DROP TABLE IF EXISTS PHASE;

-- Create good practices tables

CREATE TABLE PHASE (
    phase_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    phase_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE GOODPRACTICE (
    goodpractice_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    item VARCHAR(1000) NOT NULL,
    phase_id INT NOT NULL,
    is_hidden BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (phase_id) REFERENCES PHASE(phase_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE PROGRAM (
    program_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    program_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE KEYWORD (
    keyword_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    onekeyword VARCHAR(255)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE GOODPRACTICE_PROGRAM (
    goodpractice_program_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    goodpractice_id INT NOT NULL,
    program_id INT NOT NULL,
    is_hidden BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (goodpractice_id) REFERENCES GOODPRACTICE(goodpractice_id),
    FOREIGN KEY (program_id) REFERENCES PROGRAM(program_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE GOODPRACTICE_KEYWORD (
    goodpractice_keyword_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    goodpractice_id INT NOT NULL,
    keyword_id INT NOT NULL,
    FOREIGN KEY (goodpractice_id) REFERENCES GOODPRACTICE(goodpractice_id),
    FOREIGN KEY (keyword_id) REFERENCES KEYWORD(keyword_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Insert data into good practices tables

INSERT INTO PROGRAM (program_id, program_name) VALUES
(1, 'GENERIQUE'),
(2, 'PROG_1'),
(3, 'PROG_2'),

-- Presentation data

(4, 'PROG_4'),
(5, 'PROG_5'),
(6, 'PROG_6'),
(7, 'PROG_7'),
(8, 'PROG_8'),
(9, 'PROG_9'),
(10, 'PROG_10'),
(11, 'PROG_11'),
(12, 'PROG_12'),
(13, 'PROG_13'),
(14, 'PROG_14'),
(15, 'PROG_15'),
(16, 'PROG_16'),
(17, 'PROG_17'),
(18, 'PROG_18');


INSERT INTO PHASE (phase_id, phase_name) VALUES
(1, 'préparation'),
(2, 'exécution'),
(3, 'analyse'),
(4, 'codage');

INSERT INTO KEYWORD (keyword_id, onekeyword) VALUES
(1, ' '),
(2, 'TOUS'),
(3, 'PL'),
(4, 'POS3'),
(5, 'sauf deploiement'),
(6, 'MeO'),
(7, 'post RDP1'),
(8, 'annulé'),
(9, 'GPS'),
(10, 'warmstart'),
(11, 'BF GYRO'),
(12, 'calibration');

INSERT INTO GOODPRACTICE (goodpractice_id, item, phase_id) VALUES
(1, 'creertest <test>avec option -c ou -p selon besoin', 4),
(2, 'modini <test>', 4),
(3, 'Vérifier que les dates dans le .tp correspond au fichier définition dans REF_CONTEXT/<contexte>/definition', 4),
(4, 'Mettre une instruction set_1553_Error pour chaque instrument PL utilisé : Set_1553_Error ("1", "TIMEOUT"); /* POSEIDON 1 = 1, POSEIDON 2 = 4 */ Set_1553_Error ("7", "TIMEOUT"); /* DORIS 1 = 7, DORIS 2 = 10 */', 4),
(5, 'Autoriser l''utilisation de la FDTM durant le test', 4),
(6, 'Modifier la configuration du RM en cas de reconfiguration inattendue durant le test', 4),
(7, "Envoyer les TC spécifiques de reprise de contexte des SADM spécifiques avant l'envoi des TC decontexte", 4),
(8, "Modifier la FDIR pour le health status GYRO pendant le mode TEST après l'envoi des TC de contexte", 4),
(9, 'La procedure de WarmStart à utiliser se trouve dans la librairie cc_proc_warmstart. Elle porte le nom WS_miss5_<ctx_reprise>_PM<A ou B>(<tGPSWarmStart>).', 4),
(10, 'Vérifier que outputs et outputs_ctx sont accessibles en écriture', 2),
(11, 'Vérifier la date de la dernière calibration. Elle doit être < 15 jours sinon, il faut calibrer les gyros', 2),
(12, 'Mettre à jour le fichier gyrostd.cal dans $SGSE_HOME/CURRENT_CONF/CHR et $SGSE_HOME/CONF/CHR en fonction des gyros utilisés', 2),
(13, 'Modifier le fichier plbs.chr pour configurer le paquet DIODE sur le RT, la sous-adresse, la fréquence souhaitée (si paquet diode nécessaire)', 2),
(14, "Connecter l'EBB POS-3 en fonction du PM utilisé (si besoin)", 2),
(15, "Vérifier que l'alimentation du DHU est ON", 2),
(16, "Mettre ON les alimentations de l'EBB POS-3 quand nécessaire", 2),
(17, 'ana_fonc <test> -c -v', 3),
(18, 'réception équipement: liste des documents à demander', 1),
(19, 'expédition équipement: liste des documents à fournir', 1);

INSERT INTO GOODPRACTICE_PROGRAM (goodpractice_program_id, goodpractice_id, program_id) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 2, 2),
(4, 2, 3),
(5, 3, 2),
(6, 3, 3),
(7, 4, 2),
(8, 4, 3),
(9, 5, 2),
(10, 5, 3),
(11, 6, 2),
(12, 6, 3),
(13, 7, 2),
(14, 7, 3),
(15, 8, 2),
(16, 8, 3),
(17, 9, 2),
(18, 9, 3),
(19, 10, 2),
(20, 10, 3),
(21, 11, 2),
(22, 11, 3),
(23, 12, 2),
(24, 12, 3),
(25, 13, 3),
(26, 14, 3),
(27, 15, 2),
(28, 15, 3),
(29, 16, 3),
(30, 17, 2),
(31, 17, 3),
(32, 18, 1),
(33, 19, 1);

INSERT INTO GOODPRACTICE_KEYWORD (goodpractice_keyword_id, goodpractice_id, keyword_id) VALUES
(1, 1, 2),
(2, 2, 2),
(3, 3, 1),
(4, 4, 3),
(5, 4, 4),
(6, 5, 2),
(7, 5, 5),
(8, 6, 2),
(9, 6, 6),
(10, 7, 7),
(11, 8, 8),
(12, 9, 6),
(13, 9, 9),
(14, 9, 10),
(15, 10, 2),
(16, 11, 11),
(17, 11, 12),
(18, 12, 11),
(19, 13, 3),
(20, 14, 3),
(21, 14, 4),
(22, 15, 2),
(23, 16, 3),
(24, 16, 4),
(25, 17, 2),
(26, 18, 2),
(27, 19, 2);