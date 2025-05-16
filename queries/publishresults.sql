-- Create the stored procedure to determine the winner and store results

DELIMITER $$

CREATE PROCEDURE publish_results()
BEGIN
    -- Insert the winning candidate for each constituency into the winners table
    INSERT INTO winners (constituency_id, winner_name, party_name)
    SELECT 
        c.constituency_id, 
        CONCAT(v.first_name, ' ', v.last_name) AS winner_name, 
        p.party_name
    FROM 
        candidates c
        JOIN voters v ON c.voter_id = v.voter_id
        JOIN parties p ON c.party_id = p.party_id
    WHERE c.votes_received = (
        SELECT MAX(c2.votes_received) 
        FROM candidates c2 
        WHERE c2.constituency_id = c.constituency_id
    )
    GROUP BY c.constituency_id, p.party_name, v.first_name, v.last_name; 
END$$

DELIMITER ; 