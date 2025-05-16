CREATE TABLE competes_in (
    constituency_id INT NOT NULL,
    party_id INT NOT NULL,
    PRIMARY KEY (constituency_id, party_id),
    FOREIGN KEY (constituency_id) REFERENCES constituencies(constituency_id) ON DELETE CASCADE,
    FOREIGN KEY (party_id) REFERENCES parties(party_id) ON DELETE CASCADE
);
