-- Step 1: Create the winners table
CREATE TABLE winners (
    winner_id INT AUTO_INCREMENT PRIMARY KEY,
    constituency_id INT NOT NULL,
    winner_name VARCHAR(255) NOT NULL,
    party_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (constituency_id) REFERENCES constituencies(constituency_id)
);


-- Step 2: Create the stored procedure to determine the winner and store results
