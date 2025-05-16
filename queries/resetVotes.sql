-- Reset Votes

DELIMITER $$

CREATE TRIGGER reset_votes_trigger 
AFTER UPDATE ON candidates
FOR EACH ROW
BEGIN
    -- Check if all candidates have their votes reset to 0
    IF NEW.votes_received = 0 THEN
        -- Reset has_voted flag for all voters
        UPDATE voters SET has_voted = 0;
    END IF;
END$$

DELIMITER ;