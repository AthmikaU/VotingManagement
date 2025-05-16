CREATE TABLE IF NOT EXISTS voters(
    voter_id INT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);
ALTER TABLE `voters` ADD `address` VARCHAR(255) NULL , ADD `phone` INT NULL;


CREATE TABLE IF NOT EXISTS parties (
    party_id INT PRIMARY KEY,
    party_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS constituencies (
    constituency_id INT PRIMARY KEY,
    constituency_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);


INSERT INTO voters (voter_id, first_name, last_name, password)
VALUES
    (1, 'John', 'Doe', 'password123'),
    (2, 'Jane', 'Smith', 'mypassword');
INSERT INTO `voters` (`voter_id`, `first_name`, `last_name`, `password`, `address`, `phone`) VALUES ('4', 'Alice', 'Johnson', 'alice', NULL, NULL);

INSERT INTO parties (party_id, party_name, password)
VALUES
    (1, 'Democratic Party', 'party123'),
    (2, 'Republican Party', 'party456');

INSERT INTO constituencies (constituency_id, constituency_name, password)
VALUES
    (1, 'Constituency A', 'constituency123'),
    (2, 'Constituency B', 'constituency456');
INSERT INTO `constituencies` (`constituency_id`, `constituency_name`, `password`) VALUES ('3', 'Constituency C', 'constC');

INSERT INTO votes (voter_id, constituency_id, party_id)
VALUES
    (1, 1, 1), -- Voter 1 votes for Party 1 in Constituency A
    (2, 2, 2); -- Voter 2 votes for Party 2 in Constituency B

ALTER TABLE `voters` 
ADD COLUMN `constituency_id` INT NULL, 
ADD CONSTRAINT `fk_voters_constituency`
FOREIGN KEY (`constituency_id`) REFERENCES `constituencies`(`constituency_id`);

CREATE TABLE IF NOT EXISTS candidates (
    candidate_id INT PRIMARY KEY,
    voter_id INT NOT NULL,
    party_id INT NOT NULL,
    constituency_id INT NOT NULL,
    experience INT NOT NULL,  -- years of experience
    votes_received INT DEFAULT 0,  -- number of votes the candidate got after the election
    FOREIGN KEY (voter_id) REFERENCES voters(voter_id),
    FOREIGN KEY (party_id) REFERENCES parties(party_id),
    FOREIGN KEY (constituency_id) REFERENCES constituencies(constituency_id)
);

ALTER TABLE candidates
ADD CONSTRAINT unique_candidate UNIQUE (voter_id, party_id, constituency_id);

-- Insert Candidate 1
INSERT INTO candidates (voter_id, party_id, constituency_id, experience, votes_received)
VALUES (5, 1, 2, 3, 0);  -- Candidate 1 with 3 years of experience and no votes initially

-- Insert Candidate 2
INSERT INTO candidates (voter_id, party_id, constituency_id, experience, votes_received)
VALUES (8, 2, 1, 5, 0);  -- Candidate 2 with 5 years of experience and no votes initially

-- Insert Candidate 3
INSERT INTO candidates (voter_id, party_id, constituency_id, experience, votes_received)
VALUES (4, 1, 1, 8, 0);  -- Candidate 3 with 8 years of experience and no votes initially

-- Insert Candidate 4
INSERT INTO candidates (voter_id, party_id, constituency_id, experience, votes_received)
VALUES (9, 2, 1, 4, 0);  -- Candidate 4 with 4 years of experience and no votes initially


-- Adding has_voted column
ALTER TABLE voters 
ADD COLUMN has_voted TINYINT(1) DEFAULT 0;
